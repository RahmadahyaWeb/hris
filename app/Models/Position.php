<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    protected $fillable = [
        'division_id',
        'title',
        'parent_id',
    ];

    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    public function parent()
    {
        return $this->belongsTo(Position::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Position::class, 'parent_id');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
