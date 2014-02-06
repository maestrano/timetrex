<?php
/*********************************************************************************
 * TimeTrex is a Payroll and Time Management program developed by
 * TimeTrex Software Inc. Copyright (C) 2003 - 2013 TimeTrex Software Inc.
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
/*
 * $Revision: 10397 $
 * $Id: HierarchyListFactory.class.php 10397 2013-07-09 19:19:53Z ipso $
 * $Date: 2013-07-09 12:19:53 -0700 (Tue, 09 Jul 2013) $
 */

/**
 * @package Modules\Hierarchy
 */
class HierarchyListFactory extends HierarchyFactory implements IteratorAggregate {

	protected $fasttree_obj = NULL;

	function getFastTreeObject() {

		if ( is_object($this->fasttree_obj) ) {
			return $this->fasttree_obj;
		} else {
			global $fast_tree_options;
			$this->fasttree_obj = new FastTree($fast_tree_options);

			return $this->fasttree_obj;
		}
	}

	function getChildLevelIdArrayByHierarchyControlIdAndUserId($tree_id, $user_id, $recurse = FALSE) {
		//This only gets the immediate children
		//Used for authorization list when they just want to see immediate children.

		if ( $tree_id == '' ) {
			return FALSE;
		}

		if ( $user_id == '' ) {
			return FALSE;
		}

		$this->getFastTreeObject()->setTree( $tree_id );

		//Get current level IDs first, then get children of all of them.
		$ids = $this->getCurrentLevelIdArrayByHierarchyControlIdAndUserId($tree_id, $user_id, FALSE);
		//Debug::Arr($ids ,' zzNodes at the same level: User ID: '. $user_id, __FILE__, __LINE__, __METHOD__,10);

		if ( $ids === FALSE ) {
			return FALSE;
		}

		$hslf = new HierarchyShareListFactory();

		$retarr = array();
		foreach ( $ids as $id ) {
			//Debug::Text(' Getting Children of ID: '. $id, __FILE__, __LINE__, __METHOD__,10);
			$children = $this->getFastTreeObject()->getAllChildren( $id, $recurse );
			//Debug::Arr($children ,' ccNodes at the same level', __FILE__, __LINE__, __METHOD__,10);

			if ( $children === FALSE ) {
				continue;
			}

			//Remove $user_id from final array, otherwise permission checks will think the user doing the permission
			//check is a child of themself, preventing users from view/editing children but not themselves.
			/*
			if ( isset($children[$user_id]) ) {
				unset($children[$user_id]);
			}
			*/
			$child_ids = array_keys( $children );

			$retarr = array_merge($retarr, $child_ids);
			unset($child_ids);
		}

		return $retarr;
	}

	function getAllParentLevelIdArrayByHierarchyControlIdAndUserId($tree_id, $user_id) {
		//This only gets the immediate parents
		if ( $tree_id == '' ) {
			return FALSE;
		}

		if ( $user_id == '' ) {
			return FALSE;
		}

		$this->getFastTreeObject()->setTree( $tree_id );

		$ids = $this->getFastTreeObject()->getAllParents( $user_id );
		//Debug::Arr($ids ,' Parent Nodes', __FILE__, __LINE__, __METHOD__,10);

		//Find out if any of the parents are shared.
		$hslf = new HierarchyShareListFactory();

		$retarr = array();
		foreach ( $ids as $id ) {

			$hierarchy_share = $hslf->getByHierarchyControlIdAndUserId( $tree_id, $id )->getCurrent()->isNew();

			if ( $hierarchy_share === FALSE ) {
				//Debug::Text(' Node IS shared:  '. $id, __FILE__, __LINE__, __METHOD__,10);

				//Get current level IDs
				$current_level_ids = $this->getCurrentLevelIdArrayByHierarchyControlIdAndUserId( $tree_id, $id );
				$retarr = array_merge($retarr, $current_level_ids);
				unset($current_level_ids);
			} else {
				//Debug::Text(' Node isnt shared:  '. $id, __FILE__, __LINE__, __METHOD__,10);
				$retarr[] = (int)$id;
			}
		}

		//Debug::Arr($retarr ,' Final Parent Nodes including shared', __FILE__, __LINE__, __METHOD__,10);
		return array_unique($retarr);
	}



	function getLevelsByHierarchyControlIdAndUserId( $id, $user_id ) {
		if ( $id == '' ) {
			return FALSE;
		}

		if ( $user_id == '' ) {
			return FALSE;
		}

		$hllf = new HierarchyLevelListFactory();
		return $hllf->getLevelsByHierarchyControlIdAndUserId( $id, $user_id );
	}

	function getByHierarchyControlIdAndUserId( $tree_id, $user_id ) {
		if ( $tree_id == '' ) {
			return FALSE;
		}

		if ( $user_id == '' ) {
			return FALSE;
		}

		$this->getFastTreeObject()->setTree( $tree_id );

		$node = $this->getFastTreeObject()->getNode( $user_id );

		if ($node === FALSE ) {
			return FALSE;
		}

		$ulf = new UserListFactory();
		$user_obj = $ulf->getById( $node['object_id'] )->getCurrent();

		$hslf = new HierarchyShareListFactory();
		$hierarchy_share = $hslf->getByHierarchyControlIdAndUserId( $tree_id, $user_id )->getCurrent()->isNew();

		if ( $hierarchy_share === FALSE ) {
			$shared = TRUE;
		} else {
			$shared = FALSE;
		}

		$retarr = array(
						'id' => $node['object_id'],
						'parent_id' => $node['parent_id'],
						'name' => $user_obj->getFullName(),
						'level' => $node['level'],
						'shared' => $shared
					);

		return $retarr;
	}


	function getByHierarchyControlId( $tree_id ) {
		if ( $tree_id == '' ) {
			return FALSE;
		}

		$this->getFastTreeObject()->setTree( $tree_id );

		$children = $this->getFastTreeObject()->getAllChildren(NULL, 'RECURSE');

		$ulf = new UserListFactory();
		$hslf = new HierarchyShareListFactory();
		$hslf->getByHierarchyControlId( $tree_id );
		$shared_user_ids = array();
		foreach( $hslf as $hierarchy_share ) {
			$shared_user_ids[] = $hierarchy_share->getUser();
		}

		if ( $children !== FALSE ) {
			foreach ($children as $object_id => $level ) {

				if ( $object_id !== 0 ) {
					$user_obj = $ulf->getById ( $object_id )->getCurrent();

					unset($shared);
					if ( in_array( $object_id, $shared_user_ids) === TRUE ) {
						$shared = TRUE;
					} else {
						$shared = FALSE;
					}

					$nodes[] = array(
									'id' => $object_id,
									'name' => $user_obj->getFullName(),
									'level' => $level,
									'shared' => $shared
									);
				}

			}

			if ( isset($nodes) ) {
				return $nodes;
			}
		}

		return FALSE;
	}






	function getByHierarchyControlIdAndUserIdAndLevel( $id, $user_id, $level = 1 ) {
		if ( $id == '' ) {
			return FALSE;
		}

		$tree_id = $id;

		if ( $user_id == '' ) {
			return FALSE;
		}

		if ( !is_numeric($level) ) {
			return FALSE;
		}
		$min_level = $level-1;
		if ( $min_level <= 1 ) {
			$min_level = 1;
		}
		$max_level = $level+1;
		Debug::Text(' User ID: '. $user_id .' Level: '. $level, __FILE__, __LINE__, __METHOD__,10);

		$retarr = array( 'current_level' => array(), 'parent_level' => array(), 'child_level' => array() );

		$hlf = new HierarchyLevelFactory();
		$huf = new HierarchyUserFactory();

		$ph = array(
					'id' => $id,
					'idb' => $id,
					'idc' => $id,
					'min_level' => $min_level,
					'max_level' => $max_level,
					'user_id' => $user_id,
					);

		$query = '
				select * from  (
						select 	a.level,
								a.user_id
						from	'. $hlf->getTable() .' as a
						where	a.hierarchy_control_id = ?
							AND a.deleted = 0

						UNION ALL

						select 	(select max(level)+1 from '. $hlf->getTable() .' as z where z.hierarchy_control_id = ? AND z.deleted = 0 ) as level,
								b.user_id
						from	'. $huf->getTable() .' as b
						where	b.hierarchy_control_id = ?
					) as tmp
					WHERE level >= ?
						AND level <= ?
					ORDER BY user_id = ? DESC, level ASC, user_id ASC
				';


		//Debug::Text(' Query: '. $query, __FILE__, __LINE__, __METHOD__,10);

		$rs = $this->db->Execute($query, $ph);

		if ( $rs->RecordCount() > 0 ) {

			//The first row should belong to the user_id that was passed.
			$current_level = FALSE;
			$i=0;
			foreach( $rs as $row ) {
				if ( $i == 0 AND $user_id == $row['user_id'] ) {
					//First row.
					$current_level = $row['level'];
					$retarr['current_level'][] = $row['user_id'];
				} elseif ( $i > 0 AND $row['level'] < $current_level ) {
					$retarr['parent_level'][] = $row['user_id'];
				} elseif ( $i > 0 AND $row['level'] > $current_level ) {
					$retarr['child_level'][] = $row['user_id'];
				} else {
					//Debug::Text(' User not in hierarchy...', __FILE__, __LINE__, __METHOD__,10);
					return FALSE;
					break;
				}

				$i++;
			}

			$retarr['current_level'] = array_unique( $retarr['current_level'] );
			$retarr['parent_level'] = array_unique( $retarr['parent_level'] );
			$retarr['child_level'] = array_unique( $retarr['child_level'] );

			//Debug::Arr($retarr ,' aChildren of User: '. $user_id .' At Level: '. $level, __FILE__, __LINE__, __METHOD__,10);

			return $retarr;

		}

		return FALSE;
	}

	function getByUserIdAndObjectTypeIDAndLevel( $user_id, $object_type_id, $level = 1, $recursive = TRUE ) {
		if ( $user_id == '' ) {
			return FALSE;
		}

		if ( !is_numeric($level) ) {
			return FALSE;
		}

		$min_level = $level-1;
		if ( $min_level <= 0 ) {
			$min_level = 0;
		}

		//This should have two modes, one where it returns just the immediate child level, and one that returns all children "recursively".
		if ( $recursive == TRUE ) {
			$max_level = 99;
		} else {
			$max_level = $level+1;
		}
		Debug::Text(' User ID: '. $user_id .' Object Type ID: '. $object_type_id .' Level: '. $level .' Min Level: '. $min_level .' Max Level: '. $max_level, __FILE__, __LINE__, __METHOD__,10);

		$retarr = array( 'current_level' => array(), 'parent_level' => array(), 'child_level' => array() );

		$hcf = new HierarchyControlFactory();
		$hlf = new HierarchyLevelFactory();
		$huf = new HierarchyUserFactory();
		$hotf = new HierarchyObjectTypeFactory();

		$ph = array();

		//UNION two queries together, the first query gets all superiors, one level above, and all levels below.
		//The 2nd query gets all subordinates.
		$query = '
				select * from  (
						select 	x.hierarchy_control_id,
								x.user_id,
								x.level,
								0 as is_subordinate
						from 	'. $hlf->getTable() .' as x
						LEFT JOIN '. $hcf->getTable() .' as y ON x.hierarchy_control_id = y.id
						LEFT JOIN '. $hotf->getTable() .' as y2 ON x.hierarchy_control_id = y2.hierarchy_control_id
						LEFT JOIN '. $hlf->getTable() .' as z ON x.hierarchy_control_id = z.hierarchy_control_id AND z.user_id = '. (int)$user_id .'
						where
							y2.object_type_id in ('. $this->getListSQL($object_type_id, $ph) .')
							AND x.level >= z.level-1
							AND ( x.deleted = 0 AND y.deleted = 0 AND z.deleted = 0 )

						UNION ALL

						select
								n.hierarchy_control_id,
								n.user_id,
								(
									select max(level)+1
									from '. $hlf->getTable() .' as z
									where z.hierarchy_control_id = n.hierarchy_control_id AND z.deleted = 0
								) as level,
								1 as is_subordinate
						from 	'. $huf->getTable() .' as n
						LEFT JOIN '. $hcf->getTable() .' as o ON n.hierarchy_control_id = o.id
						LEFT JOIN '. $hotf->getTable() .' as p ON n.hierarchy_control_id = p.hierarchy_control_id
						LEFT JOIN '. $hlf->getTable() .' as z ON n.hierarchy_control_id = z.hierarchy_control_id AND z.user_id = '. (int)$user_id .'
						where
							p.object_type_id in ('. $this->getListSQL($object_type_id, $ph) .')
							AND ( o.deleted = 0 AND z.deleted = 0 )
					) as tmp
					WHERE level >= '. (int)$min_level .'
						AND level <= '. (int)$max_level.'
					ORDER BY level ASC, user_id ASC
				';

		//Debug::Text(' Query: '. $query, __FILE__, __LINE__, __METHOD__,10);
		$rs = $this->db->Execute($query, $ph);
		//Debug::Text(' Rows: '. $rs->RecordCount(), __FILE__, __LINE__, __METHOD__,10);

		if ( $rs->RecordCount() > 0 ) {
			$current_level = $level;
			$i=0;
			foreach( $rs as $row ) {
				//Debug::Text(' User ID: '. $row['user_id'] .' Level: '. $row['level'] .' Sub: '. $row['is_subordinate'] .' Current Level: '. $current_level, __FILE__, __LINE__, __METHOD__,10);
				if (  $row['level'] == $current_level AND $row['is_subordinate'] == 0 ) {
					$retarr['current_level'][] = $row['user_id'];
				} elseif ( $row['level'] < $current_level AND $row['is_subordinate'] == 0 ) {
					$retarr['parent_level'][] = $row['user_id'];
				} elseif ( $row['level'] > $current_level AND $row['is_subordinate'] == 1 ) {
					//Only ever show suborindates at child levels, this fixes the bug where the currently logged in user would see their own requests
					//in the authorization list.
					$retarr['child_level'][] = $row['user_id'];
				} else {
					//Debug::Text(' Skipping row...', __FILE__, __LINE__, __METHOD__,10);
				}

				$i++;
			}

			$retarr['current_level'] = array_unique( $retarr['current_level'] );
			$retarr['parent_level'] = array_unique( $retarr['parent_level'] );
			$retarr['child_level'] = array_unique( $retarr['child_level'] );

			//Debug::Arr($retarr ,' aChildren of User: '. $user_id .' At Level: '. $level, __FILE__, __LINE__, __METHOD__,10);

			return $retarr;

		}

		return FALSE;
	}

	function getByUserIdAndObjectTypeIDAndLevelAndHierarchyControlIDs( $user_id, $object_type_id, $level = 1, $hierarchy_control_ids, $recursive = TRUE ) {
		if ( $user_id == '' ) {
			return FALSE;
		}

		if ( !is_numeric($level) ) {
			return FALSE;
		}

		$min_level = $level-1;
		if ( $min_level <= 0 ) {
			$min_level = 0;
		}

		//This should have two modes, one where it returns just the immediate child level, and one that returns all children "recursively".
		if ( $recursive == TRUE ) {
			$max_level = 99;
		} else {
			$max_level = $level+1;
		}
		Debug::Text(' User ID: '. $user_id .' Object Type ID: '. $object_type_id .' Level: '. $level .' Min Level: '. $min_level .' Max Level: '. $max_level, __FILE__, __LINE__, __METHOD__,10);

		$retarr = array( 'current_level' => array(), 'parent_level' => array(), 'child_level' => array() );

		$hcf = new HierarchyControlFactory();
		$hlf = new HierarchyLevelFactory();
		$huf = new HierarchyUserFactory();
		$hotf = new HierarchyObjectTypeFactory();

		$ph = array();

		//UNION two queries together, the first query gets all superiors, one level above, and all levels below.
		//The 2nd query gets all subordinates.
		$query = '
				select * from  (
						select 	x.hierarchy_control_id,
								x.user_id,
								x.level,
								0 as is_subordinate
						from 	'. $hlf->getTable() .' as x
						LEFT JOIN '. $hcf->getTable() .' as y ON x.hierarchy_control_id = y.id
						LEFT JOIN '. $hotf->getTable() .' as y2 ON x.hierarchy_control_id = y2.hierarchy_control_id
						LEFT JOIN '. $hlf->getTable() .' as z ON x.hierarchy_control_id = z.hierarchy_control_id AND z.user_id = '. (int)$user_id .'
						where
							y2.object_type_id in ('. $this->getListSQL($object_type_id, $ph) .')
							AND x.level >= z.level-1
							AND ( x.deleted = 0 AND y.deleted = 0 AND z.deleted = 0 )

						UNION ALL

						select
								n.hierarchy_control_id,
								n.user_id,
								(
									select max(level)+1
									from '. $hlf->getTable() .' as z
									where z.hierarchy_control_id = n.hierarchy_control_id AND z.deleted = 0
								) as level,
								1 as is_subordinate
						from 	'. $huf->getTable() .' as n
						LEFT JOIN '. $hcf->getTable() .' as o ON n.hierarchy_control_id = o.id
						LEFT JOIN '. $hotf->getTable() .' as p ON n.hierarchy_control_id = p.hierarchy_control_id
						LEFT JOIN '. $hlf->getTable() .' as z ON n.hierarchy_control_id = z.hierarchy_control_id AND z.user_id = '. (int)$user_id .'
						where
							p.object_type_id in ('. $this->getListSQL($object_type_id, $ph) .')
							AND ( o.deleted = 0 AND z.deleted = 0 )
					) as tmp
					WHERE
						hierarchy_control_id in ('. $this->getListSQL($hierarchy_control_ids, $ph) .')
						AND ( level >= '. (int)$min_level .' AND level <= '. (int)$max_level.' )
					ORDER BY level ASC, user_id ASC
				';

		//Debug::Text(' Query: '. $query, __FILE__, __LINE__, __METHOD__,10);
		$rs = $this->db->Execute($query, $ph);
		//Debug::Text(' Rows: '. $rs->RecordCount(), __FILE__, __LINE__, __METHOD__,10);

		if ( $rs->RecordCount() > 0 ) {
			$current_level = $level;
			$i=0;
			foreach( $rs as $row ) {
				Debug::Text(' User ID: '. $row['user_id'] .' Level: '. $row['level'] .' Sub: '. $row['is_subordinate'] .' Current Level: '. $current_level, __FILE__, __LINE__, __METHOD__,10);
				if (  $row['level'] == $current_level AND $row['is_subordinate'] == 0 ) {
					$retarr['current_level'][] = $row['user_id'];
				} elseif ( $row['level'] < $current_level AND $row['is_subordinate'] == 0 ) {
					$retarr['parent_level'][] = $row['user_id'];
				} elseif ( $row['level'] > $current_level AND $row['is_subordinate'] == 1 ) {
					//Only ever show suborindates at child levels, this fixes the bug where the currently logged in user would see their own requests
					//in the authorization list.
					$retarr['child_level'][] = $row['user_id'];
				} else {
					//Debug::Text(' Skipping row...', __FILE__, __LINE__, __METHOD__,10);
				}

				$i++;
			}

			$retarr['current_level'] = array_unique( $retarr['current_level'] );
			$retarr['parent_level'] = array_unique( $retarr['parent_level'] );
			$retarr['child_level'] = array_unique( $retarr['child_level'] );

			Debug::Arr($retarr ,' aChildren of User: '. $user_id .' At Level: '. $level, __FILE__, __LINE__, __METHOD__,10);

			return $retarr;

		}

		return FALSE;
	}

	function getHierarchyParentByCompanyIdAndUserIdAndObjectTypeID( $company_id, $user_id, $object_type_id = 100, $immediate_parents_only = TRUE, $include_levels = TRUE ) {
		if ( $company_id == '' ) {
			return FALSE;
		}

		if ( $user_id == '' ) {
			return FALSE;
		}

		if ( $object_type_id == '' ) {
			return FALSE;
		}

		$retval = FALSE;

		//Parents are only considered if an employee is explicitly assigned to a hierarchy as a subordinate.
		//This does not take into account an employee being in the middle of a hierarchy but not assigned to it as a suborindate.
		//This is because the same employee can be assigned as a superior to many hierarchies, but only to a single hierarchy (of the same object type) if they are subordinates.

		$uf = new UserFactory();
		$hlf = new HierarchyLevelFactory();
		$huf = new HierarchyUserFactory();
		$hotf = new HierarchyObjectTypeFactory();
		$hcf = new HierarchyControlFactory();

		$ph = array(
					'user_id' => $user_id,
					'company_id' => $company_id,
					//'object_type_id' => $object_type_id,
					);

		$query = '
						select w.level, w.user_id
						from '. $hlf->getTable() .' as w
						LEFT JOIN '. $huf->getTable() .' as x ON w.hierarchy_control_id = x.hierarchy_control_id
						LEFT JOIN '. $hotf->getTable() .' as y ON w.hierarchy_control_id = y.hierarchy_control_id
						LEFT JOIN '. $uf->getTable() .' as z ON x.user_id = z.id
						LEFT JOIN '. $hcf->getTable() .' as e ON w.hierarchy_control_id = e.id
						WHERE
							x.user_id = ?
							AND z.company_id = ?
							AND y.object_type_id in ('. $this->getListSQL($object_type_id, $ph) .')
							AND ( w.deleted = 0 AND e.deleted = 0 )
						ORDER BY w.level DESC
					';

		//Debug::Text(' Query: '. $query, __FILE__, __LINE__, __METHOD__,10);
		$rs = $this->db->Execute($query, $ph);
		//Debug::Text(' Rows: '. $rs->RecordCount(), __FILE__, __LINE__, __METHOD__,10);

		if ( $rs->RecordCount() > 0 ) {
			$valid_level = FALSE;
			foreach( $rs as $row ) {
				if ( $immediate_parents_only == TRUE ) {
					//Even if immediate_parents_only is set, we need to return all parents at the same level.
					//Prior to v3.1 we just returned a single parent.
					if ( $valid_level === FALSE OR $valid_level == $row['level'] ) {
						$retval[] = (int)$row['user_id'];

						if ( $valid_level === FALSE ) {
							$valid_level = $row['level'];
						}
					}
				} else {
					if ( $include_levels == TRUE ) {
						$retval[(int)$row['level']][] = (int)$row['user_id'];
					} else {
						$retval[] = (int)$row['user_id'];
					}
				}
			}
			if ( $immediate_parents_only == FALSE ) {
				ksort($retval);
			}
		}

		return $retval;
	}

	function getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $company_id, $user_id, $object_type_id = 100 ) {
		global $profiler;
		$profiler->startTimer( "getPermissionHierarchyChildrenByCompanyIdAndUserId" );

		if ( $company_id == '' ) {
			return FALSE;
		}

		if ( $user_id == '' ) {
			return FALSE;
		}

		if ( $object_type_id == '' ) {
			return FALSE;
		}

		$retval = FALSE;

		$uf = new UserFactory();
		$hlf = new HierarchyLevelFactory();
		$huf = new HierarchyUserFactory();
		$hotf = new HierarchyObjectTypeFactory();
		$hcf = new HierarchyControlFactory();

		//When it comes to permissions we only consider subordinates, not other supervisors/managers in the hierarchy.

		$ph = array(
					'user_id' => $user_id,
					'company_id' => $company_id,
					//'object_type_id' => $object_type_id,
					//'user_idb' => $user_id,
					//'object_type_idb' => $object_type_id,
					//'company_idb' => $company_id,
					);


		//w.user_id != x.user_id, is there to make sure we exclude the current user from the subordinate list,
		//as we now allow superiors to also be subordinates in the same hierarchy. 
		$query = '
						select w.user_id as user_id
						from '. $huf->getTable() .' as w
						LEFT JOIN '. $hlf->getTable() .' as x ON w.hierarchy_control_id = x.hierarchy_control_id
						LEFT JOIN '. $hotf->getTable() .' as y ON w.hierarchy_control_id = y.hierarchy_control_id
						LEFT JOIN '. $uf->getTable() .' as z ON x.user_id = z.id
						LEFT JOIN '. $hcf->getTable() .' as z2 ON w.hierarchy_control_id = z2.id
						WHERE
							x.user_id = ?
							AND z.company_id = ?
							AND y.object_type_id in ('. $this->getListSQL($object_type_id, $ph) .')
							AND w.user_id != x.user_id
							AND ( x.deleted = 0 AND z2.deleted = 0 AND z.deleted = 0 )
					';

		//Debug::Text(' Query: '. $query, __FILE__, __LINE__, __METHOD__,10);
		$rs = $this->db->Execute($query, $ph);
		//Debug::Text(' Rows: '. $rs->RecordCount(), __FILE__, __LINE__, __METHOD__,10);

		if ( $rs->RecordCount() > 0 ) {
			foreach( $rs as $row ) {
				$retval[] = $row['user_id'];
			}
		}

		$profiler->stopTimer( "getPermissionHierarchyChildrenByCompanyIdAndUserId" );
		return $retval;
	}








	//Used by installer to upgrade.
	function getByCompanyIdAndHierarchyControlId( $company_id, $tree_id ) {
		if ( $company_id == '' ) {
			return FALSE;
		}

		if ( $tree_id == '' ) {
			return FALSE;
		}

		$hclf = new HierarchyControlListFactory();
		$hclf->getByIdAndCompanyId($tree_id, $company_id);
		if ( $hclf->getRecordCount() == 0 ) {
			return FALSE;
		}

		return $this->getByHierarchyControlId( $tree_id );
	}

	//Used by installer to upgrade
	function getParentLevelIdArrayByHierarchyControlIdAndUserId($tree_id, $user_id) {
		//This only gets the immediate parents
		if ( $tree_id == '' ) {
			return FALSE;
		}

		if ( $user_id == '' ) {
			return FALSE;
		}

		$this->getFastTreeObject()->setTree( $tree_id );

		//Get the parent, then get the current level from that.
		$parent_id = $this->getFastTreeObject()->getParentId( $user_id );

		$retarr = array();

		$parent_nodes = $this->getCurrentLevelIdArrayByHierarchyControlIdAndUserId($tree_id, $parent_id);
		//Debug::Arr($parent_nodes ,' Parent Nodes', __FILE__, __LINE__, __METHOD__,10);
		if ( is_array($parent_nodes) ) {
			$retarr = $parent_nodes;
		}

		return $retarr;
	}

	//Used by installer to upgrade
	function getCurrentLevelIdArrayByHierarchyControlIdAndUserId($tree_id, $user_id, $ignore_self = FALSE ) {
		if ( $tree_id == '' ) {
			return FALSE;
		}

		if ( $user_id == '' ) {
			return FALSE;
		}

		$this->getFastTreeObject()->setTree( $tree_id );

		$parent_id = $this->getFastTreeObject()->getParentId( $user_id);

		$children = $this->getFastTreeObject()->getAllChildren( $parent_id );
		if ( $children === FALSE ) {
			return FALSE;
		}

		$ids = array_keys( $children );
		//Debug::Arr($ids ,' zNodes at the same level', __FILE__, __LINE__, __METHOD__,10);

		$hslf = new HierarchyShareListFactory();

		//Check if current user is shared, because if it isn't shared, then we can ignore
		//all other shared users in the tree.
		$root_user_id_shared = $hslf->getByHierarchyControlIdAndUserId( $tree_id, $user_id )->getRecordCount();
		Debug::Text('Root User ID: '. $user_id .' Shared: '. (int)$root_user_id_shared, __FILE__, __LINE__, __METHOD__,10);

		$retarr[] = (int)$user_id;
		foreach ( $ids as $id ) {

			$hierarchy_share = $hslf->getByHierarchyControlIdAndUserId( $tree_id, $id )->getCurrent()->isNew();

			if ( $root_user_id_shared == TRUE AND $hierarchy_share === FALSE ) {
				//Debug::Text(' Node IS shared:  '. $id, __FILE__, __LINE__, __METHOD__,10);
				$retarr[] = $id;
			} else {
				//Debug::Text(' Node isnt shared:  '. $id, __FILE__, __LINE__, __METHOD__,10);
			}
		}

		return array_unique($retarr);
	}

}
?>
