<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use DateTimeInterface;

abstract class BaseModel extends Model
{
    
    protected function serializeDate(DateTimeInterface $date): string
    {
        $tz = config('app.timezone', 'Asia/Jakarta');

        return Carbon::instance($date)
            ->setTimezone($tz)
            ->format('Y-m-d H:i:s'); 
        // kalau mau ISO 8601 dengan offset: ->format('Y-m-d\TH:i:sP');
    }
}
