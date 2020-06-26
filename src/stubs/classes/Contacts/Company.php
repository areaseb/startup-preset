<?php

namespace App\Classes\Contacts;

use App\Classes\Primitive;

class Company extends Primitive
{
    public function setNazioneAttribute($value)
    {
        $this->attributes['nazione'] = $value;
        if($value == 'IT')
        {
            $this->attributes['lingua'] = strtolower($value);
        }
        else
        {
            $this->attributes['lingua'] = 'en';
        }
    }

    public function getAvatarAttribute()
    {
        $arr = explode(' ', $this->rag_soc);
        $letters = '';$count = 0;
        foreach($arr as $value)
        {
            if($count < 2)
            {
                $letters .= trim(strtoupper(substr($value, 0, 1)));
            }
            $count++;
        }
        if( strlen($letters) == 1)
        {
            $letters = trim(strtoupper(substr($arr[0], 0, 2)));
        }

        return '<div class="avatar">'.$letters.'</div>';
    }

    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }
}
