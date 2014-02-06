{include file="header.tpl" body_onload="formChangeDetect(); showAutoUpdate(); "}

<script	language=JavaScript>
{literal}
function setName() {
	if ( document.getElementById('currency_id').value == '' ) {
		document.getElementById('name').value = document.getElementById('iso_code').value;
	}
}
function showAutoUpdate() {
	if ( document.getElementById('auto_update').checked == true ) {
		document.getElementById('type_id-10').className = '';
		document.getElementById('type_id-10').style.display = '';
	} else {
		document.getElementById('type_id-10').style.display = 'none';
	}
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

				{include file="data_saved.tpl" result=$data_saved}

				<tr onClick="showHelpEntry('status')">
					<td class="{isvalid object="cf" label="status" value="cellLeftEditTable"}">
						{t}Status:{/t}
					</td>
					<td class="cellRightEditTable">
						<select name="data[status]">
							{html_options options=$data.status_options selected=$data.status}
						</select>
					</td>
				</tr>

				<tr onClick="showHelpEntry('iso_code')">
					<td class="{isvalid object="cf" label="iso_code" value="cellLeftEditTable"}">
						{t}ISO Currency:{/t}
					</td>
					<td class="cellRightEditTable">
						<select name="data[iso_code]" id="iso_code" onChange="setName();">
							{html_options options=$data.iso_code_options selected=$data.iso_code}
						</select>
					</td>
				</tr>

				<tr onClick="showHelpEntry('name')">
					<td class="{isvalid object="cf" label="name" value="cellLeftEditTable"}">
						{t}Name:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" id="name" name="data[name]" value="{$data.name}">
					</td>
				</tr>

				<tr onClick="showHelpEntry('is_base')">
					<td class="{isvalid object="cf" label="is_base" value="cellLeftEditTable"}">
						{t}Base Currency:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="checkbox" class="checkbox" name="data[is_base]" value="1" {if $data.is_base == TRUE}checked{/if}>
						{t}(base all other conversion rates off this currency){/t}
					</td>
				</tr>

				<tr onClick="showHelpEntry('conversion_rate')">
					<td class="{isvalid object="cf" label="conversion_rate" value="cellLeftEditTable"}">
						{t}Conversion Rate:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="15" name="data[conversion_rate]" value="{$data.conversion_rate}">
					</td>
				</tr>

				<tr onClick="showHelpEntry('is_default')">
					<td class="{isvalid object="cf" label="is_default" value="cellLeftEditTable"}">
						{t}Default Currency:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="checkbox" class="checkbox" name="data[is_default]" value="1" {if $data.is_default == TRUE}checked{/if}>
					</td>
				</tr>

				<tr onClick="showHelpEntry('auto_update')">
					<td class="{isvalid object="cf" label="auto_update" value="cellLeftEditTable"}">
						{t}Auto Update:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="checkbox" id="auto_update" class="checkbox" name="data[auto_update]" value="1" onChange="showAutoUpdate();"{if $data.auto_update == TRUE}checked{/if}>
						{t}(download rate from real-time data feed){/t}
					</td>
				</tr>

				<tbody id="type_id-10" style="display:none">

				<tr onClick="showHelpEntry('rate_modify_percent')">
					<td class="{isvalid object="cf" label="rate_modify_percent" value="cellLeftEditTable"}">
						{t}Rate Modify Percent:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="15" name="data[rate_modify_percent]" value="{$data.rate_modify_percent}">%
					</td>
				</tr>

				<tr onClick="showHelpEntry('actual_rate')">
					<td class="cellLeftEditTable">
						{t}Actual Rate:{/t}
					</td>
					<td class="cellRightEditTable">
						{$data.actual_rate|default:"N/A"}
					</td>
				</tr>

				<tr onClick="showHelpEntry('actual_rate')">
					<td class="cellLeftEditTable">
						{t}Last Downloaded Date:{/t}
					</td>
					<td class="cellRightEditTable">
						{getdate type="DATE+TIME" epoch=$data.actual_rate_updated_date default=TRUE}
					</td>
				</tr>


				</tbody>


			</table>
		</div>

		<div id="contentBoxFour">
			<input type="submit" class="btnSubmit" name="action:submit" value="{t}Submit{/t}" onClick="return singleSubmitHandler(this)">
		</div>

		<input type="hidden" id="currency_id" name="data[id]" value="{$data.id}">
		</form>
	</div>
</div>
{include file="footer.tpl"}
