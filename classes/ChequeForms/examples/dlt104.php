<?php
require_once('../../../includes/global.inc.php');
require_once('../../ChequeForms/ChequeForms.class.php');
$cf = new ChequeForms();

$cf->tcpdf_dir = '../tcpdf';
$cf->fpdi_dir = '../fpdi';

    $dlt104_obj = $cf->getFormObject( 'dlt104' );

    $dlt104_obj->setDebug(FALSE);
    $dlt104_obj->setShowBackground(FALSE);

    $dlt104_obj->date = 1342206000;
    $dlt104_obj->amount = 724.0900;

//    $dlt104_obj->stub_left_column =  " Mr. Administrator\n Identification #: 000000000000023\n Net Pay: $724.09\n ";
//    $dlt104_obj->stub_right_column = " Pay Start Date: 24-Jun-12\n Pay End Date: 07-Jul-12\n Payment Date: 13-Jul-12\n ";

    $dlt104_obj->stub_left_column =  "This is really long text that should fit on the left stub column without overlapping any other text or going off the page. This is really long text that should fit on the left stub column without overlapping any other text or going off the page. This is really long text that should fit on the left stub column without overlapping any other text or going off the page. This is really long text that should fit on the left stub column without overlapping any other text or going off the page. This is really long text that should fit on the left stub column without overlapping any other text or going off the page. This is really long text that should fit on the left stub column without overlapping any other text or going off the page.";
    $dlt104_obj->stub_right_column = "This is really long text that should fit on the RIGHT stub column without overlapping any other text or going off the page. This is really long text that should fit on the RIGHT stub column without overlapping any other text or going off the page. This is really long text that should fit on the RIGHT stub column without overlapping any other text or going off the page. This is really long text that should fit on the RIGHT stub column without overlapping any other text or going off the page. This is really long text that should fit on the RIGHT stub column without overlapping any other text or going off the page. This is really long text that should fit on the RIGHT stub column without overlapping any other text or going off the page. This is really long text that should fit on the RIGHT stub column without overlapping any other text or going off the page. This is really long text that should fit on the RIGHT stub column without overlapping any other text or going off the page. This is really long text that should fit on the RIGHT stub column without overlapping any other text or going off the page. ";

    $dlt104_obj->full_name = 'Mr. Administrator';
    $dlt104_obj->address1 = '1719 Main St';
    $dlt104_obj->address2 = 'Unit #461';
    $dlt104_obj->city = 'New York';
    $dlt104_obj->province = 'NY';
    $dlt104_obj->postal_code = '00420';
    $dlt104_obj->country = 'US';
    $dlt104_obj->company_name = 'ABC Company';
    $dlt104_obj->symbol = '$';

    $cf->addForm( $dlt104_obj );


$output = $cf->output( 'PDF' );
file_put_contents( 'dlt104.pdf', $output );
?>

