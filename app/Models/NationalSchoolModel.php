<?php

namespace App\Models;

use CodeIgniter\Model;

class NationalSchoolModel extends Model
{
    protected $table = 'national_schools';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'npsn',
        'name',
        'school_type',
        'education_type',
        'school_status',
        'stage',
        'city',
        'district',
        'subdistrict',
        'address',
        'track',
        'supervising_ministry',
    ];
    protected $useTimestamps = true;

    /**
     * Search schools by NPSN prefix or Name keywords
     */
    public function searchSchools(string $query, int $limit = 15): array
    {
        $query = trim($query);
        if ($query === '') {
            return [];
        }

        // If numeric, prioritize NPSN search
        if (ctype_digit($query)) {
            $builder = $this->like('npsn', $query, 'after')
                ->orderBy('npsn', 'ASC')
                ->limit($limit);
            return $builder->findAll();
        }

        // Text search: search by name or city
        return $this->groupStart()
                ->like('name', $query)
                ->orLike('city', $query)
                ->orLike('district', $query)
            ->groupEnd()
            ->orderBy('name', 'ASC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Find single school by exact NPSN
     */
    public function findByNpsn(string $npsn): ?array
    {
        $npsn = trim($npsn);
        if ($npsn === '') {
            return null;
        }

        return $this->where('npsn', $npsn)->first();
    }
}

