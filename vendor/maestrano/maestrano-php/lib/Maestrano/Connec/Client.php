<?php

/**
 * Maestrano Connec! HTTP Client.
 */
class Maestrano_Connec_Client extends Maestrano_Util_PresetObject
{
  private $group_id;
  protected $_preset;

  /**
   * Constructor
   * @param group_id The customer group id (defaults to Maestrano configuration)
   */
  public function __construct($group_id = null) {
    $this->group_id = $group_id;
  }

  /**
   * @param string $id The ID of the bill to instantiate.
   * @param string|null $apiToken
   *
   * @return Maestrano_Billing_Bill
   */
  public static function newWithPreset($preset, $group_id = null)
  {
    $obj = new Maestrano_Connec_Client($group_id);
    $obj->_preset = $preset;
    return $obj;
  }

  public function getBaseUrl() {
    return Maestrano::with($this->_preset)->param('connec.host') . Maestrano::with($this->_preset)->param('connec.base_path');
  }

  public function getV2Path() {
    return Maestrano::with($this->_preset)->param('connec.v2_path');
  }

  public function getReportsPath() {
    return Maestrano::with($this->_preset)->param('connec.v2_path');
  }

  public function getGroupId() {
    if(!is_null($this->group_id)) {
      return $this->group_id;
    } else {
      return Maestrano::with($this->_preset)->param('api.group_id');
    }
  }

  /**
   * Perform a GET request to Connec!
   *
   * @param relativePath The relative path to the resource or resource collection
   * @param params Optional filtering parameters
   * @return associative array describing the response. E.g. ( 'code' => 200, 'body' => {...} )
   */
  public function get($relativePath, $params = null) {
    return $this->_curlRequest(
      'GET',
      $this->scopedUrl($this->getV2Path(), $relativePath),
      $this->defaultHeaders(),
      $params
    );
  }

  /**
   * Perform a GET request to Connec! reports
   *
   * @param relativePath The relative path to the report
   * @param params Optional filtering parameters
   * @return associative array describing the response. E.g. ( 'code' => 200, 'body' => {...} )
   */
  public function getReport($relativePath, $params = null) {
    return $this->_curlRequest(
        'GET',
        $this->scopedUrl($this->getReportsPath(), $relativePath),
        $this->defaultHeaders(),
        $params
    );
  }

  /**
   * Perform a POST request to Connec!
   *
   * @param relativePath The relative path to the resource or resource collection
   * @param attributes Associative array of attributes
   * @return associative array describing the response. E.g. ( 'code' => 200, 'body' => {...} )
   */
  public function post($relativePath, $attributes = null) {
    return $this->_curlRequest(
      'POST',
      $this->scopedUrl($this->getV2Path(), $relativePath),
      $this->defaultHeaders(),
      $attributes
    );
  }

  /**
   * Perform a PUT request to Connec!
   *
   * @param relativePath The relative path to the resource or resource collection
   * @param attributes Associative array of attributes
   * @return associative array describing the response. E.g. ( 'code' => 200, 'body' => {...} )
   */
  public function put($relativePath, $attributes = null) {
    return $this->_curlRequest(
      'PUT',
      $this->scopedUrl($this->getV2Path(), $relativePath),
      $this->defaultHeaders(),
      $attributes
    );
  }


  /**
   * @return array The default HTTP headers
   */
  private function defaultHeaders() {
    $apiToken = Maestrano::param('api.token');

    return array(
      'Authorization: Basic ' . base64_encode($apiToken),
      'Accept: ' . 'application/vnd.api+json',
      'Content-Type: ' . 'application/vnd.api+json',
      'Connec-Country-Format: alpha2'
    );
  }

  /**
   * @param relativePath the API resource path. E.g. '/organizations'
   * @return String the relative path prefixed with the group_id
   */
  private function scopedPath($relativePath) {
    $clean_path = preg_replace('/^\/+/','',$relativePath);
    $clean_path = preg_replace('/\/+$/','',$clean_path);

    return "/" . $this->getGroupId() . "/" . $clean_path;
  }


  /**
   * @param $api the API to use (eg. v2 or reports)
   * @param $relativePath the API resource path. E.g. '/organizations'
   * @return string the absolute url to the resource
   */
  private function scopedUrl($api, $path) {
    if (preg_match("/https?\:\/\/.*/i", $path)) {
      return $path;
    } else {
      return $this->getBaseUrl() . $api . $this->scopedPath($path);
    }
  }

  /**
   * @param array $arr An map of param keys to values.
   *
   * @return string A querystring, essentially.
   */
  public static function encode($arr) {
    if (!is_array($arr))
      return $arr;

    $r = array();
    foreach ($arr as $k => $v) {
      if (is_null($v))
        continue;

      if (is_array($v)) {
        $r[] = self::encode($v, $k, true);
      } else {
        $r[] = urlencode($k)."=".urlencode($v);
      }
    }

    return implode("&", $r);
  }

  /**
   * @param string|mixed $value A string to UTF8-encode.
   *
   * @return string|mixed The UTF8-encoded string, or the object passed in if
   *    it wasn't a string.
   */
  public static function utf8($value) {
    if (is_string($value)
        && mb_detect_encoding($value, "UTF-8", TRUE) != "UTF-8") {
      return utf8_encode($value);
    } else {
      return $value;
    }
  }

  private function _curlRequest($method, $absUrl, $headers, $params) {
    $curl = curl_init();
    $method = strtoupper($method);
    $opts = array();
    if ($method == 'GET') {
      $opts[CURLOPT_HTTPGET] = 1;
      if (count($params) > 0) {
        $encoded = self::encode($params);
        $absUrl = "$absUrl?$encoded";
      }
    } else if ($method == 'POST') {
      $opts[CURLOPT_POST] = 1;
      $opts[CURLOPT_POSTFIELDS] = json_encode($params);

    } else if ($method == 'PUT') {
      $opts[CURLOPT_CUSTOMREQUEST] = "PUT";
      $opts[CURLOPT_POSTFIELDS] = json_encode($params);

    } else if ($method == 'DELETE') {
      $opts[CURLOPT_CUSTOMREQUEST] = 'DELETE';
      if (count($params) > 0) {
        $encoded = self::encode($params);
        $absUrl = "$absUrl?$encoded";
      }
    } else {
      throw new Maestrano_Api_Error("Unrecognized method $method");
    }

    $absUrl = self::utf8($absUrl);
    $opts[CURLOPT_URL] = $absUrl;
    $opts[CURLOPT_RETURNTRANSFER] = true;
    $opts[CURLOPT_CONNECTTIMEOUT] = 30;
    $opts[CURLOPT_TIMEOUT] = 80;
    $opts[CURLOPT_HTTPHEADER] = $headers;
    if (!Maestrano::param('verify_ssl_certs'))
      $opts[CURLOPT_SSL_VERIFYPEER] = false;

    curl_setopt_array($curl, $opts);
    $rbody = curl_exec($curl);

    if (!defined('CURLE_SSL_CACERT_BADFILE')) {
      define('CURLE_SSL_CACERT_BADFILE', 77);  // constant not defined in PHP
    }

    $errno = curl_errno($curl);
    if ($errno == CURLE_SSL_CACERT ||
        $errno == CURLE_SSL_PEER_CERTIFICATE ||
        $errno == CURLE_SSL_CACERT_BADFILE) {
      array_push(
          $headers,
          'X-Maestrano-Client-Info: {"ca":"using Maestrano-supplied CA bundle"}'
      );
      $cert = $this->caBundle();
      curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
      curl_setopt($curl, CURLOPT_CAINFO, $cert);
      $rbody = curl_exec($curl);
    }

    if ($rbody === false) {
      $errno = curl_errno($curl);
      $message = curl_error($curl);
      curl_close($curl);
      $this->handleCurlError($errno, $message);
    }

    $rcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    return array( 'body' => $rbody, 'code' => $rcode);
  }

  private function handleCurlError($errno, $message)
  {
    throw new Maestrano_Api_Error("curl_errno: $errno, message: $message");
  }
}
