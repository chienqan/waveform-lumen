<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Aws\S3\S3Client;

class S3Controller extends Controller
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
     * Generate presigned url to access into s3
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function presigned(Request $request)
    {
        $filename = $request->query('filename');

        // Check file name query is have or not
        if(empty($filename)) {
            $this->response->result = 0;
            $this->response->message = 'The filename field is required';
            return response()->json($this->response);
        }

        $config = [
            'region'  => env('AWS_DEFAULT_REGION'),
            'version' => 'latest',
            'credentials' => array(
                'key' => env('AWS_ACCESS_KEY'),
                'secret' => env('AWS_SECRET_KEY'),
            )
        ];

        $s3Client = new S3Client($config);
        $command = $s3Client->getCommand('PutObject', [
            'Bucket' => env('AWS_BUCKET'),
            'Key' => $filename,
        ]);

        $request = $s3Client->createPresignedRequest($command, '+20 minutes');
        $presignedUrl = (string) $request->getUri();

        $this->response->result = 1;
        $this->response->url = $presignedUrl;
        return response()->json($this->response);
    }
}
