<?php 

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Carbon\Carbon;
use App\Concert;

class ConcertTest extends TestCase 
{
    use DatabaseMigrations;

    /** @test **/
    function can_get_formatted_date()
    {
      // Create a concert with a known date

      $concert = factory(Concert::class)->create([
        'date' => Carbon::parse('December 1, 2016 8:00pm'),
      ]);

      // Retrieve the formatted date
      $date = $concert->formatted_date;

      //  Verify the date is formatted as expected
      $this->assertEquals('December 1, 2016', $date);
    }
} 