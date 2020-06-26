<?php

namespace App\Classes;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $guarded = array();

    public function notificationable()
    {
        return $this->morphTo();
    }

    public function getDirectoryAttribute()
    {
        return str_plural(strtolower($this->class));
    }

    //get url of element
    public function getUrlAttribute()
    {
        return config('app.url') . 'notifications/' . $this->id;
    }

    public function scopeUnread($query)
    {
        $query = $query->where('read', 0);
    }

    public static function countUnread()
    {
        return self::unread()->count();
    }
}
