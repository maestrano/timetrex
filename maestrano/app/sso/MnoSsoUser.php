<?php

/**
 * Configure App specific behavior for Maestrano SSO
 */
class MnoSsoUser extends Maestrano_Sso_User {
  /**
   * Database connection
   * @var PDO
   */
  public $connection = null;

  /**
   * Construct the Maestrano_Sso_User object from a SAML response
   *
   * @param Maestrano_Saml_Response $saml_response
   *   A SamlResponse object from Maestrano containing details
   *   about the user being authenticated
   */
  public function __construct($saml_response) {
    global $db;

    parent::__construct($saml_response);
    
    $this->connection = $db;
  }

  /**
  * Find or Create a user based on the SAML response parameter and Add the user to current session
  */
  public function findOrCreate() {
    // Find user by uid or email
    $local_id = $this->getLocalIdByUid();
    if($local_id == null) { $local_id = $this->getLocalIdByEmail(); }
    
    if ($local_id) {
      // User found, load it
      $this->local_id = $local_id;
      $this->syncLocalDetails();
    } else {
      // New user, create it
      $this->local_id = $this->createLocalUser();
      $this->setLocalUid();
    }

    // Add user to current session
    $this->setInSession();
  }
  
  /**
   * Sign the user in the application. 
   * Parent method deals with putting the mno_uid, 
   * mno_session and mno_session_recheck in session.
   *
   * @return boolean whether the user was successfully set in session or not
   */
  protected function setInSession() {
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
  protected function createLocalUser() {
    // First build the user
    $user = $this->buildLocalUser();
    // Then save the user and retrieve the local id
    $lid = $user->Save();

    return $lid;
  }
  
  /**
   * Build a local user for creation
   *
   * @return a timetrex user
   */
  protected function buildLocalUser() {
    $user = TTnew( 'UserFactory' );

    $user->setCompany(CompanyMapper::getDefaultCompany()->getId());
    $user->setStatus(10); //Active
    $user->setUserName($this->uid);
    $user->setPassword($this->generatePassword());

    $user->setEmployeeNumber($this->getEmployeeNumberToAssign(CompanyMapper::getDefaultCompany()->getId()));
    $user->setFirstName($this->getFirstName());
    $user->setLastName($this->getLastName());
    $user->setWorkEmail($this->getEmail());
    $user->setCurrency(CompanyMapper::getDefaultCurrency()->getId());

    if ( is_object( $user->getCompanyObject() ) ) {
      $user->setCountry( $user->getCompanyObject()->getCountry() );
      $user->setProvince( $user->getCompanyObject()->getProvince() );
      $user->setAddress1( $user->getCompanyObject()->getAddress1() );
      $user->setAddress2( $user->getCompanyObject()->getAddress2() );
      $user->setCity( $user->getCompanyObject()->getCity() );
      $user->setPostalCode( $user->getCompanyObject()->getPostalCode() );
      $user->setWorkPhone( $user->getCompanyObject()->getWorkPhone() );
      $user->setHomePhone( $user->getCompanyObject()->getWorkPhone() );
    }
    
    $user->setPermissionControl($this->getRoleIdToAssign());
    
    return $user;
  }
  
  /**
   * Return the employee number to assign
   *
   * @return integer the next available employee number
   */
  protected function getEmployeeNumberToAssign($company_id) {
    return UserFactory::getNextAvailableEmployeeNumber($company_id);
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
    // Set permission level based on role

    // Role                             Level
    // Administrator                    25
    // Regular Employee (Punch In/Out)  1
    // Regular Employee (Manual Entry)  2
    // Supervisor (Subordinates Only)   10
    // HR Manager                       18
    // Payroll Administrator            20
    // Supervisor (All Employees)       5

    switch($this->getGroupRole()) {
      case 'Member':
        $level = 2;
        break;
      case 'Power User':
        $level = 18;
        break;
      case 'Admin':
        $level = 20;
        break;
      case 'Super Admin':
        $level = 25;
        break;
      default:
        $level = 1;
        break;
    }
    $pclf = TTnew('PermissionControlListFactory');
    $pclf->getByCompanyIdAndLevel(CompanyMapper::getDefaultCompany()->getId(), $level, null, null, null, array( 'level' => 'desc' ));
    return $pclf->getCurrent()->getID();
  }
  
  
  /**
   * Get the ID of a local user via Maestrano UID lookup
   *
   * @return a user ID if found, null otherwise
   */
  protected function getLocalIdByUid() {
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
  protected function getLocalIdByEmail() {
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
   protected function syncLocalDetails() {
     if($this->local_id) {
       $data = Array(
       'user_name'  => $this->connection->escape($this->uid),
       'first_name' => $this->connection->escape($this->getFirstName()),
       'last_name'  => $this->connection->escape($this->getLastName()),
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
  protected function setLocalUid() {
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

   /**
  * Generate a random password.
  * Convenient to set dummy passwords on users
  *
  * @return string a random password
  */
  protected function generatePassword() {
    $length = 20;
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
      $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
  }
}