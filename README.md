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

#### Update 1.1
The only major change here is that RankTrackr is now enforcing SSL for the user access on the subdomain 'users.ranktrackr.com', which should be used only for interface access.
This prompted me to change around some methods to make it easy to reflect these changes. These routes have already been updated in the protected vars. The only thing you will need to update on your local wrapper is the two different methods: `$this->setApiRoute('api/v1/urls?access_token='.$this->_tokenKey);` (notice the `$this->setApiRoute()`, instead of the old `$this->setRoute`).

`setBaseRoute()` will ONLY be used when generating the initial token.

This was a quick fix, so report any bugs you might find. Thanks!
