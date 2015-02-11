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
 * @package Modules\Users
 */
class UserContactListFactory extends UserContactFactory implements IteratorAggregate {

	function getAll($limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $order == NULL ) {
			$order = array();
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$query = '
					select	*
					from	'. $this->getTable() .'
					WHERE deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->ExecuteSQL($query, NULL, $limit, $page);

		return $this;
	}

	function getByStatus($status, $where = NULL, $order = NULL) {
		$key = Option::getByValue($status, $this->getOptions('status') );
		if ($key !== FALSE) {
			$status = $key;
		}

		$ph = array(
					'status_id' => $status,
					);

		$query = '
					select	*
					from	'. $this->getTable() .'
					where
						status_id = ?
						AND deleted = 0';

		$this->ExecuteSQL($query, $ph);

		return $this;
	}

	function getByCompanyIdAndStatus($company_id, $status, $where = NULL, $order = NULL) {
		$key = Option::getByValue($status, $this->getOptions('status') );
		if ($key !== FALSE) {
			$status = $key;
		}

		$uf = TTnew( 'UserFactory' );

		$ph = array(
					'company_id' => $company_id,
					'status_id' => $status,
					);

		$query = '
					select	a.*
					from	'. $this->getTable() .' as a
					LEFT JOIN '. $uf->getTable() .' as u ON ( u.id = a.user_id AND u.deleted = 0 )
					where
						u.company_id = ?
						AND a.status_id = ?
						AND a.deleted = 0';

		$this->ExecuteSQL($query, $ph);

		return $this;
	}

	static function getFullNameById( $id ) {
		if ( $id == '') {
			return FALSE;
		}

		$ulf = new UserListFactory();
		$ulf = $ulf->getById( $id );
		if ( $ulf->getRecordCount() > 0 ) {
			$u_obj = $ulf->getCurrent();
			return $u_obj->getFullName();
		}

		return FALSE;
	}

	function getById($id) {
		if ( $id == '') {
			return FALSE;
		}

		$this->rs = $this->getCache($id);
		if ( $this->rs === FALSE ) {
			$ph = array(
						'id' => $id,
						);

			$query = '
						select	*
						from	'. $this->getTable() .'
						where	id = ?
							AND deleted = 0';

			$this->ExecuteSQL($query, $ph);

			$this->saveCache($this->rs, $id);
		}

		return $this;
	}

	function getByIdAndCompanyId($id, $company_id, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		if ( $company_id == '') {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'status_id' => 'asc', 'last_name' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$uf	 = TTnew('UserFactory');
		$ph = array(
					'company_id' => $company_id,
					);

		$query = '
					select	a.*
					from	'. $this->getTable() .' as a
					LEFT JOIN '. $uf->getTable() .' as u ON	 ( u.id = a.user_id AND u.deleted = 0 )
					where	u.company_id = ?
						AND	a.id in ('. $this->getListSQL($id, $ph) .')
						AND a.deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->ExecuteSQL($query, $ph, $limit, $page);

		$this->ExecuteSQL($query, $ph);

		return $this;
	}



	function getByHomeEmailOrWorkEmail( $email ) {
		$email = trim(strtolower($email));

		if ( $email == '') {
			return FALSE;
		}

		if ( $this->Validator->isEmail('email', $email ) == FALSE ) {
			return FALSE;
		}

		$ph = array(
					'home_email' => $email,
					'work_email' => $email,
					);

		$query = '
					select	*
					from	'. $this->getTable() .'
					where
						( lower(home_email) = ?
							OR lower(work_email) = ? )
						AND deleted = 0';

		$this->ExecuteSQL($query, $ph);

		return $this;
	}

	function getByIdAndStatus($id, $status, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		$key = Option::getByValue($status, $this->getOptions('status') );
		if ($key !== FALSE) {
			$status = $key;
		}

		$ph = array(
					'id' => $id,
					'status' => $status,
					);

		$query = '
					select	*
					from	'. $this->getTable() .'
					where	id = ?
						AND status_id = ?
						AND deleted = 0';

		$this->ExecuteSQL($query, $ph);

		return $this;
	}

	function getByCompanyId($company_id, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'status_id' => 'asc', 'last_name' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$uf = TTnew('UserFactory');
		$ph = array(
					'company_id' => $company_id,
					);

		$query = '
					select	a.*
					from	'. $this->getTable() .' as a
					LEFT JOIN '. $uf->getTable() .' as u ON ( u.id = a.user_id AND u.deleted = 0  )
					where	u.company_id = ?
						AND a.deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->ExecuteSQL($query, $ph, $limit, $page);

		return $this;
	}


	function getByCompanyIdArray($company_id, $include_blank = TRUE, $include_disabled = TRUE, $last_name_first = TRUE ) {

		$uclf = new UserContactListFactory();
		$uclf->getByCompanyId($company_id);

		if ( $include_blank == TRUE ) {
			$user_list[0] = '--';
		}

		foreach ($uclf as $user) {
			if ( $user->getStatus() > 10 ) { //ENABLE
				$status = '('.Option::getByKey( $user->getStatus(), $user->getOptions('status') ).') ';
			} else {
				$status = NULL;
			}

			if ( $include_disabled == TRUE OR ( $include_disabled == FALSE AND $user->getStatus() == 10 ) ) {
				$user_list[$user->getID()] = $status.$user->getFullName($last_name_first);
			}
		}

		if ( isset($user_list) ) {
			return $user_list;
		}

		return FALSE;
	}

	function getArrayByListFactory($lf, $include_blank = TRUE, $include_disabled = TRUE ) {
		if ( !is_object($lf) ) {
			return FALSE;
		}

		if ( $include_blank == TRUE ) {
			$list[0] = '--';
		}
		foreach ($lf as $obj) {
			if ( !isset($status_options) ) {
				$status_options = $obj->getOptions('status');
			}

			if ( $obj->getStatus() > 10 ) { //ENABLE
				$status = '('.Option::getByKey( $obj->getStatus(), $status_options ).') ';
			} else {
				$status = NULL;
			}

			if ( $include_disabled == TRUE OR ( $include_disabled == FALSE AND $obj->getStatus() == 10 ) ) {
				$list[$obj->getID()] = $status.$obj->getFullName(TRUE);
			}
		}

		if ( isset($list) ) {
			return $list;
		}

		return FALSE;
	}

	function getDeletedByCompanyIdAndDate($company_id, $date, $limit = NULL, $page = NULL, $where = NULL, $order = NULL ) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $date == '') {
			return FALSE;
		}
		$uf = TTnew('UserFactory');
		$ph = array(
					'company_id' => $company_id,
					'created_date' => $date,
					'updated_date' => $date,
					'deleted_date' => $date,
					);

		//INCLUDE Deleted rows in this query.
		$query = '
					select	a.*
					from	'. $this->getTable() .' as	a
					LEFT JOIN '. $uf->getTable() .' as	u ON ( u.id = a.user_id AND u.deleted = 0 )
					where
							u.company_id = ?
						AND
							( a.created_date >= ? OR a.updated_date >= ? OR a.deleted_date >= ? )
						AND a.deleted = 1
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL($query, $ph, $limit, $page);

		return $this;
	}

	function getAPISearchByCompanyIdAndArrayCriteria( $company_id, $filter_data, $limit = NULL, $page = NULL, $where = NULL, $order = NULL ) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( !is_array($order) ) {
			//Use Filter Data ordering if its set.
			if ( isset($filter_data['sort_column']) AND $filter_data['sort_order']) {
				$order = array(Misc::trimSortPrefix($filter_data['sort_column']) => $filter_data['sort_order']);
			}
		}

		$additional_order_fields = array('employee_first_name', 'employee_last_name', 'title', 'user_group', 'default_branch', 'default_department', 'type_id', 'sex_id', 'status_id');

		$sort_column_aliases = array(
									'type' => 'type_id',
									'status' => 'status_id',
									'sex' => 'sex_id',
									);

		$order = $this->getColumnsFromAliases( $order, $sort_column_aliases );

		if ( $order == NULL ) {
			$order = array( 'employee_first_name' => 'asc', 'employee_last_name' => 'asc', 'last_name' => 'asc', 'first_name' => 'asc');
			$strict = FALSE;
		} else {
			
			if ( !isset($order['last_name']) ) {
				$order['last_name'] = 'asc';
			}
			if ( !isset($order['first_name']) ) {
				$order['first_name'] = 'asc';
			}

			$strict = TRUE;
		}
		
		//Debug::Arr($order, 'Order Data:', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($filter_data, 'Filter Data:', __FILE__, __LINE__, __METHOD__, 10);

		$uf = TTnew( 'UserFactory' );
		$bf = new BranchFactory();
		$df = new DepartmentFactory();
		$ugf = new UserGroupFactory();
		$utf = new UserTitleFactory();

		$ph = array(
					'company_id' => $company_id,
					);

		$query = '
					select
							a.*,
							u.first_name as employee_first_name,
							u.last_name as employee_last_name,

							bf.id as default_branch_id,
							bf.name as default_branch,
							df.id as default_department_id,
							df.name as default_department,
							ugf.id as group_id,
							ugf.name as user_group,
							utf.id as title_id,
							utf.name as title,

							y.first_name as created_by_first_name,
							y.middle_name as created_by_middle_name,
							y.last_name as created_by_last_name,
							z.first_name as updated_by_first_name,
							z.middle_name as updated_by_middle_name,
							z.last_name as updated_by_last_name
					from	'. $this->getTable() .' as a
						LEFT JOIN '. $uf->getTable() .' as u ON ( u.id = a.user_id AND u.deleted = 0 )
						LEFT JOIN '. $bf->getTable() .' as bf ON ( u.default_branch_id = bf.id AND bf.deleted = 0)
						LEFT JOIN '. $df->getTable() .' as df ON ( u.default_department_id = df.id AND df.deleted = 0)
						LEFT JOIN '. $ugf->getTable() .' as ugf ON ( u.group_id = ugf.id AND ugf.deleted = 0 )
						LEFT JOIN '. $utf->getTable() .' as utf ON ( u.title_id = utf.id AND utf.deleted = 0 )
						LEFT JOIN '. $uf->getTable() .' as y ON ( a.created_by = y.id AND y.deleted = 0 )
						LEFT JOIN '. $uf->getTable() .' as z ON ( a.updated_by = z.id AND z.deleted = 0 )
						where u.company_id = ?
						';

		$query .= ( isset($filter_data['permission_children_ids']) ) ? $this->getWhereClauseSQL( 'a.user_id', $filter_data['permission_children_ids'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['id']) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['user_id']) ) ? $this->getWhereClauseSQL( 'a.user_id', $filter_data['user_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['exclude_id']) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['exclude_id'], 'not_numeric_list', $ph ) : NULL;

		if ( isset($filter_data['status']) AND !is_array($filter_data['status']) AND trim($filter_data['status']) != '' AND !isset($filter_data['status_id']) ) {
			$filter_data['status_id'] = Option::getByFuzzyValue( $filter_data['status'], $this->getOptions('status') );
		}
		$query .= ( isset($filter_data['status_id']) ) ? $this->getWhereClauseSQL( 'a.status_id', $filter_data['status_id'], 'numeric_list', $ph ) : NULL;

		if ( isset($filter_data['type']) AND !is_array($filter_data['type']) AND trim($filter_data['type']) != '' AND !isset($filter_data['status_id']) ) {
			$filter_data['type_id'] = Option::getByFuzzyValue( $filter_data['type'], $this->getOptions('type') );
		}
		$query .= ( isset($filter_data['type_id']) ) ? $this->getWhereClauseSQL( 'a.type_id', $filter_data['type_id'], 'numeric_list', $ph ) : NULL;

		if ( isset($filter_data['sex']) AND !is_array($filter_data['sex']) AND trim($filter_data['sex']) != '' AND !isset($filter_data['sex_id']) ) {
			$filter_data['sex_id'] = Option::getByFuzzyValue( $filter_data['sex'], $this->getOptions('sex') );
		}
		$query .= ( isset($filter_data['sex_id']) ) ?$this->getWhereClauseSQL( 'a.sex_id', $filter_data['sex_id'], 'numeric_list', $ph ) : NULL;

		$query .= ( isset($filter_data['first_name']) ) ? $this->getWhereClauseSQL( 'a.first_name', $filter_data['first_name'], 'text', $ph ) : NULL;
		$query .= ( isset($filter_data['middle_name']) ) ? $this->getWhereClauseSQL( 'a.middle_name', $filter_data['middle_name'], 'text', $ph ) : NULL;
		$query .= ( isset($filter_data['last_name']) ) ? $this->getWhereClauseSQL( 'a.last_name', $filter_data['last_name'], 'text', $ph ) : NULL;
		$query .= ( isset($filter_data['home_phone']) ) ? $this->getWhereClauseSQL( 'a.home_phone', $filter_data['home_phone'], 'phone', $ph ) : NULL;
		$query .= ( isset($filter_data['work_phone']) ) ? $this->getWhereClauseSQL( 'a.work_phone', $filter_data['work_phone'], 'phone', $ph ) : NULL;
		$query .= ( isset($filter_data['country']) ) ?$this->getWhereClauseSQL( 'a.country', $filter_data['country'], 'upper_text_list', $ph ) : NULL;
		$query .= ( isset($filter_data['province']) ) ? $this->getWhereClauseSQL( 'a.province', $filter_data['province'], 'upper_text_list', $ph ) : NULL;
		$query .= ( isset($filter_data['city']) ) ? $this->getWhereClauseSQL( 'a.city', $filter_data['city'], 'text', $ph ) : NULL;
		$query .= ( isset($filter_data['address1']) ) ? $this->getWhereClauseSQL( 'a.address1', $filter_data['address1'], 'text', $ph ) : NULL;
		$query .= ( isset($filter_data['address2']) ) ? $this->getWhereClauseSQL( 'a.address2', $filter_data['address2'], 'text', $ph ) : NULL;
		$query .= ( isset($filter_data['postal_code']) ) ? $this->getWhereClauseSQL( 'a.postal_code', $filter_data['postal_code'], 'text', $ph ) : NULL;
		$query .= ( isset($filter_data['sin']) ) ? $this->getWhereClauseSQL( 'a.sin', $filter_data['sin'], 'numeric', $ph ) : NULL;

		$query .= ( isset($filter_data['work_email']) ) ? $this->getWhereClauseSQL( 'a.work_email', $filter_data['work_email'], 'text', $ph ) : NULL;
		$query .= ( isset($filter_data['home_email']) ) ? $this->getWhereClauseSQL( 'a.home_email', $filter_data['home_email'], 'text', $ph ) : NULL;

		$query .= ( isset($filter_data['tag']) ) ? $this->getWhereClauseSQL( 'a.id', array( 'company_id' => $company_id, 'object_type_id' => 230, 'tag' => $filter_data['tag'] ), 'tag', $ph ) : NULL;

		if ( isset($filter_data['created_date']) AND !is_array($filter_data['created_date']) AND trim($filter_data['created_date']) != '' ) {
			$date_filter = $this->getDateRangeSQL( $filter_data['created_date'], 'a.created_date' );
			if ( $date_filter != FALSE ) {
				$query	.=	' AND '. $date_filter;
			}
			unset($date_filter);
		}
		if ( isset($filter_data['updated_date']) AND !is_array($filter_data['updated_date']) AND trim($filter_data['updated_date']) != '' ) {
			$date_filter = $this->getDateRangeSQL( $filter_data['updated_date'], 'a.updated_date' );
			if ( $date_filter != FALSE ) {
				$query	.=	' AND '. $date_filter;
			}
			unset($date_filter);
		}

		$query .= ( isset($filter_data['created_by']) ) ? $this->getWhereClauseSQL( array('a.created_by', 'y.first_name', 'y.last_name'), $filter_data['created_by'], 'user_id_or_name', $ph ) : NULL;
		$query .= ( isset($filter_data['updated_by']) ) ? $this->getWhereClauseSQL( array('a.updated_by', 'z.first_name', 'z.last_name'), $filter_data['updated_by'], 'user_id_or_name', $ph ) : NULL;

		$query .=	' AND ( a.deleted = 0 ) ';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );

		$this->ExecuteSQL($query, $ph, $limit, $page);

		return $this;
	}

}
?>
