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
 * @package Modules\Qualification
 */
class UserEducationListFactory extends UserEducationFactory implements IteratorAggregate {

	function getAll($limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		$query = '
					select	*
					from	'. $this->getTable() .'
					WHERE deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL($query, NULL, $limit, $page);

		return $this;
	}

	function getById($id, $where = NULL, $order = NULL) {
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
			$query .= $this->getWhereSQL( $where );
			$query .= $this->getSortSQL( $order );

			$this->ExecuteSQL($query, $ph);

			$this->saveCache($this->rs, $id);
		}

		return $this;
	}

	function getByUserId($user_id, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		$ph = array(
					'user_id' => $user_id,
					);

		$query = '
					select	*
					from	'. $this->getTable() .'
					where	user_id = ?
						AND deleted = 0';
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL($query, $ph);

		return $this;
	}

	function getByIdAndCompanyId($id, $company_id, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		if ( $company_id == '') {
			return FALSE;
		}

		$qf = new QualificationFactory();

		$ph = array(
					'id' => $id,
					'company_id' => $company_id
					);

		$query = '
					select	a.*
					from	'. $this->getTable() .' as a
						LEFT JOIN  '. $qf->getTable() .' as b on a.qualification_id = b.id
					where	a.id = ?
						AND b.company_id = ?
						AND a.deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL($query, $ph);

		return $this;
	}

	function getByCompanyId($company_id, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		$qf = new QualificationFactory();

		$ph = array(
					'company_id' => $company_id
					);

		$query = '
					select	a.*
					from	'. $this->getTable() .' as a
						LEFT JOIN  '. $qf->getTable() .' as b on a.qualification_id = b.id
					where	b.company_id = ?
						AND a.deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->ExecuteSQL($query, $ph);

		return $this;
	}

	function getByIdAndUserId($id, $user_id, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		$this->rs = $this->getCache($id.$user_id);
		if ( $this->rs === FALSE ) {
			$ph = array(
						'id' => $id,
						'user_id' => $user_id,
						);

			$query = '
						select	*
						from	'. $this->getTable() .'
						where	id = ?
							AND user_id = ?
							AND deleted = 0';
			$query .= $this->getWhereSQL( $where );
			$query .= $this->getSortSQL( $order );

			$this->ExecuteSQL($query, $ph);

			$this->saveCache($this->rs, $id.$user_id);
		}

		return $this;
	}

	function getByUserIdAndQualificationId($user_id, $qualification_id, $where = NULL, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		$this->rs = $this->getCache($user_id.$qualification_id);
		if ( $this->rs === FALSE ) {
			$ph = array(
						'user_id' => $user_id,
						'qualification_id' => $qualification_id,
						);

			$query = '
						select	*
						from	'. $this->getTable() .'
						where	user_id = ?
							AND qualification_id = ?
							AND deleted = 0';
			$query .= $this->getWhereSQL( $where );
			$query .= $this->getSortSQL( $order );

			$this->ExecuteSQL($query, $ph);

			$this->saveCache($this->rs, $user_id.$qualification_id);
		}

		return $this;
	}

	function  getAPISearchByCompanyIdAndArrayCriteria( $company_id, $filter_data, $limit = NULL, $page = NULL, $where = NULL, $order = NULL ) {
	
		if ( $company_id == '') {
			return FALSE;
		}

		if ( isset( $filter_data['include_user_id'] ) ) {
			$filter_data['user_id'] = $filter_data['include_user_id'];
		}
		if ( isset( $filter_data['exclude_user_id'] ) ) {
			$filter_data['exclude_id'] = $filter_data['exclude_user_id'];
		}

		if ( isset( $filter_data['qualification_group_id'] ) ) {
			$filter_data['group_id'] =	$filter_data['qualification_group_id'];
		}

		if ( !is_array($order) ) {
			//Use Filter Data ordering if its set.
			if ( isset($filter_data['sort_column']) AND $filter_data['sort_order']) {
				$order = array(Misc::trimSortPrefix($filter_data['sort_column']) => $filter_data['sort_order']);
			}
		}

		$additional_order_fields = array('uf.first_name', 'uf.last_name', 'qf.name', 'qgf.name', 'df.name', 'bf.name', 'ugf.name', 'utf.name');
		$sort_column_aliases = array();

		$order = $this->getColumnsFromAliases( $order, $sort_column_aliases );

		if ( $order == NULL ) {
			$order = array( 'qf.name' => 'asc', 'qgf.name' => 'asc' );
			$strict = FALSE;
		} else {
			if ( isset($order['first_name']) ) {
				$order['uf.first_name'] = $order['first_name'];
				unset($order['first_name']);
			}
			if ( isset($order['last_name']) ) {
				$order['uf.last_name'] = $order['last_name'];
				unset($order['last_name']);
			}
			if ( isset($order['qualification']) ) {
				$order['qf.name'] = $order['qualification'];
				unset($order['qualification']);
			}
			if ( isset($order['group']) ) {
				$order['qgf.name'] = $order['group'];
				unset($order['group']);
			}
			if ( isset($order['default_department']) ) {
				$order['df.name'] = $order['default_department'];
				unset($order['default_department']);
			}
			if ( isset($order['default_branch']) ) {
				$order['bf.name'] = $order['default_branch'];
				unset($order['default_branch']);
			}
			if ( isset($order['user_group']) ) {
				$order['ugf.name'] = $order['user_group'];
				unset($order['user_group']);
			}
			if ( isset($order['title']) ) {
				$order['utf.name'] = $order['title'];
				unset($order['title']);
			}
			$strict = TRUE;
		}

		//Debug::Arr($order, 'Order Data:', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($filter_data, 'Filter Data:', __FILE__, __LINE__, __METHOD__, 10);

		$uf = new UserFactory();
		$bf = new BranchFactory();
		$df = new DepartmentFactory();
		$ugf = new UserGroupFactory();
		$utf = new UserTitleFactory();
		$qf = new QualificationFactory();
		$usf = new UserSkillFactory();
		$ulf = new UserLanguageFactory();
		$umf = new UserMembershipFactory();
		$qgf = new QualificationGroupFactory();
		$ph = array(
					'company_id' => $company_id,
					);

		$query = '
					select	a.*,
							uf.first_name as first_name,
							uf.last_name as last_name,
							qf.name as qualification,
							qgf.name as "group",

							bf.id as default_branch_id,
							bf.name as default_branch,
							df.id as default_department_id,
							df.name as default_department,
							ugf.id as user_group_id,
							ugf.name as user_group,
							utf.id as user_title_id,
							utf.name as title,

							y.first_name as created_by_first_name,
							y.middle_name as created_by_middle_name,
							y.last_name as created_by_last_name,
							z.first_name as updated_by_first_name,
							z.middle_name as updated_by_middle_name,
							z.last_name as updated_by_last_name
					from	'. $this->getTable() .' as a
						LEFT JOIN '. $uf->getTable() .' as uf ON ( a.user_id = uf.id AND uf.deleted = 0)
						LEFT JOIN '. $usf->getTable() .' as usf ON	( a.qualification_id = usf.qualification_id AND usf.deleted = 0)
						LEFT JOIN '. $ulf->getTable() .' as ulf ON ( a.qualification_id = ulf.qualification_id AND ulf.deleted =0 )
						LEFT JOIN '. $umf->getTable() .' as umf ON ( a.qualification_id = umf.qualification_id AND umf.deleted = 0 )
						LEFT JOIN '. $qf->getTable() .' as qf ON ( a.qualification_id = qf.id  AND qf.deleted = 0 )
						LEFT JOIN '. $bf->getTable() .' as bf ON ( uf.default_branch_id = bf.id AND bf.deleted = 0)
						LEFT JOIN '. $df->getTable() .' as df ON ( uf.default_department_id = df.id AND df.deleted = 0)
						LEFT JOIN '. $ugf->getTable() .' as ugf ON ( uf.group_id = ugf.id AND ugf.deleted = 0 )
						LEFT JOIN '. $utf->getTable() .' as utf ON ( uf.title_id = utf.id AND utf.deleted = 0 )
						LEFT JOIN '. $qgf->getTable() .' as qgf ON ( qf.group_id = qgf.id AND qgf.deleted = 0 )
						LEFT JOIN '. $uf->getTable() .' as y ON ( a.created_by = y.id AND y.deleted = 0 )
						LEFT JOIN '. $uf->getTable() .' as z ON ( a.updated_by = z.id AND z.deleted = 0 )
					where	qf.company_id = ?';

		$query .= ( isset($filter_data['permission_children_ids']) ) ? $this->getWhereClauseSQL( 'a.user_id', $filter_data['permission_children_ids'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['user_id']) ) ? $this->getWhereClauseSQL( 'a.user_id', $filter_data['user_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['id']) ) ? $this->getWhereClauseSQL( 'a.id', $filter_data['id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['exclude_id']) ) ? $this->getWhereClauseSQL( 'a.user_id', $filter_data['exclude_id'], 'not_numeric_list', $ph ) : NULL;

		$query .= ( isset($filter_data['qualification_id']) ) ? $this->getWhereClauseSQL( 'a.qualification_id', $filter_data['qualification_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['qualification']) ) ? $this->getWhereClauseSQL( 'qf.name', $filter_data['qualification'], 'text', $ph ) : NULL;

		$query .= ( isset($filter_data['proficiency_id']) ) ? $this->getWhereClauseSQL( 'usf.proficiency_id', $filter_data['proficiency_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['fluency_id']) ) ? $this->getWhereClauseSQL( 'ulf.fluency_id', $filter_data['fluency_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['competency_id']) ) ? $this->getWhereClauseSQL( 'ulf.competency_id', $filter_data['competency_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['ownership_id']) ) ? $this->getWhereClauseSQL( 'umf.ownership_id', $filter_data['ownership_id'], 'numeric_list', $ph ) : NULL;

		$query .= ( isset($filter_data['institute']) ) ? $this->getWhereClauseSQL( 'a.institute', $filter_data['institute'], 'text', $ph ) : NULL;

		$query .= ( isset($filter_data['major']) ) ? $this->getWhereClauseSQL( 'a.major', $filter_data['major'], 'text', $ph ) : NULL;

		$query .= ( isset($filter_data['minor']) ) ? $this->getWhereClauseSQL( 'a.minor', $filter_data['minor'], 'text', $ph ) : NULL;
		
		$query .= ( isset($filter_data['grade_score']) ) ? $this->getWhereClauseSQL( 'a.grade_score', $filter_data['grade_score'], 'numeric', $ph ) : NULL;

		$query .= ( isset($filter_data['group_id']) ) ? $this->getWhereClauseSQL( 'qf.group_id', $filter_data['group_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['group']) ) ? $this->getWhereClauseSQL( 'qgf.name', $filter_data['group'], 'text', $ph ) : NULL;

		$query .= ( isset($filter_data['qualification_type_id']) ) ? $this->getWhereClauseSQL( 'qf.type_id', $filter_data['qualification_type_id'], 'numeric_list', $ph ) : NULL;

		$query .= ( isset($filter_data['default_branch_id']) ) ? $this->getWhereClauseSQL( 'uf.default_branch_id', $filter_data['default_branch_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['default_department_id']) ) ? $this->getWhereClauseSQL( 'uf.default_department_id', $filter_data['default_department_id'], 'numeric_list', $ph ) : NULL;
		$query .= ( isset($filter_data['tag']) ) ? $this->getWhereClauseSQL( 'a.id', array( 'company_id' => $company_id, 'object_type_id' => 252, 'tag' => $filter_data['tag'] ), 'tag', $ph ) : NULL;

		$query .= ( isset($filter_data['start_date']) ) ? $this->getWhereClauseSQL( 'a.start_date', $filter_data['start_date'], 'date_range', $ph ) : NULL;

		$query .= ( isset($filter_data['end_date']) ) ? $this->getWhereClauseSQL( 'a.end_date', $filter_data['end_date'], 'date_range', $ph ) : NULL;

		$query .= ( isset($filter_data['graduate_date']) ) ? $this->getWhereClauseSQL( 'a.graduate_date', $filter_data['graduate_date'], 'date_range', $ph ) : NULL;

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

		$query .=	' AND a.deleted = 0 ';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, $additional_order_fields );

		$this->ExecuteSQL($query, $ph, $limit, $page);

		return $this;
	}
}
?>
