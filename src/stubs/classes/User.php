<?php

namespace App\Classes;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Classes\Contacts\Contact;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = ['email', 'password'];
    protected $hidden = ['password', 'remember_token'];

    //check if current user has role
    public function hasRole( ... $roles )
    {
		foreach ($roles as $role)
        {
			if ($this->roles->contains('slug', $role))
            {
				return true;
			}
		}
		return false;
	}

    public function roles()
    {
		return $this->belongsToMany(Role::class,'role_user');
	}


    public function contact()
    {
        return $this->hasOne(Contact::class);
    }

    public function getFullnameAttribute()
    {
        return $this->contact->fullname;
    }
}
