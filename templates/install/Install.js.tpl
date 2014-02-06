<script	language=JavaScript>

{literal}

function toggleNextButton() {
	button = document.getElementById('next_button');
	if ( button.disabled == true ) {
		button.disabled = false;
	} else {
		button.disabled = true;
	}

	return true;
}

function toggleLicenseAccept() {
	license_accept = document.getElementById('license_accept');
	if ( license_accept.checked == true ) {
		license_accept.checked = false;
	} else {
		license_accept.checked = true;
	}

	return true;
}

function clearSelect(src_obj) {
	for (i=0; i < src_obj.options.length; i++) {
		src_obj.options[i] = null;
		i=i - 1;
	}
}

function populateSelectBox( select_box_obj, options, selected, include_blank) {
	clearSelect(select_box_obj);

	if ( include_blank == true ) {
		select_box_obj.options[0] = new Option('--', 0);

		var i=1;
	} else {
		var i=0;
	}

	if ( options != null ) {
		for ( x in options ) {
			select_box_obj.options[i] = new Option(options[x], x);
			if ( selected == x ) {
				select_box_obj.options[i].selected = true;
			}

			var i = i + 1;
		}
	}

	return true;
}

function showHelpEntry(objectID) {
	return true;
}

function showDatabaseTypeWarning() {
	if ( document.getElementById('type').value == 'mysqli' || document.getElementById('type').value == 'mysqlt' ) {
		alert({/literal}'{t}WARNING: Using MySQL is NOT recommended if you have more or plan on growing to more than 25 employees, if you have employees in multiple timezones, or if you plan on using this system for mission critical purposes. \\n\\nMySQL lacks proper timezone support, is orders of magnitude slower in processing some of the complex queries that are required and it lacks support for DDL transactions, so if an error occurs during an upgrade your data will become corrupt and you must restore from backup. \\n\\nWe recommend using PostgreSQL instead as it does not exhibit any of these shortcomings. You have been warned!{/t}'{literal});
	}
}

var submitButtonPressed = false;
{/literal}
</script>