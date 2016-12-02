<?php

namespace App\Http\Controllers;

use App\Concert;
use App\Billing\PaymentGateway;
use Illuminate\Http\Request;

class ConcertOrdersController extends Controller
{
  private $paymentGateway;

  public function __construct(PaymentGateway $paymentGateway)
  {
    $this->paymentGateway = $paymentGateway;
  }

  public function store($concertId)
  {
    $this->validate(request(), [
      'email' => 'required'
    ]);
    
    $concert = Concert::find($concertId);
    
    // charging the customer with $amount and $token
    $this->paymentGateway->charge(request('ticket_quantity') * $concert->ticket_price, request('payment_token'));

    // creating the order
    $order = $concert->orderTickets(request('email'), request('ticket_quantity'));

    return response()->json([], 201);
  }
}
