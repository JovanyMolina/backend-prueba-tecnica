<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    
    protected $fillable = ['name', 'description', 'start_date', 'end_date', 'status'];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public function collaborators()
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }
    public function tasks()
    {
        return $this->hasMany(\App\Models\Task::class);
    }

}
