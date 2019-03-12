<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DownloadController extends Controller
{
    /**
     * Start a process to download file
     *
     * @param $filename
     * @return \Illuminate\Http\JsonResponse
     */
    public function process(Request $request)
    {
        $response = new \stdClass();

        $filename = $request->query('filename');

        // Check file name query is have or not
        if(empty($filename)) {
            $response->result = 0;
            $response->message = 'The filename field is required';
            return response()->json($response);
        }

        // Check file is exist or not
        if(!Storage::has($filename)) {
            $response->result = 0;
            $response->message = 'This file is not exist in the system';
            return response()->json($response);
        }

        return Storage::download($filename);
    }
}
