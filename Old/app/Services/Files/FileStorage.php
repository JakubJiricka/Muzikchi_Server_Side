<?php namespace App\Services\Files;

use App\Upload;
use Illuminate\Http\File;
use Illuminate\Support\Arr;
use Illuminate\Http\UploadedFile;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Filesystem\FilesystemManager;

class FileStorage {

    /**
     * Upload model.
     *
     * @var Upload
     */
    private $upload;

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
     * @param FilesystemManager $laravelStorage
     */
    public function __construct(Upload $upload, FilesystemManager $laravelStorage)
    {
        $this->upload = $upload;
        $this->laravelStorage = $laravelStorage;
    }

    /**
     * Save specified files to currently active flysystem disk.
     *
     * @param mixed $contents
     * @param string $path
     * @param string $fileName
     * @param array $options
     *
     * @return string
     */
    public function put($contents, $path = 'uploads', $fileName = null, $options = [])
    {
        if ( ! $fileName) $fileName = str_random(40);

        $disk = Arr::pull($options, 'disk');

        if ($contents instanceof File || $contents instanceof UploadedFile) {
            $this->laravelStorage->disk($disk)->putFileAs($path, $contents, $fileName, $options);
        } else {
            $this->laravelStorage->disk($disk)->put($path.'/'.$fileName, $contents, $options);
        }

        return $fileName;
    }

    /**
     * Save static (inline) images in specified folder.
     *
     * @param UploadedFile|array $file
     * @param string $folder
     * @param string $fallbackExtension
     * @return string
     */
    public function putStatic($file, $folder, $fallbackExtension = null)
    {
        if (is_a($file, UploadedFile::class)) {
            $extension = $file->guessClientExtension();
        } else {
            $extension = $file['extension'];
            $file = $file['contents'];
        }

        if ( ! $extension) $extension = $fallbackExtension;

        $fileName = str_random(40) . '.' . $extension;

        $fileName = $this->put($file, $folder, $fileName, ['visibility' => 'public', 'disk' => 'public']);

        return "storage/$folder/$fileName";
    }
}