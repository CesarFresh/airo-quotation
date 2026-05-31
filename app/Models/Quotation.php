<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    protected $fillable = [
        'ages', 
        'currency_id', 
        'start_date',
        'end_date', 
        'trip_days', 
        'total',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'total'      => 'float',
    ];
}
