<?php namespace App\Services\Files;

use DB;
use Storage;
use App\Reply;
use App\Upload;

class UploadsRepository {

    /**
     * Upload model.
     *
     * @var Upload
     */
    private $upload;

    /**
     * Uploads service instance.
     *
     * @var Uploads
     */
    private $uploads;

    /**
     * UploadsRepository constructor.
     *
     * @param Upload $upload
     * @param Uploads $uploads
     */
    public function __construct(Upload $upload, Uploads $uploads)
    {
        $this->upload = $upload;
        $this->uploads = $uploads;
    }

    /**
     * Find upload by given id or throw exception.
     *
     * @param integer $id
     * @return Upload
     */
    public function findOrFail($id)
    {
        return $this->upload->findOrFail($id);
    }

    /**
     * Paginate all uploads using specified params.
     *
     * @param $params
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate($params)
    {
        $perPage = isset($params['per_page']) ? $params['per_page'] : 15;
        $order   = isset($params['order']) ? $params['order'] : 'created_at';
        $searchQuery = isset($params['query']) ? $params['query'] : null;

        $query = $this->upload->with('tags');

        if ($searchQuery) {
            $query->where('name', 'like', "$searchQuery%");
        }

        return $query->orderBy($order, 'desc')->paginate($perPage);
    }

    /**
     * Attach uploads with given ids to reply.
     *
     * @param Reply $reply
     * @param array $fileNames
     *
     * @return array
     */
    public function attachUploadsToReply(Reply $reply, $fileNames)
    {
        $ids = $this->upload->whereIn('file_name', $fileNames)->pluck('id');
        return $reply->uploads()->sync($ids, false);
    }

    /**
     * Delete specified uploads from disk and database.
     *
     * @param integer[] $ids
     * @return bool|null
     */
    public function delete($ids)
    {
        //detach tags
        DB::table('taggables')->where('taggable_type', Upload::class)->whereIn('taggable_id', $ids)->delete();

        //detach replies
        DB::table('uploadables')->where('uploadable_type', Upload::class)->whereIn('uploadable_id', $ids)->delete();

        //delete uploads from dis
        $names = $this->upload->whereIn('id', $ids)->pluck('file_name')->map(function($name) { return "uploads/$name"; });
        Storage::delete($names->toArray());

        //delete uploads from database
        return $this->upload->whereIn('id', $ids)->delete();
    }
}