<?php
require_once('../../../includes/global.inc.php');
require_once('../../../includes/CLI.inc.php');
require_once('../../GovernmentForms/GovernmentForms.class.php');
$gf = new GovernmentForms();
$gf->tcpdf_dir = '../tcpdf';
$gf->fpdi_dir = '../fpdi';

$grid_obj = $gf->getFormObject( 'grid' );
$grid_obj->setDebug(FALSE);
$grid_obj->setShowBackground(TRUE);
$grid_obj->setTemplate('../country/ca/templates/t4a-sum-11b.pdf');
$grid_obj->setTemplatePages(2);

$gf->addForm( $grid_obj );

$output = $gf->output( 'PDF' );
file_put_contents( 'grid.pdf', $output );
?>