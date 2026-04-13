<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::table('roles')->insertOrIgnore([
            'name' => 'superadmin',
            'display_name' => 'Super Admin',
            'description' => 'Full unrestricted access to all system features',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('roles')->where('name', 'superadmin')->delete();
    }
};
