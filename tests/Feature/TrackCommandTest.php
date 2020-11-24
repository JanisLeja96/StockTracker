<?php

namespace Tests\Feature;

use App\Clients\StockStatus;
use App\Models\Product;
use App\Models\User;
use App\Notifications\ImportantStockUpdate;
use Database\Seeders\RetailerWithProductSeeder;
use Facades\App\Clients\ClientFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class TrackCommandTest extends TestCase
{

    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();

        $this->seed(RetailerWithProductSeeder::class);
    }


    /**
     * @test
     */
    public function it_tracks_product_stock()
    {
        $this->assertFalse(Product::first()->inStock());

        $this->mockClientRequest();

        $this->artisan('track');

        $this->assertTrue(Product::first()->inStock());
    }

    public function testItNotifiesTheUserWhenTheStockIsNowAvailable()
    {
        $this->mockClientRequest();

        $this->artisan('track');

        Notification::assertSentTo(User::first(), ImportantStockUpdate::class);
    }

    public function testItDoesNotNotifyTheUserWhenTheStockRemainsUnavailable()
    {
        $this->mockClientRequest(false);

        $this->artisan('track');

        Notification::assertNothingSent();
    }
}
