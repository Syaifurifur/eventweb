<?php

namespace App\Models;

use CodeIgniter\Model;

class RoleModel extends Model
{
    protected $table = 'roles';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $allowedFields = [
        'name',
        'description',
        'is_system',
        'default_permissions',
    ];
    protected $useTimestamps = true;
    protected $validationRules = [
        'id' => 'permit_empty|is_natural_no_zero',
        'name' => 'required|min_length[3]|max_length[80]|is_unique[roles.name,id,{id}]',
        'description' => 'permit_empty|max_length[255]',
    ];
}
