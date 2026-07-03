<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{
    public function up():void
    {
        Schema::create('documents', function(Blueprint $table){
            $table->id('document_id');
            $table->foreignId('user_id')->constrained('users','user_id');
            $table->string('document_name');
            $table->string('path_file',500);
            $table->string('file_type',20);
            $table->unsignedBigInteger('size');
            $table->enum('status',['processing','indexed','failed'])->default('processing');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate()->useCurrent();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    public function down():void
    {
        Schema::dropIfExists('documents');
    }
};