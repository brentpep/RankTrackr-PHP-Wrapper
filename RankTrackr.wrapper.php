<?php 
/**
 * RankTrackr.wrapper.php
 *
 * Access RankTrackr API via cURL. Currently this wrapper only 
 * contains methods for grabbing URLs and Keywords associated with the URLs.
 *
 * PHP version 5
 *
 *
 * @author     Brent Pepitone <brent@legnd.com>
 * @license    The MIT License (MIT)
 * @version    1.0
 * @link       n/a
 */

/*
The MIT License (MIT)
http://opensource.org/licenses/MIT

Copyright(c) 2013
*/

class RankTrackr{
	
	# RankTrackr Creditentials
	private $_username 		 = 'email';
	private $_password 		 = 'password';
	private $_tokenKey		 = '';
	
	# (optional) MySQL Tables
	private $_urlTable		 = 'ranktrackr_urls';
	private $_keywordTable	 = 'ranktrackr_keywords';
	
	public $output			 = array();
	
	private $_baseRoute 	 = 'http://users.ranktrackr.com/';
	private $_route			 = '';
	private $_curlResult	 = '';
	
	# cURL Settings
	private $_isPost		 = false;
	private $_timeout		 = 30;
	private $_maxRedirects	 = 4;
	private $_followLocation = true;
	private $_postFields	 = array();
	
	public function initUpdate(){
	    # Truncate the two DB tables
		$this->truncateTables();
		
		# Get Auth Token from RankTrackr
		$this->getAuthToken();
		
		# Get URLs and store in DB
		$urls = $this->getUrls();
		foreach($urls as $url){
			$last_processed_at = $this->isoToTimeStamp($url['last_processed_at']);
			$created_at = $this->isoToTimeStamp($url['created_at']);
			
			# Insert Query for URLs
			
			# Get Keywords for URLs and store to DB
			$keywords = $this->getKeywords($url['id']);
			foreach($keywords as $keyword){
				$last_processed_at = $this->isoToTimeStamp($keyword['last_processed_at']);
				$created_at = $this->isoToTimeStamp($keyword['created_at']);
				
				# Insert Query for Keywords
			}
		}
		
	}
	
	public function truncateTables(){
		# Truncate MySQL tables before storing the new batch
	}
	
	public function getAuthToken(){
		$this->setRoute('api/v1/token');
		$this->enablePostMethod();
		
		$postFields = array('email'=>$this->_username,'password'=>$this->_password);
		$this->_postFields = json_encode($postFields);
		
		$this->createCurl();
		$result = $this->_curlResult;
		
		$token = json_decode($result);
		$this->_tokenKey = $token->access_token;
		
		return json_decode($result, true);
	}
	
	public function getUrls(){
		$this->setRoute('api/v1/urls?access_token='.$this->_tokenKey);
		$this->disablePostMethod();
		
		$this->createCurl();
		$result = $this->_curlResult;
		
		return json_decode($result, true);
	}
	
	public function getKeywords($urlId){
		$this->setRoute('api/v1/urls/'.$urlId.'/keywords?access_token='.$this->_tokenKey);
		$this->disablePostMethod();
		
		$this->createCurl();
		$result = $this->_curlResult;
		
		return json_decode($result, true);
	}
	
	public function isoToTimeStamp($time){
		# Convert to UNIX
		$unix = date("U",strtotime($time));
		# Convert to GMT
		$gmt = gmdate('r',$unix);
		# Finally convert to timestamp for database entry
		$timestamp = date('Y-m-d H:i:s', strtotime($gmt));
		
		return $timestamp;
	}
	
	public function setLastUpdated($time){
		$this->_lastUpdated = $time;
	}
	public function getLastUpdated(){
		return $this->_lastUpdated;
	}
	
	public function setRoute($route){
		$this->_route = $route;
	}
	public function getRoute(){
		return $this->_baseRoute . $this->_route;
	}
	
	public function disablePostMethod(){
		# Use this for GET
		$this->_isPost = false;
		return $this;
	}
	
	public function postMethod(){
		return $this->_isPost;
	}
	
	public function enablePostMethod(){
		# Use this for POST
		$this->_isPost = true;
		return $this;
	}
	
	public function curlHttpHeader(){
		$header = array(
			'Accept: application/json',
		    'Content-Type: application/json',
		    'Host: users.ranktrackr.com'
		);
		return $header;
	}
	
	public function createCurl(){
		$route = $this->getRoute();
		
		$ch = curl_init();
		
		curl_setopt($ch, CURLOPT_URL, $route);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $this->curlHttpHeader());
		curl_setopt($ch, CURLOPT_TIMEOUT, $this->_timeout);
		curl_setopt($ch, CURLOPT_MAXREDIRS, $this->_maxRedirects);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $this->_followLocation);
		curl_setopt($ch, CURLOPT_MAXREDIRS, $this->_maxRedirects);
		curl_setopt($ch, CURLOPT_POST, $this->postMethod());
		if($this->postMethod()==true) curl_setopt($ch, CURLOPT_POSTFIELDS, $this->_postFields);
		
		$this->_curlResult = $this->execCurl($ch);
		$this->_curlStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		
		$this->closeCurl($ch);
	}
	
	public function execCurl($route){
		return curl_exec($route);
	}
	
	public function closeCurl($ch){
		return curl_close($ch);
	}

}