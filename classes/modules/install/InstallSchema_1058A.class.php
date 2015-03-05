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
class InstallSchema_1058A extends InstallSchema_Base {

	function preInstall() {
		Debug::text('preInstall: '. $this->getVersion(), __FILE__, __LINE__, __METHOD__, 9);

		return TRUE;
	}

	function postInstall() {
		global $config_vars;

		Debug::text('postInstall: '. $this->getVersion(), __FILE__, __LINE__, __METHOD__, 9);

		//Update permission for new modules.
		$clf = TTnew( 'CompanyListFactory' );
		$clf->getAll();
		if ( $clf->getRecordCount() > 0 ) {
			$i = 0;
			foreach( $clf as $c_obj ) {
				if ( $c_obj->getStatus() != 30 ) {
					Debug::text( $i.'/'. $clf->getRecordCount() .'. Company: '. $c_obj->getName(), __FILE__, __LINE__, __METHOD__, 9);

					//Disable detailed audit logging while updating permissions, as this can create millions of records and slow the upgrade down substantially.
					$config_vars['other']['disable_audit_log_detail'] = TRUE;
					$config_vars['other']['disable_audit_log'] = TRUE;
					
					$pclf = TTnew( 'PermissionControlListFactory' );
					// Update all the HR related permissions for the Standard/Business Edition (KPIs, reviews, qualifications, etc..., but *not* job vacancy or job applicants)
					$pclf->getByCompanyID( $c_obj->getID(), NULL, NULL, NULL, array( 'id' => 'asc' ) );
					if ( $pclf->getRecordCount() > 0 ) {
						$pf = TTnew( 'PermissionFactory' );
						$preset_options = $pf->getOptions('preset');
						$preset_level_options = $pf->getOptions('preset_level');
						ksort( $preset_options );
						foreach( $pclf as $pc_obj ) {
							$level = $pc_obj->getLevel(); // 1, 10, 12, 15, 20, 25
							if ( $level >= 10 ) { //Only process levels 10 and higher, as those are supervisors and only ones that need adjusting.
								$old_permission_arr = $pc_obj->getPermission();
								if ( is_array($old_permission_arr) ) {
									foreach( $preset_options as $preset => $preset_name	) {
										// preset: 10, 18, 20, 30, 40
										if ( $level == $preset_level_options[$preset] ) {
											//This automatically includes system presets (user, reports, etc...) so it can override customers custom permissions
											$permission_arr = $pf->getPresetPermissions( $preset, array(70, 75, 80) ); //Module: Human Resources.
											//Debug::Arr( $permission_arr, ' New Permissions: ', __FILE__, __LINE__, __METHOD__, 10);
											$pc_obj->setPermission($permission_arr, $old_permission_arr);
										}
									}
								}
							}
						}
					}
					unset($pclf, $pf, $preset_options, $preset_level_options, $old_permission_arr, $level, $pc_obj );

					//Re-enable audit logging after permissions were updated.
					$config_vars['other']['disable_audit_log_detail'] = FALSE;
					$config_vars['other']['disable_audit_log'] = FALSE;
					
					//Delete duplicate OPEN shifts before todays date.
					$sf = new ScheduleFactory();
					$udf = new UserDateFactory();
					$ph = array(
											'id' => $this->db->BindDate( time() ),
										);
					$query = 'update '. $sf->getTable() .' set deleted = 1 where user_date_id in ( select id from '. $udf->getTable() .' where user_id = 0 and date_stamp <= ? and deleted = 0 ) and created_by is NULL AND deleted = 0';
					$this->db->Execute( $query, $ph );
					unset($query, $ph);


					//Assign all absence policies to every policy group.
					$aplf = TTnew('AbsencePolicyListFactory');
					$aplf->getByCompanyId( $c_obj->getId() );
					if ( $aplf->getRecordCount() > 0 ) {
						foreach( $aplf as $ap_obj ) {
							$ap_ids[] = $ap_obj->getId();
						}
					}
					$pglf = TTnew('PolicyGroupListFactory');
					$pglf->getByCompanyId( $c_obj->getId() );
					if ( $pglf->getRecordCount() > 0 ) {
						foreach( $pglf as $pg_obj ) {
							if ( isset( $ap_ids ) ) {
								$pg_obj->setAbsencePolicy($ap_ids);
								if ( $pg_obj->isValid() ) {
									$pg_obj->Save();
								}
							}
						}
					}
					unset( $aplf, $pglf, $ap_obj, $pg_obj, $ap_ids );
				}

				$i++;
			}
		}

		return TRUE;
	}
}
?>
