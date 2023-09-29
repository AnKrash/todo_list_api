<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'status',
        'priority',
        'title',
        'description',
        'created_at',
        'completed_at',
        'user_id',
    ];

    // Relation to child tasks
    public function subtasks(): HasMany
    {
        return $this->hasMany(Subtask::class, 'task_id');
    }

    // Relation to the user
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function authorize(User $user): bool
    {
        return $this->user_id === $user->id;
    }

}
