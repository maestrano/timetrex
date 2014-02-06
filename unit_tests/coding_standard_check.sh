#!/bin/bash
#Requires PEAR PHP_CodeSniffer: pear install PHP_CodeSniffer
if [ $# -eq 0 ] ; then
	phpcs --standard=./Coding_Standard --ignore=adodb,bitmask,cache_lite,fpdf,fpdi,icalcreator,Image_Barcode,jpgraph,misc,pear,SabreAMF,smarty,upload,tcpdf,SoapClient ../classes/
	#phpcs --standard=./coding_standard --ignore=adodb,bitmask,cache_lite,fpdf,fpdi,icalcreator,Image_Barcode,jpgraph,misc,pear,SabreAMF,smarty,upload,tcpdf ../../interface/html5
else
	phpcs --standard=./Coding_Standard --ignore=adodb,bitmask,cache_lite,fpdf,fpdi,icalcreator,Image_Barcode,jpgraph,misc,pear,SabreAMF,smarty,upload,tcpdf,SoapClient $1
fi;

