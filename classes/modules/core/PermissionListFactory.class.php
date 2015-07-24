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
class PermissionListFactory extends PermissionFactory implements IteratorAggregate {
	function getAll($limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		$query = '
					select	*
					from	'. $this->getTable() .'
						WHERE deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, NULL, $limit, $page );

		return $this;
	}

	function getById($id, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		$ph = array(
					'id' => $id,
					);

		$query = '
					select	*
					from	'. $this->getTable() .'
					where	id = ?
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByCompanyId($company_id, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		$ph = array(
					'company_id' => $company_id,
					);

		$pcf = new PermissionControlFactory();

		$query = '
					select	a.*
					from	'. $this->getTable() .' as a,
							'. $pcf->getTable() .' as b
					where	b.id = a.permission_control_id
						AND b.company_id = ?
						AND ( a.deleted = 0 AND b.deleted = 0 )';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByCompanyIdAndPermissionControlId($company_id, $permission_control_id, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $permission_control_id == '') {
			return FALSE;
		}

		$ph = array(
					'company_id' => $company_id,
					'permission_control_id' => $permission_control_id,
					);

		$pcf = new PermissionControlFactory();

		$query = '
					select	a.*
					from	'. $this->getTable() .' as a,
							'. $pcf->getTable() .' as b
					where	b.id = a.permission_control_id
						AND b.company_id = ?
						AND a.permission_control_id = ?
						AND ( a.deleted = 0 AND b.deleted = 0 )';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByCompanyIdAndPermissionControlIdAndSectionAndName($company_id, $permission_control_id, $section, $name, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $permission_control_id == '') {
			return FALSE;
		}

		if ( $section == '') {
			return FALSE;
		}

		if ( $name == '') {
			return FALSE;
		}

		$ph = array(
					'company_id' => $company_id,
					'permission_control_id' => $permission_control_id,
					'section' => $section,
					//'name' => $name, //Allow a list of names.
					);

		$pcf = new PermissionControlFactory();

		$query = '
					select	a.*
					from	'. $this->getTable() .' as a,
							'. $pcf->getTable() .' as b
					where	b.id = a.permission_control_id
						AND b.company_id = ?
						AND a.permission_control_id = ?
						AND a.section = ?
						AND a.name in ('. $this->getListSQL($name, $ph) .')
						AND ( a.deleted = 0 AND b.deleted = 0)';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByCompanyIdAndPermissionControlIdAndSectionAndNameAndValue($company_id, $permission_control_id, $section, $name, $value, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $permission_control_id == '') {
			return FALSE;
		}

		if ( $section == '') {
			return FALSE;
		}

		if ( $name == '') {
			return FALSE;
		}

		$ph = array(
					'company_id' => $company_id,
					'permission_control_id' => $permission_control_id,
					'section' => $section,
					'value' => (int)$value,
					//'name' => $name, //Allow a list of names.
					);

		$pcf = new PermissionControlFactory();

		$query = '
					select	a.*
					from	'. $this->getTable() .' as a,
							'. $pcf->getTable() .' as b
					where	b.id = a.permission_control_id
						AND b.company_id = ?
						AND a.permission_control_id = ?
						AND a.section = ?
						AND a.value = ?
						AND a.name in ('. $this->getListSQL($name, $ph) .')
						AND ( a.deleted = 0 AND b.deleted = 0)';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByCompanyIdAndSectionAndDateAndValidIDs($company_id, $section, $date = NULL, $valid_ids = array(), $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $section == '') {
			return FALSE;
		}

		if ( $date == '') {
			$date = 0;
		}

		$ph = array(
					'company_id' => $company_id,
					);

		$pcf = new PermissionControlFactory();

		$query = '
					select	a.*
					from	'. $this->getTable() .' as a,
							'. $pcf->getTable() .' as b
					where	b.id = a.permission_control_id
						AND b.company_id = ?
						AND (
								(
								a.section in ('. $this->getListSQL($section, $ph) .') ';

		if ( isset($date) AND $date > 0 ) {
			//Append the same date twice for created and updated.
			$ph[] = (int)$date;
			$ph[] = (int)$date;
			$query	.=	'		AND ( a.created_date >= ? OR a.updated_date >= ? ) ) ';
		} else {
			$query	.=	' ) ';
		}

		if ( isset($valid_ids) AND is_array($valid_ids) AND count($valid_ids) > 0 ) {
			$query	.=	' OR a.id in ('. $this->getListSQL($valid_ids, $ph) .') ';
		}

		$query .= '	)
						AND ( a.deleted = 0 AND b.deleted = 0)';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getAllPermissionsByCompanyIdAndUserId($company_id, $user_id) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $user_id == '') {
			return FALSE;
		}

		$ph = array(
					'company_id' => $company_id,
					'user_id' => $user_id,
					);

		$pcf = new PermissionControlFactory();
		$puf = new PermissionUserFactory();

		$query = '
					select	a.*,
							b.level as level
					from	'. $this->getTable() .' as a,
							'. $pcf->getTable() .' as b,
							'. $puf->getTable() .' as c
					where b.id = a.permission_control_id
						AND b.id = c.permission_control_id
						AND b.company_id = ?
						AND	c.user_id = ?
						AND ( a.deleted = 0 AND b.deleted = 0 )
				';

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getIsModifiedByCompanyIdAndDate($company_id, $date, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $date == '') {
			return FALSE;
		}

		$ph = array(
					'company_id' => $company_id,
					'created_date' => $date,
					'updated_date' => $date,
					'deleted_date' => $date,
					);

		$pcf = new PermissionControlFactory();

		//INCLUDE Deleted rows in this query.
		$query = '
					select	a.*
					from	'. $this->getTable() .' as a,
							'. $pcf->getTable() .' as b
					where
							b.company_id = ?
						AND
							( a.created_date >=	 ? OR a.updated_date >= ? OR ( a.deleted = 1 AND a.deleted_date >= ? ) )
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->db->SelectLimit($query, 1, -1, $ph);
		if ( $this->getRecordCount() > 0 ) {
			Debug::text('Rows have been modified: '. $this->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);

			return TRUE;
		}
		Debug::text('Rows have NOT been modified', __FILE__, __LINE__, __METHOD__, 10);
		return FALSE;
	}
}
?>
