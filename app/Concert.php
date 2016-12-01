<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Concert extends Model
{
    // stop whitelisting fields - FIX IT LATER
    protected $guarded = [];

    // cast back into carbon object
    protected $dates = ['date'];

    public function getFormattedDateAttribute()
    {
      return $this->date->format('F j, Y');
    }

    public function getFormattedStartTimeAttribute()
    {
      return $this->date->format('g:ia');
    }

    public function getTicketPriceInDollarsAttribute()
    {
      return number_format($this->ticket_price / 100, 2);
    }
}
