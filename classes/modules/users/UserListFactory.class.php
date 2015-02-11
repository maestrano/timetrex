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
class UserListFactory extends UserFactory implements IteratorAggregate {

	function getAll($limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $order == NULL ) {
			$order = array( 'company_id' => 'asc', 'status_id' => '= 10 desc', 'last_name' => 'asc' );
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

		$this->ExecuteSQL( $query, NULL, $limit, $page );

		return $this;
	}

	function getByStatus($status, $where = NULL, $order = NULL) {
		//$key = Option::getByValue($status, $this->getOptions('status') );
		//if ($key !== FALSE) {
		//	$status = $key;
		//}

		$ph = array(
					'status_id' => $status,
					);

		$query = '
					select	*
					from	'. $this->getTable() .'
					where
						status_id = ?
						AND deleted = 0';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getUniqueCountryByCompanyId($id, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		$uf = new UserFactory();

		$ph = array(
					'id' => $id,
					);

		$query = '
					select	distinct a.country
					from	'. $uf->getTable() .' as a
					where	a.company_id = ?
						AND ( a.deleted = 0 )';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		return $this->db->GetCol( $query, $ph );
	}

	function getByCompanyIdAndStatus($company_id, $status, $where = NULL, $order = NULL) {
		//$key = Option::getByValue($status, $this->getOptions('status') );
		//if ($key !== FALSE) {
		//	$status = $key;
		//}

		$ph = array(
					'company_id' => $company_id,
					'status_id' => $status,
					);

		$query = '
					select	*
					from	'. $this->getTable() .'
					where
						company_id = ?
						AND status_id = ?
						AND deleted = 0';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

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

			$this->ExecuteSQL( $query, $ph );

			$this->saveCache($this->rs, $id);
		}

		return $this;
	}

	function getByIdAndCompanyId( $id, $company_id, $limit = NULL, $page = NULL, $where = NULL, $order = NULL ) {
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

		$ph = array(
					'company_id' => $company_id,
					);

		$query = '
					select	*
					from	'. $this->getTable() .'
					where	company_id = ?
						AND	id in ('. $this->getListSQL($id, $ph) .')
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		//This supports a list of IDs, so we need to make sure paging is also available.
		$this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	//Security measure, only returns user_ids that are valid for the specific company.
	function getCompanyValidUserIds( $id, $company_id ) {
		if ( $id == '') {
			return FALSE;
		}

		if ( $company_id == '') {
			return FALSE;
		}

		$ph = array(
					'company_id' => $company_id,
					);

		$query = '
					select	id
					from	'. $this->getTable() .'
					where company_id = ?
						AND id in ('. $this->getListSQL($id, $ph) .')
						AND deleted = 0';

		//This supports a list of IDs, so we need to make sure paging is also available.
		return $this->db->GetCol( $query, $ph );
	}

	function getByUserName($user_name, $where = NULL, $order = NULL) {
		if ( $user_name == '') {
			return FALSE;
		}

		$ph = array(
					'user_name' => $user_name,
					);

		$query = '
					select	*
					from	'. $this->getTable() .'
					where	user_name = ?
						AND deleted = 0';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

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

		$cf = new CompanyFactory();

		$ph = array(
					'home_email' => $email,
					'work_email' => $email,
					);

		//Only return users of active companies and active users, as they are the only ones that can login anyways.
		$query = '
					select	a.*
					from	'. $this->getTable() .' as a
					LEFT JOIN	'. $cf->getTable() .' as cf ON ( a.company_id = cf.id )
					where cf.status_id = 10
						AND a.status_id = 10
						AND ( lower(a.home_email) = ? OR lower(a.work_email) = ? )
						AND a.deleted = 0';

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByEmailIsValidKey( $key ) {
		$key = trim($key);

		if ( $this->Validator->isRegEx('email', $key, NULL, '/^[a-z0-9]{32}$/i' ) == FALSE ) {
			return FALSE;
		}

		$ph = array(
					'key1' => $key,
					'key2' => $key,
					);

		$query = '
					select	*
					from	'. $this->getTable() .'
					where
						( work_email_is_valid_key = ? OR home_email_is_valid_key = ? )
						AND deleted = 0';

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByPasswordResetKey( $key ) {
		$key = trim($key);

		if ( $this->Validator->isRegEx('email', $key, NULL, '/^[a-z0-9]{32}$/i' ) == FALSE ) {
			return FALSE;
		}

		$ph = array(
					'key' => $key,
					);

		$query = '
					select	*
					from	'. $this->getTable() .'
					where
						password_reset_key = ?
						AND deleted = 0';

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByUserNameAndCompanyId($user_name, $company_id, $where = NULL, $order = NULL) {
		if ( $user_name == '') {
			return FALSE;
		}

		if ( $company_id == '') {
			return FALSE;
		}

		$ph = array(
					'company_id' => $company_id,
					'user_name' => $user_name,
					);

		$query = '
					select	*
					from	'. $this->getTable() .'
					where	company_id = ?
						AND user_name = ?
						AND deleted = 0';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByUserNameAndStatus($user_name, $status, $where = NULL, $order = NULL) {
		if ( $user_name == '') {
			return FALSE;
		}

		//$key = Option::getByValue($status, $this->getOptions('status') );
		//if ($key !== FALSE) {
		//	$status = $key;
		//}

		$ph = array(
					'user_name' => strtolower( trim( $user_name ) ),
					'status' => $status,
					);

		$query = '
					select	*
					from	'. $this->getTable() .'
					where	user_name = ?
						AND status_id = ?
						AND deleted = 0';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );
		//Debug::Arr($ph, 'Query: '. $query, __FILE__, __LINE__, __METHOD__, 10);

		return $this;
	}

	function getByPhoneIdAndStatus($phone_id, $status, $where = NULL, $order = NULL) {
		if ( $phone_id == '') {
			return FALSE;
		}

		//$key = Option::getByValue($status, $this->getOptions('status') );
		//if ($key !== FALSE) {
		//	$status = $key;
		//}

		$ph = array(
					'phone_id' => $phone_id,
					'status' => $status,
					);

		$query = '
					select	*
					from	'. $this->getTable() .'
					where	phone_id = ?
						AND status_id = ?
						AND deleted = 0';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByIdAndStatus($id, $status, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		//$key = Option::getByValue($status, $this->getOptions('status') );
		//if ($key !== FALSE) {
		//	$status = $key;
		//}

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

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByCurrencyID($id, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		$ph = array(
					'id' => $id,
					);

		$query = '
					select	*
					from	'. $this->getTable() .'
					where	currency_id = ?
						AND deleted = 0';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByCompanyIDAndPermissionLevel($company_id, $level, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $level == '') {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'a.id' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$pcf = new PermissionControlFactory();
		$puf = new PermissionUserFactory();

		$ph = array(
					'company_id' => $company_id,
					'level' => $level,
					);

		$query = '
					select	a.*
					from	'. $this->getTable() .' as a
					LEFT JOIN
					(
						SELECT g2.*, g1.user_id
						FROM '. $puf->getTable() .' as g1, '. $pcf->getTable() .' as g2
						WHERE ( g1.permission_control_id = g2.id AND g2.deleted = 0)
					) as g ON ( a.id = g.user_id )
					where	a.company_id = ?
						AND g.level = ?
						AND a.deleted = 0';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByCompanyIDAndGroupID($company_id, $id, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $id == '') {
			return FALSE;
		}

		$ph = array(
					'company_id' => $company_id,
					'id' => $id,
					);

		$query = '
					select	*
					from	'. $this->getTable() .'
					where	company_id = ?
						AND group_id = ?
						AND deleted = 0';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByCompanyIDAndEmployeeNumber($company_id, $employee_number, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $employee_number == '') {
			return FALSE;
		}

		$ph = array(
					'company_id' => $company_id,
					'employee_number' => $employee_number,
					);

		$query = '
					select	*
					from	'. $this->getTable() .'
					where	company_id = ?
						AND employee_number = ?
						AND deleted = 0';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getByCompanyIDAndStationIDAndStatusAndDate($company_id, $station_id, $status_id, $date = NULL, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $station_id == '') {
			return FALSE;
		}

		if ( $status_id == '') {
			return FALSE;
		}

		if ( $date == '') {
			$date = 0;
		}

		if ( $order == NULL ) {
			$order = array( 'a.id' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$sf = new StationFactory();
		$sugf = new StationUserGroupFactory();
		$sbf = new StationBranchFactory();
		$sdf = new StationDepartmentFactory();
		$siuf = new StationIncludeUserFactory();
		$seuf = new StationExcludeUserFactory();

		$ph = array(
					'company_id' => $company_id,
					'station_id' => $station_id,
					'status_id' => $status_id,
					//'date' => $date,
					//'date2' => $date,
					);

		$query = '
					select	_ADODB_COUNT
						a.*
						_ADODB_COUNT
					from	'. $this->getTable() .' as a,
							'. $sf->getTable() .' as z
					where	a.company_id = ?
						AND z.id = ?
						AND a.status_id = ?
						AND
							(
								(
									(
										z.user_group_selection_type_id = 10
											OR ( z.user_group_selection_type_id = 20 AND a.group_id in ( select b.group_id from '. $sugf->getTable() .' as b WHERE z.id = b.station_id ) )
											OR ( z.user_group_selection_type_id = 30 AND a.group_id not in ( select b.group_id from '. $sugf->getTable() .' as b WHERE z.id = b.station_id ) )
									)
									AND
									(
										z.branch_selection_type_id = 10
											OR ( z.branch_selection_type_id = 20 AND a.default_branch_id in ( select c.branch_id from '. $sbf->getTable() .' as c WHERE z.id = c.station_id ) )
											OR ( z.branch_selection_type_id = 30 AND a.default_branch_id not in ( select c.branch_id from '. $sbf->getTable() .' as c WHERE z.id = c.station_id ) )
									)
									AND
									(
										z.department_selection_type_id = 10
											OR ( z.department_selection_type_id = 20 AND a.default_department_id in ( select d.department_id from '. $sdf->getTable() .' as d WHERE z.id = d.station_id ) )
											OR ( z.department_selection_type_id = 30 AND a.default_department_id not in ( select d.department_id from '. $sdf->getTable() .' as d WHERE z.id = d.station_id ) )
									)
									AND a.id not in ( select f.user_id from '. $seuf->getTable() .' as f WHERE z.id = f.station_id )
								)
								OR a.id in ( select e.user_id from '. $siuf->getTable() .' as e WHERE z.id = e.station_id )
							)';

		if ( isset($date) AND $date > 0 ) {
			//Append the same date twice for created and updated.
			$ph[] = $date;
			$ph[] = $date;
			$query	.=	' AND ( a.created_date >= ? OR a.updated_date >= ? )';
			unset($date_filter);
		}

		$query .= ' AND ( a.deleted = 0 AND z.deleted = 0 )';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}


	function getByCompanyIDAndStationIDAndStatusAndDateAndValidUserIDs($company_id, $station_id, $status_id, $date = NULL, $valid_user_ids = array(), $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $station_id == '') {
			return FALSE;
		}

		if ( $status_id == '') {
			return FALSE;
		}

		if ( $date == '') {
			$date = 0;
		}

		if ( $order == NULL ) {
			$order = array( 'a.id' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$sf = new StationFactory();
		$sugf = new StationUserGroupFactory();
		$sbf = new StationBranchFactory();
		$sdf = new StationDepartmentFactory();
		$siuf = new StationIncludeUserFactory();
		$seuf = new StationExcludeUserFactory();
		$uif = new UserIdentificationFactory();

		$ph = array(
					'company_id' => $company_id,
					'station_id' => $station_id,
					'status_id' => $status_id,
					//'date' => $date,
					//'date2' => $date,
					);

		//Also include users with user_identifcation rows that have been *created* after the given date
		//so the first supervisor/admin enrolled on a timeclock is properly updated to lock the menu.
		//Make sure we return distinct user rows so there aren't duplicates.
		$query = '
					select	distinct a.*
					from	'. $this->getTable() .' as a

					LEFT JOIN '. $sf->getTable() .' as z ON (1=1)
					LEFT JOIN '. $uif->getTable() .' as uif ON ( a.id = uif.user_id )
					where	a.company_id = ?
						AND z.id = ?
						AND a.status_id = ?
						AND
							(
								(
									(
										(
											(
												z.user_group_selection_type_id = 10
													OR ( z.user_group_selection_type_id = 20 AND a.group_id in ( select b.group_id from '. $sugf->getTable() .' as b WHERE z.id = b.station_id ) )
													OR ( z.user_group_selection_type_id = 30 AND a.group_id not in ( select b.group_id from '. $sugf->getTable() .' as b WHERE z.id = b.station_id ) )
											)
											AND
											(
												z.branch_selection_type_id = 10
													OR ( z.branch_selection_type_id = 20 AND a.default_branch_id in ( select c.branch_id from '. $sbf->getTable() .' as c WHERE z.id = c.station_id ) )
													OR ( z.branch_selection_type_id = 30 AND a.default_branch_id not in ( select c.branch_id from '. $sbf->getTable() .' as c WHERE z.id = c.station_id ) )
											)
											AND
											(
												z.department_selection_type_id = 10
													OR ( z.department_selection_type_id = 20 AND a.default_department_id in ( select d.department_id from '. $sdf->getTable() .' as d WHERE z.id = d.station_id ) )
													OR ( z.department_selection_type_id = 30 AND a.default_department_id not in ( select d.department_id from '. $sdf->getTable() .' as d WHERE z.id = d.station_id ) )
											)
											AND a.id not in ( select f.user_id from '. $seuf->getTable() .' as f WHERE z.id = f.station_id )
										)
										OR a.id in ( select e.user_id from '. $siuf->getTable() .' as e WHERE z.id = e.station_id )
									)

							';

		if ( isset($date) AND $date > 0 ) {
			//Append the same date twice for created and updated.
			$ph[] = (int)$date;
			$ph[] = (int)$date;
			$ph[] = (int)$date;
			$query	.=	'		AND ( a.created_date >= ? OR a.updated_date >= ? OR uif.created_date >= ? )
								)';
		} else {
				$query	.=	'	)';
		}

		if ( isset($valid_user_ids) AND is_array($valid_user_ids) AND count($valid_user_ids) > 0 ) {
			$query	.=	' OR a.id in ('. $this->getListSQL($valid_user_ids, $ph) .') ';
		}

		$query .= '			)
						AND ( a.deleted = 0 AND z.deleted = 0 )';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	function getByCompanyIDAndStationIDAndStatusAndEmployeeNumber($company_id, $station_id, $status_id, $employee_number, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $station_id == '') {
			return FALSE;
		}

		if ( $status_id == '') {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'a.id' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$sf = new StationFactory();
		$sugf = new StationUserGroupFactory();
		$sbf = new StationBranchFactory();
		$sdf = new StationDepartmentFactory();
		$siuf = new StationIncludeUserFactory();
		$seuf = new StationExcludeUserFactory();
		$uif = new UserIdentificationFactory();

		$ph = array(
					'company_id' => $company_id,
					'station_id' => $station_id,
					'status_id' => $status_id,
					'employee_number' => $employee_number,
					//'date' => $date,
					//'date2' => $date,
					);

		//Also include users with user_identifcation rows that have been *created* after the given date
		//so the first supervisor/admin enrolled on a timeclock is properly updated to lock the menu.
		$query = '
					select	_ADODB_COUNT
						a.*
						_ADODB_COUNT
					from	'. $this->getTable() .' as a

					LEFT JOIN '. $sf->getTable() .' as z ON (1=1)
					where	a.company_id = ?
						AND z.id = ?
						AND a.status_id = ?
						AND a.employee_number = ?
						AND
							(
								(
									(
										(
											(
												z.user_group_selection_type_id = 10
													OR ( z.user_group_selection_type_id = 20 AND a.group_id in ( select b.group_id from '. $sugf->getTable() .' as b WHERE z.id = b.station_id ) )
													OR ( z.user_group_selection_type_id = 30 AND a.group_id not in ( select b.group_id from '. $sugf->getTable() .' as b WHERE z.id = b.station_id ) )
											)
											AND
											(
												z.branch_selection_type_id = 10
													OR ( z.branch_selection_type_id = 20 AND a.default_branch_id in ( select c.branch_id from '. $sbf->getTable() .' as c WHERE z.id = c.station_id ) )
													OR ( z.branch_selection_type_id = 30 AND a.default_branch_id not in ( select c.branch_id from '. $sbf->getTable() .' as c WHERE z.id = c.station_id ) )
											)
											AND
											(
												z.department_selection_type_id = 10
													OR ( z.department_selection_type_id = 20 AND a.default_department_id in ( select d.department_id from '. $sdf->getTable() .' as d WHERE z.id = d.station_id ) )
													OR ( z.department_selection_type_id = 30 AND a.default_department_id not in ( select d.department_id from '. $sdf->getTable() .' as d WHERE z.id = d.station_id ) )
											)
											AND a.id not in ( select f.user_id from '. $seuf->getTable() .' as f WHERE z.id = f.station_id )
										)
										OR a.id in ( select e.user_id from '. $siuf->getTable() .' as e WHERE z.id = e.station_id )
									)
								)
							)
							';

		$query .= '	AND ( a.deleted = 0 AND z.deleted = 0 )';

		//Debug::Arr($ph, 'Query: '. $query, __FILE__, __LINE__, __METHOD__, 10);

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->ExecuteSQL( $query, $ph, $limit, $page );

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

		$ph = array(
					'company_id' => $company_id,
					);

		$query = '
					select	*
					from	'. $this->getTable() .'
					where	company_id = ?
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	function getByCompanyIdAndLongitudeAndLatitude($company_id, $longitude, $latitude, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'longitude' => 'asc', 'latitude' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$ph = array(
					'company_id' => $company_id,
					);

		$query = '
					select	*
					from	'. $this->getTable() .'
					where	company_id = ? ';

		//isset() returns false on NULL.
		$query .= $this->getWhereClauseSQL( 'longitude', $longitude, 'numeric', $ph );
		$query .= $this->getWhereClauseSQL( 'latitude', $latitude, 'numeric', $ph );
		$query .= '	AND deleted = 0';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	static function getByCompanyIdArray($company_id, $include_blank = TRUE, $include_disabled = TRUE, $last_name_first = TRUE ) {

		$ulf = new UserListFactory();
		$ulf->getByCompanyId($company_id);

		if ( $include_blank == TRUE ) {
			$user_list[0] = '--';
		}

		foreach ($ulf as $user) {
			if ( $user->getStatus() > 10 ) { //INACTIVE
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

	static function getArrayByListFactory($lf, $include_blank = TRUE, $include_disabled = TRUE ) {
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

			if ( $obj->getStatus() > 10 ) { //INACTIVE
				$status = '('.Option::getByKey( $obj->getStatus(), $status_options ).') ';
				//$status = '(INACTIVE) ';
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

		$ph = array(
					'company_id' => $company_id,
					'created_date' => $date,
					'updated_date' => $date,
					'deleted_date' => $date,
					);

		//INCLUDE Deleted rows in this query.
		$query = '
					select	*
					from	'. $this->getTable() .'
					where
							company_id = ?
						AND
							( created_date >= ? OR updated_date >= ? OR deleted_date >= ? )
						AND deleted = 1
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		if ($limit == NULL) {
			$this->ExecuteSQL( $query, $ph );
		} else {
			$this->rs = $this->db->PageExecute($query, $limit, $page, $ph);

		}

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
					'uif_created_date' => $date,
					);

		$uif = new UserIdentificationFactory();

		//INCLUDE Deleted rows in this query.
		//Also include users with user_identifcation rows that have been *created* after the given date
		//so the first supervisor/admin enrolled on a timeclock is properly updated to lock the menu.
		$query = '
					select	a.*
					from	'. $this->getTable() .' as a
					LEFT JOIN '. $uif->getTable() .' as uif ON ( a.id = uif.user_id )
					where
							a.company_id = ?
						AND
							( a.created_date >= ? OR a.updated_date >= ? OR uif.created_date >= ? )
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->db->SelectLimit($query, 1, -1, $ph);
		if ( $this->getRecordCount() > 0 ) {
			Debug::text('User rows have been modified: '. $this->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);

			return TRUE;
		}

		Debug::text('User rows have NOT been modified', __FILE__, __LINE__, __METHOD__, 10);

		return FALSE;
	}

	function getHighestEmployeeNumberByCompanyId($id, $where = NULL, $order = NULL) {
		if ( $id == '' ) {
			return FALSE;
		}

		$ph = array(
					'id' => $id,
					'id2' => $id,
					);

		//employee_number is a varchar field, so we can't reliably cast it to an integer
		//however if we left pad it, we can get a similar effect.
		//Eventually we can change it to an integer field.
		$query = '
					select	*
					from	'. $this->getTable() .' as a
					where	company_id = ?
						AND id = ( select id
									from '. $this->getTable() .'
									where company_id = ?
										AND employee_number != \'\'
										AND employee_number IS NOT NULL
										AND deleted = 0
									ORDER BY LPAD( employee_number, 10, \'0\' ) DESC
									LIMIT 1
									)
						AND deleted = 0
					LIMIT 1
						';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getSearchByArrayCriteria( $filter_data, $limit = NULL, $page = NULL, $where = NULL, $order = NULL ) {
		if ( !is_array($order) ) {
			//Use Filter Data ordering if its set.
			if ( isset($filter_data['sort_column']) AND $filter_data['sort_order']) {
				$order = array(Misc::trimSortPrefix($filter_data['sort_column']) => $filter_data['sort_order']);
			}
		}

		$additional_order_fields = array('b.name', 'c.name', 'd.name', 'e.name');
		if ( $order == NULL ) {
			$order = array( 'company_id' => 'asc', 'status_id' => 'asc', 'last_name' => 'asc', 'first_name' => 'asc', 'middle_name' => 'asc');
			$strict = FALSE;
		} else {
			//Do order by column conversions, because if we include these columns in the SQL
			//query, they contaminate the data array.
			if ( isset($order['default_branch']) ) {
				$order['b.name'] = $order['default_branch'];
				unset($order['default_branch']);
			}
			if ( isset($order['default_department']) ) {
				$order['c.name'] = $order['default_department'];
				unset($order['default_department']);
			}
			if ( isset($order['user_group']) ) {
				$order['d.name'] = $order['user_group'];
				unset($order['user_group']);
			}
			if ( isset($order['title']) ) {
				$order['e.name'] = $order['title'];
				unset($order['title']);
			}

			//Always try to order by status first so INACTIVE employees go to the bottom.
			if ( !isset($order['status_id']) ) {
				$order = Misc::prependArray( array('status_id' => 'asc'), $order );
			}
			//Always sort by last name, first name after other columns
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

		if ( isset($filter_data['company_ids']) ) {
			$filter_data['company_id'] = $filter_data['company_ids'];
		}

		if ( isset($filter_data['exclude_user_ids']) ) {
			$filter_data['exclude_id'] = $filter_data['exclude_user_ids'];
		}
		if ( isset($filter_data['include_user_ids']) ) {
			$filter_data['id'] = $filter_data['include_user_ids'];
		}
		if ( isset($filter_data['user_status_ids']) ) {
			$filter_data['status_id'] = $filter_data['user_status_ids'];
		}
		if ( isset($filter_data['user_title_ids']) ) {
			$filter_data['title_id'] = $filter_data['user_title_ids'];
		}
		if ( isset($filter_data['group_ids']) ) {
			$filter_data['group_id'] = $filter_data['group_ids'];
		}
		if ( isset($filter_data['branch_ids']) ) {
			$filter_data['default_branch_id'] = $filter_data['branch_ids'];
		}
		if ( isset($filter_data['department_ids']) ) {
			$filter_data['default_department_id'] = $filter_data['department_ids'];
		}
		if ( isset($filter_data['currency_ids']) ) {
			$filter_data['currency_id'] = $filter_data['currency_ids'];
		}

		$bf = new BranchFactory();
		$df = new DepartmentFactory();
		$ugf = new UserGroupFactory();
		$utf = new UserTitleFactory();

		$ph = array();

		$query = '
					select	a.*
					from	'. $this->getTable() .' as a
						LEFT JOIN '. $bf->getTable() .' as b ON a.default_branch_id = b.id
						LEFT JOIN '. $df->getTable() .' as c ON a.default_department_id = c.id
						LEFT JOIN '. $ugf->getTable() .' as d ON a.group_id = d.id
						LEFT JOIN '. $utf->getTable() .' as e ON a.title_id = e.id
					where	1=1
					';

		if ( isset($filter_data['company_id']) AND isset($filter_data['company_id'][0]) AND !in_array(-1, (array)$filter_data['company_id']) ) {
			$query	.=	' AND a.company_id in ('. $this->getListSQL($filter_data['company_id'], $ph) .') ';
		}
		if ( isset($filter_data['permission_children_ids']) AND isset($filter_data['permission_children_ids'][0]) AND !in_array(-1, (array)$filter_data['permission_children_ids']) ) {
			$query	.=	' AND a.id in ('. $this->getListSQL($filter_data['permission_children_ids'], $ph) .') ';
		}
		if ( isset($filter_data['id']) AND isset($filter_data['id'][0]) AND !in_array(-1, (array)$filter_data['id']) ) {
			$query	.=	' AND a.id in ('. $this->getListSQL($filter_data['id'], $ph) .') ';
		}
		if ( isset($filter_data['exclude_id']) AND isset($filter_data['exclude_id'][0]) AND !in_array(-1, (array)$filter_data['exclude_id']) ) {
			$query	.=	' AND a.id not in ('. $this->getListSQL($filter_data['exclude_id'], $ph) .') ';
		}
		if ( isset($filter_data['status_id']) AND isset($filter_data['status_id'][0]) AND !in_array(-1, (array)$filter_data['status_id']) ) {
			$query	.=	' AND a.status_id in ('. $this->getListSQL($filter_data['status_id'], $ph) .') ';
		}
		if ( isset($filter_data['group_id']) AND isset($filter_data['group_id'][0]) AND !in_array(-1, (array)$filter_data['group_id']) ) {
			if ( isset($filter_data['include_subgroups']) AND (bool)$filter_data['include_subgroups'] == TRUE ) {
				$uglf = new UserGroupListFactory();
				$filter_data['group_id'] = $uglf->getByCompanyIdAndGroupIdAndSubGroupsArray( $company_id, $filter_data['group_id'], TRUE);
			}
			$query	.=	' AND a.group_id in ('. $this->getListSQL($filter_data['group_id'], $ph) .') ';
		}
		if ( isset($filter_data['default_branch_id']) AND isset($filter_data['default_branch_id'][0]) AND !in_array(-1, (array)$filter_data['default_branch_id']) ) {
			$query	.=	' AND a.default_branch_id in ('. $this->getListSQL($filter_data['default_branch_id'], $ph) .') ';
		}
		if ( isset($filter_data['default_department_id']) AND isset($filter_data['default_department_id'][0]) AND !in_array(-1, (array)$filter_data['default_department_id']) ) {
			$query	.=	' AND a.default_department_id in ('. $this->getListSQL($filter_data['default_department_id'], $ph) .') ';
		}
		if ( isset($filter_data['title_id']) AND isset($filter_data['title_id'][0]) AND !in_array(-1, (array)$filter_data['title_id']) ) {
			$query	.=	' AND a.title_id in ('. $this->getListSQL($filter_data['title_id'], $ph) .') ';
		}
		if ( isset($filter_data['currency_id']) AND isset($filter_data['currency_id'][0]) AND !in_array(-1, (array)$filter_data['currency_id']) ) {
			$query	.=	' AND a.currency_id in ('. $this->getListSQL($filter_data['currency_id'], $ph) .') ';
		}
		if ( isset($filter_data['sex_id']) AND isset($filter_data['sex_id'][0]) AND !in_array(-1, (array)$filter_data['sex_id']) ) {
			$query	.=	' AND a.sex_id in ('. $this->getListSQL($filter_data['sex_id'], $ph) .') ';
		}
		if ( isset($filter_data['country']) AND isset($filter_data['country'][0]) AND !in_array(-1, (array)$filter_data['country']) ) {
			$query	.=	' AND a.country in ('. $this->getListSQL($filter_data['country'], $ph) .') ';
		}
		if ( isset($filter_data['province']) AND isset($filter_data['province'][0]) AND !in_array( -1, (array)$filter_data['province']) AND !in_array( '00', (array)$filter_data['province']) ) {
			$query	.=	' AND a.province in ('. $this->getListSQL($filter_data['province'], $ph) .') ';
		}
		if ( isset($filter_data['city']) AND !is_array($filter_data['city']) AND trim($filter_data['city']) != '' ) {
			$ph[] = strtolower(trim($filter_data['city']));
			$query	.=	' AND lower(a.city) LIKE ?';
		}
		if ( isset($filter_data['first_name']) AND !is_array($filter_data['first_name']) AND trim($filter_data['first_name']) != '' ) {
			$ph[] = strtolower(trim($filter_data['first_name']));
			$query	.=	' AND lower(a.first_name) LIKE ?';
		}
		if ( isset($filter_data['last_name']) AND !is_array($filter_data['last_name']) AND trim($filter_data['last_name']) != '' ) {
			$ph[] = strtolower(trim($filter_data['last_name']));
			$query	.=	' AND lower(a.last_name) LIKE ?';
		}
		if ( isset($filter_data['home_phone']) AND !is_array($filter_data['home_phone']) AND trim($filter_data['home_phone']) != '' ) {
			$ph[] = trim($filter_data['home_phone']);
			$query	.=	' AND a.home_phone LIKE ?';
		}
		if ( isset($filter_data['employee_number']) AND !is_array($filter_data['employee_number']) AND trim($filter_data['employee_number']) != '' ) {
			$ph[] = trim($filter_data['employee_number']);
			$query	.=	' AND a.employee_number LIKE ?';
		}
		if ( isset($filter_data['user_name']) AND !is_array($filter_data['user_name']) AND trim($filter_data['user_name']) != '' ) {
			$ph[] = strtolower(trim($filter_data['user_name']));
			$query	.=	' AND lower(a.user_name) LIKE ?';
		}
		if ( isset($filter_data['sin']) AND !is_array($filter_data['sin']) AND trim($filter_data['sin']) != '' ) {
			$ph[] = trim($filter_data['sin']);
			$query	.=	' AND a.sin LIKE ?';
		}

		$query .=	'
						AND a.deleted = 0
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );

		$this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	function getSearchByCompanyIdAndArrayCriteria( $company_id, $filter_data, $limit = NULL, $page = NULL, $where = NULL, $order = NULL ) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( !is_array($order) ) {
			//Use Filter Data ordering if its set.
			if ( isset($filter_data['sort_column']) AND $filter_data['sort_order']) {
				$order = array(Misc::trimSortPrefix($filter_data['sort_column']) => $filter_data['sort_order']);
			}
		}

		$additional_order_fields = array('b.name', 'c.name', 'd.name', 'e.name');
		if ( $order == NULL ) {
			$order = array( 'status_id' => 'asc', 'last_name' => 'asc', 'first_name' => 'asc', 'middle_name' => 'asc');
			$strict = FALSE;
		} else {
			//Do order by column conversions, because if we include these columns in the SQL
			//query, they contaminate the data array.
			if ( isset($order['default_branch']) ) {
				$order['b.name'] = $order['default_branch'];
				unset($order['default_branch']);
			}
			if ( isset($order['default_department']) ) {
				$order['c.name'] = $order['default_department'];
				unset($order['default_department']);
			}
			if ( isset($order['user_group']) ) {
				$order['d.name'] = $order['user_group'];
				unset($order['user_group']);
			}
			if ( isset($order['title']) ) {
				$order['e.name'] = $order['title'];
				unset($order['title']);
			}

			//Always try to order by status first so INACTIVE employees go to the bottom.
			if ( !isset($order['status_id']) ) {
				$order = Misc::prependArray( array('status_id' => 'asc'), $order );
			}
			//Always sort by last name, first name after other columns
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

		if ( isset($filter_data['exclude_user_ids']) ) {
			$filter_data['exclude_id'] = $filter_data['exclude_user_ids'];
		}
		if ( isset($filter_data['include_user_ids']) ) {
			$filter_data['id'] = $filter_data['include_user_ids'];
		}
		if ( isset($filter_data['user_status_ids']) ) {
			$filter_data['status_id'] = $filter_data['user_status_ids'];
		}
		if ( isset($filter_data['user_title_ids']) ) {
			$filter_data['title_id'] = $filter_data['user_title_ids'];
		}
		if ( isset($filter_data['group_ids']) ) {
			$filter_data['group_id'] = $filter_data['group_ids'];
		}
		if ( isset($filter_data['branch_ids']) ) {
			$filter_data['default_branch_id'] = $filter_data['branch_ids'];
		}
		if ( isset($filter_data['department_ids']) ) {
			$filter_data['default_department_id'] = $filter_data['department_ids'];
		}
		if ( isset($filter_data['currency_ids']) ) {
			$filter_data['currency_id'] = $filter_data['currency_ids'];
		}

		$bf = new BranchFactory();
		$df = new DepartmentFactory();
		$ugf = new UserGroupFactory();
		$utf = new UserTitleFactory();

		$ph = array(
					'company_id' => $company_id,
					);

		$query = '
					select	a.*
					from	'. $this->getTable() .' as a
						LEFT JOIN '. $bf->getTable() .' as b ON a.default_branch_id = b.id
						LEFT JOIN '. $df->getTable() .' as c ON a.default_department_id = c.id
						LEFT JOIN '. $ugf->getTable() .' as d ON a.group_id = d.id
						LEFT JOIN '. $utf->getTable() .' as e ON a.title_id = e.id
					where	a.company_id = ?
					';

		if ( isset($filter_data['permission_children_ids']) AND isset($filter_data['permission_children_ids'][0]) AND !in_array(-1, (array)$filter_data['permission_children_ids']) ) {
			$query	.=	' AND a.id in ('. $this->getListSQL($filter_data['permission_children_ids'], $ph) .') ';
		}
		if ( isset($filter_data['id']) AND isset($filter_data['id'][0]) AND !in_array(-1, (array)$filter_data['id']) ) {
			$query	.=	' AND a.id in ('. $this->getListSQL($filter_data['id'], $ph) .') ';
		}
		if ( isset($filter_data['exclude_id']) AND isset($filter_data['exclude_id'][0]) AND !in_array(-1, (array)$filter_data['exclude_id']) ) {
			$query	.=	' AND a.id not in ('. $this->getListSQL($filter_data['exclude_id'], $ph) .') ';
		}
		if ( isset($filter_data['status_id']) AND isset($filter_data['status_id'][0]) AND !in_array(-1, (array)$filter_data['status_id']) ) {
			$query	.=	' AND a.status_id in ('. $this->getListSQL($filter_data['status_id'], $ph) .') ';
		}
		if ( isset($filter_data['group_id']) AND isset($filter_data['group_id'][0]) AND !in_array(-1, (array)$filter_data['group_id']) ) {
			if ( isset($filter_data['include_subgroups']) AND (bool)$filter_data['include_subgroups'] == TRUE ) {
				$uglf = new UserGroupListFactory();
				$filter_data['group_id'] = $uglf->getByCompanyIdAndGroupIdAndSubGroupsArray( $company_id, $filter_data['group_id'], TRUE);
			}
			$query	.=	' AND a.group_id in ('. $this->getListSQL($filter_data['group_id'], $ph) .') ';
		}
		if ( isset($filter_data['default_branch_id']) AND isset($filter_data['default_branch_id'][0]) AND !in_array(-1, (array)$filter_data['default_branch_id']) ) {
			$query	.=	' AND a.default_branch_id in ('. $this->getListSQL($filter_data['default_branch_id'], $ph) .') ';
		}
		if ( isset($filter_data['default_department_id']) AND isset($filter_data['default_department_id'][0]) AND !in_array(-1, (array)$filter_data['default_department_id']) ) {
			$query	.=	' AND a.default_department_id in ('. $this->getListSQL($filter_data['default_department_id'], $ph) .') ';
		}
		if ( isset($filter_data['title_id']) AND isset($filter_data['title_id'][0]) AND !in_array(-1, (array)$filter_data['title_id']) ) {
			$query	.=	' AND a.title_id in ('. $this->getListSQL($filter_data['title_id'], $ph) .') ';
		}
		if ( isset($filter_data['currency_id']) AND isset($filter_data['currency_id'][0]) AND !in_array(-1, (array)$filter_data['currency_id']) ) {
			$query	.=	' AND a.currency_id in ('. $this->getListSQL($filter_data['currency_id'], $ph) .') ';
		}
		if ( isset($filter_data['sex_id']) AND isset($filter_data['sex_id'][0]) AND !in_array(-1, (array)$filter_data['sex_id']) ) {
			$query	.=	' AND a.sex_id in ('. $this->getListSQL($filter_data['sex_id'], $ph) .') ';
		}
		if ( isset($filter_data['country']) AND isset($filter_data['country'][0]) AND !in_array(-1, (array)$filter_data['country']) ) {
			$query	.=	' AND a.country in ('. $this->getListSQL($filter_data['country'], $ph) .') ';
		}
		if ( isset($filter_data['province']) AND isset($filter_data['province'][0]) AND !in_array( -1, (array)$filter_data['province']) AND !in_array( '00', (array)$filter_data['province']) ) {
			$query	.=	' AND a.province in ('. $this->getListSQL($filter_data['province'], $ph) .') ';
		}
		if ( isset($filter_data['city']) AND !is_array($filter_data['city']) AND trim($filter_data['city']) != '' ) {
			$ph[] = strtolower(trim($filter_data['city']));
			$query	.=	' AND lower(a.city) LIKE ?';
		}
		if ( isset($filter_data['first_name']) AND !is_array($filter_data['first_name']) AND trim($filter_data['first_name']) != '' ) {
			$ph[] = strtolower(trim($filter_data['first_name']));
			$query	.=	' AND lower(a.first_name) LIKE ?';
		}
		if ( isset($filter_data['last_name']) AND !is_array($filter_data['last_name']) AND trim($filter_data['last_name']) != '' ) {
			$ph[] = strtolower(trim($filter_data['last_name']));
			$query	.=	' AND lower(a.last_name) LIKE ?';
		}
		if ( isset($filter_data['home_phone']) AND !is_array($filter_data['home_phone']) AND trim($filter_data['home_phone']) != '' ) {
			$ph[] = trim($filter_data['home_phone']);
			$query	.=	' AND a.home_phone LIKE ?';
		}
		if ( isset($filter_data['employee_number']) AND !is_array($filter_data['employee_number']) AND trim($filter_data['employee_number']) != '' ) {
			$ph[] = trim($filter_data['employee_number']);
			$query	.=	' AND a.employee_number LIKE ?';
		}
		if ( isset($filter_data['user_name']) AND !is_array($filter_data['user_name']) AND trim($filter_data['user_name']) != '' ) {
			$ph[] = strtolower(trim($filter_data['user_name']));
			$query	.=	' AND lower(a.user_name) LIKE ?';
		}
		if ( isset($filter_data['sin']) AND !is_array($filter_data['sin']) AND trim($filter_data['sin']) != '' ) {
			$ph[] = trim($filter_data['sin']);
			$query	.=	' AND a.sin LIKE ?';
		}

		$query .=	'
						AND a.deleted = 0
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );

		$this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

	function getSearchByCompanyIdAndBranchIdAndDepartmentIdAndStatusId($company_id, $branch_id, $department_id, $status_id = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'status_id' => 'asc', 'last_name' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$ph = array(
					'company_id' => $company_id,
					);

		$query = '
					select	*
					from	'. $this->getTable() .'
					where	company_id = ?
					';

		if ( $status_id != '' AND isset($status_id[0]) AND !in_array(-1, (array)$status_id) ) {
			$query	.=	' AND status_id in ('. $this->getListSQL($status_id, $ph) .') ';
		}
		if ( $branch_id != '' AND isset($branch_id[0]) AND !in_array(-1, (array)$branch_id) ) {
			$query	.=	' AND default_branch_id in ('. $this->getListSQL($branch_id, $ph) .') ';
		}
		if ( $department_id != '' AND ( isset($department_id[0]) AND !in_array(-1, (array)$department_id) ) ) {
			$query	.=	' AND default_department_id in ('. $this->getListSQL($department_id, $ph) .') ';
		}

		$query .=	'
						AND deleted = 0
					';
		$query .= $this->getSortSQL( $order, $strict );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getSearchByCompanyIdAndGroupIdAndSubGroupsAndBranchIdAndDepartmentIdAndStatusId($company_id, $group_id, $include_sub_groups, $branch_id, $department_id, $status_id = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'status_id' => 'asc', 'last_name' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		if ( $include_sub_groups == TRUE
			AND ( $group_id != '' AND isset($group_id[0]) AND !in_array(-1, (array)$group_id) ) ) {
			$uglf = new UserGroupListFactory();
			$group_id = $uglf->getByCompanyIdAndGroupIdAndSubGroupsArray( $company_id, $group_id, TRUE);
		}

		$ph = array(
					'company_id' => $company_id,
					);

		$query = '
					select	*
					from	'. $this->getTable() .'
					where	company_id = ?
					';

		if ( $status_id != '' AND isset($status_id[0]) AND !in_array(-1, (array)$status_id) ) {
			$query	.=	' AND status_id in ('. $this->getListSQL($status_id, $ph) .') ';
		}
		if ( $group_id != '' AND isset($group_id[0]) AND !in_array(-1, (array)$group_id) ) {
			$query	.=	' AND group_id in ('. $this->getListSQL($group_id, $ph) .') ';
		}
		if ( $branch_id != '' AND isset($branch_id[0]) AND !in_array(-1, (array)$branch_id) ) {
			$query	.=	' AND default_branch_id in ('. $this->getListSQL($branch_id, $ph) .') ';
		}
		if ( $department_id != '' AND ( isset($department_id[0]) AND !in_array(-1, (array)$department_id) ) ) {
			$query	.=	' AND default_department_id in ('. $this->getListSQL($department_id, $ph) .') ';
		}

		$query .=	'
						AND deleted = 0
					';
		$query .= $this->getSortSQL( $order, $strict );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getSearchByCompanyIdAndUserIDAndGroupIdAndSubGroupsAndBranchIdAndDepartmentIdAndStatusId($company_id, $user_id, $group_id, $include_sub_groups, $branch_id, $department_id, $status_id = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $user_id == '') {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'status_id' => 'asc', 'last_name' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		if ( $include_sub_groups == TRUE
			AND ( $group_id != '' AND isset($group_id[0]) AND !in_array(-1, (array)$group_id) ) ) {
			$uglf = new UserGroupListFactory();
			$group_id = $uglf->getByCompanyIdAndGroupIdAndSubGroupsArray( $company_id, $group_id, TRUE);
		}

		$ph = array(
					'company_id' => $company_id,
					);

		$query = '
					select	*
					from	'. $this->getTable() .'
					where	company_id = ?
						AND	id in ('. $this->getListSQL($user_id, $ph) .')
					';

		if ( $status_id != '' AND isset($status_id[0]) AND !in_array(-1, (array)$status_id) ) {
			$query	.=	' AND status_id in ('. $this->getListSQL($status_id, $ph) .') ';
		}
		if ( $group_id != '' AND isset($group_id[0]) AND !in_array(-1, (array)$group_id) ) {
			$query	.=	' AND group_id in ('. $this->getListSQL($group_id, $ph) .') ';
		}
		if ( $branch_id != '' AND isset($branch_id[0]) AND !in_array(-1, (array)$branch_id) ) {
			$query	.=	' AND default_branch_id in ('. $this->getListSQL($branch_id, $ph) .') ';
		}
		if ( $department_id != '' AND ( isset($department_id[0]) AND !in_array(-1, (array)$department_id) ) ) {
			$query	.=	' AND default_department_id in ('. $this->getListSQL($department_id, $ph) .') ';
		}

		$query .=	'
						AND deleted = 0
					';
		$query .= $this->getSortSQL( $order, $strict );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getSearchByCompanyIdAndStatusIdAndBranchIdAndDepartmentIdAndUserTitleIdAndIncludeIdAndExcludeId($company_id, $status_id, $branch_id, $department_id, $user_title_id = NULL, $include_user_id = NULL, $exclude_user_id = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'status_id' => 'asc', 'last_name' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$ph = array(
					'company_id' => $company_id,
					);

		$query = '
					select	a.*
					from	'. $this->getTable() .' as a
					where	a.company_id = ?

					';

		$filter_query = NULL;
		if ( $status_id != '' AND isset($status_id[0]) AND !in_array(-1, $status_id) ) {
			$filter_query  .=	' AND a.status_id in ('. $this->getListSQL($status_id, $ph) .') ';
		}
		if ( $branch_id != '' AND isset($branch_id[0]) AND !in_array(-1, $branch_id) ) {
			$filter_query  .=	' AND a.default_branch_id in ('. $this->getListSQL($branch_id, $ph) .') ';
		}
		if ( $department_id != '' AND isset($department_id[0]) AND !in_array(-1, $department_id) ) {
			$filter_query  .=	' AND a.default_department_id in ('. $this->getListSQL($department_id, $ph) .') ';
		}
		if ( $user_title_id != '' AND isset($user_title_id[0]) AND !in_array(-1, $user_title_id) ) {
			$filter_query  .=	' AND a.title_id in ('. $this->getListSQL($user_title_id, $ph) .') ';
		}
		if ( $exclude_user_id != '' AND isset($exclude_user_id[0]) ) {
			$filter_query  .=	' AND a.id not in ('. $this->getListSQL($exclude_user_id, $ph) .') ';
		}

		//If Branch, Dept, Status, Exclude are set, we need to prepend
		//the company_id filter.
		if ( isset($filter_query) AND $filter_query != '' ) {
			$query .= $filter_query;
			$include_user_by_or = TRUE;
		} else {
			$include_user_by_or = FALSE;
		}

		if ( $include_user_id != '' AND isset($include_user_id[0]) ) {
			$ph[] = $company_id;

			//If other criteria are set, we OR this filter.
			//otherwise we just leave it as is.
			if ( $include_user_by_or == TRUE ) {
				$query .= ' OR ';
			} else {
				$query .= ' AND ';
			}
			$query	.=	' ( a.company_id = ? AND a.id in ('. $this->getListSQL($include_user_id, $ph) .') ) ';
		}

		$query .=	'
						AND a.deleted = 0
					';
		$query .= $this->getSortSQL( $order, $strict );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getReportByCompanyIdAndUserIDList($company_id, $user_ids, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $user_ids == '') {
			return FALSE;
		}
/*
		if ( $order == NULL ) {
			$order = array( 'status_id' => 'asc', 'last_name' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}
*/

//		$utf = new UserTaxFactory();
//					LEFT JOIN '. $utf->getTable() .' as b ON a.id = b.user_id AND (b.deleted=0 OR b.deleted IS NULL)
		$baf = new BankAccountFactory();

		$ph = array(
					'company_id' => $company_id,
					);

		$query = '
					select	c.*, a.*
					from	'. $this->getTable() .' as a
					LEFT JOIN '. $baf->getTable() .' as c ON a.id = c.user_id AND (c.deleted=0 OR c.deleted IS NULL)
					where
						a.company_id = ?
						AND a.id in ('. $this->getListSQL($user_ids, $ph) .')
						AND ( a.deleted = 0 )
				';
		$query .= $this->getSortSQL( $order, FALSE );

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	function getAPIEmailAddressDataByArrayCriteria( $filter_data = NULL, $limit = NULL, $page = NULL, $where = NULL, $order = NULL ) {
		$ph = array();

		$uf = new UserFactory();
		$cf = new CompanyFactory();
		$pcf = new PermissionControlFactory();
		$puf = new PermissionUserFactory();

		$query = '
					select
							cf.id as company_id,
							cf.parent_id as company_parent_id,
							cf.name as company_name,
							cf.status_id as company_status_id,
							g.level as permission_level,
							a.status_id,
							a.first_name,
							a.last_name,
							a.user_name,
							a.work_email,
							a.home_email,
							a.birth_date,
							a.hire_date,
							a.termination_date,
							a.created_date,
							a.last_login_date
					from	'. $this->getTable() .' as a
					LEFT JOIN	'. $cf->getTable() .' as cf ON ( a.company_id = cf.id )
					LEFT JOIN
					(
						SELECT g2.*, g1.user_id
						FROM '. $puf->getTable() .' as g1, '. $pcf->getTable() .' as g2
						WHERE ( g1.permission_control_id = g2.id AND g2.deleted = 0)
					) as g ON ( a.id = g.user_id )
					where ( 1 = 1 )
				';

		$query .= ( isset($filter_data['company_id']) ) ? $this->getWhereClauseSQL( 'a.company_id', $filter_data['company_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['company_status_id']) ) ? $this->getWhereClauseSQL( 'cf.status_id', $filter_data['company_status_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['status_id']) ) ? $this->getWhereClauseSQL( 'a.status_id', $filter_data['status_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['permission_level']) ) ? $this->getWhereClauseSQL( 'g.level', $filter_data['permission_level'], 'numeric_list', $ph ) : NULL;

		$query .= ( isset($filter_data['product_edition_id']) ) ? $this->getWhereClauseSQL( 'cf.product_edition_id', $filter_data['product_edition_id'], 'numeric_list', $ph ) : NULL;

		if ( isset($filter_data['created_date']) AND !is_array($filter_data['created_date']) AND trim($filter_data['created_date']) != '' ) {
			$date_filter = $this->getDateRangeSQL( $filter_data['created_date'], 'a.created_date' );
			if ( $date_filter != FALSE ) {
				$query	.=	' AND '. $date_filter;
			}
			unset($date_filter);
		}

		if ( isset($filter_data['last_login_date']) AND !is_array($filter_data['last_login_date']) AND trim($filter_data['last_login_date']) != '' ) {
			$date_filter = $this->getDateRangeSQL( $filter_data['last_login_date'], 'a.last_login_date' );
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

		$query .= $this->getSortSQL( $order, FALSE );

		Debug::Arr($ph, 'Query: '. $query, __FILE__, __LINE__, __METHOD__, 10);

		$this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * Return user records based on advanced filter criteria.
	 *
	 * @param int $company_id Company ID
	 * @param array $filter_data Filter criteria in array('id' => array(1, 2), 'last_name' => 'smith' ) format, with possible top level array keys as follows: id, exclude_id, status_id, user_group_id, default_branch_id, default_department_id, title_id, currency_id, permission_control_id, pay_period_schedule_id, policy_group_id, sex_id, first_name, last_name, home_phone, work_phone, country, province, city, address1, address2, postal_code, employee_number, user_name, sin, work_email, home_email, tag, last_login_date, created_by, created_date, updated_by, updated_date
	 * @param int $limit Optional. Restrict the number of records returned
	 * @param int $page Optional. Specify the page of records to return
	 * @param array $where Optional. Additional WHERE clauses in array( 'column' => 'value', 'column' => 'value' ) format.
	 * @param array $order Optional. Sort order in array( 'column' => ASC, 'column2' => DESC ) format.
	 *
	 * @return object $this
	 */
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

		if ( isset($filter_data['user_status_id']) ) {
			$filter_data['status_id'] = $filter_data['user_status_id'];
		}

		if ( isset($filter_data['include_user_id']) ) {
			$filter_data['id'] = $filter_data['include_user_id'];
		}
		if ( isset($filter_data['exclude_user_id']) ) {
			$filter_data['exclude_id'] = $filter_data['exclude_user_id'];
		}

		//Some of these are passed from Flex Schedule view.
		if ( isset($filter_data['default_branch_ids']) ) {
			$filter_data['default_branch_id'] = $filter_data['default_branch_ids'];
		}
		if ( isset($filter_data['default_department_ids']) ) {
			$filter_data['default_department_id'] = $filter_data['default_department_ids'];
		}

		if ( isset($filter_data['group_id']) ) {
			$filter_data['user_group_id'] = $filter_data['group_id'];
		}

		if ( isset($filter_data['user_title_id']) ) {
			$filter_data['title_id'] = $filter_data['user_title_id'];
		}

		if ( isset($filter_data['user_tag']) ) {
			$filter_data['tag'] = $filter_data['user_tag'];
		}

		//$additional_order_fields = array('b.name', 'c.name', 'd.name', 'e.name');
		$additional_order_fields = array(	'default_branch',
											'default_department',
											'default_job',
											'default_job_item',
											'sex',
											'user_group',
											'title',
											'currency',
											'permission_control',
											'pay_period_schedule',
											'policy_group',
											);

		$sort_column_aliases = array(
									'type' => 'type_id',
									'status' => 'status_id',
									'sex' => 'sex_id',
									'full_name' => 'last_name',
									);

		$order = $this->getColumnsFromAliases( $order, $sort_column_aliases );
		if ( $order == NULL ) {
			$order = array( 'status_id' => 'asc', 'last_name' => 'asc', 'first_name' => 'asc', 'middle_name' => 'asc');
			$strict = FALSE;
		} else {
			//Do order by column conversions, because if we include these columns in the SQL
			//query, they contaminate the data array.

			//Always try to order by status first so INACTIVE employees go to the bottom.
			if ( !isset($order['status_id']) ) {
				$order = Misc::prependArray( array('status_id' => 'asc'), $order );
			}
			//Always sort by last name, first name after other columns
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

		$compf = new CompanyFactory();
		$bf = new BranchFactory();
		$df = new DepartmentFactory();
		$ugf = new UserGroupFactory();
		$utf = new UserTitleFactory();
		$cf = new CurrencyFactory();
		$pcf = new PermissionControlFactory();
		$puf = new PermissionUserFactory();
		$ppsuf = new PayPeriodScheduleUserFactory();
		$ppsf = new PayPeriodScheduleFactory();
		$pguf = new PolicyGroupUserFactory();
		$pgf = new PolicyGroupFactory();
		$egf = new EthnicGroupFactory();

		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			$jf = new JobFactory();
			$jif = new JobItemFactory();
		}

		$ph = array(
					'company_id' => $company_id,
					);

		$query = '
					select
							a.*,
							compf.name as company,
							b.name as default_branch,
							b.manual_id as default_branch_manual_id,
							c.name as default_department,
							c.manual_id as default_department_manual_id,
							d.name as user_group,
							e.name as title,
							f.name as currency,
							f.conversion_rate as currency_rate,
							g.id as permission_control_id,
							g.name as permission_control,
							h.id as pay_period_schedule_id,
							h.name as pay_period_schedule,
							i.id as policy_group_id,
							i.name as policy_group,
							egf.name as ethnic_group, ';

		$query .= Permission::getPermissionIsChildIsOwnerSQL( ( isset($filter_data['permission_current_user_id']) ) ? $filter_data['permission_current_user_id'] : 0, 'a.id' );

		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			$query .= '	jf.name as default_job,
						jf.manual_id as default_job_manual_id,
						jif.name as default_job_item,
						jif.manual_id as default_job_item_manual_id, ';
		}

		$query .= '			y.first_name as created_by_first_name,
							y.middle_name as created_by_middle_name,
							y.last_name as created_by_last_name,
							z.first_name as updated_by_first_name,
							z.middle_name as updated_by_middle_name,
							z.last_name as updated_by_last_name
					from	'. $this->getTable() .' as a
						LEFT JOIN '. $compf->getTable() .' as compf ON ( a.company_id = compf.id AND compf.deleted = 0)
						LEFT JOIN '. $bf->getTable() .' as b ON ( a.default_branch_id = b.id AND b.deleted = 0)
						LEFT JOIN '. $df->getTable() .' as c ON ( a.default_department_id = c.id AND c.deleted = 0)
						LEFT JOIN '. $ugf->getTable() .' as d ON ( a.group_id = d.id AND d.deleted = 0 )
						LEFT JOIN '. $utf->getTable() .' as e ON ( a.title_id = e.id AND e.deleted = 0 )
						LEFT JOIN '. $cf->getTable() .' as f ON ( a.currency_id = f.id AND f.deleted = 0 )
						LEFT JOIN '. $egf->getTable() .' as egf ON ( a.ethnic_group_id = egf.id AND egf.deleted = 0 ) ';

		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			$query .= '	LEFT JOIN '. $jf->getTable() .' as jf ON a.default_job_id = jf.id';
			$query .= '	LEFT JOIN '. $jif->getTable() .' as jif ON a.default_job_item_id = jif.id';
		}

		$query .= '		LEFT JOIN
						(
							SELECT g2.*, g1.user_id
							FROM '. $puf->getTable() .' as g1, '. $pcf->getTable() .' as g2
							WHERE ( g1.permission_control_id = g2.id AND g2.deleted = 0)
						) as g ON ( a.id = g.user_id )
						LEFT JOIN
						(
							SELECT h2.*, h1.user_id
							FROM '. $ppsuf->getTable() .' as h1, '. $ppsf->getTable() .' as h2
							WHERE ( h1.pay_period_schedule_id = h2.id AND h2.deleted = 0)
						) as h ON ( a.id = h.user_id )
						LEFT JOIN
						(
							SELECT i2.*, i1.user_id
							FROM '. $pguf->getTable() .' as i1, '. $pgf->getTable() .' as i2
							WHERE ( i1.policy_group_id = i2.id AND i2.deleted = 0)
						) as i ON ( a.id = i.user_id ) ';

		$query .= Permission::getPermissionHierarchySQL( $company_id, ( isset($filter_data['permission_current_user_id']) ) ? $filter_data['permission_current_user_id'] : 0, 'a.id' );

		$query .= '
						LEFT JOIN '. $this->getTable() .' as y ON ( a.created_by = y.id AND y.deleted = 0 )
						LEFT JOIN '. $this->getTable() .' as z ON ( a.updated_by = z.id AND z.deleted = 0 )
					where	a.company_id = ?
					';

		$query .= Permission::getPermissionIsChildIsOwnerFilterSQL( $filter_data, 'a.id' );
		$query .= ( isset($filter_data['permission_children_ids']) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['permission_children_ids'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['id']) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['exclude_id']) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['exclude_id'], 'not_numeric_list', $ph ) : NULL;

		if ( isset($filter_data['status']) AND !is_array($filter_data['status']) AND trim($filter_data['status']) != '' AND !isset($filter_data['status_id']) ) {
			$filter_data['status_id'] = Option::getByFuzzyValue( $filter_data['status'], $this->getOptions('status') );
		}
		$query .= ( isset($filter_data['status_id']) ) ? $this->getWhereClauseSQL( 'a.status_id', $filter_data['status_id'], 'numeric_list', $ph ) : NULL;

		if ( isset($filter_data['include_subgroups']) AND (bool)$filter_data['include_subgroups'] == TRUE ) {
			$uglf = new UserGroupListFactory();
			$filter_data['user_group_id'] = $uglf->getByCompanyIdAndGroupIdAndSubGroupsArray( $company_id, $filter_data['user_group_id'], TRUE);
		}
		$query .= ( isset($filter_data['user_group_id']) ) ? $this->getWhereClauseSQL( 'a.group_id', $filter_data['user_group_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['user_group']) ) ? $this->getWhereClauseSQL( 'd.name', $filter_data['user_group'], 'text', $ph ) : NULL;

		$query .= ( isset($filter_data['default_branch_id']) ) ? $this->getWhereClauseSQL( 'a.default_branch_id', $filter_data['default_branch_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['default_branch']) ) ? $this->getWhereClauseSQL( 'b.name', $filter_data['default_branch'], 'text', $ph ) : NULL;

		$query .= ( isset($filter_data['default_department_id']) ) ? $this->getWhereClauseSQL( 'a.default_department_id', $filter_data['default_department_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['default_department']) ) ? $this->getWhereClauseSQL( 'c.name', $filter_data['default_department'], 'text', $ph ) : NULL;

		$query .= ( isset($filter_data['title_id']) ) ? $this->getWhereClauseSQL( 'a.title_id', $filter_data['title_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['title']) ) ? $this->getWhereClauseSQL( 'e.name', $filter_data['title'], 'text', $ph ) : NULL;

		$query .= ( isset($filter_data['ethnic_group_id']) ) ? $this->getWhereClauseSQL( 'a.ethnic_group_id', $filter_data['ethnic_group_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['ethnic_group']) ) ? $this->getWhereClauseSQL( 'egf.name', $filter_data['ethnic_group'], 'text', $ph ) : NULL;

		$query .= ( isset($filter_data['currency_id']) ) ? $this->getWhereClauseSQL( 'a.currency_id', $filter_data['currency_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['currency']) ) ? $this->getWhereClauseSQL( 'f.name', $filter_data['currency'], 'text', $ph ) : NULL;

		$query .= ( isset($filter_data['permission_control_id']) ) ? $this->getWhereClauseSQL( 'g.id', $filter_data['permission_control_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['permission_control']) ) ? $this->getWhereClauseSQL( 'g.name', $filter_data['permission_control'], 'text', $ph ) : NULL;

		$query .= ( isset($filter_data['pay_period_schedule_id']) ) ? $this->getWhereClauseSQL( 'h.id', $filter_data['pay_period_schedule_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['pay_period_schedule']) ) ? $this->getWhereClauseSQL( 'h.name', $filter_data['pay_period_schedule'], 'text', $ph ) : NULL;

		$query .= ( isset($filter_data['policy_group_id']) ) ? $this->getWhereClauseSQL( 'i.id', $filter_data['policy_group_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['policy_group']) ) ? $this->getWhereClauseSQL( 'i.name', $filter_data['policy_group'], 'text', $ph ) : NULL;

		if ( isset($filter_data['sex']) AND !is_array($filter_data['sex']) AND trim($filter_data['sex']) != '' AND !isset($filter_data['sex_id']) ) {
			$filter_data['sex_id'] = Option::getByFuzzyValue( $filter_data['sex'], $this->getOptions('sex') );
		}
		$query .= ( isset($filter_data['sex_id']) ) ? $this->getWhereClauseSQL( 'a.sex_id', $filter_data['sex_id'], 'numeric_list', $ph ) : NULL;

		$query .= ( isset($filter_data['first_name']) ) ? $this->getWhereClauseSQL( 'a.first_name', $filter_data['first_name'], 'text_metaphone', $ph ) : NULL;
		$query .= ( isset($filter_data['last_name']) ) ? $this->getWhereClauseSQL( 'a.last_name', $filter_data['last_name'], 'text_metaphone', $ph ) : NULL;
		$query .= ( isset($filter_data['full_name']) ) ? $this->getWhereClauseSQL( 'a.last_name', $filter_data['full_name'], 'text_metaphone', $ph ) : NULL;
		$query .= ( isset($filter_data['home_phone']) ) ? $this->getWhereClauseSQL( 'a.home_phone', $filter_data['home_phone'], 'phone', $ph ) : NULL;
		$query .= ( isset($filter_data['work_phone']) ) ? $this->getWhereClauseSQL( 'a.work_phone', $filter_data['work_phone'], 'phone', $ph ) : NULL;
		$query .= ( isset($filter_data['country']) ) ?$this->getWhereClauseSQL( 'a.country', $filter_data['country'], 'upper_text_list', $ph ) : NULL;
		$query .= ( isset($filter_data['province']) ) ? $this->getWhereClauseSQL( 'a.province', $filter_data['province'], 'upper_text_list', $ph ) : NULL;
		$query .= ( isset($filter_data['city']) ) ? $this->getWhereClauseSQL( 'a.city', $filter_data['city'], 'text', $ph ) : NULL;
		$query .= ( isset($filter_data['address1']) ) ? $this->getWhereClauseSQL( 'a.address1', $filter_data['address1'], 'text', $ph ) : NULL;
		$query .= ( isset($filter_data['address2']) ) ? $this->getWhereClauseSQL( 'a.address2', $filter_data['address2'], 'text', $ph ) : NULL;
		$query .= ( isset($filter_data['postal_code']) ) ? $this->getWhereClauseSQL( 'a.postal_code', $filter_data['postal_code'], 'text', $ph ) : NULL;
		$query .= ( isset($filter_data['employee_number']) ) ? $this->getWhereClauseSQL( 'a.employee_number', $filter_data['employee_number'], 'numeric', $ph ) : NULL;
		$query .= ( isset($filter_data['user_name']) ) ? $this->getWhereClauseSQL( 'a.user_name', $filter_data['user_name'], 'text', $ph ) : NULL;
		$query .= ( isset($filter_data['sin']) ) ? $this->getWhereClauseSQL( 'a.sin', $filter_data['sin'], 'numeric', $ph ) : NULL;

		$query .= ( isset($filter_data['email']) AND !is_array($filter_data['email']) AND $filter_data['email'] != '' ) ? 'AND ('. $this->getWhereClauseSQL( 'a.work_email', $filter_data['email'], 'text', $ph, NULL, FALSE ).' OR '. $this->getWhereClauseSQL( 'a.home_email', $filter_data['email'], 'text', $ph, NULL, FALSE ) .')' : NULL;
		$query .= ( isset($filter_data['work_email']) ) ? $this->getWhereClauseSQL( 'a.work_email', $filter_data['work_email'], 'text', $ph ) : NULL;
		$query .= ( isset($filter_data['home_email']) ) ? $this->getWhereClauseSQL( 'a.home_email', $filter_data['home_email'], 'text', $ph ) : NULL;

		$query .= ( isset($filter_data['tag']) ) ? $this->getWhereClauseSQL( 'a.id', array( 'company_id' => $company_id, 'object_type_id' => 200, 'tag' => $filter_data['tag'] ), 'tag', $ph ) : NULL;

		//$query .= ( isset($filter_data['longitude']) ) ? $this->getWhereClauseSQL( 'a.longitude', $filter_data['longitude'], 'numeric', $ph ) : NULL;

		if ( isset($filter_data['last_login_date']) AND !is_array($filter_data['last_login_date']) AND trim($filter_data['last_login_date']) != '' ) {
			$date_filter = $this->getDateRangeSQL( $filter_data['last_login_date'], 'a.last_login_date' );
			if ( $date_filter != FALSE ) {
				$query	.=	' AND '. $date_filter;
			}
			unset($date_filter);
		}

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

		$query .=	'
						AND ( a.deleted = 0 )
					';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );

		//Debug::Arr($ph, 'Query: '. $query, __FILE__, __LINE__, __METHOD__, 10);

		$this->ExecuteSQL( $query, $ph, $limit, $page );

		return $this;
	}

}
?>
