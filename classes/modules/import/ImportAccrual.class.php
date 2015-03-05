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
 * @package Modules\Import
 */
class ImportAccrual extends Import {

	public $class_name = 'APIAccrual';

	public $accrual_policy_account_options = FALSE;

	function _getFactoryOptions( $name, $parent = NULL ) {

		$retval = NULL;
		switch( $name ) {
			case 'columns':
				$apf = TTNew('AccrualFactory');
				$retval = Misc::prependArray( $this->getUserIdentificationColumns(), Misc::arrayIntersectByKey( array('accrual_policy_account', 'type', 'amount', 'date_stamp'), Misc::trimSortPrefix( $apf->getOptions('columns') ) ) );

				break;
			case 'column_aliases':
				//Used for converting column names after they have been parsed.
				$retval = array(
								'type' => 'type_id',
								'accrual_policy_account' => 'accrual_policy_account_id',
								);
				break;
			case 'import_options':
				$retval = array(
								'-1010-fuzzy_match' => TTi18n::getText('Enable smart matching.'),
								);
				break;
			case 'parse_hint':
				$upf = TTnew('UserPreferenceFactory');

				$retval = array(
								'date_stamp' => $upf->getOptions('date_format'),
								'amount' => $upf->getOptions('time_unit_format'),
								);
				break;
		}

		return $retval;
	}


	function _preParseRow( $row_number, $raw_row ) {
		$retval = $this->getObject()->stripReturnHandler( $this->getObject()->getAccrualDefaultData() );

		return $retval;
	}

	function _postParseRow( $row_number, $raw_row ) {
		$raw_row['user_id'] = $this->getUserIdByRowData( $raw_row );
		if ( $raw_row['user_id'] == FALSE ) {
			unset($raw_row['user_id']);
		}

		if ( isset($raw_row['date_stamp']) ) {
			$raw_row['time_stamp'] = $raw_row['date_stamp']; //AcrualFactory wants time_stamp column not date_stamp, so convert that here.
		}

		return $raw_row;
	}

	function _import( $validate_only ) {
		return $this->getObject()->setAccrual( $this->getParsedData(), $validate_only );
	}

	//
	// Generic parser functions.
	//
	function getAccrualPolicyAccountOptions() {
		//Get accrual policies
		$aplf = TTNew('AccrualPolicyAccountListFactory');
		$aplf->getByCompanyId( $this->company_id );
		$this->accrual_policy_account_options = (array)$aplf->getArrayByListFactory( $aplf, FALSE, TRUE );
		unset($aplf);

		return TRUE;
	}
	function parse_accrual_policy_account( $input, $default_value = NULL, $parse_hint = NULL ) {
		if ( trim($input) == '' ) {
			return 0; //Default Wage Group
		}

		if ( !is_array( $this->accrual_policy_account_options ) ) {
			$this->getAccrualPolicyAccountOptions();
		}

		$retval = $this->findClosestMatch( $input, $this->accrual_policy_account_options );
		if ( $retval === FALSE ) {
			$retval = -1; //Make sure this fails.
		}

		return $retval;
	}

	function parse_date_stamp( $input, $default_value = NULL, $parse_hint = NULL ) {
		return $this->parse_date( $input, $default_value, $parse_hint );
	}

	function parse_type( $input, $default_value = NULL, $parse_hint = NULL ) {
		$af = TTnew('AccrualFactory');
		$options = $af->getOptions( 'user_type' );

		if ( isset($options[$input]) ) {
			return $input;
		} else {
			if ( $this->getImportOptions('fuzzy_match') == TRUE ) {
				return $this->findClosestMatch( $input, $options, 50 );
			} else {
				return array_search( strtolower($input), array_map('strtolower', $options) );
			}
		}
	}

	function parse_amount( $input, $default_value = NULL, $parse_hint = NULL, $raw_row = NULL ) {
		$val = new Validator();

		TTDate::setTimeUnitFormat( $parse_hint );

		$retval = TTDate::parseTimeUnit( $val->stripNonTimeUnit($input) );

		return $retval;
	}
}
?>
