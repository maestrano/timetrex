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
 * $Revision: 4925 $
 * $Id: UserReviewControlFactory.class.php 4925 2011-07-04 18:43:18Z ipso $
 * $Date: 2011-07-05 02:43:18 +0800 (Tue, 05 Jul 2011) $
 */

/**
 * @package Module_KPI
 */
class UserReviewControlFactory extends Factory {
	protected $table = 'user_review_control';
	protected $pk_sequence_name = 'user_review_control_id_seq'; //PK Sequence name
    protected $user_obj = NULL;
	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
            case 'type':
                $retval = array(
                                10 => TTi18n::gettext('Accolade'),
                                15 => TTi18n::gettext('Discipline'),
                                20 => TTi18n::gettext('Review (General)'),
                                25 => TTi18n::gettext('Review (Wage)'),
                                30 => TTi18n::gettext('Review (Performance)'),
                                35 => TTi18n::gettext('Accident/Injury'),
								37 => TTi18n::gettext('Background Check'),
								38 => TTi18n::gettext('Drug Test'),
                                40 => TTi18n::gettext('Entrance Interview'),
                                45 => TTi18n::gettext('Exit Interview'),
                            );
                break;
            case 'term':
                $retval = array(
                                10 => TTi18n::gettext('Positive'),
                                20 => TTi18n::gettext('Neutral'),
                                30 => TTi18n::gettext('Negative'),
                            );
                break;
            case 'severity':
                $retval = array(
                               10 => TTi18n::gettext('Normal'),
                               20 => TTi18n::gettext('Low'),
                               30 => TTi18n::gettext('Medium'),
                               40 => TTi18n::gettext('High'),
                               50 => TTi18n::gettext('Critical'),
                            );
                break;
            case 'status':
                $retval = array(
                                10 => TTi18n::gettext('Scheduled'),
                                20 => TTi18n::gettext('Being Reviewed'),
                                30 => TTi18n::gettext('Complete'),
                            );
                break;
			case 'columns':
				$retval = array(
                                        '-4070-user' => TTi18n::gettext('Employee Name'),
                                        '-4080-reviewer_user' => TTi18n::gettext('Reviewer Name'),
                                        '-1170-start_date' => TTi18n::gettext('Start Date'),
                                        '-1180-end_date' => TTi18n::gettext('End Date'),
                                        '-4090-due_date' => TTi18n::gettext('Due Date'),
                                        '-1040-type' => TTi18n::gettext('Type'),
                                        '-1060-term' => TTi18n::gettext('Terms'),
                                        '-1010-severity' => TTi18n::gettext('Severity/Importance'),
                                        '-1020-status' => TTi18n::gettext('Status'),
                                        '-1050-rating' => TTi18n::gettext('Overall Rating'),
                                        '-1200-note' => TTi18n::gettext('Notes'),
                                        '-1300-tag' => TTi18n::gettext('Tags'),

										'-2000-created_by' => TTi18n::gettext('Created By'),
										'-2010-created_date' => TTi18n::gettext('Created Date'),
										'-2020-updated_by' => TTi18n::gettext('Updated By'),
										'-2030-updated_date' => TTi18n::gettext('Updated Date'),
							);
				break;
			case 'list_columns':
				$retval = Misc::arrayIntersectByKey( $this->getOptions('default_display_columns'), Misc::trimSortPrefix( $this->getOptions('columns') ) );
				break;
			case 'default_display_columns': //Columns that are displayed by default.
				$retval = array(
								'user',
                                'reviewer_user',
                                'type',
                                'term',
                                'severity',
                                'start_date',
                                'end_date',
                                'due_date',
								);
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = array();
				break;
		}

		return $retval;
	}

	function _getVariableToFunctionMap( $data ) {
		$variable_function_map = array(
										'id' => 'ID',
										'user_id' => 'User',
                                        'user' => FALSE,
                                        'reviewer_user_id' => 'ReviewerUser',
                                        'reviewer_user' => FALSE,
                                        'type_id' => 'Type',
                                        'type' => FALSE,
                                        'term_id' => 'Term',
                                        'term' => FALSE,
                                        'severity_id' => 'Severity',
                                        'severity' => FALSE,
                                        'status_id' => 'Status',
										'status' => FALSE,
                                        'start_date' => 'StartDate',
                                        'end_date' => 'EndDate',
                                        'due_date' => 'DueDate',
                                        'rating' => 'Rating',
                                        'note' => 'Note',
                                        'tag' => 'Tag',

										'deleted' => 'Deleted',
										);
		return $variable_function_map;
	}

    function getUserObject() {
        return $this->getGenericObject( 'UserListFactory', $this->getUser(), 'user_obj' );
	}

    function getUser() {
        if ( isset($this->data['user_id']) ) {
            return $this->data['user_id'];
        }
        return FALSE;
    }
    function setUser($id) {
		$id = trim($id);
		$ulf = TTnew( 'UserListFactory' );
        //$cgmlf = TTnew( 'CompanyGenericMapListFactory' );
		if ( $this->Validator->isResultSetWithRows(	'user_id',
															$ulf->getByID($id),
															TTi18n::gettext('Invalid employee')
															) ) {
                $this->data['user_id'] = $id;

                return TRUE;
		}

		return FALSE;
	}

    function getReviewerUser() {
        if ( isset($this->data['reviewer_user_id']) ) {
            return $this->data['reviewer_user_id'];
        }
        return FALSE;
    }
    function setReviewerUser($id) {
        $id = trim($id);
        $ulf = TTnew( 'UserListFactory' );
        if ( $this->Validator->isResultSetWithRows( 'reviewer_user_id',
                                                    $ulf->getByID($id),
                                                    TTi18n::gettext('Invalid reviewer')
                                                     ) ) {
             $this->data['reviewer_user_id'] = $id;

             return TRUE;
        }

        return FALSE;
    }

    function getType() {
		if ( isset($this->data['type_id']) ) {
			return (int)$this->data['type_id'];
		}

		return FALSE;
	}
	function setType($type) {
		$type = trim($type);

		$key = Option::getByValue($type, $this->getOptions('type') );
		if ($key !== FALSE) {
			$type = $key;
		}

		if ( $this->Validator->inArrayKey(	'type',
											$type,
											TTi18n::gettext('Incorrect Type'),
											$this->getOptions('type')) ) {
			$this->data['type_id'] = $type;
			return TRUE;
		}

		return FALSE;
	}

    function getTerm() {
		if ( isset($this->data['term_id']) ) {
			return (int)$this->data['term_id'];
		}

		return FALSE;
	}
	function setTerm($value) {
		$value = trim($value);

		$key = Option::getByValue($value, $this->getOptions('term') );
		if ($key !== FALSE) {
			$value = $key;
		}

		if ( $this->Validator->inArrayKey(	'term',
											$value,
											TTi18n::gettext('Incorrect Terms'),
											$this->getOptions('term')) ) {
			$this->data['term_id'] = $value;
			return TRUE;
		}

		return FALSE;
	}

    function getSeverity() {
		if ( isset($this->data['severity_id']) ) {
			return $this->data['severity_id'];
		}

		return FALSE;
	}
	function setSeverity($value) {
		$value = trim($value);

		$key = Option::getByValue($value, $this->getOptions('severity') );
		if ($key !== FALSE) {
			$value = $key;
		}

		if ( $this->Validator->inArrayKey(	'severity',
											$value,
											TTi18n::gettext('Incorrect Severity'),
											$this->getOptions('severity')) ) {

			$this->data['severity_id'] = $value;

			return TRUE;
		}

		return FALSE;
	}

    function getStatus() {
		if ( isset($this->data['status_id']) ) {
			return (int)$this->data['status_id'];
		}

		return FALSE;
	}
	function setStatus($status) {
		$status = trim($status);

		$key = Option::getByValue($status, $this->getOptions('status') );
		if ($key !== FALSE) {
			$status = $key;
		}

		if ( $this->Validator->inArrayKey(	'status',
											$status,
											TTi18n::gettext('Incorrect Status'),
											$this->getOptions('status')) ) {
			$this->data['status_id'] = $status;
            Debug::Text('Setting status_id data...    ' . $this->data['status_id'] , __FILE__, __LINE__, __METHOD__, 10);
			return TRUE;
		}

		return FALSE;
	}

    function getStartDate() {
		if ( isset($this->data['start_date']) ) {
			return (int)$this->data['start_date'];
		}

		return FALSE;
	}
	function setStartDate($epoch) {
		$epoch = trim($epoch);

        if ($epoch == '') {
            $epoch == NULL;
        }

        if 	(   $epoch == NULL
                OR
                $this->Validator->isDate(		'start_date',
												$epoch,
												TTi18n::gettext('Incorrect start date'))
		) {

			$this->data['start_date'] = $epoch;

			return TRUE;
		}

		return FALSE;
	}

	function getEndDate() {
		if ( isset($this->data['end_date']) ) {
			return (int)$this->data['end_date'];
		}

		return FALSE;
	}
	function setEndDate($epoch) {
		$epoch = trim($epoch);
        if ($epoch == '') {
            $epoch == NULL;
        }
		if (  $epoch == NULL
                OR
             $this->Validator->isDate(		'end_date',
												$epoch,
												TTi18n::gettext('Incorrect end date'))
		) {

			$this->data['end_date'] = $epoch;

			return TRUE;
		}

		return FALSE;
	}

    function getDueDate() {
        if ( isset($this->data['due_date']) ) {
			return (int)$this->data['due_date'];
		}

		return FALSE;
    }

    function setDueDate($epoch) {
        $epoch = trim($epoch);
        if ( $epoch == '' ) {
            $epoch == NULL;
        }
        if 	(   $epoch == NULL
                OR
            	$this->Validator->isDate(		'due_date',
												$epoch,
												TTi18n::gettext('Incorrect due date'))
		) {

			$this->data['due_date'] = $epoch;

			return TRUE;
		}

		return FALSE;
    }

    function getRating() {
        if ( isset($this->data['rating']) ) {
            return $this->data['rating'];
        }
        return FALSE;
    }

    function setRating($value) {
        $value = trim($value);

        if ( $value == '' ){
			$value = NULL;
		}
        if ( (
                $value == NULL
                OR
                ( $this->Validator->isNumeric(	'rating',
													$value,
													TTi18n::gettext('Rating must only be digits')
                                         )
                 AND
                $this->Validator->isLengthAfterDecimal( 'rating',
													   $value,
													   TTi18n::gettext('Invalid Rating'),
                                                        0,
                                                        2
                                        ) ) )

            ) {
                $this->data['rating'] = $value;
                return  TRUE;
        }

        //$this->data['rating'] = $value;
        //return  TRUE;
        return FALSE;
    }

    function getNote() {
        if ( isset($this->data['note']) ) {
            return $this->data['note'];
        }
        return FALSE;
    }
    function setNote($note) {
        $note = trim($note);

        if (    $note == ''
                OR
                $this->Validator->isLength( 'note',
                                            $note,
                                            TTi18n::gettext('Note is too short or too long'),
                                            2,2048 )  ) {
                $this->data['note'] = $note;
                return  TRUE;
        }

        return FALSE;
    }

    function getTag() {
		//Check to see if any temporary data is set for the tags, if not, make a call to the database instead.
		//postSave() needs to get the tmp_data.
        if ( isset($this->tmp_data['tags']) ) {
			return $this->tmp_data['tags'];
		} elseif ( is_object( $this->getUserObject() ) AND $this->getUserObject()->getCompany() > 0 AND $this->getID() > 0  ) {
			return CompanyGenericTagMapListFactory::getStringByCompanyIDAndObjectTypeIDAndObjectID( $this->getUserObject()->getCompany(), 320, $this->getID() );
		}
		return FALSE;
	}
	function setTag( $tags ) {
		$tags = trim($tags);

		//Save the tags in temporary memory to be committed in postSave()
		$this->tmp_data['tags'] = $tags;

		return TRUE;
	}


	function Validate() {

        $start_date = $this->getStartDate();
        $end_date = $this->getEndDate();
        $due_date = $this->getDueDate();
        if ( $start_date != '' AND $end_date != '' AND $due_date != ''  ) {
            if ( $end_date < $start_date ) {
                $this->Validator->isTrue( 'end_date',
                                            FALSE,
                                            TTi18n::gettext('End date should be after start date')
                 );
            }
            if ( $due_date < $start_date ) {
                $this->Validator->isTrue( 'due_date',
                                            FALSE,
                                            TTi18n::gettext('Due date should be after start date')
                 );
            }
        }
		return TRUE;
	}

	function preSave() {

		return TRUE;
	}

	function postSave() {
	    $this->removeCache( $this->getId() );

	    if ( $this->getDeleted() == FALSE ) {
            Debug::text('Setting Tags...', __FILE__, __LINE__, __METHOD__, 10);
			CompanyGenericTagMapFactory::setTags( $this->getUserObject()->getCompany(), 320, $this->getID(), $this->getTag() );
		}
		return TRUE;
	}

	//Support setting created_by,updated_by especially for importing data.
	//Make sure data is set based on the getVariableToFunctionMap order.
	function setObjectFromArray( $data ) {
	    Debug::Arr($data,'setObjectFromArray...',__FILE__,__LINE__,__METHOD__,10);
		if ( is_array( $data ) ) {
			$variable_function_map = $this->getVariableToFunctionMap();
			foreach( $variable_function_map as $key => $function ) {
				if ( isset($data[$key]) ) {
					$function = 'set'.$function;
					switch( $key ) {
					    case 'start_date':
                            $this->setStartDate( TTDate::parseDateTime( $data['start_date'] ) );
                            break;
						case 'end_date':
                            $this->setEndDate( TTDate::parseDateTime( $data['end_date'] ) );
                            break;
                        case 'due_date':
                            $this->setDueDate( TTDate::parseDateTime( $data['due_date'] ) );
                            break;
						default:
							if ( method_exists( $this, $function ) ) {
								$this->$function( $data[$key] );
							}
							break;
					}
				}
			}

			$this->setCreatedAndUpdatedColumns( $data );

			return TRUE;
		}

		return FALSE;
	}

	function getObjectAsArray( $include_columns = NULL, $permission_children_ids = FALSE  ) {
		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == NULL OR ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) ) {

					$function = 'get'.$function_stub;
					switch( $variable ) {
					    case 'type':
                        case 'term':
                        case 'severity':
					    case 'status':
							$function = 'get'.$variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
                        case 'user':
                            $data[$variable] = Misc::getFullName( $this->getColumn('user_first_name'), NULL , $this->getColumn('user_last_name'),FALSE, FALSE );
                            break;
                        case 'reviewer_user':
                            $data[$variable] = Misc::getFullName( $this->getColumn('reviewer_user_first_name'), NULL, $this->getColumn('reviewer_user_last_name'),FALSE, FALSE );
                            break;
                        case 'start_date':
                            $data['start_date'] = TTDate::getAPIDate( 'DATE', $this->getStartDate() );
                            break;
						case 'end_date':
                            $data['end_date'] = TTDate::getAPIDate( 'DATE', $this->getEndDate() );
                            break;
                        case 'due_date':
                            $data['due_date'] = TTDate::getAPIDate( 'DATE', $this->getDueDate() );
                            break;
						default:
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = $this->$function();
							}
							break;
					}

				}
			}
            
            $this->getPermissionColumns( $data, $this->getUser(), $this->getCreatedBy(), $permission_children_ids, $include_columns );
            
			$this->getCreatedAndUpdatedColumns( $data, $include_columns );	
            
		}
		return $data;
	}

	function addLog( $log_action ) {
		return TTLog::addEntry( $this->getId(), $log_action,  TTi18n::getText('Employee Review') .' - '. TTi18n::getText('Type') .': '. Option::getByKey($this->getType(), $this->getOptions('type')) .', '. TTi18n::getText('Status') .': '. Option::getByKey($this->getStatus(), $this->getOptions('status')) , NULL, $this->getTable(), $this );
	}
}
?>
