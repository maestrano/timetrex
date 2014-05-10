<?php
/*********************************************************************************
 * TimeTrex is a Payroll and Time Management program developed by
 * TimeTrex Software Inc. Copyright (C) 2003 - 2013 TimeTrex Software Inc.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by
 * the Free Software Foundation with the addition of the following permission
 * added to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED
 * WORK IN WHICH THE COPYRIGHT IS OWNED BY TIMETREX, TIMETREX DISCLAIMS THE
 * WARRANTY OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Affero General Public License along
 * with this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 *
 * You can contact TimeTrex headquarters at Unit 22 - 2475 Dobbin Rd. Suite
 * #292 Westbank, BC V4T 2E9, Canada or at email address info@timetrex.com.
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU Affero General Public License
 * version 3, these Appropriate Legal Notices must retain the display of the
 * "Powered by TimeTrex" logo. If the display of the logo is not reasonably
 * feasible for technical reasons, the Appropriate Legal Notices must display
 * the words "Powered by TimeTrex".
 ********************************************************************************/
/*
 * $Revision: 4104 $
 * $Id: index.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php'); //Mainly to force redirect to SSL URL if required...

// Hook:Maestrano
// Load Maestrano
$authentication = new Authentication();
$maestrano = MaestranoService::getInstance();
// Require authentication straight away if intranet
// mode enabled
if ($maestrano->isSsoIntranetEnabled()) {
  if (!isset($_SESSION)) session_start();
  if (!$maestrano->getSsoSession()->isValid()) {
    header("Location: " . $maestrano->getSsoInitUrl());
    exit;
  }
}

// Hook:Maestrano
if ($maestrano->isSsoEnabled()) {
  if (!isset($_SESSION)) session_start();
  if ($authentication->Check()) {
    // Check Maestrano session is still valid
    if (!$maestrano->getSsoSession()->isValid()) {
      header("Location: " . $maestrano->getSsoInitUrl());
      exit;
    }
  } else {
    // Redirect to login
    header("Location: " . $maestrano->getSsoInitUrl());
    exit;
  }
}

//Misc::redirectMobileBrowser(); //Redirect mobile browsers automatically.

?>
<html lang="en" id="ng-app" ng-app="timetrex">

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<title>TimeTrex - Secure Login</title>
<?php if ($maestrano->isSsoEnabled()) { ?>
<script>
var logoutRedirect = "<?php echo $maestrano->getSsoLogoutUrl();?>";
var ssoLoginRedirect = "<?php echo $maestrano->getSsoInitUrl();?>";
setInterval(function(){
  if (document.cookie.indexOf('timetrex_logout=1') != -1) {
    document.cookie = 'timetrex_logout=; expires=Thu, 01-Jan-70 00:00:01 GMT; path=/';
    window.location.replace(logoutRedirect);
  }
  if (document.cookie.indexOf('timetrex_relogin=1') != -1) {
    document.cookie = 'timetrex_relogin=; expires=Thu, 01-Jan-70 00:00:01 GMT; path=/';
    window.location.replace(ssoLoginRedirect);
  }
},1000);
</script>
<?php } ?>

<link href="/interface/ng/libs/css/bootstrap/bootstrap-theme.min.css" media="screen" rel="stylesheet" type="text/css">
<link href="/interface/ng/libs/css/bootstrap/bootstrap.min.css" media="screen" rel="stylesheet" type="text/css">
<link href="/interface/ng/libs/css/custom.css" media="screen" rel="stylesheet" type="text/css">

</head>
<body ng-controller="MainCtrl">
  <div class="container">
    <div tx-timesheet></div>
  </div>
  
  <!-- Javascript -->
  <script src="/interface/ng/libs/js/jquery/jquery-1.11.1.min.js" language="javascript"></script>
  <script src="/interface/ng/libs/js/jquery/jquery.cookie.js" language="javascript"></script>
  <script src="/interface/ng/libs/js/bootstrap/bootstrap.min.js" language="javascript"></script>
  <script src="/interface/ng/libs/js/angular/underscore.min.js" language="javascript"></script>
  <script src="/interface/ng/libs/js/angular/angular.min.js" language="javascript"></script>
  <script src="/interface/ng/libs/js/angular/angular-cookies.min.js" language="javascript"></script>
  <script src="/interface/ng/libs/js/angular/angular-animate.min.js" language="javascript"></script>
  <script src="/interface/ng/app.js" language="javascript"></script>
  <script src="/interface/ng/controllers/main_ctrl.js" language="javascript"></script>
  <script src="/interface/ng/directives/tx-timesheet.js" language="javascript"></script>
  <script src="/interface/ng/services/tx-entities.js" language="javascript"></script>
</body>
</html>
