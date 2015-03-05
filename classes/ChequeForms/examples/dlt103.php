<?php
require_once('../../../includes/global.inc.php');
require_once('../../ChequeForms/ChequeForms.class.php');
$cf = new ChequeForms();

$cf->tcpdf_dir = '../tcpdf';
$cf->fpdi_dir = '../fpdi';

    $dlt103_obj = $cf->getFormObject( 'dlt103' );

    $dlt103_obj->setDebug(FALSE);
    $dlt103_obj->setShowBackground(FALSE);

    $dlt103_obj->date = 1342206000;
    $dlt103_obj->amount = 724.0900;

//    $dlt103_obj->stub_left_column =  " Mr. Administrator\n Identification #: 000000000000023\n Net Pay: $724.09\n ";
//    $dlt103_obj->stub_right_column = " Pay Start Date: 24-Jun-12\n Pay End Date: 07-Jul-12\n Payment Date: 13-Jul-12\n ";

    $dlt103_obj->stub_left_column =  "This is really long text that should fit on the left stub column without overlapping any other text or going off the page. This is really long text that should fit on the left stub column without overlapping any other text or going off the page. This is really long text that should fit on the left stub column without overlapping any other text or going off the page. This is really long text that should fit on the left stub column without overlapping any other text or going off the page. This is really long text that should fit on the left stub column without overlapping any other text or going off the page. This is really long text that should fit on the left stub column without overlapping any other text or going off the page.";
    $dlt103_obj->stub_right_column = "This is really long text that should fit on the RIGHT stub column without overlapping any other text or going off the page. This is really long text that should fit on the RIGHT stub column without overlapping any other text or going off the page. This is really long text that should fit on the RIGHT stub column without overlapping any other text or going off the page. This is really long text that should fit on the RIGHT stub column without overlapping any other text or going off the page. This is really long text that should fit on the RIGHT stub column without overlapping any other text or going off the page. This is really long text that should fit on the RIGHT stub column without overlapping any other text or going off the page. This is really long text that should fit on the RIGHT stub column without overlapping any other text or going off the page. This is really long text that should fit on the RIGHT stub column without overlapping any other text or going off the page. This is really long text that should fit on the RIGHT stub column without overlapping any other text or going off the page. ";

    $dlt103_obj->full_name = 'Mr. Administrator';
    $dlt103_obj->address1 = '1719 Main St';
    $dlt103_obj->address2 = 'Unit #461';
    $dlt103_obj->city = 'New York';
    $dlt103_obj->province = 'NY';
    $dlt103_obj->postal_code = '00420';
    $dlt103_obj->country = 'US';
    $dlt103_obj->company_name = 'ABC Company';
    $dlt103_obj->symbol = '$';

    $cf->addForm( $dlt103_obj );


$output = $cf->output( 'PDF' );
file_put_contents( 'dlt103.pdf', $output );
?>

