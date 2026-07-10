<?php

namespace App\Models;

use CodeIgniter\Model;

class LoginAttemptModel extends Model
{
    protected $table = 'auth_login_attempts';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['email', 'ip_address', 'user_agent', 'success', 'attempted_at'];
    protected $useTimestamps = false;

    public function recentFailures(string $email, string $ipAddress, int $minutes = 15): int
    {
        return $this
            ->groupStart()
                ->where('email', $email)
                ->orWhere('ip_address', $ipAddress)
            ->groupEnd()
            ->where('success', 0)
            ->where('attempted_at >=', date('Y-m-d H:i:s', time() - ($minutes * 60)))
            ->countAllResults();
    }
}
