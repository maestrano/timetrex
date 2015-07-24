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
abstract class APIFactory {

	public $data = array();

	protected $main_class_obj = NULL;

	protected $AMF_message_id = NULL;

	protected $pager_obj = NULL;

	protected $current_company = NULL;
	protected $current_user = NULL;
	protected $current_user_prefs = NULL;
	protected $permission = NULL;

	protected $progress_bar_obj = NULL;

	function __construct() {
		global $current_company, $current_user, $current_user_prefs;

		$this->current_company = $current_company;
		$this->current_user = $current_user;
		$this->current_user_prefs = $current_user_prefs;

		$this->permission = new Permission();
		return TRUE;
	}

	function getProtocolVersion() {
		if ( isset($_GET['v']) AND $_GET['v'] != '' ) {
			return (int)$_GET['v'];	 //1=Initial, 2=Always return detailed
		}

		return 1;
	}
	
	//Returns the AMF messageID for each individual call.
	function getAMFMessageID() {
		if ( $this->AMF_message_id != NULL ) {
			return $this->AMF_message_id;
		}
		return FALSE;
	}
	function setAMFMessageID( $id ) {
		if ( $id != '' ) {
			global $amf_message_id; //Make this global so Debug() class can reference it on Shutdown()
			$this->AMF_message_id = $amf_message_id = $id;
			return TRUE;
		}

		return FALSE;
	}

	function getCurrentCompanyObject() {
		if ( is_object($this->current_company) ) {
			return $this->current_company;
		}
		return FALSE;
	}
	function getCurrentUserObject() {
		if ( is_object($this->current_user) ) {
			return $this->current_user;
		}
		return FALSE;
	}
	function getCurrentUserPreferenceObject() {
		if ( is_object($this->current_user_prefs) ) {
			return $this->current_user_prefs;
		}
		return FALSE;
	}
	function getPermissionObject() {
		if ( is_object($this->permission) ) {
			return $this->permission;
		}
		return FALSE;
	}

	function getProgressBarObject() {
		if	( !is_object( $this->progress_bar_obj ) ) {
			$this->progress_bar_obj = new ProgressBar();
		}

		return $this->progress_bar_obj;
	}

	function setPagerObject( $lf ) {
		if	( is_object( $lf ) ) {
			$this->pager_obj = new Pager($lf);
		}

		return TRUE;
	}
	function getPagerData() {
		if ( is_object( $this->pager_obj ) ) {
			return $this->pager_obj->getPageVariables();
		}

		return FALSE;
	}

	//Allow storing the main class object persistently in memory, so we can build up other variables to help out things like getOptions()
	//Mainly used for the APIReport class.
	function setMainClassObject( $obj ) {
		if ( is_object( $obj ) ) {
			$this->main_class_obj =	$obj;
			return TRUE;
		}

		return FALSE;
	}
	function getMainClassObject() {
		if ( !is_object( $this->main_class_obj ) ) {
			$this->main_class_obj = new $this->main_class;
			return $this->main_class_obj;
		} else {
			return $this->main_class_obj;
		}

		return FALSE;
	}

	function initializeFilterAndPager( $data, $disable_paging = FALSE ) {
		//Preset values for LF search function.
		$data = Misc::preSetArrayValues( $data, array( 'filter_data', 'filter_columns', 'filter_items_per_page', 'filter_page', 'filter_sort' ), NULL );

		if ( $disable_paging == FALSE AND (int)$data['filter_items_per_page'] <= 0 ) { //Used to check $data['filter_items_per_page'] === NULL
			$data['filter_items_per_page'] = $this->getCurrentUserPreferenceObject()->getItemsPerPage();
		}

		if ( $disable_paging == TRUE ) {
			$data['filter_items_per_page'] = $data['filter_page'] = FALSE;
		}

		//Debug::Arr($data, 'Getting Data: ', __FILE__, __LINE__, __METHOD__, 10);

		return $data;
	}

	//In cases where data can be displayed in just a list_view (dropdown boxes), ie: branch, department, job, task in In/Out punch view
	//restrict the dropdown box to just a subset of columns, so not all data is shown.
	function handlePermissionFilterColumns( $filter_columns, $allowed_columns ) {
		//Always allow these columns to be returned.
		$allowed_columns['id'] = TRUE;
		$allowed_columns['is_owner'] = TRUE;
		$allowed_columns['is_child'] = TRUE;
		
		if ( is_array($filter_columns) ) {
			$retarr = Misc::arrayIntersectByKey( $allowed_columns, $filter_columns );
		} else {
			$retarr = $allowed_columns;
		}

		//If no valid columns are being returned, revert back to allowed columns.
		//Never return *NULL* or a blank array from here, as that will allow all columns to be displayed.
		if ( !is_array($retarr) ) {
			//Return all allowed columns
			$retarr = $allowed_columns;

		}

		return $retarr;
	}

	function convertToSingleRecord( $data ) {
		if ( isset($data[0]) AND !isset($data[1]) ) {
			return $data[0];
		} else {
			return $data;
		}
	}
	function convertToMultipleRecords( $data ) {
		if ( isset($data[0]) AND is_array($data[0]) ) {
			$retarr = array(
							'data' => $data,
							'total_records' => count($data)
							);
		} else {
			$tmp_data[0] = $data;

			$retarr = array(
							'data' => $tmp_data,
							'total_records' => 1
							);
		}

		//Debug::Arr($retarr, 'Array: ', __FILE__, __LINE__, __METHOD__, 10);

		return $retarr;
	}

	function getNextInsertID() {
		return $this->getMainClassObject()->getNextInsertId();
	}

	function getPermissionChildren() {
		return $this->getPermissionObject()->getPermissionHierarchyChildren( $this->getCurrentCompanyObject()->getId(), $this->getCurrentUserObject()->getId() );
		/*
		$hlf = TTnew( 'HierarchyListFactory' );
		$permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $this->getCurrentCompanyObject()->getId(), $this->getCurrentUserObject()->getId(), 100 );

		Debug::Arr($permission_children_ids, 'Permission Child IDs: ', __FILE__, __LINE__, __METHOD__, 10);

		return $permission_children_ids;
		*/
	}

	//Controls returning information to client in a standard format.
	//FIXME: Need to return the original request (with any modified values due to restrictions/validation issues)
	//		 Also need to return paging data variables here too, as JSON can't make multiple calls.
	//		 In order to do this we need to always return a special data structure that includes this information.
	//		 static function returnHandler( $retval = TRUE, $args = array( 'code' => FALSE, 'description' => FALSE, 'details' = FALSE, 'validator_stats' => FALSE, 'user_generic_status_batch_id' => FALSE ) ) {
	//		 The above will require too many changes, just add two more variables at the end, as it will only really be used by API->get*() functions.
	//FIXME: Use a requestHandler() to handle all input requests, so we can parse out things like validate_only, ignore_warning (for user acknowledgable warnings) and handling all parameter parsing in a central place.
	//		 static function returnHandler( $retval = TRUE, $code = FALSE, $description = FALSE, $details = FALSE, $validator_stats = FALSE, $user_generic_status_batch_id = FALSE, $request = FALSE, $pager = FALSE ) {
	function returnHandler( $retval = TRUE, $code = FALSE, $description = FALSE, $details = FALSE, $validator_stats = FALSE, $user_generic_status_batch_id = FALSE, $request_data = FALSE ) {
		if ( $this->getProtocolVersion() == 1 ) {
			if ( $retval === FALSE OR ( $retval === TRUE AND $code !== FALSE ) OR ( $user_generic_status_batch_id !== FALSE ) ) {
				if ( $retval === FALSE ) {
					if	( $code == '' ) {
						$code = 'GENERAL';
					}
					if ( $description == '' ) {
						$description = 'Insufficient data to carry out action';
					}
				} elseif ( $retval === TRUE ) {
					if	( $code == '' ) {
						$code = 'SUCCESS';
					}
				}

				$validator_stats = Misc::preSetArrayValues( $validator_stats, array( 'total_records', 'valid_records', 'invalids_records' ), 0 );

				$retarr = array(
								'api_retval' => $retval,
								'api_details' => array(
												'code' => $code,
												'description' => $description,
												'record_details' => array(
																		'total' => $validator_stats['total_records'],
																		'valid' => $validator_stats['valid_records'],
																		'invalid' => ($validator_stats['total_records'] - $validator_stats['valid_records'])
																		),
												'user_generic_status_batch_id' => $user_generic_status_batch_id,
												'details' => $details,
												)
								);

				if ( $retval === FALSE ) {
					Debug::Arr($retarr, 'returnHandler v1 ERROR: '. (int)$retval, __FILE__, __LINE__, __METHOD__, 10);
				}

				//Handle progress bar here, make sure they are stopped and if an error occurs display the error.
				if ( $retval === FALSE ) {
					$this->getProgressBarObject()->error( $this->getAMFMessageID(), $description );
				} else {
					$this->getProgressBarObject()->stop( $this->getAMFMessageID() );
				}

				return $retarr;
			}

			//No errors, or additional information, return unmodified data.
			return $retval;
		} else {
			if ( $retval === FALSE ) {
				if	( $code == '' ) {
					$code = 'GENERAL';
				}
				if ( $description == '' ) {
					$description = 'Insufficient data to carry out action';
				}
			} elseif ( $retval === TRUE ) {
				if	( $code == '' ) {
					$code = 'SUCCESS';
				}
			}

			$validator_stats = Misc::preSetArrayValues( $validator_stats, array( 'total_records', 'valid_records', 'invalids_records' ), 0 );

			$retarr = array(
							'api_retval' => $retval,
							'api_details' => array(
											'code' => $code,
											'description' => $description,
											'record_details' => array(
																	'total' => $validator_stats['total_records'],
																	'valid' => $validator_stats['valid_records'],
																	'invalid' => ($validator_stats['total_records'] - $validator_stats['valid_records'])
																	),
											'user_generic_status_batch_id' => $user_generic_status_batch_id,
											//Allows the API to modify the original request data to send back to the UI for notifying the user.
											//We would like to implement validation on non-set*() calls as well perhaps?
											'request' => $request_data,
											'pager' => $this->getPagerData(),
											'details' => $details,
											)
							);

			if ( $retval === FALSE ) {
				Debug::Arr($retarr, 'returnHandler v2 ERROR: '. (int)$retval, __FILE__, __LINE__, __METHOD__, 10);
			}

			//Handle progress bar here, make sure they are stopped and if an error occurs display the error.
			if ( $retval === FALSE ) {
				$this->getProgressBarObject()->start( $this->getAMFMessageID(), 9999, 9999, $description );
			} else {
				$this->getProgressBarObject()->stop( $this->getAMFMessageID() );
			}

			//Debug::Arr($retarr, 'returnHandler: '. (int)$retval, __FILE__, __LINE__, __METHOD__, 10);
			return $retarr;
		}
	}
	function stripReturnHandler( $retarr ) {
		if ( isset($retarr['api_retval']) ) {
			return $retarr['api_retval'];
		}

		return $retarr;
	}

	//Bridge to main class getOptions factory.
	function getOptions( $name, $parent = NULL ) {
		if ( method_exists($this->getMainClassObject(), 'getOptions') ) {
			return $this->getMainClassObject()->getOptions($name, $parent);
		} else {
			Debug::Text('getOptions() function does not exist for object: '. get_class( $this->getMainClassObject() ), __FILE__, __LINE__, __METHOD__, 10);
		}

		return FALSE;
	}

	//Bridge to main class getVariableToFunctionMap factory.
	function getVariableToFunctionMap( $name, $parent = NULL ) {
		return $this->getMainClassObject()->getVariableToFunctionMap($name, $parent);
	}
}
?>
