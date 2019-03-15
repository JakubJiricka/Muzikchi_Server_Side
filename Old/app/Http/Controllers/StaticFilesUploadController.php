<?php namespace App\Http\Controllers;

use App\Upload;
use Illuminate\Http\Request;
use App\Services\Files\FileStorage;

class StaticFilesUploadController extends Controller {

    /**
     * Laravel request instance.
     *
     * @var Request
     */
    private $request;

    /**
     * @var FileStorage
     */
    private $fileStorage;

    /**
     * UploadsController constructor.
     *
     * @param Request $request
     * @param FileStorage $fileStorage
     */
    public function __construct(Request $request, FileStorage $fileStorage) {
        $this->request = $request;
        $this->fileStorage = $fileStorage;
    }

    /**
     * Store video or music files without attaching them to any database records.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function videos()
    {
        $this->authorize('store', Upload::class);

        $this->validate($this->request, [
            'type'    => 'required|string|in:track',
            'files'   => 'required|array|min:1|max:5',
            'files.*' => 'required|file'
        ]);

        $type = $this->request->get('type');

        $urls = array_map(function($file) use($type) {
            return ['url' => $this->fileStorage->putStatic($file, "{$type}_files", 'mp3')];
        }, $this->request->all()['files']);

        return $this->success(['data' => $urls], 201);
    }

    /**
     * Store images without attaching them to any database records.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function images() {

        $this->authorize('store', Upload::class);

        $this->validate($this->request, [
            'type'    => 'required|string|in:avatar,playlist,artist,album,branding,page',
            'files'   => 'required|array|min:1|max:5',
            'files.*' => 'required|file|image'
        ]);

        $type = $this->request->get('type', 'article');

        $urls = array_map(function($file) use($type) {
            return ['url' => $this->fileStorage->putStatic($file, "{$type}_images")];
        }, $this->request->all()['files']);

        return $this->success(['data' => $urls], 201);
    }
}
