<?php
  
class SessionTestHelper {
  
  public static function setMnoEntry(& $httpSession, $array) {
    $httpSession['maestrano'] = base64_encode(json_encode($array));
  }
  
}
  
?>