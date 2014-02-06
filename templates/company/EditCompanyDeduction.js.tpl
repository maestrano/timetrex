<script	language=JavaScript>

{$data.js_arrays}
{literal}

function filterIncludeCount() {
	total = countSelect(document.getElementById('filter_include'));
	writeLayer('filter_include_count', total);
}

function filterExcludeCount() {
	total = countSelect(document.getElementById('filter_exclude'));
	writeLayer('filter_exclude_count', total);
}

function filterUserCount() {
	total = countSelect(document.getElementById('filter_user'));
	writeLayer('filter_user_count', total);
}

function isCountryCalculationID() {
	calculation_id = document.getElementById('calculation_id').value;

	for (i=0; i <= country_calculation_ids.length; i++) {
		if ( country_calculation_ids[i] == calculation_id ) {
			return true;
		}
	}

	return false;
}

function isProvinceCalculationID() {
	calculation_id = document.getElementById('calculation_id').value;

	for (i=0; i <= province_calculation_ids.length; i++) {
		if ( province_calculation_ids[i] == calculation_id ) {
			return true;
		}
	}

	return false;
}

function isDistrictCalculationID() {
	calculation_id = document.getElementById('calculation_id').value;

	for (i=0; i <= district_calculation_ids.length; i++) {
		if ( district_calculation_ids[i] == calculation_id ) {
			return true;
		}
	}

	return false;
}

function showCountryOrProvince(type) {
	if ( document.getElementById('country_id') ) {
		if ( type == null ) {
			calculation_id = document.getElementById('calculation_id').value;
			document.getElementById('country').style.display = 'none';

			if ( isCountryCalculationID() == true ) {
				document.getElementById('country_id').disabled = false;
				document.getElementById('country').className = '';
				document.getElementById('country').style.display = '';
			} else {
				document.getElementById('country_id').disabled = true;
			}
		}

		if ( type == 'country' || type == null ) {
			hideProvince();
			hideDistrict();

			if ( isProvinceCalculationID() == true ) {
				showProvince();
			}
		}
		if ( type == 'province' || type == null ) {
			hideDistrict();

			if ( isDistrictCalculationID() == true ) {
				showDistrict();
			}
		}
	}
}

old_id = fields[{/literal}'{$data.calculation_id}{if $data.country != ''}-{$data.country}{/if}{if $data.province != ''}-{$data.province_id}{/if}'{literal}];
function showCalculation( type ) {
	if ( document.getElementById('id').value == '' ) {
		showCountryOrProvince(type);
	}

	calculation_id = document.getElementById('calculation_id').value;
	country_id = document.getElementById('country_id').value;

	province_id = document.getElementById('province_id').value;

	if ( isCountryCalculationID() == true
			&& isProvinceCalculationID() == true
			&& country_id != 'undefined'
			&& province_id != 'undefined' ) {
		id = calculation_id+'-'+country_id+'-'+province_id;
	} else if ( isCountryCalculationID() == true
					&& country_id != 'undefined') {
		id = calculation_id+'-'+country_id;
	} else {
		id = calculation_id;
	}

	//alert('Calculation ID: '+ calculation_id +' ID: '+ id);

	//Hide old ID
	old_section = document.getElementById(fields[old_id]);
	if ( isUndefined( fields[old_id] ) == false && old_section != null ) {
		old_section.className = 'none';
		old_section.style.display = 'none';

		old_section_fields = old_section.getElementsByTagName('input');
		for (var x = 0; x < old_section_fields.length; x++) {
			old_section_fields[x].disabled = true;
		}

		old_section_fields = old_section.getElementsByTagName('select');
		for (var x = 0; x < old_section_fields.length; x++) {
			old_section_fields[x].disabled = true;
		}
	}

	//alert('Field ID: '+ fields[id] );
	section = document.getElementById(fields[id]);
	if ( isUndefined( fields[id] ) == false && section != null ) {
		section.className = '';
		section.style.display = '';
		section.disabled = false;

		section_fields = section.getElementsByTagName('input');
		for (var x = 0; x < section_fields.length; x++) {
			section_fields[x].disabled = false;
		}

		section_fields = section.getElementsByTagName('select');
		for (var x = 0; x < section_fields.length; x++) {
			section_fields[x].disabled = false;
		}
	}

	old_id = id;
}

function hideProvince() {
	document.getElementById('province').style.display = 'none';
	document.getElementById('province_id').disabled = true;
}

function showProvince() {
	document.getElementById('province_id').disabled = true;
	clearSelect(document.getElementById('province_id'))

	for (var i=0; i <= province_calculation_ids.length; i++) {
		if ( province_calculation_ids[i] == calculation_id ) {
			country_value = document.getElementById('country_id').value;
			var result = remoteHW.getProvinceOptions( country_value );

			if ( result != false ) {
				province_obj = document.getElementById('province_id');

				selected_province = document.getElementById('selected_province').value;

				populateSelectBox( province_obj, result, selected_province, true);
			}

			if ( country_value != 0 ) {
				document.getElementById('province').className = '';
				document.getElementById('province').style.display = '';
				document.getElementById('province_id').disabled = false;
			}
		}
	}
}

function hideDistrict() {
	document.getElementById('district').style.display = 'none';
	document.getElementById('district_id').disabled = true;
}

function showDistrict() {
	//document.getElementById('selected_district').value = document.getElementById('district_id').value;
	document.getElementById('district_id').disabled = true;
	clearSelect(document.getElementById('district_id'));

	for (var i=0; i <= district_calculation_ids.length; i++) {

		if ( district_calculation_ids[i] == calculation_id ) {
			country_value = document.getElementById('country_id').value;
			province_value = document.getElementById('province_id').value;

			district_obj = document.getElementById('district_id');

			result = remoteHW.getProvinceDistrictOptions( country_value, province_value );
			if ( result != false ) {
				selected_district = document.getElementById('selected_district').value;
				populateSelectBox( district_obj, result, selected_district, false);
			} else {
				populateSelectBox( district_obj, null, null, true);
			}

			if ( province_value != 0 ) {
				document.getElementById('district').className = '';
				document.getElementById('district').style.display = '';
				document.getElementById('district_id').disabled = false;
			}

		}

	}

}

//Sync calls
var remoteHW = new AJAX_Server();

{/literal}
</script>
