<?php namespace App\Http\Controllers;

use App;
use App\Artist;
use App\Track;
use Cache;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Services\Search\UserSearch;
use App\Services\Search\SearchSaver;
use App\Services\Search\PlaylistSearch;
use App\Services\Providers\ProviderResolver;
use function PHPSTORM_META\map;
use Vebto\Bootstrap\Controller;
use Vebto\Settings\Settings;

class SearchController extends Controller
{
    /**
     * @var ProviderResolver
     */
    private $resolver;

    /**
     * @var SearchSaver
     */
    private $saver;

    /**
     * @var UserSearch
     */
    private $userSearch;

    /**
     * @var PlaylistSearch
     */
    private $playlistSearch;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var Settings
     */
    private $settings;

    /**
     * Create new SearchController instance.
     *
     * @param Request $request
     * @param Settings $settings
     * @param SearchSaver $saver
     * @param UserSearch $userSearch
     * @param ProviderResolver $resolver
     * @param PlaylistSearch $playlistSearch
     */
    public function __construct(
        Request $request,
        SearchSaver $saver,
        Settings $settings,
        UserSearch $userSearch,
        ProviderResolver $resolver,
        PlaylistSearch $playlistSearch
    )
    {
        $this->saver = $saver;
        $this->request = $request;
        $this->settings = $settings;
        $this->resolver = $resolver;
        $this->userSearch = $userSearch;
        $this->playlistSearch = $playlistSearch;
    }

    /**
     * Use active search provider to search for
     * songs, albums and artists matching given query.
     *
     * @param string $q
     * @return array
     */
    public function search($q)
    {
        $this->authorize('show', Artist::class);
        $this->authorize('show', Track::class);

        $limit = $this->request->get('limit', 5);
        
	    $results = $this->resolver->get('search')->search($q, $limit, 0);
	
	    if ($this->resolver->getProviderNameFor('search') !== 'Local') {
		    $results = $this->saver->save($results);
	    }
	
	    $results['playlists'] = $this->playlistSearch->search($q, $limit);
	    $results['users'] = $this->userSearch->search($q, $limit);

//        $results = Cache::remember('search.' . $q . $limit, Carbon::now()->addDays(3), function () use ($q, $limit) {
//            $results = $this->resolver->get('search')->search($q, $limit, 0);
//
//            if ($this->resolver->getProviderNameFor('search') !== 'Local') {
//                $results = $this->saver->save($results);
//            }
//
//            $results['playlists'] = $this->playlistSearch->search($q, $limit);
//            $results['users'] = $this->userSearch->search($q, $limit);
//
//            return $results;
//        });
	    
        $tracks = $results['tracks'];
        $temp = array();
        $flag = array();
	    if($limit != 5) {
		    foreach ($tracks as $key1 => $track1) {
			    $flag[$key1] = 0;
			    foreach ($tracks as $key2 => $track2) {
				    if ($key1 != $key2 && $track1['url'] == $track2['url'] && $track1['duration'] == $track2['duration'] && strlen($track1['artists'][0]) < strlen($track2['artists'][0])) {
					    $flag[$key1] = 1;
					    break;
				    }
			    }
		    }
		    foreach ($tracks as $key => $track) {
			    if ($flag[$key] == 0) array_push($temp, $track);
		    }
		
		    $results['tracks'] = $temp;
	    }

	    $results['tracks'] = array_reverse(array_sort($results['tracks'], function ($value) {
        	return $value['plays'];
        }));
        return $this->filterOutBlockedArtists($results);
    }
    
    public function getClientIp() {

        if (getenv('HTTP_CLIENT_IP'))
        {
            $ipAddress = getenv('HTTP_CLIENT_IP');
        }
        else if(getenv('HTTP_X_FORWARDED_FOR'))
        {
            $ipAddress = getenv('HTTP_X_FORWARDED_FOR');
        }
        else if(getenv('HTTP_X_FORWARDED'))
        {
            $ipAddress = getenv('HTTP_X_FORWARDED');
        }
        else if(getenv('HTTP_FORWARDED_FOR'))
        {
            $ipAddress = getenv('HTTP_FORWARDED_FOR');
        }
        else if(getenv('HTTP_FORWARDED'))
        {
            $ipAddress = getenv('HTTP_FORWARDED');
        }
        else if(getenv('REMOTE_ADDR'))
        {
            $ipAddress = getenv('REMOTE_ADDR');
        }
        else
        {
            $ipAddress = config('settings.nullIpAddess');
        }
        return $ipAddress;
    }

    /*
     * set language for search
     */
    public function lang($lang){
        $ip = $this->getClientIp();
        $langtable = \DB::table('lang')->where('ipaddress',$ip)->first();
        if(empty($langtable)){
            \DB::table('lang')->insert(['lang'=>$lang, 'ipaddress'=>$ip]);
        }else {
            \DB::table('lang')->where('ipaddress',$ip)
                ->update(['lang'=>$lang]);
        }
        $data = array();
        $one = (object)array();
        $one->lang = $lang;
        array_push($data,$one);
        return $data;
    }
   
     /**
     * Use active search provider to search for
     * songs, albums and artists matching given query in mobile.
     *
     * @param string $q
     * @return array
     */
   public function mobilesearch($q)
    {

        $this->authorize('show', Artist::class);
        $this->authorize('show', Track::class);

        $limit = $this->request->get('limit', 3);
	    $results = $this->resolver->get('search')->search($q, $limit, 1);
	
	    if ($this->resolver->getProviderNameFor('search') !== 'Local') {
		    $results = $this->saver->save($results);
	    }
	
	    $results['playlists'] = $this->playlistSearch->search($q, $limit);
	    $results['users'] = $this->userSearch->search($q, $limit);
	    
//        $results = Cache::remember('search.' . $q . $limit, Carbon::now()->addDays(3), function () use ($q, $limit) {
//            $results = $this->resolver->get('search')->search($q, $limit, 1);
//
//            if ($this->resolver->getProviderNameFor('search') !== 'Local') {
//                $results = $this->saver->save($results);
//            }
//
//            $results['playlists'] = $this->playlistSearch->search($q, $limit);
//            $results['users'] = $this->userSearch->search($q, $limit);
//
//            return $results;
//        });
        $resultList = $this->filterOutBlockedArtists($results);
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $domain = $_SERVER['HTTP_HOST'];
        $public_path = $protocol.$domain;

        $tracks = $resultList['tracks'];
        if(!empty($tracks)) {
	        $tracks = array_reverse(array_sort($tracks, function ($value) {
		        return $value['plays'];
	        }));
            foreach ($tracks as $item) {
                $id     = $item->id;
                $name   = $item->name;
                $url    = $public_path."/track/".$id."/".$name;
                return $url;
            }
        }
        $artists = $resultList['artists'];
        if(!empty($artists)) {
            foreach ($artists as $item) {
                $name = $item->name;
                $tracks = \DB::table('tracks')
                    ->where('artists','like','%'.$name.'%')
                    ->orderBy('plays','desc')
                    ->first();
                if(!empty($tracks)) {
                    $id     = $tracks->id;
                    $name   = $tracks->name;
                    $url    = $public_path."/track/".$id."/".$name;
                    return $url;
                }
            }
        }
        $albums = $resultList['albums'];
        if(!empty($albums)) {
            foreach ($albums as $item) {
                $id = $item->id;
                $tracks = \DB::table('tracks')
                    ->where('album_id',$id)
                    ->orderBy('plays','desc')
                    ->first();
                if(!empty($tracks)) {
                    $id     = $tracks->id;
                    $name   = $tracks->name;
                    $url    = $public_path."/track/".$id."/".$name;
                    return $url;
                }
            }
        }
        $playlists = $resultList['playlists'];
        if(!empty($playlists)) {
            foreach ($playlists as $item) {
                $id     = $item['id'];
                $playtrack   = \DB::table('playlist_user as pu')
                    ->leftjoin('track_user as tu','pu.user_id','=','tu.user_id')
                    ->where('pu.playlist_id', $id)
                    ->select("tu.track_id")
                    ->first();
                if(!empty($playtrack)) {
                    $track_id = $playtrack->track_id;
                    $tracks = \DB::table('tracks')
                        ->where('id',$track_id)
                        ->first();
                    if(!empty($tracks)) {
                        $id     = $tracks->id;
                        $name   = $tracks->name;
                        $url    = $public_path."/track/".$id."/".$name;
                        return $url;
                    }
                }
            }
        }
        return "“Not Found”";
    }

    /**
     * Search for audio matching given query.
     *
     * @param string $artist
     * @param string $track
     * @return array
     */
    public function searchAudio($artist, $track)
    {
        $this->authorize('show', Track::class);

        return $this->resolver->get('audio_search')->search($artist, $track, 1);
    }

    /**
     * Search local database for matching artists.
     *
     * @param string $query
     * @return \Illuminate\Database\Eloquent\Collection
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function searchLocalArtists($query)
    {
        $this->authorize('show', Track::class);

        $limit = $this->request->get('limit', 8);

        return Artist::where('name', 'like', "$query%")
            ->orderByPopularity('desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Remove artists that were blocked by admin from search results.
     *
     * @param array $results
     * @return array
     */
    private function filterOutBlockedArtists($results)
    {
        if (($artists = $this->settings->get('artists.blocked'))) {
            $artists = json_decode($artists);

            foreach ($results['artists'] as $k => $artist) {
                if ($this->shouldBeBlocked($artist->name, $artists)) {
                    //unset($results['artists'][$k]);
	                array_splice($results['artists'], $k, 1);
                }
            }

            foreach ($results['albums'] as $k => $album) {
                if (isset($album['artist'])) {
                    if ($this->shouldBeBlocked($album['artist']['name'], $artists)) {
                        //unset($results['albums'][$k]);
	                    array_splice($results['albums'], $k, 1);
                    }
                }
            }
            
            foreach ($results['tracks'] as $k => $track) {
                if (isset($track['album']['artist'])) {
                    if ($this->shouldBeBlocked($track['album']['artist']['name'], $artists)) {
                        //unset($results['tracks'][$k]);
                        array_splice($results['tracks'], $k, 1);
                    }
                }
            }
        }
        return $results;
    }

    /**
     * Check if given artist should be blocked.
     *
     * @param string $name
     * @param array $toBlock
     * @return boolean
     */
    private function shouldBeBlocked($name, $toBlock)
    {
        foreach ($toBlock as $blockedName) {
            $pattern = '/' . str_replace('*', '.*?', strtolower($blockedName)) . '/i';
            if (preg_match($pattern, $name)) return true;
        }
    }
}
