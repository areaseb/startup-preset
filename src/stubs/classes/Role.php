<?php

namespace App\Classes;

use App\Classes\Primitive;

class Role extends Primitive
{
    public function users()
    {
        return $this->belongsToMany(User::class,'role_user');
    }
}
