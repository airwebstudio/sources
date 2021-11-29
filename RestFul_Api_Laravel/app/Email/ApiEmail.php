<?php
namespace App\Email;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class ApiEmail {

    public const EMAIL_TEMPLATE_WELCOME = 'welcome';
    public const EMAIL_TEMPLATE_FORGOT_PASSWORD = 'forgot_password';

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
        if(!config('api-email.auth.host')){
            return FALSE;
        }
        $this->access_token = Cache::get('api_email_access_token');
        if(!$this->access_token) {
            try {
                $response = Http::post(config('api-email.auth.host').'/api/user/login', [
                    'email' => config('api-email.auth.email'),
                    'password' => config('api-email.auth.pass')
                ])->json();
            }
            catch(\Exception $ex){
                return false;
            }

            if (!isset($response['access_token'])) {
                return response()->json(['error' => 'Access Token not found']);
            }

            Cache::put('api_email_access_token', $response['access_token'], now()->addHour());
            $this->access_token = $response['access_token'];
        }
        return $this->access_token;
    }

    /**
     * main http function
     */
    public function http($type, $url, $parameters){
        if(!config('api-email.auth.host')){
            return FALSE;
        }
        try {
            $return = Http::withToken($this->access_token)
                ->timeout(1)
                ->$type(config('api-email.auth.host') . $url, $parameters)
                ->json();
        }
        catch(\Exception $ex){
            return false;
        }
        return $return;
    }

    /**
     * send email
     */
    public function send_email(array $parameters)
    {
        return $this->http('post','/api/email', $parameters);
    }

}
