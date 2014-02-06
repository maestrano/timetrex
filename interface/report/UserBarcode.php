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
 * $Revision: 5776 $
 * $Id: UserBarcode.php 5776 2011-12-16 18:59:26Z ipso $
 * $Date: 2011-12-16 10:59:26 -0800 (Fri, 16 Dec 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');
require_once(Environment::getBasePath() .'classes/misc/arr_multisort.class.php');

if ( !$permission->Check('report','enabled')
		OR !$permission->Check('report','view_user_barcode') ) {
	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', TTi18n::gettext($title = 'Employee Barcodes')); // See index.php


/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'generic_data',
												'filter_data'

												) ) );

URLBuilder::setURL($_SERVER['SCRIPT_NAME'],
											array(
													'filter_data' => $filter_data
//													'sort_column' => $sort_column,
//													'sort_order' => $sort_order,
												) );

$columns = array(
											'-1010-employee_number' => TTi18n::gettext('Employee #'),
											'-1030-user_name' => TTi18n::gettext('User Name'),
											'-1040-phone_id' => TTi18n::gettext('Quick Punch ID'),

											'-1060-first_name' => TTi18n::gettext('First Name'),
											'-1070-middle_name' => TTi18n::gettext('Middle Name'),
											'-1080-last_name' => TTi18n::gettext('Last Name'),

											'-1090-title' => TTi18n::gettext('Title'),

											'-1100-default_branch' => TTi18n::gettext('Branch'),
											'-1110-default_department' => TTi18n::gettext('Department'),

											'-1200-barcode' => TTi18n::gettext('Barcode'),
											);

if ( !isset($filter_data['include_user_ids']) ) {
	$filter_data['include_user_ids'] = array();
}
if ( !isset($filter_data['exclude_user_ids']) ) {
	$filter_data['exclude_user_ids'] = array();
}
if ( !isset($filter_data['user_status_ids']) ) {
	$filter_data['user_status_ids'] = array();
}
if ( !isset($filter_data['group_ids']) ) {
	$filter_data['group_ids'] = array();
}
if ( !isset($filter_data['branch_ids']) ) {
	$filter_data['branch_ids'] = array();
}
if ( !isset($filter_data['department_ids']) ) {
	$filter_data['department_ids'] = array();
}
if ( !isset($filter_data['user_title_ids']) ) {
	$filter_data['user_title_ids'] = array();
}
if ( !isset($filter_data['column_ids']) ) {
	$filter_data['column_ids'] = array();
}

//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
$permission_children_ids = array();
if ( $permission->Check('user','view') == FALSE ) {
	$hlf = TTnew( 'HierarchyListFactory' );
	$permission_children_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $current_company->getId(), $current_user->getId() );
	Debug::Arr($permission_children_ids,'Permission Children Ids:', __FILE__, __LINE__, __METHOD__,10);

	if ( $permission->Check('user','view_child') == FALSE ) {
		$permission_children_ids = array();
	}
	if ( $permission->Check('user','view_own') ) {
		$permission_children_ids[] = $current_user->getId();
	}

	$filter_data['permission_children_ids'] = $permission_children_ids;
}

$ugdlf = TTnew( 'UserGenericDataListFactory' );
$ugdf = TTnew( 'UserGenericDataFactory' );

$action = Misc::findSubmitButton();
Debug::Text('Action: '. $action, __FILE__, __LINE__, __METHOD__,10);
switch ($action) {
	case 'display_report':
		//Debug::setVerbosity(11);

		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

		//Get all employees that match the criteria:
		$ulf = TTnew( 'UserListFactory' );
		$ulf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), $filter_data );
		/*
		$ulf->getSearchByCompanyIdAndStatusIdAndBranchIdAndDepartmentIdAndUserTitleIdAndIncludeIdAndExcludeId(
			$current_company->getId(),
			$filter_data['user_status_ids'],
			$filter_data['branch_ids'],
			$filter_data['department_ids'],
			$filter_data['user_title_ids'],
			$filter_data['include_user_ids'],
			$filter_data['exclude_user_ids'] );
		*/
		if ( $ulf->getRecordCount() > 0 ) {
			foreach( $ulf as $u_obj ) {
				$filter_data['user_ids'][] = $u_obj->getId();
			}

			$ulf->getReportByCompanyIdAndUserIDList( $current_company->getId(), $filter_data['user_ids'] );

			//Get title list,
			$utlf = TTnew( 'UserTitleListFactory' );
			$user_titles = $utlf->getByCompanyIdArray( $current_company->getId() );

			//Get default branch list
			$blf = TTnew( 'BranchListFactory' );
			$branch_options = $blf->getByCompanyIdArray( $current_company->getId() );

			$dlf = TTnew( 'DepartmentListFactory' );
			$department_options = $dlf->getByCompanyIdArray( $current_company->getId() );

			foreach ($ulf as $u_obj ) {

				$user_rows[] = array(
									'id' => $u_obj->getId(),
									'employee_number' => $u_obj->getEmployeeNumber(),
									'user_name' => $u_obj->getUserName(),
									'phone_id' => $u_obj->getPhoneID(),

									'first_name' => $u_obj->getFirstName(),
									'middle_name' => $u_obj->getMiddleName(),
									'last_name' => $u_obj->getLastName(),

									'title' => Option::getByKey($u_obj->getTitle(), $user_titles ),

									'default_branch' => Option::getByKey($u_obj->getDefaultBranch(), $branch_options ),
									'default_department' => Option::getByKey($u_obj->getDefaultDepartment(), $department_options ),
								);
			}

			$user_rows = Sort::Multisort($user_rows, Misc::trimSortPrefix($filter_data['primary_sort']), Misc::trimSortPrefix($filter_data['secondary_sort']), $filter_data['primary_sort_dir'], $filter_data['secondary_sort_dir']);

			$dir = $config_vars['cache']['dir'] . DIRECTORY_SEPARATOR . uniqid('user_barcodes_') . DIRECTORY_SEPARATOR;
			mkdir( $dir );

			$border = 0;

			$pdf = new TTPDF();
			$pdf->setMargins(10,10,10,10);
			$pdf->SetAutoPageBreak(FALSE);
			$pdf->SetFont('freeserif','',10);

			if ( isset($user_rows) ) {
				//Trim prefixes from column names
				$filter_data['column_ids'] = array_flip( Misc::trimSortPrefix( array_flip($filter_data['column_ids']) ) );

				$pdf->AddPage();
				$pdf->SetFont('freeserif','BU',20);
				$pdf->Cell(190,10, TTi18n::gettext('Employees'), $border, 0, 'C');

				$pdf->SetFont('freeserif','B',10);

				$next_x = 10;
				$next_y = 25;
				$i=1;
				foreach( $user_rows as $user_row ) {
					if ( $i > 1 AND $i % 16 == 1 ) {
						$pdf->AddPage();
						$next_x = 10;
						$next_y = 10;
					}
					$row_next_y = $next_y;

					$name = '';
					if ( $user_row['employee_number'] != ''
							AND in_array('employee_number', $filter_data['column_ids']) ) {
						$name = '#'.$user_row['employee_number'] .' - ';
					}

					if ( in_array('first_name', $filter_data['column_ids']) ) {
						$name .= $user_row['first_name'];
					}
					if ( in_array('middle_name', $filter_data['column_ids']) ) {
						$name .= ' '.$user_row['middle_name'];
					}
					if ( in_array('last_name', $filter_data['column_ids']) ) {
						$name .= ' '.$user_row['last_name'];
					}

					$pdf->setXY($next_x,$next_y);
					$pdf->SetFont('freeserif','B',10);
					$pdf->Cell(60,5, $name , $border, 0, 'L');

					$barcode_x = $next_x+55; //$pdf->getX()+1;
					$barcode_y = $pdf->getY();

					$pdf->SetFont('freeserif','',10);
					if ( in_array('title', $filter_data['column_ids']) ) {
						$row_next_y = $row_next_y+5;
						$pdf->setXY($next_x,$row_next_y);
						$pdf->Cell(5,5, '' , $border, 0, 'L');
						$pdf->Cell(55,5, TTi18n::gettext('Title:').' '.$user_row['title'] , $border, 0, 'L');
					}

					if ( in_array('user_name', $filter_data['column_ids']) ) {
						$row_next_y = $row_next_y+5;
						$pdf->setXY($next_x,$row_next_y);
						$pdf->Cell(5,5, '' , $border, 0, 'L');
						$pdf->Cell(55,5, TTi18n::gettext('User Name:').' '.$user_row['user_name'] , $border, 0, 'L');
					}

					if ( in_array('phone_id', $filter_data['column_ids']) ) {
						$row_next_y = $row_next_y+5;
						$pdf->setXY($next_x,$row_next_y);
						$pdf->Cell(5,5, '' , $border, 0, 'L');
						$pdf->Cell(55,5, TTi18n::gettext('Phone ID:').' '.$user_row['phone_id'] , $border, 0, 'L');
					}

					if ( in_array('default_branch', $filter_data['column_ids']) ) {
						$row_next_y = $row_next_y+5;
						$pdf->setXY($next_x,$row_next_y);
						$pdf->Cell(5,5, '' , $border, 0, 'L');
						$pdf->Cell(55,5, TTi18n::gettext('Branch:').' '.$user_row['default_branch'] , $border, 0, 'L');
					}

					if ( in_array('default_department', $filter_data['column_ids']) ) {
						$row_next_y = $row_next_y+5;
						$pdf->setXY($next_x,$row_next_y);
						$pdf->Cell(5,5, '' , $border, 0, 'L');
						$pdf->Cell(55,5, TTi18n::gettext('Department:').' '.$user_row['default_department'] , $border, 0, 'L');
					}

					if ( in_array('barcode', $filter_data['column_ids']) ) {
						if ( $user_row['employee_number'] != '' ) {
							$barcode_id = $user_row['employee_number'];
						} else {
							$barcode_id = $user_row['id'];
						}

						$pdf->setXY( $barcode_x,$barcode_y);
						$barcode_file_name = $dir . 'U'. $barcode_id .'.png';
						if ( Misc::writeBarCodeFile( $barcode_file_name, 'U'. $barcode_id ) == TRUE) {
							$pdf->Image($barcode_file_name,$barcode_x,$barcode_y,NULL,25);
						}
						unset($barcode_id);
					}
					$next_x = $pdf->getX()+40;

					if ( $i > 0 AND $i % 2 == 0 ) {
						$next_x = 10;
						$next_y = $next_y + 30;
					}

					$i++;
				}
			}

			if ( isset($branch_options) AND count($branch_options) > 1 ) {
				$pdf->AddPage();
				$pdf->SetFont('freeserif','BU',20);
				$pdf->Cell(190,10, TTi18n::gettext('Branches'), $border, 0, 'C');

				$pdf->SetFont('freeserif','',10);

				$next_x = 10;
				$next_y = 25;
				$i=1;
				foreach( $branch_options as $branch_id => $branch_name ) {
					if ( $branch_id == 0 ) {
						continue;
					}

					if ( $i > 1 AND $i % 16 == 1 ) {
						$pdf->AddPage();
						$next_x = 10;
						$next_y = 10;
					}

					$name = $branch_name;

					//echo "Task: $i<br>\n";
					$pdf->setXY($next_x,$next_y);
					$pdf->SetFont('freeserif','B',10);
					$pdf->Cell(60,5, $name , $border, 0, 'L');
					$barcode_x = $next_x+55; //$pdf->getX()+1;
					$barcode_y = $pdf->getY();

					$pdf->SetFont('freeserif','',10);
					/*
					if ( in_array('description', $filter_data['column_ids']) ) {
						$pdf->setXY($next_x,$next_y+5);
						$pdf->Cell(5,5, '' , $border, 0, 'L');
						$pdf->Cell(55,5, $job_item_row['description'] , $border, 0, 'L');
					}
					*/

					if ( in_array('barcode', $filter_data['column_ids']) ) {
						$pdf->setXY( $barcode_x,$barcode_y);
						$barcode_file_name = $dir . 'B'.$branch_id .'.png';
						if ( Misc::writeBarCodeFile( $barcode_file_name, 'B'.$branch_id) == TRUE) {
							$pdf->Image($barcode_file_name,$barcode_x,$barcode_y,NULL,25);
						}
					}
					$next_x = $pdf->getX()+40;

					if ( $i > 0 AND $i % 2 == 0 ) {
						$next_x = 10;
						$next_y = $next_y + 30;
					}

					$i++;
				}
			}

			if ( isset($department_options) AND count($department_options) > 1 ) {
				$pdf->AddPage();
				$pdf->SetFont('freeserif','BU',20);
				$pdf->Cell(190,10, TTi18n::gettext('Departments'), $border, 0, 'C');

				$pdf->SetFont('freeserif','',10);

				$next_x = 10;
				$next_y = 25;
				$i=1;
				foreach( $department_options as $department_id => $department_name ) {
					if ( $department_id == 0 ) {
						continue;
					}

					if ( $i > 1 AND $i % 16 == 1 ) {
						$pdf->AddPage();
						$next_x = 10;
						$next_y = 10;
					}

					$name = $department_name;

					//echo "Task: $i<br>\n";
					$pdf->setXY($next_x,$next_y);
					$pdf->SetFont('freeserif','B',10);
					$pdf->Cell(60,5, $name , $border, 0, 'L');
					$barcode_x = $next_x+55; //$pdf->getX()+1;
					$barcode_y = $pdf->getY();

					$pdf->SetFont('freeserif','',10);
					/*
					if ( in_array('description', $filter_data['column_ids']) ) {
						$pdf->setXY($next_x,$next_y+5);
						$pdf->Cell(5,5, '' , $border, 0, 'L');
						$pdf->Cell(55,5, $job_item_row['description'] , $border, 0, 'L');
					}
					*/

					if ( in_array('barcode', $filter_data['column_ids']) ) {
						$pdf->setXY( $barcode_x,$barcode_y);
						$barcode_file_name = $dir . 'D'.$department_id .'.png';
						if ( Misc::writeBarCodeFile( $barcode_file_name, 'D'.$department_id) == TRUE) {
							$pdf->Image($barcode_file_name,$barcode_x,$barcode_y,NULL,25);
						}
					}
					$next_x = $pdf->getX()+40;

					if ( $i > 0 AND $i % 2 == 0 ) {
						$next_x = 10;
						$next_y = $next_y + 30;
					}

					$i++;
				}
			}

			//
			//Barcode commands and special options
			//
			$pdf->AddPage();
			$pdf->SetFont('freeserif','BU',20);
			$pdf->Cell(190,10, TTi18n::gettext('Commands'), $border, 0, 'C');

			$pdf->SetFont('freeserif','',10);

			$next_x = 10;
			$next_y = 25;

			$pdf->setXY($next_x,$next_y);
			$pdf->SetFont('freeserif','B',10);
			$pdf->Cell(50,5, TTi18n::gettext('Normal') , $border, 0, 'C');
			$barcode_x = $next_x+6;
			$barcode_y = $pdf->getY()+5;

			if ( in_array('barcode', $filter_data['column_ids']) ) {
				$pdf->setXY( $barcode_x,$barcode_y);
				$barcode_file_name = $dir . 'NORMAL.png';
				if ( Misc::writeBarCodeFile( $barcode_file_name, 'NORMAL') == TRUE) {
					$pdf->Image($barcode_file_name,$barcode_x,$barcode_y,NULL,25);
				}
			}

			$next_x = 80;
			$pdf->setXY($next_x,$next_y);
			$pdf->SetFont('freeserif','B',10);
			$pdf->Cell(50,5, TTi18n::gettext('Lunch') , $border, 0, 'C');
			$barcode_x = $next_x+8;
			$barcode_y = $pdf->getY()+5;

			if ( in_array('barcode', $filter_data['column_ids']) ) {
				$pdf->setXY( $barcode_x,$barcode_y);
				$barcode_file_name = $dir . 'LUNCH.png';
				if ( Misc::writeBarCodeFile( $barcode_file_name, 'LUNCH') == TRUE) {
					$pdf->Image($barcode_file_name,$barcode_x,$barcode_y,NULL,25);
				}
			}

			$next_x = 150;
			$pdf->setXY($next_x,$next_y);
			$pdf->SetFont('freeserif','B',10);
			$pdf->Cell(50,5, TTi18n::gettext('Break') , $border, 0, 'C');
			$barcode_x = $next_x+8;
			$barcode_y = $pdf->getY()+5;

			if ( in_array('barcode', $filter_data['column_ids']) ) {
				$pdf->setXY( $barcode_x,$barcode_y);
				$barcode_file_name = $dir . 'BREAK.png';
				if ( Misc::writeBarCodeFile( $barcode_file_name, 'BREAK') == TRUE) {
					$pdf->Image($barcode_file_name,$barcode_x,$barcode_y,NULL,25);
				}
			}

			$next_x = 10;
			$next_y = 60;

			$pdf->setXY($next_x,$next_y);
			$pdf->SetFont('freeserif','B',10);
			$pdf->Cell(50,5, TTi18n::gettext('In') , $border, 0, 'C');
			$barcode_x = $next_x+13;
			$barcode_y = $pdf->getY()+5;

			if ( in_array('barcode', $filter_data['column_ids']) ) {
				$pdf->setXY( $barcode_x,$barcode_y);
				$barcode_file_name = $dir . 'IN.png';
				if ( Misc::writeBarCodeFile( $barcode_file_name, 'IN') == TRUE) {
					$pdf->Image($barcode_file_name,$barcode_x,$barcode_y,NULL,25);
				}
			}

			$next_x = 150;
			$pdf->setXY($next_x,$next_y);
			$pdf->SetFont('freeserif','B',10);
			$pdf->Cell(50,5, TTi18n::gettext('Out') , $border, 0, 'C');
			$barcode_x = $next_x+11;
			$barcode_y = $pdf->getY()+5;

			if ( in_array('barcode', $filter_data['column_ids']) ) {
				$pdf->setXY( $barcode_x,$barcode_y);
				$barcode_file_name = $dir . 'OUT.png';
				if ( Misc::writeBarCodeFile( $barcode_file_name, 'OUT') == TRUE) {
					$pdf->Image($barcode_file_name,$barcode_x,$barcode_y,NULL,25);
				}
			}


			$next_x = 10;
			$next_y = 95;

			$pdf->setXY($next_x,$next_y);
			$pdf->SetFont('freeserif','B',10);
			$pdf->Cell(50,5, TTi18n::gettext('Good Quantity') , $border, 0, 'C');
			$barcode_x = $next_x+3;
			$barcode_y = $pdf->getY()+5;

			if ( in_array('barcode', $filter_data['column_ids']) ) {
				$pdf->setXY( $barcode_x,$barcode_y);
				$barcode_file_name = $dir . 'QUANTITY.png';
				if ( Misc::writeBarCodeFile( $barcode_file_name, 'QUANTITY') == TRUE) {
					$pdf->Image($barcode_file_name,$barcode_x,$barcode_y,NULL,25);
				}
			}

			$next_x = 150;
			$pdf->setXY($next_x,$next_y);
			$pdf->SetFont('freeserif','B',10);
			$pdf->Cell(50,5, TTi18n::gettext('Bad Quantity') , $border, 0, 'C');
			$barcode_x = $next_x+0;
			$barcode_y = $pdf->getY()+5;

			if ( in_array('barcode', $filter_data['column_ids']) ) {
				$pdf->setXY( $barcode_x,$barcode_y);
				$barcode_file_name = $dir . 'BAD_QUANTITY.png';
				if ( Misc::writeBarCodeFile( $barcode_file_name, 'BQUANTITY') == TRUE) {
					$pdf->Image($barcode_file_name,$barcode_x,$barcode_y,NULL,25);
				}
			}

			$next_x = 10;
			$next_y = 130;

			$pdf->setXY($next_x,$next_y);
			$pdf->SetFont('freeserif','B',10);
			$pdf->Cell(20,5, '0' , $border, 0, 'C');
			$barcode_x = $next_x+0;
			$barcode_y = $pdf->getY()+5;

			if ( in_array('barcode', $filter_data['column_ids']) ) {
				$pdf->setXY( $barcode_x,$barcode_y);
				$barcode_file_name = $dir . '0.png';
				if ( Misc::writeBarCodeFile( $barcode_file_name, '0') == TRUE) {
					$pdf->Image($barcode_file_name,$barcode_x,$barcode_y,NULL,25);
				}
			}

			$next_x = 53;
			$pdf->setXY($next_x,$next_y);
			$pdf->SetFont('freeserif','B',10);
			$pdf->Cell(20,5, '1' , $border, 0, 'C');
			$barcode_x = $next_x+0;
			$barcode_y = $pdf->getY()+5;

			if ( in_array('barcode', $filter_data['column_ids']) ) {
				$pdf->setXY( $barcode_x,$barcode_y);
				$barcode_file_name = $dir . '1.png';
				if ( Misc::writeBarCodeFile( $barcode_file_name, '1') == TRUE) {
					$pdf->Image($barcode_file_name,$barcode_x,$barcode_y,NULL,25);
				}
			}

			$next_x = 95;
			$pdf->setXY($next_x,$next_y);
			$pdf->SetFont('freeserif','B',10);
			$pdf->Cell(20,5, '2' , $border, 0, 'C');
			$barcode_x = $next_x+0;
			$barcode_y = $pdf->getY()+5;

			if ( in_array('barcode', $filter_data['column_ids']) ) {
				$pdf->setXY( $barcode_x,$barcode_y);
				$barcode_file_name = $dir . '2.png';
				if ( Misc::writeBarCodeFile( $barcode_file_name, '2') == TRUE) {
					$pdf->Image($barcode_file_name,$barcode_x,$barcode_y,NULL,25);
				}
			}

			$next_x = 138;
			$pdf->setXY($next_x,$next_y);
			$pdf->SetFont('freeserif','B',10);
			$pdf->Cell(20,5, '3' , $border, 0, 'C');
			$barcode_x = $next_x+0;
			$barcode_y = $pdf->getY()+5;

			if ( in_array('barcode', $filter_data['column_ids']) ) {
				$pdf->setXY( $barcode_x,$barcode_y);
				$barcode_file_name = $dir . '3.png';
				if ( Misc::writeBarCodeFile( $barcode_file_name, '3') == TRUE) {
					$pdf->Image($barcode_file_name,$barcode_x,$barcode_y,NULL,25);
				}
			}

			$next_x = 180;
			$pdf->setXY($next_x,$next_y);
			$pdf->SetFont('freeserif','B',10);
			$pdf->Cell(20,5, '4' , $border, 0, 'C');
			$barcode_x = $next_x+0;
			$barcode_y = $pdf->getY()+5;

			if ( in_array('barcode', $filter_data['column_ids']) ) {
				$pdf->setXY( $barcode_x,$barcode_y);
				$barcode_file_name = $dir . '4.png';
				if ( Misc::writeBarCodeFile( $barcode_file_name, '4') == TRUE) {
					$pdf->Image($barcode_file_name,$barcode_x,$barcode_y,NULL,25);
				}
			}


			$next_x = 10;
			$next_y = 170;

			$pdf->setXY($next_x,$next_y);
			$pdf->SetFont('freeserif','B',10);
			$pdf->Cell(20,5, '5' , $border, 0, 'C');
			$barcode_x = $next_x+0;
			$barcode_y = $pdf->getY()+5;

			if ( in_array('barcode', $filter_data['column_ids']) ) {
				$pdf->setXY( $barcode_x,$barcode_y);
				$barcode_file_name = $dir . '5.png';
				if ( Misc::writeBarCodeFile( $barcode_file_name, '5') == TRUE) {
					$pdf->Image($barcode_file_name,$barcode_x,$barcode_y,NULL,25);
				}
			}

			$next_x = 53;
			$pdf->setXY($next_x,$next_y);
			$pdf->SetFont('freeserif','B',10);
			$pdf->Cell(20,5, '6' , $border, 0, 'C');
			$barcode_x = $next_x+0;
			$barcode_y = $pdf->getY()+5;

			if ( in_array('barcode', $filter_data['column_ids']) ) {
				$pdf->setXY( $barcode_x,$barcode_y);
				$barcode_file_name = $dir . '6.png';
				if ( Misc::writeBarCodeFile( $barcode_file_name, '6') == TRUE) {
					$pdf->Image($barcode_file_name,$barcode_x,$barcode_y,NULL,25);
				}
			}

			$next_x = 95;
			$pdf->setXY($next_x,$next_y);
			$pdf->SetFont('freeserif','B',10);
			$pdf->Cell(20,5, '7' , $border, 0, 'C');
			$barcode_x = $next_x+0;
			$barcode_y = $pdf->getY()+5;

			if ( in_array('barcode', $filter_data['column_ids']) ) {
				$pdf->setXY( $barcode_x,$barcode_y);
				$barcode_file_name = $dir . '7.png';
				if ( Misc::writeBarCodeFile( $barcode_file_name, '7') == TRUE) {
					$pdf->Image($barcode_file_name,$barcode_x,$barcode_y,NULL,25);
				}
			}

			$next_x = 138;
			$pdf->setXY($next_x,$next_y);
			$pdf->SetFont('freeserif','B',10);
			$pdf->Cell(20,5, '8' , $border, 0, 'C');
			$barcode_x = $next_x+0;
			$barcode_y = $pdf->getY()+5;

			if ( in_array('barcode', $filter_data['column_ids']) ) {
				$pdf->setXY( $barcode_x,$barcode_y);
				$barcode_file_name = $dir . '8.png';
				if ( Misc::writeBarCodeFile( $barcode_file_name, '8') == TRUE) {
					$pdf->Image($barcode_file_name,$barcode_x,$barcode_y,NULL,25);
				}
			}

			$next_x = 180;
			$pdf->setXY($next_x,$next_y);
			$pdf->SetFont('freeserif','B',10);
			$pdf->Cell(20,5, '9' , $border, 0, 'C');
			$barcode_x = $next_x+0;
			$barcode_y = $pdf->getY()+5;

			if ( in_array('barcode', $filter_data['column_ids']) ) {
				$pdf->setXY( $barcode_x,$barcode_y);
				$barcode_file_name = $dir . '9.png';
				if ( Misc::writeBarCodeFile( $barcode_file_name, '9') == TRUE) {
					$pdf->Image($barcode_file_name,$barcode_x,$barcode_y,NULL,25);
				}
			}


			$next_x = 10;
			$next_y = 210;

			$pdf->setXY($next_x,$next_y);
			$pdf->SetFont('freeserif','B',10);
			$pdf->Cell(50,5, TTi18n::gettext('Clear') , $border, 0, 'C');
			$barcode_x = $next_x+9;
			$barcode_y = $pdf->getY()+5;

			if ( in_array('barcode', $filter_data['column_ids']) ) {
				$pdf->setXY( $barcode_x,$barcode_y);
				$barcode_file_name = $dir . 'CLEAR.png';
				if ( Misc::writeBarCodeFile( $barcode_file_name, 'CLEAR') == TRUE) {
					$pdf->Image($barcode_file_name,$barcode_x,$barcode_y,NULL,25);
				}
			}

			$next_x = 150;
			$pdf->setXY($next_x,$next_y);
			$pdf->SetFont('freeserif','B',10);
			$pdf->Cell(50,5, TTi18n::gettext('Delete') , $border, 0, 'C');
			$barcode_x = $next_x+6;
			$barcode_y = $pdf->getY()+5;

			if ( in_array('barcode', $filter_data['column_ids']) ) {
				$pdf->setXY( $barcode_x,$barcode_y);
				$barcode_file_name = $dir . 'DELETE.png';
				if ( Misc::writeBarCodeFile( $barcode_file_name, 'DELETE') == TRUE) {
					$pdf->Image($barcode_file_name,$barcode_x,$barcode_y,NULL,25);
				}
			}


			$next_x = 10;
			$next_y = 255;

			$pdf->setXY($next_x,$next_y);
			$pdf->SetFont('freeserif','B',10);
			$pdf->Cell(50,5, TTi18n::gettext('Transfer') , $border, 0, 'C');
			$barcode_x = $next_x+3;
			$barcode_y = $pdf->getY()+5;

			if ( in_array('barcode', $filter_data['column_ids']) ) {
				$pdf->setXY( $barcode_x,$barcode_y);
				$barcode_file_name = $dir . 'TRANSFER.png';
				if ( Misc::writeBarCodeFile( $barcode_file_name, 'TRANSFER') == TRUE) {
					$pdf->Image($barcode_file_name,$barcode_x,$barcode_y,NULL,25);
				}
			}

			$next_x = 150;
			$pdf->setXY($next_x,$next_y);
			$pdf->SetFont('freeserif','B',10);
			$pdf->Cell(50,5, TTi18n::gettext('Submit') , $border, 0, 'C');
			$barcode_x = $next_x+6;
			$barcode_y = $pdf->getY()+5;

			if ( in_array('barcode', $filter_data['column_ids']) ) {
				$pdf->setXY( $barcode_x,$barcode_y);
				$barcode_file_name = $dir . 'SUBMIT.png';
				if ( Misc::writeBarCodeFile( $barcode_file_name, 'SUBMIT') == TRUE) {
					$pdf->Image($barcode_file_name,$barcode_x,$barcode_y,NULL,25);
				}
			}


			//Delete tmp files.
			foreach(glob($dir.'*') as $filename) {
				unlink($filename);
			}
			rmdir($dir);

			$output = $pdf->Output('','S');

			//Debug::Display();
			Misc::FileDownloadHeader('employee_barcodes.pdf', 'application/pdf', strlen($output));
			echo $output;
			exit;
		} else {
			echo TTi18n::gettext('Sorry, no items match your criteria.')."<br>\n";
		}

		exit;

		break;
	case 'delete':
	case 'save':
		Debug::Text('Action: '. $action, __FILE__, __LINE__, __METHOD__,10);

		$generic_data['id'] = UserGenericDataFactory::reportFormDataHandler( $action, $filter_data, $generic_data, URLBuilder::getURL(NULL, $_SERVER['SCRIPT_NAME']) );
		unset($generic_data['name']);

	default:
		BreadCrumb::setCrumb($title);

		if ( $action == 'load' ) {
			Debug::Text('Loading Report!', __FILE__, __LINE__, __METHOD__,10);
			extract( UserGenericDataFactory::getReportFormData( $generic_data['id'] ) );

		} elseif ( $action == '' ) {
			//Check for default saved report first.
			$ugdlf->getByUserIdAndScriptAndDefault( $current_user->getId(), $_SERVER['SCRIPT_NAME'] );
			if ( $ugdlf->getRecordCount() > 0 ) {
				Debug::Text('Found Default Report!', __FILE__, __LINE__, __METHOD__,10);

				$ugd_obj = $ugdlf->getCurrent();
				$filter_data = $ugd_obj->getData();
				$generic_data['id'] = $ugd_obj->getId();
			} else {
				Debug::Text('Default Settings!', __FILE__, __LINE__, __METHOD__,10);
				//Default selections
				$filter_data['user_status_ids'] = array( -1 );
				$filter_data['branch_ids'] = array( -1 );
				$filter_data['department_ids'] = array( -1 );
				$filter_data['user_title_ids'] = array( -1 );
				$filter_data['group_ids'] = array( -1 );

				$filter_data['column_ids'] = array(
												'-1010-employee_number',
												'-1060-first_name',
												'-1080-last_name',
												'-1100-default_branch',
												'-1110-default_department',
												'-1200-barcode',
												);

				$filter_data['primary_sort'] = '-1080-last_name';
				$filter_data['secondary_sort'] = '-1100-default_branch';
			}
		}


		$ulf = TTnew( 'UserListFactory' );
		$all_array_option = array('-1' => TTi18n::gettext('-- All --'));

		//Get include employee list.

		if ( !isset($filter_data['include_user_ids']) ) {
				$filter_data['include_user_ids'] = NULL;
		}
		$ulf->getSearchByCompanyIdAndArrayCriteria( $current_company->getId(), array('permission_children_ids' => $permission_children_ids ) );

		$user_options = $ulf->getArrayByListFactory( $ulf, FALSE, TRUE );
		$filter_data['src_include_user_options'] = Misc::arrayDiffByKey( (array)$filter_data['include_user_ids'], $user_options );
		$filter_data['selected_include_user_options'] = Misc::arrayIntersectByKey( (array)$filter_data['include_user_ids'], $user_options );

		//Get exclude employee list
		if ( !isset($filter_data['exclude_user_ids']) ) {
				$filter_data['exclude_user_ids'] = NULL;
		}
		$exclude_user_options = Misc::prependArray( $all_array_option, $ulf->getArrayByListFactory( $ulf, FALSE, TRUE ) );
		$filter_data['src_exclude_user_options'] = Misc::arrayDiffByKey( (array)$filter_data['exclude_user_ids'], $user_options );
		$filter_data['selected_exclude_user_options'] = Misc::arrayIntersectByKey( (array)$filter_data['exclude_user_ids'], $user_options );

		//Get employee status list.
		if ( !isset($filter_data['user_status_ids']) ) {
				$filter_data['user_status_ids'] = NULL;
		}
		$user_status_options = Misc::prependArray( $all_array_option, $ulf->getOptions('status') );
		$filter_data['src_user_status_options'] = Misc::arrayDiffByKey( (array)$filter_data['user_status_ids'], $user_status_options );
		$filter_data['selected_user_status_options'] = Misc::arrayIntersectByKey( (array)$filter_data['user_status_ids'], $user_status_options );

		//Get Employee Groups
		if ( !isset($filter_data['group_ids']) ) {
				$filter_data['group_ids'] = NULL;
		}
		$uglf = TTnew( 'UserGroupListFactory' );
		$group_options = Misc::prependArray( $all_array_option, $uglf->getArrayByNodes( FastTree::FormatArray( $uglf->getByCompanyIdArray( $current_company->getId() ), 'TEXT', TRUE) ) );
		$filter_data['src_group_options'] = Misc::arrayDiffByKey( (array)$filter_data['group_ids'], $group_options );
		$filter_data['selected_group_options'] = Misc::arrayIntersectByKey( (array)$filter_data['group_ids'], $group_options );

		//Get branches
		if ( !isset($filter_data['branch_ids']) ) {
				$filter_data['branch_ids'] = NULL;
		}
		$blf = TTnew( 'BranchListFactory' );
		$blf->getByCompanyId( $current_company->getId() );
		$branch_options = Misc::prependArray( $all_array_option, $blf->getArrayByListFactory( $blf, FALSE, TRUE ) );
		$filter_data['src_branch_options'] = Misc::arrayDiffByKey( (array)$filter_data['branch_ids'], $branch_options );
		$filter_data['selected_branch_options'] = Misc::arrayIntersectByKey( (array)$filter_data['branch_ids'], $branch_options );

		//Get departments
		if ( !isset($filter_data['department_ids']) ) {
				$filter_data['department_ids'] = NULL;
		}
		$dlf = TTnew( 'DepartmentListFactory' );
		$dlf->getByCompanyId( $current_company->getId() );
		$department_options = Misc::prependArray( $all_array_option, $dlf->getArrayByListFactory( $dlf, FALSE, TRUE ) );
		$filter_data['src_department_options'] = Misc::arrayDiffByKey( (array)$filter_data['department_ids'], $department_options );
		$filter_data['selected_department_options'] = Misc::arrayIntersectByKey( (array)$filter_data['department_ids'], $department_options );

		//Get employee titles
		if ( !isset($filter_data['user_title_ids']) ) {
				$filter_data['user_title_ids'] = NULL;
		}
		$utlf = TTnew( 'UserTitleListFactory' );
		$utlf->getByCompanyId( $current_company->getId() );
		$user_title_options = Misc::prependArray( $all_array_option, $utlf->getArrayByListFactory( $utlf, FALSE, TRUE ) );
		$filter_data['src_user_title_options'] = Misc::arrayDiffByKey( (array)$filter_data['user_title_ids'], $user_title_options );
		$filter_data['selected_user_title_options'] = Misc::arrayIntersectByKey( (array)$filter_data['user_title_ids'], $user_title_options );

		//Get column list
		if ( !isset($filter_data['column_ids']) ) {
				$filter_data['column_ids'] = NULL;
		}
		$filter_data['src_column_options'] = Misc::arrayDiffByKey( (array)$filter_data['column_ids'], $columns );
		$filter_data['selected_column_options'] = Misc::arrayIntersectByKey( (array)$filter_data['column_ids'], $columns );

		//Get primary/secondary order list
		$filter_data['sort_options'] = $columns;
		$filter_data['sort_options']['effective_date_order'] = TTi18n::gettext('Wage Effective Date');
		unset($filter_data['sort_options']['effective_date']);

		$filter_data['sort_direction_options'] = Misc::getSortDirectionArray();

		$saved_report_options = $ugdlf->getByUserIdAndScriptArray( $current_user->getId(), $_SERVER['SCRIPT_NAME']);
		$generic_data['saved_report_options'] = $saved_report_options;
		$smarty->assign_by_ref('generic_data', $generic_data);

		$smarty->assign_by_ref('filter_data', $filter_data);

		$smarty->assign_by_ref('ugdf', $ugdf);

		$smarty->display('report/UserBarcode.tpl');

		break;
}
?>
