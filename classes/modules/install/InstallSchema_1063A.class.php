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
 * @package Module_Install
 */
class InstallSchema_1063A extends InstallSchema_Base {

	function preInstall() {
		Debug::text('preInstall: '. $this->getVersion(), __FILE__, __LINE__, __METHOD__, 9);

		$plf = new PunchListFactory();
		$punch_control_ids = $plf->db->GetCol( 'SELECT distinct punch_control_id FROM punch WHERE deleted = 0 GROUP BY punch_control_id,status_id HAVING count(*) > 1' );

		if ( is_array($punch_control_ids) ) {
			Debug::text('Duplicate Punch Records: '. count($punch_control_ids), __FILE__, __LINE__, __METHOD__, 9);
			foreach( $punch_control_ids as $punch_control_id ) {
				Debug::text('  Punch Control ID: '. $punch_control_id, __FILE__, __LINE__, __METHOD__, 9);

				//Handle duplicate punch timestamps...
				$plf->getByPunchControlId( $punch_control_id, NULL, array( 'time_stamp' => 'asc') );
				if ( $plf->getRecordCount() > 2 ) {
					Debug::text('    Found Punches: '. $plf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 9);

					//If there are more than two duplicate punches, delete ones with identical timestamps.
					$prev_time_stamp = FALSE;
					$i = 0;
					foreach( $plf as $p_obj ) {
						if ( $prev_time_stamp !== FALSE AND $prev_time_stamp == $p_obj->getTimeStamp() ) {
							Debug::text('    Found Duplicate TimeStamp: '. $p_obj->getTimeStamp() .'('. $p_obj->getID() .') Deleting...', __FILE__, __LINE__, __METHOD__, 9);
							$plf->db->Execute('UPDATE punch SET deleted = 1 WHERE id = '. (int)$p_obj->getID() );
							$i++;
						}

						$prev_time_stamp = $p_obj->getTimeStamp();
					}

					//If there are more than two duplicate punches and no duplicate timestamps, delete ones with duplicate status_ids in timestamp order.
					//Check for duplicate statuses not in a row too, ie: 10, 20, 10.
					if ( $i == 0 ) {
						$prev_status_id = FALSE;
						foreach( $plf as $p_obj ) {
							if ( $prev_status_id !== FALSE AND in_array( $p_obj->getStatus(), $prev_status_id ) ) {
								Debug::text('    Found Duplicate Status: '. $p_obj->getStatus() .'('. $p_obj->getID() .') Deleting...', __FILE__, __LINE__, __METHOD__, 9);
								$plf->db->Execute('UPDATE punch SET deleted = 1 WHERE id = '. (int)$p_obj->getID() );
								$i++;
							}

							$prev_status_id[] = $p_obj->getStatus();
						}
					}
				}

				//Handle punches with the same status_id
				$plf->getByPunchControlId( $punch_control_id, NULL, array( 'time_stamp' => 'asc') );
				if ( $plf->getRecordCount() == 2 ) {
					Debug::text('    Checking Duplicate Status Punches: '. $plf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 9);
					$x = 0;
					foreach( $plf as $p_obj ) {
						if ( $x == 0 AND $p_obj->getStatus() != 10 ) {
							Debug::text('    Found Duplicate IN punches: '. $p_obj->getID() .' Correcting...', __FILE__, __LINE__, __METHOD__, 9);
							$plf->db->Execute('UPDATE punch SET status_id = 10 WHERE id = '. (int)$p_obj->getID() );
						} elseif ( $x == 1 AND $p_obj->getStatus() != 20 ) {
							Debug::text('    Found Duplicate OUT punches: '. $p_obj->getID() .' Correcting...', __FILE__, __LINE__, __METHOD__, 9);
							$plf->db->Execute('UPDATE punch SET status_id = 20 WHERE id = '. (int)$p_obj->getID() );
						}
						$x++;
					}
				}

			}
		}

		Debug::text('preInstall Done: '. $this->getVersion(), __FILE__, __LINE__, __METHOD__, 9);

		return TRUE;
	}

	function postInstall() {
		Debug::text('postInstall: '. $this->getVersion(), __FILE__, __LINE__, __METHOD__, 9);

		$clf = TTNew('CompanyListFactory');
		$clf->getAll();
		if ( $clf->getRecordCount() > 0 ) {
			$x = 0;
			foreach( $clf as $company_obj ) {
				//Go through each permission group, and enable schedule, view_open for for anyone who has schedule, view
				Debug::text('Company: '. $company_obj->getName() .' X: '. $x .' of :'. $clf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 9);

				//Populate currency rate table.
				CurrencyFactory::updateCurrencyRates( $company_obj->getId() );

				$x++;
			}
		}

		return TRUE;
	}
}
?>
