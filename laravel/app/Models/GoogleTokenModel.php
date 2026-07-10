<?php

namespace App\Models;

use CodeIgniter\Model;

class GoogleTokenModel extends Model
{
    protected $table = 'google_oauth_tokens';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'user_id',
        'provider',
        'access_token',
        'refresh_token',
        'scope',
        'token_type',
        'expires_at',
    ];
    protected $useTimestamps = true;

    public function tokenForUser(int $userId): ?array
    {
        return $this
            ->where('user_id', $userId)
            ->where('provider', 'google_calendar')
            ->orderBy('id', 'DESC')
            ->first();
    }

    public function latestActiveAdminToken(): ?array
    {
        return $this
            ->select('google_oauth_tokens.*')
            ->join('users', 'users.id = google_oauth_tokens.user_id')
            ->where('google_oauth_tokens.provider', 'google_calendar')
            ->where('users.role', 'Admin Sistem')
            ->where('users.status', 'Aktif')
            ->where('users.deleted_at', null)
            ->orderBy('google_oauth_tokens.updated_at', 'DESC')
            ->orderBy('google_oauth_tokens.id', 'DESC')
            ->first();
    }
}
