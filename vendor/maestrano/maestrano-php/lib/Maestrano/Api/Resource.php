<?php

abstract class Maestrano_Api_Resource extends Maestrano_Api_Object
{
  protected static function _scopedRetrieve($class, $id, $preset=null)
  {
    $instance = new $class($id, $preset);
    $instance->refresh();
    return $instance;
  }

  /**
   * @returns Maestrano_Api_Resource The refreshed resource.
   */
  public function refresh()
  {
    $requestor = new Maestrano_Api_Requestor($this->_preset);
    $url = $this->instanceUrl();

    list($response, $apiToken) = $requestor->request(
        'get',
        $url,
        $this->_retrieveOptions
    );
    $this->refreshFrom($response, $this->_preset);
    return $this;
  }

  protected function getRelated($subpath, $params=null, $preset=null)
  {
    $realParams = $params;
    if ($realParams && is_array($params)) {
      $realParams = Maestrano_Api_Util::convertMaestranoObjectToArray($params);
    }

    $requestor = new Maestrano_Api_Requestor($preset);
    $url = $this->instanceUrl() . $subpath;
    list($response, $apiToken) = $requestor->request('get', $url, $realParams);
    return Maestrano_Api_Util::convertToMaestranoObject($response, $this->_preset);
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

  private static function _validateCall($method, $params=null, $preset=null)
  {
    if ($params && !is_array($params)) {
      $message = "You must pass an array as the first argument to Maestrano API "
               . "method calls.";
      throw new Maestrano_Api_Error($message);
    }

    if ($preset && !is_string($preset)) {
      $message = 'The second argument to Maestrano API method calls is an '
               . 'optional per-request apiKey, which must be a string.  ';
      throw new Maestrano_Api_Error($message);
    }
  }

  protected static function _scopedAll($class, $params=null, $preset=null)
  {
    $realParams = $params;
    if ($realParams && is_array($params)) {
      $realParams = Maestrano_Api_Util::convertMaestranoObjectToArray($params,$preset);
    }

    self::_validateCall('all', $realParams, $preset);
    $requestor = new Maestrano_Api_Requestor($preset);
    $url = self::_scopedLsb($class, 'classUrl', $class);
    list($response, $apiToken) = $requestor->request('get', $url, $realParams);

    return Maestrano_Api_Util::convertToMaestranoObject($response, $preset);
  }

  protected static function _scopedCreate($class, $params=null, $preset=null)
  {
    self::_validateCall('create', $params, $preset);
    $requestor = new Maestrano_Api_Requestor($preset);
    $url = self::_scopedLsb($class, 'classUrl', $class);

    $realParams = Maestrano_Api_Util::convertMaestranoObjectToArray($params);

    list($response, $apiToken) = $requestor->request('post', $url, $realParams);
    return Maestrano_Api_Util::convertToMaestranoObject($response, $preset);
  }

  protected function _scopedSave($class)
  {
    self::_validateCall('save');
    $requestor = new Maestrano_Api_Requestor($this->_preset);
    $params = $this->serializeParameters();

    if (count($params) > 0) {
      $url = $this->instanceUrl();
      $realParams = Maestrano_Api_Util::convertMaestranoObjectToArray($params);
      list($response, $apiToken) = $requestor->request('post', $url, $params);
      $this->refreshFrom($response, $this->_preset);
    }
    return $this;
  }

  protected function _scopedDelete($class, $params=null)
  {
    self::_validateCall('delete');
    $requestor = new Maestrano_Api_Requestor($this->_preset);
    $url = $this->instanceUrl();
    list($response, $apiToken) = $requestor->request('delete', $url, $params);
    $this->refreshFrom($response, $this->_preset);
    return $this;
  }
}
