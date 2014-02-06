{include file="header.tpl" body_onload="handleEnableBankTime();"}

<script	language=JavaScript>

{literal}
function handleEnableBankTime() {
	if(document.getElementById) {
		if ( document.getElementById('enable_bank_time').checked == true ) {
			document.getElementById('over_time_default').disabled = false;
			document.getElementById('under_time_default').disabled = false;
		} else {
			document.getElementById('over_time_default').disabled = true;
			document.getElementById('under_time_default').disabled = true;

			document.getElementById('over_time_default').value = 10;
			document.getElementById('under_time_default').value = 10;
		}
	}
}
{/literal}
</script>

<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">

		<form method="post" name="round_policy" action="{$smarty.server.SCRIPT_NAME}">
		    <div id="contentBoxTwoEdit">

				{if !$rpf->Validator->isValid()}
					{include file="form_errors.tpl" object="rpf"}
				{/if}

				<table class="editTable">
					<tr onClick="showHelpEntry('name')">
						<td class="{isvalid object="rpf" label="name" value="cellLeftEditTable"}">
							{t}Name:{/t}
						</td>
						<td class="cellRightEditTable" colspan="2">
							<input type="text" name="round_policy_data[name]" value="{$round_policy_data.name}">
						</td>
						<td class="cellRightEditTable" colspan="2">
							<br>
						</td>
					</tr>
					<tr onClick="showHelpEntry('name')">
						<td class="{isvalid object="rpf" label="description" value="cellLeftEditTable"}">
							{t}Description:{/t}
						</td>
						<td class="cellRightEditTable" colspan="2">
							<input type="text" name="round_policy_data[description]" value="{$round_policy_data.description}">
						</td>
						<td class="cellRightEditTable" colspan="2">
							<br>
						</td>
					</tr>

					<tr onClick="showHelpEntry('default_policy')">
						<td class="{isvalid object="rpf" label="default" value="cellLeftEditTable"}">
							{t}Default Policy:{/t}
						</td>
						<td class="cellRightEditTable" colspan="2">
							<input type="checkbox" class="checkbox" name="round_policy_data[default]" value="1" {if $round_policy_data.default == TRUE}checked{/if}>
						</td>
						<td class="cellRightEditTable" colspan="2">
							<br>
						</td>
					</tr>

					<tr onClick="showHelpEntry('enable_bank_time')">
						<td class="{isvalid object="rpf" label="default" value="cellLeftEditTable"}">
							{t}Enable Time Bank:{/t}
						</td>
						<td class="cellRightEditTable" colspan="2">
							<input type="checkbox" id="enable_bank_time" class="checkbox" onClick="handleEnableBankTime();" name="round_policy_data[enable_bank_time]" value="1" {if $round_policy_data.enable_bank_time == TRUE}checked{/if}>
						</td>
						<td class="cellRightEditTable" colspan="2">
							<br>
						</td>
					</tr>

					<tr onClick="showHelpEntry('over_time_default')">
						<td class="{isvalid object="rpf" label="default" value="cellLeftEditTable"}">
							{t}Over Time Shift Default Attribute:{/t}
						</td>
						<td class="cellRightEditTable" colspan="2">
							<select name="round_policy_data[over_time_default]" id="over_time_default" >
								{html_options options=$over_time_default_options selected=$round_policy_data.over_time_default}
							</select>
						</td>
						<td class="cellRightEditTable" colspan="2">
							<br>
						</td>
					</tr>

					<tr onClick="showHelpEntry('under_time_default')">
						<td class="{isvalid object="rpf" label="default" value="cellLeftEditTable"}">
							{t}Under Time Shift Default Attribute:{/t}
						</td>
						<td class="cellRightEditTable" colspan="2">
							<select name="round_policy_data[under_time_default]" id="under_time_default">
								{html_options options=$under_time_default_options selected=$round_policy_data.under_time_default}
							</select>
						</td>
						<td class="cellRightEditTable" colspan="2">
							<br>
						</td>
					</tr>

					<tr class="tblHeader">
						<td width="200">
							<br>
						</td>
						<td width="150">
							{t}Start{/t}
						</td>
						<td width="150">
							{t}Lunch Start{/t}
						</td>
						<td width="150">
							{t}Lunch End{/t}
						</td>
						<td width="150">
							{t}End{/t}
						</td>
					</tr>
					{cycle assign=row_class values="tblDataWhiteNH,tblDataGreyNH"}
					<tr class="{$row_class}">
						<td class="cellLeftEditTable">
							{t}Total:{/t}
						</td>
						<td>
							<input type="checkbox" class="checkbox" name="" disabled="true">
						</td>
						<td>
							<input type="checkbox" class="checkbox" name="" disabled="true">
						</td>
						<td onClick="showHelpEntry('round_lunch_total')">
							<input type="checkbox" class="checkbox" name="round_policy_data[round_lunch_total]" value="1"{if $round_policy_data.round_lunch_total == TRUE}checked{/if}>
						</td>
						<td onClick="showHelpEntry('round_total')">
							<input type="checkbox" class="checkbox" name="round_policy_data[round_total]" value="1"{if $round_policy_data.round_total == TRUE}checked{/if}>
						</td>
					</tr>
					<tr class="{$row_class}" onClick="showHelpEntry('strict')">
						<td class="cellLeftEditTable">
							{t}Strict:{/t}
						</td>
						<td>
							<input type="checkbox" class="checkbox" name="round_policy_data[strict_start]" value="1"{if $round_policy_data.strict_start == TRUE}checked{/if}>
						</td>
						<td>
							<input type="checkbox" class="checkbox" name="round_policy_data[strict_lunch_start]" value="1"{if $round_policy_data.strict_lunch_start == TRUE}checked{/if}>
						</td>
						<td>
							<input type="checkbox" class="checkbox" name="round_policy_data[strict_lunch_end]" value="1"{if $round_policy_data.strict_lunch_end == TRUE}checked{/if}>
						</td>
						<td>
							<input type="checkbox" class="checkbox" name="round_policy_data[strict_end]" value="1"{if $round_policy_data.strict_end == TRUE}checked{/if}>
						</td>
					</tr>
					<tr class="{$row_class}" onClick="showHelpEntry('grace_period')">
						<td  class="cellLeftEditTable" nowrap>
							{t}Grace Period:{/t}
						</td>
						<td id="{isvalid object="rpf" label="round_grace_start" value="value"}">
							<input type="text" size="5" name="round_policy_data[round_grace_start]" value="{gettimeunit value=$round_policy_data.round_grace_start}">
						</td>
						<td id="{isvalid object="rpf" label="round_grace_lunch_start" value="value"}">
							<input type="text" size="5" name="round_policy_data[round_grace_lunch_start]" value="{gettimeunit value=$round_policy_data.round_grace_lunch_start}">
						</td>
						<td id="{isvalid object="rpf" label="round_grace_lunch_end" value="value"}">
							<input type="text" size="5" name="round_policy_data[round_grace_lunch_end]" value="{gettimeunit value=$round_policy_data.round_grace_lunch_end}">
						</td>
						<td id="{isvalid object="rpf" label="round_grace_end" value="value"}">
							<input type="text" size="5" name="round_policy_data[round_grace_end]" value="{gettimeunit value=$round_policy_data.round_grace_end}">
						</td>
					</tr>

					<tr class="{$row_class}" onClick="showHelpEntry('round_value')">
						<td class="cellLeftEditTable" nowrap>
							{t}Round Value:{/t}
						</td>
						<td id="{isvalid object="rpf" label="round_start" value="value"}">
							<input type="text" size="5" name="round_policy_data[round_start]" value="{gettimeunit value=$round_policy_data.round_start}">
						</td>
						<td id="{isvalid object="rpf" label="round_lunch_start" value="value"}">
							<input type="text" size="5" name="round_policy_data[round_lunch_start]" value="{gettimeunit value=$round_policy_data.round_lunch_start}">
						</td>
						<td id="{isvalid object="rpf" label="round_lunch_end" value="value"}">
							<input type="text" size="5" name="round_policy_data[round_lunch_end]" value="{gettimeunit value=$round_policy_data.round_lunch_end}">
						</td>
						<td id="{isvalid object="rpf" label="round_end" value="value"}">
							<input type="text" size="5" name="round_policy_data[round_end]" value="{gettimeunit value=$round_policy_data.round_end}">
						</td>
					</tr>

					<tr class="{$row_class}" onClick="showHelpEntry('round_type')">
						<td class="cellLeftEditTable" nowrap>
							{t}Round Type:{/t}
						</td>
						<td>
							<select name="round_policy_data[round_type_start]">
								{html_options options=$round_type_options selected=$round_policy_data.round_type_start}
							</select>
						</td>
						<td>
							<select name="round_policy_data[round_type_lunch_start]">
								{html_options options=$round_type_options selected=$round_policy_data.round_type_lunch_start}
							</select>

						</td>
						<td>
							<select name="round_policy_data[round_type_lunch_end]">
								{html_options options=$round_type_options selected=$round_policy_data.round_type_lunch_end}
							</select>
						</td>
						<td>
							<select name="round_policy_data[round_type_end]">
								{html_options options=$round_type_options selected=$round_policy_data.round_type_end}
							</select>
						</td>
					</tr>

			</table>
		</div>

		<div id="contentBoxFour">
			<input type="submit" class="btnSubmit" name="action" value="Submit">
		</div>

		<input type="hidden" name="round_policy_data[id]" value="{$round_policy_data.id}">
		</form>
	</div>
</div>
{include file="footer.tpl"}
