<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chunks', function (Blueprint $table) {
            $table->id('chunk_id');
            $table->foreignId('document_id')->constrained('documents', 'document_id')->cascadeOnDelete()->cascadeOnUpdate();
            $table->text('chunk_text');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate()->useCurrent();
            $table->timestamp('deleted_at')->nullable();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('chunks');
    }
};
