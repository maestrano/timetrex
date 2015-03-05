<?php
/*********************************************************************************
 * TimeTrex is a Payroll and Time Management program developed by
 * TimeTrex Software Inc. Copyright (C) 2003 - 2014 TimeTrex Software Inc.
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

	This class has to treat punch and punch_control data as if they are one.

*/

/**
 * @package API\Punch
 */
class APIPunch extends APIFactory {
	protected $main_class = 'PunchFactory';

	public function __construct() {
		parent::__construct(); //Make sure parent constructor is always called.

		return TRUE;
	}

	function getUserPunch( $user_id = NULL, $epoch = NULL, $station_id = NULL, $company_id = NULL ) {
		if ( $epoch == '' ) {
			$epoch = TTDate::getTime();
		}

		if ( !is_numeric( $user_id	) ) {
			$user_id = $this->getCurrentUserObject()->getId();
		}

		if ( !is_numeric( $company_id ) ) {
			$company_id = $this->getCurrentCompanyObject()->getId();
		}

		if ( !is_numeric( $station_id ) ) {
			$station_id = getStationID(); //API.inc
		}

		//Must call APIStation->getCurrentStation( $station_id = NULL ) first, so the Station ID cookie can be set and passed to this.
		//Check if station is allowed.
		$current_station = FALSE;
		$slf = new StationListFactory();
		$slf->getByStationIdandCompanyId( $station_id, $company_id );
		if ( $slf->getRecordCount() == 1 ) {
			$current_station = $slf->getCurrent();
			$station_type = $current_station->getType();
		}
		unset($slf);

		Debug::Text('Station ID: '. $station_id .' User ID: '. $user_id .' Epoch: '. $epoch, __FILE__, __LINE__, __METHOD__, 10);
		if ( is_object($current_station) AND $current_station->checkAllowed( $user_id, $station_id, $station_type ) == TRUE ) {
			Debug::Text('Station Allowed! ID: '. $station_id .' User ID: '. $user_id .' Epoch: '. $epoch, __FILE__, __LINE__, __METHOD__, 10);
			//Get user object from ID.
			$ulf = TTNew('UserListFactory');
			$ulf->getByIdAndCompanyId( $user_id, $company_id );
			if ( $ulf->getRecordCount() == 1 ) {
				$user_obj = $ulf->getCurrent();

				$plf = TTNew('PunchListFactory');				
				$data = $plf->getDefaultPunchSettings( $user_obj, $epoch, $current_station, $this->getPermissionObject() );
				$data['date_stamp'] = TTDate::getAPIDate( 'DATE', $epoch );
				$data['time_stamp'] = TTDate::getAPIDate( 'DATE+TIME', $epoch );
				$data['punch_date'] = TTDate::getAPIDate( 'DATE', $epoch );
				$data['punch_time'] = TTDate::getAPIDate( 'TIME', $epoch );
				$data['original_time_stamp'] = TTDate::getAPIDate( 'DATE+TIME', $epoch );
				$data['actual_time_stamp'] = TTDate::getAPIDate( 'DATE+TIME', $epoch );
				$data['first_name'] = $user_obj->getFirstName();
				$data['last_name'] = $user_obj->getLastName();
				$data['station_id'] = $current_station->getId();
				
				if ( isset($data ) ) {
					Debug::Arr($data, 'Punch Data: ', __FILE__, __LINE__, __METHOD__, 10);
					return $this->returnHandler( $data );
				}
			}
		} else {
			Debug::Text('Station IS NOT Allowed! ID: '. $station_id .' User ID: '. $user_id .' Epoch: '. $epoch, __FILE__, __LINE__, __METHOD__, 10);
			$validator_obj = new Validator();
			$validator_stats = array('total_records' => 1, 'valid_records' => 0 );

			$error_message = TTi18n::gettext('You are not authorized to punch in or out from this station!');
			$validator_obj->isTrue( 'user_name', FALSE, $error_message );
			$validator[0] = $validator_obj->getErrorsArray();

			return $this->returnHandler( FALSE, 'VALIDATION', TTi18n::getText('INVALID DATA'), $validator, $validator_stats );
		}

		return FALSE;
	}

	function setUserPunch( $data, $validate_only = FALSE ) {
		if ( !$this->getPermissionObject()->Check('punch', 'enabled')
				OR !( $this->getPermissionObject()->Check('punch', 'punch_in_out') ) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}

		//Force proper settings.
		$data['user_id'] = $this->getCurrentUserObject()->getId();
		if ( isset($data['transfer']) AND $data['transfer'] == TRUE ) {
			$data['type_id'] = 10;
			$data['status_id'] = 10;
		}

		//Make sure employees don't try to circumvent the disabled timestamp field. By allowing a small variance.
		//This also prevents them from leaving the punch window open by accident, then submitting an old punch time.
		$tmp_epoch = TTDate::getTime();
		$max_variance = 300; //5minutes.
		if ( isset( $data['time_stamp'] ) AND ( TTDate::parseDateTime( $data['time_stamp'] ) > ( $tmp_epoch + $max_variance ) OR TTDate::parseDateTime( $data['time_stamp'] ) < ( $tmp_epoch - $max_variance ) ) ) {
			Debug::Text('Punch timestamp outside max variance: '. TTDate::getDate('DATE+TIME', TTDate::parseDateTime( $data['time_stamp'] ) ), __FILE__, __LINE__, __METHOD__, 10);
			$data['time_stamp'] = TTDate::getDate('DATE+TIME', $tmp_epoch );
		}
		unset( $tmp_epoch, $data['punch_date'], $data['punch_time'], $data['actual_time_stamp'], $data['original_time_stamp']); //Only accept full time_stamp field, ignore punch_date/punch_time. This also helps prevent circumvention by the user.

		$validator_stats = array('total_records' => 1, 'valid_records' => 0 );

		$lf = TTnew( 'PunchFactory' );
		$lf->StartTransaction();

		//We can't set the PunchControlID from $data, otherwise findPunchControlID() will never work.
		//This causes transfer punches to fail.
		if ( isset($data['punch_control_id']) ) {
			$tmp_punch_control_id = $data['punch_control_id'];
			unset($data['punch_control_id']);
		}

		$lf->setObjectFromArray( $data );

		if ( isset($data['status_id']) AND $data['status_id'] == 20 AND isset($tmp_punch_control_id) AND $tmp_punch_control_id != '' ) {
			$lf->setPunchControlID( $tmp_punch_control_id );
		} else {
			$lf->setPunchControlID( $lf->findPunchControlID() );
		}
		unset($tmp_punch_control_id);

		$key = 0;
		$is_valid = $lf->isValid();
		if ( $is_valid == TRUE ) {
			Debug::Text('Saving data...', __FILE__, __LINE__, __METHOD__, 10);
			if ( $validate_only == TRUE ) {
				$save_result[$key] = TRUE;
				$validator_stats['valid_records']++;
			} else {
				//Save Punch object and start on PunchControl
				if ( $save_result[$key] = $lf->Save( FALSE ) == TRUE ) {
					unset($data['id']); //ID must be removed so it doesn't get confused with PunchControlID
					Debug::Text('Saving PCF data... Punch Control ID: '. $lf->getPunchControlID(), __FILE__, __LINE__, __METHOD__, 10);
					$pcf = TTnew( 'PunchControlFactory' );

					$pcf->setId( $lf->getPunchControlID() );
					$pcf->setPunchObject( $lf );

					$pcf->setObjectFromArray( $data );

					$pcf->setEnableStrictJobValidation( TRUE );
					$pcf->setEnableCalcUserDateID( TRUE );
					$pcf->setEnableCalcTotalTime( TRUE );
					$pcf->setEnableCalcSystemTotalTime( TRUE );
					$pcf->setEnableCalcWeeklySystemTotalTime( TRUE );
					$pcf->setEnableCalcUserDateTotal( TRUE );
					$pcf->setEnableCalcException( TRUE );
					$pcf->setEnablePreMatureException( TRUE ); //Enable pre-mature exceptions at this point.

					Debug::Arr($lf->data, 'Punch Object: ', __FILE__, __LINE__, __METHOD__, 10);
					Debug::Arr($pcf->data, 'Punch Control Object: ', __FILE__, __LINE__, __METHOD__, 10);
					if ( $pcf->isValid() ) {
						$validator_stats['valid_records']++;
						if ( $pcf->Save( TRUE, TRUE ) != TRUE ) { //Force isNew() lookup.
							$is_valid = $pcf_valid = FALSE;
						}
					} else {
						$is_valid = $pcf_valid = FALSE;
					}
				}
			}
		}

		if ( $is_valid == FALSE ) {
			Debug::Text('Data is Invalid...', __FILE__, __LINE__, __METHOD__, 10);

			$lf->FailTransaction(); //Just rollback this single record, continue on to the rest.

			Debug::Text('PF Data is Invalid...', __FILE__, __LINE__, __METHOD__, 10);
			$validator[$key] = $lf->Validator->getErrorsArray();
			//Merge PCF validation errors onto array.
			if ( isset($pcf) AND $pcf_valid == FALSE ) {
				Debug::Text('PCF Data is Invalid...', __FILE__, __LINE__, __METHOD__, 10);
				$validator[$key] += $pcf->Validator->getErrorsArray();
			}
		}

		$lf->CommitTransaction();

		if ( $validator_stats['valid_records'] > 0 AND $validator_stats['total_records'] == $validator_stats['valid_records'] ) {
			if ( $validator_stats['total_records'] == 1 ) {
				return $this->returnHandler( $save_result[$key] ); //Single valid record
			} else {
				return $this->returnHandler( TRUE, 'SUCCESS', TTi18n::getText('MULTIPLE RECORDS SAVED'), $save_result, $validator_stats ); //Multiple valid records
			}
		} else {
			return $this->returnHandler( FALSE, 'VALIDATION', TTi18n::getText('INVALID DATA'), $validator, $validator_stats );
		}

		return FALSE;
	}

	/**
	 * Get default punch data for creating new punches.
	 * @return array
	 */
	function getPunchDefaultData( $user_id = NULL, $date = NULL, $punch_control_id = NULL, $previous_punch_id = NULL ) {
		$company_obj = $this->getCurrentCompanyObject();

		if ( !is_numeric( $user_id	) ) {
			$user_id = $this->getCurrentUserObject()->getId();
		}

		Debug::Text('Getting punch default data... User ID: '. $user_id .' Date: '. $date .' Punch Control ID: '. $punch_control_id .' Previous Punch Id: '. $previous_punch_id, __FILE__, __LINE__, __METHOD__, 10);

		$data = array(
						'status_id' => 10,
						'type_id' => 10,
						'user_id' => $this->getCurrentUserObject()->getId(),
						'punch_time' => TTDate::getAPIDate( 'TIME', TTDate::strtotime( '12:00 PM' ) ),
						'branch_id' => $this->getCurrentUserObject()->getDefaultBranch(),
						'department_id' => $this->getCurrentUserObject()->getDefaultDepartment(),
						'job_id' => $this->getCurrentUserObject()->getDefaultJob(),
						'job_item_id' => $this->getCurrentUserObject()->getDefaultJobItem(),
					);

		//If user_id is specified, use their default branch/department.
		$ulf = TTnew( 'UserListFactory' );
		$ulf->getByIdAndCompanyId( $user_id, $company_obj->getID() );
		if ( $ulf->getRecordCount() == 1 ) {
			$user_obj = $ulf->getCurrent();

			$data['user_id'] = $user_obj->getID();
			$data['branch_id'] = $user_obj->getDefaultBranch();
			$data['department_id'] = $user_obj->getDefaultDepartment();
			$data['job_id'] = $user_obj->getDefaultJob();
			$data['job_item_id'] = $user_obj->getDefaultJobItem();
		}
		unset($ulf, $user_obj);

		if ( $punch_control_id > 0 ) {
			$pclf = TTnew('PunchControlListFactory');
			$pclf->getByIDAndCompanyID( $punch_control_id, $company_obj->getId() );
			if ( $pclf->getRecordCount() == 1 ) {
				$prev_punch_control_obj = $pclf->getCurrent();

				$data = array_merge( $data, (array)$prev_punch_control_obj->getObjectAsArray( array('branch_id' => TRUE, 'department_id' => TRUE, 'job_id' => TRUE, 'job_item_id' => TRUE, 'quantity' => TRUE, 'bad_quantity' => TRUE, 'note' => TRUE, 'other_id1' => TRUE, 'other_id2' => TRUE, 'other_id3' => TRUE, 'other_id4' => TRUE, ) ) );
			}
			unset($pclf, $prev_punch_control_obj);
		}

		//IF specified, get the previous punch object to determine the next punch type/status.
		if ( $previous_punch_id > 0 ) {
			$plf = TTnew('PunchListFactory');
			$plf->getByCompanyIDAndId( $company_obj->getId(), $previous_punch_id );
			if ( $plf->getRecordCount() == 1 ) {
				$prev_punch_obj = $plf->getCurrent();
				$data['type_id'] = $prev_punch_obj->getNextType();
				//$data['status_id'] = $prev_punch_obj->getNextStatus(); //Flex handles this.
				Debug::Text('Getting previous punch default data... Type ID: '. $data['type_id'], __FILE__, __LINE__, __METHOD__, 10);
			}
			unset($plf, $prev_punch_obj);
		}

		return $this->returnHandler( $data );
	}

	/**
	 * Get all necessary dates for building the TimeSheet in a single call, this is mainly as a performance optimization.
	 * @param array $data filter data
	 * @return array
	 */
	function getTimeSheetDates( $base_date ) {
		$epoch = TTDate::parseDateTime( $base_date );

		if ( $epoch == '' ) {
			$epoch = TTDate::getTime();
		}

		$start_date = TTDate::getBeginWeekEpoch( $epoch, $this->getCurrentUserPreferenceObject()->getStartWeekDay() );
		$end_date = TTDate::getEndWeekEpoch( $epoch, $this->getCurrentUserPreferenceObject()->getStartWeekDay() );

		$retarr = array(
						'base_date' => $epoch,
						'start_date' => $start_date,
						'end_date' => $end_date,
						'base_display_date' => TTDate::getAPIDate('DATE', $epoch ),
						'start_display_date' => TTDate::getAPIDate('DATE', $start_date ),
						'end_display_date' => TTDate::getAPIDate('DATE', $end_date),
						);

		return $retarr;
	}

	/**
	 * Get punch data for one or more punches.
	 * @param array $data filter data
	 * @return array
	 */
	function getPunch( $data = NULL, $disable_paging = FALSE ) {
		if ( !$this->getPermissionObject()->Check('punch', 'enabled')
				OR !( $this->getPermissionObject()->Check('punch', 'view') OR $this->getPermissionObject()->Check('punch', 'view_own') OR $this->getPermissionObject()->Check('punch', 'view_child') ) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}

		$data = $this->initializeFilterAndPager( $data, $disable_paging );

		$data['filter_data']['permission_children_ids'] = $this->getPermissionObject()->getPermissionChildren( 'punch', 'view' );

		//As a performance optimization to prevent the API from having to do additional date lookups, accept a single "date" field, that converts
		//into start/end dates.
		if ( isset($data['filter_data']['date']) AND $data['filter_data']['date'] != '' ) {
			$data['filter_data']['start_date'] = TTDate::getBeginWeekEpoch( $data['filter_data']['date'], $this->getCurrentUserPreferenceObject()->getStartWeekDay() );
			$data['filter_data']['end_date'] = TTDate::getEndWeekEpoch( $data['filter_data']['date'], $this->getCurrentUserPreferenceObject()->getStartWeekDay() );
		}

		//No filter data, restrict to last pay period as a performance optimization when hundreds of thousands of punches exist.
		//The issue with this though is that the API doesn't know what the filter criteria is, so it can't display this to the user.
		//Make sure we don't apply a pay_period filter if we are looking up just one punch.
		//if ( count($data['filter_data']) == 1 AND !isset($data['filter_data']['pay_period_ids']) ) {
		if ( !isset($data['filter_data']['id']) AND !isset($data['filter_data']['pay_period_ids']) AND !isset($data['filter_data']['pay_period_id']) AND ( !isset($data['filter_data']['start_date']) AND !isset($data['filter_data']['end_date']) ) ) {
			Debug::Text('Adding default filter data...', __FILE__, __LINE__, __METHOD__, 10);
			$pplf = TTnew( 'PayPeriodListFactory' );
			$pplf->getByCompanyId( $this->getCurrentCompanyObject()->getId() );
			$pay_period_ids = array_keys((array)$pplf->getArrayByListFactory( $pplf, FALSE, FALSE ) );
			if ( isset($pay_period_ids[0]) AND isset($pay_period_ids[1]) ) {
				$data['filter_data']['pay_period_ids'] = array($pay_period_ids[0], $pay_period_ids[1]);
			}
			unset($pplf, $pay_period_ids);
		}

		$blf = TTnew( 'PunchListFactory' );
		if ( DEPLOYMENT_ON_DEMAND == TRUE ) { $blf->setQueryStatementTimeout( 60000 ); }
		$blf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCurrentCompanyObject()->getId(), $data['filter_data'], $data['filter_items_per_page'], $data['filter_page'], NULL, $data['filter_sort'] );
		Debug::Text('Record Count: '. $blf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
		if ( $blf->getRecordCount() > 0 ) {
			$this->getProgressBarObject()->start( $this->getAMFMessageID(), $blf->getRecordCount() );

			$this->setPagerObject( $blf );

			foreach( $blf as $b_obj ) {
				$retarr[] = $b_obj->getObjectAsArray( $data['filter_columns'], $data['filter_data']['permission_children_ids'] );

				$this->getProgressBarObject()->set( $this->getAMFMessageID(), $blf->getCurrentRow() );
			}

			$this->getProgressBarObject()->stop( $this->getAMFMessageID() );

			return $this->returnHandler( $retarr );
		}

		return $this->returnHandler( TRUE ); //No records returned.
	}

	/**
	 * Get only the fields that are common across all records in the search criteria. Used for Mass Editing of records.
	 * @param array $data filter data
	 * @return array
	 */
	function getCommonPunchData( $data ) {
		return Misc::arrayIntersectByRow( $this->stripReturnHandler( $this->getPunch( $data, TRUE ) ) );
	}

	/**
	 * Validate punch data for one or more punches.
	 * @param array $data punch data
	 * @return array
	 */
	function validatePunch( $data ) {
		return $this->setPunch( $data, TRUE );
	}

	/**
	 * Set punch data for one or more punches.
	 * @param array $data punch data
	 * @return array
	 */
	function setPunch( $data, $validate_only = FALSE ) {
		$validate_only = (bool)$validate_only;

		if ( !is_array($data) ) {
			return $this->returnHandler( FALSE );
		}

		if ( !$this->getPermissionObject()->Check('punch', 'enabled')
				OR !( $this->getPermissionObject()->Check('punch', 'edit') OR $this->getPermissionObject()->Check('punch', 'edit_own') OR $this->getPermissionObject()->Check('punch', 'edit_child') OR $this->getPermissionObject()->Check('punch', 'add') ) ) {
			return	$this->getPermissionObject()->PermissionDenied();
		}

		if ( $validate_only == TRUE ) {
			Debug::Text('Validating Only!', __FILE__, __LINE__, __METHOD__, 10);
			$permission_children_ids = FALSE;
		} else {
			//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
			$permission_children_ids = $this->getPermissionChildren();
		}

		extract( $this->convertToMultipleRecords($data) );
		Debug::Text('Received data for: '. $total_records .' Punchs', __FILE__, __LINE__, __METHOD__, 10);
		Debug::Arr($data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10);

		$validator_stats = array('total_records' => $total_records, 'valid_records' => 0 );
		if ( is_array($data) ) {
			$this->getProgressBarObject()->start( $this->getAMFMessageID(), $total_records );

			foreach( $data as $key => $row ) {
				$primary_validator = new Validator();
				$lf = TTnew( 'PunchListFactory' );
				$lf->StartTransaction();
				if ( isset($row['id']) AND $row['id'] > 0 ) {
					//Modifying existing object.
					//Get punch object, so we can only modify just changed data for specific records if needed.
					//Use the special getAPIByIdAndCompanyId() function as it returns additional columns needed for mass editing.
					//These additional columns break editing a single record if we make $lf the current object.
					$lf->getAPIByIdAndCompanyId( $row['id'], $this->getCurrentCompanyObject()->getId() );
					if ( $lf->getRecordCount() == 1 ) {
						//Object exists, check edit permissions
						if (
							$validate_only == TRUE
							OR
								(
								$this->getPermissionObject()->Check('punch', 'edit')
									OR ( $this->getPermissionObject()->Check('punch', 'edit_own') AND $this->getPermissionObject()->isOwner( $lf->getCurrent()->getCreatedBy(), $lf->getCurrent()->getPunchControlObject()->getUser() ) === TRUE )
									OR ( $this->getPermissionObject()->Check('punch', 'edit_child') AND $this->getPermissionObject()->isChild( $lf->getCurrent()->getPunchControlObject()->getUser(), $permission_children_ids ) === TRUE )
								) ) {

							Debug::Text('Row Exists, getting current data: ', $row['id'], __FILE__, __LINE__, __METHOD__, 10);
							//If we make the current object be $lf, it fails saving the punch because extra columns exist.
							//$lf = $lf->getCurrent();
							//$row = array_merge( $lf->getObjectAsArray( array('id' => TRUE, 'user_id' => TRUE, 'transfer' => TRUE, 'type_id' => TRUE, 'status_id' => TRUE, 'time_stamp' => TRUE, 'punch_control_id' => TRUE, 'actual_time_stamp' => TRUE, 'original_time_stamp' => TRUE, 'schedule_id' => TRUE, 'station_id' => TRUE, 'longitude' => TRUE, 'latitude' => TRUE, 'deleted' => TRUE) ), $row );
							$row = array_merge( $lf->getCurrent()->getObjectAsArray(), $row );
						} else {
							$primary_validator->isTrue( 'permission', FALSE, TTi18n::gettext('Edit permission denied') );
						}
					} else {
						//Object doesn't exist.
						$primary_validator->isTrue( 'id', FALSE, TTi18n::gettext('Edit permission denied, record does not exist') );
					}
				} else {
					//Adding new object, check ADD permissions.
					if (	!( $validate_only == TRUE
								OR
								( $this->getPermissionObject()->Check('punch', 'add')
									AND
									(
										$this->getPermissionObject()->Check('punch', 'edit')
										OR ( isset($row['user_id']) AND $this->getPermissionObject()->Check('punch', 'edit_own') AND $this->getPermissionObject()->isOwner( FALSE, $row['user_id'] ) === TRUE ) //We don't know the created_by of the user at this point, but only check if the user is assigned to the logged in person.
										OR ( isset($row['user_id']) AND $this->getPermissionObject()->Check('punch', 'edit_child') AND $this->getPermissionObject()->isChild( $row['user_id'], $permission_children_ids ) === TRUE )
									)
								)
							) ) {
						$primary_validator->isTrue( 'permission', FALSE, TTi18n::gettext('Add permission denied') );
					}
				}
				Debug::Arr($row, 'Data: ', __FILE__, __LINE__, __METHOD__, 10);

				$is_valid = $pcf_valid = $primary_validator->isValid();
				if ( $is_valid == TRUE ) { //Check to see if all permission checks passed before trying to save data.
					Debug::Text('Setting object data...', __FILE__, __LINE__, __METHOD__, 10);

					//If no punch control id is sent, make sure its blank so setObjectFromArray can try to automatically determine it.
					//Mainly for importing.
					if ( !isset($row['punch_control_id']) ) {
						$row['punch_control_id'] = FALSE;
					}

					//Try to automatically determine punch data, mainly for importing punches.
					if ( isset($row['time_stamp']) AND isset($row['user_id']) AND ( !isset( $row['status_id'] ) OR ( isset($row['status_id']) AND ( $row['status_id'] == '' OR $row['status_id'] == 0 ) ) ) ) {
						$plf = TTNew('PunchListFactory');
						$plf->getPreviousPunchByUserIDAndEpoch( $row['user_id'], $row['time_stamp'] );
						if ( $plf->getRecordCount() > 0 ) {
							$prev_punch_obj = $plf->getCurrent();
							$row['status_id'] = $prev_punch_obj->getNextStatus();
							Debug::Text('Automatically determine status: '. $row['status_id'], __FILE__, __LINE__, __METHOD__, 10);
						} else {
							$row['status_id'] = 10; //In
						}
					}

					$lf->setObjectFromArray( $row );

					$is_valid = $lf->isValid();
					if ( $is_valid == TRUE ) {
						Debug::Text('Saving data...', __FILE__, __LINE__, __METHOD__, 10);
						if ( $validate_only == TRUE ) {
							$save_result[$key] = TRUE;
							$validator_stats['valid_records']++;
						} else {
							//Save Punch object and start on PunchControl
							$save_result[$key] = $lf->Save( FALSE );
							if ( $save_result[$key] == TRUE ) {
								unset($row['id']); //ID must be removed so it doesn't get confused with PunchControlID
								Debug::Text('Saving PCF data... Punch Control ID: '. $lf->getPunchControlID(), __FILE__, __LINE__, __METHOD__, 10);
								$pcf = TTnew( 'PunchControlFactory' );
								$pcf->setId( $lf->getPunchControlID() );
								$pcf->setPunchObject( $lf );

								//This is important when adding/editing a punch, without it there can be issues calculating exceptions
								//because if a specific punch was modified that caused the day to change, smartReCalculate
								//may only be able to recalculate a single day, instead of both.
								//$pcf->setUser( $row['user_id'] ); //Set from PunchObject.
								$old_date_stamp = ( is_object( $lf->getPunchControlObject() ) ) ? $lf->getPunchControlObject()->getDateStamp() : 0;
								if ( $old_date_stamp != 0 ) {
									Debug::Text('Setting old date stamp to: '. TTDate::getDate('DATE', $old_date_stamp ), __FILE__, __LINE__, __METHOD__, 10);
									$pcf->setOldDateStamp( $old_date_stamp );
								}

								$pcf->setObjectFromArray( $row );

								$pcf->setEnableStrictJobValidation( TRUE );
								$pcf->setEnableCalcUserDateID( TRUE );
								$pcf->setEnableCalcTotalTime( TRUE );
								$pcf->setEnableCalcSystemTotalTime( TRUE );
								$pcf->setEnableCalcWeeklySystemTotalTime( TRUE );
								$pcf->setEnableCalcUserDateTotal( TRUE );
								$pcf->setEnableCalcException( TRUE );

								if ( $pcf->isValid() ) {
									$validator_stats['valid_records']++;
									if ( $pcf->Save( TRUE, TRUE ) != TRUE ) { //Force isNew() lookup.
										$is_valid = $pcf_valid = FALSE;
									}
								} else {
									$is_valid = $pcf_valid = FALSE;
								}
							}
							Debug::Text('Save Result ID: '. (int)$save_result[$key], __FILE__, __LINE__, __METHOD__, 10);
						}
					}
				}

				if ( $is_valid == FALSE ) {
					Debug::Text('Data is Invalid...', __FILE__, __LINE__, __METHOD__, 10);

					$lf->FailTransaction(); //Just rollback this single record, continue on to the rest.

					if ( $primary_validator->isValid() == FALSE ) {
						$validator[$key] = $primary_validator->getErrorsArray();
					} else {
						Debug::Text('PF Data is Invalid...', __FILE__, __LINE__, __METHOD__, 10);
						$validator[$key] = $lf->Validator->getErrorsArray();
						//Merge PCF validation errors onto array.
						if ( isset($pcf) AND $pcf_valid == FALSE ) {
							Debug::Text('PCF Data is Invalid...', __FILE__, __LINE__, __METHOD__, 10);
							$validator[$key] += $pcf->Validator->getErrorsArray();
						}
					}
				}

				$lf->CommitTransaction();

				$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
			}

			$this->getProgressBarObject()->stop( $this->getAMFMessageID() );

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
	 * Delete one or more punchs.
	 * @param array $data punch data
	 * @return array
	 */
	function deletePunch( $data ) {
		if ( is_numeric($data) ) {
			$data = array($data);
		}

		if ( !is_array($data) ) {
			return $this->returnHandler( FALSE );
		}

		if ( !$this->getPermissionObject()->Check('punch', 'enabled')
				OR !( $this->getPermissionObject()->Check('punch', 'delete') OR $this->getPermissionObject()->Check('punch', 'delete_own') OR $this->getPermissionObject()->Check('punch', 'delete_child') ) ) {
			return	$this->getPermissionObject()->PermissionDenied();
		}

		//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
		$permission_children_ids = $this->getPermissionChildren();

		Debug::Text('Received data for: '. count($data) .' Punchs', __FILE__, __LINE__, __METHOD__, 10);
		Debug::Arr($data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10);

		$total_records = count($data);
		$validator_stats = array('total_records' => $total_records, 'valid_records' => 0 );
		if ( is_array($data) ) {
			$this->getProgressBarObject()->start( $this->getAMFMessageID(), $total_records );

			foreach( $data as $key => $id ) {
				$primary_validator = new Validator();
				$lf = TTnew( 'PunchListFactory' );
				$lf->StartTransaction();
				if ( is_numeric($id) ) {
					//Modifying existing object.
					//Get punch object, so we can only modify just changed data for specific records if needed.
					$lf->getByIdAndCompanyId( $id, $this->getCurrentCompanyObject()->getId() );
					if ( $lf->getRecordCount() == 1 ) {
						//Object exists, check edit permissions
						//NOTE: Make sure we pass the user the punch is assigned too for proper delete_child permissions to work correctly.
						if ( $this->getPermissionObject()->Check('punch', 'delete')
								OR ( $this->getPermissionObject()->Check('punch', 'delete_own') AND $this->getPermissionObject()->isOwner( $lf->getCurrent()->getCreatedBy(), $lf->getCurrent()->getPunchControlObject()->getUser() ) === TRUE )
								OR ( $this->getPermissionObject()->Check('punch', 'delete_child') AND $this->getPermissionObject()->isChild( $lf->getCurrent()->getPunchControlObject()->getUser(), $permission_children_ids ) === TRUE )) {
							Debug::Text('Record Exists, deleting record: '. $id, __FILE__, __LINE__, __METHOD__, 10);
							$lf = $lf->getCurrent();
						} else {
							$primary_validator->isTrue( 'permission', FALSE, TTi18n::gettext('Delete permission denied') );
						}
					} else {
						//Object doesn't exist.
						$primary_validator->isTrue( 'id', FALSE, TTi18n::gettext('Delete permission denied, record does not exist') );
					}
				} else {
					$primary_validator->isTrue( 'id', FALSE, TTi18n::gettext('Delete permission denied, record does not exist') );
				}

				//Debug::Arr($lf, 'AData: ', __FILE__, __LINE__, __METHOD__, 10);

				$is_valid = $primary_validator->isValid();
				if ( $is_valid == TRUE ) { //Check to see if all permission checks passed before trying to save data.
					Debug::Text('Attempting to delete record...', __FILE__, __LINE__, __METHOD__, 10);
					Debug::Arr($lf->data, 'Current Data: ', __FILE__, __LINE__, __METHOD__, 10);

					$lf->setUser( $lf->getPunchControlObject()->getUser() );
					$lf->setDeleted(TRUE);

					$is_valid = $lf->isValid();
					if ( $is_valid == TRUE ) {
						$lf->setEnableCalcTotalTime( TRUE );
						$lf->setEnableCalcSystemTotalTime( TRUE );
						$lf->setEnableCalcWeeklySystemTotalTime( TRUE );
						$lf->setEnableCalcUserDateTotal( TRUE );
						$lf->setEnableCalcException( TRUE );

						Debug::Text('Record Deleted...', __FILE__, __LINE__, __METHOD__, 10);
						$save_result[$key] = $lf->Save();
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
				}

				$lf->CommitTransaction();

				$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
			}

			$this->getProgressBarObject()->stop( $this->getAMFMessageID() );

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

	function getMealAndBreakTotalTime( $data, $disable_paging = FALSE  ) {
		return PunchFactory::calcMealAndBreakTotalTime( $this->getPunch( $data, TRUE ) );
	}
}
?>
