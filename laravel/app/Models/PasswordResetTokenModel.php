<?php

namespace App\Models;

use CodeIgniter\Model;

class PasswordResetTokenModel extends Model
{
    protected $table = 'password_reset_tokens';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'user_id',
        'email',
        'token_hash',
        'expires_at',
        'used_at',
        'requested_ip',
        'user_agent',
    ];
    protected $useTimestamps = true;
}
