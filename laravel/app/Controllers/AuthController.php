<?php

namespace App\Controllers;

use App\Models\LoginAttemptModel;
use App\Models\NotificationModel;
use App\Models\PasswordResetTokenModel;
use App\Models\RoleModel;
use App\Models\UserModel;
use Throwable;

class AuthController extends BaseController
{
    public function login()
    {
        if (session('isLoggedIn')) {
            return redirect()->to(site_url('dashboard'));
        }

        return view('pages/login', [
            'title' => 'Login',
            'loginMetrics' => $this->loginMetrics(),
        ]);
    }

    public function register()
    {
        if (session('isLoggedIn')) {
            return redirect()->to(site_url('dashboard'));
        }

        return view('pages/register', [
            'title' => 'Daftar User',
        ]);
    }

    public function forgotPassword()
    {
        if (session('isLoggedIn')) {
            return redirect()->to(site_url('dashboard'));
        }

        return view('pages/forgot_password', [
            'title' => 'Lupa Password',
        ]);
    }

    public function sendResetLink()
    {
        if (session('isLoggedIn')) {
            return redirect()->to(site_url('dashboard'));
        }

        if (! $this->validate([
            'identity' => 'required|min_length[3]',
        ])) {
            return redirect()->back()->withInput()->with('error', 'Username atau email wajib diisi.');
        }

        $identity = strtolower(trim((string) $this->request->getPost('identity')));
        $message = 'Jika akun ditemukan dan aktif, link reset password akan dikirim ke email terdaftar.';
        $redirect = redirect()->to(site_url('forgot-password'))->with('success', $message);

        $user = (new UserModel())
            ->groupStart()
                ->where('email', $identity)
                ->orWhere('username', $identity)
            ->groupEnd()
            ->where('status', 'Aktif')
            ->first();

        if (! $user) {
            return $redirect;
        }

        $token = bin2hex(random_bytes(32));
        $resetLink = site_url('reset-password/' . $token);
        $this->expireActiveResetTokens((int) $user['id']);

        (new PasswordResetTokenModel())->insert([
            'user_id' => (int) $user['id'],
            'email' => strtolower((string) $user['email']),
            'token_hash' => hash('sha256', $token),
            'expires_at' => date('Y-m-d H:i:s', time() + 3600),
            'requested_ip' => $this->request->getIPAddress(),
            'user_agent' => substr((string) $this->request->getUserAgent(), 0, 255),
        ]);

        $this->sendPasswordResetEmail($user, $resetLink);

        if (defined('ENVIRONMENT') && ENVIRONMENT !== 'production') {
            $redirect = $redirect->with('reset_link', $resetLink);
        }

        return $redirect;
    }

    public function resetPassword(string $token)
    {
        if (session('isLoggedIn')) {
            return redirect()->to(site_url('dashboard'));
        }

        if (! $this->validResetToken($token)) {
            return redirect()->to(site_url('forgot-password'))->with('error', 'Link reset password tidak valid atau sudah kedaluwarsa.');
        }

        return view('pages/reset_password', [
            'title' => 'Reset Password',
            'token' => $token,
        ]);
    }

    public function updatePassword(string $token)
    {
        if (session('isLoggedIn')) {
            return redirect()->to(site_url('dashboard'));
        }

        $reset = $this->validResetToken($token);
        if (! $reset) {
            return redirect()->to(site_url('forgot-password'))->with('error', 'Link reset password tidak valid atau sudah kedaluwarsa.');
        }

        $password = (string) $this->request->getPost('password');
        $confirmation = (string) $this->request->getPost('password_confirmation');

        if (strlen($password) < 6) {
            return redirect()->back()->with('error', 'Password baru minimal 6 karakter.');
        }

        if ($password !== $confirmation) {
            return redirect()->back()->with('error', 'Konfirmasi password tidak sama.');
        }

        $userModel = new UserModel();
        $user = $userModel->where('status', 'Aktif')->find((int) $reset['user_id']);
        if (! $user) {
            return redirect()->to(site_url('forgot-password'))->with('error', 'Akun tidak ditemukan atau tidak aktif.');
        }

        $userModel->update((int) $user['id'], [
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        ]);

        (new PasswordResetTokenModel())->update((int) $reset['id'], [
            'used_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to(site_url('login'))->with('success', 'Password berhasil diubah. Silakan login dengan password baru.');
    }

    public function storeRegistration()
    {
        if (session('isLoggedIn')) {
            return redirect()->to(site_url('dashboard'));
        }

        $role = $this->activeRoleByName('Event Creator');
        if (! $role || ($role['name'] ?? '') === 'Admin Sistem') {
            return redirect()->back()->withInput()->with('error', 'Role default pendaftaran belum tersedia. Hubungi Admin Sistem.');
        }

        $password = (string) $this->request->getPost('password');
        if (strlen($password) < 6) {
            return redirect()->back()->withInput()->with('error', 'Password minimal 6 karakter.');
        }

        $permissions = $this->permissionsFromRole($role);
        $userModel = new UserModel();
        $ok = $userModel->insert([
            'name' => $this->request->getPost('name'),
            'username' => strtolower(trim((string) $this->request->getPost('username'))),
            'email' => strtolower(trim((string) $this->request->getPost('email'))),
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'role' => $role['name'],
            'access_permissions' => json_encode($permissions),
            'status' => 'Review',
        ]);

        if (! $ok) {
            return redirect()->back()->withInput()->with('error', implode(' ', $userModel->errors()));
        }

        $this->notifyAdmins('Pendaftaran user baru menunggu approval: ' . (string) $this->request->getPost('name') . '.');

        return redirect()->to(site_url('login'))->with('success', 'Pendaftaran berhasil dikirim. Akun dapat digunakan setelah disetujui Admin Sistem.');
    }

    public function attempt()
    {
        $identity = strtolower(trim((string) $this->request->getPost('identity')));
        $password = (string) $this->request->getPost('password');
        $ipAddress = $this->request->getIPAddress();
        $attempts = new LoginAttemptModel();

        if ($attempts->recentFailures($identity, $ipAddress) >= 5) {
            $this->recordAttempt($identity, false);

            return redirect()->back()->withInput()->with('error', 'Terlalu banyak percobaan gagal. Coba lagi setelah 15 menit.');
        }

        if (! $this->validate([
            'identity' => 'required|min_length[3]',
            'password' => 'required|min_length[6]',
        ])) {
            $this->recordAttempt($identity, false);

            return redirect()->back()->withInput()->with('error', 'Username/email dan password wajib valid.');
        }

        $userModel = new UserModel();
        $user = $userModel
            ->groupStart()
                ->where('email', $identity)
                ->orWhere('username', $identity)
            ->groupEnd()
            ->first();

        if (! $user || ! password_verify($password, $user['password_hash'])) {
            $this->recordAttempt($identity, false);

            return redirect()->back()->withInput()->with('error', 'Username/email atau password salah.');
        }

        if ($user['status'] !== 'Aktif') {
            $this->recordAttempt($identity, false);

            return redirect()->back()->withInput()->with('error', 'Akun belum aktif. Hubungi admin sistem.');
        }

        session()->regenerate(true);
        session()->put([
            'isLoggedIn' => true,
            'user' => [
                'id' => (int) $user['id'],
                'name' => $user['name'],
                'username' => $user['username'],
                'email' => $user['email'],
                'role' => $user['role'],
                'campus_id' => $user['campus_id'] ?? null,
                'permissions' => $this->permissionsFromUser($user),
            ],
        ]);

        $userModel->update($user['id'], ['last_login_at' => date('Y-m-d H:i:s')]);
        $this->recordAttempt($identity, true);

        return redirect()->to(site_url('dashboard'))->with('success', 'Login berhasil. Selamat datang, ' . $user['name'] . '.');
    }

    public function logout()
    {
        session()->invalidate();

        return redirect()->to(site_url('login'))->with('success', 'Anda sudah logout.');
    }

    private function recordAttempt(string $identity, bool $success): void
    {
        (new LoginAttemptModel())->insert([
            'email' => $identity !== '' ? $identity : 'unknown',
            'ip_address' => $this->request->getIPAddress(),
            'user_agent' => substr((string) $this->request->getUserAgent(), 0, 255),
            'success' => $success ? 1 : 0,
            'attempted_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function permissionsFromUser(array $user): array
    {
        if ($user['role'] === 'Admin Sistem') {
            return array_keys($this->accessOptions());
        }

        $decoded = json_decode((string) ($user['access_permissions'] ?? '[]'), true);

        return is_array($decoded) ? array_values(array_filter($decoded, 'is_string')) : [];
    }

    private function accessOptions(): array
    {
        return [
            'dashboard' => 'Dashboard',
            'crm' => 'CRM',
            'events' => 'Event',
            'calendar' => 'Kalender',
            'approvals' => 'Approval',
            'documents' => 'Dokumen',
            'reports' => 'Laporan',
            'one-campus' => 'One Campus',
            'analytics' => 'Analytics',
            'notifications' => 'Notifikasi',
            'users' => 'Users',
        ];
    }

    private function selectedPermissions(string $field, array $fallback): array
    {
        $selected = $this->request->getPost($field);
        $selected = is_array($selected) ? $selected : $fallback;
        $allowed = array_diff(array_keys($this->accessOptions()), ['users']);
        $permissions = array_values(array_intersect($allowed, $selected));

        if (! in_array('dashboard', $permissions, true)) {
            array_unshift($permissions, 'dashboard');
        }

        return array_values(array_unique($permissions));
    }

    private function permissionsFromRole(array $role): array
    {
        $decoded = json_decode((string) ($role['default_permissions'] ?? '[]'), true);
        $permissions = is_array($decoded) ? $decoded : ['dashboard'];

        return $this->selectedSafePermissions($permissions);
    }

    private function selectedSafePermissions(array $permissions): array
    {
        $allowed = array_diff(array_keys($this->accessOptions()), ['users']);
        $permissions = array_values(array_intersect($allowed, $permissions));

        if (! in_array('dashboard', $permissions, true)) {
            array_unshift($permissions, 'dashboard');
        }

        return array_values(array_unique($permissions));
    }

    private function activeRoleByName(string $name): ?array
    {
        $name = trim((string) preg_replace('/\s+/', ' ', $name));
        if ($name === '') {
            return null;
        }

        return (new RoleModel())->where('name', $name)->first();
    }

    private function notifyAdmins(string $message): void
    {
        $admins = (new UserModel())->where('role', 'Admin Sistem')->where('status', 'Aktif')->findAll();
        $notificationModel = new NotificationModel();

        foreach ($admins as $admin) {
            $notificationModel->insert([
                'user_id' => $admin['id'],
                'type' => 'user',
                'message' => $message,
            ]);
        }
    }

    private function expireActiveResetTokens(int $userId): void
    {
        db_connect()
            ->table('password_reset_tokens')
            ->where('user_id', $userId)
            ->where('used_at', null)
            ->update([
                'used_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
    }

    private function validResetToken(string $token): ?array
    {
        if (! preg_match('/^[a-f0-9]{64}$/', $token)) {
            return null;
        }

        return (new PasswordResetTokenModel())
            ->where('token_hash', hash('sha256', $token))
            ->where('used_at', null)
            ->where('expires_at >=', date('Y-m-d H:i:s'))
            ->first();
    }

    private function sendPasswordResetEmail(array $user, string $resetLink): void
    {
        try {
            $config = [
                'protocol' => env('email.protocol', 'mail'),
                'SMTPHost' => env('email.SMTPHost', ''),
                'SMTPUser' => env('email.SMTPUser', ''),
                'SMTPPass' => env('email.SMTPPass', ''),
                'SMTPPort' => (int) env('email.SMTPPort', 25),
                'SMTPCrypto' => env('email.SMTPCrypto', 'tls'),
                'mailType' => env('email.mailType', 'text'),
                'charset' => 'UTF-8',
                'newline' => "\r\n",
                'CRLF' => "\r\n",
            ];

            $email = service('email', $config, false);
            $email->setFrom(env('email.fromEmail', 'noreply@eventhub.local'), env('email.fromName', 'EventHub'));
            $email->setTo((string) $user['email']);
            $email->setSubject('Reset Password EventHub');
            $email->setMessage(
                "Halo " . (string) $user['name'] . ",\n\n"
                . "Kami menerima permintaan reset password untuk akun EventHub Anda.\n"
                . "Klik link berikut untuk membuat password baru. Link berlaku selama 1 jam:\n\n"
                . $resetLink . "\n\n"
                . "Jika Anda tidak meminta reset password, abaikan email ini."
            );

            if (! $email->send(false)) {
                log_message('error', 'Gagal mengirim email reset password: ' . strip_tags($email->printDebugger(['headers', 'subject'])));
            }
        } catch (Throwable) {
            log_message('error', 'Gagal mengirim email reset password karena exception.');
        }
    }

    private function loginMetrics(): array
    {
        return [
            [
                'label' => 'Event',
                'value' => $this->countRows('events', ['deleted_at' => null]),
            ],
            [
                'label' => 'Approval',
                'value' => $this->countRows('events', ['status' => 'diajukan', 'deleted_at' => null]),
            ],
            [
                'label' => 'Dokumen',
                'value' => $this->countRows('proposals') + $this->countRows('reports'),
            ],
        ];
    }

    private function countRows(string $table, array $where = []): int
    {
        try {
            $db = db_connect();
            if (! $db->tableExists($table)) {
                return 0;
            }

            $builder = $db->table($table);
            foreach ($where as $field => $value) {
                $builder->where($field, $value);
            }

            return $builder->countAllResults();
        } catch (Throwable) {
            return 0;
        }
    }
}
