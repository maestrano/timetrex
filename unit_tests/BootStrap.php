<?php
require_once('../includes/global.inc.php');
//PHPUnit 3.1.9 works with unit tests, but v3.6 fails on ADODB for some reason.
//Need to run phpunit like this: phpunit --bootstrap BootStrap.php --no-globals-backup DateTimeTest

Debug::setBufferOutput(FALSE);
Debug::setEnable(FALSE); //Set to TRUE to see debug output. Leave buffer output FALSE.
Debug::setVerbosity(10);

//This prevent PHPUnit from creating a mock ADODB-lib class and causing a fatal error on redeclaration of its functions.
//See for a possible fix? http://sebastian-bergmann.de/archives/797-Global-Variables-and-PHPUnit.html#content
//Must use --no-globals-backup to get tests to run properly.
$ADODB_INCLUDED_LIB=TRUE;
require_once( Environment::getBasePath() .'classes'. DIRECTORY_SEPARATOR .'adodb'. DIRECTORY_SEPARATOR .'adodb.inc.php');
require_once( Environment::getBasePath() .'classes'. DIRECTORY_SEPARATOR .'adodb'. DIRECTORY_SEPARATOR .'adodb-exceptions.inc.php');
require_once( Environment::getBasePath() .'classes'. DIRECTORY_SEPARATOR .'adodb'. DIRECTORY_SEPARATOR .'adodb-lib.inc.php');

if ( PRODUCTION != FALSE ) {
	echo "DO NOT RUN ON A PRODUCTION SERVER<br>\n";
	exit;
}

set_include_path( get_include_path() . PATH_SEPARATOR . '/usr/share/php'  );

echo "Include Path: ". get_include_path() ."\n";

$profiler = new Profiler( TRUE );
?>
