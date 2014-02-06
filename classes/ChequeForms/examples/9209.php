<?php
require_once('../../../includes/global.inc.php');
require_once('../../ChequeForms/ChequeForms.class.php');
$cf = new ChequeForms();

$cf->tcpdf_dir = '../tcpdf';
$cf->fpdi_dir = '../fpdi';

    $c9209p = $cf->getFormObject( '9209p' );

    $c9209p->setDebug(FALSE);
    $c9209p->setShowBackground(FALSE);

    $c9209p->date = 1342206000;
    $c9209p->amount = 724.0900;

//    $c9209p->stub_left_column =  " Mr. Administrator\n Identification #: 000000000000023\n Net Pay: $724.09\n ";
//    $c9209p->stub_right_column = " Pay Start Date: 24-Jun-12\n Pay End Date: 07-Jul-12\n Payment Date: 13-Jul-12\n ";

    $c9209p->stub_left_column =  "This is really long text that should fit on the left stub column without overlapping any other text or going off the page. This is really long text that should fit on the left stub column without overlapping any other text or going off the page. This is really long text that should fit on the left stub column without overlapping any other text or going off the page. This is really long text that should fit on the left stub column without overlapping any other text or going off the page. This is really long text that should fit on the left stub column without overlapping any other text or going off the page. This is really long text that should fit on the left stub column without overlapping any other text or going off the page.";
    $c9209p->stub_right_column = "This is really long text that should fit on the RIGHT stub column without overlapping any other text or going off the page. This is really long text that should fit on the RIGHT stub column without overlapping any other text or going off the page. This is really long text that should fit on the RIGHT stub column without overlapping any other text or going off the page. This is really long text that should fit on the RIGHT stub column without overlapping any other text or going off the page. This is really long text that should fit on the RIGHT stub column without overlapping any other text or going off the page. This is really long text that should fit on the RIGHT stub column without overlapping any other text or going off the page. This is really long text that should fit on the RIGHT stub column without overlapping any other text or going off the page. This is really long text that should fit on the RIGHT stub column without overlapping any other text or going off the page. This is really long text that should fit on the RIGHT stub column without overlapping any other text or going off the page. ";

    $c9209p->full_name = 'Mr. Administrator';
    $c9209p->address1 = '1719 Main St';
    $c9209p->address2 = 'Unit #461';
    $c9209p->city = 'New York';
    $c9209p->province = 'NY';
    $c9209p->postal_code = '00420';
    $c9209p->country = 'US';
    $c9209p->company_name = 'ABC Company';
    $c9209p->symbol = '$';

    $cf->addForm( $c9209p );


$output = $cf->output( 'PDF' );
file_put_contents( '9209.pdf', $output );
?>

