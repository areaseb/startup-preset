<?php

namespace App\Classes;

use App\Classes\Primitive;
use Illuminate\Support\Facades\Cache;

class City extends Primitive
{
    //list all provinces
    static public function uniqueProvinces($region = null)
    {
        if(is_null($region))
        {
            $provinces = Cache::remember('provinces', 60*24*7, function () {
                $arr[''] = '';
                foreach (self::select('provincia')->where('italia', 1)->distinct()->orderBy('provincia', 'ASC')->get() as $value)
                {
                    $arr[$value->provincia] = $value->provincia;
                }
                return $arr;
            });
        }
        else
        {
            $provinces[''] = '';

            foreach (self::select('provincia')->where('regione', $region)->distinct()->orderBy('provincia', 'ASC')->get() as $value)
            {
                $provinces[$value->provincia] = $value->provincia;
            }

        }

        return $provinces;
    }

    //list all provinces
    static public function uniqueRegions()
    {
        $regions = Cache::remember('regions', 60*24*7, function () {
            $arr[''] = '';
            foreach (self::select('regione')->where('italia', 1)->distinct()->orderBy('regione', 'ASC')->get() as $value)
            {
                $arr[$value->regione] = $value->regione;
            }
            $arr['Estero'] = 'Estero';
            return $arr;
        });

        return $regions;
    }

    public static function getCityIdFromData($provincia, $nazione)
    {

        $city = self::where('comune', $provincia)->first();
        if($city)
        {
            return $city->id;
        }
        elseif(self::where('provincia', $provincia)->exists())
        {
            return  self::where('provincia', $provincia)->first()->id;
        }

        return self::where('sigla_provincia', $nazione)->where('italia',0)->first()->id;
    }


}
