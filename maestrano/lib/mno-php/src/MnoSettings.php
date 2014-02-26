<?php

/**
 * Holds Maestrano settings
 *
 * These settings need to be filled in by the user prior to being used.
 */
class MnoSettings
{
    
    /**
     * The name of the application.
     * @var string
     */
    public $app_name = 'myapp';
    
    /**
     * Is SSO enabled for this application
     * @var boolean
     */
    public $sso_enabled = false;
    
    /**
     * If enabled then public access will be completely
     * denied (ALL pages will require authentication)
     * @var boolean
     */
    public $sso_intranet_mode = false;
    
    /**
     * Maestrano Single Sign On url
     * @var string
     */
    public $sso_url = '';
    
    /**
     * The URL where the SSO request should be initiated.
     * @var string
     */
    public $sso_init_url = '';
    
    /**
     * The URL where the SSO response will be posted.
     * @var string
     */
    public $sso_return_url = '';
    
    /**
     * The URL where the application should redirect if
     * user is not given access.
     * @var string
     */
    public $sso_access_unauthorized_url = '';
    
    /**
     * The URL where the application should redirect when
     * user logs out
     * @var string
     */
    public $sso_access_logout_url = '';
    
    /**
     * The x509 certificate used to authenticate the request.
     * @var string
     */
    public $sso_x509_certificate = '';
    

    /**
     * Specifies what format to return the identification token (Maestrano user UID)
     * @var string
     */
    public $sso_name_id_format = OneLogin_Saml_Settings::NAMEID_PERSISTENT;
    
    /**
     * The Maestrano endpoint in charge of providing session information
     * @var string
     */
    public $sso_session_check_url = '';
    
    /**
     * Return a settings object for php-saml
     * 
     * @return OneLogin_Saml_Settings
     */
    public function getSamlSettings() {
      $settings = new OneLogin_Saml_Settings();
      
      // Configure SAML
      $settings->idpSingleSignOnUrl = $this->sso_url;
      $settings->idpPublicCertificate = $this->sso_x509_certificate;
      $settings->spReturnUrl = $this->sso_return_url;
      $settings->spIssuer = $this->app_name;
      $settings->requestedNameIdFormat = $this->sso_name_id_format;
      
      return $settings;
    }
}