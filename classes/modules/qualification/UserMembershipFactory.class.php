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
 * $Revision: 5229 $
 * $Id: UserMembershipFactory.class.php 5229 2011-09-20 17:52:53Z ipso $
 * $Date: 2011-09-21 01:52:53 +0800 (Wed, 21 Sep 2011) $
 */

/**
 * @package
 */
class UserMembershipFactory extends Factory {
	protected $table = 'user_membership';
	protected $pk_sequence_name = 'user_membership_id_seq'; //PK Sequence name
    protected $qualification_obj = NULL;
	function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'ownership':
				$retval = array(
										10 => TTi18n::gettext('Company'),
										20 => TTi18n::gettext('Individual'),
									);
				break;
			case 'columns':
				$retval = array(
                                        '-1010-first_name' => TTi18n::gettext('First Name'),
										'-1020-last_name' => TTi18n::gettext('Last Name'),
										'-2050-qualification' => TTi18n::gettext('Membership'),
                                        '-2040-group' => TTi18n::gettext('Group'),
                                        '-4030-ownership' => TTi18n::gettext('Ownership'),
                                        '-1060-amount' => TTi18n::gettext('Amount'),
                                        '-2500-currency' => TTi18n::gettext('Currency'),
                                        '-1080-start_date' => TTi18n::gettext('Start Date'),
                                        '-4040-renewal_date' => TTi18n::gettext('Renewal Date'),
                                        '-1300-tag' => TTi18n::gettext('Tags'),

                                        '-1090-title' => TTi18n::gettext('Title'),
										'-1099-user_group' => TTi18n::gettext('Employee Group'),
										'-1100-default_branch' => TTi18n::gettext('Branch'),
										'-1110-default_department' => TTi18n::gettext('Department'),

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
                                'first_name',
								'last_name',
                                'qualification',
                                'ownership',
								'amount',
                                'currency',
                                'start_date',
                                'renewal_date',
								);
				break;

		}

		return $retval;
	}

    function _getVariableToFunctionMap( $data ) {
		$variable_function_map = array(
										'id' => 'ID',
										'user_id' => 'User',
                                        'first_name' => FALSE,
										'last_name' => FALSE,
                                        'qualification_id' => 'Qualification',
                                        'qualification' => FALSE,
                                        'group' => false,
                                        'ownership_id' => 'Ownership',
                                        'ownership' => FALSE,
                                        'amount' => 'Amount',
                                        'currency_id' => 'Currency',
                                        'currency' => FALSE,

                                        'start_date' => 'StartDate',

                                        'renewal_date' => 'RenewalDate',

                                        'tag' => 'Tag',
                                        'default_branch' => FALSE,
										'default_department' => FALSE,
										'user_group' => FALSE,
										'title' => FALSE,
										'deleted' => 'Deleted',
										);
		return $variable_function_map;
	}

    function getQualificationObject() {

        return $this->getGenericObject( 'QualificationListFactory', $this->getQualification(), 'qualification_obj' );
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

		if ( $this->Validator->isResultSetWithRows(	'user_id',
															$ulf->getByID($id),
															TTi18n::gettext('Invalid User')
															) ) {
			$this->data['user_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

    function getQualification() {
        if ( isset( $this->data['qualification_id'] ) ) {
            return $this->data['qualification_id'];
        }
        return FALSE;
    }

    function setQualification( $id ) {
        $id = trim( $id );

        $qlf = TTnew( 'QualificationListFactory' );

        if( $this->Validator->isResultSetWithRows( 'qualification_id',
                                                                    $qlf->getById( $id ),
                                                                    TTi18n::gettext('Invalid Qualification')
                                                                     ) ) {
            $this->data['qualification_id'] = $id;

            return TRUE;
        }

        return FALSE;
    }



    function getOwnership() {
        if ( isset( $this->data['ownership_id'] ) ) {
            return $this->data['ownership_id'];
        }
        return FALSE;
    }

    function setOwnership ( $ownership_id ) {
        $ownership_id = trim( $ownership_id );

        if( $this->Validator->inArrayKey( 'ownership_id',
                                          $ownership_id,
                                          TTi18n::gettext( 'Ownership is invalid' ),
                                          $this->getOptions( 'ownership' ) ) ) {
            $this->data['ownership_id'] = $ownership_id;

            return TRUE;
        }

        return FALSE;
    }



    function getCurrency() {
		if ( isset($this->data['currency_id']) ) {
			return $this->data['currency_id'];
		}

		return FALSE;
	}
	function setCurrency($id) {
		$id = trim($id);

		Debug::Text('Currency ID: '. $id, __FILE__, __LINE__, __METHOD__,10);
		$culf = TTnew( 'CurrencyListFactory' );

		if (
				$this->Validator->isResultSetWithRows(	'currency_id',
														$culf->getByID($id),
														TTi18n::gettext('Invalid Currency')
													) ) {

			$this->data['currency_id'] = $id;

			return TRUE;
		}

		return FALSE;
	}

    /*
    function getAmount() {
		if ( isset($this->data['amount']) ) {
			return $this->data['amount'];
		}

		return FALSE;
	}

	function setAmount($int) {
		$int = trim($int);

		if  ( empty($int) ){
			$int = 0;
		}

		if 	(	$this->Validator->isNumeric(		'amount',
													$int,
													TTi18n::gettext('Incorrect Amount'))
				) {

			$this->data['amount'] = $int;

			return TRUE;
		}

		return FALSE;
	}
    */

    function getAmount() {
		if ( isset($this->data['amount']) ) {
			return Misc::MoneyFormat( $this->data['amount'], FALSE );
		}

		return FALSE;
	}
	function setAmount($value) {
		$value = trim($value);

		//Pull out only digits and periods.
		$value = $this->Validator->stripNonFloat($value);

		if (
				$this->Validator->isFloat(	'amount',
											$value,
											TTi18n::gettext('Invalid Amount, Must be a numeric value')) ) {

			$this->data['amount'] = Misc::MoneyFormat( $value, FALSE );

			return TRUE;
		}

		return FALSE;
	}


    function getStartDate() {
        if ( isset( $this->data['start_date'] ) ) {
            return $this->data['start_date'];
        }

        return FALSE;
    }

    function setStartDate($epoch) {
        $epoch = trim($epoch);

		if ( $epoch == '' ) {
			$epoch = NULL;
		}
        if (  ( $epoch !=  NULL )
                OR
                $this->Validator->isDate(  'start_date',
                                            $epoch,
                                            TTi18n::gettext('Start date is invalid') ) ) {
                  //$this->data['start_date']  = TTDate::getBeginDayEpoch( $epoch );
                  $this->data['start_date']  = $epoch;
                return  TRUE;
        }

        return FALSE;
    }


    function getRenewalDate() {
        if ( isset( $this->data['renewal_date'] ) ) {
            return $this->data['renewal_date'];
        }

        return FALSE;
    }

    function setRenewalDate($epoch) {
        $epoch = trim($epoch);

		if ( $epoch == '' ) {
			$epoch = NULL;
		}
        if (  ( $epoch != NULL )
                OR
                $this->Validator->isDate(  'renewal_date',
                                            $epoch,
                                            TTi18n::gettext('Renewal date is invalid') ) ) {
                  //$this->data['renewal_date']  = TTDate::getBeginDayEpoch( $epoch );
                  $this->data['renewal_date']  = $epoch;
                return  TRUE;
        }

        return FALSE;
    }

    function getTag() {
		//Check to see if any temporary data is set for the tags, if not, make a call to the database instead.
		//postSave() needs to get the tmp_data.
		if ( isset($this->tmp_data['tags']) ) {
			return $this->tmp_data['tags'];
		} elseif ( is_object( $this->getQualificationObject() ) AND $this->getQualificationObject()->getCompany() > 0 AND $this->getID() > 0 ) {
			return CompanyGenericTagMapListFactory::getStringByCompanyIDAndObjectTypeIDAndObjectID( $this->getQualificationObject()->getCompany(), 255, $this->getID() );
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
		//$this->setProvince( $this->getProvince() ); //Not sure why this was there, but it causes duplicate errors if the province is incorrect.

		return TRUE;
	}

    function preSave() {
		return TRUE;
	}

	function postSave() {
		$this->removeCache( $this->getId() );
        if ( $this->getDeleted() == FALSE ) {
            Debug::text('Setting Tags...', __FILE__, __LINE__, __METHOD__, 10);
			CompanyGenericTagMapFactory::setTags( $this->getQualificationObject()->getCompany(), 255, $this->getID(), $this->getTag() );
		}
		return TRUE;
	}

	function setObjectFromArray( $data ) {

		if ( is_array( $data ) ) {
			$variable_function_map = $this->getVariableToFunctionMap();
			foreach( $variable_function_map as $key => $function ) {
				if ( isset($data[$key]) ) {

					$function = 'set'.$function;
					switch( $key ) {
					    case 'start_date':
                            $this->setStartDate( TTDate::parseDateTime( $data['start_date'] ) );
                            break;
                        case 'renewal_date':
                            $this->setRenewalDate( TTDate::parseDateTime( $data['renewal_date'] ) );
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
					    case 'qualification':
                        case 'group':
                        case 'currency':
                        case 'first_name':
						case 'last_name':
                        case 'title':
						case 'user_group':
                        case 'default_branch':
						case 'default_department':
                            $data[$variable] = $this->getColumn( $variable );
							break;
						case 'ownership':
							$function = 'get'.$variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
                        case 'start_date':
                            $data[$variable] = TTDate::getAPIDate( 'DATE', $this->getStartDate() );
                            break;
                        case 'renewal_date':
                            $data['renewal_date'] = TTDate::getAPIDate( 'DATE', $this->getRenewalDate() );
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
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText('Membership') , NULL, $this->getTable(), $this );
	}

}
?>
