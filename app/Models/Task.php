<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    public function subtasks()
    {
        return $this->hasMany(Subtask::class, 'task_id');
    }

    // Relation to the user
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
