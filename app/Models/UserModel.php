<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $allowedFields = [
        'name',
        'username',
        'email',
        'password_hash',
        'role',
        'campus_id',
        'access_permissions',
        'status',
        'last_login_at',
    ];
    protected $useTimestamps = true;
    protected $validationRules = [
        'id' => 'permit_empty|is_natural_no_zero',
        'name' => 'required|min_length[3]|max_length[120]',
        'username' => 'required|alpha_numeric_punct|min_length[3]|max_length[80]|is_unique[users.username,id,{id}]',
        'email' => 'required|valid_email|max_length[160]|is_unique[users.email,id,{id}]',
        'role' => 'required|max_length[80]',
        'status' => 'required|in_list[Aktif,Review,Nonaktif]',
    ];
}
