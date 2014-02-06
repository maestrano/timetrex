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
 * $Id: APIUser.class.php 2196 2008-10-14 16:08:54Z ipso $
 * $Date: 2008-10-14 09:08:54 -0700 (Tue, 14 Oct 2008) $
 */

/**
 * @package API\Users
 */
class APIUserReportData extends APIFactory {
	protected $main_class = 'UserReportDataFactory';

	public function __construct() {
		parent::__construct(); //Make sure parent constructor is always called.

		return TRUE;
	}

	/**
	 * Get user data for one or more users.
	 * @param array $data filter data
	 * @return array
	 */
	function getUserReportData( $data = NULL ) {

		$data = $this->initializeFilterAndPager( $data );

		//Only allow getting report data for currently logged in user.
		$data['filter_data']['user_id'] = $this->getCurrentUserObject()->getId();

		Debug::Arr($data, 'Getting User Report Data: ', __FILE__, __LINE__, __METHOD__, 10);

		$ugdlf = TTnew( 'UserReportDataListFactory' );
		$ugdlf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCurrentCompanyObject()->getId(), $data['filter_data'], $data['filter_items_per_page'], $data['filter_page'], NULL, $data['filter_sort'] );
		Debug::Text('Record Count: '. $ugdlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
		if ( $ugdlf->getRecordCount() > 0 ) {
			$this->setPagerObject( $ugdlf );

			foreach( $ugdlf as $ugd_obj ) {
				$retarr[] = $ugd_obj->getObjectAsArray( $data['filter_columns'] );
			}

			return $this->returnHandler( $retarr );
		}

		return $this->returnHandler( TRUE );
	}

	/**
	 * Set user data for one or more users.
	 * @param array $data user data
	 * @return array
	 */
	function setUserReportData( $data, $validate_only = FALSE  ) {
		if ( !is_array($data) ) {
			return $this->returnHandler( FALSE );
		}
		$validate_only = (bool)$validate_only;

		extract( $this->convertToMultipleRecords($data) );
		Debug::Text('Received data for: '. $total_records .' Users', __FILE__, __LINE__, __METHOD__, 10);
		Debug::Arr($data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10);

		if ( $validate_only == TRUE ) {
			Debug::Text('Validating Only!', __FILE__, __LINE__, __METHOD__, 10);
			$permission_children_ids = FALSE;
		} else {
			//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
			$permission_children_ids = $this->getPermissionChildren();
		}

		$validator_stats = array('total_records' => $total_records, 'valid_records' => 0 );
		if ( is_array($data) ) {
			foreach( $data as $key => $row ) {
				$row['company_id'] = $this->getCurrentUserObject()->getCompany();

				if ( !isset($row['user_id'])
						OR !( $this->getPermissionObject()->Check('user','view') OR ( $this->getPermissionObject()->Check('user','view_child') AND $this->getPermissionObject()->isChild( $row['user_id'], $permission_children_ids ) === TRUE ) ) ) {
					//Force user_id to currently logged in user.
					Debug::Text('Forcing user_id...', __FILE__, __LINE__, __METHOD__, 10);
					$row['user_id'] = $this->getCurrentUserObject()->getId();
				}

				$primary_validator = new Validator();
				$lf = TTnew( 'UserReportDataListFactory' );
				$lf->StartTransaction();
				if ( isset($row['id']) ) {
					//Modifying existing object.
					//Get user object, so we can only modify just changed data for specific records if needed.
					$lf->getByUserIdAndId( $row['user_id'], $row['id'] );
					if ( $lf->getRecordCount() == 1 ) {
						//Object exists, check edit permissions
						$row = array_merge( $lf->getCurrent()->getObjectAsArray(), $row );
					} else {
						//Object doesn't exist.
						$primary_validator->isTrue( 'id', FALSE, TTi18n::gettext('Edit permission denied, employee does not exist') );
					}
				} else {
					//Adding new object, check ADD permissions.
					//$primary_validator->isTrue( 'permission', $this->getPermissionObject()->Check('user','add'), TTi18n::gettext('Add permission denied') );
				}
				Debug::Arr($row, 'User Report Data: ', __FILE__, __LINE__, __METHOD__, 10);

				$is_valid = $primary_validator->isValid();
				if ( $is_valid == TRUE ) { //Check to see if all permission checks passed before trying to save data.
					Debug::Text('Attempting to save User Data...', __FILE__, __LINE__, __METHOD__, 10);
					$lf->setObjectFromArray( $row );

					//Force Company ID to current company.
					$lf->setCompany( $this->getCurrentCompanyObject()->getId() );
					//$lf->setUser( $this->getCurrentUserObject()->getId() ); //Need to be able support copying reports to other users.

					if ( $validate_only == TRUE ) {
						$lf->validate_only = TRUE;
					}

					$is_valid = $lf->isValid();
					if ( $is_valid == TRUE ) {
						Debug::Text('Saving User Data...', __FILE__, __LINE__, __METHOD__, 10);
						if ( $validate_only == TRUE ) {
							$save_result[$key] = TRUE;
						} else {
							$save_result[$key] = $lf->Save();
						}
						$validator_stats['valid_records']++;
					}
				}

				if ( $is_valid == FALSE ) {
					Debug::Text('Data is Invalid...', __FILE__, __LINE__, __METHOD__, 10);

					$lf->FailTransaction(); //Just rollback this single record, continue on to the rest.

					if ( $primary_validator->isValid() == FALSE ) {
						$validator[$key] = $primary_validator->getErrorsArray();
					} else {
						$validator[$key] = $lf->Validator->getErrorsArray();
					}
				} elseif ( $validate_only == TRUE ) {
					//Always fail transaction when valididate only is used, as  is saved to different tables immediately.
					$lf->FailTransaction();
				}

				$lf->CommitTransaction();
			}

			if ( $validator_stats['valid_records'] > 0 AND $validator_stats['total_records'] == $validator_stats['valid_records'] ) {
				if ( $validator_stats['total_records'] == 1 ) {
					return $this->returnHandler( $save_result[$key] ); //Single valid record
				} else {
					return $this->returnHandler( TRUE, 'SUCCESS', TTi18n::getText('MULTIPLE RECORDS SAVED'), $save_result, $validator_stats ); //Multiple valid records
				}
			} else {
				return $this->returnHandler( FALSE, 'VALIDATION', TTi18n::getText('INVALID DATA'), $validator, $validator_stats );
			}
		}

		return $this->returnHandler( FALSE );
	}

	/**
	 * Validate user data for one or more users.
	 * @param array $data user data
	 * @return array
	 */
	function validateUserReportData( $data ) {
		return $this->setUserReportData( $data, TRUE );
	}

	/**
	 * Delete one or more users.
	 * @param array $data user data
	 * @return array
	 */
	function deleteUserReportData( $data ) {
		Debug::Arr($data, 'DataA: ', __FILE__, __LINE__, __METHOD__, 10);

		if ( is_numeric($data) ) {
			$data = array($data);
		}

		if ( !is_array($data) ) {
			return $this->returnHandler( FALSE );
		}

		Debug::Text('Received data for: '. count($data) .' Users', __FILE__, __LINE__, __METHOD__, 10);
		Debug::Arr($data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10);

		$total_records = count($data);
        $validator_stats = array('total_records' => $total_records, 'valid_records' => 0 );
		if ( is_array($data) ) {
			foreach( $data as $key => $id ) {
				$primary_validator = new Validator();
				$lf = TTnew( 'UserReportDataListFactory' );
				$lf->StartTransaction();
				if ( is_numeric($id) ) {
					//Modifying existing object.
					//Get user object, so we can only modify just changed data for specific records if needed.
					$lf->getByUserIdAndId( $this->getCurrentUserObject()->getId(), $id );
					if ( $lf->getRecordCount() == 1 ) {
						//Object exists
						Debug::Text('User Report Data Exists, deleting record: ', $id, __FILE__, __LINE__, __METHOD__, 10);
						$lf = $lf->getCurrent();
					} else {
						//Object doesn't exist.
						$primary_validator->isTrue( 'id', FALSE, TTi18n::gettext('Delete permission denied, report data does not exist') );
					}
				} else {
					$primary_validator->isTrue( 'id', FALSE, TTi18n::gettext('Delete permission denied, report data does not exist') );
				}

				//Debug::Arr($lf, 'AData: ', __FILE__, __LINE__, __METHOD__, 10);

				$is_valid = $primary_validator->isValid();
				if ( $is_valid == TRUE ) { //Check to see if all permission checks passed before trying to save data.
					Debug::Text('Attempting to delete user report data...', __FILE__, __LINE__, __METHOD__, 10);
					$lf->setDeleted(TRUE);

					$is_valid = $lf->isValid();
					if ( $is_valid == TRUE ) {
						Debug::Text('User Deleted...', __FILE__, __LINE__, __METHOD__, 10);
						$save_result[$key] = $lf->Save();
						$validator_stats['valid_records']++;
					}
				}

				if ( $is_valid == FALSE ) {
					Debug::Text('User Report Data is Invalid...', __FILE__, __LINE__, __METHOD__, 10);

					$lf->FailTransaction(); //Just rollback this single record, continue on to the rest.

					if ( $primary_validator->isValid() == FALSE ) {
						$validator[$key] = $primary_validator->getErrorsArray();
					} else {
						$validator[$key] = $lf->Validator->getErrorsArray();
					}
				}

				$lf->CommitTransaction();
			}

			if ( $validator_stats['valid_records'] > 0 AND $validator_stats['total_records'] == $validator_stats['valid_records'] ) {
				if ( $validator_stats['total_records'] == 1 ) {
					return $this->returnHandler( $save_result[$key] ); //Single valid record
				} else {
					return $this->returnHandler( TRUE, 'SUCCESS', TTi18n::getText('MULTIPLE RECORDS SAVED'), $save_result, $validator_stats ); //Multiple valid records
				}
			} else {
				return $this->returnHandler( FALSE, 'VALIDATION', TTi18n::getText('INVALID DATA'), $validator, $validator_stats );
			}
		}

		return $this->returnHandler( FALSE );
	}

	/**
	 * Share or copy report to other users.
	 * @param array $user_report_data_ids User Report Data row IDs
	 * @param array $destination_user_ids User IDs to copy reports to
	 * @return bool
	 */
	function shareUserReportData( $user_report_data_ids, $destination_user_ids ) {
		if ( is_numeric($user_report_data_ids) ) {
			$user_report_data_ids = array($user_report_data_ids);
		}

		if ( is_numeric($destination_user_ids) ) {
			$destination_user_ids = array($destination_user_ids);
		}

		Debug::Arr($user_report_data_ids, 'User Report Data IDs: ', __FILE__, __LINE__, __METHOD__, 10);
		Debug::Arr($destination_user_ids, 'Destination User IDs: ', __FILE__, __LINE__, __METHOD__, 10);

		$src_rows = $this->stripReturnHandler( $this->getUserReportData( array('filter_data' => array('id' => $user_report_data_ids ) ), TRUE ) );
		if ( is_array( $src_rows ) AND count($src_rows) > 0 ) {
			Debug::Arr($src_rows, 'SRC Rows: ', __FILE__, __LINE__, __METHOD__, 10);
			$dst_rows = array();

			$x=0;
			foreach( $src_rows as $key => $row ) {
				unset($src_rows[$key]['id'],$src_rows[$key]['created_date'],$src_rows[$key]['created_by']); //Clear fields that can't be copied
				$src_rows[$key]['name'] = Misc::generateShareName( $this->getCurrentUserObject()->getFullName(), $row['name']); //Generate unique name

				$description = NULL;
				if ( isset($row['description']) AND $row['description'] != '') {
					$description = $row['description']."\n";
				}
				$src_rows[$key]['description'] = $description.TTi18n::getText('Report shared by').' '. $this->getCurrentUserObject()->getFullName() .' '. TTi18n::getText('on').' '. TTDate::getDate('DATE+TIME', time() );

				//Should we always disable the default setting?
				//Should we copy any schedules that go along with each saved report? This could cause a lot of issues with mass emails being sent out without intention.

				//Copy to destination users.
				if ( is_array($destination_user_ids) ) {
					foreach( $destination_user_ids as $destination_user_id ) {
						$dst_rows[$x] = $src_rows[$key];
						$dst_rows[$x]['user_id'] = $destination_user_id;
						$x++;
					}
				}
			}
			unset($src_rows);
			Debug::Arr($dst_rows, 'DST Rows: ', __FILE__, __LINE__, __METHOD__, 10);

			return $this->setUserReportData( $dst_rows ); //Save copied rows
		}

		return $this->returnHandler( FALSE );
	}
}
?>
