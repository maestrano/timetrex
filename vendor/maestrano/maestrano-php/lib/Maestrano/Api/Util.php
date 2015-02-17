<?php

abstract class Maestrano_Api_Util
{
  /**
   * Whether the provided array (or other) is a list rather than a dictionary.
   *
   * @param array|mixed $array
   * @return boolean True if the given object is a list.
   */
  public static function isList($array)
  {
    if (!is_array($array))
      return false;

    // TODO: generally incorrect, but it's correct given Maestrano's response
    foreach (array_keys($array) as $k) {
      if (!is_numeric($k))
        return false;
    }
    return true;
  }
  
  public static function toUnderscore($string) {
    return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $string));
  }

  /**
   * Recursively converts the PHP Maestrano object to an array.
   *
   * @param array $values The PHP Maestrano object to convert.
   * @return array
   */
  public static function convertMaestranoObjectToArray($values)
  {
    $results = array();
    foreach ($values as $k => $v) {
      // FIXME: this is an encapsulation violation
      if ($k[0] == '_') {
        continue;
      }
      
      // Convert key to underscore
      $kReal = self::toUnderscore($k);
      
      if ($v instanceof Maestrano_Api_Object) {
        $results[$kReal] = $v->__toArray(true);
      } else if (is_array($v)) {
        $results[$kReal] = self::convertMaestranoObjectToArray($v);
      } else if ($v instanceOf DateTime) {
        $results[$kReal] = $v->format(Maestrano_Helper_DateTime::ISO8601);
      } else {
        $results[$kReal] = $v;
      }
    }
    return $results;
  }

  /**
   * Converts a response from the Maestrano API to the corresponding PHP object.
   *
   * @param array $resp The response from the Maestrano API.
   * @param string $apiToken
   * @return Maestrano_Api_Object|array
   */
  public static function convertToMaestranoObject($resp, $apiToken)
  {
    $types = array(
      'account_bill' => 'Maestrano_Account_Bill',
      'account_recurring_bill' => 'Maestrano_Account_RecurringBill',
      'account_group' => 'Maestrano_Account_Group',
      'account_user' => 'Maestrano_Account_User',
    );
    
    if (self::isList($resp)) {
      $mapped = array();
      foreach ($resp as $i)
        array_push($mapped, self::convertToMaestranoObject($i, $apiToken));
      return $mapped;
    
    } else if (is_array($resp)) {
      if (isset($resp['object']) 
          && is_string($resp['object'])
          && isset($types[$resp['object']])) {
        
        $class = $types[$resp['object']];
      } else {
        $class = 'Maestrano_Api_Object';
      }
      return Maestrano_Api_Object::scopedConstructFrom($class, $resp, $apiToken);
    } else {
      // Automatically convert dates
      if (preg_match('/\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $resp)) {
        return new DateTime($resp);
      } else {
        return $resp;
      }
    }
  }
}
