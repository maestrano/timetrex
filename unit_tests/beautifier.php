#!/usr/bin/php
<?php
require_once('PHP/Beautifier.php');
// Create the instance
$oBeautifier = new PHP_Beautifier();

echo "Filter Directory: ". dirname(__FILE__).'/beautifier_filters' ."\n";
$oBeautifier->addFilterDirectory( dirname(__FILE__).'/beautifier_filters' );

// Add a filter, without any parameter

$oBeautifier->addFilter('TTDefault');

// Set the indent char, number of chars to indent and newline char
$oBeautifier->setIndentChar(' ');
$oBeautifier->setIndentNumber(4);
$oBeautifier->setNewLine("\n");
// Define the input file
$oBeautifier->setInputFile( $argv[1] );
// Define an output file.
//$oBeautifier->setOutputFile( $argv[1] .'.beautified.php');
$oBeautifier->setOutputFile( $argv[1] );

// Process the file. DONT FORGET TO USE IT
$oBeautifier->process();

// Show the file (echo to screen)
//$oBeautifier->show();
// Save the file
$oBeautifier->save();
?>