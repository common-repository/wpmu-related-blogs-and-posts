<?php
error_reporting(E_ALL);
set_time_limit(0);
include_once('oryx-functions.php');

class OpenCalais {
	
	private $api_url = "http://api1.opencalais.com/enlighten/rest/";
	private $api_key;
	private $text;
	private $relevance;
	public $tags = array();
	
	private $response;
	
	function OpenCalais($api_key, $text, $relevance=0)
	{
		$this->api_key = $api_key;
		$this->text = $text;
		$this->relevance = $relevance;
		$this->send_to_oc();
		$this->find_tags();
		return $this->tags;
	}
	
	function find_tags()
	{
		$response = json_decode($this->response);
		$result = array();
		$tags = array();
		
		if(count($response) > 0){
			foreach($response as $key=>$value)
			{
				array_push($result, $key=array($value));
			}
			
			unset($result[0]);
			unset($result[1]);
			unset($result[2]);
			
			if(count($result) > 0)
			{
				foreach($result as $key=>$value)
				{
					if(!empty($value[0]->name) && !empty($value[0]->relevance) && $value[0]->relevance >= $this->relevance && stristr($value[0]->name, 'http') === false){
						//$tags[] = $value[0]->name;
						$tags[addslashes($value[0]->name)] = ((float)$value[0]->relevance*100);
					}
				}
			}
		} else {
			oryx_debug('Didnt find any OC tags');
		}
		
		if(count($tags) > 0){
			 $this->tags = $tags;
		} else {
			$this->tags = FALSE;
		}
	}
	
	function send_to_oc()
	{
		$postdata = array();
	
		$postdata['licenseID'] = $this->api_key;
	
		$postdata['paramsXML'] = 
			  '<c:params xmlns:c="http://s.opencalais.com/1/pred/"'
			. ' xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#">'
			. '	<c:processingDirectives c:contentType="text/raw'
			. '" c:outputFormat="application/json" enableMetadataType="GenericRelations,SocialTags"></c:processingDirectives>'
			. '	<c:userDirectives c:allowDistribution="false' 
			. '" c:allowSearch="false" c:externalID="' 
			. '" c:submitter="WP Open Calais Cron Tagger"></c:userDirectives>'
			. '	<c:externalMetadata></c:externalMetadata>'
			. '</c:params>';
			
		$postdata['content'] = $this->text;
		
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
		oryx_debug('OC RESPONSE = '.strlen($response));
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

$oc = new OpenCalais('f8b3ww7w6agnqzkxuhap975k', $text);
print_r($oc->tags);*/
?>