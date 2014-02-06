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
 * $Id: EditStationUser.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('station','enabled')
		OR !( $permission->Check('station','assign') ) ) {

	$permission->Redirect( FALSE ); //Redirect

}

$smarty->assign('title', TTi18n::gettext($title = 'Edit Station')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'id',
												'station_data'
												) ) );

$sf = TTnew( 'StationFactory' );

$action = Misc::findSubmitButton();
switch ($action) {
	case 'submit':
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

		Debug::Arr($station_data['user_ids'],'Selected Users', __FILE__, __LINE__, __METHOD__,10);

		$sf->setId($station_data['id']);
		$sf->setUser( $station_data['user_ids'] );

		if ( $sf->isValid() ) {
			$sf->Save(FALSE);

			Redirect::Page( URLBuilder::getURL(NULL, 'StationList.php') );

			break;
		}

	default:
		if ( isset($station_data['id']) ) {
			$id = $station_data['id'];
		}

		if ( isset($id) ) {
			BreadCrumb::setCrumb($title);

			$slf = TTnew( 'StationListFactory' );
			$sulf = TTnew( 'StationUserListFactory' );

			$slf->GetByIdAndCompanyId($id, $current_company->getId() );

			foreach ($slf as $station) {
				//Debug::Arr($station,'Department', __FILE__, __LINE__, __METHOD__,10);

				if ( isset( $station_data['user_ids'] ) ) {
					Debug::Text('Using Selected Users', __FILE__, __LINE__, __METHOD__,10);
					//Use selected values
                    $user_ids = $station_data['user_ids'];
				} else {
					Debug::Text('Grabbing Users from DB', __FILE__, __LINE__, __METHOD__,10);
					$sulf->getByStationId( $station->getId() );

					$user_ids = array();
					foreach ($sulf as $station_user) {
						$user_ids[] = $station_user->getUser();
					}
				}
				$station_data = array(
									'id' => $station->getId(),
									'status' => TTi18n::gettext($station->getStatus()),
									'type' => $station->getType(),
									'station' => $station->getStation(),
									'source' => $station->getSource(),
									'description' => $station->getDescription(),
									'user_ids' => $user_ids,
									'created_date' => $station->getCreatedDate(),
									'created_by' => $station->getCreatedBy(),
									'updated_date' => $station->getUpdatedDate(),
									'updated_by' => $station->getUpdatedBy(),
									'deleted_date' => $station->getDeletedDate(),
									'deleted_by' => $station->getDeletedBy()
								);
			}
		}

		//Select box options;
		$station_data['status_options'] = $sf->getOptions('status');
		$station_data['type_options'] = $sf->getOptions('type');

		$user_options = UserListFactory::getByCompanyIdArray( $current_company->getId(), FALSE );
		$user_options = Misc::prependArray( array( -1 => TTi18n::gettext('-- ALL --')), $user_options );
		$station_data['user_options'] = $user_options;

		$smarty->assign_by_ref('station_data', $station_data);

		break;
}

$smarty->assign_by_ref('sf', $sf);

$smarty->display('station/EditStationUser.tpl');
?>