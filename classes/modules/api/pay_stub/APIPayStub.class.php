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
 * $Id: APIPayStub.class.php 2196 2008-10-14 16:08:54Z ipso $
 * $Date: 2008-10-14 09:08:54 -0700 (Tue, 14 Oct 2008) $
 */

/**
 * @package API\PayStub
 */
class APIPayStub extends APIFactory {
	protected $main_class = 'PayStubFactory';

	public function __construct() {
		parent::__construct(); //Make sure parent constructor is always called.

		return TRUE;
	}

	/**
	 * Get pay_stub data for one or more pay_stubes.
	 * @param array $data filter data
	 * @return array
	 */
	function getPayStub( $data = NULL, $disable_paging = FALSE, $format = FALSE, $hide_employer_rows = TRUE ) {
		if ( !$this->getPermissionObject()->Check('pay_stub','enabled')
				OR !( $this->getPermissionObject()->Check('pay_stub','view') OR $this->getPermissionObject()->Check('pay_stub','view_own')  OR $this->getPermissionObject()->Check('pay_stub','view_child')  ) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}
		$data = $this->initializeFilterAndPager( $data, $disable_paging );

		$format = Misc::trimSortPrefix( $format );

		$data['filter_data']['permission_children_ids'] = $this->getPermissionObject()->getPermissionChildren( 'pay_stub', 'view' );

		if ( $this->getPermissionObject()->Check('pay_stub','view') == FALSE AND $this->getPermissionObject()->Check('pay_stub','view_child') == FALSE ) {
			//Only display PAID pay stubs.
			$data['filter_data']['status_id'] = array(40);
		}

		$pslf = TTnew( 'PayStubListFactory' );
		$pslf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCurrentCompanyObject()->getId(), $data['filter_data'], $data['filter_items_per_page'], $data['filter_page'], NULL, $data['filter_sort'] );
		Debug::Text('Record Count: '. $pslf->getRecordCount() .' Format: '. $format, __FILE__, __LINE__, __METHOD__, 10);

		if ( strtolower($format) == 'pdf' ) {
			if ( $pslf->getRecordCount() > 0 ) {
				$this->getProgressBarObject()->setDefaultKey( $this->getAMFMessageID() );
				$this->getProgressBarObject()->start( $this->getAMFMessageID(), $pslf->getRecordCount() );
				$pslf->setProgressBarObject( $this->getProgressBarObject() ); //Expose progress bar object to pay stub object.

				$output = $pslf->getPayStub( $pslf, (bool)$hide_employer_rows );

				$this->getProgressBarObject()->stop( $this->getAMFMessageID() );

				return Misc::APIFileDownload( 'pay_stub.pdf', 'application/pdf', $output );
			}
		} elseif ( strpos( strtolower($format), 'cheque_' ) !== FALSE ) {
			if ( $pslf->getRecordCount() > 0 ) {
				$this->getProgressBarObject()->setDefaultKey( $this->getAMFMessageID() );
				$this->getProgressBarObject()->start( $this->getAMFMessageID(), $pslf->getRecordCount() );
				$pslf->setProgressBarObject( $this->getProgressBarObject() ); //Expose progress bar object to pay stub object.

				$output = $pslf->exportPayStub( $pslf, strtolower($format) );

				$this->getProgressBarObject()->stop( $this->getAMFMessageID() );

				return Misc::APIFileDownload( 'checks_'. str_replace(array('/',',',' '), '_', TTDate::getDate('DATE', time() ) ) .'.pdf', 'application/pdf', $output );
			}
		} elseif ( strpos( strtolower($format), 'eft_' ) !== FALSE ) {
			if ( $pslf->getRecordCount() > 0 ) {
				$this->getProgressBarObject()->setDefaultKey( $this->getAMFMessageID() );
				$this->getProgressBarObject()->start( $this->getAMFMessageID(), $pslf->getRecordCount() );
				$pslf->setProgressBarObject( $this->getProgressBarObject() ); //Expose progress bar object to pay stub object.

				$output = $pslf->exportPayStub( $pslf, strtolower($format) );

				$this->getProgressBarObject()->stop( $this->getAMFMessageID() );

				//Include file creation number in the exported file name, so the user knows what it is without opening the file,
				//and can generate multiple files if they need to match a specific number.
				$ugdlf = TTnew( 'UserGenericDataListFactory' );
				$ugdlf->getByCompanyIdAndScriptAndDefault( $this->getCurrentCompanyObject()->getId(), 'PayStubFactory', TRUE );
				if ( $ugdlf->getRecordCount() > 0 ) {
					$ugd_obj = $ugdlf->getCurrent();
					$setup_data = $ugd_obj->getData();
				}

				if ( isset($setup_data) ) {
					$file_creation_number = $setup_data['file_creation_number']++;
				} else {
					$file_creation_number = 0;
				}
				
				return Misc::APIFileDownload( 'eft_'.$file_creation_number.'_'.date('Y_m_d').'.txt', 'application/pdf', $output );
			}
		} else {
			if ( $pslf->getRecordCount() > 0 ) {
				$this->getProgressBarObject()->start( $this->getAMFMessageID(), $pslf->getRecordCount() );

				$this->setPagerObject( $pslf );

				foreach( $pslf as $ps_obj ) {
					$retarr[] = $ps_obj->getObjectAsArray( $data['filter_columns'], $data['filter_data']['permission_children_ids'] );

					$this->getProgressBarObject()->set( $this->getAMFMessageID(), $pslf->getCurrentRow() );
				}

				$this->getProgressBarObject()->stop( $this->getAMFMessageID() );

				return $this->returnHandler( $retarr );
			}

			return $this->returnHandler( TRUE ); //No records returned.
		}
	}

	/**
	 * Get only the fields that are common across all records in the search criteria. Used for Mass Editing of records.
	 * @param array $data filter data
	 * @return array
	 */
	function getCommonPayStubData( $data ) {
		return Misc::arrayIntersectByRow( $this->stripReturnHandler( $this->getPayStub( $data, TRUE ) ) );
	}

	/**
	 * Validate pay_stub data for one or more pay_stubes.
	 * @param array $data pay_stub data
	 * @return array
	 */
	function validatePayStub( $data ) {
		return $this->setPayStub( $data, TRUE );
	}

	/**
	 * Set pay_stub data for one or more pay_stubes.
	 * @param array $data pay_stub data
	 * @return array
	 */
	function setPayStub( $data, $validate_only = FALSE ) {
		$validate_only = (bool)$validate_only;

		if ( !is_array($data) ) {
			return $this->returnHandler( FALSE );
		}

		if ( !$this->getPermissionObject()->Check('pay_stub','enabled')
				OR !( $this->getPermissionObject()->Check('pay_stub','edit') OR $this->getPermissionObject()->Check('pay_stub','edit_own') OR $this->getPermissionObject()->Check('pay_stub','edit_child') OR $this->getPermissionObject()->Check('pay_stub','add') ) ) {
			return  $this->getPermissionObject()->PermissionDenied();
		}

		if ( $validate_only == TRUE ) {
			Debug::Text('Validating Only!', __FILE__, __LINE__, __METHOD__, 10);
		}

		extract( $this->convertToMultipleRecords($data) );
		Debug::Text('Received data for: '. $total_records .' PayStubs', __FILE__, __LINE__, __METHOD__, 10);
		Debug::Arr($data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10);

		$validator_stats = array('total_records' => $total_records, 'valid_records' => 0 );
		if ( is_array($data) ) {
			$this->getProgressBarObject()->start( $this->getAMFMessageID(), $total_records );

			foreach( $data as $key => $row ) {
				$primary_validator = new Validator();
				$lf = TTnew( 'PayStubListFactory' );
				$lf->StartTransaction();
				if ( isset($row['id']) AND $row['id'] > 0 ) {
					//Modifying existing object.
					//Get pay_stub object, so we can only modify just changed data for specific records if needed.
					$lf->getByIdAndCompanyId( $row['id'], $this->getCurrentCompanyObject()->getId() );
					if ( $lf->getRecordCount() == 1 ) {
						//Object exists, check edit permissions
						if (
							  $validate_only == TRUE
							  OR
								(
								$this->getPermissionObject()->Check('pay_stub','edit')
									OR ( $this->getPermissionObject()->Check('pay_stub','edit_own') AND $this->getPermissionObject()->isOwner( $lf->getCurrent()->getCreatedBy(), $lf->getCurrent()->getID() ) === TRUE )
								) ) {

							Debug::Text('Row Exists, getting current data: ', $row['id'], __FILE__, __LINE__, __METHOD__, 10);
							$lf = $lf->getCurrent();
							$row = array_merge( $lf->getObjectAsArray(), $row );
						} else {
							$primary_validator->isTrue( 'permission', FALSE, TTi18n::gettext('Edit permission denied') );
						}
					} else {
						//Object doesn't exist.
						$primary_validator->isTrue( 'id', FALSE, TTi18n::gettext('Edit permission denied, record does not exist') );
					}
				} else {
					//Adding new object, check ADD permissions.
					$primary_validator->isTrue( 'permission', $this->getPermissionObject()->Check('pay_stub','add'), TTi18n::gettext('Add permission denied') );
				}
				Debug::Arr($row, 'Data: ', __FILE__, __LINE__, __METHOD__, 10);

				$is_valid = $primary_validator->isValid();
				if ( $is_valid == TRUE ) { //Check to see if all permission checks passed before trying to save data.
					Debug::Text('Setting object data...', __FILE__, __LINE__, __METHOD__, 10);

					$lf->setObjectFromArray( $row );

					//Force Company ID to current company.
					//$lf->setCompany( $this->getCurrentCompanyObject()->getId() );

					if ( $validate_only == TRUE ) {
						$lf->validate_only = TRUE;
					}

					$is_valid = $lf->isValid();
					if ( $is_valid == TRUE ) {
						Debug::Text('Saving data...', __FILE__, __LINE__, __METHOD__, 10);
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
					$lf->FailTransaction();
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
	 * Delete one or more pay_stubs.
	 * @param array $data pay_stub data
	 * @return array
	 */
	function deletePayStub( $data ) {
		if ( is_numeric($data) ) {
			$data = array($data);
		}

		if ( !is_array($data) ) {
			return $this->returnHandler( FALSE );
		}

		if ( !$this->getPermissionObject()->Check('pay_stub','enabled')
				OR !( $this->getPermissionObject()->Check('pay_stub','delete') OR $this->getPermissionObject()->Check('pay_stub','delete_own') OR $this->getPermissionObject()->Check('pay_stub','delete_child') ) ) {
			return  $this->getPermissionObject()->PermissionDenied();
		}

		Debug::Text('Received data for: '. count($data) .' PayStubs', __FILE__, __LINE__, __METHOD__, 10);
		Debug::Arr($data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10);

		$total_records = count($data);
        $validator_stats = array('total_records' => $total_records, 'valid_records' => 0 );
		if ( is_array($data) ) {
			$this->getProgressBarObject()->start( $this->getAMFMessageID(), $total_records );

			foreach( $data as $key => $id ) {
				$primary_validator = new Validator();
				$lf = TTnew( 'PayStubListFactory' );
				$lf->StartTransaction();
				if ( is_numeric($id) ) {
					//Modifying existing object.
					//Get pay_stub object, so we can only modify just changed data for specific records if needed.
					$lf->getByIdAndCompanyId( $id, $this->getCurrentCompanyObject()->getId() );
					if ( $lf->getRecordCount() == 1 ) {
						//Object exists, check edit permissions
						if ( $this->getPermissionObject()->Check('pay_stub','delete')
								OR ( $this->getPermissionObject()->Check('pay_stub','delete_own') AND $this->getPermissionObject()->isOwner( $lf->getCurrent()->getCreatedBy(), $lf->getCurrent()->getID() ) === TRUE ) ) {
							Debug::Text('Record Exists, deleting record: ', $id, __FILE__, __LINE__, __METHOD__, 10);
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
					$lf->setDeleted(TRUE);

					$is_valid = $lf->isValid();
					if ( $is_valid == TRUE ) {
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

	function generatePayStubs( $pay_period_ids, $user_ids = NULL, $enable_correction = FALSE ) {
		global $profiler;
		Debug::Text('Generate Pay Stubs!', __FILE__, __LINE__, __METHOD__,10);

		if ( !$this->getPermissionObject()->Check('pay_period_schedule','enabled')
				OR !( $this->getPermissionObject()->Check('pay_period_schedule','edit') OR $this->getPermissionObject()->Check('pay_period_schedule','edit_own') ) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}

		if ( !is_array($pay_period_ids) ) {
			$pay_period_ids = array($pay_period_ids);
		}

		if ( $user_ids !== NULL AND !is_array($user_ids) AND $user_ids > 0 ) {
			$user_ids = array($user_ids);
		} elseif ( is_array($user_ids) AND isset($user_ids[0]) AND $user_ids[0] == 0 ) {
			$user_ids = NULL;
		}

		foreach($pay_period_ids as $pay_period_id) {
			Debug::text('Pay Period ID: '. $pay_period_id, __FILE__, __LINE__, __METHOD__,10);

			$pplf = TTnew( 'PayPeriodListFactory' );
			$pplf->getByIdAndCompanyId($pay_period_id, $this->getCurrentCompanyObject()->getId() );

			$epoch = TTDate::getTime();

			foreach ($pplf as $pay_period_obj) {
				Debug::text('Pay Period Schedule ID: '. $pay_period_obj->getPayPeriodSchedule(), __FILE__, __LINE__, __METHOD__,10);

				if ( $pay_period_obj->isPreviousPayPeriodClosed() == TRUE ) {
					//Grab all users for pay period
					$ppsulf = TTnew( 'PayPeriodScheduleUserListFactory' );
					if ( is_array($user_ids) AND count($user_ids) > 0 ) {
						Debug::text('Generating pay stubs for specific users...', __FILE__, __LINE__, __METHOD__,10);

						TTLog::addEntry( $this->getCurrentCompanyObject()->getId(), 500, TTi18n::gettext('Calculating Company Pay Stubs for Pay Period').': '. $pay_period_id, $this->getCurrentUserObject()->getId(), 'pay_stub' ); //Notice
						$ppsulf->getByCompanyIDAndPayPeriodScheduleIdAndUserID( $this->getCurrentCompanyObject()->getId(), $pay_period_obj->getPayPeriodSchedule(), $user_ids );
					} else {
						TTLog::addEntry( $this->getCurrentCompanyObject()->getId(), 500, TTi18n::gettext('Calculating Employee Pay Stub for Pay Period').': '. $pay_period_id, $this->getCurrentUserObject()->getId(), 'pay_stub' );
						$ppsulf->getByCompanyIDAndPayPeriodScheduleId( $this->getCurrentCompanyObject()->getId(), $pay_period_obj->getPayPeriodSchedule() );
					}
					$total_pay_stubs = $ppsulf->getRecordCount();

					$this->getProgressBarObject()->start( $this->getAMFMessageID(), $total_pay_stubs, NULL, TTi18n::getText('Generating Paystubs...') );

					//Delete existing pay stub. Make sure we only
					//delete pay stubs that are the same as what we're creating.
					$pslf = TTnew( 'PayStubListFactory' );
					$pslf->getByPayPeriodId( $pay_period_obj->getId() );
					foreach ( $pslf as $pay_stub_obj ) {
						if ( is_array($user_ids) AND count($user_ids) > 0 AND in_array( $pay_stub_obj->getUser(), $user_ids ) == FALSE ) {
							continue; //Only generating pay stubs for individual employees, skip ones not in the list.
						}
						Debug::text('Existing Pay Stub: '. $pay_stub_obj->getId(), __FILE__, __LINE__, __METHOD__,10);

						//Check PS End Date to match with PP End Date
						//So if an ROE was generated, it won't get deleted when they generate all other Pay Stubs
						//later on.
						if ( $pay_stub_obj->getStatus() <= 25
								AND $pay_stub_obj->getTainted() === FALSE
								AND TTDate::getMiddleDayEpoch( $pay_stub_obj->getEndDate() ) == TTDate::getMiddleDayEpoch( $pay_period_obj->getEndDate() ) ) {
							Debug::text('Deleting pay stub: '. $pay_stub_obj->getId(), __FILE__, __LINE__, __METHOD__,10);
							$pay_stub_obj->setDeleted(TRUE);
							$pay_stub_obj->Save();
						} else {
							Debug::text('Pay stub does not need regenerating, or it is LOCKED! ID: '. $pay_stub_obj->getID() .' Status: '. $pay_stub_obj->getStatus() .' Tainted: '. (int)$pay_stub_obj->getTainted() .' Pay Stub End Date: '. $pay_stub_obj->getEndDate() .' Pay Period End Date: '. $pay_period_obj->getEndDate(), __FILE__, __LINE__, __METHOD__,10);
						}
					}

					$i=1;
					foreach ($ppsulf as $pay_period_schdule_user_obj) {
						Debug::text('Pay Period User ID: '. $pay_period_schdule_user_obj->getUser(), __FILE__, __LINE__, __METHOD__,10);
						Debug::text('Total Pay Stubs: '. $total_pay_stubs .' - '. ceil( 1 / (100 / $total_pay_stubs) ) , __FILE__, __LINE__, __METHOD__,10);

						$profiler->startTimer( 'Calculating Pay Stub' );
						//Calc paystubs.
						$cps = new CalculatePayStub();
						$cps->setEnableCorrection( (bool)$enable_correction );
						$cps->setUser( $pay_period_schdule_user_obj->getUser() );
						$cps->setPayPeriod( $pay_period_obj->getId() );
						$cps->calculate();
						unset($cps);
						$profiler->stopTimer( 'Calculating Pay Stub' );

						$this->getProgressBarObject()->set( $this->getAMFMessageID(), $i );

						//sleep(1); /////////////////////////////// FOR TESTING ONLY //////////////////

						$i++;
					}
					unset($ppsulf);

					$this->getProgressBarObject()->stop( $this->getAMFMessageID() );
				} else {
					UserGenericStatusFactory::queueGenericStatus( TTi18n::gettext('ERROR'), 10, TTi18n::gettext('Pay period prior to %1 is not closed, please close all previous pay periods and try again...', array( TTDate::getDate('DATE', $pay_period_obj->getStartDate() ).' -> '. TTDate::getDate('DATE', $pay_period_obj->getEndDate() ) ) ), NULL );
				}
			}
		}

		if ( UserGenericStatusFactory::isStaticQueue() == TRUE ) {
			$ugsf = TTnew( 'UserGenericStatusFactory' );
			$ugsf->setUser( $this->getCurrentUserObject()->getId() );
			$ugsf->setBatchID( $ugsf->getNextBatchId() );
			$ugsf->setQueue( UserGenericStatusFactory::getStaticQueue() );
			$ugsf->saveQueue();
			$user_generic_status_batch_id = $ugsf->getBatchID();
		} else {
			$user_generic_status_batch_id = FALSE;
		}
		unset($ugsf);

		return $this->returnHandler( TRUE, TRUE, FALSE, FALSE, FALSE, $user_generic_status_batch_id );
	}
}
?>
