<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessProductsCsv;
use App\Models\Products;
use Illuminate\Bus\Batch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Throwable;

class UploadController extends Controller
{
    public function index()
    {
        return view('main');
    }

    public function upload(Request $request) 
    {
        if ($request->has('import')) {
            $data = file($request->import);
            $chunks = array_chunk($data, 1000);

            $header = [];
            $batch = Bus::batch([])->dispatch();

            foreach ($chunks as $key => $chunk) {
                $data = array_map('str_getcsv', $chunk);

                if ($key === 0) {
                    $header = $data[0];

                    if ($header[0] !== "UNIQUE_KEY") {
                        // Remove any invalid or hidden characters from csv data
                        $header[0] = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $header[0]);
                    }
                    unset($data[0]);
                }

                $batch->add(new ProcessProductsCsv($data, $header));
            }

            return $batch;
        }
        return 'Please upload a file';
    }

    public function batch(Request $request, $batchID)
    {
        $batchID = $batchID;
        $batch = Bus::findBatch($batchID);

        return $batch;
    }
}
