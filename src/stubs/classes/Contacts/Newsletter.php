<?php

namespace App\Classes\Contacts;

use App\Classes\Primitive;
use App\Classes\Contacts\Template;

class Newsletter extends Primitive
{
    public function lists()
    {
        return $this->belongsToMany(NewsletterList::class, 'list_newsletter', 'list_id', 'newsletter_id');
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    public function getTemplateAttribute()
    {
        return Template::find($this->template_id);
    }

    public function addTrackingAndPersonalize($identifier, $contact)
    {
        $content = str_replace('%%%contact.fullname%%%', $contact->fullname, $this->template->contenuto);
        $unsub = 'href="'.config('app.url').'unsubscribe?r='.$identifier.'"';
        $content = str_replace('%%%unsubscribe%%%', $unsub, $content);
        return $content.'<img src="'.url('tracker?r='.$identifier).'"/>';
    }

}
