<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SampleController extends Controller
{
    /**
     * Show demo how to use wav2png
     *
     * @return \Illuminate\View\View
     */
    public function wav2png()
    {
        return view('sample/wav2png');
    }

    /**
     * Show demo how to use primitive
     *
     * @return \Illuminate\View\View
     */
    public function primitive()
    {
        return view('sample/primitive');
    }
}
