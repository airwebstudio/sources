<?php
namespace App\Journal;
use Illuminate\Support\Facades\Http;

class Journal {
    
    private $client;

    public function add($primary_id, $seondary_id, $type, $description, $details) {
        $jserver = config('app.journal_server');
        /* if (!$this->client) {
            $this->client = new \GuzzleHttp\Client();
        } */

        $response = Http::put($jserver.'/api/event', [

                'primary_id' => $primary_id, 
                'secondary_id' => $seondary_id, 
                'type' => $type, 
                'description' => $description, 
                'details' => $details,

        ]
        );

        return Array(
            'status_code' => $response->getStatusCode(),
            'body' => $response->getBody(),
        );


    }
}