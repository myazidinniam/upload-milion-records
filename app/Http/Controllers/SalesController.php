<?php

namespace App\Http\Controllers;

use App\Models\Sales;
use Illuminate\Http\Request;
use App\Jobs\SalesCsvProccess;
use Illuminate\Support\Facades\Bus;

class SalesController extends Controller
{
    public function index()
    {
        return view('upload-file');
    }

    public function upload(Request $request)
    {
        if (request()->has('mycsv')) {
            $data = file(request()->mycsv);

            //Chuking fille
            //Convert 1000 records into new csv file
            $chunks = array_chunk($data, 1000);
            $header = [];
            $batch = Bus::batch([])->dispatch();
            foreach ($chunks as $key => $chunk) {
                $data = array_map('str_getcsv', $chunk);
                if ($key === 0) {
                    $header = $data[0];
                    unset($data[0]);
                }
                $batch->add(new SalesCsvProccess($data, $header));      
            }

            return Bus::findBatch($batch->id);
        }

        return 'please upload file';
    }

    public function batch()
    {
        $batchId = request('id');
        return Bus::findBatch($batchId);
    }
}
