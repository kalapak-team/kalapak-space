<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public $withinTransaction = false;

    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        // Prisma/Postgres: ensure OAuth users can be created without a password.
        // Laravel's ->change() is unreliable here without doctrine/dbal.
        DB::statement('ALTER TABLE users ALTER COLUMN password DROP NOT NULL');

        $now = now();
        foreach ([
            ['name' => 'admin', 'display_name' => 'Administrator', 'description' => 'Manage content: projects, blog, media, messages'],
            ['name' => 'member', 'display_name' => 'Member', 'description' => 'Team member with profile access'],
            ['name' => 'guest', 'display_name' => 'Guest', 'description' => 'Registered user with basic access'],
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
        // Keep password nullable — rolling back would break existing OAuth users.
    }
};
