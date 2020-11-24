<?php


namespace App\UseCases;


use App\Clients\StockStatus;
use App\Models\History;
use App\Models\Stock;
use App\Models\User;
use App\Notifications\ImportantStockUpdate;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class TrackStock implements ShouldQueue
{
    use Dispatchable;

    public Stock $stock;
    public StockStatus $status;

    public function __construct(Stock $stock)
    {
        $this->stock = $stock;
    }

    public function handle()
    {
        $this->checkAvailability();
        $this->notifyUser();
        $this->refreshStock();
        $this->recordToHistory();
    }

    public function checkAvailability()
    {
        $this->status = $this->stock->retailer
            ->client()
            ->checkAvailability($this->stock);
    }

    public function notifyUser()
    {
        if ($this->isNowInStock()) {
            User::first()->notify(
                new ImportantStockUpdate($this->stock)
            );
        }
    }

    public function refreshStock()
    {
        $this->stock->update([
            'in_stock' => $this->status->available,
            'price' => $this->status->available
        ]);
    }

    public function recordToHistory()
    {
        History::create([
            'price' => $this->stock->price,
            'stock_id' => $this->stock->id,
            'product_id' => $this->stock->product_id,
            'in_stock' => $this->stock->in_stock
        ]);
    }

    public function isNowInStock(): bool
    {
        return !$this->stock->in_stock && $this->status->available;
    }
}
