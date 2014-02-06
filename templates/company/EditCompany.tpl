{include file="header.tpl" enable_ajax=TRUE body_onload="showProvince(); showLogo(); showLDAPAuthenticationType();"}
<script	language=JavaScript>
{literal}

logo_file_name = {/literal}'{$company_data.logo_file_name}';{literal}
function showLogo() {
	if ( logo_file_name != '' ) {
		document.getElementById('no_logo').style.display = 'none';

		document.getElementById('show_logo').className = '';
		document.getElementById('show_logo').style.display = '';
	} else {
		document.getElementById('no_logo').className = '';
		document.getElementById('no_logo').style.display = '';
	}
}

function setLogo() {
	document.getElementById('logo').src = '{/literal}{$BASE_URL}{literal}/send_file.php?object_type=company_logo&rand=123';

	logo_file_name = true;

	showLogo();
}

function showLDAPAuthenticationType() {
	hideObject('ldap_authentication');

	if ( document.getElementById('ldap_authentication_type_id').value > 0 ) {
		showObject('ldap_authentication');
	}

	if ( document.getElementById('ldap_authentication_type_id').value == 2 ) {
		alert('{/literal}{t}WARNING! With LDAP Only authentication if a connection is not able to be established with your LDAP server you will be locked out of the system completely. Please test the LDAP settings using Enabled w/Local Fallback mode prior to choosing this option.{/t}{literal}');
	}
}

var loading = false;
var hwCallback = {
		getProvinceOptions: function(result) {
			if ( result != false ) {
				province_obj = document.getElementById('province');
				selected_province = document.getElementById('selected_province').value;

				populateSelectBox( province_obj, result, selected_province);
			}
			loading = false;
		}
	}

var remoteHW = new AJAX_Server(hwCallback);

function showProvince() {
	country = document.getElementById('country').value;
	remoteHW.getProvinceOptions( country );
}

{/literal}
</script>

<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">

		<form method="post" action="{$smarty.server.SCRIPT_NAME}">
		    <div id="contentBoxTwoEdit">

				{if !$cf->Validator->isValid()}
					{include file="form_errors.tpl" object="cf"}
				{/if}

				<table class="editTable">
				{if $permission->Check('company','edit')}
				<tr>
					<td class="cellLeftEditTable">
						{t}ID:{/t}
					</td>
					<td class="cellRightEditTable">
						{$company_data.id|default:"N/A"}
					</td>
				</tr>

				<tr onClick="showHelpEntry('parent')">
					<td class="{isvalid object="cf" label="parent" value="cellLeftEditTable"}">
						{t}Parent:{/t}
					</td>
					<td class="cellRightEditTable">
						<select name="company_data[parent]">
							{html_options options=$company_data.company_list_options selected=$company_data.parent}
						</select>
					</td>
				</tr>

				<tr onClick="showHelpEntry('status')">
					<td class="{isvalid object="cf" label="status" value="cellLeftEditTable"}">
						{t}Status:{/t}
					</td>
					<td class="cellRightEditTable">
						<select name="company_data[status]">
							{html_options options=$company_data.status_options selected=$company_data.status}
						</select>
					</td>
				</tr>

				<tr onClick="showHelpEntry('product_edition')">
					<td class="{isvalid object="cf" label="product_edition" value="cellLeftEditTable"}">
						{t}Product Edition:{/t}
					</td>
					<td class="cellRightEditTable">
						<select name="company_data[product_edition]">
							{html_options options=$company_data.product_edition_options selected=$company_data.product_edition}
						</select>
					</td>
				</tr>
				{else}
				<tr onClick="showHelpEntry('product_edition')">
					<td class="{isvalid object="cf" label="product_edition" value="cellLeftEditTable"}">
						{t}Product Edition:{/t}
					</td>
					<td class="cellRightEditTable">
						{$company_data.product_edition_options[$company_data.product_edition]}
					</td>
				</tr>
				{/if}

				<tr onClick="showHelpEntry('name')">
					<td class="{isvalid object="cf" label="name" value="cellLeftEditTable"}">
						{t}Full Name:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="40" name="company_data[name]" value="{$company_data.name}" {if $config_vars.other.primary_company_id == $company_data.id AND getTTProductEdition() > 10}disabled{/if}>
						{if $config_vars.other.primary_company_id == $company_data.id AND getTTProductEdition() > 10}
							<input type="hidden" name="company_data[name]" value="{$company_data.name}">
						{/if}
					</td>
				</tr>

				<tr onClick="showHelpEntry('short_name')">
					<td class="{isvalid object="cf" label="short_name" value="cellLeftEditTable"}">
						{t}Short Name:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="17" name="company_data[short_name]" value="{$company_data.short_name}">
					</td>
				</tr>

				<tr onClick="showHelpEntry('industry_id')">
					<td class="{isvalid object="cf" label="industry_id" value="cellLeftEditTable"}">
						{t}Industry:{/t}
					</td>
					<td class="cellRightEditTable">
						<select name="company_data[industry_id]">
							{html_options options=$company_data.industry_options selected=$company_data.industry_id}
						</select>
					</td>
				</tr>

				<tr onClick="showHelpEntry('name')">
					<td class="{isvalid object="cf" label="business_number" value="cellLeftEditTable"}">
						{t}Business / Employer Identification Number:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="company_data[business_number]" value="{$company_data.business_number}">
					</td>
				</tr>

				<tr onClick="showHelpEntry('address1')">
					<td class="{isvalid object="cf" label="address1" value="cellLeftEditTable"}">
						{t}Address (Line 1):{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="company_data[address1]" value="{$company_data.address1}">
					</td>
				</tr>

				<tr onClick="showHelpEntry('address2')">
					<td class="{isvalid object="cf" label="address2" value="cellLeftEditTable"}">
						{t}Address (Line 2):{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="company_data[address2]" value="{$company_data.address2}">
					</td>
				</tr>

				<tr onClick="showHelpEntry('city')">
					<td class="{isvalid object="cf" label="city" value="cellLeftEditTable"}">
						{t}City:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="company_data[city]" value="{$company_data.city}">
					</td>
				</tr>

				<tr onClick="showHelpEntry('country')">
					<td class="{isvalid object="cf" label="country" value="cellLeftEditTable"}">
						{t}Country:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="country" name="company_data[country]" onChange="showProvince()">
							{html_options options=$company_data.country_options selected=$company_data.country}
						</select>
					</td>
				</tr>

				<tr onClick="showHelpEntry('province')">
					<td class="{isvalid object="cf" label="province" value="cellLeftEditTable"}">
						{t}Province / State:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="province" name="company_data[province]">
						</select>
						<input type="hidden" id="selected_province" value="{$company_data.province}">
					</td>
				</tr>

				<tr onClick="showHelpEntry('postal_code')">
					<td class="{isvalid object="cf" label="postal_code" value="cellLeftEditTable"}">
						{t}Postal / ZIP Code:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="company_data[postal_code]" value="{$company_data.postal_code}">
					</td>
				</tr>

				<tr onClick="showHelpEntry('work_phone')">
					<td class="{isvalid object="cf" label="work_phone" value="cellLeftEditTable"}">
						{t}Phone:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="company_data[work_phone]" value="{$company_data.work_phone}">
					</td>
				</tr>

				<tr onClick="showHelpEntry('fax_phone')">
					<td class="{isvalid object="cf" label="fax_phone" value="cellLeftEditTable"}">
						{t}Fax:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="company_data[fax_phone]" value="{$company_data.fax_phone}">
					</td>
				</tr>

				<tr onClick="showHelpEntry('admin_contact')">
					<td class="{isvalid object="cf" label="admin_contact" value="cellLeftEditTable"}">
						{t}Administrative Contact:{/t}
					</td>
					<td class="cellRightEditTable">
						<select name="company_data[admin_contact]">
							{html_options options=$company_data.user_list_options selected=$company_data.admin_contact}
						</select>
					</td>
				</tr>

				<tr onClick="showHelpEntry('billing_contact')">
					<td class="{isvalid object="cf" label="billing_contact" value="cellLeftEditTable"}">
						{t}Billing Contact:{/t}
					</td>
					<td class="cellRightEditTable">
						<select name="company_data[billing_contact]">
							{html_options options=$company_data.user_list_options selected=$company_data.billing_contact}
						</select>
					</td>
				</tr>

				<tr onClick="showHelpEntry('support_contact')">
					<td class="{isvalid object="cf" label="support_contact" value="cellLeftEditTable"}">
						{t}Primary Support Contact:{/t}
					</td>
					<td class="cellRightEditTable">
						<select name="company_data[support_contact]">
							{html_options options=$company_data.user_list_options selected=$company_data.support_contact}
						</select>
					</td>
				</tr>

				{if isset($company_data.other_field_names.other_id1) }
					<tr onClick="showHelpEntry('other_id1')">
						<td class="{isvalid object="cf" label="other_id1" value="cellLeftEditTable"}">
							{$company_data.other_field_names.other_id1}:
						</td>
						<td class="cellRightEditTable">
							<input type="text" name="company_data[other_id1]" value="{$company_data.other_id1}">
						</td>
					</tr>
				{/if}

				{if isset($company_data.other_field_names.other_id2) }
				<tr onClick="showHelpEntry('other_id2')">
					<td class="{isvalid object="cf" label="other_id2" value="cellLeftEditTable"}">
						{$company_data.other_field_names.other_id2}:
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="company_data[other_id2]" value="{$company_data.other_id2}">
					</td>
				</tr>
				{/if}
				{if isset($company_data.other_field_names.other_id3) }
				<tr onClick="showHelpEntry('other_id3')">
					<td class="{isvalid object="cf" label="other_id3" value="cellLeftEditTable"}">
						{$company_data.other_field_names.other_id3}:
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="company_data[other_id3]" value="{$company_data.other_id3}">
					</td>
				</tr>
				{/if}
				{if isset($company_data.other_field_names.other_id4) }
					<tr onClick="showHelpEntry('other_id4')">
						<td class="{isvalid object="cf" label="other_id4" value="cellLeftEditTable"}">
							{$company_data.other_field_names.other_id4}:
						</td>
						<td class="cellRightEditTable">
							<input type="text" name="company_data[other_id4]" value="{$company_data.other_id4}">
						</td>
					</tr>
				{/if}
				{if isset($company_data.other_field_names.other_id5) }
					<tr onClick="showHelpEntry('other_id5')">
						<td class="{isvalid object="cf" label="other_id5" value="cellLeftEditTable"}">
							{$company_data.other_field_names.other_id5}:
						</td>
						<td class="cellRightEditTable">
							<input type="text" name="company_data[other_id5]" value="{$company_data.other_id5}">
						</td>
					</tr>
				{/if}

				<tr class="tblHeader">
					<td colspan="100">
						{t}Direct Deposit (EFT){/t}
					</td>
				</td>
				<tr onClick="showHelpEntry('originator_id')">
					<td class="{isvalid object="cf" label="originator_id" value="cellLeftEditTable"}">
						{t}Originator ID / Immediate Origin:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="company_data[originator_id]" value="{$company_data.originator_id}">
					</td>
				</tr>

				<tr onClick="showHelpEntry('data_center_id')">
					<td class="{isvalid object="cf" label="originator_id" value="cellLeftEditTable"}">
						{t}Data Center / Immediate Destination:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="company_data[data_center_id]" value="{$company_data.data_center_id}">
					</td>
				</tr>

				<tr class="tblHeader">
					<td colspan="100">
						{t}Company Settings{/t}
					</td>
				</td>

				{if DEMO_MODE != TRUE}
				<tr onClick="showHelpEntry('logo')">
					<td class="{isvalid object="cf" label="logo" value="cellLeftEditTable"}">
						{t}Logo:{/t} <a href="javascript:Upload('company_logo','');"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_popup.gif"></a>
					</td>
					<td class="cellRightEditTable">
						<span id="show_logo" {if $company_data.logo_file_name == FALSE}style="display:none"{/if}>
							<img id="logo" name="logo" src="{$BASE_URL}/send_file.php?object_type=company_logo" style="width:auto; height:42px;">
						</span>
						<span id="no_logo" style="display:none">
							<b>{t}Click the "..." icon to upload a company logo. (170px by 40px){/t}</b>
						</span>
					</td>
				</tr>
				{/if}

				<tr onClick="showHelpEntry('enable_second_last_name')">
					<td class="{isvalid object="cf" label="enable_second_last_name" value="cellLeftEditTable"}">
						{t}Enable Second Surname:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="checkbox" class="checkbox" name="company_data[enable_second_last_name]" value="1" {if $company_data.enable_second_last_name == TRUE}checked{/if}>
					</td>
				</tr>

				{if !isset($config_vars.other.enable_ldap) OR ( isset($config_vars.other.enable_ldap) AND $config_vars.other.enable_ldap == TRUE )}
				<tr class="tblHeader">
					<td colspan="100">
						{t}LDAP Authentication{/t}
					</td>
				</td>

				<tr onClick="showHelpEntry('ldap_authentication_type_id')">
					<td class="{isvalid object="cf" label="ldap_authentication_type_id" value="cellLeftEditTable"}">
						{t}LDAP Authentication:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="ldap_authentication_type_id" name="company_data[ldap_authentication_type_id]" onChange="showLDAPAuthenticationType()">
							{html_options options=$company_data.ldap_authentication_type_options selected=$company_data.ldap_authentication_type_id}
						</select>
					</td>
				</tr>

				<tbody id="ldap_authentication" style="display:none" >
				{if !function_exists('ldap_connect')}
				<tr class="tblDataError">
					<td colspan="100">
						<b>{t}ERROR: LDAP extension for PHP is not installed! LDAP authentication will not function.{/t}</b>
					</td>
				</td>
				{/if}

				<tr onClick="showHelpEntry('ldap_host')">
					<td class="{isvalid object="cf" label="ldap_host" value="cellLeftEditTable"}">
						{t}Server:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="20" name="company_data[ldap_host]" value="{$company_data.ldap_host}">
						{t}(ie: ldap.example.com or ldaps://ldap.example.com for SSL){/t}
					</td>
				</tr>

				<tr onClick="showHelpEntry('ldap_port')">
					<td class="{isvalid object="cf" label="ldap_port" value="cellLeftEditTable"}">
						{t}Port:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="5" name="company_data[ldap_port]" value="{$company_data.ldap_port}">
						{t}(ie: 389 or 636 for SSL){/t}
					</td>
				</tr>

				<tr onClick="showHelpEntry('ldap_bind_user_name')">
					<td class="{isvalid object="cf" label="ldap_bind_user_name" value="cellLeftEditTable"}">
						{t}Bind User Name:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="10" name="company_data[ldap_bind_user_name]" value="{$company_data.ldap_bind_user_name}">
						{t}(Used to search for the {$APPLICATION_NAME} user, leave blank for anonymous binding){/t}
					</td>
				</tr>

				<tr onClick="showHelpEntry('ldap_bind_password')">
					<td class="{isvalid object="cf" label="ldap_bind_password" value="cellLeftEditTable"}">
						{t}Bind Password:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="10" name="company_data[ldap_bind_password]" value="{$company_data.ldap_bind_password}">
					</td>
				</tr>

				<tr onClick="showHelpEntry('ldap_base_dn')">
					<td class="{isvalid object="cf" label="ldap_base_dn" value="cellLeftEditTable"}">
						{t}Base DN:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="25" name="company_data[ldap_base_dn]" value="{$company_data.ldap_base_dn}">
						{t}(ie: ou=People,dc=example,dc=com){/t}
					</td>
				</tr>

				<tr onClick="showHelpEntry('ldap_bind_attribute')">
					<td class="{isvalid object="cf" label="ldap_bind_attribute" value="cellLeftEditTable"}">
						{t}Bind Attribute:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="18" name="company_data[ldap_bind_attribute]" value="{$company_data.ldap_bind_attribute}">
						{t}(For binding the LDAP user. ie: AD/openLDAP: userPrincipalName, Mac OSX: uid){/t}
					</td>
				</tr>

				<tr onClick="showHelpEntry('ldap_user_filter')">
					<td class="{isvalid object="cf" label="ldap_user_filter" value="cellLeftEditTable"}">
						{t}User Filter:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="25" name="company_data[ldap_user_filter]" value="{$company_data.ldap_user_filter}">
						{t}(Additional filter parameters, ie: is_timetrex_user=1){/t}
					</td>
				</tr>

				<tr onClick="showHelpEntry('ldap_login_attribute')">
					<td class="{isvalid object="cf" label="ldap_login_attribute" value="cellLeftEditTable"}">
						{t}Login Attribute:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="18" name="company_data[ldap_login_attribute]" value="{$company_data.ldap_login_attribute}">
						{t}(For searching the LDAP user. ie: AD: sAMAccountName, openLDAP: dn, Mac OSX: dn){/t}
					</td>
				</tr>
				</tbody>
				{/if}

				</table>
		</div>

		<div id="contentBoxFour">
			<input type="submit" class="btnSubmit" name="action:submit" value="{t}Submit{/t}" onClick="return singleSubmitHandler(this)">
		</div>

		<input type="hidden" name="company_data[id]" value="{$company_data.id}">
		</form>
	</div>
</div>
{include file="footer.tpl"}
