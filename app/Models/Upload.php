<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Upload extends Model
{
    use SoftDeletes, HasFactory;

    // ── Table & primary key ────────────────────────────────────────────────────
    protected $table      = 'documents';
    protected $primaryKey = 'document_id';

    // ── Timestamps ────────────────────────────────────────────────────────────
    // Laravel default CREATED_AT is already 'created_at' — stated explicitly
    // for clarity. UPDATED_AT maps to your 'updated_at' column.
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    // SoftDeletes reads this via static::DELETED_AT.
    // Must be plain `const` — `protected const` blocks trait access.
    // 'deleted_at' is now the standard Laravel name so this is optional,
    // but kept explicit for clarity.
    const DELETED_AT = 'deleted_at';

    // ── Mass assignment ────────────────────────────────────────────────────────
    protected $fillable = [
        'user_id',
        'document_name',
        'path_file',
        'file_type',
        'size',
        'status',
    ];

    // ── Casts ─────────────────────────────────────────────────────────────────
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'size'       => 'integer',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function chunks(): HasMany
    {
        return $this->hasMany(Chunk::class, 'document_id', 'document_id');
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    public function getSizeHumanAttribute(): string
    {
        $bytes = (int) $this->size;
        if ($bytes < 1024)    return $bytes . ' B';
        if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
        return round($bytes / 1048576, 1) . ' MB';
    }
}