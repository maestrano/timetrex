{include file="sm_header.tpl" authenticate=FALSE is_report=TRUE disable_global_js=TRUE enable_ajax=TRUE body_onload="showProvince();"}

{include file="install/Install.js.tpl"}

<script	language=JavaScript>

{literal}
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

		<form method="post" action="{$smarty.server.SCRIPT_NAME}" onSubmit="return submitButtonPressed" >
		    <div id="contentBoxTwoEdit">

				{if !$cf->Validator->isValid()}
					{include file="form_errors.tpl" object="cf"}
				{/if}

				<table class="editTable">

				<tr>
					<td class="tblDataWhiteNH" colspan="7" align="right">
						{t}Please enter your company information below.{/t}
					</td>
				</tr>

				<tr onClick="showHelpEntry('name')">
					<td class="{isvalid object="cf" label="name" value="cellLeftEditTable"}">
						{t}Company Full Name:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="company_data[name]" value="{$company_data.name}">
					</td>
				</tr>

				<tr onClick="showHelpEntry('short_name')">
					<td class="{isvalid object="cf" label="short_name" value="cellLeftEditTable"}">
						{t}Company Short Name:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="17" name="company_data[short_name]" value="{$company_data.short_name}"> (ie: America Online = AOL, no spaces)
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

				</table>
		</div>

		<div id="contentBoxFour">
			<input type="submit" class="btnSubmit" id="next_button" name="action:back" value="{t}Back{/t}" onMouseDown="submitButtonPressed = true">
			<input type="submit" class="btnSubmit" id="next_button" name="action:next" value="{t}Next{/t}" onMouseDown="submitButtonPressed = true">
		</div>

		<input type="hidden" name="company_data[id]" value="{$company_data.id}">
		<input type="hidden" name="external_installer" value="{$external_installer}">
		</form>
	</div>
</div>
{include file="footer.tpl"}
