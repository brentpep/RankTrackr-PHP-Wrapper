#RankTrackr PHP Wrapper
A simple, user-friendly PHP Wrapper for RankTrackr's API

## Important
The data being pulled in via [RankTrackr API](http://users.ranktrackr.com/docs) takes a while to load if you are tracking many URLs and keywords. I HIGHLY suggest to grab the data, store it in a database and pull in the info via database.
You will see how I did it in the wrapper.

## Useage Example
```php
<?php
	// Init Class
	$RankTrackr = new RankTrackr();
	$RankTrackr->initUpdate();
?>
```

## Documentation
The wrapper makes using cURL very easy. To get the RankTrackr Auth Token:
```php
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
```
Just change `$this->setRoute('')` to the route located in the API Documentation.

### Current Methods
```php
	function getAuthToken();
	function getUrls();
	function getKeywords();
```

#### Note about updates
I use this class quite a bit and plan on making changes as I need them, so keep an eye out on updates.