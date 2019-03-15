<?php namespace App\Services\Files;

use App\Upload;
use GuzzleHttp\Client;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Http\UploadedFile;

class FileDownloader {

    /**
     * Upload model.
     *
     * @var Upload
     */
    private $upload;

    /**
     * Http client instance.
     *
     * @var Client
     */
    private $http;

    /**
     * Laravel Storage service instance.
     *
     * @var FilesystemAdapter
     */
    private $laravelStorage;

    /**
     * Storage constructor.
     *
     * @param Upload $upload
     * @param Client $http
     * @param FilesystemManager $laravelStorage
     */
    public function __construct(Upload $upload, Client $http, FilesystemManager $laravelStorage)
    {
        $this->upload = $upload;
        $this->http = $http;
        $this->laravelStorage = $laravelStorage;
    }

    /**
     * Download file from specified remote url.
     *
     * @param string $url
     * @param array $params
     *
     * @return string
     */
    public function downloadRemoteFile($url, $params = [])
    {
        return $this->http->request('GET', $url, $params)->getBody()->getContents();
    }
}