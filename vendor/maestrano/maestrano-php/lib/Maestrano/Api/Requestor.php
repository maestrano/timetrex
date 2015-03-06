<?php

class Maestrano_Api_Requestor
{
  /**
   * @var string $apiToken The API key that's to be used to make requests.
   */
  public $apiToken;

  private static $preFlight;

  private static function blacklistedCerts()
  {
    return array();
  }

  public function __construct($apiToken=null)
  {
    $this->_apiToken = $apiToken;
  }

  /**
   * @param string $url The path to the API endpoint.
   *
   * @returns string The full path.
   */
  public static function apiUrl($url='')
  {
    $apiBase = Maestrano::param('api.host');
    return "$apiBase$url";
  }

  /**
   * @param string|mixed $value A string to UTF8-encode.
   *
   * @returns string|mixed The UTF8-encoded string, or the object passed in if
   *    it wasn't a string.
   */
  public static function utf8($value)
  {
    if (is_string($value)
        && mb_detect_encoding($value, "UTF-8", TRUE) != "UTF-8") {
      return utf8_encode($value);
    } else {
      return $value;
    }
  }

  private static function _encodeObjects($d)
  {
    if ($d instanceof Maestrano_Api_Resource) {
      return self::utf8($d->id);
    } else if ($d === true) {
      return 'true';
    } else if ($d === false) {
      return 'false';
    } else if (is_array($d)) {
      $res = array();
      foreach ($d as $k => $v)
              $res[$k] = self::_encodeObjects($v);
      return $res;
    } else {
      return self::utf8($d);
    }
  }

  /**
   * @param array $arr An map of param keys to values.
   * @param string|null $prefix (It doesn't look like we ever use $prefix...)
   *
   * @returns string A querystring, essentially.
   */
  public static function encode($arr, $prefix=null)
  {
    if (!is_array($arr))
      return $arr;

    $r = array();
    foreach ($arr as $k => $v) {
      if (is_null($v))
        continue;

      if ($prefix && $k && !is_int($k))
        $k = $prefix."[".$k."]";
      else if ($prefix)
        $k = $prefix."[]";

      if (is_array($v)) {
        $r[] = self::encode($v, $k, true);
      } else {
        $r[] = urlencode($k)."=".urlencode($v);
      }
    }

    return implode("&", $r);
  }

  /**
   * @param string $method
   * @param string $url
   * @param array|null $params
   *
   * @return array An array whose first element is the response and second
   *    element is the API key used to make the request.
   */
  public function request($method, $url, $params=null)
  {
    if (!$params)
      $params = array();
    list($rbody, $rcode, $myApiToken) = $this->_requestRaw($method, $url, $params);
    $resp = $this->_interpretResponse($rbody, $rcode);
    return array($resp['data'], $myApiToken);
  }


  /**
   * @param string $rbody A JSON string.
   * @param int $rcode
   * @param array $resp
   *
   * @throws Maestrano_Api_InvalidRequestError if the error is caused by the user.
   * @throws Maestrano_Api_AuthenticationError if the error is caused by a lack of
   *    permissions.
   * @throws Maestrano_CardError if the error is the error code is 402 (payment
   *    required)
   * @throws Maestrano_Api_Error otherwise.
   */
  public function handleApiError($rbody, $rcode, $resp)
  {
    if (!is_array($resp) || !isset($resp['error'])) {
      $msg = "Invalid response object from API: $rbody "
           ."(HTTP response code was $rcode)";
      throw new Maestrano_Api_Error($msg, $rcode, $rbody, $resp);
    }

    $error = $resp['error'];
    $msg = isset($error['message']) ? $error['message'] : null;
    $param = isset($error['param']) ? $error['param'] : null;
    $code = isset($error['code']) ? $error['code'] : null;

    switch ($rcode) {
    case 400:
        if ($code == 'rate_limit') {
          throw new Maestrano_RateLimitError(
              $msg, $param, $rcode, $rbody, $resp
          );
        }
    case 404:
        throw new Maestrano_Api_InvalidRequestError(
            $msg, $param, $rcode, $rbody, $resp
        );
    case 401:
        throw new Maestrano_Api_AuthenticationError($msg, $rcode, $rbody, $resp);
    case 402:
        throw new Maestrano_CardError($msg, $param, $code, $rcode, $rbody, $resp);
    default:
        throw new Maestrano_Api_Error($msg, $rcode, $rbody, $resp);
    }
  }

  private function _requestRaw($method, $url, $params)
  {
    $myApiToken = $this->_apiToken;
    if (!$myApiToken)
      $myApiToken = Maestrano::param('api.token');

    if (!$myApiToken) {
      $msg = 'No API token provided.';
      throw new Maestrano_Api_AuthenticationError($msg);
    }

    $absUrl = $this->apiUrl($url);
    $params = self::_encodeObjects($params);
    $langVersion = phpversion();
    $uname = php_uname();
    $ua = array('bindings_version' => Maestrano::VERSION,
                'lang' => 'php',
                'lang_version' => $langVersion,
                'publisher' => 'maestrano',
                'uname' => $uname);
    
    $headers = array('X-Maestrano-Client-User-Agent: ' . json_encode($ua),
                     'User-Agent: Maestrano/v1 PhpBindings/' . Maestrano::VERSION,
                     'Authorization: Basic ' . base64_encode($myApiToken));
    
    list($rbody, $rcode) = $this->_curlRequest(
        $method,
        $absUrl,
        $headers,
        $params
    );
    return array($rbody, $rcode, $myApiToken);
  }

  private function _interpretResponse($rbody, $rcode)
  {
    try {
      $resp = json_decode($rbody, true);
    } catch (Exception $e) {
      $msg = "Invalid response body from API: $rbody "
           . "(HTTP response code was $rcode)";
      throw new Maestrano_Api_Error($msg, $rcode, $rbody);
    }

    if ($rcode < 200 || $rcode >= 300) {
      $this->handleApiError($rbody, $rcode, $resp);
    }
    return $resp;
  }

  private function _curlRequest($method, $absUrl, $headers, $params)
  {

    if (!self::$preFlight) {
      #self::$preFlight = $this->checkSslCert($this->apiUrl());
    }

    $curl = curl_init();
    $method = strtolower($method);
    $opts = array();
    if ($method == 'get') {
      $opts[CURLOPT_HTTPGET] = 1;
      if (count($params) > 0) {
        $encoded = self::encode($params);
        $absUrl = "$absUrl?$encoded";
      }
    } else if ($method == 'post') {
      $opts[CURLOPT_POST] = 1;
      $opts[CURLOPT_POSTFIELDS] = self::encode($params);
    } else if ($method == 'delete') {
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
    $opts[CURLOPT_RETURNTRANSFER] = true;
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
    return array($rbody, $rcode);
  }

  /**
   * @param number $errno
   * @param string $message
   * @throws Maestrano_Api_ConnectionError
   */
  public function handleCurlError($errno, $message)
  {
    $apiBase = Maestrano::param('api.host');
    switch ($errno) {
    case CURLE_COULDNT_CONNECT:
    case CURLE_COULDNT_RESOLVE_HOST:
    case CURLE_OPERATION_TIMEOUTED:
      $msg = "Could not connect to Maestrano ($apiBase).  Please check your "
           . "internet connection and try again.  If this problem persists, "
           . "you should check Maestrano's service status at "
           . "https://twitter.com/Maestrano, or";
        break;
    case CURLE_SSL_CACERT:
    case CURLE_SSL_PEER_CERTIFICATE:
      $msg = "Could not verify Maestrano's SSL certificate.  Please make sure "
           . "that your network is not intercepting certificates.  "
           . "(Try going to $apiBase in your browser.)  "
           . "If this problem persists,";
        break;
    default:
      $msg = "Unexpected error communicating with Maestrano.  "
           . "If this problem persists,";
    }
    $msg .= " let us know at support@Maestrano.com.";

    $msg .= "\n\n(Network error [errno $errno]: $message)";
    throw new Maestrano_Api_ConnectionError($msg);
  }

  private function checkSslCert($url)
  {
   /* Preflight the SSL certificate presented by the backend. This isn't 100%
    * bulletproof, in that we're not actually validating the transport used to
    * communicate with Maestrano, merely that the first attempt to does not use a
    * revoked certificate.

    * Unfortunately the interface to OpenSSL doesn't make it easy to check the
    * certificate before sending potentially sensitive data on the wire. This
    * approach raises the bar for an attacker significantly.
    */

    if (version_compare(PHP_VERSION, '5.3.0', '<')) {
      error_log("Warning: This version of PHP is too old to check SSL certificates correctly. " .
                "Maestrano cannot guarantee that the server has a certificate which is not blacklisted");
      return true;
    }

    $url = parse_url($url);
    $port = isset($url["port"]) ? $url["port"] : 443;
    $url = "ssl://{$url["host"]}:{$port}";

    $sslContext = stream_context_create(array( 'ssl' => array(
          'capture_peer_cert' => true,
          'verify_peer'   => true,
          'cafile'        => $this->caBundle(),
        )));
    $result = stream_socket_client($url, $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $sslContext);
    if ($errno !== 0) {
        throw new Maestrano_Api_ConnectionError(
             "Could not connect to Maestrano ($apiBase).  Please check your "
           . "internet connection and try again.  If this problem persists, "
           . "you should check Maestrano's service status at "
           . "https://twitter.com/Maestrano. Reason was: $errstr"
       );
    }

    $params = stream_context_get_params($result);

    $cert = $params['options']['ssl']['peer_certificate'];
    $cert_data = openssl_x509_parse( $cert );

    openssl_x509_export($cert, $pem_cert);

    if (self::isBlackListed($pem_cert)) {
        throw new Maestrano_Api_ConnectionError(
            "Invalid server certificate. You tried to connect to a server that has a " .
            "revoked SSL certificate, which means we cannot securely send data to " .
            "that server.  Please email support@maestrano.com if you need help " .
            "connecting to the correct API server."
        );
    }

    return true;
  }

  /* Checks if a valid PEM encoded certificate is blacklisted
   * @return boolean
   */
  public static function isBlackListed($certificate)
  {
    $certificate = trim($certificate);
    $lines = explode("\n", $certificate);

    // Kludgily remove the PEM padding
    array_shift($lines); array_pop($lines);

    $der_cert = base64_decode(implode("", $lines));
    $fingerprint = sha1($der_cert);
    return in_array($fingerprint, self::blacklistedCerts());
  }

  private function caBundle()
  {
    return dirname(__FILE__) . '/../data/ca-certificates.crt';
  }
}
