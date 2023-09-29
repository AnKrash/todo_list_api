<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subtask extends Model
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
        'task_id',
        'parent_subtask_id', // Parent Subtask ID
    ];
    public function task()
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

    public function subtasks(): HasMany
    {
        return $this->hasMany(Subtask::class, 'parent_subtask_id');
    }

}
