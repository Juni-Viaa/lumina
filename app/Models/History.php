<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class History extends Model
{
    use HasFactory;
    
    protected $table      = 'history';
    protected $primaryKey = 'history_id';

    // History is append-only — no updates, no soft deletes needed
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'query_id',
        'answer_id',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────
    // FIX: Renamed from query() → queryLog() to avoid conflict with
    // Eloquent's built-in static query() method (returns a QueryBuilder).
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function queryLog(): BelongsTo
    {
        return $this->belongsTo(QueryLog::class, 'query_id', 'query_id');
    }

    public function answer(): BelongsTo
    {
        return $this->belongsTo(Answer::class, 'answer_id', 'answer_id');
    }
}