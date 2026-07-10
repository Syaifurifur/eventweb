<?php

namespace App\Libraries;

use Throwable;

class EventHubCrmBridge
{
    public function signIn(): bool
    {
        if (session('crmLoggedIn')) {
            $this->syncExistingEventHubSession();

            return true;
        }

        $eventHubUser = session('user');
        if (! session('isLoggedIn') || ! is_array($eventHubUser) || (int) ($eventHubUser['id'] ?? 0) <= 0) {
            return false;
        }

        try {
            $crmUser = $this->ensureCrmUser($eventHubUser);
            if ($crmUser === null) {
                return false;
            }

            session()->set([
                'crmLoggedIn' => true,
                'crmUser' => [
                    'id' => (int) $crmUser['id'],
                    'username' => $eventHubUser['username'] ?? $crmUser['username'],
                    'name' => $eventHubUser['name'] ?? $crmUser['full_name'],
                    'role' => 'Admin',
                    'campus_id' => $this->crmCampusScope($eventHubUser),
                    'registered_by' => (int) $eventHubUser['id'],
                    'source' => 'eventhub',
                ],
            ]);

            return true;
        } catch (Throwable $error) {
            log_message('error', 'Gagal membuat session CRM dari EventHub: ' . $error->getMessage());

            return false;
        }
    }

    private function ensureCrmUser(array $eventHubUser): ?array
    {
        $db = db_connect();
        if (! $db->tableExists('crm_users')) {
            return null;
        }

        $now = date('Y-m-d H:i:s');
        $eventHubId = (int) $eventHubUser['id'];
        $username = 'eventhub_' . $eventHubId;
        $table = $db->table('crm_users');
        $existing = $table->where('username', $username)->get()->getRowArray();
        $data = [
            'full_name' => trim((string) ($eventHubUser['name'] ?? 'EventHub User')),
            'role' => 'Admin',
            'status' => 'Active',
            'last_login_at' => $now,
            'updated_at' => $now,
        ];

        if ($db->fieldExists('campus_id', 'crm_users')) {
            $data['campus_id'] = $this->crmCampusScope($eventHubUser);
        }

        if ($db->fieldExists('registered_by', 'crm_users')) {
            $data['registered_by'] = $eventHubId;
        }

        if ($db->fieldExists('deleted_at', 'crm_users')) {
            $data['deleted_at'] = null;
        }

        if ($existing) {
            $db->table('crm_users')->where('id', $existing['id'])->update($data);

            return $db->table('crm_users')->where('id', $existing['id'])->get()->getRowArray();
        }

        $data['username'] = $username;
        $data['password_hash'] = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
        $data['created_at'] = $now;

        $db->table('crm_users')->insert($data);
        $id = (int) $db->insertID();

        return $id > 0 ? $db->table('crm_users')->where('id', $id)->get()->getRowArray() : null;
    }

    private function syncExistingEventHubSession(): void
    {
        $eventHubUser = session('user');
        if (! session('isLoggedIn') || ! is_array($eventHubUser) || session('crmUser.source') !== 'eventhub') {
            return;
        }

        $campusScope = $this->crmCampusScope($eventHubUser);
        session()->set('crmUser.role', 'Admin');
        session()->set('crmUser.campus_id', $campusScope);

        $crmUserId = (int) session('crmUser.id');
        if ($crmUserId <= 0) {
            return;
        }

        $db = db_connect();
        if ($db->tableExists('crm_users') && $db->fieldExists('campus_id', 'crm_users')) {
            $db->table('crm_users')->where('id', $crmUserId)->update([
                'role' => 'Admin',
                'campus_id' => $campusScope,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    private function crmCampusScope(array $eventHubUser): ?int
    {
        $role = (string) ($eventHubUser['role'] ?? '');
        if (strtolower($this->normalizedRoleName($role)) !== 'kepala kampus' && ! $this->isMarkomRole($role)) {
            return null;
        }

        $campusId = (int) ($eventHubUser['campus_id'] ?? 0);
        if ($campusId <= 0) {
            return null;
        }

        if ($this->isMarkomRole($role) && $this->isGlobalCampus($campusId)) {
            return null;
        }

        return $campusId;
    }

    private function isMarkomRole(string $role): bool
    {
        $role = strtolower($this->normalizedRoleName($role));

        return in_array($role, ['markom', 'marketing akademik'], true);
    }

    private function normalizedRoleName(string $role): string
    {
        return trim((string) preg_replace('/\s+/', ' ', $role));
    }

    private function isGlobalCampus(int $campusId): bool
    {
        $db = db_connect();
        if (! $db->tableExists('campuses')) {
            return false;
        }

        $campus = $db->table('campuses')->select('name')->where('id', $campusId)->get()->getRowArray();
        $name = strtolower((string) ($campus['name'] ?? ''));

        return $name !== '' && (
            str_contains($name, 'semua')
            || str_contains($name, 'all')
            || str_contains($name, 'global')
            || str_contains($name, 'pusat')
        );
    }
}
