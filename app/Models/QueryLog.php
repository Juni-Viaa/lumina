<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QueryLog extends Model
{
    use SoftDeletes, HasFactory;

    protected $table      = 'queries';
    protected $primaryKey = 'query_id';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const DELETED_AT = 'deleted_at';

    protected $fillable = [
        'user_id',
        'query_text',
        'query_title',       // FIX: DB column is query_title, not title
        'status',
        'response_time_ms',
    ];

    protected $casts = [
        'created_at'       => 'datetime',
        'updated_at'       => 'datetime',
        'deleted_at'       => 'datetime',
        'response_time_ms' => 'integer',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function answer(): HasOne
    {
        return $this->hasOne(Answer::class, 'query_id', 'query_id');
    }

    // ── Accessor — always use $query->display_title in views ──────────────────
    // Falls back to first 60 chars of query_text if title is empty.
    public function getDisplayTitleAttribute(): string
    {
        return $this->query_title
            ?? \Illuminate\Support\Str::limit($this->query_text, 60);
    }
}