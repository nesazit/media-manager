<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('original_name');
            $table->string('file_type', 50); // image, document, video, etc.
            $table->string('mime_type', 100);
            $table->string('extension', 10);
            $table->unsignedBigInteger('size'); // Size in bytes
            $table->string('disk', 50)->default('public');
            $table->string('path', 600);
            $table->foreignId('directory_id')->nullable()->constrained('directories')->onDelete('set null');
            $table->unsignedBigInteger('owner_id')->nullable(); // Can be admin_id or user_id
            $table->string('owner_type')->nullable(); // 'admin' or 'user'
            $table->json('metadata')->nullable(); // Image dimensions, EXIF data, etc.
            $table->json('permissions')->nullable(); // ACL permissions
            $table->boolean('is_public')->default(true);
            $table->text('description')->nullable();
            $table->string('alt_text')->nullable(); // For images
            $table->json('thumbnails')->nullable(); // Thumbnail paths
            $table->boolean('is_locked')->default(false);
            $table->timestamp('last_accessed_at')->nullable();
            $table->unsignedInteger('download_count')->default(0);
            $table->timestamps();

            $table->index(['directory_id']);
            $table->index(['owner_id', 'owner_type']);
            $table->index(['file_type']);
            $table->index(['mime_type']);
            $table->index(['disk']);
            $table->index(['created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
