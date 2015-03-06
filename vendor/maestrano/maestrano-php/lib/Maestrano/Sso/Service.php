<?php

/**
 * SSO Service
 */
class Maestrano_Sso_Service
{
  /* Singleton instance */
  protected static $_instance;
  
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
  public static function instance() {
      if ( ! isset(self::$_instance)) {
          self::$_instance = new self();
      }
      return self::$_instance;
  }

  /**
   * Check if Maestrano SSO is enabled
   *
   * @return boolean
   */
   public function isSsoEnabled() {
     return Maestrano::param('sso.enabled');
   }
   
   /**
    * Check if Maestrano SLO is enabled
    *
    * @return boolean
    */
    public function isSloEnabled() {
      return Maestrano::param('sso.slo_enabled');
    }
  
  /**
   * Return the app used to initiate
   * SSO request
   *
   * @return boolean
   */
  public function getInitPath() {
    return Maestrano::param('sso.init_path');
  }
  
  /**
   * Return where the app should redirect internally to initiate
   * SSO request
   *
   * @return boolean
   */
  public function getInitUrl() {
    $host = Maestrano::param('app.host');
    $path = $this->getInitPath();
    return "${host}${path}";
  }
  
  /**
   * The path where the SSO response will be posted and consumed.
   * @var string
   */
  public function getConsumePath() {
    return Maestrano::param('sso.consume_path');
  }
  
  /**
   * The URL where the SSO response will be posted and consumed.
   * @var string
   */
  public function getConsumeUrl() {
    $host = Maestrano::param('app.host');
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
    $host = Maestrano::param('sso.idp');
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
    $host = Maestrano::param('api.host');
    $endpoint = '/app_access_unauthorized';
    
    return "${host}${endpoint}";
  }
  
  /**
   * Maestrano Single Sign-On processing URL
   * @var string
   */
  public function getIdpUrl() {
    $host = Maestrano::param('sso.idp');
    $api_base = Maestrano::param('api.base');
    $endpoint = 'auth/saml';
    return "${host}${api_base}${endpoint}";
  }
  
  /**
   * The Maestrano endpoint in charge of providing session information
   * @var string
   */
  public function getSessionCheckUrl($user_id,$sso_session)  {
    $host = Maestrano::param('sso.idp');
    $api_base = Maestrano::param('api.base');
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
    $settings->idpPublicCertificate = Maestrano::param('sso.x509_certificate');
    $settings->spIssuer = Maestrano::param('api.id');
    $settings->requestedNameIdFormat = Maestrano::param('sso.name_id_format');
    $settings->idpSingleSignOnUrl = $this->getIdpUrl();
    $settings->spReturnUrl = $this->getConsumeUrl();
    
    return $settings;
  }
}