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
 * $Revision: 2196 $
 * $Id: APIRecurringScheduleTemplateControl.class.php 2196 2008-10-14 16:08:54Z ipso $
 * $Date: 2008-10-14 09:08:54 -0700 (Tue, 14 Oct 2008) $
 */

/**
 * @package API\Schedule
 */
class APIRecurringScheduleTemplateControl extends APIFactory {
	protected $main_class = 'RecurringScheduleTemplateControlFactory';

	public function __construct() {
		parent::__construct(); //Make sure parent constructor is always called.

		return TRUE;
	}

	/**
	 * Get options for dropdown boxes.
	 * @param string $name Name of options to return, ie: 'columns', 'type', 'status'
	 * @param mixed $parent Parent name/ID of options to return if data is in hierarchical format. (ie: Province)
	 * @return array
	 */
	function getOptions( $name, $parent = NULL ) {
		if ( $name == 'columns'
				AND ( !$this->getPermissionObject()->Check('recurring_schedule_template','enabled')
					OR !( $this->getPermissionObject()->Check('recurring_schedule_template','view') OR $this->getPermissionObject()->Check('recurring_schedule_template','view_own') OR $this->getPermissionObject()->Check('recurring_schedule_template','view_child') ) ) ) {
			$name = 'list_columns';
		}

		return parent::getOptions( $name, $parent );
	}

	/**
	 * Get default recurring_schedule_template_control data for creating new recurring_schedule_template_controles.
	 * @return array
	 */
	function getRecurringScheduleTemplateControlDefaultData() {
		$company_obj = $this->getCurrentCompanyObject();

		Debug::Text('Getting recurring_schedule_template_control default data...', __FILE__, __LINE__, __METHOD__,10);

		$data = array(
						'company_id' => $company_obj->getId(),
					);

		return $this->returnHandler( $data );
	}

	/**
	 * Get recurring_schedule_template_control data for one or more recurring_schedule_template_controles.
	 * @param array $data filter data
	 * @return array
	 */
	function getRecurringScheduleTemplateControl( $data = NULL, $disable_paging = FALSE ) {
		if ( !$this->getPermissionObject()->Check('recurring_schedule_template','enabled')
				OR !( $this->getPermissionObject()->Check('recurring_schedule_template','view') OR $this->getPermissionObject()->Check('recurring_schedule_template','view_own') OR $this->getPermissionObject()->Check('recurring_schedule_template','view_child')  ) ) {
			//return $this->getPermissionObject()->PermissionDenied();
			$data['filter_columns'] = $this->handlePermissionFilterColumns( (isset($data['filter_columns'])) ? $data['filter_columns'] : NULL, Misc::trimSortPrefix( $this->getOptions('list_columns') ) );
		}
		$data = $this->initializeFilterAndPager( $data, $disable_paging );

		//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
		$data['filter_data']['permission_children_ids'] = $this->getPermissionObject()->getPermissionChildren( 'recurring_schedule_template', 'view' );

		$blf = TTnew( 'RecurringScheduleTemplateControlListFactory' );
		$blf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCurrentCompanyObject()->getId(), $data['filter_data'], $data['filter_items_per_page'], $data['filter_page'], NULL, $data['filter_sort'] );
		Debug::Text('Record Count: '. $blf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
		if ( $blf->getRecordCount() > 0 ) {
			$this->getProgressBarObject()->start( $this->getAMFMessageID(), $blf->getRecordCount() );

			$this->setPagerObject( $blf );

			foreach( $blf as $b_obj ) {
				$retarr[] = $b_obj->getObjectAsArray( $data['filter_columns'], $data['filter_data']['permission_children_ids'] );

				$this->getProgressBarObject()->set( $this->getAMFMessageID(), $blf->getCurrentRow() );
			}

			$this->getProgressBarObject()->stop( $this->getAMFMessageID() );

			return $this->returnHandler( $retarr );
		}

		return $this->returnHandler( TRUE ); //No records returned.
	}

	/**
	 * Get only the fields that are common across all records in the search criteria. Used for Mass Editing of records.
	 * @param array $data filter data
	 * @return array
	 */
	function getCommonRecurringScheduleTemplateControlData( $data ) {
		return Misc::arrayIntersectByRow( $this->stripReturnHandler( $this->getRecurringScheduleTemplateControl( $data, TRUE ) ) );
	}

	/**
	 * Validate recurring_schedule_template_control data for one or more recurring_schedule_template_controles.
	 * @param array $data recurring_schedule_template_control data
	 * @return array
	 */
	function validateRecurringScheduleTemplateControl( $data ) {
		return $this->setRecurringScheduleTemplateControl( $data, TRUE );
	}

	/**
	 * Set recurring_schedule_template_control data for one or more recurring_schedule_template_controles.
	 * @param array $data recurring_schedule_template_control data
	 * @return array
	 */
	function setRecurringScheduleTemplateControl( $data, $validate_only = FALSE ) {
		$validate_only = (bool)$validate_only;

		if ( !is_array($data) ) {
			return $this->returnHandler( FALSE );
		}

		if ( !$this->getPermissionObject()->Check('recurring_schedule_template','enabled')
				OR !( $this->getPermissionObject()->Check('recurring_schedule_template','edit') OR $this->getPermissionObject()->Check('recurring_schedule_template','edit_own') OR $this->getPermissionObject()->Check('recurring_schedule_template','edit_child') OR $this->getPermissionObject()->Check('recurring_schedule_template','add') ) ) {
			return  $this->getPermissionObject()->PermissionDenied();
		}

		if ( $validate_only == TRUE ) {
			Debug::Text('Validating Only!', __FILE__, __LINE__, __METHOD__, 10);
		}

		extract( $this->convertToMultipleRecords($data) );
		Debug::Text('Received data for: '. $total_records .' RecurringScheduleTemplateControls', __FILE__, __LINE__, __METHOD__, 10);
		Debug::Arr($data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10);

		$validator_stats = array('total_records' => $total_records, 'valid_records' => 0 );
		if ( is_array($data) ) {
			$this->getProgressBarObject()->start( $this->getAMFMessageID(), $total_records );

			foreach( $data as $key => $row ) {
				$primary_validator = new Validator();
				$lf = TTnew( 'RecurringScheduleTemplateControlListFactory' );
				$lf->StartTransaction();
				if ( isset($row['id']) AND $row['id'] > 0 ) {
					//Modifying existing object.
					//Get recurring_schedule_template_control object, so we can only modify just changed data for specific records if needed.
					$lf->getByIdAndCompanyId( $row['id'], $this->getCurrentCompanyObject()->getId() );
					if ( $lf->getRecordCount() == 1 ) {
						//Object exists, check edit permissions
						if (
							  $validate_only == TRUE
							  OR
								(
								$this->getPermissionObject()->Check('recurring_schedule_template','edit')
									OR ( $this->getPermissionObject()->Check('recurring_schedule_template','edit_own') AND $this->getPermissionObject()->isOwner( $lf->getCurrent()->getCreatedBy(), $lf->getCurrent()->getID() ) === TRUE )
								) ) {

							Debug::Text('Row Exists, getting current data: ', $row['id'], __FILE__, __LINE__, __METHOD__, 10);
							$lf = $lf->getCurrent();
							$row = array_merge( $lf->getObjectAsArray(), $row );
						} else {
							$primary_validator->isTrue( 'permission', FALSE, TTi18n::gettext('Edit permission denied') );
						}
					} else {
						//Object doesn't exist.
						$primary_validator->isTrue( 'id', FALSE, TTi18n::gettext('Edit permission denied, record does not exist') );
					}
				} else {
					//Adding new object, check ADD permissions.
					$primary_validator->isTrue( 'permission', $this->getPermissionObject()->Check('recurring_schedule_template','add'), TTi18n::gettext('Add permission denied') );
				}
				Debug::Arr($row, 'Data: ', __FILE__, __LINE__, __METHOD__, 10);

				$is_valid = $primary_validator->isValid();
				if ( $is_valid == TRUE ) { //Check to see if all permission checks passed before trying to save data.
					Debug::Text('Setting object data...', __FILE__, __LINE__, __METHOD__, 10);

					$lf->setObjectFromArray( $row );

					//Force Company ID to current company.
					$lf->setCompany( $this->getCurrentCompanyObject()->getId() );

					$is_valid = $lf->isValid();
					if ( $is_valid == TRUE ) {
						Debug::Text('Saving data...', __FILE__, __LINE__, __METHOD__, 10);
						if ( $validate_only == TRUE ) {
							$save_result[$key] = TRUE;
						} else {
							$save_result[$key] = $lf->Save();
						}
						$validator_stats['valid_records']++;
					}
				}

				if ( $is_valid == FALSE ) {
					Debug::Text('Data is Invalid...', __FILE__, __LINE__, __METHOD__, 10);

					$lf->FailTransaction(); //Just rollback this single record, continue on to the rest.

					if ( $primary_validator->isValid() == FALSE ) {
						$validator[$key] = $primary_validator->getErrorsArray();
					} else {
						$validator[$key] = $lf->Validator->getErrorsArray();
					}
				} elseif ( $validate_only == TRUE ) {
					$lf->FailTransaction();
				}


				$lf->CommitTransaction();

				$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
			}

			$this->getProgressBarObject()->stop( $this->getAMFMessageID() );

			if ( $validator_stats['valid_records'] > 0 AND $validator_stats['total_records'] == $validator_stats['valid_records'] ) {
				if ( $validator_stats['total_records'] == 1 ) {
					return $this->returnHandler( $save_result[$key] ); //Single valid record
				} else {
					return $this->returnHandler( TRUE, 'SUCCESS', TTi18n::getText('MULTIPLE RECORDS SAVED'), $save_result, $validator_stats ); //Multiple valid records
				}
			} else {
				return $this->returnHandler( FALSE, 'VALIDATION', TTi18n::getText('INVALID DATA'), $validator, $validator_stats );
			}
		}

		return $this->returnHandler( FALSE );
	}

	/**
	 * Delete one or more recurring_schedule_template_controls.
	 * @param array $data recurring_schedule_template_control data
	 * @return array
	 */
	function deleteRecurringScheduleTemplateControl( $data ) {
		if ( is_numeric($data) ) {
			$data = array($data);
		}

		if ( !is_array($data) ) {
			return $this->returnHandler( FALSE );
		}

		if ( !$this->getPermissionObject()->Check('recurring_schedule_template','enabled')
				OR !( $this->getPermissionObject()->Check('recurring_schedule_template','delete') OR $this->getPermissionObject()->Check('recurring_schedule_template','delete_own') OR $this->getPermissionObject()->Check('recurring_schedule_template','delete_child') ) ) {
			return  $this->getPermissionObject()->PermissionDenied();
		}

		Debug::Text('Received data for: '. count($data) .' RecurringScheduleTemplateControls', __FILE__, __LINE__, __METHOD__, 10);
		Debug::Arr($data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10);

		$total_records = count($data);
        $validator_stats = array('total_records' => $total_records, 'valid_records' => 0 );
		if ( is_array($data) ) {
			$this->getProgressBarObject()->start( $this->getAMFMessageID(), $total_records );

			foreach( $data as $key => $id ) {
				$primary_validator = new Validator();
				$lf = TTnew( 'RecurringScheduleTemplateControlListFactory' );
				$lf->StartTransaction();
				if ( is_numeric($id) ) {
					//Modifying existing object.
					//Get recurring_schedule_template_control object, so we can only modify just changed data for specific records if needed.
					$lf->getByIdAndCompanyId( $id, $this->getCurrentCompanyObject()->getId() );
					if ( $lf->getRecordCount() == 1 ) {
						//Object exists, check edit permissions
						if ( $this->getPermissionObject()->Check('recurring_schedule_template','delete')
								OR ( $this->getPermissionObject()->Check('recurring_schedule_template','delete_own') AND $this->getPermissionObject()->isOwner( $lf->getCurrent()->getCreatedBy(), $lf->getCurrent()->getID() ) === TRUE ) ) {
							Debug::Text('Record Exists, deleting record: ', $id, __FILE__, __LINE__, __METHOD__, 10);
							$lf = $lf->getCurrent();
						} else {
							$primary_validator->isTrue( 'permission', FALSE, TTi18n::gettext('Delete permission denied') );
						}
					} else {
						//Object doesn't exist.
						$primary_validator->isTrue( 'id', FALSE, TTi18n::gettext('Delete permission denied, record does not exist') );
					}
				} else {
					$primary_validator->isTrue( 'id', FALSE, TTi18n::gettext('Delete permission denied, record does not exist') );
				}

				//Debug::Arr($lf, 'AData: ', __FILE__, __LINE__, __METHOD__, 10);

				$is_valid = $primary_validator->isValid();
				if ( $is_valid == TRUE ) { //Check to see if all permission checks passed before trying to save data.
					Debug::Text('Attempting to delete record...', __FILE__, __LINE__, __METHOD__, 10);
					$lf->setDeleted(TRUE);

					$is_valid = $lf->isValid();
					if ( $is_valid == TRUE ) {
						Debug::Text('Record Deleted...', __FILE__, __LINE__, __METHOD__, 10);
						$save_result[$key] = $lf->Save();
						$validator_stats['valid_records']++;
					}
				}

				if ( $is_valid == FALSE ) {
					Debug::Text('Data is Invalid...', __FILE__, __LINE__, __METHOD__, 10);

					$lf->FailTransaction(); //Just rollback this single record, continue on to the rest.

					if ( $primary_validator->isValid() == FALSE ) {
						$validator[$key] = $primary_validator->getErrorsArray();
					} else {
						$validator[$key] = $lf->Validator->getErrorsArray();
					}
				}

				$lf->CommitTransaction();

				$this->getProgressBarObject()->set( $this->getAMFMessageID(), $key );
			}

			$this->getProgressBarObject()->stop( $this->getAMFMessageID() );

			if ( $validator_stats['valid_records'] > 0 AND $validator_stats['total_records'] == $validator_stats['valid_records'] ) {
				if ( $validator_stats['total_records'] == 1 ) {
					return $this->returnHandler( $save_result[$key] ); //Single valid record
				} else {
					return $this->returnHandler( TRUE, 'SUCCESS', TTi18n::getText('MULTIPLE RECORDS SAVED'), $save_result, $validator_stats ); //Multiple valid records
				}
			} else {
				return $this->returnHandler( FALSE, 'VALIDATION', TTi18n::getText('INVALID DATA'), $validator, $validator_stats );
			}
		}

		return $this->returnHandler( FALSE );
	}

	/**
	 * Copy one or more recurring_schedule_template_controles.
	 * @param array $data recurring_schedule_template_control IDs
	 * @return array
	 */
	function copyRecurringScheduleTemplateControl( $data ) {
		if ( is_numeric($data) ) {
			$data = array($data);
		}

		if ( !is_array($data) ) {
			return $this->returnHandler( FALSE );
		}

		Debug::Text('Received data for: '. count($data) .' RecurringScheduleTemplateControls', __FILE__, __LINE__, __METHOD__, 10);
		Debug::Arr($data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10);

		$src_rows = $this->stripReturnHandler( $this->getRecurringScheduleTemplateControl( array('filter_data' => array('id' => $data) ), TRUE ) );
		if ( is_array( $src_rows ) AND count($src_rows) > 0 ) {
			Debug::Arr($src_rows, 'SRC Rows: ', __FILE__, __LINE__, __METHOD__, 10);
			foreach( $src_rows as $key => $row ) {
				unset($src_rows[$key]['id'] ); //Clear fields that can't be copied
				$src_rows[$key]['name'] = Misc::generateCopyName( $row['name'] ); //Generate unique name
			}
			//Debug::Arr($src_rows, 'bSRC Rows: ', __FILE__, __LINE__, __METHOD__, 10);

			$recurring_schedule_template_control_id = $this->setRecurringScheduleTemplateControl( $src_rows ); //Save copied rows
			Debug::Text('New Recurring Schedule Template Control ID: '. $recurring_schedule_template_control_id, __FILE__, __LINE__, __METHOD__, 10);

			$rstlf = TTnew( 'APIRecurringScheduleTemplate' );
			$template_src_rows = $rstlf->getRecurringScheduleTemplate( array('filter_data' => array('recurring_schedule_template_control_id' => $data) ), TRUE );
			if ( is_array( $template_src_rows ) AND count($template_src_rows) > 0 ) {
				//Debug::Arr($template_src_rows, 'TEMPLATE SRC Rows: ', __FILE__, __LINE__, __METHOD__, 10);
				foreach( $template_src_rows as $key => $row ) {
					unset($template_src_rows[$key]['id']); //Clear fields that can't be copied
					$template_src_rows[$key]['recurring_schedule_template_control_id'] = $recurring_schedule_template_control_id;
				}

				$rstlf->setRecurringScheduleTemplate( $template_src_rows ); //Save copied rows
			}

			return $this->returnHandler( $recurring_schedule_template_control_id );

		}

		return $this->returnHandler( FALSE );
	}
}
?>
