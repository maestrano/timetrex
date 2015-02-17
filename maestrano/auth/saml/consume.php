<?php

  require_once '../../init.php';

  # Reset session
  session_unset();
  session_destroy();
  session_start();

  // Build SSO Response using SAMLResponse parameter value sent via
  // POST request
  $resp = new Maestrano_Saml_Response($_POST['SAMLResponse']);
  if ($resp->isValid()) {
    // Get the user as well as the user group
    $user = new Maestrano_Sso_User($resp);
    $group = new Maestrano_Sso_Group($resp);

    // Get Maestrano User
    $sso_user = new MnoSsoUser($resp);
    
    // Find or create the User
    $sso_user->findOrCreate();
    
    $_SESSION["loggedIn"] = true;
    $_SESSION["firstName"] = $user->getFirstName();
    $_SESSION["lastName"] = $user->getLastName();
    
    // Important - toId() and toEmail() have different behaviour compared to
    // getId() and getEmail(). In you maestrano configuration file, if your sso > creation_mode 
    // is set to 'real' then toId() and toEmail() return the actual id and email of the user which
    // are only unique across users.
    // If you chose 'virtual' then toId() and toEmail() will return a virtual (or composite) attribute
    // which is truly unique across users and groups
    $_SESSION["id"] = $user->toId();
    $_SESSION["email"] = $user->toEmail();
    
    // Store group details
    $_SESSION["groupName"] = $group->getName();
    $_SESSION["groupId"] = $group->getId();
    
    
    // Once the user is created/identified, we store the maestrano
    // session. This session will be used for single logout
    $mnoSession = new Maestrano_Sso_Session($_SESSION,$user);
    $mnoSession->save();
    
    // Redirect the user to previous or home page
    if(isset($_SESSION['mno_previous_uri'])) {
      header('Location: ' . $_SESSION['mno_previous_uri']);
    } else {
      header('Location: /');
    }
  } else {
    echo 'There was an error during the authentication process.<br/>';
    echo 'Please try again. If issue persists please contact support@maestrano.com';
  }
  
?>
