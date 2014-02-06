<?php

require_once('../../../../../includes/global.inc.php');
require_once('../../../../GovernmentForms/GovernmentForms.class.php');

$gf = new GovernmentForms();
$gf->tcpdf_dir = '../tcpdf';
$gf->fpdi_dir = '../fpdi';

    $return941 = $gf->getFormObject( 'RETURN941', 'US' );
    $return941->TaxPeriodEndDate = '1967-08-13';
    $return941->ReturnType = '941PR';
    $return941->ein = '000000000';
    $return941->BusinessName1 = '#';
    $return941->BusinessNameControl = '-';
    $return941->AddressLine = '-';
    $return941->City = 'A';
    $return941->State = 'WY';
    $return941->ZIPCode = '00000';    
    
    $gf->addForm( $return941 );

    $f941_obj = $gf->getFormObject( '941', 'US' );
    $f941_obj->setDebug(TRUE);
    $f941_obj->setShowBackground(TRUE);
    $f941_obj->year = 2013;
    $f941_obj->ein = '12-3456789';
    $f941_obj->name = 'John Doe';
    $f941_obj->trade_name = 'ABC Company';
    $f941_obj->address = '#1232 Main St';
    $f941_obj->city = 'New York';
    $f941_obj->state = 'NY';
    $f941_obj->zip_code = '12345';

    $f941_obj->quarter = array(1,2,3,4);
    $f941_obj->l1 = 10;
    $f941_obj->l2 = 9999.99;
    $f941_obj->l3 = 9999.99;
	$f941_obj->l4 = TRUE;
    $f941_obj->l5 = 9999.99;

    $f941_obj->l5a = 9999.91;
    $f941_obj->l5b = 9999.92;
    $f941_obj->l5c = 9999.93;
	$f941_obj->l5d = 9999.69;

	$f941_obj->l5f = 9999.59;

    //$f941_obj->l7 = 0.02;
    //$f941_obj->l7b = 9999.99;
    //$f941_obj->l7c = 9999.99;

	//$f941_obj->l8 = 30000.98;
    //$f941_obj->l9 = 30000.99;

    $f941_obj->l11 = 9999.96;
	//$f941_obj->l11 = 99999.96;
    $f941_obj->l12a = 9999.97;
    $f941_obj->l12b = 52;

    $f941_obj->l15a = TRUE;
    $f941_obj->l15b = TRUE;

    //$f941_obj->l16 = 'NY';

    $f941_obj->l16_month1 = 9999.99;
    $f941_obj->l16_month2 = 9999.99;
    $f941_obj->l16_month3 = 9999.99;

    $gf->addForm( $f941_obj );

//$output = $gf->output( 'xml' );
//file_put_contents( '941.xml', $output );

$output = $gf->output( 'pdf' );
file_put_contents( '/tmp/941test.pdf', $output );

Debug::writeToLog();

?>

