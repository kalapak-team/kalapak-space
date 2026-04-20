<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('docs', function (Blueprint $table) {
            if (!Schema::hasColumn('docs', 'parent_id')) {
                $table->unsignedBigInteger('parent_id')->nullable()->after('author_id');
                $table->index('parent_id');
            }
        });

        // Add self-referential FK separately (safe to skip if already exists)
        try {
            Schema::table('docs', function (Blueprint $table) {
                $table->foreign('parent_id')->references('id')->on('docs')->nullOnDelete();
            });
        } catch (\Throwable $e) {
            // FK may already exist or parent_id was already there — ignore
        }
    }

    public function down(): void
    {
        Schema::table('docs', function (Blueprint $table) {
            if (Schema::hasColumn('docs', 'parent_id')) {
                $table->dropForeign(['parent_id']);
                $table->dropColumn('parent_id');
            }
        });
    }
};
