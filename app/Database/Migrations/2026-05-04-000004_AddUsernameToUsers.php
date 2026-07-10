<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUsernameToUsers extends Migration
{
    public function up(): void
    {
        if (! $this->db->fieldExists('username', 'users')) {
            $this->forge->addColumn('users', [
                'username' => [
                    'type' => 'VARCHAR',
                    'constraint' => 80,
                    'null' => true,
                    'after' => 'name',
                ],
            ]);
            $this->forge->addUniqueKey('username');
            $this->forge->processIndexes('users');
        }

        $users = $this->db->table('users')->select('id, name, email, username')->get()->getResultArray();
        foreach ($users as $user) {
            if (! empty($user['username'])) {
                continue;
            }

            $base = strtolower((string) preg_replace('/[^a-zA-Z0-9]+/', '.', strtok((string) $user['email'], '@')));
            $base = trim($base, '.') ?: 'user';
            $username = $base;
            $suffix = 1;

            while ($this->db->table('users')->where('username', $username)->where('id !=', $user['id'])->countAllResults() > 0) {
                $username = $base . $suffix;
                $suffix++;
            }

            $this->db->table('users')->where('id', $user['id'])->update(['username' => $username]);
        }
    }

    public function down(): void
    {
        if ($this->db->fieldExists('username', 'users')) {
            $this->forge->dropColumn('users', 'username');
        }
    }
}
