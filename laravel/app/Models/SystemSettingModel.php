<?php

namespace App\Models;

use CodeIgniter\Model;

class SystemSettingModel extends Model
{
    protected $table = 'system_settings';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'setting_key',
        'setting_value',
        'description',
        'updated_at',
    ];

    public static function getSetting(string $key, ?string $default = null): ?string
    {
        $row = (new self())->where('setting_key', $key)->first();
        return $row['setting_value'] ?? $default;
    }

    public static function setSetting(string $key, ?string $value, ?string $description = null): void
    {
        $model = new self();
        $row = $model->where('setting_key', $key)->first();
        $data = [
            'setting_key' => $key,
            'setting_value' => $value,
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        if ($description !== null) {
            $data['description'] = $description;
        }

        if ($row) {
            $model->update($row['id'], $data);
        } else {
            $model->insert($data);
        }
    }
}
