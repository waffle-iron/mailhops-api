<?php

// TODO look at implementing https://github.com/guzzle/cache-subscriber/blob/master/src/CacheStorage.php

use GuzzleHttp\Client;
use GuzzleHttp\Subscriber\Cache\CacheSubscriber;

class What3Words {

	private $api_key		= '';

	private $language		= 'en';

	private $client			= null;

	public function __construct($args=array()){

		//get api key
		if(getenv('W3W_API_KEY')){
			$this->api_key = getenv('W3W_API_KEY');
		} else if(!empty($args['api_key'])){
			$this->api_key = $args['api_key'];
		}

		$this->client = new Client();
		CacheSubscriber::attach($this->client);

		if(!empty($args['lang']) && in_array($args['lang'], array('en','de','es','fr','pt-BR','ru')))
			$this->language = $args['lang']=='pt-BR'?'pt':$args['lang'];
	}

	public function getWords($lat,$lng){
		if(empty($this->api_key))
			return '';

		$fields = array('key'=>$this->api_key, 'position'=>$lat.','.$lng, 'lang'=>$this->language);

		$res = $this->client->get('http://api.what3words.com/position?'.http_build_query($fields));

		if($res->getStatusCode() == 200)
		{
			$return = $res->json();

			if(!empty($return['words']))
				return array('url'=>'http://w3w.co/'.implode('.', $return['words']),'words'=>$return['words']);
		}
		return '';
	}

	public function getWordsCurl($lat,$lng){
		if(empty($this->api_key))
			return '';

		$ch = curl_init('http://api.what3words.com/position');

		$fields = array('key'=>$this->api_key, 'position'=>$lat.','.$lng, 'lang'=>$this->language);

		curl_setopt($ch, CURLOPT_POST, count($fields));
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$return = curl_exec($ch);
		$return = json_decode($return, true);

		curl_close($ch);

		if(!empty($return['words']))
			return array('url'=>'http://w3w.co/'.implode('.', $return['words']),'words'=>$return['words']);
		return '';
	}
};
?>
