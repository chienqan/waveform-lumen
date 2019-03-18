<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use mikehaertl\shellcommand\Command;
use Illuminate\Support\Facades\Storage;
use App\Facades\Binary;

class TransformController extends Controller
{
    /**
     * @var \stdClass
     *
     */
    protected $response;

    /**
     * TransformController constructor.
     *
     */
    public function __construct()
    {
        $this->response = new \stdClass();
    }

    /**
     * Process audio file into wave image
     * INPUT: audio file
     * OUTPUT: svg file
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \ImagickException
     */
    public function wav2png()
    {
        // Store upload file into local disk
        $inputFile = storage_path('app').'/input.wav';
        shell_exec("cp $inputFile /tmp/");

        // Validate wav file is exist or not
        if(!Storage::has('input.wav')) {
            $this->response->result = 0;
            $this->response->message = 'Can not find wav file in local disk';
            return response()->json($this->response);
        }

        // Convert wav into png
        $wav2png = new Command(Binary::path('wav2png/wav2png'));
        $wav2png->addArg('-w', '800');
        $wav2png->addArg('-h', '51');
        $wav2png->addArg('-a', Storage::path('input.png'));
        $wav2png->addArg(null, Storage::path('input.wav'));

        if(!$wav2png->execute()) {
            $this->response->result = 0;
            $this->response->message = 'wav2png is not working';
            return response()->json($this->response);
        }

        // Validate png is exist or not
        if(!Storage::has('input.png')) {
            $this->response->result = 0;
            $this->response->message = 'Can not find png file in local disk';
            return response()->json($this->response);
        }

        // Drop image and convert to black background
        $imagick = new Command(Binary::path('imagemagick/convert'));
        $imagick->addArg(null, Storage::path('input.png'));
        $imagick->addArg('-gravity', 'east');
        $imagick->addArg('-background', 'black');
        $imagick->addArg('-extent', '815x51');
        $imagick->addArg(null, Storage::path('imagick_input.png'));

        if(!$imagick->execute()) {
            $this->response->result = 0;
            $this->response->message = 'Image magic is not working';
            return response()->json($this->response);
        }

        // Validate png is exist or not
        if(!Storage::has($magickFile)) {
            $this->response->result = 0;
            $this->response->message = 'Can not find final file in local disk';
            return response()->json($this->response);
        }

        // Put the final result in s3
        try {
            Storage::cloud()->put($magickFile, Storage::get($magickFile));
        } catch (\Exception $exception) {
            $this->response->result = 0;
            $this->response->message = 'Can not put final file into s3';
            $this->response->errorMessage = $exception->getMessage();
            $this->response->errorTrace = $exception->getTraceAsString();
        }

        // Finally remove all file in /tmp folder
        shell_exec('rm -rf /tmp/*');

        // Return the png file
        $this->response->result = 1;
        $this->response->file = $magickFile;
        $this->response->link = Storage::cloud()->url($magickFile);
        return response()->json($this->response);
    }

    /**
     * Process
     */
    public function primitive()
    {
        // Validate png file is exist or not
        if(!Storage::has('input.png')) {
            $this->response->result = 0;
            $this->response->message = 'Can not find wav file in local disk';
            return response()->json($this->response);
        }

        // Convert image into geogramaphic image
        $primitive = new Command(Binary::path('primitive/primitive'));
        $primitive->addArg('-m', '8');
        $primitive->addArg('-n', '30');
        $primitive->addArg('-i', 'input.png');
        $primitive->addArg('-o', 'output.svg');

        // Check primitive is working or not
        if(!$primitive->execute()) {
            $this->response->result = 0;
            $this->response->message = 'Primitive is not working';
            return response()->json($this->response);
        }

        // Put the final result in s3
        Storage::cloud()->put('imagick_input.png', Storage::get('input.svg'));

        // Return the svg file
        $this->response->result = 1;
        $this->response->file = 'input.svg';
        $this->response->link = Storage::cloud()->url('input.svg');
        return response()->json($this->response);
    }
}
