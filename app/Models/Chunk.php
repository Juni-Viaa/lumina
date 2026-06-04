<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Chunk extends Model
{
    use SoftDeletes;

    // ── Table & primary key ────────────────────────────────────────────────────
    protected $table      = 'chunks';
    protected $primaryKey = 'chunk_id';

    // ── Timestamps ────────────────────────────────────────────────────────────
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'edited_at';
    const DELETED_AT = 'deleted_at';

    // ── Mass assignment ────────────────────────────────────────────────────────
    protected $fillable = [
        'document_id',
        'chunk_text',
    ];

    // ── Casts ─────────────────────────────────────────────────────────────────
    protected $casts = [
        'created_at' => 'datetime',
        'edited_at'  => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function document(): BelongsTo
    {
        return $this->belongsTo(Upload::class, 'document_id', 'document_id');
    }
}