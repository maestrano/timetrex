<?php
require_once('../../../includes/global.inc.php');
require_once('../../ChequeForms/ChequeForms.class.php');
$cf = new ChequeForms();

$cf->tcpdf_dir = '../tcpdf';
$cf->fpdi_dir = '../fpdi';

    $c9085 = $cf->getFormObject( '9085' );

    $c9085->setDebug(FALSE);
    $c9085->setShowBackground(FALSE);

    $c9085->date = 1342206000;
    $c9085->amount = 724.0900;

//    $c9085->stub_left_column =  " Mr. Administrator\n Identification #: 000000000000023\n Net Pay: $724.09\n ";
//    $c9085->stub_right_column = " Pay Start Date: 24-Jun-12\n Pay End Date: 07-Jul-12\n Payment Date: 13-Jul-12\n ";

    $c9085->stub_left_column =  "This is really long text that should fit on the left stub column without overlapping any other text or going off the page. This is really long text that should fit on the left stub column without overlapping any other text or going off the page. This is really long text that should fit on the left stub column without overlapping any other text or going off the page. This is really long text that should fit on the left stub column without overlapping any other text or going off the page. This is really long text that should fit on the left stub column without overlapping any other text or going off the page. This is really long text that should fit on the left stub column without overlapping any other text or going off the page.";
    $c9085->stub_right_column = "This is really long text that should fit on the RIGHT stub column without overlapping any other text or going off the page. This is really long text that should fit on the RIGHT stub column without overlapping any other text or going off the page. This is really long text that should fit on the RIGHT stub column without overlapping any other text or going off the page. This is really long text that should fit on the RIGHT stub column without overlapping any other text or going off the page. This is really long text that should fit on the RIGHT stub column without overlapping any other text or going off the page. This is really long text that should fit on the RIGHT stub column without overlapping any other text or going off the page. This is really long text that should fit on the RIGHT stub column without overlapping any other text or going off the page. This is really long text that should fit on the RIGHT stub column without overlapping any other text or going off the page. This is really long text that should fit on the RIGHT stub column without overlapping any other text or going off the page. ";

    $c9085->full_name = 'Mr. Administrator';
    $c9085->address1 = '1719 Main St';
    $c9085->address2 = 'Unit #461';
    $c9085->city = 'New York';
    $c9085->province = 'NY';
    $c9085->postal_code = '00420';
    $c9085->country = 'US';
    $c9085->company_name = 'ABC Company';
    $c9085->symbol = '$';

    $cf->addForm( $c9085 );


$output = $cf->output( 'PDF' );
file_put_contents( '9085.pdf', $output );
?>

