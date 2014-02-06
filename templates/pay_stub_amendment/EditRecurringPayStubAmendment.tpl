{include file="header.tpl" enable_calendar=true body_onload="showPercent();calcAmount();filterUserCount();"}

<script	language=JavaScript>
{literal}
function filterUserCount() {
	total = countSelect(document.getElementById('filter_user'));
	writeLayer('filter_user_count', total);
}

function showPercent() {
	if ( document.getElementById('type_id').value == 20 ) {
		document.getElementById('type_id-10').style.display = 'none';

		document.getElementById('type_id-20').className = '';
		document.getElementById('type_id-20').style.display = '';
	} else {
		document.getElementById('type_id-20').style.display = 'none';

		document.getElementById('type_id-10').className = '';
		document.getElementById('type_id-10').style.display = '';
	}
}

/*
function calcAmount() {
	if ( document.getElementById('rate').value != '' || document.getElementById('units').value != '' ) {
		document.getElementById('amount').disabled = true;
	} else {
		document.getElementById('amount').disabled = false;
	}

	if ( document.getElementById('rate').value != '' && document.getElementById('units').value != '' ) {
		amount = document.getElementById('rate').value * document.getElementById('units').value;
		document.getElementById('amount').value = amount.toFixed(2);
	}
}
*/

function calcAmount() {
	//Round rate and units to 2 decimals
	rate = MoneyFormat( document.getElementById('rate').value );
	units = MoneyFormat( document.getElementById('units').value );

	if ( ( document.getElementById('rate').value != '' && rate > 0 )
			|| ( document.getElementById('units').value != '' && units > 0 ) ) {
		document.getElementById('amount').disabled = true;

		amount = rate * units;
		document.getElementById('amount').value = amount.toFixed(2);
	} else {
		document.getElementById('amount').disabled = false;
	}
}

function MoneyFormat( val ) {
	if ( val != '' ) {
		return parseFloat( val ).toFixed(2);
	}

	return '';
}

{/literal}
</script>

<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">

		<form method="post" action="{$smarty.server.SCRIPT_NAME}">
		    <div id="contentBoxTwoEdit">

				{if !$rpsaf->Validator->isValid()}
					{include file="form_errors.tpl" object="rpsaf"}
				{/if}

				<table class="editTable">

				<tr onClick="showHelpEntry('status')">
					<td class="{isvalid object="rpsaf" label="status" value="cellLeftEditTable"}">
						{t}Status:{/t}
					</td>
					<td class="cellRightEditTable">
						<select name="pay_stub_amendment_data[status_id]">
							{html_options options=$pay_stub_amendment_data.status_options selected=$pay_stub_amendment_data.status_id}
						</select>
					</td>
				</tr>

				<tr onClick="showHelpEntry('name')">
					<td class="{isvalid object="rpsaf" label="name" value="cellLeftEditTable"}">
						{t}Name:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="20" name="pay_stub_amendment_data[name]" value="{$pay_stub_amendment_data.name|escape}">
					</td>
				</tr>

				<tr onClick="showHelpEntry('description')">
					<td class="{isvalid object="rpsaf" label="description" value="cellLeftEditTable"}">
						{t}Description:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="50" name="pay_stub_amendment_data[description]" value="{$pay_stub_amendment_data.description|escape}">
					</td>
				</tr>

				<tr onClick="showHelpEntry('frequency')">
					<td class="{isvalid object="rpsaf" label="frequency" value="cellLeftEditTable"}">
						{t}Frequency:{/t}
					</td>
					<td class="cellRightEditTable">
						<select name="pay_stub_amendment_data[frequency_id]">
							{html_options options=$pay_stub_amendment_data.frequency_options selected=$pay_stub_amendment_data.frequency_id}
						</select>
					</td>
				</tr>

				<tr onClick="showHelpEntry('start_date')">
					<td class="{isvalid object="rpsaf" label="start_date" value="cellLeftEditTable"}">
						{t}Start Date:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="15" id="start_date" name="pay_stub_amendment_data[start_date]" value="{getdate type="DATE" epoch=$pay_stub_amendment_data.start_date}">
						<img src="{$BASE_URL}/images/cal.gif" id="cal_start_date" width="16" height="16" border="0" alt="Pick a date" onMouseOver="calendar_setup('start_date', 'cal_start_date', false);">
					</td>
				</tr>
				<tr onClick="showHelpEntry('end_date')">
					<td class="{isvalid object="rpsaf" label="end_date" value="cellLeftEditTable"}">
						{t}End Date:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="15" id="end_date" name="pay_stub_amendment_data[end_date]" value="{getdate type="DATE" epoch=$pay_stub_amendment_data.end_date}">
						<img src="{$BASE_URL}/images/cal.gif" id="cal_end_date" width="16" height="16" border="0" alt="Pick a date" onMouseOver="calendar_setup('end_date', 'cal_end_date', false);">
					</td>
				</tr>

				<tbody id="filter_employees_on" style="display:none" >
				<tr>
					<td class="{isvalid object="rpsaf" label="user_ids" value="cellLeftEditTable"}" nowrap>
						<b>{t}Employees:{/t}</b><a href="javascript:toggleRowObject('filter_employees_on');toggleRowObject('filter_employees_off');filterUserCount();"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_top_sm.gif"></a>
					</td>
					<td colspan="3">
						<table class="editTable">
						<tr class="tblHeader">
							<td>
								{t}UnAssigned Employees{/t}
							</td>
							<td>
								<br>
							</td>
							<td>
								{t}Assigned Employees{/t}
							</td>
						</tr>
						<tr>
							<td class="cellRightEditTable" width="49%" align="center">
								<input type="button" name="Select All" value="{t}Select All{/t}" onClick="selectAll(document.getElementById('src_filter_user'))">
								<input type="button" name="Un-Select" value="{t}Un-Select All{/t}" onClick="unselectAll(document.getElementById('src_filter_user'))">
								<br>
								<select name="src_user_id" id="src_filter_user" style="width:100%;margin:5px 0 5px 0;" size="{select_size array=$pay_stub_amendment_data.user_options}" multiple>
									{html_options options=$pay_stub_amendment_data.user_options}
								</select>
							</td>
							<td class="cellRightEditTable" style="vertical-align: middle;" width="1">
								<a href="javascript:moveItem(document.getElementById('src_filter_user'), document.getElementById('filter_user')); uniqueSelect(document.getElementById('filter_user')); sortSelect(document.getElementById('filter_user'));resizeSelect(document.getElementById('src_filter_user'), document.getElementById('filter_user'), {select_size array=$pay_stub_amendment_data.user_options})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_last.gif"></a>
								<br>
								<a href="javascript:moveItem(document.getElementById('filter_user'), document.getElementById('src_filter_user')); uniqueSelect(document.getElementById('src_filter_user')); sortSelect(document.getElementById('src_filter_user'));resizeSelect(document.getElementById('src_filter_user'), document.getElementById('filter_user'), {select_size array=$pay_stub_amendment_data.user_options})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_first.gif"></a>
								<br>
								<br>
								<br>
								<a href="javascript:UserSearch('src_filter_user','filter_user');"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_popup.gif"></a>
							</td>
							<td class="cellRightEditTable" width="49%" align="center">
								<input type="button" name="Select All" value="{t}Select All{/t}" onClick="selectAll(document.getElementById('filter_user'))">
								<input type="button" name="Un-Select" value="{t}Un-Select All{/t}" onClick="unselectAll(document.getElementById('filter_user'))">
								<br>
								<select name="pay_stub_amendment_data[user_ids][]" id="filter_user" style="width:100%;margin:5px 0 5px 0;" size="{select_size array=$filter_user_options}" multiple>
									{html_options options=$filter_user_options selected=$pay_stub_amendment_data.user_ids}
								</select>
							</td>
						</tr>
						</table>
					</td>
				</tr>
				</tbody>
				<tbody id="filter_employees_off">
				<tr>
					<td class="{isvalid object="rpsaf" label="user" value="cellLeftEditTable"}" nowrap>
						<b>{t}Employees:{/t}</b><a href="javascript:toggleRowObject('filter_employees_on');toggleRowObject('filter_employees_off');uniqueSelect(document.getElementById('filter_user'), document.getElementById('src_filter_user')); sortSelect(document.getElementById('filter_user'));resizeSelect(document.getElementById('src_filter_user'), document.getElementById('filter_user'), {select_size array=$pay_stub_amendment_data.user_options})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_bottom_sm.gif"></a>
					</td>
					<td class="cellRightEditTable" colspan="100">
						<span id="filter_user_count">0</span> {t}Employees Currently Selected, Click the arrow to modify.{/t}
					</td>
				</tr>
				</tbody>
				<tr class="tblHeader">
					<td colspan="2" >
						{t}Pay Stub Amendment{/t}
					</td>
				</tr>

				<tr onClick="showHelpEntry('pay_stub_entry_name_id')">
					<td class="{isvalid object="rpsaf" label="pay_stub_entry_name_id" value="cellLeftEditTable"}">
						{t}Pay Stub Account:{/t}
					</td>
					<td class="cellRightEditTable">
						<select name="pay_stub_amendment_data[pay_stub_entry_name_id]">
							{html_options options=$pay_stub_amendment_data.pay_stub_entry_name_options selected=$pay_stub_amendment_data.pay_stub_entry_name_id}
						</select>
					</td>
				</tr>

				<tr onClick="showHelpEntry('type')">
					<td class="{isvalid object="rpsaf" label="type_id" value="cellLeftEditTable"}">
						{t}Amount Type:{/t}
					</td>
					<td class="cellRightEditTable">
						<select name="pay_stub_amendment_data[type_id]" id="type_id"  onChange="showPercent()">
							{html_options options=$pay_stub_amendment_data.type_options selected=$pay_stub_amendment_data.type_id}
						</select>
					</td>
				</tr>

				<tbody id="type_id-10" >

				<tr onClick="showHelpEntry('rate')">
					<td class="{isvalid object="rpsaf" label="rate" value="cellLeftEditTable"}">
						{t}Rate:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="pay_stub_amendment_data[rate]" id="rate" value="{$pay_stub_amendment_data.rate}" onKeyUp="calcAmount()">
					</td>
				</tr>

				<tr onClick="showHelpEntry('units')">
					<td class="{isvalid object="rpsaf" label="units" value="cellLeftEditTable"}">
						{t}Units:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="pay_stub_amendment_data[units]" id="units" value="{$pay_stub_amendment_data.units}" onKeyUp="calcAmount()">
					</td>
				</tr>

				<tr onClick="showHelpEntry('amount')">
					<td class="{isvalid object="rpsaf" label="amount" value="cellLeftEditTable"}">
						{t}Amount:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="pay_stub_amendment_data[amount]" id="amount" value="{$pay_stub_amendment_data.amount}">
					</td>
				</tr>
				</tbody>

				<tbody id="type_id-20" style="display:none" >
				<tr onClick="showHelpEntry('percent_amount')">
					<td class="{isvalid object="rpsaf" label="percent_amount" value="cellLeftEditTable"}">
						{t}Percent:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="10" name="pay_stub_amendment_data[percent_amount]" value="{$pay_stub_amendment_data.percent_amount}">%
					</td>
				</tr>

				<tr onClick="showHelpEntry('percent_amount_entry_name')">
					<td class="{isvalid object="rpsaf" label="percent_amount_entry_name" value="cellLeftEditTable"}">
						{t}Percent Of:{/t}
					</td>
					<td class="cellRightEditTable">
						<select name="pay_stub_amendment_data[percent_amount_entry_name_id]">
							{html_options options=$pay_stub_amendment_data.percent_amount_entry_name_options selected=$pay_stub_amendment_data.percent_amount_entry_name_id}
						</select>
					</td>
				</tr>

				</tbody>

				<tr onClick="showHelpEntry('description')">
					<td class="{isvalid object="rpsaf" label="description" value="cellLeftEditTable"}">
						{t}Description:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="50" name="pay_stub_amendment_data[ps_amendment_description]" value="{$pay_stub_amendment_data.ps_amendment_description}">
					</td>
				</tr>

			</table>
		</div>

		<div id="contentBoxFour">
			<input type="submit" class="button" name="action:submit" value="{t}Submit{/t}" {if $pay_stub_amendment_data.status_id == 55}disabled="true"{/if} onClick="selectAll(document.getElementById('filter_user'))">
			<input type="submit" class="button" name="action:Recalculate" value="{t}Recalculate{/t}">
		</div>

		<input type="hidden" name="pay_stub_amendment_data[id]" value="{$pay_stub_amendment_data.id}">
		</form>
	</div>
</div>
{include file="footer.tpl"}
