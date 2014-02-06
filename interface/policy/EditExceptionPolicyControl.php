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
 * $Id: EditExceptionPolicyControl.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

//Debug::setVerbosity(11);

if ( !$permission->Check('exception_policy','enabled')
		OR !( $permission->Check('exception_policy','edit') OR $permission->Check('exception_policy','edit_own') ) ) {

	$permission->Redirect( FALSE ); //Redirect

}

$smarty->assign('title', TTi18n::gettext($title = 'Edit Exception Policy')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'id',
												'data'
												) ) );

if ( isset($data['exceptions'])) {
	foreach( $data['exceptions'] as $code => $exception ) {

		if ( isset($exception['grace']) AND $exception['grace'] != '') {
			Debug::Text('Grace: '. $exception['grace'] , __FILE__, __LINE__, __METHOD__,10);
			$data['exceptions'][$code]['grace'] = TTDate::parseTimeUnit( $exception['grace'] );
		}
		if ( isset($exception['watch_window']) AND $exception['watch_window'] != '') {
			$data['exceptions'][$code]['watch_window'] = TTDate::parseTimeUnit( $exception['watch_window'] );
		}
	}
}

$epf = TTnew( 'ExceptionPolicyFactory' );
$epcf = TTnew( 'ExceptionPolicyControlFactory' );

$action = Misc::findSubmitButton();
$action = strtolower($action);
switch ($action) {
	case 'submit':
		//Debug::setVerbosity(11);
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

		$epcf->setId( $data['id'] );
		$epcf->setCompany( $current_company->getId() );
		$epcf->setName( $data['name'] );

		if ( $epcf->isValid() ) {
			$epc_id = $epcf->Save();

			Debug::Text('aException Policy Control ID: '. $epc_id , __FILE__, __LINE__, __METHOD__,10);

			if ( $epc_id === TRUE ) {
				$epc_id = $data['id'];
			}

			Debug::Text('bException Policy Control ID: '. $epc_id , __FILE__, __LINE__, __METHOD__,10);

			if ( count($data['exceptions']) > 0 ) {
				foreach ($data['exceptions'] as $code => $exception_data) {
					Debug::Text('Looping Code: '. $code .' ID: '. $exception_data['id'], __FILE__, __LINE__, __METHOD__,10);

					if ( $exception_data['id'] != '' AND $exception_data['id'] > 0 ) {
						$epf->setId( $exception_data['id'] );
					}
					$epf->setExceptionPolicyControl( $epc_id );
					if ( isset($exception_data['active'])  ) {
						$epf->setActive( TRUE );
					} else {
						$epf->setActive( FALSE );
					}
					$epf->setType( $code );
					$epf->setSeverity( $exception_data['severity_id'] );
					$epf->setEmailNotification( $exception_data['email_notification_id'] );
					if ( isset($exception_data['demerit']) AND $exception_data['demerit'] != '') {
						$epf->setDemerit( $exception_data['demerit'] );
					}
					if ( isset($exception_data['grace']) AND $exception_data['grace'] != '' ) {
						$epf->setGrace( $exception_data['grace'] );
					}
					if ( isset($exception_data['watch_window']) AND $exception_data['watch_window'] != '' ) {
						$epf->setWatchWindow( $exception_data['watch_window'] );
					}
					if ( $epf->isValid() ) {
						$epf->Save();
					}
				}
			}

			Redirect::Page( URLBuilder::getURL( NULL, 'ExceptionPolicyControlList.php') );

			break;
		}

	default:
		$type_options = $epf->getTypeOptions( $current_company->getProductEdition() );

		if ( isset($id) AND $id != '' ) {
			BreadCrumb::setCrumb($title);

			$epclf = TTnew( 'ExceptionPolicyControlListFactory' );
			$epclf->getByIdAndCompanyID( $id, $current_company->getID() );

			foreach ($epclf as $epc_obj) {
				//Debug::Arr($station,'Department', __FILE__, __LINE__, __METHOD__,10);

				$eplf = TTnew( 'ExceptionPolicyListFactory' );
				$eplf->getByExceptionPolicyControlID( $id );
				if ( $eplf->getRecordCount() > 0 ) {
					foreach( $eplf as $ep_obj ) {
						if ( isset($type_options[$ep_obj->getType()]) ) {
							$ep_objs[$ep_obj->getType()] = $ep_obj;
						} else {
							//Delete exceptions that aren't part of the product.
							Debug::Text('Deleting exception outside product edition: '. $ep_obj->getID(), __FILE__, __LINE__, __METHOD__,10);

							$ep_obj->setDeleted(TRUE);
							if ( $ep_obj->isValid() ) {
								$ep_obj->Save();
							}
						}
					}
				}

				$exceptions = array();
				if ( isset($type_options) AND is_array($type_options) AND count($type_options) > 0 ) {
					foreach( $type_options as $exception_type => $exception_name ) {
						if ( isset($ep_objs[$exception_type]) ) {
							$ep_obj = $ep_objs[$exception_type];

							$exceptions[$ep_obj->getType()] = array(
																	'id' => $ep_obj->getId(),
																	'active' => $ep_obj->getActive(),
																	'type_id' => $ep_obj->getType(),
																	'name' => Option::getByKey( $ep_obj->getType(), $type_options ),
																	'severity_id' => $ep_obj->getSeverity(),
																	'email_notification_id' => $ep_obj->getEmailNotification(),
																	'demerit' => $ep_obj->getDemerit(),
																	'grace' => (int)$ep_obj->getGrace(),
																	'is_enabled_grace' => $ep_obj->isEnabledGrace( $ep_obj->getType() ),
																	'watch_window' => (int)$ep_obj->getWatchWindow(),
																	'is_enabled_watch_window' => $ep_obj->isEnabledWatchWindow( $ep_obj->getType() )
																	);
						}
					}
					unset($exception_name);
				}
				//var_dump($type_options, $ep_objs,$exceptions);
				
				//Populate default values.
				$default_exceptions = $epf->getExceptionTypeDefaultValues( array_keys($exceptions), $current_company->getProductEdition() );
				$exceptions = array_merge( $exceptions, $default_exceptions );

				$data = array(
									'id' => $epc_obj->getId(),
									'name' => $epc_obj->getName(),
									'exceptions' => $exceptions,
									'created_date' => $epc_obj->getCreatedDate(),
									'created_by' => $epc_obj->getCreatedBy(),
									'updated_date' => $epc_obj->getUpdatedDate(),
									'updated_by' => $epc_obj->getUpdatedBy(),
									'deleted_date' => $epc_obj->getDeletedDate(),
									'deleted_by' => $epc_obj->getDeletedBy()
								);
			}
		} elseif ( $action != 'submit' ) {
			//Populate default values.
			$exceptions = $epf->getExceptionTypeDefaultValues( NULL, $current_company->getProductEdition() );

			$data = array( 'exceptions' => $exceptions );
		}
		//print_r($data);

		//Select box options;
		$data['severity_options'] = $epf->getOptions('severity');
		$data['email_notification_options'] = $epf->getOptions('email_notification');

		$smarty->assign_by_ref('data', $data);

		break;
}

$smarty->assign_by_ref('epf', $epf);
$smarty->assign_by_ref('epcf', $epcf);

$smarty->display('policy/EditExceptionPolicyControl.tpl');
?>