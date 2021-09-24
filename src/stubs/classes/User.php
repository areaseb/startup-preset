<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Areaseb\Core\Models\{Calendar, Contact, Event, Notification};
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

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function testimonial()
    {
        if(\Illuminate\Support\Facades\Schema::hasTable('testimonials'))
        {
            return $this->hasOne(\Areaseb\Referrals\Models\Testimonial::class);
        }
        return false;
    }

    public function agent()
    {
        if(\Illuminate\Support\Facades\Schema::hasTable('agents'))
        {
            return $this->hasOne(\Areaseb\Agents\Models\Agent::class);
        }
        return false;
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
