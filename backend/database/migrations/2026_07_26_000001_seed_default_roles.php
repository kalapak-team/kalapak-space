<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public $withinTransaction = false;

    public function up(): void
    {
        $now = now();

        foreach ([
            [
                'name' => 'admin',
                'display_name' => 'Administrator',
                'description' => 'Manage content: projects, blog, media, messages',
            ],
            [
                'name' => 'member',
                'display_name' => 'Member',
                'description' => 'Team member with profile access',
            ],
            [
                'name' => 'guest',
                'display_name' => 'Guest',
                'description' => 'Registered user with basic access',
            ],
        ] as $role) {
            DB::table('roles')->insertOrIgnore([
                'name' => $role['name'],
                'display_name' => $role['display_name'],
                'description' => $role['description'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('roles')->whereIn('name', ['admin', 'member', 'guest'])->delete();
    }
};
