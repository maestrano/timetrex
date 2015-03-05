<?php

require_once('../../../../../includes/global.inc.php');
require_once('../../../../GovernmentForms/GovernmentForms.class.php');
$gf = new GovernmentForms();
$gf->tcpdf_dir = '../tcpdf';
$gf->fpdi_dir = '../fpdi';


    $return1040 = $gf->getFormObject( 'RETURN1040', 'US' );
    $return1040->return_created_timestamp = '2001-12-17T09:30:47Z';
    $return1040->year = '1000';
    $return1040->tax_period_begin_date =  '1967-08-13';
    $return1040->tax_period_end__date = '1967-08-13';
    $return1040->software_id = '00000000'; 
    $return1040->originator_efin = '000000';
    $return1040->originator_type_code = 'FinancialAgent';
    $return1040->pin_type_code = 'Practitioner';
    $return1040->jurat_disclosure_code = 'Practitioner PIN';
    $return1040->pin_entered_by = 'Taxpayer'; 
    $return1040->signature_date = '1967-08-13';
    $return1040->return_type = '1040A';
    $return1040->ssn = '000000000';
    $return1040->name = 'A#';
    $return1040->name_control = 'A';
    $return1040->address1 = '0'; 
    $return1040->city = 'A';
    $return1040->state = 'SC';
    $return1040->zip_code = '00000';
    $return1040->ip_address = '0.0.0.0';
    $return1040->ip_date = '1967-08-13';
    $return1040->ip_time = '00:00:00';    
    $return1040->timezone = 'HS';
    
    $gf->addForm( $return1040 );

    $fw2_obj = $gf->getFormObject( 'w2', 'US' );

    $fw2_obj->setType( 'government' );
    //$fw2_obj->setType( 'employee' );

    $fw2_obj->setDebug(FALSE);
    $fw2_obj->setShowBackground(TRUE);
    $fw2_obj->year = 2011;
    $fw2_obj->ein = '12-3456789';
    $fw2_obj->trade_name = 'ABC Company';
    $fw2_obj->company_address1 = '#1232 Main St';
    $fw2_obj->company_address2 = '123 #Suite';
    $fw2_obj->company_city = 'New York';
    $fw2_obj->company_state = 'NY';
    $fw2_obj->company_zip_code = '12345';
    /*
    $ee_data = array(
                        'ssn' => '123 456 789',
                        'address1' => '#1232 Main St',
                        'address2' => 'Suite 123',
                        'city' => 'New York',
                        'state' => 'NY',
                        'zip_code' => '12345',

                        //'control_number' => '0001',

                        'first_name' => 'john',
                        'middle_name' => 'george',
                        'last_name' => 'doe',

                        'l1' => 223456.99,
                        'l2' => 223456.99,
                        'l3' => 223456.99,
                        'l4' => 223456.99,
                        'l5' => 223456.99,
                        'l6' => 223456.99,
                        'l7' => 223456.99,
                        'l8' => 223456.99,
                        'l10' => 223456.99,
                        'l11' => 223456.99,
                        'l12a_code' => 1,
                        'l12a' => 223456.99,
                        'l12b_code' => 2,
                        'l12b' => 223456.99,
                        'l12c_code' => 3,
                        'l12c' => 223456.99,
                        'l12d_code' => 4,
                        'l12d' => 223456.99,

                        'l13a' => TRUE,
                        'l13b' => TRUE,
                        'l13c' => TRUE,

                        'l14a_name' => 'Test1',
                        'l14a' => 23.55,
                        'l14b_name' => 'Test2',
                        'l14b' => 34.56,
                        'l14c_name' => 'Test3',
                        'l14c' => 23.57,
                        'l14d_name' => 'Test4',
                        'l14d' => 67.58,

                        'l15a_state' => 'NY',
                        'l15a_state_id' => '987654321',
                        'l16a' => '123456789.99',
                        'l17a' => '123456789.99',
                        'l18a' => '123456789.99',
                        'l19a' => '123456789.99',
                        'l20a' => '123456789.99',

                        'l15b_state' => 'NY',
                        'l15b_state_id' => '987654321',
                        'l16b' => '123456789.99',
                        'l17b' => '123456789.99',
                        'l18b' => '123456789.99',
                        'l19b' => '123456789.99',
                        'l20b' => '123456789.99',
                       );
    $fw2_obj->addRecord( $ee_data );

    $ee_data = array(
                        'ssn' => '123 456 789',
                        'address1' => '#1232 Main St',
                        'address2' => 'Suite 123',
                        'city' => 'New York',
                        'state' => 'NY',
                        'zip_code' => '12345',

                        //'control_number' => '0001',

                        'first_name' => 'jane',
                        'middle_name' => 'george',
                        'last_name' => 'doe',

                        'l1' => 223456.99,
                        'l2' => 223456.99,
                        'l3' => 223456.99,
                        'l4' => 223456.99,
                        'l5' => 223456.99,
                        'l6' => 223456.99,
                        'l7' => 223456.99,
                        'l8' => 223456.99,
                        'l10' => 223456.99,
                        'l11' => 223456.99,
                        'l12a_code' => 1,
                        'l12a' => 223456.99,
                        'l12b_code' => 2,
                        'l12b' => 223456.99,
                        'l12c_code' => 3,
                        'l12c' => 223456.99,
                        'l12d_code' => 4,
                        'l12d' => 223456.99,

                        'l13a' => TRUE,
                        'l13b' => TRUE,
                        'l13c' => TRUE,

                        'l14a_name' => 'Test1',
                        'l14a' => 12345.55,
                        'l14b_name' => 'Test2',
                        'l14b' => 12345.56,
                        'l14c_name' => 'Test3',
                        'l14c' => 12345.57,
                        'l14d_name' => 'Test4',
                        'l14d' => 12345.58,

                        'l15a_state' => 'NY',
                        'l15a_state_id' => '987654321',
                        'l16a' => '123456789.99',
                        'l17a' => '123456789.99',
                        'l18a' => '123456789.99',
                        'l19a' => '123456789.99',
                        'l20a' => '123456789.99',

                        'l15b_state' => 'NY',
                        'l15b_state_id' => '987654321',
                        'l16b' => '123456789.99',
                        'l17b' => '123456789.99',
                        'l18b' => '123456789.99',
                        'l19b' => '123456789.99',
                        'l20b' => '123456789.99',
                       );
    $fw2_obj->addRecord( $ee_data );
    */
    $ee_data = array(
                        'ssn' => '123 456 789',
                        'address1' => '#1232 Main St',
                        'address2' => 'Suite #123',
                        'city' => 'New York',
                        'state' => 'NY',
                        'zip_code' => '12345',

                        //'control_number' => '0001',

                        'first_name' => 'George',
                        'middle_name' => 'george',
                        'last_name' => 'doe',

                        //'l1' => -223456.123213,
                        //'l2' => -223456,
                        //'l3' => 223456.99,
                        'l4' => 223456.99,
                        'l5' => 223456.99,
                        'l6' => 223456.99,
                        'l7' => 223456.99,
                        'l8' => 223456.99,
                        'l10' => 223456.99,
                        'l11' => 223456.99,
                        'l12a_code' => 'A',
                        'l12a' => 223456.99,
                        'l12b_code' => 'B',
                        'l12b' => 223456.99,
                        'l12c_code' => 'C',
                        'l12c' => 223456.99,
                        'l12d_code' => 'D',
                        'l12d' => 223456.99,

                        'l13a' => TRUE,
                        'l13b' => TRUE,
                        'l13c' => TRUE,

                        'l14a_name' => 'Test1',
                        'l14a' => 3.55,
                        'l14b_name' => 'Test2',
                        'l14b' => 55.56,
                        'l14c_name' => 'Test3',
                        'l14c' => 1253345.57,
                        'l14d_name' => 'Test4',
                        'l14d' => 13.58,

                        'l15a_state' => 'NY',
                        'l15a_state_id' => '987654321',
                        'l16a' => '123456789.99',
                        'l17a' => '123456789.99',
                        'l18a' => '123456789.99',
                        'l19a' => '123456789.99',
                        'l20a' => '123456789.99',

                        'l15b_state' => 'NY',
                        'l15b_state_id' => '435',
                        'l16b' => '45',
                        'l17b' => '435.99',
                        'l18b' => '345.99',
                        'l19b' => '123434556789.99',
                        'l20b' => '12334456789.99',
                       );
    $fw2_obj->addRecord( $ee_data );

    $gf->addForm( $fw2_obj );


$output = $gf->output( 'xml' );
//file_put_contents( '/tmp/w2.pdf', $output );

file_put_contents( 'w2.xml', $output );
Debug::writeToLog();
?>
