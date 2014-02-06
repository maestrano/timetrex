{include file="header.tpl" enable_ajax=TRUE body_onload="formChangeDetect();showProvince()"}

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

		<form method="post" action="{$smarty.server.SCRIPT_NAME}">
		    <div id="contentBoxTwoEdit">
				{if !$bf->Validator->isValid()}
					{include file="form_errors.tpl" object="bf"}
				{/if}

				<table class="editTable">

				<tr onClick="showHelpEntry('status')">
					<td class="{isvalid object="bf" label="status" value="cellLeftEditTable"}">
						{t}Status:{/t}
					</td>
					<td class="cellRightEditTable">
						<select name="branch_data[status]">
							{html_options options=$branch_data.status_options selected=$branch_data.status}
						</select>
					</td>
				</tr>

				<tr onClick="showHelpEntry('name')">
					<td class="{isvalid object="bf" label="name" value="cellLeftEditTable"}">
						{t}Name:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="branch_data[name]" value="{$branch_data.name}">
					</td>
				</tr>

				<tr onClick="showHelpEntry('manual_id')">
					<td class="{isvalid object="bf" label="manual_id" value="cellLeftEditTable"}">
						{t}Code:{/t}
					</td>
					<td class="cellRightEditTable">
						<input size="8" type="text" name="branch_data[manual_id]" value="{$branch_data.manual_id|default:$branch_data.next_available_manual_id}">
						{if $branch_data.next_available_manual_id != ''}
						{t}Next available code{/t}: {$branch_data.next_available_manual_id}
						{/if}
					</td>
				</tr>

				<tr onClick="showHelpEntry('address1')">
					<td class="{isvalid object="bf" label="address1" value="cellLeftEditTable"}">
						{t}Address (Line 1):{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="branch_data[address1]" value="{$branch_data.address1}">
					</td>
				</tr>

				<tr onClick="showHelpEntry('address2')">
					<td class="{isvalid object="bf" label="address2" value="cellLeftEditTable"}">
						{t}Address (Line 2):{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="branch_data[address2]" value="{$branch_data.address2}">
					</td>
				</tr>

				<tr onClick="showHelpEntry('city')">
					<td class="{isvalid object="bf" label="city" value="cellLeftEditTable"}">
						{t}City:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="branch_data[city]" value="{$branch_data.city}">
					</td>
				</tr>

				<tr onClick="showHelpEntry('country')">
					<td class="{isvalid object="bf" label="country" value="cellLeftEditTable"}">
						{t}Country:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="country" name="branch_data[country]" onChange="showProvince()">
							{html_options options=$branch_data.country_options selected=$branch_data.country}
						</select>
					</td>
				</tr>

				<tr onClick="showHelpEntry('province')">
					<td class="{isvalid object="bf" label="province" value="cellLeftEditTable"}">
						{t}Province / State:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="province" name="branch_data[province]">
						</select>
						<input type="hidden" id="selected_province" value="{$branch_data.province}">
					</td>
				</tr>

				<tr onClick="showHelpEntry('postal_code')">
					<td class="{isvalid object="bf" label="postal_code" value="cellLeftEditTable"}">
						{t}Postal / ZIP Code:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="branch_data[postal_code]" value="{$branch_data.postal_code}">
					</td>
				</tr>

				<tr onClick="showHelpEntry('work_phone')">
					<td class="{isvalid object="bf" label="work_phone" value="cellLeftEditTable"}">
						{t}Phone:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="branch_data[work_phone]" value="{$branch_data.work_phone}">
					</td>
				</tr>

				<tr onClick="showHelpEntry('fax_phone')">
					<td class="{isvalid object="bf" label="fax_phone" value="cellLeftEditTable"}">
						{t}Fax:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="branch_data[fax_phone]" value="{$branch_data.fax_phone}">
					</td>
				</tr>

				{if isset($branch_data.other_field_names.other_id1) }
					<tr onClick="showHelpEntry('other_id1')">
						<td class="{isvalid object="bf" label="other_id1" value="cellLeftEditTable"}">
							{$branch_data.other_field_names.other_id1}:
						</td>
						<td class="cellRightEditTable">
							<input type="text" name="branch_data[other_id1]" value="{$branch_data.other_id1}">
						</td>
					</tr>
				{/if}

				{if isset($branch_data.other_field_names.other_id2) }
				<tr onClick="showHelpEntry('other_id2')">
					<td class="{isvalid object="bf" label="other_id2" value="cellLeftEditTable"}">
						{$branch_data.other_field_names.other_id2}:
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="branch_data[other_id2]" value="{$branch_data.other_id2}">
					</td>
				</tr>
				{/if}
				{if isset($branch_data.other_field_names.other_id3) }
				<tr onClick="showHelpEntry('other_id3')">
					<td class="{isvalid object="bf" label="other_id3" value="cellLeftEditTable"}">
						{$branch_data.other_field_names.other_id3}:
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="branch_data[other_id3]" value="{$branch_data.other_id3}">
					</td>
				</tr>
				{/if}
				{if isset($branch_data.other_field_names.other_id4) }
					<tr onClick="showHelpEntry('other_id4')">
						<td class="{isvalid object="bf" label="other_id4" value="cellLeftEditTable"}">
							{$branch_data.other_field_names.other_id4}:
						</td>
						<td class="cellRightEditTable">
							<input type="text" name="branch_data[other_id4]" value="{$branch_data.other_id4}">
						</td>
					</tr>
				{/if}
				{if isset($branch_data.other_field_names.other_id5) }
					<tr onClick="showHelpEntry('other_id5')">
						<td class="{isvalid object="bf" label="other_id5" value="cellLeftEditTable"}">
							{$branch_data.other_field_names.other_id5}:
						</td>
						<td class="cellRightEditTable">
							<input type="text" name="branch_data[other_id5]" value="{$branch_data.other_id5}">
						</td>
					</tr>
				{/if}

			</table>
		</div>

		<div id="contentBoxFour">
			<input type="submit" class="btnSubmit" name="action:submit" value="{t}Submit{/t}" onClick="return singleSubmitHandler(this)">
		</div>

		<input type="hidden" name="branch_data[id]" value="{$branch_data.id}">
		</form>
	</div>
</div>
{include file="footer.tpl"}
