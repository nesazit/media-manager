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
            $table->string('file_type', 50);
            $table->string('mime_type', 100);
            $table->string('extension', 10);
            $table->unsignedBigInteger('size');
            $table->tinyInteger('disk')->default(0);
            $table->string('path', 600);
            $table->foreignId('directory_id')->nullable()->constrained('directories')->onDelete('set null');
            $table->unsignedBigInteger('owner_id')->nullable(); // Can be admin_id or user_id
            $table->string('owner_type')->nullable(); // 'admin' or 'user'
            $table->json('metadata')->nullable(); // Image dimensions, EXIF data, etc.
            $table->boolean('is_public')->default(true);
            $table->text('description')->nullable();
            $table->string('alt_text')->nullable(); // For images
            $table->timestamps();


            $table->id();
            $table->foreignId('directory_id')->nullable()->constrained('directories');
            $table->foreignId('admin_id')->nullable()->constrained('admins');
            $table->string('name');
            $table->string('type');
            $table->integer('size');
            $table->unsignedBigInteger('fileable_id');
            $table->string('fileable_type');
            $table->boolean('locked')->default(false);
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
