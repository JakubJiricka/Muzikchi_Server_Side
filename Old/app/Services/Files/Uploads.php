<?php namespace App\Services\Files;

use App\Upload;
use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\Collection;

class Uploads {

    /**
     * Upload model.
     *
     * @var Upload
     */
    private $upload;

    /**
     * Storage service instance.
     *
     * @var FileStorage
     */
    private $storage;

    /**
     * UploadsCreator service instance.
     *
     * @var UploadsCreator
     */
    private $uploadsCreator;

    /**
     * Uploads constructor.
     *
     * @param Upload $upload
     * @param \App\Services\Files\FileStorage $storage
     * @param UploadsCreator $uploadsCreator
     */
    public function __construct(Upload $upload, FileStorage $storage, UploadsCreator $uploadsCreator)
    {
        $this->upload = $upload;
        $this->storage = $storage;
        $this->uploadsCreator = $uploadsCreator;
    }

    /**
     * Create uploads from specified file data.
     *
     * This will upload files to filesystem if needed
     * and create Upload models for those files.
     *
     * @param array $files
     * @return Collection
     */
    public function store($files)
    {
        if ( ! is_array($files)) $files = [$files];

        $files = $this->saveFilesToDisk($files);

        return $this->uploadsCreator->create($files);
    }

    /**
     * Save specified files to disk.
     *
     * @param array $files
     *
     * @return array
     */
    private function saveFilesToDisk($files)
    {
        foreach ($files as $key => $file) {

            //convert UploadedFile instances to array
            if (is_a($file, UploadedFile::class)) {
                $files[$key] = $file = $this->uploadedFileToArray($file);
            }

            //store file to disk and set file name on file configuration
            $files[$key]['file_name'] = $this->storage->put($file['contents']);

            //unset contents from file configuration so
            //temp file resource is released from memory
            unset($files['contents']);
        }

        return $files;
    }

    /**
     * Convert laravel|symfony UploadedFile instance into array.
     *
     * @param UploadedFile $fileData
     * @return array
     */
    private function uploadedFileToArray(UploadedFile $fileData)
    {
        return [
            'original_name' => $fileData->getClientOriginalName(),
            'mime_type' => $fileData->getMimeType(),
            'size' => $fileData->getClientSize(),
            'extension' => $fileData->guessExtension(),
            'contents'  => $fileData
        ];
    }
}