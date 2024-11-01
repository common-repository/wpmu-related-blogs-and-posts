<?php
error_reporting(E_ALL);
set_time_limit(0);
include_once('oryx-functions.php');

class Yahoo {
	
	private $api_url = 'http://search.yahooapis.com/ContentAnalysisService/V1/termExtraction';
	private $api_key;
	private $text;
	private $response;
	public $tags = array();
	

	function Yahoo($api_key, $text)
	{
		$this->api_key = $api_key;
		$this->text = $text;
		$this->send_to_yh();
		$this->find_tags();
		return $this->tags;
	}
	
	private function find_tags()
	{
		$json = $this->response;
		$response = json_decode($json);
		$tags = array();
		
		if(count($response->ResultSet->Result) > 0){
			foreach($response->ResultSet->Result as $key=>$value)
			{
				$tags["{$value}"] = 100;
			}
		} else {
			oryx_debug('Didnt find any OC tags');
		}
		
		$this->tags = $tags;
	}
	
	private function send_to_yh()
	{
		$postdata = array();
		$postdata['appid'] = $this->api_key;
		$postdata['context'] = $this->text;
		$postdata['query'] = '';
		$postdata['output'] = 'json';
		
		$poststring = $this->urlencodeArray($postdata);
		
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->api_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $poststring);
		curl_setopt($ch, CURLOPT_POST, 1);
		
		$curl_resp = curl_exec($ch);
		if($curl_resp === false){
			oryx_debug('CURL ERROR - '.curl_error($ch));
		}
		
		$response = html_entity_decode($curl_resp);
		
		//oryx_debug('YH RESPONSE = '.$response);
		$this->response = $response;
	}
	
	private function urlencodeArray($array) {
		foreach ($array as $key => $val) {
			if (!isset($string)) {
				$string = $key . "=" . urlencode($val);
			} else {
				$string .= "&" . $key . "=" . urlencode($val);
			}
		}
		return $string;
	}
}
/*$text = 'Google Inc. is an American public corporation, earning revenue from advertising  related to its Internet search, e-mail, online mapping, office productivity, social networking, and video sharing services as well as selling advertising-free versions of the same technologies. Google has also developed an open source web browser and a mobile operating system. The Google headquarters, the Googleplex, is located in Mountain View, California. As of March 31, 2009 (2009 -03-31)[update], the company has 19,786 full-time employees. The company is running thousands of servers worldwide, which process millions of search requests each day and about 1 petabyte  of user-generated data every hour.[5]

Google was founded by Larry Page and Sergey Brin while they were students at Stanford University and the company was first incorporated as a privately held company on September 4, 1998. The initial public offering took place on August 19, 2004, raising $1.67 billion, implying a value for the entire corporation of $23 billion. Google has continued its growth through a series of new product developments, acquisitions, and partnerships. Environmentalism, philanthropy and positive employee relations have been important tenets during the growth of Google. The company has been identified multiple times as Fortune Magazine\'s #1 Best Place to Work,[6] and as the most powerful brand in the world.[7] Alexa ranks Google as the most visited website on the Internet.';

$yh = new Yahoo('OlkdSybV34ENe0I7tE7f12lA8swCF30L_f9x4yn9yn6Y.lpUtOo2KATfakQpaOAoxRs-', $text);
print_r($yh->tags);*/
?>