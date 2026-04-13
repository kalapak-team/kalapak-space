<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Drop the existing CHECK constraint on the resource column
        DB::statement("ALTER TABLE user_permissions DROP CONSTRAINT IF EXISTS user_permissions_resource_check");

        // Re-add with all 6 resource values
        DB::statement("ALTER TABLE user_permissions ADD CONSTRAINT user_permissions_resource_check CHECK (resource::text = ANY (ARRAY['projects'::text, 'categories'::text, 'tags'::text, 'team_members'::text, 'blog_posts'::text, 'media'::text]))");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE user_permissions DROP CONSTRAINT IF EXISTS user_permissions_resource_check");
        DB::statement("ALTER TABLE user_permissions ADD CONSTRAINT user_permissions_resource_check CHECK (resource::text = ANY (ARRAY['projects'::text, 'categories'::text, 'tags'::text]))");
    }
};
