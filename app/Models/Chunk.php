<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Chunk extends Model
{
    use SoftDeletes, HasFactory;

    // ── Table & primary key ────────────────────────────────────────────────────
    protected $table      = 'chunks';
    protected $primaryKey = 'chunk_id';

    // ── Timestamps ────────────────────────────────────────────────────────────
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const DELETED_AT = 'deleted_at';

    // ── Mass assignment ────────────────────────────────────────────────────────
    protected $fillable = [
        'document_id',
        'chunk_text',
    ];

    // ── Casts ─────────────────────────────────────────────────────────────────
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function document(): BelongsTo
    {
        return $this->belongsTo(Upload::class, 'document_id', 'document_id');
    }
}