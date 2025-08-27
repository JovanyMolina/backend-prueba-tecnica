<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = ['project_id','title','description','priority','due_date','state'];
     public function project() { 
        return $this->belongsTo(Project::class); 
    }
    public function assignees() { 
        return $this->belongsToMany(User::class)->withTimestamps(); 
    }
    public function assignee() { 
        return $this->belongsTo(User::class, 'assigned_to'); 
    }
}
