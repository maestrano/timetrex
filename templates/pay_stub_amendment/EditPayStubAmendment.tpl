{include file="header.tpl" enable_calendar=true enable_ajax=TRUE body_onload="showPercent(); calcAmount();"}

<script	language=JavaScript>

{literal}
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

function calcAmount() {
	//Round rate and units to 2 decimals
	rate = document.getElementById('rate').value;
	units = document.getElementById('units').value;

	if ( ( document.getElementById('rate').value != '' && rate > 0 )
			|| ( document.getElementById('units').value != '' && units > 0 ) ) {
		document.getElementById('amount').disabled = true;

		amount = rate * units;
		document.getElementById('amount').value = MoneyFormat( amount );
	} else {
		document.getElementById('amount').disabled = false;
	}
}

var hwCallback = {
		getUserHourlyRate: function(result) {
			document.getElementById('rate').value = result;
			calcAmount();
		}
	}

var remoteHW = new AJAX_Server(hwCallback);

function getHourlyRate() {
	if ( document.getElementById('filter_user').options.length == 1 ) {
		user_id = document.getElementById('filter_user').options[0].value
		remoteHW.getUserHourlyRate( user_id, document.getElementById('effective_date').value);
	} else if ( document.getElementById('filter_user').options.length > 1) {
		document.getElementById('rate').value = '';
		alert('{/literal}{t}Unable to obtain rate when multiple employees are selected.{/t}{literal}');
	} else {
		document.getElementById('rate').value = '';
		alert('{/literal}{t}Unable to obtain rate when no employee is selected.{/t}{literal}');
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

				{if !$psaf->Validator->isValid()}
					{include file="form_errors.tpl" object="psaf"}
				{/if}

				<table class="editTable">

				<tr>
					<td class="{isvalid object="pcf" label="user_id" value="cellLeftEditTable"}">
						{t}Employee(s):{/t}
					</td>
					<td>
						<table class="editTable">
							<tr class="tblHeader">
								<td>
									{t}UnSelected Employees{/t}
								</td>
								<td>
									<br>
								</td>
								<td>
									{t}Selected Employees{/t}
								</td>
							</tr>
							<tr>
								<td class="cellRightEditTable" width="50%" align="center">
									<input type="button" name="Select All" value="{t}Select All{/t}" onClick="selectAll(document.getElementById('src_filter_user'))">
									<input type="button" name="Un-Select" value="{t}Un-Select All{/t}" onClick="unselectAll(document.getElementById('src_filter_user'))">
									<br>
									<select name="pay_stub_amendment_data[src_user_id]" id="src_filter_user" style="width:90%;margin:5px 0 5px 0;" size="{select_size array=$pay_stub_amendment_data.user_options}" multiple>
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
								<td class="cellRightEditTable" width="50%"  align="center">
									<input type="button" name="Select All" value="{t}Select All{/t}" onClick="selectAll(document.getElementById('filter_user'))">
									<input type="button" name="Un-Select" value="{t}Un-Select All{/t}" onClick="unselectAll(document.getElementById('filter_user'))">
									<br>
									<select name="pay_stub_amendment_data[filter_user_id][]" id="filter_user" style="width:90%;margin:5px 0 5px 0;" size="{select_size array=$pay_stub_amendment_data.user_options}" multiple>
										{html_options options=$pay_stub_amendment_data.filter_user_options selected=$filter_user_id}
									</select>
								</td>
							</tr>
						</table>
					</td>
				</tr>
{*
				<tr>
					<td class="{isvalid object="psaf" label="user" value="cellLeftEditTable"}">
						{t}Employee:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="user_id" name="pay_stub_amendment_data[user_id]">
							{html_options options=$pay_stub_amendment_data.user_options selected=$pay_stub_amendment_data.user_id}
						</select>
					</td>
				</tr>
*}
				<tr onClick="showHelpEntry('status')">
					<td class="{isvalid object="psaf" label="status" value="cellLeftEditTable"}">
						{t}Status:{/t}
					</td>
					<td class="cellRightEditTable">
						<select name="pay_stub_amendment_data[status_id]">
							{html_options options=$pay_stub_amendment_data.status_options selected=$pay_stub_amendment_data.status_id}
						</select>
					</td>
				</tr>

				<tr onClick="showHelpEntry('pay_stub_entry_name_id')">
					<td class="{isvalid object="psaf" label="pay_stub_entry_name_id" value="cellLeftEditTable"}">
						{t}Pay Stub Account:{/t}
					</td>
					<td class="cellRightEditTable">
						<select name="pay_stub_amendment_data[pay_stub_entry_name_id]">
							{html_options options=$pay_stub_amendment_data.pay_stub_entry_name_options selected=$pay_stub_amendment_data.pay_stub_entry_name_id}
						</select>
					</td>
				</tr>

				<tr class="tblHeader">
					<td colspan="2">
						{t}Amount{/t}
					</td>
				</tr>

				<tr onClick="showHelpEntry('type')">
					<td class="{isvalid object="psaf" label="type_id" value="cellLeftEditTable"}">
						{t}Amount Type:{/t}
					</td>
					<td class="cellRightEditTable">
						<select name="pay_stub_amendment_data[type_id]" id="type_id" onChange="showPercent()">
							{html_options options=$pay_stub_amendment_data.type_options selected=$pay_stub_amendment_data.type_id}
						</select>
					</td>
				</tr>

				<tbody id="type_id-10" >

				<tr onClick="showHelpEntry('rate')">
					<td class="{isvalid object="psaf" label="rate" value="cellLeftEditTable"}">
						{t}Rate:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="15" name="pay_stub_amendment_data[rate]" id="rate" value="{$pay_stub_amendment_data.rate}" onKeyUp="calcAmount()">
						<input type="button" name="getUserHourlyRate" value="{t}Get Employee Rate{/t}" onclick="getHourlyRate(); return false;"/>
					</td>
				</tr>

				<tr onClick="showHelpEntry('units')">
					<td class="{isvalid object="psaf" label="units" value="cellLeftEditTable"}">
						{t}Units:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="15" name="pay_stub_amendment_data[units]" id="units" value="{$pay_stub_amendment_data.units}" onKeyUp="calcAmount()">
					</td>
				</tr>

				<tr onClick="showHelpEntry('amount')">
					<td class="{isvalid object="psaf" label="amount" value="cellLeftEditTable"}">
						{t}Amount:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="15" name="pay_stub_amendment_data[amount]" id="amount" value="{$pay_stub_amendment_data.amount}">
					</td>
				</tr>
				</tbody>

				<tbody id="type_id-20" style="display:none" >
				<tr onClick="showHelpEntry('percent_amount')">
					<td class="{isvalid object="psaf" label="percent_amount" value="cellLeftEditTable"}">
						{t}Percent:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="10" name="pay_stub_amendment_data[percent_amount]" value="{$pay_stub_amendment_data.percent_amount}">%
					</td>
				</tr>

				<tr onClick="showHelpEntry('percent_amount_entry_name')">
					<td class="{isvalid object="psaf" label="percent_amount_entry_name" value="cellLeftEditTable"}">
						{t}Percent Of:{/t}
					</td>
					<td class="cellRightEditTable">
						<select name="pay_stub_amendment_data[percent_amount_entry_name_id]">
							{html_options options=$pay_stub_amendment_data.percent_amount_entry_name_options selected=$pay_stub_amendment_data.percent_amount_entry_name_id}
						</select>
					</td>
				</tr>

				</tbody>

				<tr class="tblHeader">
					<td colspan="2">
						{t}Options{/t}
					</td>
				</tr>

				<tr onClick="showHelpEntry('description')">
					<td class="{isvalid object="psaf" label="description" value="cellLeftEditTable"}">
						{t}Pay Stub Note (Public):{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="50" name="pay_stub_amendment_data[description]" value="{$pay_stub_amendment_data.description|escape}">
					</td>
				</tr>

				<tr onClick="showHelpEntry('private_description')">
					<td class="{isvalid object="psaf" label="description" value="cellLeftEditTable"}">
						{t}Description (Private):{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="60" name="pay_stub_amendment_data[private_description]" value="{$pay_stub_amendment_data.private_description|escape}">
					</td>
				</tr>

				<tr onClick="showHelpEntry('effective_date')">
					<td class="{isvalid object="psaf" label="effective_date" value="cellLeftEditTable"}">
						{t}Effective Date:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="15" id="effective_date" name="pay_stub_amendment_data[effective_date]" value="{getdate type="DATE" epoch=$pay_stub_amendment_data.effective_date}">
						<img src="{$BASE_URL}/images/cal.gif" id="cal_effective_date" width="16" height="16" border="0" alt="Pick a date" onMouseOver="calendar_setup('effective_date', 'cal_effective_date', false);">
					</td>
				</tr>

				<tr onClick="showHelpEntry('ytd_adjustment')">
					<td class="{isvalid object="psaf" label="ytd_adjustment" value="cellLeftEditTable"}">
						{t}Year to Date (YTD) Adjustment:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="checkbox" class="checkbox" name="pay_stub_amendment_data[ytd_adjustment]" value="1"{if $pay_stub_amendment_data.ytd_adjustment == TRUE}checked{/if}>
					</td>
				</tr>

			</table>
		</div>

		<div id="contentBoxFour">
			<input type="submit" class="button" name="action:submit" value="{t}Submit{/t}" onClick="selectAll(document.getElementById('filter_user'))" {if $pay_stub_amendment_data.status_id == 55}disabled="true"{/if}>
		</div>

		<input type="hidden" name="pay_stub_amendment_data[id]" value="{$pay_stub_amendment_data.id}">
		{* <input type="hidden" name="user_id" value="{$user_data->getId()}"> *}
		</form>
	</div>
</div>
{include file="footer.tpl"}
