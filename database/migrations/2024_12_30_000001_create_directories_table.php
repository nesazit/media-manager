<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('directories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('path', 600);
            $table->string('disk', 50)->default('public');
            $table->foreignId('parent_id')->nullable()->constrained('directories')->onDelete('cascade');
            $table->unsignedBigInteger('owner_id')->nullable(); // Can be admin_id or user_id
            $table->string('owner_type')->nullable(); // 'admin' or 'user'
            $table->json('permissions')->nullable(); // ACL permissions
            $table->boolean('is_public')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['parent_id']);
            $table->index(['owner_id', 'owner_type']);
            $table->index(['disk']);
            $table->unique(['name', 'parent_id', 'disk']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('directories');
    }
};
