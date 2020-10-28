<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Jacofda\Core\Models\{Calendar, Contact, Event};
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use Notifiable, HasRoles;

    protected $fillable = ['email', 'password'];
    protected $hidden = ['password', 'remember_token'];

    public function contact()
    {
        return $this->hasOne(Contact::class);
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function calendars()
    {
        return $this->hasMany(Calendar::class);
    }

    public function getDefaultCalendarAttribute()
    {
        return $this->calendars()->first();
    }

    public function getFullnameAttribute()
    {
        return $this->contact->fullname;
    }

    public function getUrlAttribute()
    {
        return url('users/'.$this->id);
    }


}
