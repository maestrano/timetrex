<?php
require_once('../../../includes/global.inc.php');
require_once('../../ChequeForms/ChequeForms.class.php');
$cf = new ChequeForms();

$cf->tcpdf_dir = '../tcpdf';
$cf->fpdi_dir = '../fpdi';

    $cr_standard_form_1 = $cf->getFormObject( 'cr_standard_form_1' );

    $cr_standard_form_1->setDebug(FALSE);
    $cr_standard_form_1->setShowBackground(FALSE);

    $cr_standard_form_1->date = 1342206000;
    $cr_standard_form_1->amount = 724.0900;

    $cr_standard_form_1->stub_left_column =  " Mr. Administrator\n Identification #: 000000000000023\n Net Pay: $724.09\n ";
    $cr_standard_form_1->stub_right_column = " Pay Start Date: 24-Jun-12\n Pay End Date: 07-Jul-12\n Payment Date: 13-Jul-12\n ";

    //$cr_standard_form_1->stub_left_column =  "This is really long text that should fit on the left stub column without overlapping any other text or going off the page. This is really long text that should fit on the left stub column without overlapping any other text or going off the page. This is really long text that should fit on the left stub column without overlapping any other text or going off the page. This is really long text that should fit on the left stub column without overlapping any other text or going off the page. This is really long text that should fit on the left stub column without overlapping any other text or going off the page. This is really long text that should fit on the left stub column without overlapping any other text or going off the page.";
    //$cr_standard_form_1->stub_right_column = "This is really long text that should fit on the RIGHT stub column without overlapping any other text or going off the page. This is really long text that should fit on the RIGHT stub column without overlapping any other text or going off the page. This is really long text that should fit on the RIGHT stub column without overlapping any other text or going off the page. This is really long text that should fit on the RIGHT stub column without overlapping any other text or going off the page. This is really long text that should fit on the RIGHT stub column without overlapping any other text or going off the page. This is really long text that should fit on the RIGHT stub column without overlapping any other text or going off the page. This is really long text that should fit on the RIGHT stub column without overlapping any other text or going off the page. This is really long text that should fit on the RIGHT stub column without overlapping any other text or going off the page. This is really long text that should fit on the RIGHT stub column without overlapping any other text or going off the page. ";

    $cr_standard_form_1->full_name = 'Mr. Administrator';
    $cr_standard_form_1->address1 = '1719 Main St';
    $cr_standard_form_1->address2 = 'Unit #461';
    $cr_standard_form_1->city = 'New York';
    $cr_standard_form_1->province = 'NY';
    $cr_standard_form_1->postal_code = '00420';
    $cr_standard_form_1->country = 'US';
    $cr_standard_form_1->company_name = 'ABC Company';
    $cr_standard_form_1->symbol = '$';

    $cf->addForm( $cr_standard_form_1 );


$output = $cf->output( 'PDF' );
file_put_contents( 'cr_standard_form_1.pdf', $output );
?>

