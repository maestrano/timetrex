<?php
  
class Maestrano_Net_HttpClient
{
  public function get($url) {
    return file_get_contents($url);
  }
}
  
?>