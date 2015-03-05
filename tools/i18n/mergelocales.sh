#!/usr/bin/php
<?php
/*$License$*/
/*
 * File Contributed By: Open Source Consulting, S.A.   San Jose, Costa Rica.
 * http://osc.co.cr
 */

// Merges new strings from messages.pot into messages.po for all
// locales.  Also compiles message.mo file, and calculates stats.
 
$cwd = getcwd();
$path="../../interface/locale/";
$directory = dir($path);
$invalid_dir = array('CVS' => 1, '..' => 2, '.' => 3);
$locales = array();
$index = 0;

while ($arch = $directory->read())
{ 
	if (!isset($invalid_dir[$arch]) && is_dir($path.$arch)){
   	$locales[$index] = $arch;
   	$index ++;
   }
}
 
$directory->close();

asort($locales);
foreach ($locales as $locale){
	echo $locale; 
   $cmd = "./mergelocale.sh $locale";
   exec($cmd); 
}

exec( "php ./calc_l10n_stats.php" );
?>  
