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
 * $Revision: 9521 $
 * $Id: RecurringHolidayFactory.class.php 9521 2013-04-08 23:09:52Z ipso $
 * $Date: 2013-04-08 16:09:52 -0700 (Mon, 08 Apr 2013) $
 */

/**
 * @package Modules\Holiday
 */
class RecurringHolidayFactory extends Factory {
	protected $table = 'recurring_holiday';
	protected $pk_sequence_name = 'recurring_holiday_id_seq'; //PK Sequence name

	protected $company_obj = NULL;


	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'special_day':
				$retval = array(
										0 => TTi18n::gettext('N/A'),
										1 => TTi18n::gettext('Good Friday'),
										5 => TTi18n::gettext('Easter Sunday'),
									);
				break;

			case 'type':
				$retval = array(
										10 => TTi18n::gettext('Static'),
										20 => TTi18n::gettext('Dynamic: Week Interval'),
										30 => TTi18n::gettext('Dynamic: Pivot Day')
									);
				break;
			case 'week_interval':
				$retval = array(
										1 => TTi18n::gettext('1st'),
										2 => TTi18n::gettext('2nd'),
										3 => TTi18n::gettext('3rd'),
										4 => TTi18n::gettext('4th'),
										5 => TTi18n::gettext('5th')
									);
				break;

			case 'pivot_day_direction':
				$retval = array(
										10 => TTi18n::gettext('Before'),
										20 => TTi18n::gettext('After'),
										30 => TTi18n::gettext('On or Before'),
										40 => TTi18n::gettext('On or After'),
									);
				break;
			case 'always_week_day':
				$retval = array(
											//Adjust holiday to next weekday
											0 => TTi18n::gettext('No'),
											1 => TTi18n::gettext('Yes - Previous Week Day'),
											2 => TTi18n::gettext('Yes - Next Week Day'),
											3 => TTi18n::gettext('Yes - Closest Week Day'),
										);
				break;
			case 'columns':
				$retval = array(
										'-1010-name' => TTi18n::gettext('Name'),
										'-1010-type' => TTi18n::gettext('Type'),
										'-1020-next_date' => TTi18n::gettext('Next Date'),

										'-2000-created_by' => TTi18n::gettext('Created By'),
										'-2010-created_date' => TTi18n::gettext('Created Date'),
										'-2020-updated_by' => TTi18n::gettext('Updated By'),
										'-2030-updated_date' => TTi18n::gettext('Updated Date'),
							);
				break;
			case 'list_columns':
				$retval = Misc::arrayIntersectByKey( $this->getOptions('default_display_columns'), Misc::trimSortPrefix( $this->getOptions('columns') ) );
				break;
			case 'default_display_columns': //Columns that are displayed by default.
				$retval = array(
								'name',
								'next_date',
								'updated_date',
								'updated_by',
								);
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = array(
								'name',
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
										'company_id' => 'Company',
										'special_day' => 'SpecialDay',
										'type_id' => 'Type',
										'pivot_day_direction_id' => 'PivotDayDirection',
										'name' => 'Name',
										'week_interval' => 'WeekInterval',
										'day_of_week' => 'DayOfWeek',
										'day_of_month' => 'DayOfMonth',
										'month_int' => 'Month',
										'always_week_day_id' => 'AlwaysOnWeekDay',
										'next_date' => 'NextDate',
										'deleted' => 'Deleted',
										);
		return $variable_function_map;
	}

	function getCompanyObject() {
		if ( is_object($this->company_obj) ) {
			return $this->company_obj;
		} else {
			$clf = TTnew( 'CompanyListFactory' );
			$this->company_obj = $clf->getById( $this->getCompany() )->getCurrent();

			return $this->company_obj;
		}
	}

	function getCompany() {
		if ( isset($this->data['company_id']) ) {
			return $this->data['company_id'];
		}

		return FALSE;
	}
	function setCompany($id) {
		$id = trim($id);

		Debug::Text('Company ID: '. $id, __FILE__, __LINE__, __METHOD__,10);
		$clf = TTnew( 'CompanyListFactory' );

		if ( $this->Validator->isResultSetWithRows(	'company',
													$clf->getByID($id),
													TTi18n::gettext('Company is invalid')
													) ) {

			$this->data['company_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

	function getSpecialDay() {
		if ( isset($this->data['special_day']) ) {
			return $this->data['special_day'];
		}

		return FALSE;
	}
	function setSpecialDay($value) {
		$value = trim($value);

		if ( $this->Validator->inArrayKey(	'special_day',
											$value,
											TTi18n::gettext('Incorrect Special Day'),
											$this->getOptions('special_day')) ) {

			$this->data['special_day'] = $value;

			return FALSE;
		}

		return FALSE;
	}

	function getType() {
		if ( isset($this->data['type_id']) ) {
			return $this->data['type_id'];
		}

		return FALSE;
	}
	function setType($value) {
		$value = trim($value);

		$key = Option::getByValue($value, $this->getOptions('type') );
		if ($key !== FALSE) {
			$value = $key;
		}

		if ( $this->Validator->inArrayKey(	'type',
											$value,
											TTi18n::gettext('Incorrect Type'),
											$this->getOptions('type')) ) {

			$this->data['type_id'] = $value;

			return FALSE;
		}

		return FALSE;
	}

	function getPivotDayDirection() {
		if ( isset($this->data['pivot_day_direction_id']) ) {
			return $this->data['pivot_day_direction_id'];
		}

		return FALSE;
	}
	function setPivotDayDirection($value) {
		$value = trim($value);

		if ( 	$value == 0
				OR
				$this->Validator->inArrayKey(	'pivot_day_direction',
											$value,
											TTi18n::gettext('Incorrect Pivot Day Direction'),
											$this->getOptions('pivot_day_direction')) ) {

			$this->data['pivot_day_direction_id'] = $value;

			return FALSE;
		}

		return FALSE;
	}

	function isUniqueName($name) {
		$ph = array(
					'company_id' => $this->getCompany() ,
					'name' => $name,
					);

		$query = 'select id from '. $this->getTable() .' where company_id = ? AND name = ? AND deleted=0';
		$name_id = $this->db->GetOne($query, $ph);
		Debug::Arr($name_id,'Unique Name: '. $name, __FILE__, __LINE__, __METHOD__,10);

		if ( $name_id === FALSE ) {
			return TRUE;
		} else {
			if ($name_id == $this->getId() ) {
				return TRUE;
			}
		}

		return FALSE;
	}
	function getName() {
		if ( isset($this->data['name']) ) {
			return $this->data['name'];
		}

		return FALSE;
	}
	function setName($name) {
		$name = trim($name);
		if (	$this->Validator->isLength(	'name',
											$name,
											TTi18n::gettext('Name is invalid'),
											2,50)
					AND
						$this->Validator->isTrue(		'name',
														$this->isUniqueName($name),
														TTi18n::gettext('Name is already in use'))

						) {

			$this->data['name'] = $name;

			return TRUE;
		}

		return FALSE;
	}

	function getWeekInterval() {
		if ( isset($this->data['week_interval']) ) {
			return (int)$this->data['week_interval'];
		}

		return FALSE;
	}
	function setWeekInterval($int) {
		$int = trim($int);

		if  ( empty($int) ){
			$int = 0;
		}

		if 	(	$this->Validator->isNumeric(		'week_interval',
													$int,
													TTi18n::gettext('Incorrect Week Interval')) ) {
			$this->data['week_interval'] = $int;

			return TRUE;
		}

		return FALSE;
	}

	function getDayOfWeek() {
		if ( isset($this->data['day_of_week']) ) {
			return (int)$this->data['day_of_week'];
		}

		return FALSE;
	}
	function setDayOfWeek($int) {
		$int = trim($int);

		if  ( $int == '' ){
			$int = 0;
		}

		if 	(	$this->Validator->isNumeric(		'day_of_week',
													$int,
													TTi18n::gettext('Incorrect Day Of Week')) ) {
			$this->data['day_of_week'] = $int;

			return TRUE;
		}

		return FALSE;
	}


	function getDayOfMonth() {
		if ( isset($this->data['day_of_month']) ) {
			return (int)$this->data['day_of_month'];
		}

		return FALSE;
	}
	function setDayOfMonth($int) {
		$int = trim($int);

		if  ( empty($int) ){
			$int = 0;
		}

		if 	(	$this->Validator->isNumeric(		'day_of_month',
													$int,
													TTi18n::gettext('Incorrect Day Of Month')) ) {
			$this->data['day_of_month'] = $int;

			return TRUE;
		}

		return FALSE;
	}

	function getMonth() {
		if ( isset($this->data['month_int']) ) {
			return (int)$this->data['month_int'];
		}

		return FALSE;
	}
	function setMonth($int) {
		$int = trim($int);

		if  ( empty($int) ){
			$int = 0;
		}

		if 	(	$this->Validator->isNumeric(		'month',
													$int,
													TTi18n::gettext('Incorrect Month')) ) {
			$this->data['month_int'] = $int;

			return TRUE;
		}

		return FALSE;
	}

	function getAlwaysOnWeekDay() {
		if ( isset($this->data['always_week_day_id']) ) {
			return (int)$this->data['always_week_day_id'];
		}
		return FALSE;
	}
	function setAlwaysOnWeekDay($int) {
		$int = (int)$int;

		if ( $this->Validator->inArrayKey(	'always_week_day_id',
											$int,
											TTi18n::gettext('Incorrect always on week day adjustment'),
											$this->getOptions('always_week_day') ) ) {

			$this->data['always_week_day_id'] = $int;

			return TRUE;
		}

		return FALSE;
	}

	function getNextDate( $epoch = FALSE ) {
		if ( $epoch == '' ) {
			$epoch = TTDate::getTime();
		}

		if ( $this->getSpecialDay() == 1 OR $this->getSpecialDay() == 5) {
			Debug::text('Easter Sunday Date...', __FILE__, __LINE__, __METHOD__, 10);

			//Use easter_days() instead, as easter_date returns incorrect values for some timezones/years (2010 and US/Eastern on Windows)
			//$easter_epoch = easter_date(date('Y', $epoch));
			$easter_epoch = mktime( 12, 0, 0, 3, ( 21+easter_days( date('Y', $epoch) ) ), date('Y', $epoch) );

			//Fix "cross-year" bug.
			if ( $easter_epoch < $epoch ) {
				//$easter_epoch = easter_date(date('Y', $epoch)+1);
				$easter_epoch = mktime( 12, 0, 0, 3, ( 21+easter_days( (date('Y', $epoch)+1) ) ), ( date('Y', $epoch)+1 ) );
			}

			if ( $this->getSpecialDay() == 1 ) {
				Debug::text('Good Friday Date...', __FILE__, __LINE__, __METHOD__, 10);
				//$holiday_epoch = mktime(12,0,0,date('n',$easter_epoch),date('j',$easter_epoch) - 2, date('Y', $easter_epoch));
				$holiday_epoch = $easter_epoch-(2*86400);
			} else {
				$holiday_epoch = $easter_epoch;
			}
		} else {
			if ( $this->getType() == 10 ) { //Static
				Debug::text('Static Date...', __FILE__, __LINE__, __METHOD__, 10);
				//Static date
				$holiday_epoch = mktime(12,0,0, $this->getMonth(), $this->getDayOfMonth(), date('Y', $epoch));
				if ( $holiday_epoch < $epoch ) {
					$holiday_epoch = mktime(12,0,0, $this->getMonth(), $this->getDayOfMonth(), date('Y', $epoch)+1 );
				}
			} elseif ( $this->getType() == 20 ) { //Dynamic - Week Interval
				Debug::text('Dynamic - Week Interval... Current Month: '. TTDate::getMonth( $epoch ) .' Holiday Month: '. $this->getMonth(), __FILE__, __LINE__, __METHOD__, 10);
				//Dynamic

				$start_month_epoch = TTDate::getBeginMonthEpoch( $epoch );
				$end_month_epoch = mktime(12,0,0, $this->getMonth()+1, 1, (date('Y', $epoch)+1));

				Debug::text('Start Epoch: '. TTDate::getDate('DATE+TIME', $start_month_epoch) .' End Epoch: '. TTDate::getDate('DATE+TIME', $end_month_epoch), __FILE__, __LINE__, __METHOD__, 10);
				//Get all day of weeks in the month. Determine which is less or greater then day.
				$day_of_week_dates = array();
				$week_interval = 0;
				for ($i = $start_month_epoch; $i <= $end_month_epoch; $i+=86400) {
					if ( TTDate::getMonth( $i ) == $this->getMonth() ) {
						$day_of_week = TTDate::getDayOfWeek( $i );
						//Debug::text('I: '. $i .'('.TTDate::getDate('DATE+TIME', $i).') Current Day Of Week: '. $day_of_week .' Looking for Day Of Week: '. $this->getDayOfWeek(), __FILE__, __LINE__, __METHOD__, 10);

						if ( $day_of_week == abs( $this->getDayOfWeek() ) ) {
							$day_of_week_dates[] = date('j', $i);
							Debug::text('I: '. $i .' Day Of Month: '. date('j',$i), __FILE__, __LINE__, __METHOD__, 10);

							$week_interval++;
						}

						if ( $week_interval >= $this->getWeekInterval() ) {
							$tmp_holiday_epoch = mktime(12,0,0, $this->getMonth(), $day_of_week_dates[$this->getWeekInterval()-1], date('Y', $i));

							//Make sure we keep processing until the holiday comes AFTER todays date.
							if ( $tmp_holiday_epoch > $epoch ) {
								break;
							}
						}
					}
				}

				//Debug::Arr($day_of_week_dates, 'Week Dates Arr: ', __FILE__, __LINE__, __METHOD__, 10);
				//$holiday_epoch = mktime(12,0,0, $this->getMonth(), $day_of_week_dates[$this->getWeekInterval()-1], date('Y', $i));
				$holiday_epoch = $tmp_holiday_epoch;
			} elseif ( $this->getType() == 30 ) { //Dynamic - Pivot Day
				Debug::text('Dynamic - Pivot Date...', __FILE__, __LINE__, __METHOD__, 10);
				//Dynamic
				if ( TTDate::getMonth( $epoch ) > $this->getMonth() ) {
					$year_modifier = 1;
				} else {
					$year_modifier = 0;
				}

				$start_epoch = mktime(12,0,0, $this->getMonth(), $this->getDayOfMonth(), date('Y', $epoch)+$year_modifier);

				$holiday_epoch = $start_epoch;

				$x=0;
				$x_max=100;

				if ( $this->getPivotDayDirection() == 10 OR $this->getPivotDayDirection() == 30 ) {
					$direction_multiplier = -1;
				} else {
					$direction_multiplier = 1;
				}

				$adjustment = (86400 * $direction_multiplier);  // Adjust by 1 day before or after.

				if ( $this->getPivotDayDirection() == 10 OR $this->getPivotDayDirection() == 20 ) {
					$holiday_epoch += $adjustment;
				}

				while ( $this->getDayOfWeek() != TTDate::getDayOfWeek( $holiday_epoch ) AND $x < $x_max ) {
						Debug::text('X: '. $x .' aTrying...'. TTDate::getDate('DATE+TIME', $holiday_epoch), __FILE__, __LINE__, __METHOD__, 10);
						$holiday_epoch += $adjustment;

						$x++;
				}
			}
		}

		$holiday_epoch = TTDate::getNearestWeekDay( $holiday_epoch, $this->getAlwaysOnWeekDay() );

		Debug::text('Next Date for: '. $this->getName() .' is: '. TTDate::getDate('DATE+TIME', $holiday_epoch), __FILE__, __LINE__, __METHOD__, 10);

		return $holiday_epoch;
	}

	static function addPresets($company_id, $country) {
		if ( $company_id == '' ) {
			return FALSE;
		}

		//http://www.statutoryholidays.com/
		switch (strtolower($country)) {
			case 'ca':
				Debug::text('Saving.... New Years', __FILE__, __LINE__, __METHOD__, 10);
				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				$rhf->setName( 'New Years Day' );
				$rhf->setType( 10 );
				$rhf->setSpecialDay( 0 );
				//$rhf->setWeekInterval( $data['week_interval'] );
				//$rhf->setDayOfWeek( $data['day_of_week'] );
				$rhf->setDayOfMonth( 1 );
				$rhf->setMonth( 1 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
				}

				Debug::text('Saving.... Good Friday/Easter', __FILE__, __LINE__, __METHOD__, 10);
				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				$rhf->setName( 'Good Friday' );
				$rhf->setType( 20 );
				$rhf->setSpecialDay( 1 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
				}

				Debug::text('Saving.... Canada Day', __FILE__, __LINE__, __METHOD__, 10);
				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				$rhf->setName( 'Canada Day' );
				$rhf->setType( 10 );
				$rhf->setSpecialDay( 0 );

				//$rhf->setWeekInterval( $data['week_interval'] );
				//$rhf->setDayOfWeek( $data['day_of_week'] );
				$rhf->setDayOfMonth( 1 );
				$rhf->setMonth( 7 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
				}

				Debug::text('Saving.... Labour Day', __FILE__, __LINE__, __METHOD__, 10);
				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				$rhf->setName( 'Labour Day' );
				$rhf->setType( 20 );
				$rhf->setSpecialDay( 0 );
				$rhf->setWeekInterval( 1 );
				$rhf->setDayOfWeek( 1 );
				//$rhf->setDayOfMonth( 1 );
				$rhf->setMonth( 9 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
				}

				Debug::text('Saving.... Xmas', __FILE__, __LINE__, __METHOD__, 10);
				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				$rhf->setName( 'Christmas' );
				$rhf->setType( 10 );
				$rhf->setSpecialDay( 0 );

				//$rhf->setWeekInterval( $data['week_interval'] );
				//$rhf->setDayOfWeek( $data['day_of_week'] );
				$rhf->setDayOfMonth( 25 );
				$rhf->setMonth( 12 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
				}

				Debug::text('Saving.... Xmas', __FILE__, __LINE__, __METHOD__, 10);
				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				$rhf->setName( 'Boxing Day' ); //ON - Boxing day.
				$rhf->setType( 10 );
				$rhf->setSpecialDay( 0 );

				//$rhf->setWeekInterval( $data['week_interval'] );
				//$rhf->setDayOfWeek( $data['day_of_week'] );
				$rhf->setDayOfMonth( 26 );
				$rhf->setMonth( 12 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
				}

				Debug::text('Saving.... British Columbia Day', __FILE__, __LINE__, __METHOD__, 10);
				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				//Known as different names in different provinces. BC, SK, MN, NB, NU.
				//PEI calls this Islander Day, MN calls it Louis Reil day.
				$rhf->setName( 'Civic Holiday' );
				$rhf->setType( 20 );
				$rhf->setSpecialDay( 0 );
				$rhf->setWeekInterval( 1 );
				$rhf->setDayOfWeek( 1 );
				//$rhf->setDayOfMonth( 1 );
				$rhf->setMonth( 8 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
				}

				Debug::text('Saving.... Family Day', __FILE__, __LINE__, __METHOD__, 10);
				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				$rhf->setName( 'Family Day' ); //BC, AB, SK, ON
				$rhf->setType( 20 );
				$rhf->setSpecialDay( 0 );
				$rhf->setWeekInterval( 3 );
				$rhf->setDayOfWeek( 1 );
				//$rhf->setDayOfMonth( 1 );
				$rhf->setMonth( 2 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
				}

				//Holidays across different provinces
				Debug::text('Saving.... Victoria Day', __FILE__, __LINE__, __METHOD__, 10);
				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				$rhf->setName( 'Victoria Day' );
				$rhf->setType( 30 );
				$rhf->setSpecialDay( 0 );
				$rhf->setPivotDayDirection( 30 );
				$rhf->setDayOfWeek( 1 );
				$rhf->setDayOfMonth( 24 );
				$rhf->setMonth( 5 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
				}

				Debug::text('Saving.... Thanksgiving', __FILE__, __LINE__, __METHOD__, 10);
				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				$rhf->setName( 'Thanksgiving Day' );
				$rhf->setType( 20 );
				$rhf->setSpecialDay( 0 );
				$rhf->setWeekInterval( 2 );
				$rhf->setDayOfWeek( 1 );
				//$rhf->setDayOfMonth( 1 );
				$rhf->setMonth( 10 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
				}

				Debug::text('Saving.... Rememberance Day', __FILE__, __LINE__, __METHOD__, 10);
				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				$rhf->setName( 'Rememberance Day' );
				$rhf->setType( 10 );
				$rhf->setSpecialDay( 0 );
				//$rhf->setWeekInterval( $data['week_interval'] );
				//$rhf->setDayOfWeek( $data['day_of_week'] );
				$rhf->setDayOfMonth( 11 );
				$rhf->setMonth( 11 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
				}

				break;
			case 'us':

				Debug::text('Saving.... New Years', __FILE__, __LINE__, __METHOD__, 10);
				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				$rhf->setName( 'New Years Day' );
				$rhf->setType( 10 );
				$rhf->setSpecialDay( 0 );
				//$rhf->setWeekInterval( $data['week_interval'] );
				//$rhf->setDayOfWeek( $data['day_of_week'] );
				$rhf->setDayOfMonth( 1 );
				$rhf->setMonth( 1 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
				}

				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				$rhf->setName( 'Independence Day' );
				$rhf->setType( 10 );
				$rhf->setSpecialDay( 0 );
				//$rhf->setWeekInterval( $data['week_interval'] );
				//$rhf->setDayOfWeek( $data['day_of_week'] );
				$rhf->setDayOfMonth( 4 );
				$rhf->setMonth( 7 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
				}

				Debug::text('Saving.... New Years', __FILE__, __LINE__, __METHOD__, 10);
				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				$rhf->setName( 'Veterans Day' );
				$rhf->setType( 10 );
				$rhf->setSpecialDay( 0 );
				//$rhf->setWeekInterval( $data['week_interval'] );
				//$rhf->setDayOfWeek( $data['day_of_week'] );
				$rhf->setDayOfMonth( 11 );
				$rhf->setMonth( 11 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
				}

				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				$rhf->setName( 'Christmas' );
				$rhf->setType( 10 );
				$rhf->setSpecialDay( 0 );
				//$rhf->setWeekInterval( $data['week_interval'] );
				//$rhf->setDayOfWeek( $data['day_of_week'] );
				$rhf->setDayOfMonth( 25 );
				$rhf->setMonth( 12 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
				}

				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				$rhf->setName( 'Martin Luther King Day' );
				$rhf->setType( 20 );
				$rhf->setSpecialDay( 0 );

				$rhf->setWeekInterval( 3 );
				$rhf->setDayOfWeek( 1 );
				$rhf->setMonth( 1 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
				}

				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				$rhf->setName( 'Presidents Day' );
				$rhf->setType( 20 );
				$rhf->setSpecialDay( 0 );

				$rhf->setWeekInterval( 3 );
				$rhf->setDayOfWeek( 1 );
				$rhf->setMonth( 2 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
				}

				//Pivot Day
				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				$rhf->setName( 'Memorial Day' );
				$rhf->setType( 30 );
				$rhf->setSpecialDay( 0 );

				$rhf->setPivotDayDirection( 20 );
				$rhf->setDayOfWeek( 1 );
				$rhf->setDayOfMonth( 24 );
				$rhf->setMonth( 5 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
				}

				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				$rhf->setName( 'Labor Day' );
				$rhf->setType( 20 );
				$rhf->setSpecialDay( 0 );
				$rhf->setWeekInterval( 1 );
				$rhf->setDayOfWeek( 1 );
				$rhf->setMonth( 9 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
				}

				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				$rhf->setName( 'Columbus Day' );
				$rhf->setType( 20 );
				$rhf->setSpecialDay( 0 );
				$rhf->setWeekInterval( 2 );
				$rhf->setDayOfWeek( 1 );
				$rhf->setMonth( 10 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
				}

				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				$rhf->setName( 'Thanksgiving Day' );
				$rhf->setType( 20 );
				$rhf->setSpecialDay( 0 );
				$rhf->setWeekInterval( 4 );
				$rhf->setDayOfWeek( 4 );
				$rhf->setMonth( 11 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
				}

				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				$rhf->setName( 'Day After Thanksgiving Day' );
				$rhf->setType( 20 );
				$rhf->setSpecialDay( 0 );
				$rhf->setWeekInterval( 4 );
				$rhf->setDayOfWeek( 5 );
				$rhf->setMonth( 11 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
				}

				Debug::text('Saving.... Good Friday/Easter', __FILE__, __LINE__, __METHOD__, 10);
				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				$rhf->setName( 'Good Friday' );
				$rhf->setType( 20 );
				$rhf->setSpecialDay( 1 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
				}

				break;

			case 'cr':

				Debug::text('Saving.... New Years', __FILE__, __LINE__, __METHOD__, 10);
				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				$rhf->setName( TTi18n::gettext('New Years Day') );
				$rhf->setType( 10 );
				$rhf->setSpecialDay( 0 );
				$rhf->setDayOfMonth( 1 );
				$rhf->setMonth( 1 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
				}

				Debug::text('Saving.... Good Thursday/Easter', __FILE__, __LINE__, __METHOD__, 10);
				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				$rhf->setName( TTi18n::gettext('Good Thursday') );
				$rhf->setType( 20 );
				$rhf->setSpecialDay( 2 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
				}

				Debug::text('Saving.... Good Friday/Easter', __FILE__, __LINE__, __METHOD__, 10);
				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				$rhf->setName( TTi18n::gettext('Good Friday') );
				$rhf->setType( 20 );
				$rhf->setSpecialDay( 1 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
				}

				Debug::text('Saving.... Juan Santamaria Day', __FILE__, __LINE__, __METHOD__, 10);
				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				$rhf->setName( TTi18n::gettext('Juan Santamaria Day') );
				$rhf->setType( 10 );
				$rhf->setSpecialDay( 0 );
				$rhf->setDayOfMonth( 11 );
				$rhf->setMonth( 4 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
				}

				Debug::text('Saving.... Labour Day', __FILE__, __LINE__, __METHOD__, 10);
				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				$rhf->setName( TTi18n::gettext('Labour Day') );
				$rhf->setType( 10 );
				$rhf->setSpecialDay( 0 );
				$rhf->setDayOfMonth( 1 );
				$rhf->setMonth( 5 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
				}

				Debug::text('Saving.... Anexion de Guanacaste Day', __FILE__, __LINE__, __METHOD__, 10);
				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				$rhf->setName( TTi18n::gettext('Anexion de Guanacaste Day') );
				$rhf->setType( 10 );
				$rhf->setSpecialDay( 0 );
				$rhf->setDayOfMonth( 25 );
				$rhf->setMonth( 7 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
				}

				Debug::text('Saving.... Virgen de los Angeles Day', __FILE__, __LINE__, __METHOD__, 10);
				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				$rhf->setName( TTi18n::gettext('Virgen de los Angeles Day') );
				$rhf->setType( 10 );
				$rhf->setSpecialDay( 0 );
				$rhf->setDayOfMonth( 2 );
				$rhf->setMonth( 8 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
				}

				Debug::text('Saving.... Mothers  Day', __FILE__, __LINE__, __METHOD__, 10);
				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				$rhf->setName( TTi18n::gettext('Mothers Day') );
				$rhf->setType( 10 );
				$rhf->setSpecialDay( 0 );
				$rhf->setDayOfMonth( 15 );
				$rhf->setMonth( 8 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
				}

				Debug::text('Saving.... Independence Day', __FILE__, __LINE__, __METHOD__, 10);
				$rhf = TTnew( 'RecurringHolidayFactory' );
 				$rhf->setCompany( $company_id );
				$rhf->setName( TTi18n::gettext('Independence Day CR') );
				$rhf->setType( 10 );
				$rhf->setSpecialDay( 0 );
				$rhf->setDayOfMonth( 15 );
				$rhf->setMonth( 9 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
				}

				Debug::text('Saving.... Culture Day', __FILE__, __LINE__, __METHOD__, 10);
				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				$rhf->setName( TTi18n::gettext('Culture Day') );
				$rhf->setType( 10 );
				$rhf->setSpecialDay( 0 );
				$rhf->setDayOfMonth( 12 );
				$rhf->setMonth( 10 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
				}

				Debug::text('Saving.... Christmas', __FILE__, __LINE__, __METHOD__, 10);
				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				$rhf->setName( TTi18n::gettext('Christmas') );
				$rhf->setType( 10 );
				$rhf->setSpecialDay( 0 );
				$rhf->setDayOfMonth( 25 );
				$rhf->setMonth( 12 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
				}

				break;
			case 'gt':

				Debug::text('Saving.... New Years', __FILE__, __LINE__, __METHOD__, 10);
				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				$rhf->setName( TTi18n::gettext('New Years Day') );
				$rhf->setType( 10 );
				$rhf->setSpecialDay( 0 );
				$rhf->setDayOfMonth( 1 );
				$rhf->setMonth( 1 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
				}

				Debug::text('Saving.... Good Wednesday/Easter', __FILE__, __LINE__, __METHOD__, 10);
				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				$rhf->setName( TTi18n::gettext('Good Wednesday') );
				$rhf->setType( 20 );
				$rhf->setSpecialDay( 3 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
				}

				Debug::text('Saving.... Good Thursday/Easter', __FILE__, __LINE__, __METHOD__, 10);
				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				$rhf->setName( TTi18n::gettext('Good Thursday') );
				$rhf->setType( 20 );
				$rhf->setSpecialDay( 2 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
				}

				Debug::text('Saving.... Good Friday/Easter', __FILE__, __LINE__, __METHOD__, 10);
				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				$rhf->setName( TTi18n::gettext('Good Friday') );
				$rhf->setType( 20 );
				$rhf->setSpecialDay( 1 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
				}

				Debug::text('Saving.... Labour Day', __FILE__, __LINE__, __METHOD__, 10);
				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				$rhf->setName( TTi18n::gettext('Labour Day') );
				$rhf->setType( 10 );
				$rhf->setSpecialDay( 0 );
				$rhf->setDayOfMonth( 1 );
				$rhf->setMonth( 5 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
				}

				Debug::text('Saving.... Army Day', __FILE__, __LINE__, __METHOD__, 10);
				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				$rhf->setName( TTi18n::gettext('Army Day') );
				$rhf->setType( 10 );
				$rhf->setSpecialDay( 0 );
				$rhf->setDayOfMonth( 30 );
				$rhf->setMonth( 6 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
				}

				Debug::text('Saving.... Virgin Day', __FILE__, __LINE__, __METHOD__, 10);
				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				$rhf->setName( TTi18n::gettext('Virgin Day') );
				$rhf->setType( 10 );
				$rhf->setSpecialDay( 0 );
				$rhf->setDayOfMonth( 15 );
				$rhf->setMonth( 8 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
				}

				Debug::text('Saving.... Independence Day', __FILE__, __LINE__, __METHOD__, 10);
				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				$rhf->setName( TTi18n::gettext('Independence Day CR') );
				$rhf->setType( 10 );
				$rhf->setSpecialDay( 0 );
				$rhf->setDayOfMonth( 15 );
				$rhf->setMonth( 9 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
				}

				Debug::text('Saving.... 1944 Revolution Day', __FILE__, __LINE__, __METHOD__, 10);
				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				$rhf->setName( TTi18n::gettext('1944 Revolution Day') );
				$rhf->setType( 10 );
				$rhf->setSpecialDay( 0 );
				$rhf->setDayOfMonth( 20 );
				$rhf->setMonth( 10 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
				}

				Debug::text('Saving.... All Saint Day', __FILE__, __LINE__, __METHOD__, 10);
				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				$rhf->setName( TTi18n::gettext('All Saint Day') );
				$rhf->setType( 30 );
				$rhf->setSpecialDay( 0 );
				$rhf->setDayOfMonth( 1 );
				$rhf->setMonth( 11 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
				}


				Debug::text('Saving.... Christmas Eve', __FILE__, __LINE__, __METHOD__, 10);
				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				$rhf->setName( TTi18n::gettext('Christmas Eve') );
				$rhf->setType( 10 );
				$rhf->setSpecialDay( 0 );
				$rhf->setDayOfMonth( 24 );
				$rhf->setMonth( 12 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
				}

				Debug::text('Saving.... Christmas', __FILE__, __LINE__, __METHOD__, 10);
				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				$rhf->setName( TTi18n::gettext('Christmas') );
				$rhf->setType( 10 );
				$rhf->setSpecialDay( 0 );
				$rhf->setDayOfMonth( 25 );
				$rhf->setMonth( 12 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
				}

				break;

			case 'hn':

				Debug::text('Saving.... New Years', __FILE__, __LINE__, __METHOD__, 10);
				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				$rhf->setName( TTi18n::gettext('New Years Day') );
				$rhf->setType( 10 );
				$rhf->setSpecialDay( 0 );
				$rhf->setDayOfMonth( 1 );
				$rhf->setMonth( 1 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
				}

				Debug::text('Saving.... Good Thursday/Easter', __FILE__, __LINE__, __METHOD__, 10);
				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				$rhf->setName( TTi18n::gettext('Good Thursday') );
				$rhf->setType( 20 );
				$rhf->setSpecialDay( 2 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
				}

				Debug::text('Saving.... Good Friday/Easter', __FILE__, __LINE__, __METHOD__, 10);
				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				$rhf->setName( TTi18n::gettext('Good Friday') );
				$rhf->setType( 20 );
				$rhf->setSpecialDay( 1 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
				}

				Debug::text('Saving.... Good Saturday/Easter', __FILE__, __LINE__, __METHOD__, 10);
				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				$rhf->setName( TTi18n::gettext('Good Saturday') );
				$rhf->setType( 20 );
				$rhf->setSpecialDay( 2 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
				}

				Debug::text('Saving.... Labour Day', __FILE__, __LINE__, __METHOD__, 10);
				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				$rhf->setName( TTi18n::gettext('Labour Day') );
				$rhf->setType( 10 );
				$rhf->setSpecialDay( 0 );
				$rhf->setDayOfMonth( 1 );
				$rhf->setMonth( 5 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
				}

				Debug::text('Saving.... Independence Day', __FILE__, __LINE__, __METHOD__, 10);
				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				$rhf->setName( TTi18n::gettext('Independence Day') );
				$rhf->setType( 10 );
				$rhf->setSpecialDay( 0 );
				$rhf->setDayOfMonth( 15 );
				$rhf->setMonth( 9 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
				}

				Debug::text('Saving.... Morazan Day', __FILE__, __LINE__, __METHOD__, 10);
				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				$rhf->setName( TTi18n::gettext('Morazan Day') );
				$rhf->setType( 10 );
				$rhf->setSpecialDay( 0 );
				$rhf->setDayOfMonth( 3 );
				$rhf->setMonth( 10 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
				}

				Debug::text('Saving.... Culture Day', __FILE__, __LINE__, __METHOD__, 10);
				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				$rhf->setName( TTi18n::gettext('Culture Day') );
				$rhf->setType( 30 );
				$rhf->setSpecialDay( 0 );
				$rhf->setDayOfMonth( 12 );
				$rhf->setMonth( 10 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
				}


				Debug::text('Saving.... Armed Forces Day', __FILE__, __LINE__, __METHOD__, 10);
				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				$rhf->setName( TTi18n::gettext('Armed Forces Day') );
				$rhf->setType( 10 );
				$rhf->setSpecialDay( 0 );
				$rhf->setDayOfMonth( 21 );
				$rhf->setMonth( 12 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
				}

				Debug::text('Saving.... Christmas', __FILE__, __LINE__, __METHOD__, 10);
				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				$rhf->setName( TTi18n::gettext('Christmas') );
				$rhf->setType( 10 );
				$rhf->setSpecialDay( 0 );
				$rhf->setDayOfMonth( 25 );
				$rhf->setMonth( 12 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
				}

				break;

			case 'sv':

				Debug::text('Saving.... New Years', __FILE__, __LINE__, __METHOD__, 10);
				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				$rhf->setName( TTi18n::gettext('New Years Day') );
				$rhf->setType( 10 );
				$rhf->setSpecialDay( 0 );
				$rhf->setDayOfMonth( 1 );
				$rhf->setMonth( 1 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
				}

				Debug::text('Saving.... Good Thursday/Easter', __FILE__, __LINE__, __METHOD__, 10);
				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				$rhf->setName( TTi18n::gettext('Good Thursday') );
				$rhf->setType( 20 );
				$rhf->setSpecialDay( 2 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
				}

				Debug::text('Saving.... Good Friday/Easter', __FILE__, __LINE__, __METHOD__, 10);
				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				$rhf->setName( TTi18n::gettext('Good Friday') );
				$rhf->setType( 20 );
				$rhf->setSpecialDay( 1 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
 				}

				Debug::text('Saving.... Labour Day', __FILE__, __LINE__, __METHOD__, 10);
				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				$rhf->setName( TTi18n::gettext('Labour Day') );
				$rhf->setType( 10 );
				$rhf->setSpecialDay( 0 );
				$rhf->setDayOfMonth( 1 );
				$rhf->setMonth( 5 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
				}

 				Debug::text('Saving.... Bank Day', __FILE__, __LINE__, __METHOD__, 10);
				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				$rhf->setName( TTi18n::gettext('Bank Day') );
				$rhf->setType( 10 );
				$rhf->setSpecialDay( 0 );
				$rhf->setDayOfMonth( 30 );
				$rhf->setMonth( 6 );

 				if ( $rhf->isValid() ) {
					$rhf->Save();
 				}

				Debug::text('Saving.... San Salvador Party', __FILE__, __LINE__, __METHOD__, 10);
				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				$rhf->setName( TTi18n::gettext('San Salvador Party') );
				$rhf->setType( 10 );
				$rhf->setSpecialDay( 0 );
				$rhf->setDayOfMonth( 4 );
				$rhf->setMonth( 8 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
				}

				Debug::text('Saving.... Independence Day', __FILE__, __LINE__, __METHOD__, 10);
				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				$rhf->setName( TTi18n::gettext('Independence Day') );
				$rhf->setType( 10 );
				$rhf->setSpecialDay( 0 );
				$rhf->setDayOfMonth( 15 );
				$rhf->setMonth( 9 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
				}

				Debug::text('Saving.... Columbus Day', __FILE__, __LINE__, __METHOD__, 10);
				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				$rhf->setName( TTi18n::gettext('Columbus Day') );
				$rhf->setType( 10 );
				$rhf->setSpecialDay( 0 );
				$rhf->setDayOfMonth( 12 );
				$rhf->setMonth( 10 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
				}

				Debug::text('Saving.... All Saints Day', __FILE__, __LINE__, __METHOD__, 10);
				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				$rhf->setName( TTi18n::gettext('All Saints Day') );
				$rhf->setType( 10 );
				$rhf->setSpecialDay( 0 );
				$rhf->setDayOfMonth( 2 );
				$rhf->setMonth( 11 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
				}

				Debug::text('Saving.... First Cry of Independence', __FILE__, __LINE__, __METHOD__, 10);
				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				$rhf->setName( TTi18n::gettext('First Cry of Independence') );
				$rhf->setType( 10 );
				$rhf->setSpecialDay( 0 );
				$rhf->setDayOfMonth( 5 );
				$rhf->setMonth( 11 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
				}

				Debug::text('Saving.... Christmas', __FILE__, __LINE__, __METHOD__, 10);
				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				$rhf->setName( TTi18n::gettext('Christmas') );
				$rhf->setType( 10 );
				$rhf->setSpecialDay( 0 );
				$rhf->setDayOfMonth( 25 );
				$rhf->setMonth( 12 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
				}

				break;

			case 'ni':

				Debug::text('Saving.... New Years', __FILE__, __LINE__, __METHOD__, 10);
				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				$rhf->setName( TTi18n::gettext('New Years Day') );
				$rhf->setType( 10 );
				$rhf->setSpecialDay( 0 );
				$rhf->setDayOfMonth( 1 );
				$rhf->setMonth( 1 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
				}

				Debug::text('Saving.... Good Thursday/Easter', __FILE__, __LINE__, __METHOD__, 10);
				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				$rhf->setName( TTi18n::gettext('Good Thursday') );
				$rhf->setType( 20 );
				$rhf->setSpecialDay( 2 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
				}

				Debug::text('Saving.... Good Friday/Easter', __FILE__, __LINE__, __METHOD__, 10);
				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				$rhf->setName( TTi18n::gettext('Good Friday') );
				$rhf->setType( 20 );
				$rhf->setSpecialDay( 1 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
				}

				Debug::text('Saving.... Labour Day', __FILE__, __LINE__, __METHOD__, 10);
				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				$rhf->setName( TTi18n::gettext('Labour Day') );
				$rhf->setType( 10 );
				$rhf->setSpecialDay( 0 );
				$rhf->setDayOfMonth( 1 );
				$rhf->setMonth( 5 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
				}

				Debug::text('Saving.... Revolution Day', __FILE__, __LINE__, __METHOD__, 10);
				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				$rhf->setName( TTi18n::gettext('Revolution Day') );
				$rhf->setType( 10 );
				$rhf->setSpecialDay( 0 );
				$rhf->setDayOfMonth( 19 );
				$rhf->setMonth( 7 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
				}

				Debug::text('Saving.... San Jacinto Battle', __FILE__, __LINE__, __METHOD__, 10);
				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				$rhf->setName( TTi18n::gettext('San Jacinto Battle') );
				$rhf->setType( 10 );
				$rhf->setSpecialDay( 0 );
				$rhf->setDayOfMonth( 14 );
				$rhf->setMonth( 9 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
				}

				Debug::text('Saving.... Independence Day', __FILE__, __LINE__, __METHOD__, 10);
				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				$rhf->setName( TTi18n::gettext('Independence Day') );
				$rhf->setType( 10 );
				$rhf->setSpecialDay( 0 );
				$rhf->setDayOfMonth( 15 );
				$rhf->setMonth( 9 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
				}

				Debug::text('Saving.... Christmas', __FILE__, __LINE__, __METHOD__, 10);
				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				$rhf->setName( TTi18n::gettext('Christmas') );
				$rhf->setType( 10 );
				$rhf->setSpecialDay( 0 );
 				$rhf->setDayOfMonth( 25 );
				$rhf->setMonth( 12 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
 				}

				break;

			case 'pa':

				Debug::text('Saving.... New Years', __FILE__, __LINE__, __METHOD__, 10);
				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				$rhf->setName( TTi18n::gettext('New Years Day') );
				$rhf->setType( 10 );
				$rhf->setSpecialDay( 0 );
 				$rhf->setDayOfMonth( 1 );
	 			$rhf->setMonth( 1 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
				}

  				Debug::text('Saving.... Martyr`s Day', __FILE__, __LINE__, __METHOD__, 10);
				$rhf = TTnew( 'RecurringHolidayFactory' );
 				$rhf->setCompany( $company_id );
				$rhf->setName( TTi18n::gettext('Martyr`s Day') );
				$rhf->setType( 10 );
				$rhf->setSpecialDay( 0 );
				$rhf->setDayOfMonth( 9 );
 				$rhf->setMonth( 1 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
				}

				Debug::text('Saving.... Good Friday/Easter', __FILE__, __LINE__, __METHOD__, 10);
	 			$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				$rhf->setName( TTi18n::gettext('Good Friday') );
				$rhf->setType( 20 );
				$rhf->setSpecialDay( 1 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
  				}

				Debug::text('Saving.... Labour Day', __FILE__, __LINE__, __METHOD__, 10);
				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				$rhf->setName( TTi18n::gettext('Labour Day') );
 				$rhf->setType( 10 );
				$rhf->setSpecialDay( 0 );
				$rhf->setDayOfMonth( 1 );
				$rhf->setMonth( 5 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
				}

				Debug::text('Saving.... Separation Day (from Colombia)', __FILE__, __LINE__, __METHOD__, 10);
 				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				$rhf->setName( TTi18n::gettext('Separation Day from Colombia') );
				$rhf->setType( 10 );
				$rhf->setSpecialDay( 0 );
				$rhf->setDayOfMonth( 3 );
	 			$rhf->setMonth( 11 );

				if ( $rhf->isValid() ) {
 					$rhf->Save();
				}

	 			Debug::text('Saving.... Flag Day', __FILE__, __LINE__, __METHOD__, 10);
				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
	 			$rhf->setName( TTi18n::gettext('Flag Day') );
				$rhf->setType( 10 );
 				$rhf->setSpecialDay( 0 );
				$rhf->setDayOfMonth( 4 );
				$rhf->setMonth( 11 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
				}

				Debug::text('Saving.... Primer Grito de Independencia de la Villa de los Santos', __FILE__, __LINE__, __METHOD__, 10);
				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				$rhf->setName( TTi18n::gettext('Primer Grito de Independencia de la Villa de los Santos') );
 				$rhf->setType( 10 );
 				$rhf->setSpecialDay( 0 );
				$rhf->setDayOfMonth( 10 );
				$rhf->setMonth( 11 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
				}

 				Debug::text('Saving.... Independence Day', __FILE__, __LINE__, __METHOD__, 10);
				$rhf = TTnew( 'RecurringHolidayFactory' );
 				$rhf->setCompany( $company_id );
				$rhf->setName( TTi18n::gettext('Independence Day') );
				$rhf->setType( 10 );
				$rhf->setSpecialDay( 0 );
 				$rhf->setDayOfMonth( 28 );
				$rhf->setMonth( 11 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
				}

 				Debug::text('Saving.... Mothers Day', __FILE__, __LINE__, __METHOD__, 10);
				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				$rhf->setName( TTi18n::gettext('Mothers Day') );
 				$rhf->setType( 10 );
				$rhf->setSpecialDay( 0 );
				$rhf->setDayOfMonth( 8 );
 				$rhf->setMonth( 12 );

				if ( $rhf->isValid() ) {
					$rhf->Save();
				}

				Debug::text('Saving.... Christmas', __FILE__, __LINE__, __METHOD__, 10);
 				$rhf = TTnew( 'RecurringHolidayFactory' );
				$rhf->setCompany( $company_id );
				$rhf->setName( TTi18n::gettext('Christmas')  );
				$rhf->setType( 10 );
				$rhf->setSpecialDay( 0 );
				$rhf->setDayOfMonth( 25 );
				$rhf->setMonth( 12 );

 				if ( $rhf->isValid() ) {
					$rhf->Save();
				}

			break;
		}


		return TRUE;
	}

	function Validate() {
		return TRUE;
	}

	function preSave() {
		return TRUE;
	}

	function postSave() {
		return TRUE;
	}

	function setObjectFromArray( $data ) {
		if ( is_array( $data ) ) {
			$variable_function_map = $this->getVariableToFunctionMap();
			foreach( $variable_function_map as $key => $function ) {
				if ( isset($data[$key]) ) {

					$function = 'set'.$function;
					switch( $key ) {
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

	function getObjectAsArray( $include_columns = NULL ) {
		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == NULL OR ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) ) {

					$function = 'get'.$function_stub;
					switch( $variable ) {
						case 'status':
							$function = 'get'.$variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						case 'next_date':
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = TTDate::getAPIDate( 'DATE', $this->$function() );
							}
							break;
						default:
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = $this->$function();
							}
							break;
					}

				}
			}
			$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
	}

	function addLog( $log_action ) {
		return TTLog::addEntry( $this->getId(), $log_action,  TTi18n::getText('Recurring Holiday'), NULL, $this->getTable(), $this );
	}
}
?>