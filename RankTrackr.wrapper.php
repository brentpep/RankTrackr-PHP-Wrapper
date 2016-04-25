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
 * @version    1.1
 * @link       n/a
 */

/*
The MIT License (MIT)
http://opensource.org/licenses/MIT

Copyright (c) 2013 Brent Pepitone

Permission is hereby granted, free of charge, to any person
obtaining a copy of this software and associated documentation
files (the "Software"), to deal in the Software without
restriction, including without limitation the rights to use,
copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the
Software is furnished to do so, subject to the following
conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
OTHER DEALINGS IN THE SOFTWARE.
*/

class RankTrackr{

	# RankTrackr Creditentials
	private $_username 		 = 'email';
	private $_password 		 = 'password';
	private $_tokenKey		 = '';

	# (optional) MySQL Tables
	private $_urlTable		 		= 'ranktrackr_urls';
	private $_keywordTable	 	= 'ranktrackr_keywords';

	public $output = array();

	private $_baseRoute 	 	= 'https://users.ranktrackr.com/';
	private $_apiEndpoint  	= 'http://api.ranktrackr.com/';
	private $_route			 		= '';
	private $_curlResult	 	= '';

	# cURL Settings
	private $_isPost		 		 = false;
	private $_timeout		 		 = 30;
	private $_maxRedirects	 = 4;
	private $_followLocation = true;
	private $_postFields	   = array();

	# Make changes to support how you want to handle the RankTrackr Data
	public function initUpdate(){
	    # Truncate the two DB tables
		$this->truncateTables();

		# Get Auth Token from RankTrackr
		$this->getAuthToken();

		# Get URLs and store in DB
		$urls = $this->getUrls();
		$output = array();
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

		return $output;
	}

	public function truncateTables(){
		# Truncate MySQL tables before storing the new batch
	}

	public function getAuthToken(){
		$this->setBaseRoute('api/v1/token');
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
		$this->setApiRoute('api/v1/urls?access_token='.$this->_tokenKey);
		$this->disablePostMethod();

		$this->createCurl();
		$result = $this->_curlResult;

		return json_decode($result, true);
	}

	public function getKeywords($urlId){
		$this->setApiRoute('api/v1/urls/'.$urlId.'/keywords?access_token='.$this->_tokenKey);
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

	public function setApiRoute($route){
		$this->_apiEndpoint = $route;
	}
	public function getApiRoute(){
		return $this->_apiEndpoint . $this->_route;
	}

	public function setBaseRoute($route){
		$this->_baseRoute = $route;
	}
	public function getBaseRoute(){
		return $this->_baseRoute . $this->_route;
	}

	public function disablePostMethod(){
		# Use this for GET
		$this->_isPost = false;
		return $this; # Allow method chaining
	}

	public function postMethod(){
		return $this->_isPost;
	}

	public function enablePostMethod(){
		# Use this for POST
		$this->_isPost = true;
		return $this; # Allow method chaining
	}

	public function curlHttpHeader(){
		$header = array(
			'Accept: application/json',
		    'Content-Type: application/json',
		    'Host: users.ranktrackr.com'
		);
		return $header;
	}

	public function createCurl($base = false){
		$route = ($base == false ? $this->getApiRoute() : $this->getBaseRoute());

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
