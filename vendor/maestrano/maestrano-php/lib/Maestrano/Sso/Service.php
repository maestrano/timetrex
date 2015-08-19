<?php

/**
 * SSO Service
 */
class Maestrano_Sso_Service extends Maestrano_Util_PresetObject
{
  /* Singleton instance */
  protected static $_instances = array();

  /* Path to redirect to after signin */
  protected $after_sso_sign_in_path = '/';

  /* Pointer to the current client session */
  protected $client_session;

  /**
   * Returns an instance of this class
   * (this class uses the singleton pattern)
   *
   * @return Maestrano_Sso_Service
   */
  public static function instanceWithPreset($preset) {
      if (!array_key_exists($preset, self::$_instances) || is_null(self::$_instances[$preset])) {
          self::$_instances[$preset] = new self($preset);
      }
      return self::$_instances[$preset];
  }

  public function __construct($preset)
  {
    $this->_preset = $preset;
  }

  /**
   * Check if Maestrano SSO is enabled
   *
   * @return boolean
   */
   public function isSsoEnabled() {
     return Maestrano::with($this->_preset)->param('sso.enabled');
   }

   /**
    * Check if Maestrano SLO is enabled
    *
    * @return boolean
    */
    public function isSloEnabled() {
      return Maestrano::with($this->_preset)->param('sso.slo_enabled');
    }

  /**
   * Return the app used to initiate
   * SSO request
   *
   * @return boolean
   */
  public function getInitPath() {
    return Maestrano::with($this->_preset)->param('sso.init_path');
  }

  /**
   * Return where the app should redirect internally to initiate
   * SSO request
   *
   * @return boolean
   */
  public function getInitUrl() {
    $host = Maestrano::with($this->_preset)->param('app.host');
    $path = $this->getInitPath();
    return "${host}${path}";
  }

  /**
   * The path where the SSO response will be posted and consumed.
   * @var string
   */
  public function getConsumePath() {
    return Maestrano::with($this->_preset)->param('sso.consume_path');
  }

  /**
   * The URL where the SSO response will be posted and consumed.
   * @var string
   */
  public function getConsumeUrl() {
    $host = Maestrano::with($this->_preset)->param('app.host');
    $path = $this->getConsumePath();
    return "${host}${path}";
  }

  /**
   * Return where the app should redirect after logging user
   * out
   *
   * @return string url
   */
  public function getLogoutUrl() {
    $host = Maestrano::with($this->_preset)->param('sso.idp');
    $endpoint = '/app_logout';

    return "${host}${endpoint}";
  }

  /**
   * Return where the app should redirect if user does
   * not have access to it
   *
   * @return string url
   */
  public function getUnauthorizedUrl() {
    $host = Maestrano::with($this->_preset)->param('api.host');
    $endpoint = '/app_access_unauthorized';

    return "${host}${endpoint}";
  }

  /**
   * Maestrano Single Sign-On processing URL
   * @var string
   */
  public function getIdpUrl() {
    $host = Maestrano::with($this->_preset)->param('sso.idp');
    $api_base = Maestrano::with($this->_preset)->param('api.base');
    $endpoint = 'auth/saml';
    return "${host}${api_base}${endpoint}";
  }

  /**
   * The Maestrano endpoint in charge of providing session information
   * @var string
   */
  public function getSessionCheckUrl($user_id,$sso_session)  {
    $host = Maestrano::with($this->_preset)->param('sso.idp');
    $api_base = Maestrano::with($this->_preset)->param('api.base');
    $endpoint = 'auth/saml';

    return "${host}${api_base}${endpoint}/${user_id}?session=${sso_session}";
  }

  /**
   * Return a settings object for php-saml
   *
   * @return Maestrano_Saml_Settings
   */
  public function getSamlSettings() {
    $settings = new Maestrano_Saml_Settings();

    // Configure SAML
    $settings->idpPublicCertificate = Maestrano::with($this->_preset)->param('sso.x509_certificate');
    $settings->spIssuer = Maestrano::with($this->_preset)->param('api.id');
    $settings->requestedNameIdFormat = Maestrano::with($this->_preset)->param('sso.name_id_format');
    $settings->idpSingleSignOnUrl = $this->getIdpUrl();
    $settings->spReturnUrl = $this->getConsumeUrl();

    return $settings;
  }
}
