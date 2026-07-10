<?php

namespace App\Models;

use CodeIgniter\Model;

class CrmUserModel extends Model
{
    protected $table = 'crm_users';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $allowedFields = [
        'username',
        'password_hash',
        'full_name',
        'role',
        'status',
        'campus_id',
        'registered_by',
        'last_login_at',
    ];
    protected $useTimestamps = true;
    protected $validationRules = [
        'id' => 'permit_empty|is_natural_no_zero',
        'username' => 'required|alpha_numeric_punct|min_length[3]|max_length[80]|is_unique[crm_users.username,id,{id}]',
        'full_name' => 'required|min_length[3]|max_length[120]',
        'role' => 'required|in_list[Agent,Admin]',
        'status' => 'required|in_list[Active,Inactive,Review]',
        'campus_id' => 'permit_empty|is_natural_no_zero',
        'registered_by' => 'permit_empty|is_natural_no_zero',
    ];
}
