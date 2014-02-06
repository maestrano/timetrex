<?php
require_once('../../../../../includes/global.inc.php');
require_once('../../../../GovernmentForms/GovernmentForms.class.php');
$gf = new GovernmentForms();
$gf->tcpdf_dir = '../tcpdf';
$gf->fpdi_dir = '../fpdi';

    $roe_obj = $gf->getFormObject( 'ROE', 'CA' );
    $roe_obj->setDebug(FALSE);
    $roe_obj->setShowBackground(TRUE);
    $roe_obj->company_name = 'ABC Company';
    $roe_obj->company_address1 = '123 Main St';
    $roe_obj->company_address2 = 'Unit #123';
    $roe_obj->company_city = 'New York';
    $roe_obj->company_province = 'NY';
    $roe_obj->company_postal_code = '12345';
    $roe_obj->business_number = '123456789';
    $roe_obj->sin = '492746316';
    $roe_obj->first_name = 'Gale';
    $roe_obj->middle_name = '';
    $roe_obj->last_name = 'Mench';
    $roe_obj->employee_full_name = 'Gale Mench';
    $roe_obj->employee_address1 = '2944 Gordon St';
    $roe_obj->employee_address2 = 'Unit #960';
    $roe_obj->employee_city = 'New York';
    $roe_obj->employee_province = 'NY';
    $roe_obj->employee_postal_code = '00553';
    $roe_obj->title = 'Painter';
    $roe_obj->created_user_first_name = 'ennis';
    $roe_obj->created_user_middle_name = '';
    $roe_obj->created_user_last_name = 'huang';
    $roe_obj->created_user_full_name = 'ennis huang';
    $roe_obj->created_user_work_phone = '00000000000';
    $roe_obj->company_work_phone = '555-555-555';
    $roe_obj->pay_period_type = 10;
    $roe_obj->code_id = 'A';
    //$roe_obj->other_monies_code_id;
    $roe_obj->first_date = '1336543200';
    $roe_obj->last_date = '1336629599';
    $roe_obj->pay_period_end_date = '1336629600';
    $roe_obj->recall_date = '1338134400';
    $roe_obj->insurable_hours = '453454353411.34';
    $roe_obj->insurable_earnings = '565445.23';
    $roe_obj->vacation_pay = '43543512.43';
    $roe_obj->serial = '223232323232';
    $roe_obj->payroll_reference_number = '223232323232';
    $roe_obj->comments = 'ennis huang test';
    $roe_obj->created_date = '1336548824';

    $roe_obj->pay_period_earnings = array( 860.1300, 1204.0000,860.1300 ,
                                        1204.0000, 860.1300,1204.0000,860.1300,1204.0000,860.1300,1204.0000,860.1300,1204.0000, 860.1300, 1204.0000,860.1300,
                                        1204.0000,860.1300,1204.0000,860.1300,1204.0000,860.1300, 1204.0000, 860.1300, 1204.0000, 860.1300, 1204.0000,
                                         860.1300, 1204.0000, 860.1300, 1204.0000, 860.1300, 1204.0000, 860.1300, 1204.0000, 860.1300, 1204.0000, 860.1300, 1204.0000,
                                          860.1300, 1204.0000, 860.1300, 1204.0000, 860.1300, 1204.0000, 860.1300, 1204.0000, 860.1300, 1204.0000, 860.1300,

                                           1204.0000, 860.1300, 1204.0000, 860.1300,);


    $roe_obj->english = TRUE;
    $roe_obj->not_returning = TRUE;
    $roe_obj->recall_cod = 1;
    $roe_obj->other_monies = array('1256556.43','5654612.43','654612.43',);

    $roe_obj->statutory_holiday = array( '4353412.43', '4353412.43', '4353412.43','4353412.43','4353412.43','4353412.43','4353412.43','4353412.43','4353412.43','4353412.43',
                                        '4353412.43','4353412.43','4353412.43','4353412.43', );

    $gf->addForm( $roe_obj );

//$output = $gf->output( 'pdf' );
//file_put_contents( 'roe.pdf', $output );
$output = $gf->output( 'xml' );
//var_dump($output);
file_put_contents( 'roe.blk', $output );
Debug::writeToLog();
?>
