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
 * $Revision: 9936 $
 * $Id: DatabaseSchema.php 9936 2013-05-20 16:40:47Z ipso $
 * $Date: 2013-05-20 09:40:47 -0700 (Mon, 20 May 2013) $
 */
require_once('../../includes/global.inc.php');
require_once('HTML/Progress.php');

ignore_user_abort(TRUE);
ini_set( 'max_execution_time', 0 );
ini_set( 'memory_limit', '1024M' ); //Just in case.

//Debug::setVerbosity(11);

$authenticate = FALSE;

//Make sure we disable the database connection ONLY for Interface.inc.php to stop it from trying to access system_settings table before its created.
//However we have to do this AFTER global.inc.php so the $db variable still exists.
$disable_database_connection=TRUE;
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

/*
 *
 * START Progress Bar Header...
 *
 */
function InitProgressBar( $increment = 1 ) {
	global $progress_bar;

	$progress_bar = new HTML_Progress();
	$progress_bar->setAnimSpeed(100);

	//$progress_bar->setIncrement( (int)$increment );
	//$progress_bar->setIndeterminate(true);
	$progress_bar->setBorderPainted(true);


	$ui =& $progress_bar->getUI();
	$ui->setCellAttributes('active-color=#3874B4 inactive-color=#CCCCCC width=10');
	$ui->setBorderAttributes('width=1 color=navy');
	$ui->setStringAttributes('width=60 font-size=14 background-color=#FFFFFF align=center');

	?>
	<html>
	<head>
	<style type="text/css">
	<!--
	<?php echo $progress_bar->getStyle(); ?>

	body {
			background-color: #FFFFFF;
			color: #FFFFFF;
			font-family: Verdana, freesans;
	}

	a:visited, a:active, a:link {
			color: yellow;
	}
	// -->
	</style>
	<script type="text/javascript">
	<!--
	<?php echo $progress_bar->getScript(); ?>
	//-->
	</script>
	</head>
	<body>

	<div align="center">
	<?php
	echo $progress_bar->toHtml();
}

/*
 *
 * END Progress Bar Header...
 *
 */

$smarty->assign('title', TTi18n::gettext($title = '3. Database Configuration')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'data',
												'external_installer',
												) ) );

$install_obj = new Install();
if ( $install_obj->isInstallMode() == FALSE ) {
	Redirect::Page( URLBuilder::getURL(NULL, 'install.php') );
}

$database_engine = TRUE;

$install_obj->setDatabaseConnection( $db ); //Default connection
if ( $install_obj->checkDatabaseExists( $config_vars['database']['database_name'] ) == TRUE ) {
	if ( $install_obj->checkTableExists( 'company' ) == TRUE ) {
		//Table could be created, but check to make sure a company actually exists too.
		$clf = TTnew( 'CompanyListFactory' );
		$clf->getAll();
		if ( $clf->getRecordCount() >= 1 ) {
			$install_obj->setIsUpgrade( TRUE );
		} else {
			//No company exists, send them to the create company page.
			$install_obj->setIsUpgrade( FALSE );
		}
	} else {
		$install_obj->setIsUpgrade( FALSE );
	}
}

$action = Misc::findSubmitButton();
switch ($action) {
	case 'install_schema':
		//Need to create the tables after the database
		//exists and Database.inc.php has made a connection.
		//Otherwise we can't use objects yet.
		//Debug::setVerbosity(11);
		//Debug::setBufferOutput(FALSE);
		Debug::Text('Install Schema', __FILE__, __LINE__, __METHOD__,10);

		InitProgressBar();
		$progress_bar->setValue(1);
		$progress_bar->display();

		//$install_obj->getDatabaseConnection()->StartTrans(); //Used for debugging only.
		if ( $install_obj->checkDatabaseExists( $config_vars['database']['database_name'] ) == TRUE ) {
			//Create SQL, always try to install every schema version, as
			//installSchema() will check if its already been installed or not.
			$install_obj->setDatabaseDriver( $config_vars['database']['type'] );
			$install_obj->createSchemaRange( NULL, NULL ); //All schema versions
			//FIXME: Notify the user of any errors.
			$install_obj->setVersions();
		} else {
			Debug::Text('bDatabase does not exist.', __FILE__, __LINE__, __METHOD__,10);
		}
		//$install_obj->getDatabaseConnection()->FailTrans(); //Used for debugging only.

		$progress_bar->setValue( 100 );
		$progress_bar->display();

		if ( $install_obj->getIsUpgrade() == TRUE ) {
			//Make sure when using external installer that update notifications are always enabled.
			$sslf = TTnew( 'SystemSettingListFactory' );
			$sslf->getByName('update_notify');
			if ( $sslf->getRecordCount() == 1 ) {
				$obj = $sslf->getCurrent();
			} else {
				$obj = TTnew( 'SystemSettingListFactory' );
			}

			$obj->setName( 'update_notify' );
			if ( $external_installer == 1 ) {
				$obj->setValue( 1 );
			}
			if ( $obj->isValid() ) {
				$obj->Save();
			}

			$next_page = URLBuilder::getURL( array('external_installer' => $external_installer), 'PostUpgrade.php');
		} else {
			if ( $external_installer == 1 ) {
				$next_page = URLBuilder::getURL( array('action:next' => 1, 'external_installer' => $external_installer), 'SystemSettings.php');
			} else {
				$next_page = URLBuilder::getURL( array('external_installer' => $external_installer), 'SystemSettings.php');
			}
		}
		Debug::writeToLog();
		if ( Debug::getVerbosity() >= 11 ) {
			Debug::Display();
		} else {
			?>
			<script type="text/javascript">parent.location.href='<?php echo $next_page;?>'</script>
			<?php
		}
		exit;
		break;
	default:
		$handle = @fopen('http://www.timetrex.com/'.URLBuilder::getURL( array('v' => $install_obj->getFullApplicationVersion(), 'page' => 'database_schema'), 'pre_install.php'), "r");
		@fclose($handle);

		$smarty->assign_by_ref('install_obj', $install_obj);
		$smarty->assign_by_ref('external_installer', $external_installer);
		//$smarty->assign_by_ref('upgrade', $upgrade);

		$smarty->display('install/DatabaseSchema.tpl');

		break;
}
?>
