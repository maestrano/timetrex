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
 * @package Modules\Install
 */
class InstallSchema_1007A extends InstallSchema_Base {

	function preInstall() {
		Debug::text('preInstall: '. $this->getVersion(), __FILE__, __LINE__, __METHOD__, 9);

		return TRUE;
	}


	function postInstall() {
		Debug::text('postInstall: '. $this->getVersion(), __FILE__, __LINE__, __METHOD__, 9);

		//New Pay Period Schedule format, update any current schedules.
		$ppslf = TTnew( 'PayPeriodScheduleListFactory' );
		$ppslf->getAll();
		Debug::text('Found Pay Period Schedules: '. $ppslf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 9);
		if ( $ppslf->getRecordCount() > 0 ) {
			foreach( $ppslf as $pps_obj ) {
				if ( $pps_obj->getType() == 10 OR $pps_obj->getType() == 20 ) {
					$pps_obj->setStartDayOfWeek( TTDate::getDayOfWeek( TTDate::strtotime( $pps_obj->getColumn('anchor_date') ) ) );
					$pps_obj->setTransactionDate( ( floor( (TTDate::strtotime( $pps_obj->getColumn('primary_transaction_date') ) - TTDate::strtotime( $pps_obj->getColumn('primary_date') ) ) / 86400 ) + 1 ) );
				} elseif (	$pps_obj->getType() == 30 ) {
					$pps_obj->setPrimaryDayOfMonth( ( TTDate::getDayOfMonth( TTDate::strtotime( $pps_obj->getColumn('anchor_date') ) ) + 1 ) );
					if ( $pps_obj->getColumn('primary_transaction_date_ldom') == 1 ) {
						$pps_obj->setPrimaryTransactionDayOfMonth( -1 );
					} else {
						$pps_obj->setPrimaryTransactionDayOfMonth( TTDate::getDayOfMonth( TTDate::strtotime( $pps_obj->getColumn('primary_transaction_date') ) ) );
					}

					$pps_obj->setSecondaryDayOfMonth( TTDate::getDayOfMonth( TTDate::strtotime( $pps_obj->getColumn('primary_date') ) ) );
					if ( $pps_obj->getColumn('secondary_transaction_date_ldom') == 1 ) {
						$pps_obj->setSecondaryTransactionDayOfMonth( -1 );
					} else {
						$pps_obj->setSecondaryTransactionDayOfMonth( TTDate::getDayOfMonth( TTDate::strtotime( $pps_obj->getColumn('secondary_transaction_date') ) ) );
					}
				} elseif ( $pps_obj->getType() == 50 ) {
					$pps_obj->setPrimaryDayOfMonth( TTDate::getDayOfMonth( TTDate::strtotime( $pps_obj->getColumn('anchor_date') ) ) );
					if ( $pps_obj->getColumn('primary_transaction_date_ldom') == 1 ) {
						$pps_obj->setPrimaryTransactionDayOfMonth( -1 );
					} else {
						$pps_obj->setPrimaryTransactionDayOfMonth( TTDate::getDayOfMonth( TTDate::strtotime( $pps_obj->getColumn('primary_transaction_date') ) ) );
					}
				}

				if ( $pps_obj->getColumn('transaction_date_bd') == 1 OR $pps_obj->getColumn('secondary_transaction_date_bd') == 1 ) {
					$pps_obj->setTransactionDateBusinessDay( TRUE );
				}

				if ( $pps_obj->isValid() ) {
					$pps_obj->Save();
				}
			}
		}
		
		return TRUE;

	}
}
?>
