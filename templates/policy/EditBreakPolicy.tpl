{include file="header.tpl" body_onload="showType(); showAutoDetectType();"}

<script	language=JavaScript>
{literal}
function showType() {
	type_id = document.getElementById('type_id').value;

	document.getElementById('type_10_desc').style.display = 'none';
	document.getElementById('type_20_desc').style.display = 'none';
	document.getElementById('include_break_punch_time').style.display = 'none';

	if ( type_id == 10 || type_id == 15 ) {
		document.getElementById('type_10_desc').className = '';
		document.getElementById('type_10_desc').style.display = '';

		document.getElementById('include_break_punch_time').className = '';
		document.getElementById('include_break_punch_time').style.display = '';
	} else {
		document.getElementById('type_20_desc').className = '';
		document.getElementById('type_20_desc').style.display = '';
	}
}

function showAutoDetectType() {
	auto_detect_type_id = document.getElementById('auto_detect_type_id').value;

	//document.getElementById('trigger_time').style.display = 'none';
	document.getElementById('auto_detect_type-10').style.display = 'none';
	document.getElementById('auto_detect_type-20').style.display = 'none';

	if ( auto_detect_type_id == 10 ) {
		document.getElementById('auto_detect_type-10').className = '';
		document.getElementById('auto_detect_type-10').style.display = '';

	} else {
		document.getElementById('auto_detect_type-20').className = '';
		document.getElementById('auto_detect_type-20').style.display = '';
	}
}

{/literal}
</script>

<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">

		<form method="post" name="wage" action="{$smarty.server.SCRIPT_NAME}">
		    <div id="contentBoxTwoEdit">
				{if !$bpf->Validator->isValid()}
					{include file="form_errors.tpl" object="bpf"}
				{/if}

				<table class="editTable">

				<tr onClick="showHelpEntry('name')">
					<td class="{isvalid object="bpf" label="name" value="cellLeftEditTable"}">
						{t}Name:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="data[name]" value="{$data.name}">
					</td>
				</tr>

				<tr onClick="showHelpEntry('type')">
					<td class="{isvalid object="bpf" label="type" value="cellLeftEditTable"}">
						{t}Type:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="type_id" name="data[type_id]" onChange="showType()">
							{html_options options=$data.type_options selected=$data.type_id}
						</select>
					</td>
				</tr>

				<tr onClick="showHelpEntry('trigger_time')">
					<td class="{isvalid object="bpf" label="trigger_time" value="cellLeftEditTable"}">
						{t}Active After:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="8" name="data[trigger_time]" value="{gettimeunit value=$data.trigger_time}"> {$current_user_prefs->getTimeUnitFormatExample()}
					</td>
				</tr>

				<tr onClick="showHelpEntry('amount')">
					<td class="{isvalid object="bpf" label="amount" value="cellLeftEditTable"}">
						<span id="type_10_desc" {if $data.type_id != 10}style="display:none"{/if}>{t}Deduction/Addition Time{/t}</span><span id="type_20_desc" {if $data.type_id != 20}style="display:none"{/if}>{t}Break Time{/t}</span>:
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="8" name="data[amount]" value="{gettimeunit value=$data.amount}"> {$current_user_prefs->getTimeUnitFormatExample()}
					</td>
				</tr>

				<tr onClick="showHelpEntry('auto_detect_type')">
					<td class="{isvalid object="bpf" label="type" value="cellLeftEditTable"}">
						{t}Auto-Detect Breaks By:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="auto_detect_type_id" name="data[auto_detect_type_id]" onChange="showAutoDetectType()">
							{html_options options=$data.auto_detect_type_options selected=$data.auto_detect_type_id}
						</select>
					</td>
				</tr>

				<tbody id="auto_detect_type-10" style="display:none">
				<tr onClick="showHelpEntry('start_window')">
					<td class="{isvalid object="bpf" label="start_window" value="cellLeftEditTable"}">
						{t}Start Window:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="8" name="data[start_window]" value="{gettimeunit value=$data.start_window|default:0}"> {$current_user_prefs->getTimeUnitFormatExample()}
					</td>
				</tr>

				<tr onClick="showHelpEntry('window_length')">
					<td class="{isvalid object="bpf" label="window_length" value="cellLeftEditTable"}">
						{t}Window Length:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="8" name="data[window_length]" value="{gettimeunit value=$data.window_length|default:0}"> {$current_user_prefs->getTimeUnitFormatExample()}
					</td>
				</tr>
				</tbody>

				<tbody id="auto_detect_type-20" style="display:none">
				<tr onClick="showHelpEntry('minimum_punch_time')">
					<td class="{isvalid object="bpf" label="minimum_punch_time" value="cellLeftEditTable"}">
						{t}Minimum Punch Time:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="8" name="data[minimum_punch_time]" value="{gettimeunit value=$data.minimum_punch_time|default:0}"> {$current_user_prefs->getTimeUnitFormatExample()}
					</td>
				</tr>

				<tr onClick="showHelpEntry('maximum_punch_time')">
					<td class="{isvalid object="bpf" label="maximum_punch_time" value="cellLeftEditTable"}">
						{t}Maximum Punch Time:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="8" name="data[maximum_punch_time]" value="{gettimeunit value=$data.maximum_punch_time|default:0}"> {$current_user_prefs->getTimeUnitFormatExample()}
					</td>
				</tr>
				</tbody>

				<tbody id="include_break_punch_time" style="display:none">
				<tr onClick="showHelpEntry('include_break_punch_time')">
					<td class="{isvalid object="bpf" label="window_length" value="cellLeftEditTable"}">
						{t}Include Any Punched Time for Break:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="checkbox" class="checkbox" name="data[include_break_punch_time]" value="1" {if $data.include_break_punch_time == TRUE}checked{/if}>
					</td>
				</tr>
				<tr onClick="showHelpEntry('include_multiple_breaks')">
					<td class="{isvalid object="bpf" label="window_length" value="cellLeftEditTable"}">
						{t}Include Multiple Breaks:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="checkbox" class="checkbox" name="data[include_multiple_breaks]" value="1" {if $data.include_multiple_breaks == TRUE}checked{/if}>
					</td>
				</tr>
				</tbody>

			</table>
		</div>

		<div id="contentBoxFour">
			<input type="submit" class="btnSubmit" name="action:submit" value="{t}Submit{/t}" onClick="return singleSubmitHandler(this)">
		</div>

		<input type="hidden" name="data[id]" value="{$data.id}">
		</form>
	</div>
</div>
{include file="footer.tpl"}
