<?php
namespace App\Journal;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class Journal {

    public $access_token;

    public function __construct()
    {
        $this->auth();
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \JsonException
     */
    public function auth()
    {
        if(!config('journal-public.auth.host')){
            return FALSE;
        }
        $this->access_token = Cache::get('event_journal_access_token');
        if(!$this->access_token) {
            try {
                $response = Http::post(config('journal-public.auth.host').'/api/user/login', [
                    'email' => config('journal-public.auth.email'),
                    'password' => config('journal-public.auth.pass')
                ])->json();
            }
            catch(\Exception $ex){
                return false;
            }

            if (!isset($response['access_token'])) {
                return response()->json(['error' => 'Access Token not found']);
            }

            Cache::put('event_journal_access_token', $response['access_token'], now()->addHour());
            $this->access_token = $response['access_token'];
        }
        return $this->access_token;
    }

    /**
     * main http function
     */
    public function http($type, $url, $parameters){
        if(!config('journal-public.auth.host')){
            return FALSE;
        }
        try {
            $return = Http::withToken($this->access_token)
                ->timeout(1)
                ->$type(config('journal-public.auth.host') . $url, $parameters)
                ->json();
        }
        catch(\Exception $ex){
            return false;
        }
        return $return;
    }

    /**
     * get all events with parameters
     */
    public function get_all_events(array $parameters)
    {
        return $this->http('get','/api/event', $parameters);
    }

    /**
     * add new event
     */
    public function add_event(array $parameters)
    {
        return $this->http('put','/api/event', $parameters);
    }

}
