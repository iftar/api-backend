<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Charity extends Model
{
    //
    protected $table = 'charities';

    protected $fillable = [
        'name',
        'charity_registration_number',
        'max_delivery_capacity',
    ];
}
