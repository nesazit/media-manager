<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('file_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('file_id')->constrained('files')->onDelete('cascade');
            $table->unsignedBigInteger('shared_with_id')->nullable(); // user_id or admin_id
            $table->string('shared_with_type')->nullable(); // 'user' or 'admin'
            $table->enum('permission', ['read', 'write', 'delete'])->default('read');
            $table->unsignedBigInteger('shared_by_id');
            $table->string('shared_by_type'); // 'user' or 'admin'
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['file_id']);
            $table->index(['shared_with_id', 'shared_with_type']);
            $table->index(['shared_by_id', 'shared_by_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('file_shares');
    }
};
