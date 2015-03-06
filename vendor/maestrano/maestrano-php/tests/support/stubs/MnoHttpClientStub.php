<?php
  
class MnoHttpClientStub extends Maestrano_Net_HttpClient
{
  private $responseStubs;
  private $defaultResponseStub;
  
  public function __construct()
  {
    $this->responseStubs = array();
  }
  
	public function get($url) {
		return $this->getResponseStub($url,null,null);
	}
  
	public function getResponseStub($url, $params = null, $payload = null) {
    $keyStr = $url;
    $paramsStr = null;
    if ($params != null) {
      $paramsStr = json_encode($params);
    }
		
		if ($paramsStr != null) {
			$keyStr += "?" + $paramsStr;
		}
		
		if ($payload != null) {
			$keyStr += "@@payload@@" + $payload;
		}
		
    if ($this->responseStubs[$keyStr] == null) {
      return $this->defaultResponseStub;
    } else {
      return $this->responseStubs[$keyStr];
    }
	}
  
	public function setResponseStub($responseStub,$url = null, $params = null, $payload = null) {
		if ($url == null) {
		  $this->defaultResponseStub = json_encode($responseStub);
      return true;
		}
    
    $keyStr = $url;
    $paramsStr = null;
    if ($params != null) {
      $paramsStr = json_encode($params);
    }
		
		if ($paramsStr != null) {
			$keyStr += "?" + $paramsStr;
		}
		
		if ($payload != null) {
			$keyStr += "@@payload@@" + $payload;
		}
		
		$this->responseStubs[$keyStr] = json_encode($responseStub);
	}
}
  
?>