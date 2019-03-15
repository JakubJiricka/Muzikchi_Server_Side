<?php namespace App\Services\Providers\Local;

use App\Track;
use Illuminate\Database\Eloquent\Collection;

class LocalTopTracks {

    /**
     * Get top tracks using local provider.
     *
     * @return Collection
     */
    public function getTopTracks() {
	    $tracks = Track::with('album.artist')->orderBy('plays', 'desc')->limit(100)->get()->toArray();
	    $temp = array();
	    $flag = array();

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
	
	    return array_slice($temp, 0, 50);
	    
    }
}
