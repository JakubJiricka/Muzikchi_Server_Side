<?php namespace App\Services\Files\Response;

use Storage;
use Response;
use App\Upload;

class FileContentResponseCreator {

    /**
     * ImageResponse service instance.
     *
     * @var ImageResponse
     */
    private $imageResponse;

    /**
     * AudioVideoResponse service instance.
     *
     * @var AudioVideoResponse
     */
    private $audioVideoResponse;

    /**
     * FileContentResponse constructor.
     *
     * @param ImageResponse $imageResponse
     * @param AudioVideoResponse $audioVideoResponse
     */
    public function __construct(ImageResponse $imageResponse, AudioVideoResponse $audioVideoResponse)
    {
        $this->imageResponse = $imageResponse;
        $this->audioVideoResponse = $audioVideoResponse;
    }

    /**
     * Return download or preview response for given file.
     *
     * @param Upload  $upload
     * @param array   $params
     *
     * @return mixed
     */
    public function create(Upload $upload, $params = [])
    {
        if ( ! Storage::exists($upload->path)) abort(404);

        list($mime, $type) = $this->getTypeFromModel($upload);

        if ($type === 'image') {
            return $this->imageResponse->create($upload, $params);
        } elseif ($this->shouldStream($mime, $type)) {
            return $this->audioVideoResponse->create($upload);
        } else {
            return $this->createBasicResponse($upload);
        }
    }

    /**
     * Create a basic response for specified upload content.
     *
     * @param Upload $upload
     * @return Response
     */
    private function createBasicResponse(Upload $upload)
    {
        return response(Storage::get($upload->path), 200, ['Content-Type' => $upload->mime]);
    }

    /**
     * Extract file type from model.
     *
     * @param Upload $fileModel
     * @return array
     */
    private function getTypeFromModel(Upload $fileModel)
    {
        $mime = $fileModel->mime;
        $type = explode('/', $mime)[0];

        return array($mime, $type);
    }

    /**
     * Should file with given mime be streamed.
     *
     * @param string $mime
     * @param string $type
     *
     * @return bool
     */
    private function shouldStream($mime, $type) {
        return $type === 'video' || $type === 'audio' || $mime === 'application/ogg';
    }
}