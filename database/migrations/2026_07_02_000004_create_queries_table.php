<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('queries', function (Blueprint $table) {
            $table->id('query_id');
            $table->foreignId('user_id')->constrained('users', 'user_id')->cascadeOnDelete()->cascadeOnUpdate();
            $table->text('query_text');
            $table->string('query_title');
            $table->enum('status', ['pending', 'answered', 'failed'])->default('pending');
            $table->unsignedInteger('response_time_ms')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('deleted_at')->nullable();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('queries');
    }
};
