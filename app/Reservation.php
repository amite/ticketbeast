<?php 

namespace App;

/**
* 
*/
class Reservation
{
    protected $tickets;

    function __construct($tickets)
    {
        $this->tickets = $tickets;
    }

    public function totalCost()
    {
        return $this->tickets->sum('price');
    }
}