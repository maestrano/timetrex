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
  // protected function setInSession()
  // {
  //   // First set $conn variable (need global variable?)
  //   $conn = $this->connection;
  //   
  //   $sel1 = $conn->query("SELECT ID,name,lastlogin FROM user WHERE ID = $this->local_id");
  //   $chk = $sel1->fetch();
  //   if ($chk["ID"] != "") {
  //       $now = time();
  //       
  //       // Set session
  //       $this->session['userid'] = $chk['ID'];
  //       $this->session['username'] = stripslashes($chk['name']);
  //       $this->session['lastlogin'] = $now;
  //       
  //       // Update last login timestamp
  //       $upd1 = $conn->query("UPDATE user SET lastlogin = '$now' WHERE ID = $this->local_id");
  //       
  //       return true;
  //   } else {
  //       return false;
  //   }
  // }
  
  
  /**
   * Used by createLocalUserOrDenyAccess to create a local user 
   * based on the sso user.
   * If the method returns null then access is denied
   *
   * @return the ID of the user created, null otherwise
   */
  // protected function createLocalUser()
  // {
  //   $lid = null;
  //   
  //   if ($this->accessScope() == 'private') {
  //     // First set $conn variable (need global variable?)
  //     $conn = $this->connection;
  //     
  //     // Create user
  //     $lid = $this->connection->query("CREATE BLA.....");
  //   }
  //   
  //   return $lid;
  // }
  
  /**
   * Get the ID of a local user via Maestrano UID lookup
   *
   * @return a user ID if found, null otherwise
   */
  // protected function getLocalIdByUid()
  // {
  //   $result = $this->connection->query("SELECT ID FROM user WHERE mno_uid = {$this->connection->quote($this->uid)} LIMIT 1")->fetch();
  //   
  //   if ($result && $result['ID']) {
  //     return $result['ID'];
  //   }
  //   
  //   return null;
  // }
  
  /**
   * Get the ID of a local user via email lookup
   *
   * @return a user ID if found, null otherwise
   */
  // protected function getLocalIdByEmail()
  // {
  //   $result = $this->connection->query("SELECT ID FROM user WHERE email = {$this->connection->quote($this->email)} LIMIT 1")->fetch();
  //   
  //   if ($result && $result['ID']) {
  //     return $result['ID'];
  //   }
  //   
  //   return null;
  // }
  
  /**
   * Set all 'soft' details on the user (like name, surname, email)
   * Implementing this method is optional.
   *
   * @return boolean whether the user was synced or not
   */
   // protected function syncLocalDetails()
   // {
   //   if($this->local_id) {
   //     $upd = $this->connection->query("UPDATE user SET name = {$this->connection->quote($this->name . ' ' . $this->surname)}, email = {$this->connection->quote($this->email)} WHERE ID = $this->local_id");
   //     return $upd;
   //   }
   //   
   //   return false;
   // }
  
  /**
   * Set the Maestrano UID on a local user via id lookup
   *
   * @return a user ID if found, null otherwise
   */
  // protected function setLocalUid()
  // {
  //   if($this->local_id) {
  //     $upd = $this->connection->query("UPDATE user SET mno_uid = {$this->connection->quote($this->uid)} WHERE ID = $this->local_id");
  //     return $upd;
  //   }
  //   
  //   return false;
  // }
}