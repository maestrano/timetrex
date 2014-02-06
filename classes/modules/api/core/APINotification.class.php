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
 * $Revision: 2196 $
 * $Id: APINotification.class.php 2196 2008-10-14 16:08:54Z ipso $
 * $Date: 2008-10-14 09:08:54 -0700 (Tue, 14 Oct 2008) $
 */

/**
 * @package API\Core
 */
class APINotification extends APIFactory {
	protected $main_class = '';

	public function __construct() {
		parent::__construct(); //Make sure parent constructor is always called.

		return TRUE;
	}

	/**
	 * Returns array of notifications message to be displayed to the user.
	 * @param string $action Action that is being performed, possible values: 'login', 'preference', 'notification', 'pay_period'
	 * @return array
	 */
	function getNotifications( $action = FALSE ) {
		global $config_vars, $disable_database_connection;

		$retarr = FALSE;

		//Skip this step if disable_database_connection is enabled or the user is going through the installer still
		switch ( strtolower($action) ) {
			case 'login':
				if ( ( !isset($disable_database_connection) OR ( isset($disable_database_connection) AND $disable_database_connection != TRUE ) )
						AND ( !isset($config_vars['other']['installer_enabled']) OR ( isset($config_vars['other']['installer_enabled']) AND $config_vars['other']['installer_enabled'] != TRUE ) )) {
					//Get all system settings, so they can be used even if the user isn't logged in, such as the login page.
					$sslf = new SystemSettingListFactory();
					$system_settings = $sslf->getAllArray();
				}
				unset($sslf);

				//Check license validity
				if ( ( ( DEPLOYMENT_ON_DEMAND == FALSE AND $this->getCurrentCompanyObject()->getId() == 1 ) OR ( isset($config_vars['other']['primary_company_id']) AND $this->getCurrentCompanyObject()->getId() == $config_vars['other']['primary_company_id'] ) ) AND getTTProductEdition() > 10 ) {
					if ( !isset($system_settings['license']) ) {
						$system_settings['license'] = NULL;
					}

					$license = new TTLicense();
					$license_validate = $license->validateLicense( $system_settings['license'] );
					$license_message = $license->getFullErrorMessage( $license_validate , TRUE );
					if ( $license_message != '' ) {
						$destination_url = 'http://www.timetrex.com/r.php?id=899';

						if ( $license_validate === TRUE ) {
							//License likely expires soon.
							$retarr[] = array(
												  'delay' => 0, //0= Show until clicked, -1 = Show until next getNotifications call.
												  'bg_color' => '#FFFF00', //Yellow
												  'message' => TTi18n::getText('WARNING: %1', $license_message ),
												  'destination' => $destination_url,
												  );
						} else {
							//License error.
							$retarr[] = array(
												  'delay' => -1, //0= Show until clicked, -1 = Show until next getNotifications call.
												  'bg_color' => '#FF0000', //Red
												  'message' => TTi18n::getText('WARNING: %1', $license_message ),
												  'destination' => $destination_url,
												  );
						}
					}
					unset($license, $license_validate, $license_message, $destination);
				}

				//System Requirements not being met.
				if ( isset($system_settings['valid_install_requirements']) AND DEPLOYMENT_ON_DEMAND == FALSE AND (int)$system_settings['valid_install_requirements'] == 0 ) {
					$retarr[] = array(
										  'delay' => -1, //0= Show until clicked, -1 = Show until next getNotifications call.
										  'bg_color' => '#FF0000', //Red
										  'message' => TTi18n::getText('WARNING: %1 system requirement check has failed! Please contact your %1 administrator immediately to re-run the %1 installer to correct the issue.', APPLICATION_NAME ),
										  'destination' => NULL,
										  );
				}

				//Check version mismatch
				if ( isset($system_settings['system_version']) AND DEPLOYMENT_ON_DEMAND == FALSE AND APPLICATION_VERSION != $system_settings['system_version'] ) {
					$retarr[] = array(
										  'delay' => -1, //0= Show until clicked, -1 = Show until next getNotifications call.
										  'bg_color' => '#FF0000', //Red
										  'message' => TTi18n::getText('WARNING: %1 application version does not match database version. Please re-run the %1 installer to complete the upgrade process.', APPLICATION_NAME ),
										  'destination' => NULL,
										  );
				}

				//Only display message to the primary company. 
				if ( ( (time()-(int)APPLICATION_VERSION_DATE) > (86400*475) )
						AND ( $this->getCurrentCompanyObject()->getId() == 1 OR ( isset($config_vars['other']['primary_company_id']) AND $this->getCurrentCompanyObject()->getId() == $config_vars['other']['primary_company_id'] ) ) ) { //~1yr and 3mths
					$retarr[] = array(
										  'delay' => -1,
										  'bg_color' => '#FF0000', //Red
										  'message' => TTi18n::getText('WARNING: This %1 version (v%2) is severely out of date and may no longer be supported. Please upgrade to the latest version as soon as possible as invalid calculations may already be occurring.', array( APPLICATION_NAME, APPLICATION_VERSION ) ),
										  'destination' => NULL,
										  );
				}

				//New version available notification.
				if ( 	DEMO_MODE == FALSE
						AND ( isset($system_settings['new_version']) AND $system_settings['new_version'] == 1 )
						AND ( $this->getCurrentCompanyObject()->getId() == 1 OR ( isset($config_vars['other']['primary_company_id']) AND $this->getCurrentCompanyObject()->getId() == $config_vars['other']['primary_company_id'] ) ) ) {

					//Only display this every two weeks.
					$new_version_available_notification_arr = UserSettingFactory::getUserSetting( $this->getCurrentUserObject()->getID(), 'new_version_available_notification' );
					if ( !isset($new_version_available_notification_arr['value']) OR ( isset($new_version_available_notification_arr['value']) AND $new_version_available_notification_arr['value'] <= (time()-(86400*14)) ) ) {
						UserSettingFactory::setUserSetting( $this->getCurrentUserObject()->getID(), 'new_version_available_notification', time() );

						$retarr[] = array(
											  'delay' => -1,
											  'bg_color' => '#FFFF00', //Yellow
											  'message' => TTi18n::getText('NOTICE: A new version of %1 available, it is highly recommended that you upgrade as soon as possible. Click here to download the latest version.', array( APPLICATION_NAME ) ),
											  'destination' => ( getTTProductEdition() == TT_PRODUCT_COMMUNITY ) ? 'http://www.timetrex.com/r.php?id=19' : 'http://www.timetrex.com/r.php?id=9',
											  );
					}
					unset($new_version_available_notification);
				}

				//Check for major new version.
				$new_version_notification_arr = UserSettingFactory::getUserSetting( $this->getCurrentUserObject()->getID(), 'new_version_notification' );
				if (	DEMO_MODE == FALSE
						AND ( !isset($config_vars['branding']['application_name']) OR ( $this->getCurrentCompanyObject()->getId() == 1 OR ( isset($config_vars['other']['primary_company_id']) AND $this->getCurrentCompanyObject()->getId() == $config_vars['other']['primary_company_id'] ) ) )
						AND $this->getPermissionObject()->getLevel() >= 20 //Payroll Admin
						AND $this->getCurrentUserObject()->getCreatedDate() <= APPLICATION_VERSION_DATE
						AND ( !isset($new_version_notification_arr['value']) OR ( isset($new_version_notification_arr['value']) AND Misc::MajorVersionCompare( APPLICATION_VERSION, $new_version_notification_arr['value'], '>' ) ) ) ) {
					UserSettingFactory::setUserSetting( $this->getCurrentUserObject()->getID(), 'new_version_notification', APPLICATION_VERSION );

					$retarr[] = array(
										  'delay' => -1,
										  'bg_color' => '#FFFF00', //Yellow
										  'message' => TTi18n::getText('NOTICE: Your instance of %1 has been upgraded to v%2, click here to see whats new.', array( APPLICATION_NAME, APPLICATION_VERSION ) ),
										  'destination' => 'http://www.timetrex.com/r.php?id=300',
										  );
				}
				unset($new_version_notification);

				//Check installer enabled.
				if ( isset($config_vars['other']['installer_enabled']) AND $config_vars['other']['installer_enabled'] == 1 ) {
					$retarr[] = array(
										  'delay' => -1,
										  'bg_color' => '#FF0000', //Red
										  'message' => TTi18n::getText('WARNING: %1 is currently in INSTALL MODE. Please go to your timetrex.ini.php file and set "installer_enabled" to "FALSE".', APPLICATION_NAME ),
										  'destination' => NULL,
										  );
				}

				//Make sure CronJobs are running correctly.
				$cjlf = new CronJobListFactory();
				$cjlf->getMostRecentlyRun();
				if ( $cjlf->getRecordCount() > 0 ) {
					//Is last run job more then 48hrs old?
					$cj_obj = $cjlf->getCurrent();

					if ( PRODUCTION == TRUE
							AND DEMO_MODE == FALSE
							AND $cj_obj->getLastRunDate() < ( time()-172800 )
							AND $cj_obj->getCreatedDate() < ( time()-172800 ) ) {
						$retarr[] = array(
											  'delay' => -1,
											  'bg_color' => '#FF0000', //Red
											  'message' => TTi18n::getText('WARNING: Critical maintenance jobs have not run in the last 48hours. Please contact your %1 administrator immediately.', APPLICATION_NAME ),
											  'destination' => NULL,
											  );
					}
				}
				unset($cjlf, $cj_obj);

				//Check if any pay periods are past their transaction date and not closed.
				if ( DEMO_MODE == FALSE AND $this->getPermissionObject()->Check('pay_period_schedule','enabled') AND $this->getPermissionObject()->Check('pay_period_schedule','view') ) {
					$pplf = TTnew('PayPeriodListFactory');
					$pplf->getByCompanyIdAndStatusAndTransactionDate( $this->getCurrentCompanyObject()->getId(), array(10,30), TTDate::getBeginDayEpoch( time() ) ); //Open or Post Adjustment pay periods.
					if ( $pplf->getRecordCount() > 0 ) {
						foreach( $pplf as $pp_obj ) {
							if ( $pp_obj->getCreatedDate() < (time()-(86400*40)) ) { //Ignore pay period schedules newer than 40 days. They are automatically closed after 45 days.
								$retarr[] = array(
													  'delay' => 0,
													  'bg_color' => '#FF0000', //Red
													  'message' => TTi18n::getText('WARNING: Pay periods past their transaction date have not been closed yet. It\'s critical that these pay periods are closed to prevent data loss, click here to close them now.'),
													  'destination' => array('menu_name' => 'Pay Periods'),
													  );
								break;
							}
						}
					}
					unset($pplf, $pp_obj);
				}

				//CHeck for unread messages
				$mclf = new MessageControlListFactory();
				$unread_messages = $mclf->getNewMessagesByCompanyIdAndUserId( $this->getCurrentCompanyObject()->getId(), $this->getCurrentUserObject()->getId() );
				Debug::text('UnRead Messages: '. $unread_messages, __FILE__, __LINE__, __METHOD__, 10);
				if ( $unread_messages > 0 ) {
					$retarr[] = array(
										  'delay' => 25,
										  'bg_color' => '#FFFF00', //Yellow
										  'message' => TTi18n::getText('NOTICE: You have %1 new message(s) waiting, click here to read them now.', $unread_messages ),
										  'destination' => array('menu_name' => 'Messages'),
										  );
				}
				unset($mclf, $unread_messages);

				if ( DEMO_MODE == FALSE ) {
					$elf = new ExceptionListFactory();
					$elf->getFlaggedExceptionsByUserIdAndPayPeriodStatus( $this->getCurrentUserObject()->getId(), 10 );
					$display_exception_flag = FALSE;
					if ( $elf->getRecordCount() > 0 ) {
						foreach($elf as $e_obj) {
							if ( $e_obj->getColumn('severity_id') == 30 ) {
								$display_exception_flag = 'red';
							}
							break;
						}
					}
					if ( isset($display_exception_flag) AND $display_exception_flag !== FALSE ) {
						Debug::Text('Exception Flag to Display: '. $display_exception_flag, __FILE__, __LINE__, __METHOD__, 10);
						$retarr[] = array(
											  'delay' => 30,
											  'bg_color' => '#FFFF00', //Yellow
											  'message' => TTi18n::getText('NOTICE: You have critical severity exceptions pending, click here to view them now.'),
											  'destination' => array('menu_name' => 'Exceptions'),
											  );
					}
					unset($elf, $e_obj, $display_exception_flag);
				}

				if ( DEMO_MODE == FALSE
						AND $this->getPermissionObject()->getLevel() >= 20 //Payroll Admin
						AND ( $this->getCurrentUserObject()->getWorkEmail() == '' AND $this->getCurrentUserObject()->getHomeEmail() == '' ) ) {
					$retarr[] = array(
										  'delay' => 30,
										  'bg_color' => '#FF0000', //Red
										  'message' => TTi18n::getText('WARNING: Please click here and enter an email address for your account, this is required to receive important notices and prevent your account from being locked out.'),
										  'destination' => array('menu_name' => 'Contact Information'),
										  );
				}

				break;
			default:
				break;
		}

		//Check timezone is proper.
		$current_user_prefs = $this->getCurrentUserObject()->getUserPreferenceObject();
		if ( $current_user_prefs->setDateTimePreferences() == FALSE ) {
			//Setting timezone failed, alert user to this fact.
			//WARNING: %1 was unable to set your time zone. Please contact your %1 administrator immediately.{/t} {if $permission->Check('company','enabled') AND $permission->Check('company','edit_own')}<a href="http://forums.timetrex.com/viewtopic.php?t=40">{t}For more information please click here.{/t}</a>{/if}
			if ( $this->getPermissionObject()->Check('company','enabled') AND $this->getPermissionObject()->Check('company','edit_own') ) {
				$destination_url = 'http://www.timetrex.com/r.php?id=1010';
				$sub_message = TTi18n::getText('For more information please click here.');
			} else {
				$destination_url = NULL;
				$sub_message = NULL;
			}

			$retarr[] = array(
								  'delay' => -1,
								  'bg_color' => '#FF0000', //Red
								  'message' => TTi18n::getText('WARNING: %1 was unable to set your time zone. Please contact your %1 administrator immediately.', APPLICATION_NAME ).' '. $sub_message,
								  'destination' => $destination_url,
								  );
			unset($destination_url, $sub_message );
		}

		return $retarr;

	}
}
?>
