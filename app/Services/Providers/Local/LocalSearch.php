<?php namespace App\Services\Providers\Local;

use App\Album;
use App\Track;
use App\Artist;
use App\Services\Search\SearchInterface;

class LocalSearch implements SearchInterface {

    /**
     * Search database using given params.
     *
     * @param string  $q
     * @param int     $limit
     * @param string  $type
     *
     * @return array
     */
    public function search($q, $limit = 10, $mobile = 1) {
        $q = urldecode($q);
        $ip = $this->getClientIp();
        $langtable = \DB::table('lang')->where('ipaddress',$ip)->first();
        if(!empty($langtable)) {
            $lang = $langtable->lang;
            
            if($mobile == 1)    $lang = 'farsi';
	       
            if ($lang == 'english') {
                return [
                    'artists' => Artist::where('name', 'like', $q . '%')->whereNull('Illegal_or_not')->limit($limit)->get(),
                    'albums' => Album::whereHas('artist', function ($query) {
                    	$query->whereNull('Illegal_or_not');
                    })->with('artist')->where('name', 'like', $q . '%')->limit($limit)->get(),
                    'tracks' => Track::whereHas('album.artist', function ($query) {
	                    $query->whereNull('Illegal_or_not');
                    })->with('album.artist')->where('name', 'like', $q . '%')->orderBy('plays', 'desc')->limit($limit)->get()
                ];
            } else if ($lang == 'farsi') {
                return [
                    'artists' => Artist::where('farsi_name', 'like', '%' . $q . '%')->whereNull('Illegal_or_not')->limit($limit)->get(),
                    'albums' => Album::whereHas('artist', function ($query) {
	                    $query->whereNull('Illegal_or_not');
                    })->with('artist')->where('name', 'like', '%' . $q . '%')->limit($limit)->get(),
                    'tracks' => Track::whereHas('album.artist', function ($query) {
	                    $query->whereNull('Illegal_or_not');
                    })->with('album.artist')->where('farsi_name', 'like', '%' . $q . '%')->orderBy('plays', 'desc')->limit($limit)->get()
                ];
            }
        }else {
            return [
                'artists' => Artist::where('name', 'like', $q . '%')->whereNull('Illegal_or_not')->limit($limit)->get(),
                'albums' => Album::whereHas('artist', function ($query) {
	                $query->whereNull('Illegal_or_not');
                })->with('artist')->where('name', 'like', $q . '%')->limit($limit)->get(),
                'tracks' => Track::whereHas('album.artist', function ($query) {
	                $query->whereNull('Illegal_or_not');
                })->with('album.artist')->where('name', 'like', $q . '%')->orderBy('plays', 'desc')->limit($limit)->get()
            ];
        }
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
}
