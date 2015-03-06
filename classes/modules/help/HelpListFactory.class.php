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
 * @package Modules\Help
 */
class HelpListFactory extends HelpFactory implements IteratorAggregate {

	function getAll($limit = NULL, $page = NULL, $where = NULL, $order = NULL) {

		$strict_order = TRUE;
		if ( $order == NULL ) {
			$order = array('created_date' => 'desc');
			//$strict_order = FALSE;
		}

		$query = '
					select	*
					from	'. $this->getTable() .'
					WHERE deleted=0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict_order );

		$this->ExecuteSQL( $query, NULL, $limit, $page );

		return $this;
	}

	function getById($id, $where = NULL, $order = NULL) {
		if ( $id == '' ) {
			return FALSE;
		}

		$ph = array(
					'id' => $id,
					);

		$query = '
					select	*
					from	'. $this->getTable() .'
					where	id = ?
						AND deleted=0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByScriptNameAndGroupName($script_name, $group_name = NULL, $where = NULL, $order = NULL) {
		if ( $script_name == '' AND $group_name == '' ) {
			return FALSE;
		}

		$hgcf = new HelpGroupControlFactory();
		$hg = new HelpGroupFactory();

		$ph = array(
					'script_name' => $script_name,
					'group_name' => $group_name,
					);

		$query = '
					select	a.*
					from	'. $this->getTable() .' as a,
							'. $hgcf->getTable() .' as b,
							'. $hg->getTable() .' as c
					where	b.id = c.help_group_control_id
							AND c.help_id = a.id
						';
		//if ( $script_name != '' ) {
			$query .= ' AND b.script_name = ?';
		//}

		//if ( $group_name != '') {
			$query .= ' AND b.name = ?';
		//}

		$query .= ' AND a.deleted=0
					AND b.deleted=0
					ORDER BY c.order_value asc';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByScriptNameAndType($script_name, $type, $where = NULL, $order = NULL) {
		if ( $script_name == '') {
			return FALSE;
		}

		if ( $type == '') {
			return FALSE;
		}

		$type_key = Option::getByValue($type, $this->getOptions('type') );
		if ($type_key !== FALSE) {
			$type = $type_key;
		}

		$hgcf = new HelpGroupControlFactory();
		$hg = new HelpGroupFactory();

		$ph = array(
					'script_name' => $script_name,
					'type_id' => $type,
					);

		$query = '
					select	a.*, b.name as group_name
					from	'. $this->getTable() .' as a,
							'. $hgcf->getTable() .' as b,
							'. $hg->getTable() .' as c
					where	b.id = c.help_group_control_id
							AND c.help_id = a.id
							AND b.script_name = ?
							AND a.type_id = ?
							AND a.deleted=0
							AND b.deleted=0
					ORDER BY c.order_value asc
						';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByScriptNameAndTypeAndStatus($script_name, $type, $status, $where = NULL, $order = NULL) {
		if ( $script_name == '') {
			return FALSE;
		}

		if ( $type == '') {
			return FALSE;
		}

		if ( $status == '') {
			return FALSE;
		}

		$type_key = Option::getByValue($type, $this->getOptions('type') );
		if ($type_key !== FALSE) {
			$type = $type_key;
		}

		$hgcf = new HelpGroupControlFactory();
		$hg = new HelpGroupFactory();

		$ph = array(
					'script_name' => $script_name,
					'type_id' => $type,
					'status_id' => $status,
					);

		$query = '
					select	a.*, b.name as group_name
					from	'. $this->getTable() .' as a,
							'. $hgcf->getTable() .' as b,
							'. $hg->getTable() .' as c
					where	b.id = c.help_group_control_id
							AND c.help_id = a.id
							AND b.script_name = ?
							AND a.type_id = ?
							AND a.status_id = ?
							AND a.deleted=0
							AND b.deleted=0
					ORDER BY c.order_value asc
						';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByScriptNameAndStatus($script_name, $status, $where = NULL, $order = NULL) {
		if ( $script_name == '') {
			return FALSE;
		}

		if ( $status == '') {
			return FALSE;
		}

		$status_key = Option::getByValue($status, $this->getOptions('status') );
		if ($status_key !== FALSE) {
			$status = $status_key;
		}

		$hgcf = new HelpGroupControlFactory();
		$hg = new HelpGroupFactory();

		$ph = array(
					'script_name' => $script_name,
					'status_id' => $status,
					);

		$query = '
					select	a.*, b.name as group_name
					from	'. $this->getTable() .' as a,
							'. $hgcf->getTable() .' as b,
							'. $hg->getTable() .' as c
					where	b.id = c.help_group_control_id
							AND c.help_id = a.id
							AND b.script_name = ?
							AND a.status_id = ?
							AND a.deleted=0
							AND b.deleted=0
					ORDER BY a.type_id desc, c.order_value asc
						';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getAllArray() {
		$hlf = new HelpListFactory();
		$hlf->getAll();

		$help_list[0] = '--';

		foreach ($hlf as $help) {
			$help_list[$help->getID()] = '('. $help->getID() .') ['. Option::getByKey($help->getType(), $help->getOptions('type') ) .'] '. $help->getHeading();
		}

		return $help_list;
	}
}
?>
