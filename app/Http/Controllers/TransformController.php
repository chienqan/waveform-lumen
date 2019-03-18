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
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function wav2png(Request $request)
    {
        // Check file is uploaded or not
        if(!$request->hasFile('media')) {
            $this->response->result = 0;
            $this->response->message = 'Uploaded file is required';
            return response()->json($this->response);
        }

        // Check file is valid or not
        if(!$request->file('media')->isValid()) {
            $this->response->result = 0;
            $this->response->message = 'Uploaded file is not valid';
            return response()->json($this->response);
        }

        // Check extension of the file
        if(!$request->file('media')->extension() !== 'mp3') {
            $this->response->result = 0;
            $this->response->message = 'Uploaded file must be mp3 file';
            return response()->json($this->response);
        }

        $fileName = $request->file('media')->getFilename();

        // Store upload file into local disk
        $mp3File = "$fileName-".time().".mp3";
        try {
            Storage::put($mp3File, $request->file('media'));
        } catch (\Exception $exception) {
            $this->response->result = 0;
            $this->response->message = 'Can not put uploaded file into server';
            $this->response->errorMessage = $exception->getMessage();
            $this->response->errorTrace = $exception->getTraceAsString();
        }

        // Valide mp3 file is exist or not
        if(!Storage::has($mp3File)) {
            $this->response->result = 0;
            $this->response->message = 'Can not find mp3 file in local disk';
            return response()->json($this->response);
        }

        // Convert mp3 into wav
        $wavFile = "$fileName-".time().".wav";
        $ffmpeg = new Command(Binary::path('ffmpeg/ffmpeg'));
        $ffmpeg->addArg('-i', Storage::path($mp3File));
        $ffmpeg->addArg(null, Storage::path($wavFile));


        // Validate wav file is exist or not
        if(!Storage::has($wavFile)) {
            $this->response->result = 0;
            $this->response->message = 'Can not find wav file in local disk';
            return response()->json($this->response);
        }

        // Convert wav into png
        $pngFile = "$fileName-".time().".png";
        $wav2png = new Command(Binary::path('wav2png/wav2png'));
        $wav2png->addArg('-w', '800');
        $wav2png->addArg('-h', '51');
        $wav2png->addArg('-a', Storage::path($pngFile));
        $wav2png->addArg(null, Storage::path($wavFile));

        // Check wav2png is execute error or not
        if(!$wav2png->execute()) {
            $this->response->result = 0;
            $this->response->message = 'wav2png is not working';
            return response()->json($this->response);
        }

        // Validate png is exist or not
        if(!Storage::has($pngFile)) {
            $this->response->result = 0;
            $this->response->message = 'Can not find png file in local disk';
            return response()->json($this->response);
        }

        // Drop image and convert to black background
        $magickFile = "$fileName-".time().".png";
        $imagick = new Command(Binary::path('imagemagick/convert'));
        $imagick->addArg(null, Storage::path($pngFile));
        $imagick->addArg('-gravity', 'east');
        $imagick->addArg('-background', 'black');
        $imagick->addArg('-extent', '815x51');
        $imagick->addArg(null, Storage::path($magickFile));

        // Check image magick is execute error or not
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
