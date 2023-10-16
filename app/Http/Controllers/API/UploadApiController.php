<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessProductsCsv;
use Illuminate\Bus\BatchRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Database\QueryException;

class UploadApiController extends Controller
{
    /**
     * The job repository implementation.
     *
     * @var \Illuminate\Bus\BatchRepository
     */
    public $batches;

    public function __construct(BatchRepository $batches)
    {
        $this->batches = $batches;
    }

    public function upload(Request $request)
    {
        if ($request->has('file')) {
            foreach ($request->file('file') as $key => $file) {
                $data = file($file);
                $chunks = array_chunk($data, 1000);

                $header = [];
                $batch = Bus::batch([])->name($file->getClientOriginalName())->dispatch();

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

                return response()->json([
                    'job' => $batch
                ], 200);
            }
        }
        return 'Please upload a file';
    }

    public function batch(Request $request)
    {
        $batchID = $request->batchId;
        $batch = Bus::findBatch($batchID);

        return response()->json([
            'status' => $batch
        ], 200);
    }

    public function batches(Request $request)
    {
        try {
            $batches = $this->batches->get(50, $request->query('before_id') ?: null);
        } catch (QueryException $e) {
            $batches = [];
        }

        return [
            'batches' => $batches,
        ];
    }
}
