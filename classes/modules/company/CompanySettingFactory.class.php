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
 * $Revision: 7166 $
 * $Id: SystemSettingFactory.class.php 7166 2012-06-26 22:32:24Z ipso $
 * $Date: 2012-06-27 06:32:24 +0800 (Wed, 27 Jun 2012) $
 */

/**
 * @package Core
 */
class CompanySettingFactory extends Factory {
	protected $table = 'company_setting';
	protected $pk_sequence_name = 'company_setting_id_seq'; //PK Sequence name
    
    function _getFactoryOptions( $name ) {

		$retval = NULL;
		switch( $name ) {
			case 'type':
				$retval = array(
								10 => TTi18n::gettext('Public'),
								20 => TTi18n::gettext('Private'),		
									);
				break;
		}

		return $retval;
	}

    function _getVariableToFunctionMap( $data ) {
		$variable_function_map = array(
										'id' => 'ID',
										'company_id' => 'Company',
										'type_id' => 'Type',
										'type' => FALSE,
										'name' => 'Name',
                                        'value' => 'Value',
										'deleted' => 'Deleted',
										);
		return $variable_function_map;
	}
    
	function isUniqueName($name) {
		Debug::Arr($this->getCompany(),'Company: ', __FILE__, __LINE__, __METHOD__,10);
		if ( $this->getCompany() == FALSE ) {
			return FALSE;
		}

		$name = trim($name);
		if ( $name == '' ) {
			return FALSE;
		}

		$ph = array(
					'company_id' => $this->getCompany(),
					'name' => $name,
					);

		$query = 'select id from '. $this->getTable() .'
					where company_id = ?
						AND name = ?
						AND deleted = 0';
		$name_id = $this->db->GetOne($query, $ph);
		Debug::Arr($name_id,'Unique Name: '. $name , __FILE__, __LINE__, __METHOD__,10);

		if ( $name_id === FALSE ) {
			return TRUE;
		} else {
			if ($name_id == $this->getId() ) {
				return TRUE;
			}
		}

		return FALSE;
	}
    
    function getCompany() {
		if ( isset($this->data['company_id']) ) {
			return (int)$this->data['company_id'];
		}

		return FALSE;
	}
    function setCompany($id) {
		$id = trim($id);

		$clf = TTnew( 'CompanyListFactory' );

		if ( $id == 0
				OR $this->Validator->isResultSetWithRows(	'company',
															$clf->getByID($id),
															TTi18n::gettext('Company is invalid')
															) ) {
			$this->data['company_id'] = $id;

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

			return FALSE;
		}

		return FALSE;
	}
    
	function getName() {
		if ( isset($this->data['name']) ) {
			return $this->data['name'];
		}

		return FALSE;
	}
	function setName($value) {
		$value = trim($value);
		if (	$this->Validator->isLength(	'name',
											$value,
											TTi18n::gettext('Name is too short or too long'),
											1,250)
				AND
						$this->Validator->isTrue(		'name',
														$this->isUniqueName($value),
														TTi18n::gettext('Name already exists')
														)

						) {

			$this->data['name'] = $value;

			return TRUE;
		}

		return FALSE;
	}

	function getValue() {
		if ( isset($this->data['value']) ) {
			return $this->data['value'];
		}

		return FALSE;
	}
	function setValue($value) {
		$value = trim($value);
		if (	$this->Validator->isLength(	'value',
											$value,
											TTi18n::gettext('Value is too short or too long'),
											1,4096)
						) {

			$this->data['value'] = $value;

			return TRUE;
		}

		return FALSE;
	}
    	

	function preSave() {
		return TRUE;
	}

	function postSave() {
		$this->removeCache( $this->getCompany().$this->getName() );
		return TRUE;
	}
    
    function setObjectFromArray( $data ) {
		if ( is_array( $data ) ) {
			$variable_function_map = $this->getVariableToFunctionMap();
			foreach( $variable_function_map as $key => $function ) {
				if ( isset($data[$key]) ) {

					$function = 'set'.$function;
					switch( $key ) {
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

	function getObjectAsArray( $include_columns = NULL ) {
		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == NULL OR ( isset($include_columns[$variable]) AND $include_columns[$variable] == TRUE ) ) {

					$function = 'get'.$function_stub;
					switch( $variable ) {
						case 'type':
							$function = 'get'.$variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						default:
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = $this->$function();
							}
							break;
					}

				}
			}
			$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
	}
    
	function addLog( $log_action ) {
		return TTLog::addEntry( $this->getId(), $log_action,  TTi18n::getText('Company Setting - Name').': '. $this->getName() .' '. TTi18n::getText('Value').': '. $this->getValue(), NULL, $this->getTable() );
	}
    
    static function getCompanySetting( $company_id, $name ) {
        $cslf = new CompanySettingListFactory();
        $cslf->getByCompanyIdAndName( $company_id, $name );
        if ( $cslf->getRecordCount() == 1 ) {
            $cs_obj = $cslf->getCurrent();
            $retarr = $cs_obj->getObjectAsArray();
            return $retarr;
        }
        
        return FALSE;
    }
    
    static function setCompanySetting( $company_id, $name, $value, $type_id = 10 ) {
        $row = array(
            'company_id' => $company_id,
            'name' => $name,
            'value' => $value,
            'type_id' => $type_id
        );
        $cslf = new CompanySettingListFactory();
        $cslf->getByCompanyIdAndName( $company_id, $name );
        if ( $cslf->getRecordCount() == 1 ) {
            $csf = $cslf->getCurrent();
            $row = array_merge( $csf->getObjectAsArray(), $row );
        } else {
            $csf = new CompanySettingFactory();
        }
        
        Debug::Arr($row, 'Data: ', __FILE__, __LINE__, __METHOD__, 10);        
        $csf->setObjectFromArray( $row );
        if ( $csf->isValid() ) {
            $csf->Save();
        }
        
        return FALSE;

    }
    
    static function deleteCompanySetting( $company_id, $name ) {
        $cslf = new CompanySettingListFactory();
        $cslf->getByCompanyIdAndName( $company_id, $name );
        if ( $cslf->getRecordCount() == 1 ) {
            $csf = $cslf->getCurrent();
            $csf->setDeleted(TRUE);
            if ( $csf->isValid() ) {
                $csf->Save();
            }
        }
        
        return FALSE;
    }
}
?>
