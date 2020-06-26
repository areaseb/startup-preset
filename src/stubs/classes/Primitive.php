<?php

namespace App\Classes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;


class Primitive extends Model
{
    protected $guarded = array();


    public function media()
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    public function notifications()
    {
        return $this->morphMany(Notification::class, 'notificationable');
    }


    //get class name
    public function getClassAttribute()
    {
        $arr = explode("\\", get_class($this));
        return end($arr);
    }

    //autogenerate slug and storage folder name from class
    public function getDirectoryAttribute()
    {
        return str_plural(strtolower($this->class));
    }

    //get url of element
    public function getUrlAttribute()
    {
        return config('app.url') . $this->directory . '/' . $this->id;
    }

    //check if model has column in table
    public function scopeHasColumn($query, $column_name)
    {
        return Schema::connection('mysql')->hasColumn($query->getQuery()->from, $column_name);
    }


    public function scopeNation($query, $field)
    {
        if($query->hasColumn('nazione'))
        {
            return $query->where('nazione', $field);
        }
        return $query;
    }


    public function scopeTipo($query, $field)
    {
        if($query->hasColumn('tipo'))
        {
            return $query->where('tipo', $field);
        }
        return $query;
    }


    public function scopeRegion($query, $search)
    {
        if($query->hasColumn('city_id'))
        {
            return $query->whereHas('city',
                function($q) use($search){
                    $q->where('regione',$search);
                });
        }
        else
        {
            return $query;
        }
    }

    public function scopeProvince($query, $search)
    {
        if($query->hasColumn('city_id'))
        {
            return $query->whereHas('city',
                function($q) use($search){
                    $q->where('provincia',$search);
                });
        }
        else
        {
            return $query;
        }
    }

    public function scopeUpdated($query, $days)
    {
        return $query->whereDate('updated_at', '>=', Carbon::today()->subDays( $days ) );
    }

    public function scopeCreated($query, $days)
    {
        return $query->whereDate('created_at', '>=', Carbon::today()->subDays( $days ) );
    }




}
