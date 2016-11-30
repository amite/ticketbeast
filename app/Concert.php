<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Concert extends Model
{
    // stop whitelisting fields - FIX IT LATER
    protected $guarded = [];

    // cast back into carbon object
    protected $dates = ['date'];
}
