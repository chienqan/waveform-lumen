<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use mikehaertl\shellcommand\Command;
use Illuminate\Support\Facades\Storage;

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

        // Validate mp3 file is exist or not
        if(!Storage::has('input.mp3')) {
            $this->response->result = 0;
            $this->response->message = 'Can not find mp3 file in local disk';
            return response()->json($this->response);
        }

        // Convert mp3 into wav
        $ffmpeg = \FFMpeg\FFMpeg::create();
        $mp3 = $ffmpeg->open(Storage::path('input.mp3'));
        $mp3->save(new \FFMpeg\Format\Audio\Wav(), Storage::path('input.wav'));

        // Validate wav file is exist or not
        if(!Storage::has('input.wav')) {
            $this->response->result = 0;
            $this->response->message = 'Can not find wav file in local disk';
            return response()->json($this->response);
        }

        // Convert wav into png
        $command = new Command('python /Users/chien/wav2png/wav2png.py');
        $command->addArg('-w', '800');
        $command->addArg('-h', '51');
        $command->addArg('-a', Storage::path('input.png'));
        $command->addArg(null, Storage::path('input.wav'));

        if(!$command->execute()) {
            $this->response->result = 0;
            $this->response->message = $command->getError();
            return response()->json($this->response);
        }

        // Validate png is exist or not
        if(!Storage::has('input.png')) {
            $this->response->result = 0;
            $this->response->message = 'Can not find png file in local disk';
            return response()->json($this->response);
        }

        // Drop image and convert to black background
        $image = new \Imagick(Storage::path('input.png'));
        $image->setImageGravity(\Imagick::GRAVITY_EAST);
        $image->setImageBackgroundColor('black');
        $image->extentImage(815, 51, 0, 0);

        // Save file when imagick has processed it
        Storage::put('imagick_input.png', $image);

        // Return the png file
        $this->response->result = 1;
        $this->response->file = 'imagick_input.png';
        $this->response->message = 'Successfully';
        return response()->json($this->response);
    }

    /**
     * Process
     */
    public function primitive()
    {

    }
}
