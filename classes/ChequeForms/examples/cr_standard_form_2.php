<?php
require_once('../../../includes/global.inc.php');
require_once('../../ChequeForms/ChequeForms.class.php');
$cf = new ChequeForms();

$cf->tcpdf_dir = '../tcpdf';
$cf->fpdi_dir = '../fpdi';

    $cr_standard_form_2 = $cf->getFormObject( 'cr_standard_form_2' );

    $cr_standard_form_2->setDebug(FALSE);
    $cr_standard_form_2->setShowBackground(FALSE);

    $cr_standard_form_2->date = 1342206000;
    $cr_standard_form_2->amount = 724.0900;

    $cr_standard_form_2->start_date = 1342206000;
    $cr_standard_form_2->end_date = 1342206000;

    //$cr_standard_form_2->stub_left_column =  " Mr. Administrator\n Identification #: 000000000000023\n Net Pay: $724.09\n ";
    //$cr_standard_form_2->stub_right_column = " Pay Start Date: 24-Jun-12\n Pay End Date: 07-Jul-12\n Payment Date: 13-Jul-12\n ";

    //$cr_standard_form_2->stub_left_column =  "This is really long text that should fit on the left stub column without overlapping any other text or going off the page. This is really long text that should fit on the left stub column without overlapping any other text or going off the page. This is really long text that should fit on the left stub column without overlapping any other text or going off the page. This is really long text that should fit on the left stub column without overlapping any other text or going off the page. This is really long text that should fit on the left stub column without overlapping any other text or going off the page. This is really long text that should fit on the left stub column without overlapping any other text or going off the page.";
    //$cr_standard_form_2->stub_right_column = "This is really long text that should fit on the RIGHT stub column without overlapping any other text or going off the page. This is really long text that should fit on the RIGHT stub column without overlapping any other text or going off the page. This is really long text that should fit on the RIGHT stub column without overlapping any other text or going off the page. This is really long text that should fit on the RIGHT stub column without overlapping any other text or going off the page. This is really long text that should fit on the RIGHT stub column without overlapping any other text or going off the page. This is really long text that should fit on the RIGHT stub column without overlapping any other text or going off the page. This is really long text that should fit on the RIGHT stub column without overlapping any other text or going off the page. This is really long text that should fit on the RIGHT stub column without overlapping any other text or going off the page. This is really long text that should fit on the RIGHT stub column without overlapping any other text or going off the page. ";

    $cr_standard_form_2->full_name = 'Mr. Administrator';
    $cr_standard_form_2->address1 = '1719 Main St';
    $cr_standard_form_2->address2 = 'Unit #461';
    $cr_standard_form_2->city = 'New York';
    $cr_standard_form_2->province = 'NY';
    $cr_standard_form_2->postal_code = '00420';
    $cr_standard_form_2->country = 'US';
    $cr_standard_form_2->company_name = 'ABC Company';
    $cr_standard_form_2->symbol = '$';

    $cf->addForm( $cr_standard_form_2 );


$output = $cf->output( 'PDF' );
file_put_contents( 'cr_standard_form_2.pdf', $output );
?>

