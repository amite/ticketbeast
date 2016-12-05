<?php 

use App\Concert;
use App\Billing\FakePaymentGateway;
use App\Billing\PaymentGateway;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PurchaseTicketsTest extends TestCase 
{
    use DatabaseMigrations;

    protected $paymentGateway;

    protected function setup() {
      parent::setup();
      $this->paymentGateway = new FakePaymentGateway;
      $this->app->instance(PaymentGateway::class,  $this->paymentGateway);
    }


    private function orderTickets($concert, $params)
    {
        $this->json('POST', "/concerts/{$concert->id}/orders", $params);
    }

    private function assertValidationError($field)
    {
        $this->assertResponseStatus(422);
        $this->assertArrayHasKey($field, $this->decodeResponseJson());
    }

    /** @test **/
    function customer_can_purchase_concert_tickets()
    {
      // Arrange
      // create a concert
      $concert = factory(Concert::class)->create(['ticket_price' => 3250]);

      // Act
      // Purchase Concert Tickets

      $this->orderTickets($concert, [
        'email' => 'john@example.com',
        'ticket_quantity' => 3,
        'payment_token' => $this->paymentGateway->getValidTestToken()
      ]);

      // Assert
      $this->assertResponseStatus(201);

      // Assert
      // Make sure the customer was charged the right amount
      $this->assertEquals(9750, $this->paymentGateway->totalCharges());

      // Make sure an order exists for this customer
      $order = $concert->orders()->where('email', 'john@example.com')->first();
      $this->assertNotNull($order);
      $this->assertEquals(3, $order->tickets()->count());
    }

    /** @test **/
    function an_order_is_not_created_if_payment_fails()
    {
      $this->disableExceptionHandling();
      $concert = factory(Concert::class)->create(['ticket_price' => 3250]);

      $this->orderTickets($concert, [
        'email' => 'john@example.com',
        'ticket_quantity' => 3,
        'payment_token' => 'invalid-payment-token'
      ]);
      
      $this->assertResponseStatus(422);
      $order = $concert->orders()->where('email', 'john@example.com')->first();
      $this->assertNull($order);
    }

    /** @test **/
    function email_is_required_to_purchase_tickets()
    {

      $concert = factory(Concert::class)->create();

      // Act
      // Purchase Concert Tickets
      $this->orderTickets($concert, [
        'ticket_quantity' => 3,
        'payment_token' => $this->paymentGateway->getValidTestToken()
      ]);

      // Assert
      $this->assertValidationError('email');

    }

    /** @test */
    function email_must_be_valid_to_purchase_tickets()
    {
        $concert = factory(Concert::class)->create();
        $this->orderTickets($concert, [
            'email' => 'not-an-email-address',
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);
        $this->assertValidationError('email');
    }


    /** @test */
    function ticket_quantity_is_required_to_purchase_tickets()
    {
        $concert = factory(Concert::class)->create();
        $this->orderTickets($concert, [
            'email' => 'john@example.com',
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);
        $this->assertValidationError('ticket_quantity');
    }


    /** @test */
    function ticket_quantity_must_be_at_least_1_to_purchase_tickets()
    {
        $concert = factory(Concert::class)->create();
        $this->orderTickets($concert, [
            'email' => 'john@example.com',
            'ticket_quantity' => 0,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);
        $this->assertValidationError('ticket_quantity');
    }


    /** @test */
    function payment_token_is_required()
    {
        $concert = factory(Concert::class)->create();
        $this->orderTickets($concert, [
            'email' => 'john@example.com',
            'ticket_quantity' => 3,
        ]);
        $this->assertValidationError('payment_token');
    }

    /** @test **/
    function order_is_not_created_if_payment_fails()
    {
        $concert = factory(Concert::class)->create(['ticket_price' => 3250]);
        
        $this->orderTickets($concert, [
            'email' => 'john@example.com',
            'ticket_quantity' => 0,
            'payment_token' => 'invalid-payment-token',
        ]);

        $this->assertResponseStatus(422);
        $order = $concert->orders()->where('email', 'john@example.com')->first();
        $this->assertNull($order);
    }

}
