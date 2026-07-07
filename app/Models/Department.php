<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    protected $fillable = ['name', 'code', 'total_semesters'];

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function faculties(): HasMany
    {
        return $this->hasMany(Faculty::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }
}
