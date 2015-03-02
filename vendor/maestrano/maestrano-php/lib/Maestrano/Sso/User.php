<?php

/**
 * Properly format a User received from Maestrano 
 * SAML IDP
 */
class Maestrano_Sso_User
{
  /* UID of current group */
  public $groupUid = '';
  
  /* Role in current group */
  public $groupRole = '';
  
  /* User UID */
  public $uid = '';
  
  /* User Virtual UID - unique across users and groups */
  public $virtualUid = '';
  
  /* User email */
  public $email = '';
  
  /* User virtual email - unique across users and groups */
  public $virtualEmail = '';
  
  /* User firstName */
  public $firstName = '';
  
  /* User lastName */
  public $lastName = '';
  
  /* User country - alpha2 code */
  public $country = '';
  
  /* User company firstName */
  public $companyName = '';
  
  /* Maestrano specific user sso session token */
  public $ssoSession = '';
  
  /* When to recheck for validity of the sso session */
  public $ssoSessionRecheck = null;
  
  
  /**
   * Construct the Maestrano_Sso_User object from a SAML response
   *
   * @param Maestrano_Saml_Response $saml_response
   *   A SamlResponse object from Maestrano containing details
   *   about the user being authenticated
   */
  public function __construct($saml_response)
  {
      // Get assertion attributes
      $att = $saml_response->getAttributes();
      
      // Group related information
      $this->groupUid  = $att['group_uid'];
      $this->groupRole = $att['group_role'];
      
      // Extract mno session information
      $this->ssoSession = $att['mno_session'];
      $this->ssoSessionRecheck = new DateTime($att['mno_session_recheck']);
      
      // Extract user metadata
      $this->uid = $att['uid'];
      $this->virtualUid = $att['virtual_uid'];
      $this->email = $att['email'];
      $this->virtualEmail = $att['virtual_email'];
      $this->firstName = $att['name'];
      $this->lastName = $att['surname'];
      $this->country = $att['country'];
      $this->companyName = $att['company_name'];
  }
  
  public function toId() {
    return $this->toUid();
  }
  
	/**
	 * Return the real UID if Maestrano Sso Creation Mode is set
   * to "real" and the Virtual UID otherwise ("virtual" mode)
	 * @return $this->String uid to use in application
	 */
    public function toUid()
    {
    	if (Maestrano::param('sso.creation_mode') == "real") {
    		return $this->uid;
    	} else {
    		return $this->virtualUid;
    	}
    }
    
    /**
     * Return the real email if Maestrano Sso Creation Mode is set
     * to "real" and the Virtual email otherwise ("virtual" mode)
     * @return
     */
    public function toEmail()
    {
    	if (Maestrano::param('sso.creation_mode') == "real") {
    		return $this->email;
    	} else {
    		return $this->virtualEmail;
    	}
    }
	
	/**
	 * Return the current user session token
	 * @return String session token
	 */
	public function getSsoSession() {
		return $this->ssoSession;
	}
	
	/**
	 * Return when the user session should be remotely checked
	 * @return DateTime session check time
	 */
	public function getSsoSessionRecheck() {
		return $this->ssoSessionRecheck;
	}
	
	/**
	 * Return the user group UID 
	 * @return String group UID
	 */
	public function getGroupUid() {
		return $this->groupUid;
	}
	
	/**
	 * Return the user role in the group
	 * Roles are: 'Member', 'Power User', 'Admin', 'Super Admin'
	 * @return String user role in group
	 */
	public function getGroupRole() {
		return $this->groupRole;
	}
	
	/**
	 * The Maestrano user ID (UID)
	 * @return String user ID (UID)
	 */
	public function getId() {
		return $this->uid;
	}
  
	/**
	 * The Maestrano user UID
	 * @return String user UID
	 */
	public function getUid() {
		return $this->uid;
	}
	
	/**
	 * The user virtual (ID) UID which is truly unique across users and groups
	 * @return String user virtual uid
	 */
	public function getVirtualId() {
		return $this->virtualUid;
	}
  
	/**
	 * The user virtual UID which is truly unique across users and groups
	 * @return String user virtual uid
	 */
	public function getVirtualUid() {
		return $this->virtualUid;
	}
	
	/**
	 * The actual user email 
	 * @return String user email
	 */
	public function getEmail() {
		return $this->email;
	}
	
	/**
	 * Virtual email that can be used instead of regular email fields
	 * This email is unique across users and groups
	 * All emails sent to this email address are redirected to the real
	 * user email
	 * @return String virtual email
	 */
	public function getVirtualEmail() {
		return $this->virtualEmail;
	}
	
	/**
	 * User first firstName
	 * @return String user first name
	 */
	public function getFirstName() {
		return $this->firstName;
	}
	
	/**
	 * User last last name
	 * @return String user last name
	 */
	public function getLastName() {
		return $this->lastName;
	}
	
	/**
	 * ALPHA2 code of user country
	 * @return
	 */
	public function getCountry() {
		return $this->country;
	}
	
	/**
	 * Company firstName entered by the user
	 * Can be empty
	 * @return String company name
	 */
	public function getCompanyName() {
		return $this->companyName;
	}
}