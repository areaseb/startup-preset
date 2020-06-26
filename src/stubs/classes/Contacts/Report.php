<?php

namespace App\Classes\Contacts;

use App\Classes\Primitive;

class Report extends Primitive
{
    protected $guarded = array();
    protected $casts = [
        'opened_at' => 'datetime:Y-m-d H:i:s',
    ];

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function newsletter()
    {
        return $this->belongsTo(Newsletter::class);
    }

    public function scopeInviate($query)
    {
        $query = $query->where('delivered', true);
    }

    public function scopeErrore($query)
    {
        $query = $query->where('error', true);
    }

    public function scopeAperte($query)
    {
        $query = $query->where('opened', true);
    }

    public function scopeUnsubscribed($query)
    {
        $query = $query->where('unsubscribed', true);
    }


    public static function identify($value)
    {
        return self::where('identifier', $value)->first();
    }

    public static function stats($newsletter)
    {
        $tot = 0;
        foreach ($newsletter->lists as $list)
        {
            $tot += $list->count_contacts;
        }

        $data = [
            'inizio' => self::where('newsletter_id', $newsletter->id)->orderBy('id', 'ASC')->first(),
            'fine' => self::where('newsletter_id', $newsletter->id)->orderBy('id', 'DESC')->first(),
            'totali' => $tot,
            'inviate' => self::where('newsletter_id', $newsletter->id)->inviate()->count(),
            'aperte' => self::where('newsletter_id', $newsletter->id)->aperte()->count(),
            'errore' => self::where('newsletter_id', $newsletter->id)->errore()->count(),
            'unsubscribed' => self::where('newsletter_id', $newsletter->id)->unsubscribed()->count(),
        ];

        return (object)$data;
    }


}
