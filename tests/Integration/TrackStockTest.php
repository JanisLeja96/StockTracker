<?php

namespace Tests\Integration;

use App\Models\Stock;
use App\Notifications\ImportantStockUpdate;
use App\UseCases\TrackStock;
use Database\Seeders\RetailerWithProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class TrackStockTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();

        $this->mockClientRequest(true, 24900);

        $this->seed(RetailerWithProductSeeder::class);

        (new TrackStock(Stock::first()))->handle();
    }


    public function testItNotifiesTheUser()
    {
        Notification::assertTimesSent(1, ImportantStockUpdate::class);
    }

    public function testItRefreshesTheLocalStock()
    {
        tap(Stock::first(), function ($stock) {
            $this->assertEquals(24900, $stock->price);
            $this->assertTrue($stock->in_stock);
        });
    }

    public function testItRecordsToHistory()
    {
        $this->assertEquals(1, History::count());
    }
}
