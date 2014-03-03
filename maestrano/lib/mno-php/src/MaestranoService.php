<?php

/**
 * Maestrano Service used to access all maestrano config variables
 *
 * These settings need to be filled in by the user prior to being used.
 */
class MaestranoService
{
  protected static $_settings;
  protected static $_instance;
  public static $_after_sso_sign_in_path = '/';
  
  /**
   * constructor
   *
   * this is private constructor (use getInstance to get an instance of this class)
   */
  private function __construct() {}
  
  /**
   * Configure the service by assigning settings
   *
   * @return MaestranoService
   */
  public static function configure(MnoSettings $config_settings)
  {
      self::$_settings = $config_settings;
  }
   
  /**
   * Returns an instance of this class
   * (this class uses the singleton pattern)
   *
   * @return MaestranoService
   */
  public static function getInstance()
  {
      if ( ! isset(self::$_instance)) {
          self::$_instance = new self();
      }
      return self::$_instance;
  }
  
  
   
   /**
    * Return the maestrano settings
    *
    * @return MnoSsoSession
    */
    public function getSettings()
    {
      return self::$_settings;
    }
   
   /**
    * Return the maestrano sso session
    *
    * @return MnoSsoSession
    */
   public function getPhpSession()
   {
     return $_SESSION;
   }
   
   /**
    * Return the maestrano sso session
    *
    * @return MnoSsoSession
    */
    public function getSsoSession()
    {
      return new MnoSsoSession(self::$_settings, $this->getPhpSession());
    }
    
    /**
     * Check if Maestrano SSO is enabled
     *
     * @return boolean
     */
     public function isSsoEnabled()
     {
       return (self::$_settings && self::$_settings->sso_enabled);
     }
    
    /**
     * Return wether intranet sso mode is enabled (no public pages)
     *
     * @return boolean
     */
    public function isSsoIntranetEnabled()
    {
      return ($this->isSsoEnabled() && self::$_settings->sso_intranet_mode);
    }
    
    /**
     * Return where the app should redirect internally to initiate
     * SSO request
     *
     * @return boolean
     */
    public function getSsoInitUrl()
    {
      return self::$_settings->sso_init_url;
    }
    
    /**
     * Return where the app should redirect after logging user
     * out
     *
     * @return string url
     */
    public function getSsoLogoutUrl()
    {
      return self::$_settings->sso_access_logout_url;
    }
    
    /**
     * Return where the app should redirect if user does
     * not have access to it
     *
     * @return string url
     */
    public function getSsoUnauthorizedUrl()
    {
      return self::$_settings->sso_access_logout_url;
    }
    
    /**
     * Set the after sso signin path
     *
     * @return string url
     */
    public static function setAfterSsoSignInPath($path)
    {
      self::$_after_sso_sign_in_path = $path;
    }
    
    /**
     * Return the after sso signin path
     *
     * @return string url
     */
    public function getAfterSsoSignInPath()
    {
      if ($this->getPhpSession()) {
				$session = & $this->getPhpSession();
				if (isset($session['mno_previous_url'])) {
					return $session['mno_previous_url'];
				}
        
			}
			return self::$_after_sso_sign_in_path;
    }
  
}