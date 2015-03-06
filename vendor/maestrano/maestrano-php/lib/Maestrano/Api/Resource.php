<?php

abstract class Maestrano_Api_Resource extends Maestrano_Api_Object
{
  protected static function _scopedRetrieve($class, $id, $apiToken=null)
  {
    $instance = new $class($id, $apiToken);
    $instance->refresh();
    return $instance;
  }

  /**
   * @returns Maestrano_Api_Resource The refreshed resource.
   */
  public function refresh()
  {
    $requestor = new Maestrano_Api_Requestor($this->_apiToken);
    $url = $this->instanceUrl();

    list($response, $apiToken) = $requestor->request(
        'get',
        $url,
        $this->_retrieveOptions
    );
    $this->refreshFrom($response, $apiToken);
    return $this;
  }

  /**
   * @param string $class
   *
   * @returns string The name of the class, with namespacing and underscores
   *    stripped.
   */
  public static function className($class)
  {
    // Useful for namespaces: Foo\Maestrano_Charge
    if ($postfix = strrchr($class, '\\')) {
      $class = substr($postfix, 1);
    }
    if (substr($class, 0, strlen('Maestrano')) == 'Maestrano') {
      $class = substr($class, strlen('Maestrano'));
    }
    $class = str_replace('_', '', $class);
    $name = urlencode($class);
    $name = strtolower($name);
    return $name;
  }

  /**
   * @param string $class
   *
   * @returns string The endpoint URL for the given class.
   */
  public static function classUrl($class)
  {
    $base = self::_scopedLsb($class, 'className', $class);
    return "/v1/${base}s";
  }

  /**
   * @returns string The full API URL for this API resource.
   */
  public function instanceUrl()
  {
    $id = $this['id'];
    $class = get_class($this);
    if ($id === null) {
      $message = "Could not determine which URL to request: "
               . "$class instance has invalid ID: $id";
      throw new Maestrano_Api_InvalidRequestError($message, null);
    }
    $id = Maestrano_Api_Requestor::utf8($id);
    $base = $this->_lsb('classUrl', $class);
    $extn = urlencode($id);
    return "$base/$extn";
  }

  private static function _validateCall($method, $params=null, $apiToken=null)
  {
    if ($params && !is_array($params)) {
      $message = "You must pass an array as the first argument to Maestrano API "
               . "method calls.";
      throw new Maestrano_Api_Error($message);
    }

    if ($apiToken && !is_string($apiToken)) {
      $message = 'The second argument to Maestrano API method calls is an '
               . 'optional per-request apiKey, which must be a string.  ';
      throw new Maestrano_Api_Error($message);
    }
  }

  protected static function _scopedAll($class, $params=null, $apiToken=null)
  {
    $realParams = $params;
    if ($realParams && is_array($params)) {
      $realParams = Maestrano_Api_Util::convertMaestranoObjectToArray($params);
    }
    
    self::_validateCall('all', $realParams, $apiToken);
    $requestor = new Maestrano_Api_Requestor($apiToken);
    $url = self::_scopedLsb($class, 'classUrl', $class);
    list($response, $apiToken) = $requestor->request('get', $url, $realParams);
    return Maestrano_Api_Util::convertToMaestranoObject($response, $apiToken);
  }

  protected static function _scopedCreate($class, $params=null, $apiToken=null)
  {
    self::_validateCall('create', $params, $apiToken);
    $requestor = new Maestrano_Api_Requestor($apiToken);
    $url = self::_scopedLsb($class, 'classUrl', $class);
    
    $realParams = Maestrano_Api_Util::convertMaestranoObjectToArray($params);
    
    list($response, $apiToken) = $requestor->request('post', $url, $realParams);
    return Maestrano_Api_Util::convertToMaestranoObject($response, $apiToken);
  }

  protected function _scopedSave($class, $apiToken=null)
  {
    self::_validateCall('save');
    $requestor = new Maestrano_Api_Requestor($apiToken);
    $params = $this->serializeParameters();

    if (count($params) > 0) {
      $url = $this->instanceUrl();
      $realParams = Maestrano_Api_Util::convertMaestranoObjectToArray($params);
      list($response, $apiToken) = $requestor->request('post', $url, $params);
      $this->refreshFrom($response, $apiToken);
    }
    return $this;
  }

  protected function _scopedDelete($class, $params=null)
  {
    self::_validateCall('delete');
    $requestor = new Maestrano_Api_Requestor($this->_apiToken);
    $url = $this->instanceUrl();
    list($response, $apiToken) = $requestor->request('delete', $url, $params);
    $this->refreshFrom($response, $apiToken);
    return $this;
  }
}
