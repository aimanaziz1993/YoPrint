<?php

namespace App\Jobs;

use App\Models\Products;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ProcessProductsCsv implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $data;
    public $header;

    /**
     * Create a new job instance.
     */
    public function __construct($data, $header)
    {
        $this->onConnection('redis');
        $this->data = $data;
        $this->header = $header;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        
        foreach ($this->data as $product) {
            
            $productData = array_combine($this->header, $product);

            Products::updateOrCreate([
                'UNIQUE_KEY' => $productData['UNIQUE_KEY'],
            ], [
                'PRODUCT_TITLE' => $productData['PRODUCT_TITLE'], 
                'PRODUCT_DESCRIPTION' => $productData['PRODUCT_DESCRIPTION'], 
                'STYLE#' => $productData['STYLE#'], 
                'SANMAR_MAINFRAME_COLOR' => $productData['SANMAR_MAINFRAME_COLOR'], 
                'SIZE' => $productData['SIZE'], 
                'COLOR_NAME' => $productData['COLOR_NAME'], 
                'PIECE_PRICE' => $productData['PIECE_PRICE']
            ]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $exception): void
    {
        // Send user notification of failure, etc...
    }
}
