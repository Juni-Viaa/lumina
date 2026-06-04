<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Answer extends Model
{
    use SoftDeletes;

    protected $table      = 'answers';
    protected $primaryKey = 'answer_id';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'edited_at';
    const DELETED_AT = 'deleted_at';

    protected $fillable = [
        'query_id',
        'answer_text',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'edited_at'  => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────
    // FIX: Renamed from query() → queryLog() to avoid conflict with
    // Eloquent's built-in static query() method (returns a QueryBuilder).
    public function queryLog(): BelongsTo
    {
        return $this->belongsTo(QueryLog::class, 'query_id', 'query_id');
    }
}