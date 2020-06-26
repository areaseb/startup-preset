<?php

namespace App\Classes\Contacts;

use App\Classes\Primitive;

class Template extends Primitive
{

    public function setContenutoAttribute($value)
    {
        $start = strpos($value, "<table");
        $end = strrpos($value, "table>")+6;
        $this->attributes['contenuto'] = substr($value, $start, ($end-$start));
    }

    public function getBuilderAttribute()
    {
        return url('template-builder/'.$this->id);
    }

}
