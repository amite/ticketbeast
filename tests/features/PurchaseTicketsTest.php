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

    /** @test **/
    function customer_can_purchase_concert_tickets()
    {
      // Arrange
      // create a concert
      $concert = factory(Concert::class)->create(['ticket_price' => 3250]);

      // Act
      // Purchase Concert Tickets
      $this->json('POST', "/concerts/{$concert->id}/orders", [
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
    function email_is_required_to_purchase_tickets()
    {

      $concert = factory(Concert::class)->create();

      // Act
      // Purchase Concert Tickets
      $this->json('POST', "/concerts/{$concert->id}/orders", [
        'ticket_quantity' => 3,
        'payment_token' => $this->paymentGateway->getValidTestToken()
      ]);

      // Assert
      $this->assertResponseStatus(422);
      $this->assertArrayHasKey('email', $this->decodeResponseJson());

    }

}
