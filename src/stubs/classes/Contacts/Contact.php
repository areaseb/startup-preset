<?php

namespace App\Classes\Contacts;

use App\Classes\{City, Primitive};
use App\Classes\Contacts\{Company, Report, NewsletterList};
use Carbon\Carbon;

class Contact extends Primitive
{

//ELOQUENT
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function lists()
    {
        return $this->belongsToMany(NewsletterList::class, 'contact_list', 'contact_id', 'list_id');
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }

// GETTERS AND SETTERs
    public function getFullnameAttribute()
    {
        return $this->nome . ' ' . $this->cognome;
    }

    public function getRagSocAttribute()
    {
        if($this->company_id)
        {
            return $this->company->rag_soc;
        }
        return null;
    }

    public function setNomeAttribute($value)
    {
        $this->attributes['nome'] = ucwords(strtolower($value));
    }

    public function setCognomeAttribute($value)
    {
        $this->attributes['cognome'] = ucwords(strtolower($value));
    }

    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = strtolower($value);
    }


//SCOPES & FILTERS
    public function scopeUser($query)
    {
        return $query->whereNotNull('user_id');
    }

    public function scopeSubscribed($query)
    {
        return $query->where('subscribed', 1);
    }


    public function scopeBelongToList($query, $list_id)
    {
        $contactIds = NewsletterList::find($list_id)->contacts()->pluck('contact_id')->toArray();
        return $query = $query->whereIn('id', $contactIds );
    }

    public function getAvatarAttribute()
    {
        $arr = explode(' ', $this->fullname);
        $letters = '';$count = 0;
        foreach($arr as $value)
        {
            if($count < 2)
            {
                $letters .= trim(strtoupper(substr($value, 0, 1)));
            }
            $count++;
        }
        if( strlen($letters) == 1)
        {
            $letters = trim(strtoupper(substr($arr[0], 0, 2)));
        }

        return '<div class="avatar">'.$letters.'</div>';
    }


    public function getNewsletterInviateAttribute()
    {
        if($this->reports()->exists())
        {
            return $this->reports()->inviate()->count();
        }
        return 0;
    }

    public function getNewsletterAperteAttribute()
    {
        if($this->reports()->exists())
        {
            return $this->reports()->aperte()->count();
        }
        return 0;
    }


    public function getNewsletterStatsAttribute()
    {
        return (object)['inviate' => $this->reports()->inviate()->count(), 'aperte' => $this->reports()->aperte()->count()];
    }




    public static function filter($data)
    {
        $query = self::with('city');

        if($data->get('search'))
        {
            $like = '%'.$data['search'].'%';
            $query = $query->where('nome', 'like', $like )
                            ->orWhere('cognome', 'like', $like )
                            ->orWhere('email', 'like', $like )
                            ->orWhere('citta', 'like', $like );

        }


        if($data->get('region'))
        {
            $query = $query->region( $data['region'] );
        }
        
        if($data->get('province'))
        {
            $query = $query->province( $data['province'] );
        }

        if($data->get('created_at'))
        {
            $query = $query->created( $data['created_at'] );
        }

        if($data->get('updated_at'))
        {
            $query = $query->updated( $data['updated_at'] );
        }

        if($data->get('tipo'))
        {
            $query = $query->tipo( $data['tipo'] );
        }

        if($data->get('list'))
        {
            $query = $query->belongToList( $data['list'] );
        }
        if($data->get('sort'))
        {
            $arr = explode('|', $data->sort);
            $field = $arr[0];
            $value = $arr[1];
            $query = $query->orderBy($field, $value);
        }

        return $query;
    }



    public static function createOrUpdate($contact, $data, $user_id = null)
    {
        if(is_null($user_id))
        {
            $user_id = $data['user_id'];
        }
        $contact->nome = $data['nome'];
        $contact->cognome = $data['cognome'];
        $contact->cellulare = $data['cellulare'];
        $contact->nazione = $data['nazione'];
        $contact->email = $data['email'];
        $contact->indirizzo = $data['indirizzo'];
        $contact->cap = $data['cap'];
        $contact->citta = $data['citta'];
        $contact->provincia = $data['provincia'];
        $contact->city_id = City::getCityIdFromData($data['provincia'], $data['nazione']);
        $contact->nazione = $data['nazione'];
        if($data['nazione'] != 'IT')
        {
            $contact->lingua = 'en';
        }
        $contact->user_id = $user_id;
        $contact->company_id = $data['company_id'];
        $contact->save();

        return $contact;
    }








}
