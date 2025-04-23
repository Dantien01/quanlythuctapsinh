<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Intern extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'student_code', 'phone', 'address', 'faculty',
        'major', 'academic_year', 'skills', 'cv_path'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function evaluations()
    {
        return $this->hasMany(Evaluation::class);
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }
}