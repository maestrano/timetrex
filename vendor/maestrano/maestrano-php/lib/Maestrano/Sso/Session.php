<?php

/**
 * Helper class used to check the validity
 * of a Maestrano session
 */
class Maestrano_Sso_Session extends Maestrano_Util_PresetObject
{
  private $httpSession = null;
  private $uid = '';
  private $groupUid = '';
  private $sessionToken = '';
  private $recheck = null;
  protected $preset;

  /**
   * Construct the Maestrano_Sso_Session object
   */
  public function __construct(&$http_session, $user = null)
  {
    // Populate attributes from params
    $this->httpSession = &$http_session;

    if ($user != null) {
      // Setup the session with $user
      $this->uid = $user->getUid();
      $this->groupUid = $user->getGroupUid();
      $this->sessionToken = $user->getSsoSession();
      $this->recheck = $user->getSsoSessionRecheck();
    } else if ($this->ssoTokenExists()) {
      // Get maestrano sso token
      $mnoEntry = $this->httpSession['maestrano'];

      if ($mnoEntry != null) {
        // Decode the object
        $sessionObj = base64_decode($mnoEntry);
        $sessionObj = json_decode($sessionObj, true);

        // Setup the session
        $this->uid = $sessionObj['uid'];
        $this->groupUid = $sessionObj["group_uid"];
        $this->sessionToken = $sessionObj['session'];
        $this->recheck = new DateTime($sessionObj['session_recheck']);
      }
    }
  }

  /**
   * @param string $id The ID of the bill to instantiate.
   * @param string|null $apiToken
   *
   * @return Maestrano_Billing_Bill
   */
  public static function newWithPreset($preset, &$http_session, $user = null)
  {
    $obj = new Maestrano_Sso_Session($http_session, $user, $preset);
    $obj->_preset = $preset;
    return $obj;
  }

  /**
   * Check if the maestrano SSO token exists in the http session
   *
   * @return boolean
   */
  public function ssoTokenExists() {
    if ($this->httpSession != null && array_key_exists('maestrano', $this->httpSession)) {
      return true;
    }

    return false;
  }

  /**
   * Check whether we need to remotely check the
   * session or not
   *
   * @return boolean
   */
   public function isRemoteCheckRequired()
   {
     if ($this->uid && $this->sessionToken && $this->recheck) {
       if($this->recheck > (new DateTime('NOW'))) {
         return false;
       }
     }

     return true;
   }

   /**
    * Return the full url from which session check
    * should be performed
    *
    * @return string the endpoint url
    */
    public function getSessionCheckUrl()
    {
      $url = Maestrano::with($this->_preset)->sso()->getSessionCheckUrl($this->uid, $this->sessionToken);
      return $url;
    }

    /**
     * Fetch url and return content. Wrapper function.
     *
     * @param string full url to fetch
     * @return string page content
     */
    public function fetchUrl($url, $httpClient = null) {
      if ($httpClient == null) {
        $httpClient = new Maestrano_Net_HttpClient();
      }

      return $httpClient->get($url);
    }

    /**
     * Perform remote session check on Maestrano
     *
     * @return boolean the validity of the session
     */
    public function performRemoteCheck($httpClient = null) {
      if(empty($this->sessionToken)) { return false; }
      
      $json = $this->fetchUrl($this->getSessionCheckUrl(), $httpClient);
      if ($json) {
        $response = json_decode($json,true);

        if ($response['valid'] == "true" && $response['recheck'] != null) {
          $this->recheck = new DateTime($response['recheck']);
          return true;
        }
      }

      return false;
    }

  /**
  * Perform check to see if session is valid
  * Check is only performed if current time is after
  * the recheck timestamp
  * If a remote check is performed then the mno_session_recheck
  * timestamp is updated in session.
  *
  * @return boolean the validity of the session
  */
  public function isValid($ifSession = false, $httpClient = null) {
    if ($ifSession) { return true; }
    
    $svc = Maestrano::with($this->_preset)->sso();
    if (!$svc->isSloEnabled()) return true;

    if (!$this->ssoTokenExists() || $this->isRemoteCheckRequired()) {
      if ($this->performRemoteCheck($httpClient)) {
        $this->save();
        return true;
      } else {
        return false;
      }
    } else {
      return true;
    }
  }

  public function save() {
    // Set values
    $sessObj = array();
    $sessObj['uid'] = $this->uid;
    $sessObj['group_uid'] = $this->groupUid;
    $sessObj['session'] = $this->sessionToken;
    $sessObj['session_recheck'] = $this->recheck->format(DateTime::ISO8601);

    $sessionStr = json_encode($sessObj);
    $sessionStr = base64_encode($sessionStr);

    $this->httpSession['maestrano'] = $sessionStr;
  }

  public function getUid() {
    return $this->uid;
  }

  public function getGroupUid() {
    return $this->groupUid;
  }

  public function getRecheck() {
    return $this->recheck;
  }

  public function getSessionToken() {
    return $this->sessionToken;
  }

  public function getHttpSession() {
    return $this->httpSession;
  }

  public function setUid($uid) {
    $this->uid = $this->uid;
  }

  public function setGroupUid($groupUid) {
    $this->groupUid = $groupUid;
  }

  public function setRecheck($recheck) {
    $this->recheck = $recheck;
  }

  public function setSessionToken($sessionToken) {
    $this->sessionToken = $sessionToken;
  }

  public function setHttpSession($httpSession) {
    $this->httpSession = $httpSession;
  }
}
