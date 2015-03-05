HTML_AJAX needs to update the PEAR_DATA_DIR/HTML_AJAX/JS files each time it is
updated. As well we need to add:

$pear_data_dir = Environment::getBasePath() .'classes'. DIRECTORY_SEPARATOR .'pear'. DIRECTORY_SEPARATOR .'data';

to function clientJSLocation(). 
