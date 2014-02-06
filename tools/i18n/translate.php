<?php
/*
 * This script when passed a gettext *.POT, or *.PO file will
 * attempt to use a free online service to translate each string
 * to the specified language. 
 *
 * This should hopefully serve as a good STARTING point for further
 * human transation.
 *
 * This file will first create batched input files ready for translation.
 * It will then load the translated files and create a messages.po file from them.
 *
 * Take .PO file and create small HTML batch files for translations
 * php translate.php -s ../../interface/locale/fr_FR/LC_MESSAGES/messages.po ./tr_batches.html
 *
 * Translate HTML batch files back into .PO file
 * php translate.php -t ../../interface/locale/fr_FR/LC_MESSAGES/messages.po ./tr_batches.html1 fr.po
 *
 */

set_include_path( '../../classes'. DIRECTORY_SEPARATOR . 'pear' . PATH_SEPARATOR . get_include_path() );
require_once('../../classes/pear/File/Gettext.php');

/*
function translate( $str, $dest_lang, $translator ) {
	return translate_$translator($str, $dest_lang);
	//return 'T'.$str;
}
*/
if ( $argc < 3 OR in_array ($argv[1], array('--help', '-help', '-h', '-?') ) ) {
	$help_output = "Usage: translate.php [OPTIONS] \n";
	$help_output .= "  Options:\n";
	$help_output .= "    -s 	[.POT or .PO] [OUT HTML]\n";
	$help_output .= "    	 	Create a source translation file, suitable to be translated on mass.\n";
	$help_output .= "    -t 	[.POT or .PO] [IN HTML] [OUTFILE]\n";

	echo $help_output;

} else {
	//Handle command line arguments
	$last_arg = count($argv)-1;
	
	if ( in_array('-s', $argv) ) {
		$create_source = TRUE;
	} else {
		$create_source = FALSE;
	}
	
	if ( isset($argv[$last_arg-2]) AND $argv[2] != '' ) {
		if ( !file_exists( $argv[2] ) OR !is_readable( $argv[2] ) ) {
			echo ".POT or .PO File: ". $argv[2] ." does not exists or is not readable!\n";
		} else {
			$source_file = $argv[2];
		}

		if ( $create_source == TRUE ) {
			$outfile = $argv[3];
			$infile = NULL;
		} else {
			$infile = $argv[3];
			$outfile = $argv[4];
		}
		echo "In File: $infile\n";
		echo "Out File: $outfile\n";
		
		//Use Pears FILE_GetText package		
		$gtFile = File_Gettext::factory('PO');
		$gtFile->load($source_file);

		if ( $create_source == TRUE ) {
			$batch_size = 1000;
			$batch = 0;
			$prev_batch = 0;
			$i=0;
			$out = NULL;
			$max=count($gtFile->strings)-1;
			echo "Max: $max\n";
			foreach ($gtFile->strings as $msgid => $msgstr ) {
				//echo "$i. $msgid\n";
				
				if ( $i == 0 OR $out == NULL ) {
					echo "I = 0 OR Batch = 0\n"; 
					$out  = "<html>\n";
					$out .= "<body><pre>\n";					
				}
								
				if ( $i > 0 AND ( $i % $batch_size == 0 OR $i == $max ) ) {
					$batch++;
					echo "New Batch = $batch\n"; 					
				}
				
				$out .= '<span class="'.$i.'">'. $msgid ."</span><br>\n";
				//$out .= $i.': '. str_replace('<br>', '(11)', $msgid) ."<br>\n";
				
				if ( $batch != $prev_batch ) {
					echo "Writing...\n";
					$out .= "</pre></body>\n";
					$out .= "</html>\n";					
					
					//Write the file.
					file_put_contents( $outfile.$batch, $out );
					
					$out = NULL;					
				}
				
							
				if ( $i > 20 ) {
					//break;
				}
				
				$prev_batch = $batch;
				$i++;
			}
		} else {
			//Load translated HTML files.
			echo "Loading Translated File\n";
			
			$file_contents = file_get_contents( $infile );
			$file_contents = preg_replace('/<head>.*<\/head>/iu', '', $file_contents);
			$file_contents = preg_replace('/<base.*>/iu', '', $file_contents);
			$file_contents = preg_replace('/<\/span>([\s]*)<br>/iu', '</span>', $file_contents);
			$file_contents = preg_replace('/ :/iu', ':', $file_contents);
			$file_contents = str_replace( array('<html>', '</html>', '<body>', '</body>', '<pre>','</pre>') , '', $file_contents);
			
			$lines = explode('</span>', $file_contents);
			//var_dump($lines);
			if ( is_array($lines) ) {
				echo "Total Lines: ". count($lines) ."\n";

				//Create a line # to msgid mapping first.
				$i=0;
				foreach( $gtFile->strings as $msgid => $msgstr) {
					$line_mapping[$i] = $msgid;
					$i++;
				}
				unset($msgid, $msgstr);
				//var_dump($line_mapping);
				
				foreach( $lines as $line ) {
					//Parse the string number
					//if ( preg_match('/\(([0-9]{1,6})\)\s(.*)/i', trim($line), $matches) == TRUE ) {
					if ( preg_match('/<span class=\"([0-9]{1,6})\">(.*)/i', trim($line), $matches) == TRUE ) {
						//var_dump($matches);
						if ( is_array($matches) AND isset($matches[1]) AND isset($matches[2]) ) {
							//Find msgid from line #
							if ( isset($line_mapping[$matches[1]]) ) {
								$msgid = $line_mapping[$matches[1]];
								//$msgstr = preg_replace('/\s\"\s/iu', '"', html_entity_decode($matches[2] ) );
								$msgstr = preg_replace('/\s\"\s/iu', '"', $matches[2] );
								//$msgstr = str_replace('"', '\"', $matches[2] );
								echo $matches[1] .". Translating: ". $msgid ."\n";
								echo "              To: ". $msgstr ."\n";
								$gtFile->strings[$msgid] = $msgstr;
							}
						} else {
							echo "ERROR parsing line!\n";
						}
					}
					//break;
				}
			}
			
			$gtFile->Save( $outfile );
				
		}		
	}
}
?>
