<?php namespace App\Http\Controllers;

use App\Upload;
use Illuminate\Http\Request;
use App\Services\Files\Uploads;
use App\Services\Files\UploadsRepository;
use App\Services\Files\Response\FileContentResponseCreator;

class UploadsController extends Controller {

    /**
     * Laravel request instance.
     *
     * @var Request
     */
    private $request;

    /**
     * Uploads service instance.
     *
     * @var Uploads
     */
    private $uploads;

    /**
     * FileContentResponseCreator instance.
     *
     * @var FileContentResponseCreator
     */
    private $contentResponse;

    /**
     * UploadsRepository instance.
     *
     * @var UploadsRepository
     */
    private $uploadsRepository;

    /**
     * UploadsController constructor.
     *
     * @param Request $request
     * @param FileContentResponseCreator $contentResponse
     * @param UploadsRepository $uploadsRepository
     * @param Uploads $uploads
     */
    public function __construct(
        Request $request,
        FileContentResponseCreator $contentResponse,
        UploadsRepository $uploadsRepository,
        Uploads $uploads
    )
    {
        $this->request = $request;
        $this->uploads = $uploads;
        $this->contentResponse = $contentResponse;
        $this->uploadsRepository = $uploadsRepository;
    }

    /**
     * Paginate all available uploads.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function index()
    {
        $this->authorize('index', Upload::class);

        return $this->uploadsRepository->paginate($this->request->all());
    }

    /**
     * Return data needed to preview specified file based on its type.
     *
     * @param $id
     *
     * @return mixed
     */
    public function show($id)
    {
        $upload = $this->uploadsRepository->findOrFail($id);

        $this->authorize('show', $upload);

        return $this->contentResponse->create($upload);
    }

    /**
     * Store files in database and filesystem.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store() {

        $this->authorize('store', Upload::class);

        $this->validate($this->request, [
            'files'   => 'required|array|min:1|max:5',
            'files.*' => 'required|file'
        ]);

        $uploads = $this->uploads->store($this->request->all()['files']);
        return $this->success(['data' => $uploads], 201);
    }

    /**
     * Delete specified uploads from disk and database.
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function destroy()
    {
        $this->authorize('destroy', Upload::class);

        $this->validate($this->request, [
            'ids'   => 'required|array|min:1',
            'ids.*' => 'required|integer|min:1'
        ]);

        $this->uploadsRepository->delete($this->request->get('ids'));

        return $this->success(null, 204);
    }
}
