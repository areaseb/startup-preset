<?php

namespace App\Classes;

use App\Classes\Primitive;
use Illuminate\Support\Facades\Cache;

class Country extends Primitive
{
    static public function listCountries()
    {
         $countries = Cache::remember('countries', 60*24*7, function () {
             return array_merge(
                 ['IT' => 'Italia'],
                 self::eu()->pluck('name', 'iso2')->toArray(),
                 self::world()->pluck('name', 'iso2')->toArray()
              );
         });

         return $countries;

    }

    public function scopeEu($query)
    {
        return $query->where('iso2', '!=', 'IT')->where('is_eu', 1);
    }

    public function scopeWorld($query)
    {
        return $query->where('is_eu', 0);
    }

    public static function getCountryPhone($code)
    {
        if(self::where('iso2', $code)->exists())
        {
            return self::where('iso2', $code)->first()->phone_code;
        }
        return null;
    }


}
