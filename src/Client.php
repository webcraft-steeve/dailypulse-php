<?php


namespace Celpax\Dailypulse;

use GuzzleHttp\Exception\RequestException;

class Client {

    const API_ENDPOINT="https://api.celpax.com:4443";
    const VERSION="1.0.10";
    const ACCEPTS_VERSION="~1.0";

    const SIGNATURE_HEADER="X-Celpax-Signature";
    const REQUEST_TIMEOUT=10;

    private $access_key_id;
    private $secret_access_key;
    private $client;
    private $urlSign;

    public function __construct($key,$secret,$verifyssl=true){
        $this->access_key_id=$key;
        $this->secret_access_key=$secret;
        $this->urlSign= new URLSign();
        $this->client = new \GuzzleHttp\Client([
            'base_url'=>self::API_ENDPOINT,
            'defaults'=>[
	    	'verify'=> $verifyssl,
                'headers'=>[
                    'X-Celpax-Access-Key-Id' => $this->access_key_id,
                    'X-Celpax-Api-Client' => "PHP ".self::VERSION,
                    'accept-version'=>self::ACCEPTS_VERSION,
                    'accept'=>'application/json'
                ]
            ]
        ]);
    }

    // return the signature as Guzzle Options.
    private function signature($path){
        return [
            'headers'=>[
                self::SIGNATURE_HEADER=>$this->urlSign->sign($path, $this->secret_access_key)
            ],
            'allow_redirects' => false,
            'timeout'         => self::REQUEST_TIMEOUT
        ];
    }

    private function get($path){
        $response = NULL;
        $exception = NULL;

        try {
            $response=$this->client->get($path,$this->signature($path));
        }
        catch(RequestException $e){
            $exception=$e;
        }
        return new Response($response,$exception);
    }

    public function echoMsg($msg){
        return $this->get('/test/echo/' . $msg);
    }

    public function getSites(){
        return $this->get('/profile/sites');
    }

    public function getMoodKPI($site_id){
        return $this->get('/latest/mood/' . $site_id);
    }

    public function getPulsesPerTypicalDay($site_id){
        return $this->get('/latest/pulsesperday/' . $site_id);
    }

    public function getHistoricalMoodKPI($site_id, $number_of_days){
        return $this->get('/historical/mood/' . $site_id . '/' . $number_of_days);
    }

    public function getHistoricalPulsesPerTypicalDay($site_id, $number_of_days){
        return $this->get('/historical/pulsesperday/' . $site_id . '/' . $number_of_days);
    }


} 
