<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('answers', function (Blueprint $table) {
            $table->id('answer_id');
            $table->foreignId('query_id')->constrained('queries', 'query_id')->cascadeOnDelete()->cascadeOnUpdate();
            $table->text('answer_text');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('deleted_at')->nullable();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('answers');
    }
};
