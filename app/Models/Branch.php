<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'latitude', 'longitude', 'radius'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    // public function attendances()
    // {
    //     return $this->hasMany(Attendance::class);
    // }
}
