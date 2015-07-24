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


/**
 * @package Core
 */
class UserDateTotalFactory extends Factory {
	protected $table = 'user_date_total';
	protected $pk_sequence_name = 'user_date_total_id_seq'; //PK Sequence name

	protected $user_obj = NULL;
	protected $pay_period_obj = NULL;
	protected $punch_control_obj = NULL;
	protected $job_obj = NULL;
	protected $job_item_obj = NULL;
	protected $pay_code_obj = NULL;

	public $alternate_date_stamps = NULL; //Stores alternate date stamps that also need to be recalculated.

	protected $calc_system_total_time = FALSE;
	protected $timesheet_verification_check = FALSE;
	static $calc_future_week = FALSE; //Used for BiWeekly overtime policies to schedule future week recalculating.

	function _getFactoryOptions( $name ) {
		//Attempt to get the edition of the currently logged in users company, so we can better tailor the columns to them.
		$product_edition_id = Misc::getCurrentCompanyProductEdition();

		$retval = NULL;
		switch( $name ) {
			case 'start_type':
			case 'end_type':
				$retval = array(
										10 => TTi18n::gettext('Normal'),
										20 => TTi18n::gettext('Lunch'),
										30 => TTi18n::gettext('Break')
									);
				break;
			case 'object_type':
				//In order to not have to dig into punches when calculating policies, we would need to create user_date_total rows for lunch/break
				//time taken.

				//We have to continue to use two columns to determine the type of hours and the pay code its associated with.
				//Otherwise we have no idea what is Lunch Time vs Total Time vs Break Time, since they could all go to one pay code.
				$retval = array(
										5 => TTi18n::gettext('System'),
										10 => TTi18n::gettext('Worked'), //Used to be "Total"
										20 => TTi18n::gettext('Regular'),
										25 => TTi18n::gettext('Absence'),
										30 => TTi18n::gettext('Overtime'),
										40 => TTi18n::gettext('Premium'),
										
										//We need to treat Absence time like Worked Time, and calculate policies (ie: Overtime) based on it, without affecting the original entry.
										//As it can be split between regular,overtime policies just like worked time can.
										50 => TTi18n::gettext('Absence (Taken)'),
										
										100 => TTi18n::gettext('Lunch'), //Lunch Policy (auto-add/deduct)
										101 => TTi18n::gettext('Lunch (Taken)'), //Time punched out for lunch.
										
										110 => TTi18n::gettext('Break'), //Break Policy (auto-add/deduct)
										111 => TTi18n::gettext('Break (Taken)'), //Time punched out for break.
									);
				break;
			case 'columns':
				$retval = array(
										'-1000-first_name' => TTi18n::gettext('First Name'),
										'-1002-last_name' => TTi18n::gettext('Last Name'),
										'-1005-user_status' => TTi18n::gettext('Employee Status'),
										'-1010-title' => TTi18n::gettext('Title'),
										'-1039-group' => TTi18n::gettext('Group'),
										'-1040-default_branch' => TTi18n::gettext('Default Branch'),
										'-1050-default_department' => TTi18n::gettext('Default Department'),
										'-1160-branch' => TTi18n::gettext('Branch'),
										'-1170-department' => TTi18n::gettext('Department'),

										'-1200-object_type' => TTi18n::gettext('Type'),
										'-1205-name' => TTi18n::gettext('Pay Code'),
										'-1210-date_stamp' => TTi18n::gettext('Date'),
										'-1290-total_time' => TTi18n::gettext('Time'),

										'-1300-quantity' => TTi18n::gettext('QTY'),
										'-1300-bad_quantity' => TTi18n::gettext('Bad QTY'),

										'-1800-note' => TTi18n::gettext('Note'),

										'-1900-override' => TTi18n::gettext('O/R'), //Override

										'-2000-created_by' => TTi18n::gettext('Created By'),
										'-2010-created_date' => TTi18n::gettext('Created Date'),
										'-2020-updated_by' => TTi18n::gettext('Updated By'),
										'-2030-updated_date' => TTi18n::gettext('Updated Date'),
							);

				if ( $product_edition_id >= 15 ) {
					$retarr['-1180-job'] = TTi18n::gettext('Job');
					$retarr['-1190-job_item'] = TTi18n::gettext('Task');
				}
				ksort($retval);
				break;
			case 'list_columns':
				$retval = Misc::arrayIntersectByKey( $this->getOptions('default_display_columns'), Misc::trimSortPrefix( $this->getOptions('columns') ) );
				break;
			case 'default_display_columns': //Columns that are displayed by default.
				if ( $product_edition_id >= 20 ) {
					$retval = array(
									'date_stamp',
									'total_time',
									'object_type',
									'name',
									'branch',
									'department',
									'job',
									'job_item',
									'note',
									'override',
									);
				} else {
					$retval = array(
									'date_stamp',
									'total_time',
									'object_type',
									'name',
									'branch',
									'department',
									'note',
									'override',
									);
				}
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = array(
								);
				break;
			case 'linked_columns': //Columns that are linked together, mainly for Mass Edit, if one changes, they all must.
				$retval = array(
								);
				break;
		}

		return $retval;
	}

	function _getVariableToFunctionMap( $data ) {
		$variable_function_map = array(
						'id' => 'ID',
						'user_id' => 'User',
						'date_stamp' => 'DateStamp',
						'pay_period_id' => 'PayPeriod',

						//Legacy status/type functions.
						'status_id' => 'Status',
						'type_id' => 'Type',
						
						'object_type_id' => 'ObjectType',
						'object_type' => FALSE,
						'pay_code_id' => 'PayCode',
						'src_object_id' => 'SourceObject', //This must go after PayCodeID, so if the user is saving an absence we overwrite any previously selected PayCode
						'policy_name' => FALSE,
						
						'punch_control_id' => 'PunchControlID',
						'branch_id' => 'Branch',
						'branch' => FALSE,
						'department_id' => 'Department',
						'department' => FALSE,
						'job_id' => 'Job',
						'job' => FALSE,
						'job_item_id' => 'JobItem',
						'job_item' => FALSE,
						'quantity' => 'Quantity',
						'bad_quantity' => 'BadQuantity',
						'start_type_id' => 'StartType',
						'start_time_stamp' => 'StartTimeStamp',
						'end_type_id' => 'EndType',
						'end_time_stamp' => 'EndTimeStamp',
						'total_time' => 'TotalTime',
						'actual_total_time' => 'ActualTotalTime',

						'currency_id' => 'Currency',
						'currency_rate' => 'CurrencyRate',
						'base_hourly_rate' => 'BaseHourlyRate',
						'hourly_rate' => 'HourlyRate',
						'total_time_amount' => 'TotalTimeAmount',
						'hourly_rate_with_burden' => 'HourlyRateWithBurden',
						'total_time_amount_with_burden' => 'TotalTimeAmountWithBurden',
						
						'name' => FALSE,
						'override' => 'Override',
						'note' => 'Note',

						'first_name' => FALSE,
						'last_name' => FALSE,
						'user_status_id' => FALSE,
						'user_status' => FALSE,
						'group_id' => FALSE,
						'group' => FALSE,
						'title_id' => FALSE,
						'title' => FALSE,
						'default_branch_id' => FALSE,
						'default_branch' => FALSE,
						'default_department_id' => FALSE,
						'default_department' => FALSE,

						'deleted' => 'Deleted',
						);
		return $variable_function_map;
	}

	function getUserObject() {
		return $this->getGenericObject( 'UserListFactory', $this->getUser(), 'user_obj' );
	}

	function getPayPeriodObject() {
		return $this->getGenericObject( 'PayPeriodListFactory', $this->getPayPeriod(), 'pay_period_obj' );
	}

	function getPunchControlObject() {
		return $this->getGenericObject( 'PunchControlListFactory', $this->getPunchControlID(), 'punch_control_obj' );
	}

	function getPayCodeObject() {
		return $this->getGenericObject( 'PayCodeListFactory', $this->getPayCode(), 'pay_code_obj' );
	}

	function getJobObject() {
		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			return $this->getGenericObject( 'JobListFactory', $this->getJob(), 'job_obj' );
		}

		return FALSE;
	}
	function getJobItemObject() {
		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			return $this->getGenericObject( 'JobItemListFactory', $this->getJobItem(), 'job_item_obj' );
		}
		
		return FALSE;
	}


	function getUser() {
		if ( isset($this->data['user_id']) ) {
			return (int)$this->data['user_id'];
		}
	}
	function setUser($id) {
		$id = trim($id);

		$ulf = TTnew( 'UserListFactory' );

		//Need to be able to support user_id=0 for open shifts. But this can cause problems with importing punches with user_id=0.
		if ( $this->Validator->isResultSetWithRows(	'user',
															$ulf->getByID($id),
															TTi18n::gettext('Invalid User')
															) ) {
			$this->data['user_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getPayPeriod() {
		if ( isset($this->data['pay_period_id']) ) {
			return (int)$this->data['pay_period_id'];
		}

		return FALSE;
	}
	function setPayPeriod( $id = NULL ) {
		$id = trim($id);

		if ( $id == NULL ) {
			$id = (int)PayPeriodListFactory::findPayPeriod( $this->getUser(), $this->getDateStamp() );
		}

		$pplf = TTnew( 'PayPeriodListFactory' );

		//Allow NULL pay period, incase its an absence or something in the future.
		//Cron will fill in the pay period later.
		if (
				$id == 0
				OR
				$this->Validator->isResultSetWithRows(	'pay_period',
														$pplf->getByID($id),
														TTi18n::gettext('Invalid Pay Period')
														) ) {
			$this->data['pay_period_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getDateStamp( $raw = FALSE ) {
		if ( isset($this->data['date_stamp']) ) {
			if ( $raw === TRUE ) {
				return $this->data['date_stamp'];
			} else {
				if ( !is_numeric( $this->data['date_stamp'] ) ) { //Optmization to avoid converting it when run in CalculatePolicy's loops
					$this->data['date_stamp'] = TTDate::strtotime( $this->data['date_stamp'] );
				}
				return $this->data['date_stamp'];
			}
		}

		return FALSE;
	}
	function setDateStamp($epoch) {
		$epoch = (int)$epoch;

		if	(	$this->Validator->isDate(		'date_stamp',
												$epoch,
												TTi18n::gettext('Incorrect date'))
			) {

			if	( $epoch > 0 ) {
				//Use middle day epoch to help avoid confusion with different timezones/DST.
				//See comments about timezones in CalculatePolicy->_calculate().
				$this->data['date_stamp'] = TTDate::getMiddleDayEpoch( $epoch );

				$this->setPayPeriod(); //Force pay period to be set as soon as the date is.
				return TRUE;
			} else {
				$this->Validator->isTRUE(		'date_stamp',
												FALSE,
												TTi18n::gettext('Incorrect date'));
			}
		}

		return FALSE;
	}

	function getPunchControlID() {
		if ( isset($this->data['punch_control_id']) ) {
			return (int)$this->data['punch_control_id'];
		}

		return FALSE;
	}
	function setPunchControlID($id) {
		$id = trim($id);

		$pclf = TTnew( 'PunchControlListFactory' );

		if ( $id == FALSE OR $id == 0 OR $id == '') {
			$id = 0;
		}

		if (  $id == 0
				OR
				$this->Validator->isResultSetWithRows(	'punch_control_id',
														$pclf->getByID($id),
														TTi18n::gettext('Invalid Punch Control ID')
														) ) {
			$this->data['punch_control_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	//Legacy functions for now:
	function getStatus() {
		if ( in_array( $this->getObjectType(), array(5,20,25,30,40,100,110) ) ) {
			return 10;
		} elseif ( $this->getObjectType() == 20 ) {
			return 20;
		} elseif ( $this->getObjectType() == 50 ) {
			return 30;
		}
	}
	function getType() {
		if ( in_array( $this->getObjectType(), array(5,10,50) ) ) {
			return 10;
		} else {
			return $this->getObjectType();
		}
	}

	function getObjectType() {
		if ( isset($this->data['object_type_id']) ) {
			return (int)$this->data['object_type_id'];
		}

		return FALSE;
	}
	function setObjectType($value) {
		$value = trim($value);

		if ( $this->Validator->inArrayKey(	'object_type',
											$value,
											TTi18n::gettext('Incorrect Object Type'),
											$this->getOptions('object_type')) ) {

			$this->data['object_type_id'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getSourceObjectListFactory( $object_type_id ) {
		//Debug::Text('Object Type: '. $object_type_id, __FILE__, __LINE__, __METHOD__, 10);
		switch ( $object_type_id ) {
			case 20:
				$lf = TTNew('RegularTimePolicyListFactory');
				break;
			case 30:
				$lf = TTNew('OverTimePolicyListFactory');
				break;
			case 40:
				$lf = TTNew('PremiumPolicyListFactory');
				break;
			case 25:
			case 50:
				$lf = TTNew('AbsencePolicyListFactory');
				break;
			case 100:
			case 101:
				$lf = TTNew('MealPolicyListFactory');
				break;
			case 110:
			case 111:
				$lf = TTNew('BreakPolicyListFactory');
				break;
			default:
				$lf = FALSE;
				Debug::Text('Invalid Object Type: '. $object_type_id, __FILE__, __LINE__, __METHOD__, 10);
				break;
		}

		return $lf;
	}
	function getSourceObjectObject() {
		$lf = $this->getSourceObjectListFactory( $this->getObjectType() );
		if ( is_object( $lf ) ) {
			$lf->getByID( $this->getSourceObject() );
			if ( $lf->getRecordCount() == 1 ) {
				return $lf->getCurrent();
			}
		}

		return FALSE;
	}

	function getSourceObject() {
		if ( isset($this->data['src_object_id']) ) {
			return (int)$this->data['src_object_id'];
		}

		return FALSE;
	}
	function setSourceObject($id) {
		if ( $id == FALSE OR $id == 0 OR $id == '') {
			$id = 0;
		}

		//Debug::Text('Object Type: '. $this->getObjectType() .' ID: '. $id, __FILE__, __LINE__, __METHOD__, 10);
		$lf = $this->getSourceObjectListFactory( $this->getObjectType() );
		
		if ( $id == 0
				OR
				$this->Validator->isResultSetWithRows(	'src_object_id',
														$lf->getByID($id),
														TTi18n::gettext('Invalid Source Object')
											) ) {

			$this->data['src_object_id'] = $id;

			//Absences need to have pay codes set for the user created entry, then other policies can also be calculated on them too.
			//This is so they can be linked directly with accrual policies rather than having to go through regular time policies first.
			//But in cases where OT is calculated on absence time it may need to not have any pay code and just go through regular/OT policies instead.
			//Do this here rather than in preSave() like it used to be since that could cause the validation checks to fail and the user wouldnt see the message.
			//However we have to setSourceObject *after* setPayCode(), otherwise there is potential for the wrong pay code to be used.
			if ( $this->getSourceObject() != 0 ) {
				if ( $this->getObjectType() == 50 ) {
					$lf = TTNew('AbsencePolicyListFactory');
				} else {
					$lf = NULL;
				}

				if ( is_object( $lf ) ) {
					$lf->getByID( $this->getSourceObject() );
					if ( $lf->getRecordCount() > 0 ) {
						$obj = $lf->getCurrent();
						Debug::text('Setting PayCode To: '. $obj->getPayCode(), __FILE__, __LINE__, __METHOD__, 10);
						$this->setPayCode( $obj->getPayCode() );
					}
				}
			}

			return TRUE;
		}

		return FALSE;
	}

	function getPayCode() {
		if ( isset($this->data['pay_code_id']) ) {
			return (int)$this->data['pay_code_id'];
		}

		return FALSE;
	}
	function setPayCode($id) {
		if ( $id == FALSE OR $id == 0 OR $id == '') {
			$id = 0;
		}

		$lf = TTNew('PayCodeListFactory');

		if ( $id == 0
				OR
				$this->Validator->isResultSetWithRows(	'pay_code_id',
														$lf->getByID($id),
														TTi18n::gettext('Invalid Pay Code')
											) ) {

			$this->data['pay_code_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}
	
	//Returns an array of time categories that the object_type fits in.
	function getTimeCategory( $include_total = TRUE, $report_columns = FALSE ) {
		
		$retarr = array();
		switch ( $this->getObjectType() ) {
			case 5: //System Time
				if ( $include_total == TRUE ) {
					$retarr[] = 'total';
				}
				break;
			case 10: //Worked
				$retarr[] = 'worked';
				break;
			case 20: //Regular
				$retarr[] = 'regular';
				break;
			case 25: //Absence
				$retarr[] = 'absence';
				break;
			case 30: //Overtime
				$retarr[] = 'overtime';
				break;
			case 40: //Premium
				$retarr[] = 'premium';
				break;
			case 50: //Absence (Taken)
				$retarr[] = 'absence_taken';
				break;
			case 100: //Lunch
				$retarr[] = 'worked';
				break;
			case 101: //Lunch (Taken)
				//During the transition from v7 -> v8, Lunch/Break time wasnt being assigned to branch/departments in v7, so it caused
				//blank lines to appear on reports. This prevents Lunch/Break time taken from causing blank lines or even being included
				//unless the report displays these columns.
				if ( $report_columns == FALSE OR isset($report_columns['lunch_time']) ) {
					$retarr[] = 'lunch';
				}
				break;
			case 110: //Break
				$retarr[] = 'worked';
				break;
			case 111: //Break (Taken)
				//During the transition from v7 -> v8, Lunch/Break time wasnt being assigned to branch/departments in v7, so it caused
				//blank lines to appear on reports. This prevents Lunch/Break time taken from causing blank lines or even being included
				//unless the report displays these columns.
				if ( $report_columns == FALSE OR isset($report_columns['break_time']) ) {
					$retarr[] = 'break';
				}
				break;
		}

		//Don't include Absence Time Taken (ID: 50) with other 'pay_code-' categories, as that will double up on the absence time often. (ID:25 + ID:50).
		//Include Lunch(100)/Break(110) so they can be displayed as their own separate column on reports.
		if ( in_array($this->getObjectType(), array(20,25,30,40,100,110) ) ) {
			$retarr[] = 'pay_code-'. $this->getColumn('pay_code_id');
		} elseif ( $this->getObjectType() == 50 ) { //Break out absence time taken so we can have separate columns for it in reports. Prevents doubling up as described above.
			$retarr[] = 'absence_taken_pay_code-'. $this->getColumn('pay_code_id');
		}

		//Make sure we don't include Absence (Taken) [50] in gross time, use Absence [25] instead so we don't double up on absence time.
		if ( $this->getObjectType() != 50 AND $this->getColumn('pay_code_type_id') != '' AND in_array( $this->getColumn('pay_code_type_id'), array(10,12,30) )  ) {
			$retarr[] = 'gross'; //Use 'gross' instead of 'paid' so we don't have to special case it in each report.
		}

		return $retarr;
	}

	function getBranch() {
		if ( isset($this->data['branch_id']) ) {
			return (int)$this->data['branch_id'];
		}

		return FALSE;
	}
	function setBranch($id) {
		$id = trim($id);

		if ( $id == FALSE OR $id == 0 OR $id == '' ) {
			$id = 0;
		}

		$blf = TTnew( 'BranchListFactory' );

		if (  $id == 0
				OR
				$this->Validator->isResultSetWithRows(	'branch_id',
														$blf->getByID($id),
														TTi18n::gettext('Branch does not exist')
														) ) {
			$this->data['branch_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getDepartment() {
		if ( isset($this->data['department_id']) ) {
			return (int)$this->data['department_id'];
		}

		return FALSE;
	}
	function setDepartment($id) {
		$id = trim($id);

		if ( $id == FALSE OR $id == 0 OR $id == '' ) {
			$id = 0;
		}

		$dlf = TTnew( 'DepartmentListFactory' );

		if (  $id == 0
				OR
				$this->Validator->isResultSetWithRows(	'department_id',
														$dlf->getByID($id),
														TTi18n::gettext('Department does not exist')
														) ) {
			$this->data['department_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getJob() {
		if ( isset($this->data['job_id']) ) {
			return (int)$this->data['job_id'];
		}

		return FALSE;
	}
	function setJob($id) {
		$id = trim($id);

		if ( $id == FALSE OR $id == 0 OR $id == '' ) {
			$id = 0;
		}

		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			$jlf = TTnew( 'JobListFactory' );
		}

		if (  $id == 0
				OR
				$this->Validator->isResultSetWithRows(	'job_id',
														$jlf->getByID($id),
														TTi18n::gettext('Job does not exist')
														) ) {
			$this->data['job_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getJobItem() {
		if ( isset($this->data['job_item_id']) ) {
			return (int)$this->data['job_item_id'];
		}

		return FALSE;
	}
	function setJobItem($id) {
		$id = trim($id);

		if ( $id == FALSE OR $id == 0 OR $id == '' ) {
			$id = 0;
		}

		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			$jilf = TTnew( 'JobItemListFactory' );
		}

		if (  $id == 0
				OR
				$this->Validator->isResultSetWithRows(	'job_item_id',
														$jilf->getByID($id),
														TTi18n::gettext('Job Item does not exist')
														) ) {
			$this->data['job_item_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getQuantity() {
		if ( isset($this->data['quantity']) ) {
			return (float)$this->data['quantity'];
		}

		return FALSE;
	}
	function setQuantity($val) {
		$val = (float)$val;

		if ( $val == FALSE OR $val == 0 OR $val == '' ) {
			$val = 0;
		}

		if	(	$val == 0
				OR
				$this->Validator->isFloat(			'quantity',
													$val,
													TTi18n::gettext('Incorrect quantity')) ) {
			$this->data['quantity'] = $val;

			return TRUE;
		}

		return FALSE;
	}

	function getBadQuantity() {
		if ( isset($this->data['bad_quantity']) ) {
			return (float)$this->data['bad_quantity'];
		}

		return FALSE;
	}
	function setBadQuantity($val) {
		$val = (float)$val;

		if ( $val == FALSE OR $val == 0 OR $val == '' ) {
			$val = 0;
		}


		if	(	$val == 0
				OR
				$this->Validator->isFloat(			'bad_quantity',
													$val,
													TTi18n::gettext('Incorrect bad quantity')) ) {
			$this->data['bad_quantity'] = $val;

			return TRUE;
		}

		return FALSE;
	}

	function getStartType() {
		if ( isset($this->data['start_type_id']) ) {
			return (int)$this->data['start_type_id'];
		}

		return FALSE;
	}
	function setStartType($value) {
		$value = (int)$value;

		if ( $value === 0 ) {
			$value = '';
		}

		if ( $value == ''
				OR
				$this->Validator->inArrayKey(	'start_type',
											$value,
											TTi18n::gettext('Incorrect Start Type'),
											$this->getOptions('start_type')) ) {

			$this->data['start_type_id'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getStartTimeStamp( $raw = FALSE ) {
		if ( isset($this->data['start_time_stamp']) ) {
			if ( $raw === TRUE ) {
				return $this->data['start_time_stamp'];
			} else {
				//return $this->db->UnixTimeStamp( $this->data['start_date'] );
				//strtotime is MUCH faster than UnixTimeStamp
				//Must use ADODB for times pre-1970 though.
				if ( !is_numeric( $this->data['start_time_stamp'] ) ) { //Optmization to avoid converting it when run in CalculatePolicy's loops
					$this->data['start_time_stamp'] = TTDate::strtotime( $this->data['start_time_stamp'] );
				}
				return $this->data['start_time_stamp'];
			}
		}

		return FALSE;
	}
	function setStartTimeStamp($epoch) {
		$epoch = trim($epoch);

		if	(	$epoch == ''
				OR
				$this->Validator->isDate(		'start_time_stamp',
												$epoch,
												TTi18n::gettext('Incorrect start time stamp'))
			) {

			$this->data['start_time_stamp'] = $epoch;

			return TRUE;
		}

		return FALSE;
	}

	function getEndType() {
		if ( isset($this->data['end_type_id']) ) {
			return (int)$this->data['end_type_id'];
		}

		return FALSE;
	}
	function setEndType($value) {
		$value = (int)$value;

		if ( $value === 0 ) {
			$value = '';
		}

		if ( $value == ''
				OR
				$this->Validator->inArrayKey(	'end_type',
											$value,
											TTi18n::gettext('Incorrect End Type'),
											$this->getOptions('end_type')) ) {

			$this->data['end_type_id'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getEndTimeStamp( $raw = FALSE ) {
		if ( isset($this->data['end_time_stamp']) ) {
			if ( $raw === TRUE ) {
				return $this->data['end_time_stamp'];
			} else {
				if ( !is_numeric( $this->data['end_time_stamp'] ) ) { //Optmization to avoid converting it when run in CalculatePolicy's loops
					$this->data['end_time_stamp'] = TTDate::strtotime( $this->data['end_time_stamp'] );
				}
				return $this->data['end_time_stamp'];				
			}
		}

		return FALSE;
	}
	function setEndTimeStamp($epoch) {
		$epoch = trim($epoch);

		if	(	$epoch == ''
				OR
				$this->Validator->isDate(		'end_time_stamp',
												$epoch,
												TTi18n::gettext('Incorrect end time stamp'))

			) {

			$this->data['end_time_stamp'] = $epoch;

			return TRUE;
		}

		return FALSE;
	}

	function getTotalTime() {
		if ( isset($this->data['total_time']) ) {
			return (int)$this->data['total_time'];
		}
		return FALSE;
	}
	function setTotalTime($int) {
		$int = (int)$int;

		if	(	$this->Validator->isNumeric(		'total_time',
													$int,
													TTi18n::gettext('Incorrect total time')) ) {
			$this->data['total_time'] = $int;

			return TRUE;
		}

		return FALSE;
	}
	function calcTotalTime() {
		if ( $this->getEndTimeStamp() != '' AND $this->getStartTimeStamp() != '' ) {
			$retval = ( $this->getEndTimeStamp() - $this->getStartTimeStamp() );
			return $retval;
		}

		return FALSE;
	}

	function getActualTotalTime() {
		if ( isset($this->data['actual_total_time']) ) {
			return (int)$this->data['actual_total_time'];
		}
		return FALSE;
	}
	function setActualTotalTime($int) {
		$int = (int)$int;

		if	(	$this->Validator->isNumeric(		'actual_total_time',
													$int,
													TTi18n::gettext('Incorrect actual total time')) ) {
			$this->data['actual_total_time'] = $int;

			return TRUE;
		}

		return FALSE;
	}

	function getCurrency() {
		if ( isset($this->data['currency_id']) ) {
			return (int)$this->data['currency_id'];
		}

		return FALSE;
	}
	function setCurrency($id, $disable_rate_lookup = FALSE ) {
		$id = trim($id);

		//Debug::Text('Currency ID: '. $id, __FILE__, __LINE__, __METHOD__, 10);
		$culf = TTnew( 'CurrencyListFactory' );

		$old_currency_id = $this->getCurrency();

		if (
				$this->Validator->isResultSetWithRows(	'currency',
														$culf->getByID($id),
														TTi18n::gettext('Invalid Currency')
													) ) {

			$this->data['currency_id'] = $id;

			if ( $disable_rate_lookup == FALSE
					AND $culf->getRecordCount() == 1
					AND ( $this->isNew() OR $old_currency_id != $id ) ) {
				$crlf = TTnew( 'CurrencyRateListFactory' );
				$crlf->getByCurrencyIdAndDateStamp( $id, $this->getDateStamp() );
				if ( $crlf->getRecordCount() > 0 ) {
					$this->setCurrencyRate( $crlf->getCurrent()->getReverseConversionRate() );
				}
			}

			return TRUE;
		}

		return FALSE;
	}

	function getCurrencyRate() {
		if ( isset($this->data['currency_rate']) ) {
			return $this->data['currency_rate'];
		}

		return FALSE;
	}
	function setCurrencyRate( $value ) {
		$value = trim($value);

		//Pull out only digits and periods.
		$value = $this->Validator->stripNonFloat($value);

		if ( $value == 0 ) {
			$value = 1;
		}

		if (	$this->Validator->isFloat(	'currency_rate',
											$value,
											TTi18n::gettext('Incorrect Currency Rate')) ) {

			$this->data['currency_rate'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	//This the base hourly rate used to obtain the final hourly rate from. Primarily used for FLSA calculations when adding overtime wages.
	function getBaseHourlyRate() {
		if ( isset($this->data['base_hourly_rate']) ) {
			return $this->data['base_hourly_rate'];
		}

		return FALSE;
	}
	function setBaseHourlyRate( $value ) {
		$value = trim($value);

		if ( $value === FALSE OR $value === '' OR $value === NULL ) {
			$value = 0;
		}

		//Pull out only digits and periods.
		$value = $this->Validator->stripNonFloat($value);

		if (	$this->Validator->isFloat(	'base_hourly_rate',
											$value,
											TTi18n::gettext('Incorrect Base Hourly Rate')) ) {

			$this->data['base_hourly_rate'] = number_format( $value, 4, '.', '' ); //Always make sure there are 4 decimal places.

			return TRUE;
		}

		return FALSE;
	}

	function getHourlyRate() {
		if ( isset($this->data['hourly_rate']) ) {
			return $this->data['hourly_rate'];
		}

		return FALSE;
	}
	function setHourlyRate( $value ) {
		$value = trim($value);

		if ( $value === FALSE OR $value === '' OR $value === NULL ) {
			$value = 0;
		}

		//Pull out only digits and periods.
		$value = $this->Validator->stripNonFloat($value);

		if (	$this->Validator->isFloat(	'hourly_rate',
											$value,
											TTi18n::gettext('Incorrect Hourly Rate')) ) {

			$this->data['hourly_rate'] = number_format( $value, 4, '.', '' ); //Always make sure there are 4 decimal places.

			return TRUE;
		}

		return FALSE;
	}

	function getTotalTimeAmount() {
		if ( isset($this->data['total_time_amount']) ) {
			return $this->data['total_time_amount'];
		}

		return FALSE;
	}
	function setTotalTimeAmount( $value ) {
		$value = trim($value);

		if ( $value === FALSE OR $value === '' OR $value === NULL ) {
			$value = 0;
		}

		//Pull out only digits and periods.
		$value = $this->Validator->stripNonFloat($value);

		if (	$this->Validator->isFloat(	'total_time_amount',
											$value,
											TTi18n::gettext('Incorrect Total Time Amount')) ) {

			$this->data['total_time_amount'] = $value;

			return TRUE;
		}

		return FALSE;
	}
	function calcTotalTimeAmount() {
		$retval = ( TTDate::getHours( $this->getTotalTime() ) * $this->getHourlyRate() );
		return $retval;
	}

	function getHourlyRateWithBurden() {
		if ( isset($this->data['hourly_rate_with_burden']) ) {
			return $this->data['hourly_rate_with_burden'];
		}

		return FALSE;
	}
	function setHourlyRateWithBurden( $value ) {
		$value = trim($value);

		if ( $value === FALSE OR $value === '' OR $value === NULL ) {
			$value = 0;
		}

		//Pull out only digits and periods.
		$value = $this->Validator->stripNonFloat($value);

		if (	$this->Validator->isFloat(	'hourly_rate_with_burden',
											$value,
											TTi18n::gettext('Incorrect Hourly Rate with Burden')) ) {

			$this->data['hourly_rate_with_burden'] = number_format( $value, 4, '.', '' ); //Always make sure there are 4 decimal places.

			return TRUE;
		}

		return FALSE;
	}

	function getTotalTimeAmountWithBurden() {
		if ( isset($this->data['total_time_amount_with_burden']) ) {
			return $this->data['total_time_amount_with_burden'];
		}

		return FALSE;
	}
	function setTotalTimeAmountWithBurden( $value ) {
		$value = trim($value);

		if ( $value === FALSE OR $value === '' OR $value === NULL ) {
			$value = 0;
		}

		//Pull out only digits and periods.
		$value = $this->Validator->stripNonFloat($value);

		if (	$this->Validator->isFloat(	'total_time_amount_with_burden',
											$value,
											TTi18n::gettext('Incorrect Total Time Amount with Burden')) ) {

			$this->data['total_time_amount_with_burden'] = $value;

			return TRUE;
		}

		return FALSE;
	}
	function calcTotalTimeAmountWithBurden() {
		$retval = ( TTDate::getHours( $this->getTotalTime() ) * $this->getHourlyRateWithBurden() );
		return $retval;
	}

	function getOverride() {
		if ( isset($this->data['override']) ) {
			return $this->fromBool( $this->data['override'] );
		}
		return FALSE;
	}
	function setOverride($bool) {
		$this->data['override'] = $this->toBool($bool);

		return TRUE;
	}

	function getNote() {
		if ( isset($this->data['note']) ) {
			return $this->data['note'];
		}

		return FALSE;
	}
	function setNote($val) {
		$val = trim($val);

		if	(	$val == ''
				OR
				$this->Validator->isLength(		'note',
												$val,
												TTi18n::gettext('Note is too long'),
												0,
												1024) ) {

			$this->data['note'] = $val;

			return TRUE;
		}

		return FALSE;
	}
	
	function getName() {
		switch ( $this->getObjectType() ) {
			case 5:
				$name = TTi18n::gettext('Total Time');
				break;
			case 10: //Worked Time
				$name = TTi18n::gettext('Worked Time');
				break;
			case 20: //Regular Time
			case 25:
			case 30:
			case 40:
			case 100:
			case 110:
				if ( is_object( $this->getPayCodeObject() ) ) {
					$name = $this->getPayCodeObject()->getName();
				} elseif ( $this->getObjectType() == 20 ) { //Regular Time
					$name = TTi18n::gettext('ERROR: UnAssigned Regular Time'); //No regular time policies to catch all worked time.
				} else {
					$name = TTi18n::gettext('ERROR: INVALID PAY CODE');
				}
				break;
			case 101: //Lunch (Taken)
				$name = TTi18n::gettext('Lunch (Taken)');
				break;
			case 111: //Break (Taken)
				$name = TTi18n::gettext('Break (Taken)');
				break;
			case 50:
				//Absence taken time use the policy name, *not* pay code name.
				$lf = TTNew('AbsencePolicyListFactory');
				$lf->getByID( $this->getSourceObject() );
				if ( $lf->getRecordCount() == 1 ) {
					$name = $lf->getCurrent()->getName();
				} else {
					$name = TTi18n::gettext('ERROR: Invalid Absence Policy'); //No regular time policies to catch all worked time.
				}
				break;
			default:
				$name = TTi18n::gettext('N/A');
				break;
		}

		if ( isset($name) ) {
			return $name;
		}

		return FALSE;
	}

	function getIsPartialShift() {
		if ( isset($this->is_partial_shift) ) {
			return $this->is_partial_shift;
		}

		return FALSE;
	}
	function setIsPartialShift($bool) {
		$this->is_partial_shift = $bool;

		return TRUE;
	}

	function getEnableCalcSystemTotalTime() {
		if ( isset($this->calc_system_total_time) ) {
			return $this->calc_system_total_time;
		}

		return FALSE;
	}
	function setEnableCalcSystemTotalTime($bool) {
		$this->calc_system_total_time = $bool;

		return TRUE;
	}

	function getEnableCalcWeeklySystemTotalTime() {
		if ( isset($this->calc_weekly_system_total_time) ) {
			return $this->calc_weekly_system_total_time;
		}

		return FALSE;
	}
	function setEnableCalcWeeklySystemTotalTime($bool) {
		$this->calc_weekly_system_total_time = $bool;

		return TRUE;
	}

	function getEnableCalcException() {
		if ( isset($this->calc_exception) ) {
			return $this->calc_exception;
		}

		return FALSE;
	}
	function setEnableCalcException($bool) {
		$this->calc_exception = $bool;

		return TRUE;
	}

	function getEnablePreMatureException() {
		if ( isset($this->premature_exception) ) {
			return $this->premature_exception;
		}

		return FALSE;
	}
	function setEnablePreMatureException($bool) {
		$this->premature_exception = $bool;

		return TRUE;
	}

	function getEnableCalcAccrualPolicy() {
		if ( isset($this->calc_accrual_policy) ) {
			return $this->calc_accrual_policy;
		}

		return FALSE;
	}
	function setEnableCalcAccrualPolicy($bool) {
		$this->calc_accrual_policy = $bool;

		return TRUE;
	}

	static function getEnableCalcFutureWeek() {
		if ( isset(self::$calc_future_week) ) {
			return self::$calc_future_week;
		}

		return FALSE;
	}
	static function setEnableCalcFutureWeek($bool) {
		self::$calc_future_week = $bool;

		return TRUE;
	}

	function getEnableTimeSheetVerificationCheck() {
		if ( isset($this->timesheet_verification_check) ) {
			return $this->timesheet_verification_check;
		}

		return FALSE;
	}
	function setEnableTimeSheetVerificationCheck($bool) {
		$this->timesheet_verification_check = $bool;

		return TRUE;
	}

	function calcSystemTotalTime() {
		global $profiler;

		$profiler->startTimer( 'UserDateTotal::calcSystemTotalTime() - Part 1');

		if ( $this->getUser() == FALSE OR $this->getDateStamp() == FALSE ) {
			Debug::text(' User/DateStamp not found!', __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		if ( is_object( $this->getPayPeriodObject() )
				AND $this->getPayPeriodObject()->getStatus() == 20 ) {
			Debug::text(' Pay Period is closed!', __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}


		//$this->deleteSystemTotalTime(); //Handled in calculatePolicy now.

		$cp = TTNew('CalculatePolicy');
		$cp->setFlag( 'exception', $this->getEnableCalcException() );
		$cp->setFlag( 'exception_premature', $this->getEnablePreMatureException() );
		$cp->setUserObject( $this->getUserObject() );
		$cp->addPendingCalculationDate( array_merge( (array)$this->getDateStamp(), (array)$this->alternate_date_stamps ) );
		$cp->calculate(); //This sets timezone itself.
		$cp->Save();

		if ( isset($original_time_zone) ) {
			TTDate::setTimeZone( $original_time_zone );
		}

		return TRUE;
	}

	function calcWeeklySystemTotalTime() {
		if ( $this->getEnableCalcWeeklySystemTotalTime() == TRUE ) {
			//Used to call reCalculateRange() for the remainder of the week, but this is handled automatically now.
			return TRUE;
		}

		return FALSE;
	}

	static function reCalculateDay( $user_obj, $date_stamps, $enable_exception = FALSE, $enable_premature_exceptions = FALSE, $enable_future_exceptions = TRUE, $enable_holidays = FALSE ) {
		if ( !is_object( $user_obj ) ) {
			return FALSE;
		}
		
		Debug::text('Re-calculating User ID: '. $user_obj->getId() .' Enable Exception: '. (int)$enable_exception, __FILE__, __LINE__, __METHOD__, 10);

		if ( !is_array($date_stamps) ) {
			$date_stamps = array( $date_stamps );
		}
		Debug::Arr($date_stamps, 'bDate Stamps: ', __FILE__, __LINE__, __METHOD__, 10);

		$cp = TTNew('CalculatePolicy');

		$cp->setFlag( 'exception', $enable_exception );
		$cp->setFlag( 'exception_premature', $enable_premature_exceptions );
		$cp->setFlag( 'exception_future', $enable_future_exceptions );
		
		$cp->setUserObject( $user_obj );
		$cp->addPendingCalculationDate( $date_stamps );
		$cp->calculate(); //This sets timezone itself.
		return $cp->Save();
	}

	function Validate() {

		if ( $this->getUser() == FALSE ) {
			$this->Validator->isTRUE(	'user_id',
										FALSE,
										TTi18n::gettext('Employee is invalid') );
		}

		if ( $this->getObjectType() == FALSE ) {
			$this->Validator->isTRUE(	'object_type_id',
										FALSE,
										TTi18n::gettext('Type is invalid') );
		}

		//Check to make sure if this is an absence row, the absence policy is actually set.
		if ( $this->getDeleted() == FALSE AND $this->getObjectType() == 50 ) {
			if ( (int)$this->getSourceObject() == 0 ) {
				$this->Validator->isTRUE(	'absence_policy_id',
											FALSE,
											TTi18n::gettext('Please specify an absence type'));
			}

			if ( is_object( $this->getUserObject() ) AND $this->getUserObject()->getHireDate() != '' AND TTDate::getBeginDayEpoch( $this->getDateStamp() ) < TTDate::getBeginDayEpoch( $this->getUserObject()->getHireDate() ) ) {
				$this->Validator->isTRUE(	'date_stamp',
											FALSE,
											TTi18n::gettext('Absence is before employees hire date') );
			}

			if ( is_object( $this->getUserObject() ) AND $this->getUserObject()->getTerminationDate() != '' AND TTDate::getEndDayEpoch( $this->getDateStamp() ) > TTDate::getEndDayEpoch( $this->getUserObject()->getTerminationDate() ) ) {
				$this->Validator->isTRUE(	'date_stamp',
											FALSE,
											TTi18n::gettext('Absence is after employees termination date') );
			}
		}

		//Check to make sure if this is an absence row, the absence policy is actually set.
		//if ( $this->getObjectType() == 50 AND $this->getPayCode() == FALSE ) {
		if ( $this->getObjectType() == 50 AND (int)$this->getSourceObject() == 0 AND $this->getOverride() == FALSE ) {
				$this->Validator->isTRUE(	'absence_policy_id',
											FALSE,
											TTi18n::gettext('Please specify an absence type'));
		}
		//Check to make sure if this is an overtime row, the overtime policy is actually set.
		if ( $this->getObjectType() == 30 AND (int)$this->getSourceObject() == 0 AND $this->getOverride() == FALSE ) {
				$this->Validator->isTRUE(	'over_time_policy_id',
											FALSE,
											TTi18n::gettext('Invalid Overtime Policy'));
		}
		//Check to make sure if this is an premium row, the premium policy is actually set.
		if ( $this->getObjectType() == 40 AND (int)$this->getSourceObject() == 0 AND $this->getOverride() == FALSE ) {
				$this->Validator->isTRUE(	'premium_policy_id',
											FALSE,
											TTi18n::gettext('Invalid Premium Policy'));
		}
		//Check to make sure if this is an meal row, the meal policy is actually set.
		if ( $this->getObjectType() == 100 AND (int)$this->getSourceObject() == 0 AND $this->getOverride() == FALSE ) {
				$this->Validator->isTRUE(	'meal_policy_id',
											FALSE,
											TTi18n::gettext('Invalid Meal Policy'));
		}
		//Check to make sure if this is an break row, the break policy is actually set.
		if ( $this->getObjectType() == 110 AND (int)$this->getSourceObject() == 0 AND $this->getOverride() == FALSE ) {
				$this->Validator->isTRUE(	'break_policy_id',
											FALSE,
											TTi18n::gettext('Invalid Break Policy'));
		}

		//Check that the user is allowed to be assigned to the absence policy
		if ( $this->getObjectType() == 50 AND (int)$this->getSourceObject() != 0 AND $this->getUser() != FALSE ) {
			$pglf = TTNew('PolicyGroupListFactory');
			$pglf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), array('user_id' => array( $this->getUser() ), 'absence_policy' => array( $this->getSourceObject() ) ) );
			if ( $pglf->getRecordCount() == 0 ) {
				$this->Validator->isTRUE(	'absence_policy_id',
								FALSE,
								TTi18n::gettext('This absence policy is not available for this employee'));
			}
		}

		//This is likely caused by employee not being assigned to a pay period schedule?
		//Make sure to allow entries in the future (ie: absences) where no pay period exists yet.
		if ( $this->getDeleted() == FALSE AND $this->getDateStamp() == FALSE ) {
			$this->Validator->isTRUE(	'date_stamp',
										FALSE,
										TTi18n::gettext('Date is incorrect, or pay period does not exist for this date. Please create a pay period schedule and assign this employee to it if you have not done so already') );
		} elseif ( ( $this->getOverride() == TRUE OR ( $this->getOverride() == FALSE AND $this->getObjectType() == 50 ) )
					AND $this->getDateStamp() != FALSE AND is_object( $this->getPayPeriodObject() ) AND $this->getPayPeriodObject()->getIsLocked() == TRUE ) {
			//Make sure we only check for pay period being locked if override is TRUE, otherwise it can prevent recalculations from occurring
			//after the pay period is locked (ie: recalculating exceptions each day from maintenance jobs?)
			//We need to be able to stop absences (non-overridden ones too) from being deleted in closed pay periods.
			$this->Validator->isTRUE(	'date_stamp',
										FALSE,
										TTi18n::gettext('Pay Period is Currently Locked') );
		}

		//Make sure that we aren't trying to overwrite an already overridden entry made by the user for some special purpose.
		if ( $this->getDeleted() == FALSE
				AND $this->isNew() == TRUE
				//AND in_array( $this->getStatus(), array(10, 20, 30) )
				) {

			//Debug::text('Checking for already existing overridden entries ... User ID: '. $this->getUser() .' DateStamp: '. $this->getDateStamp() .' Object Type ID: '. $this->getObjectType(), __FILE__, __LINE__, __METHOD__, 10);

			$udtlf = TTnew( 'UserDateTotalListFactory' );
			if ( $this->getObjectType() == 10 AND $this->getPunchControlID() > 0 ) {
				$udtlf->getByUserIdAndDateStampAndObjectTypeAndPunchControlIdAndOverride( $this->getUser(), $this->getDateStamp(), $this->getObjectType(), $this->getPunchControlID(), TRUE );
			} elseif ( $this->getObjectType() == 50 OR $this->getObjectType() == 25 ) {
				//Allow object_type_id=50 (absence taken) entries to override object_type_id=25 entries.
				//So users can create an absence schedule shift, then override it to a smaller number of hours.
				//However how do we handle cases where an undertime absence policy creates a object_type_id=25 record and the user wants to override it?

				//Allow employee to have multiple absence entries on the same day as long as the branch, department, job, task are all different.
				if ( $this->getDateStamp() != FALSE AND $this->getUser() != FALSE ) {
					$filter_data = array( 'user_id' => (int)$this->getUser(), 'date_stamp' => $this->getDateStamp(), 'object_type_id' => array( (int)$this->getObjectType(), 25 ),  'branch_id' => (int)$this->getBranch(), 'department_id' => (int)$this->getDepartment(), 'job_id' => (int)$this->getJob(), 'job_item_id' => (int)$this->getJobItem() );
					$filter_data['object_type_id'] = (int)$this->getObjectType();
					//Restrict based on src_object_id when entering absences as well.
					//This allows multiple absence policies to point to the same pay code
					//and still have multiple entries on the same day with the same branch/department/job/task.
					//Some customers have 5-10 UNPAID absence policies all going to the same UNPAID pay code.
					//This is required to allow more than one to be used on the same day.
					$filter_data['src_object_id'] = (int)$this->getSourceObject();
					$filter_data['pay_code_id'] = (int)$this->getPayCode();
					$udtlf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
				}
			} elseif ( $this->getObjectType() == 30 ) {
				$udtlf->getByUserIdAndDateStampAndObjectTypeAndPayCodeIdAndOverride( $this->getUser(), $this->getDateStamp(), $this->getObjectType(), $this->getPayCode(), TRUE );
			} elseif ( $this->getObjectType() == 40 ) {
				$udtlf->getByUserIdAndDateStampAndObjectTypeAndPayCodeIdAndOverride( $this->getUser(), $this->getDateStamp(), $this->getObjectType(), $this->getPayCode(), TRUE );
			} elseif ( $this->getObjectType() == 100 ) {
				$udtlf->getByUserIdAndDateStampAndObjectTypeAndPayCodeIdAndOverride( $this->getUser(), $this->getDateStamp(), $this->getObjectType(), $this->getPayCode(), TRUE );
			} elseif ( $this->getObjectType() == 5 OR ( $this->getObjectType() == 20 AND $this->getPunchControlID() > 0 ) ) {
				$udtlf->getByUserIdAndDateStampAndObjectTypeAndPunchControlIdAndOverride( $this->getUser(), $this->getDateStamp(), $this->getObjectType(), $this->getPunchControlID(), TRUE );
			}

			//Debug::text('Record Count: '. (int)$udtlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
			if ( $udtlf->getRecordCount() > 0 ) {
				Debug::text('Found an overridden row... NOT SAVING: '. $udtlf->getCurrent()->getId(), __FILE__, __LINE__, __METHOD__, 10);
				$this->Validator->isTRUE(	'absence_policy_id',
											FALSE,
											TTi18n::gettext('Similar entry already exists, not overriding'));
			}
			unset($udtlf);
		}

		return TRUE;
	}

	function preSave() {
		if ( $this->getPayPeriod() == FALSE ) {
			$this->setPayPeriod(); //Not specifying pay period forces it to be looked up.
		}

		if ( $this->getPayCode() === FALSE ) {
			$this->setPayCode(0);
		}

		if ( $this->getPunchControlID() === FALSE ) {
			$this->setPunchControlID(0);
		}

		if ( $this->getBranch() === FALSE ) {
			$this->setBranch(0);
		}

		if ( $this->getDepartment() === FALSE ) {
			$this->setDepartment(0);
		}

		if ( $this->getJob() === FALSE ) {
			$this->setJob(0);
		}

		if ( $this->getJobItem() === FALSE ) {
			$this->setJobItem(0);
		}

		if ( $this->getQuantity() === FALSE ) {
			$this->setQuantity(0);
		}

		if ( $this->getBadQuantity() === FALSE ) {
			$this->setBadQuantity(0);
		}

		$this->setTotalTimeAmount( $this->calcTotalTimeAmount() );
		$this->setTotalTimeAmountWithBurden( $this->calcTotalTimeAmountWithBurden() );

		if ( $this->getEnableTimeSheetVerificationCheck() ) {
			//Check to see if timesheet is verified, if so unverify it on modified punch.
			//Make sure exceptions are calculated *after* this so TimeSheet Not Verified exceptions can be triggered again.
			if ( $this->getDateStamp() != FALSE
					AND is_object( $this->getPayPeriodObject() )
					AND is_object( $this->getPayPeriodObject()->getPayPeriodScheduleObject() )
					AND $this->getPayPeriodObject()->getPayPeriodScheduleObject()->getTimeSheetVerifyType() != 10 ) {
				//Find out if timesheet is verified or not.
				$pptsvlf = TTnew( 'PayPeriodTimeSheetVerifyListFactory' );
				$pptsvlf->getByPayPeriodIdAndUserId( $this->getPayPeriod(), $this->getUser() );
				if ( $pptsvlf->getRecordCount() > 0 ) {
					//Pay period is verified, delete all records and make log entry.
					//These can be added during the maintenance jobs, so the audit records are recorded as user_id=0, check those first.
					Debug::text('Pay Period is verified, deleting verification records: '. $pptsvlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
					foreach( $pptsvlf as $pptsv_obj ) {
						if ( $this->getObjectType() == 50 AND is_object( $this->getSourceObjectObject() ) ) {
							TTLog::addEntry( $pptsv_obj->getId(), 500, TTi18n::getText('TimeSheet Modified After Verification').': '. UserListFactory::getFullNameById( $this->getUser() ) .' '. TTi18n::getText('Absence').': '. $this->getSourceObjectObject()->getName() .' - '. TTDate::getDate('DATE', $this->getDateStamp() ), NULL, $pptsvlf->getTable() );
						}
						$pptsv_obj->setDeleted( TRUE );
						if ( $pptsv_obj->isValid() ) {
							$pptsv_obj->Save();
						}
					}
				}
			}
		}

		return TRUE;
	}

	function postSave() {
		if ( $this->getEnableCalcSystemTotalTime() == TRUE ) {
			Debug::text('Calc System Total Time Enabled: ', __FILE__, __LINE__, __METHOD__, 10);
			$this->calcSystemTotalTime();
		} else {
			Debug::text('Calc System Total Time Disabled: ', __FILE__, __LINE__, __METHOD__, 10);
		}
		
		return TRUE;
	}

	//Takes UserDateTotal rows, and calculate the accumlated time sections
	static function calcAccumulatedTime( $data ) {
		if ( is_array($data) AND count($data) > 0 ) {
			//Keep track of item ids for each section type so we can decide later on if we can eliminate unneeded data.
			$section_ids = array( 'branch' => array(), 'department' => array(), 'job' => array(), 'job_item' => array() );

			//Sort data by date_stamp at the top, so it works for multiple days at a time.
			//Keep a running total of all days, mainly for 'weekly total" purposes.
			//
			//The 'order' array element is used by JS to sort the rows displayed to the user.
			foreach ( $data as $key => $row ) {
				//Skip rows with a 0 total_time.
				if ( $row['total_time'] == 0 ) {
					continue;
				}
				//$combined_type_id_status_id = $row['type_id'].$row['status_id'];

				switch ( $row['object_type_id'] ) {
					//Section: Accumulated Time:
					//	Includes: Total Time, Regular Time, Overtime, Meal Policy Time, Break Policy Time.
					//case 1010: //Type_ID= 10, Status_ID= 10 - Total Time row.
					case 5: //System Total Time row.
						if ( !isset($retval[$row['date_stamp']]['accumulated_time']['total']) ) {
							$retval[$row['date_stamp']]['accumulated_time']['total'] = array('label' => $row['name'], 'total_time' => 0 );
						}
						$retval[$row['date_stamp']]['accumulated_time']['total']['total_time'] += $row['total_time'];

						if ( isset($row['override']) AND $row['override'] == TRUE ) {
							$retval[$row['date_stamp']]['accumulated_time']['total']['override'] = TRUE;
						}
						if ( isset($row['note']) AND $row['note'] == TRUE ) {
							$retval[$row['date_stamp']]['accumulated_time']['total']['note'] = TRUE;
						}

						if ( !isset($retval['total']['accumulated_time']['total']) ) {
							$retval['total']['accumulated_time']['total'] = array('label' => $row['name'], 'total_time' => 0, 'order' => 80 );
						}
						$retval['total']['accumulated_time']['total']['total_time'] += $row['total_time'];
						break;
					case 10: //System Worked Time row.
						if ( !isset($retval[$row['date_stamp']]['accumulated_time']['worked_time']) ) {
							$retval[$row['date_stamp']]['accumulated_time']['worked_time'] = array('label' => $row['name'], 'total_time' => 0 );

						}
						$retval[$row['date_stamp']]['accumulated_time']['worked_time']['total_time'] += $row['total_time'];

						if ( isset($row['override']) AND $row['override'] == TRUE ) {
							$retval[$row['date_stamp']]['accumulated_time']['worked_time']['override'] = TRUE;
						}
						if ( isset($row['note']) AND $row['note'] == TRUE ) {
							$retval[$row['date_stamp']]['accumulated_time']['worked_time']['note'] = TRUE;
						}

						if ( !isset($retval['total']['accumulated_time']['worked_time']) ) {
							$retval['total']['accumulated_time']['worked_time'] = array('label' => $row['name'], 'total_time' => 0, 'order' => 10 );
						}
						$retval['total']['accumulated_time']['worked_time']['total_time'] += $row['total_time'];
						break;
					//case 2010: //Type_ID= 20, Status_ID= 10 - Regular Time row.
					case 20: //Regular Time row.
						if ( !isset($retval[$row['date_stamp']]['accumulated_time']['regular_time_'.$row['pay_code_id']]) ) {
							$retval[$row['date_stamp']]['accumulated_time']['regular_time_'.$row['pay_code_id']] = array('label' => $row['name'], 'total_time' => 0 );

						}
						$retval[$row['date_stamp']]['accumulated_time']['regular_time_'.$row['pay_code_id']]['total_time'] += $row['total_time'];

						if ( isset($row['override']) AND $row['override'] == TRUE ) {
							$retval[$row['date_stamp']]['accumulated_time']['regular_time_'.$row['pay_code_id']]['override'] = TRUE;
						}
						if ( isset($row['note']) AND $row['note'] == TRUE ) {
							$retval[$row['date_stamp']]['accumulated_time']['regular_time_'.$row['pay_code_id']]['note'] = TRUE;
						}

						if ( !isset($retval['total']['accumulated_time']['regular_time_'.$row['pay_code_id']]) ) {
							$retval['total']['accumulated_time']['regular_time_'.$row['pay_code_id']] = array('label' => $row['name'], 'total_time' => 0, 'order' => 50  );
						}
						$retval['total']['accumulated_time']['regular_time_'.$row['pay_code_id']]['total_time'] += $row['total_time'];
						break;
					//Section: Absence Time:
					//	Includes: All Absence Time
					//case 1030: //Type_ID= 10, Status_ID= 30 - Absence Policy Row.
					case 25: //Absence Policy Row.
						//Daily Total
						if ( !isset($retval[$row['date_stamp']]['accumulated_time']['absence_time_'.$row['pay_code_id']]) ) {
							$retval[$row['date_stamp']]['accumulated_time']['absence_time_'.$row['pay_code_id']] = array('label' => $row['name'], 'total_time' => 0 );

						}
						$retval[$row['date_stamp']]['accumulated_time']['absence_time_'.$row['pay_code_id']]['total_time'] += $row['total_time'];

						if ( isset($row['override']) AND $row['override'] == TRUE ) {
							$retval[$row['date_stamp']]['accumulated_time']['absence_time_'.$row['pay_code_id']]['override'] = TRUE;
						}
						if ( isset($row['note']) AND $row['note'] == TRUE ) {
							$retval[$row['date_stamp']]['accumulated_time']['absence_time_'.$row['pay_code_id']]['note'] = TRUE;
						}

						if ( !isset($retval['total']['accumulated_time']['absence_time_'.$row['pay_code_id']]) ) {
							$retval['total']['accumulated_time']['absence_time_'.$row['pay_code_id']] = array('label' => $row['name'], 'total_time' => 0, 'order' => 75  );
						}
						$retval['total']['accumulated_time']['absence_time_'.$row['pay_code_id']]['total_time'] += $row['total_time'];
						break;
					//case 3010: //Type_ID= 30, Status_ID= 10 - Over Time row.
					case 30: //Over Time row.
						if ( !isset($retval[$row['date_stamp']]['accumulated_time']['over_time_'.$row['pay_code_id']]) ) {
							$retval[$row['date_stamp']]['accumulated_time']['over_time_'.$row['pay_code_id']] = array('label' => $row['name'], 'total_time' => 0 );

						}
						$retval[$row['date_stamp']]['accumulated_time']['over_time_'.$row['pay_code_id']]['total_time'] += $row['total_time'];

						if ( isset($row['override']) AND $row['override'] == TRUE ) {
							$retval[$row['date_stamp']]['accumulated_time']['over_time_'.$row['pay_code_id']]['override'] = TRUE;
						}
						if ( isset($row['note']) AND $row['note'] == TRUE ) {
							$retval[$row['date_stamp']]['accumulated_time']['over_time_'.$row['pay_code_id']]['note'] = TRUE;
						}

						if ( !isset($retval['total']['accumulated_time']['over_time_'.$row['pay_code_id']]) ) {
							$retval['total']['accumulated_time']['over_time_'.$row['pay_code_id']] = array('label' => $row['name'], 'total_time' => 0, 'order' => 60  );
						}
						$retval['total']['accumulated_time']['over_time_'.$row['pay_code_id']]['total_time'] += $row['total_time'];
						break;
					//case 10010: //Type_ID= 100, Status_ID= 10 - Meal Policy Row.
					case 100: //Meal Policy Row.
						//Daily Total
						if ( !isset($retval[$row['date_stamp']]['accumulated_time']['meal_time_'.$row['pay_code_id']]) ) {
							$retval[$row['date_stamp']]['accumulated_time']['meal_time_'.$row['pay_code_id']] = array('label' => $row['name'], 'total_time' => 0 );
						}
						$retval[$row['date_stamp']]['accumulated_time']['meal_time_'.$row['pay_code_id']]['total_time'] += $row['total_time'];

						if ( isset($row['override']) AND $row['override'] == TRUE ) {
							$retval[$row['date_stamp']]['accumulated_time']['meal_time_'.$row['pay_code_id']]['override'] = TRUE;
						}
						if ( isset($row['note']) AND $row['note'] == TRUE ) {
							$retval[$row['date_stamp']]['accumulated_time']['meal_time_'.$row['pay_code_id']]['note'] = TRUE;
						}

						if ( !isset($retval['total']['accumulated_time']['meal_time_'.$row['pay_code_id']]) ) {
							$retval['total']['accumulated_time']['meal_time_'.$row['pay_code_id']] = array('label' => $row['name'], 'total_time' => 0, 'order' => 30  );
						}
						$retval['total']['accumulated_time']['meal_time_'.$row['pay_code_id']]['total_time'] += $row['total_time'];
						break;
					//case 11010: //Type_ID= 110, Status_ID= 10 - Break Policy Row.
					case 110: //Break Policy Row.
						//Daily Total
						if ( !isset($retval[$row['date_stamp']]['accumulated_time']['break_time_'.$row['pay_code_id']]) ) {
							$retval[$row['date_stamp']]['accumulated_time']['break_time_'.$row['pay_code_id']] = array('label' => $row['name'], 'total_time' => 0 );
						}
						$retval[$row['date_stamp']]['accumulated_time']['break_time_'.$row['pay_code_id']]['total_time'] += $row['total_time'];

						if ( isset($row['override']) AND $row['override'] == TRUE ) {
							$retval[$row['date_stamp']]['accumulated_time']['break_time_'.$row['pay_code_id']]['override'] = TRUE;
						}
						if ( isset($row['note']) AND $row['note'] == TRUE ) {
							$retval[$row['date_stamp']]['accumulated_time']['break_time_'.$row['pay_code_id']]['note'] = TRUE;
						}

						if ( !isset($retval['total']['accumulated_time']['break_time_'.$row['pay_code_id']]) ) {
							$retval['total']['accumulated_time']['break_time_'.$row['pay_code_id']] = array('label' => $row['name'], 'total_time' => 0, 'order' => 20  );
						}
						$retval['total']['accumulated_time']['break_time_'.$row['pay_code_id']]['total_time'] += $row['total_time'];
						break;

					//Section: Premium Time:
					//	Includes: All Premium Time
					//case 4010: //Type_ID= 40, Status_ID= 10 - Premium Policy Row.
					case 40: //Premium Policy Row.
						//Daily Total
						if ( !isset($retval[$row['date_stamp']]['premium_time']['premium_'.$row['pay_code_id']]) ) {
							$retval[$row['date_stamp']]['premium_time']['premium_'.$row['pay_code_id']] = array('label' => $row['name'], 'total_time' => 0 );
						}
						$retval[$row['date_stamp']]['premium_time']['premium_'.$row['pay_code_id']]['total_time'] += $row['total_time'];

						if ( isset($row['override']) AND $row['override'] == TRUE ) {
							$retval[$row['date_stamp']]['premium_time']['premium_'.$row['pay_code_id']]['override'] = TRUE;
						}
						if ( isset($row['note']) AND $row['note'] == TRUE ) {
							$retval[$row['date_stamp']]['premium_time']['premium_'.$row['pay_code_id']]['note'] = TRUE;
						}

						if ( !isset($retval['total']['premium_time']['premium_'.$row['pay_code_id']]) ) {
							$retval['total']['premium_time']['premium_'.$row['pay_code_id']] = array('label' => $row['name'], 'total_time' => 0, 'order' => 85  );
						}
						$retval['total']['premium_time']['premium_'.$row['pay_code_id']]['total_time'] += $row['total_time'];
						break;
					//Section: Absence Time (Taken):
					//	Includes: All Absence Time
					//case 1030: //Type_ID= 10, Status_ID= 30 - Absence Policy Row.
					case 50: //Absence Time (Taken) Row.
						//Daily Total
						if ( !isset($retval[$row['date_stamp']]['absence_time_taken']['absence_'.$row['pay_code_id']]) ) {
							$retval[$row['date_stamp']]['absence_time_taken']['absence_'.$row['pay_code_id']] = array('label' => $row['name'] .' ('. TTi18n::getText('Taken') .')', 'total_time' => 0 );
						}
						$retval[$row['date_stamp']]['absence_time_taken']['absence_'.$row['pay_code_id']]['total_time'] += $row['total_time'];

						if ( !isset($retval['total']['absence_time_taken']['absence_'.$row['pay_code_id']]) ) {
							$retval['total']['absence_time_taken']['absence_'.$row['pay_code_id']] = array('label' => $row['name'] .' ('. TTi18n::getText('Taken') .')', 'total_time' => 0, 'order' => 90  );
						}
						$retval['total']['absence_time_taken']['absence_'.$row['pay_code_id']]['total_time'] += $row['total_time'];
						break;
				}

				//Section: Accumulated Time by Branch, Department, Job, Task
				//if ( in_array( $row['type_id'], array(20, 30) ) AND in_array( $row['status_id'], array(10) ) ) {
				if ( $row['object_type_id'] == 20 OR $row['object_type_id'] == 30 ) {
					//Branch
					$branch_name = $row['branch'];
					if ( $branch_name == '' ) {
						$branch_name = TTi18n::gettext('No Branch');
					}
					if ( !isset($retval[$row['date_stamp']]['branch_time']['branch_'.$row['branch_id']]) ) {
						$retval[$row['date_stamp']]['branch_time']['branch_'.$row['branch_id']] = array('label' => $branch_name, 'total_time' => 0 );
					}
					$retval[$row['date_stamp']]['branch_time']['branch_'.$row['branch_id']]['total_time'] += $row['total_time'];
					$section_ids['branch'][] = (int)$row['branch_id'];

					//Department
					$department_name = $row['department'];
					if ( $department_name == '' ) {
						$department_name = TTi18n::gettext('No Department');
					}
					if ( !isset($retval[$row['date_stamp']]['department_time']['department_'.$row['department_id']]) ) {
						$retval[$row['date_stamp']]['department_time']['department_'.$row['department_id']] = array('label' => $department_name, 'total_time' => 0 );
					}
					$retval[$row['date_stamp']]['department_time']['department_'.$row['department_id']]['total_time'] += $row['total_time'];
					$section_ids['department'][] = (int)$row['department_id'];

					//Job
					$job_name = $row['job'];
					if ( $job_name == '' ) {
						$job_name = TTi18n::gettext('No Job');
					}
					if ( !isset($retval[$row['date_stamp']]['job_time']['job_'.$row['job_id']]) ) {
						$retval[$row['date_stamp']]['job_time']['job_'.$row['job_id']] = array('label' => $job_name, 'total_time' => 0 );
					}
					$retval[$row['date_stamp']]['job_time']['job_'.$row['job_id']]['total_time'] += $row['total_time'];
					$section_ids['job'][] = (int)$row['job_id'];

					//Job Item/Task
					$job_item_name = $row['job_item'];
					if ( $job_item_name == '' ) {
						$job_item_name = TTi18n::gettext('No Task');
					}
					if ( !isset($retval[$row['date_stamp']]['job_item_time']['job_item_'.$row['job_item_id']]) ) {
						$retval[$row['date_stamp']]['job_item_time']['job_item_'.$row['job_item_id']] = array('label' => $job_item_name, 'total_time' => 0 );
					}
					$retval[$row['date_stamp']]['job_item_time']['job_item_'.$row['job_item_id']]['total_time'] += $row['total_time'];
					$section_ids['job_item'][] = (int)$row['job_item_id'];

					//Debug::text('ID: '. $row['id'] .' User Date ID: '. $row['date_stamp'] .' Total Time: '. $row['total_time'] .' Branch: '. $branch_name .' Job: '. $job_name, __FILE__, __LINE__, __METHOD__, 10);
				}
			}

			if ( isset($retval) ) {
				//Remove any unneeded data, such as "No Branch" for all dates in the range
				foreach( $section_ids as $section => $ids ) {
					$ids = array_unique($ids);
					sort($ids);
					if ( isset($ids[0]) AND $ids[0] == 0 AND count($ids) == 1 ) {
						foreach( $retval as $date_stamp => $day_data ) {
							unset($retval[$date_stamp][$section.'_time']);
						}
					}
				}

				return $retval;
			}
		}

		return FALSE;
	}

	function setObjectFromArray( $data ) {
		if ( is_array( $data ) ) {
			$variable_function_map = $this->getVariableToFunctionMap();
			foreach( $variable_function_map as $key => $function ) {
				if ( isset($data[$key]) ) {

					$function = 'set'.$function;
					switch( $key ) {
						case 'pay_period_id': //Ignore this if its set, as its should be determined in preSave().
							break;
						case 'date_stamp':
							$this->setDateStamp( TTDate::parseDateTime( $data[$key] ) );
							break;
						case 'start_time_stamp':
							$this->setStartTimeStamp( TTDate::parseDateTime( $data[$key] ) );
							break;
						case 'end_time_stamp':
							$this->setEndTimeStamp( TTDate::parseDateTime( $data[$key] ) );
							break;
						default:
							if ( method_exists( $this, $function ) ) {
								$this->$function( $data[$key] );
							}
							break;
					}
				}
			}

			$this->setCreatedAndUpdatedColumns( $data );

			return TRUE;
		}

		return FALSE;
	}

	function getObjectAsArray( $include_columns = NULL, $permission_children_ids = FALSE ) {
		$uf = TTnew( 'UserFactory' );

		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == NULL OR ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) ) {
					$function = 'get'.$function_stub;
					switch( $variable ) {
						case 'first_name':
						case 'last_name':
						case 'group':
						case 'title':
						case 'default_branch':
						case 'default_department':
						case 'branch':
						case 'department':
						case 'over_time_policy':
						case 'absence_policy':
						case 'premium_policy':
						case 'meal_policy':
						case 'break_policy':
						case 'job':
						case 'job_item':
							$data[$variable] = $this->getColumn( $variable );
							break;
						case 'title_id':
						case 'user_id':
						case 'user_status_id':
						case 'group_id':
						case 'pay_period_id':
						case 'default_branch_id':
						case 'default_department_id':
						case 'absence_policy_type_id':
							$data[$variable] = (int)$this->getColumn( $variable );
							break;
						case 'object_type':
							$data[$variable] = Option::getByKey( $this->getObjectType(), $this->getOptions( $variable ) );
							break;
						case 'user_status':
							$data[$variable] = Option::getByKey( (int)$this->getColumn( 'user_status_id' ), $uf->getOptions( 'status' ) );
							break;
						case 'date_stamp':
							$data[$variable] = TTDate::getAPIDate( 'DATE', $this->getDateStamp() );
							break;
						case 'start_time_stamp':
							$data[$variable] = TTDate::getAPIDate( 'DATE+TIME', $this->$function() ); //Include both date+time
							break;
						case 'end_time_stamp':
							$data[$variable] = TTDate::getAPIDate( 'DATE+TIME', $this->$function() ); //Include both date+time
							break;
						case 'name':
							$data[$variable] = $this->getName();
							break;
						default:
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = $this->$function();
							}

							break;
					}
				}
			}
			$this->getPermissionColumns( $data, $this->getColumn( 'user_id' ), $this->getCreatedBy(), $permission_children_ids, $include_columns );
			$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
	}

	function addLog( $log_action ) {
		if ( $this->getOverride() == TRUE AND $this->getDateStamp() != FALSE ) {
			if ( $this->getObjectType() == 50 ) { //Absence
				return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText('Absence') .' - '. TTi18n::getText('Date') .': '. TTDate::getDate('DATE', $this->getDateStamp() ). ' '. TTi18n::getText('Total Time') .': '. TTDate::getTimeUnit( $this->getTotalTime() ), NULL, $this->getTable(), $this );
			} else {
				return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText('Accumulated Time') .' - '. TTi18n::getText('Date') .': '. TTDate::getDate('DATE', $this->getDateStamp() ). ' '. TTi18n::getText('Total Time') .': '. TTDate::getTimeUnit( $this->getTotalTime() ), NULL, $this->getTable(), $this );
			}
		}
	}
}
?>
