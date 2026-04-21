<?php
// app/Models/Document.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Upload extends Model
{
    protected $fillable = ['name', 'path', 'type', 'size', 'status'];

    // Returns human-readable file size e.g. "1.2 MB"
    public function getSizeHumanAttribute(): string
    {
        $bytes = $this->size;
        if ($bytes < 1024) return $bytes . ' B';
        if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
        return round($bytes / 1048576, 1) . ' MB';
    }
}

/*
|--------------------------------------------------------------------------
| Migration: database/migrations/xxxx_create_documents_table.php
|--------------------------------------------------------------------------
*/

// Schema::create('documents', function (Blueprint $table) {
//     $table->id();
//     $table->string('name');
//     $table->string('path');
//     $table->string('type', 10);
//     $table->unsignedBigInteger('size')->default(0);
//     $table->enum('status', ['processing', 'indexed', 'failed'])->default('processing');
//     $table->timestamps();
// });

/*
|--------------------------------------------------------------------------
| Migration: database/migrations/xxxx_create_query_logs_table.php
|--------------------------------------------------------------------------
*/

// Schema::create('query_logs', function (Blueprint $table) {
//     $table->id();
//     $table->text('question');
//     $table->longText('answer')->nullable();
//     $table->unsignedInteger('response_time_ms')->default(0);
//     $table->timestamps();
// });
