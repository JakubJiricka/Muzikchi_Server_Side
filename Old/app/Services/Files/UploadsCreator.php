<?php namespace App\Services\Files;

use Auth;
use App\Upload;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class UploadsCreator {

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

    public function __construct(Upload $upload, FileStorage $storage)
    {
        $this->upload = $upload;
        $this->storage = $storage;
    }

    /**
     * Create multiple uploads from specified file data.
     *
     * @param array $data
     * @return Collection
     */
    public function create($data)
    {
        $inserts = array_map(function($fileData) {
            return $this->normalizeFileData($fileData);
        }, $data);

        $this->upload->insert($inserts);

        $names = array_map(function($data) {return $data['file_name']; }, $inserts);

        return $this->upload->whereIn('file_name', $names)->get();
    }

    /**
     * Normalize specified file data for inserting into database.
     *
     * @param array $data
     * @return array
     */
    private function normalizeFileData($data)
    {
        return [
            'name'       => $data['original_name'],
            'file_name'  => $data['file_name'],
            'extension'  => $this->extractExtension($data),
            'file_size'  => $data['size'],
            'mime'       => $data['mime_type'],
            'user_id'    => isset($data['user_id']) ? $data['user_id'] : Auth::user()->id,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }

    /**
     * Extract file extension from specified file data.
     *
     * @param array $fileData
     * @return string
     */
    private function extractExtension($fileData)
    {
        if (isset($fileData['extension'])) return $fileData['extension'];

        $pathinfo = pathinfo($fileData['original_name']);

        if (isset($pathinfo['extension'])) return $pathinfo['extension'];

        return explode('/', $fileData['mime_type'])[1];
    }
}