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
 * @package API\Core
 */
class APIPermission extends APIFactory {
	protected $main_class = 'PermissionFactory';

	public function __construct() {
		parent::__construct(); //Make sure parent constructor is always called.

		return TRUE;
	}

	function getUniqueCountry() {
		global $current_company;
		$company_id = (int)$current_company->getId();

		global $current_company;
		$ulf = TTNew('UserListFactory');
		return $ulf->getUniqueCountryByCompanyId( $company_id );
	}

	function getPermissions( $user_id = NULL, $company_id = NULL ) {
		if ( $user_id == NULL OR $user_id == '' ) {
			global $current_user;

			$user_id = $current_user->getId();
		}

		if ( $company_id == NULL OR $company_id == '' ) {
			global $current_company;

			$company_id = $current_company->getId();
		}

		$permission = new Permission();
		return $this->returnHandler( $permission->getPermissions( $user_id, $company_id ) );
	}

	function getSectionBySectionGroup( $section_groups ) {
		if ( !is_array($section_groups) ) {
			$section_groups = array( $section_groups );
		}
		$section_groups = Misc::trimSortPrefix( $section_groups, TRUE );
		//Debug::Arr($section_groups, 'aSection Groups: ', __FILE__, __LINE__, __METHOD__, 10);

		$section_options = Misc::trimSortPrefix( $this->getOptions('section') );
		$section_group_map = Misc::trimSortPrefix( $this->getOptions('section_group_map') );

		if ( in_array( 'all', $section_groups ) ) {
			//Debug::Text('Returning ALL section Groups: ', __FILE__, __LINE__, __METHOD__, 10);
			$section_groups = array_keys( $this->getOptions('section_group') );
			unset($section_groups[0]);
		}

		//Debug::Arr($section_groups, 'bSection Groups: ', __FILE__, __LINE__, __METHOD__, 10);
		foreach( $section_groups as $section_group ) {
			$section_group = Misc::trimSortPrefix( $section_group );
			if ( isset($section_group_map[$section_group]) ) {
				foreach( $section_group_map[$section_group] as $tmp_section ) {
					$retarr[$tmp_section] = $section_options[$tmp_section];
				}
			}
		}
		
		if ( isset($retarr) ) {
			//Debug::Arr($retarr, 'Sections: ', __FILE__, __LINE__, __METHOD__, 10);
			return $this->returnHandler( Misc::trimSortPrefix( $retarr, 1000 ) );
		}

		return FALSE;
	}

	function filterPresetPermissions( $preset, $filter_sections = FALSE, $filter_permissions = FALSE ) {
		$pf = TTNew('PermissionFactory');
		return $this->returnHandler( $pf->filterPresetPermissions( $preset, $filter_sections, $filter_permissions ) );
	}
}
?>
