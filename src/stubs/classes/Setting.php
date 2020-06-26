<?php

namespace App\Classes;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $guarded = array();

    public $timestamps = false;

    protected $casts = [
        'fields' => 'array'
    ];

    public function getUrlAttribute()
    {
        return config('app.url').'settings/'.$this->id;
    }

    public function getCountFieldsAttribute()
    {
        if($this->fields)
        {
            return count($this->fields);
        }
        return 0;
    }

    public static function defaultTestEmail($emails = null)
    {
        if(is_null($emails))
        {
            $settings = self::where('model', 'Newsletter')->first()->fields;
            if($settings)
            {
                if(isset($settings['default_test_email']))
                {
                    return explode(';', $settings['default_test_email']);
                }
            }
            return null;
        }
        return explode(';', $emails);
    }

    public static function defaultSendFrom()
    {
        $settings = self::where('model', 'Newsletter')->first()->fields;
        if($settings)
        {
            if(isset($settings['invia_da_email']))
            {
                if(isset($settings['invia_da_nome']))
                {
                    return [
                        'name' => $settings['invia_da_nome'],
                        'address' => $settings['invia_da_email'],
                    ];
                }

                return [
                    'name' => config('app.name'),
                    'address' => $settings['invia_da_email'],
                ];
            }
        }
        return [
            'name' => config('app.name'),
            'address' => auth()-user()->email,
        ];
    }

}
