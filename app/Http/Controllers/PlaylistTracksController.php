<?php namespace App\Http\Controllers;

use App\Playlist;
use Illuminate\Http\Request;
use App\Services\Playlists\PlaylistTracksPaginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Vebto\Bootstrap\Controller;

class PlaylistTracksController extends Controller {

    /**
     * @var Request
     */
    private $request;

    /**
     * @var PlaylistTracksPaginator
     */
    private $paginator;

    /**
     * @var Playlist
     */
    private $playlist;

    /**
     * Create new PlaylistTracksController instance.
     *
     * @param Request $request
     * @param PlaylistTracksPaginator $paginator
     * @param Playlist $playlist
     */
    public function __construct(Request $request, PlaylistTracksPaginator $paginator, Playlist $playlist)
    {
        $this->request = $request;
        $this->paginator = $paginator;
        $this->playlist = $playlist;
    }

    /**
     * Paginate specified playlist tracks.
     *
     * @param integer $id
     * @return LengthAwarePaginator
     */
    public function index($id) {
        return $this->paginator->paginate($id);
    }

    /**
     * Add specified tracks to playlist.
     *
     * @param integer $id
     * @return Playlist
     */
    public function add($id) {
        $playlist = $this->playlist->findOrFail($id);

        $this->authorize('update', $playlist);

        $ids = $this->request->get('ids');
        $playlist->tracks()->sync($ids, false);

        return $playlist;
    }

    /**
     * Remove specified tracks from playlist.
     *
     * @param integer $id
     * @return Playlist
     */
    public function remove($id) {
        $playlist = $this->playlist->findOrFail($id);

        $this->authorize('update', $playlist);

        $ids = $this->request->get('ids');
        $playlist->tracks()->detach($ids);

        return $playlist;
    }
}
