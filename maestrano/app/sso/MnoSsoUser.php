<?php

/**
 * Configure App specific behavior for 
 * Maestrano SSO
 */
class MnoSsoUser extends MnoSsoBaseUser
{
  /**
   * Database connection
   * @var PDO
   */
  public $connection = null;
  
  
  /**
   * Extend constructor to inialize app specific objects
   *
   * @param OneLogin_Saml_Response $saml_response
   *   A SamlResponse object from Maestrano containing details
   *   about the user being authenticated
   */
  public function __construct(OneLogin_Saml_Response $saml_response, &$session = array(), $opts = array())
  {
    // Call Parent
    parent::__construct($saml_response,$session);
    
    // Assign new attributes
    $this->connection = $opts['db_connection'];
  }
  
  
  /**
   * Sign the user in the application. 
   * Parent method deals with putting the mno_uid, 
   * mno_session and mno_session_recheck in session.
   *
   * @return boolean whether the user was successfully set in session or not
   */
  protected function setInSession()
  {
    
    if ($this->local_id) {
        $authentication = new Authentication();
        $authentication->setObject( $this->local_id );
        $authentication->Login($this->uid, '', 'USER_NAME', true);
        
        return true;
    } else {
        return false;
    }
  }
  
  
  /**
   * Used by createLocalUserOrDenyAccess to create a local user 
   * based on the sso user.
   * If the method returns null then access is denied
   *
   * @return the ID of the user created, null otherwise
   */
  protected function createLocalUser()
  {
    $lid = null;
    
    if ($this->accessScope() == 'private') {
      // First build the user
      $user = $this->buildLocalUser();
      
      // Then save the user and retrieve the local id
      $lid = $user->Save();
    }
    
    return $lid;
  }
  
  /**
   * Build a local user for creation
   *
   * @return a timetrex user
   */
  protected function buildLocalUser()
  {
    $user = TTnew( 'UserFactory' );
    
		$user->setCompany($this->getCompanyToAssign());
		$user->setStatus(10); //Active
		$user->setUserName($this->uid);
    $user->setPassword($this->generatePassword());

		$user->setEmployeeNumber($this->getEmployeeNumberToAssign());
		$user->setFirstName($this->name);
		$user->setLastName($this->surname);
		$user->setWorkEmail($this->email);

		if ( is_object( $user->getCompanyObject() ) ) {
			$user->setCountry( $user->getCompanyObject()->getCountry() );
			$user->setProvince( $user->getCompanyObject()->getProvince() );
			$user->setAddress1( $user->getCompanyObject()->getAddress1() );
			$user->setAddress2( $user->getCompanyObject()->getAddress2() );
			$user->setCity( $user->getCompanyObject()->getCity() );
			$user->setPostalCode( $user->getCompanyObject()->getPostalCode() );
			$user->setWorkPhone( $user->getCompanyObject()->getWorkPhone() );
			$user->setHomePhone( $user->getCompanyObject()->getWorkPhone() );

			if ( is_object( $user->getCompanyObject()->getUserDefaultObject() ) ) {
				$user->setCurrency( $user->getCompanyObject()->getUserDefaultObject()->getCurrency() );
			}
		}
    
    $user->setPermissionControl( $this->getRoleIdToAssign() );
    
    return $user;
  }
  
  /**
   * Return the ID of the default company to assign to the
   * user
   *
   * @return integer the ID of the company to assign
   */
  protected function getCompanyToAssign() {
    
    $result = $this->connection->Execute("SELECT id FROM company ORDER BY id ASC LIMIT 1");
    $result = $result->fields;
    
    if ($result && $result['id']) {
      return $result['id'];
    }
    
    return 0;
  }
  
  /**
   * Return the employee number to assign
   *
   * @return integer the next available employee number
   */
  protected function getEmployeeNumberToAssign() {
    
    $result = $this->connection->Execute("SELECT employee_number FROM users ORDER BY employee_number DESC LIMIT 1");
    $result = $result->fields;
    
    if ($result && $result['employee_number']) {
      $number = intval($result['employee_number']);
      return ($number + 1);
    }
    
    return 0;
  }
  
  /**
   * Return the role to give to the user based on context
   * If the user is the owner of the app or at least Admin
   * for each organization, then it is given the role of 'Admin'.
   * Return 'User' role otherwise
   *
   * @return the ID of the user created, null otherwise
   */
  protected function getRoleIdToAssign() {
    $role_id = 2; // User - Regular Employee (Punch In/Out)
    
    if ($this->app_owner) {
      $role_id = 1; // Admin
    } else {
      foreach ($this->organizations as $organization) {
        if ($organization['role'] == 'Admin' || $organization['role'] == 'Super Admin') {
          $role_id = 1;
        } else {
          $role_id = 2;
        }
      }
    }
    
    return $role_id;
  }
  
  
  /**
   * Get the ID of a local user via Maestrano UID lookup
   *
   * @return a user ID if found, null otherwise
   */
  protected function getLocalIdByUid()
  {
    $arg = $this->connection->escape($this->uid);
    $result = $this->connection->Execute("SELECT id FROM users WHERE mno_uid = '{$arg}' LIMIT 1");
    $result = $result->fields;
    
    if ($result && $result['id']) {
      return $result['id'];
    }
    
    return null;
  }
  
  /**
   * Get the ID of a local user via email lookup
   *
   * @return a user ID if found, null otherwise
   */
  protected function getLocalIdByEmail()
  {
    $arg = $this->connection->escape($this->email);
    $result = $this->connection->Execute("SELECT id FROM users WHERE work_email = '{$arg}' LIMIT 1");
    $result = $result->fields;
    
    if ($result && $result['id']) {
      return $result['id'];
    }
    
    return null;
  }
  
  /**
   * Set all 'soft' details on the user (like name, surname, email)
   * Implementing this method is optional.
   *
   * @return boolean whether the user was synced or not
   */
   protected function syncLocalDetails()
   {
     if($this->local_id) {
       $data = Array(
       'user_name'  => $this->connection->escape($this->uid),
       'first_name' => $this->connection->escape($this->name),
       'last_name'  => $this->connection->escape($this->surname),
       'work_email' => $this->connection->escape($this->email),
       'id'         => $this->connection->escape($this->local_id),
       );
       
       $upd = $this->connection->Execute("UPDATE users
         SET user_name = '{$data['user_name']}', 
         first_name = '{$data['first_name']}', 
         last_name = '{$data['last_name']}'  
         WHERE id = {$data['id']}");
       
       return $upd;
     }
     
     return false;
   }
  
  /**
   * Set the Maestrano UID on a local user via id lookup
   *
   * @return a user ID if found, null otherwise
   */
  protected function setLocalUid()
  {
    if($this->local_id) {
      $data = Array(
      'mno_uid'  => $this->connection->escape($this->uid),
      'id'         => $this->connection->escape($this->local_id),
      );
      
      $upd = $this->connection->Execute("UPDATE users 
        SET mno_uid = '{$data['mno_uid']}'
        WHERE id = {$data['id']}");
      
      return $upd;
    }
    
    return false;
  }
}