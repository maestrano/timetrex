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
 * $Revision: 3387 $
 * $Id: ImportBranch.class.php 3387 2010-03-04 17:42:17Z ipso $
 * $Date: 2010-03-04 09:42:17 -0800 (Thu, 04 Mar 2010) $
 */


/**
 * @package Modules\Import
 */
class ImportPayPeriod extends Import {

	public $class_name = 'APIPayPeriod';

	public $pay_period_schedule_options = FALSE;

	function _getFactoryOptions( $name, $parent = NULL ) {

		$retval = NULL;
		switch( $name ) {
			case 'columns':
				$ppf = TTNew('PayPeriodFactory');
				$retval = Misc::arrayIntersectByKey( array('pay_period_schedule_id','start_date','end_date','transaction_date'), Misc::trimSortPrefix( $ppf->getOptions('columns') ) );
				break;
			case 'column_aliases':
				//Used for converting column names after they have been parsed.
				$retval = array(
								'pay_period_schedule' => 'pay_period_schedule_id',
								);
				break;
			case 'import_options':
				$retval = array(
								'-1010-fuzzy_match' => TTi18n::getText('Enable smart matching.'),
								);
				break;
			case 'parse_hint':
			case 'parse_hint':
				$upf = TTnew('UserPreferenceFactory');

				$retval = array(
								'start_date' => $upf->getOptions('date_format'),
								'end_date' => $upf->getOptions('date_format'),
								'transaction_date' => $upf->getOptions('date_format'),
								);
				break;
		}

		return $retval;
	}


	function _preParseRow( $row_number, $raw_row ) {
		$retval = $this->getObject()->getPayPeriodDefaultData();

		return $retval;
	}

	function _postParseRow( $row_number, $raw_row ) {
		//$raw_row['user_id'] = $this->getUserIdByRowData( $raw_row );
		//if ( $raw_row['user_id'] == FALSE ) {
		//	unset($raw_row['user_id']);
		//}

		//If its a salary type, make sure average weekly time is always specified and hourly rate.
		return $raw_row;
	}

	function _import( $validate_only ) {
		return $this->getObject()->setPayPeriod( $this->getParsedData(), $validate_only );
	}

	//
	// Generic parser functions.
	//
	function getPayPeriodScheduleOptions() {
		//Get job titles
		$ppslf = TTNew('PayPeriodScheduleListFactory');
		$ppslf->getByCompanyId( $this->company_id );
		$this->pay_period_schedule_options = (array)$ppslf->getArrayByListFactory( $ppslf, FALSE, TRUE );
		unset($ppslf);

		return TRUE;
	}
	function parse_pay_period_schedule( $input, $default_value = NULL, $parse_hint = NULL ) {
		if ( !is_array( $this->pay_period_schedule_options ) ) {
			$this->getPayPeriodScheduleOptions();
		}

		if ( trim($input) == '' AND count($this->pay_period_schedule_options) == 1 ) {
			return key($this->pay_period_schedule_options); //Use first pay period schedule.
		}

		$retval = $this->findClosestMatch( $input, $this->pay_period_schedule_options );
		if ( $retval === FALSE ) {
			$retval = -1; //Make sure this fails.
		}

		return $retval;
	}


	function parse_start_date( $input, $default_value = NULL, $parse_hint = NULL, $raw_row = NULL ) {
		if ( isset($parse_hint) AND $parse_hint != '' ) {
			TTDate::setDateFormat( $parse_hint );
			return TTDate::parseDateTime( $input );
		} else {
			return TTDate::strtotime( $input );
		}
	}

	function parse_end_date( $input, $default_value = NULL, $parse_hint = NULL, $raw_row = NULL ) {
		if ( isset($parse_hint) AND $parse_hint != '' ) {
			TTDate::setDateFormat( $parse_hint );
			return TTDate::parseDateTime( $input );
		} else {
			return TTDate::strtotime( $input );
		}
	}

	function parse_transaction_date( $input, $default_value = NULL, $parse_hint = NULL, $raw_row = NULL ) {
		if ( isset($parse_hint) AND $parse_hint != '' ) {
			TTDate::setDateFormat( $parse_hint );
			return TTDate::parseDateTime( $input );
		} else {
			return TTDate::strtotime( $input );
		}
	}
}
?>
