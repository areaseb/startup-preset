<?php

namespace App\Classes\Contacts;

use App\Classes\Primitive;

class NewsletterList extends Primitive
{
    protected $table = 'lists';

    //a list might have many Contact
    public function contacts()
    {
        return $this->belongsToMany(Contact::class, 'contact_list', 'list_id', 'contact_id');
    }

    public function getDirectoryAttribute()
    {
        return 'contacts/lists';
    }

    public function getCountContactsAttribute()
    {
        return $this->contacts()->count();
    }


}
