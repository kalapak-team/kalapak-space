<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('docs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('content')->nullable(); // nullable — sections-based content replaces this
            $table->string('category')->default('General');
            $table->integer('order_num')->default(0);
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->foreignId('author_id')->constrained('users')->onDelete('cascade');
            $table->unsignedBigInteger('parent_id')->nullable(); // for subpages
            $table->timestamps();
            $table->softDeletes();

            $table->index('slug');
            $table->index('status');
            $table->index(['category', 'order_num']);
            $table->index('parent_id');
        });

        // Self-referential FK for parent/subpage hierarchy
        Schema::table('docs', function (Blueprint $table) {
            $table->foreign('parent_id')->references('id')->on('docs')->nullOnDelete();
        });

        Schema::create('doc_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doc_id')->constrained('docs')->cascadeOnDelete();
            $table->string('heading');
            $table->longText('content')->nullable();
            $table->integer('order_num')->default(0);
            $table->timestamps();

            $table->index(['doc_id', 'order_num']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doc_sections');
        Schema::table('docs', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
        });
        Schema::dropIfExists('docs');
    }
};
