{include file="header.tpl" enable_calendar=true body_onload="editPayStubAlert()"}
<SCRIPT language=JavaScript>
{literal}
function editPayStubAlert() {
	var alertMsg= "{/literal}{t}WARNING: Manually editing pay stubs can result in incorrect amounts being calculated for things such as taxes and vacation accrual. Please use Pay Stub Amendments instead. DO NOT EDIT PAY STUBS MANUALLY UNLESS ABSOLUTELY NECCESARY!{/t}{literal}";
	alert(alertMsg);
}

function setModifiedEntry() {
	document.getElementById('modified_entry').value = 1;
}

{/literal}
</SCRIPT>
<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">
		<br>
		<form method="post" action="{$smarty.server.SCRIPT_NAME}">

		{if !$psf->Validator->isValid() }
			{include file="form_errors.tpl" object="psf"}
		{/if}

		<table class="editTable">

			<tr onClick="showHelpEntry('status')">
				<td class="cellLeftEditTable">
					{t}Employee:{/t}
				</td>
				<td class="cellRightEditTable">
					{$data.user_full_name}
				</td>
			</tr>

			<tr onClick="showHelpEntry('status')">
				<td class="cellLeftEditTable">
					{t}Status:{/t}
				</td>
				<td class="cellRightEditTable">
					<select name="data[status_id]">
						{html_options options=$data.pay_stub_status_options selected=$data.status_id}
					</select>
				</td>
			</tr>

			<tr onClick="showHelpEntry('currency')">
				<td class="{isvalid object="psf" label="currency" value="cellLeftEditTable"}">
					{t}Currency:{/t}
				</td>
				<td class="cellRightEditTable">
					<select name="data[currency_id]">
						{html_options options=$data.currency_options selected=$data.currency_id}
					</select>
				</td>
			</tr>

			<tr onClick="showHelpEntry('start_date')">
				<td class="{isvalid object="psf" label="start_date" value="cellLeftEditTable"}">
					{t}Pay Start Date:{/t}
				</td>
				<td class="cellRightEditTable">
					<input type="text" size="15" id="start_date" name="data[start_date]" value="{getdate type="DATE" epoch=$data.start_date}">
					<img src="{$BASE_URL}/images/cal.gif" id="cal_start_date" width="16" height="16" border="0" alt="Pick a date" onMouseOver="calendar_setup('start_date', 'cal_start_date', false);">
				</td>
			</tr>

			<tr onClick="showHelpEntry('end_date')">
				<td class="{isvalid object="psf" label="end_date" value="cellLeftEditTable"}">
					{t}Pay End Date:{/t}
				</td>
				<td class="cellRightEditTable">
					<input type="text" size="15" id="end_date" name="data[end_date]" value="{getdate type="DATE" epoch=$data.end_date}">
					<img src="{$BASE_URL}/images/cal.gif" id="cal_end_date" width="16" height="16" border="0" alt="Pick a date" onMouseOver="calendar_setup('end_date', 'cal_end_date', false);">
				</td>
			</tr>

			<tr onClick="showHelpEntry('transaction_date')">
				<td class="{isvalid object="psf" label="transaction_date" value="cellLeftEditTable"}">
					{t}Payment Date:{/t}
				</td>
				<td class="cellRightEditTable">
					<input type="text" size="15" id="transaction_date" name="data[transaction_date]" value="{getdate type="DATE" epoch=$data.transaction_date}">
					<img src="{$BASE_URL}/images/cal.gif" id="cal_transaction_date" width="16" height="16" border="0" alt="Pick a date" onMouseOver="calendar_setup('transaction_date', 'cal_transaction_date', false);">
				</td>
			</tr>

		</table>
		<br>
		<table class="tblList">
			{* Earnings *}
			{cycle assign=row_class values="tblDataWhite,tblDataGrey"}
			{foreach name=earnings from=$data.entries.10 item=pay_stub_entry}
				{assign var="pay_stub_amendment_id" value=$pay_stub_entry.pay_stub_amendment_id}

				{if $smarty.foreach.earnings.first}
					<tr class="tblHeader">
						<td colspan="2" align="left">
							{t}Earnings{/t}
						</td>
						<td>
							{t}Rate{/t}
						</td>
						<td>
							{t}Hrs/Units{/t}
						</td>
						<td>
							{t}Amount{/t}
						</td>
						<td>
							{t}YTD Amount{/t}
						</td>
					</tr>
				{/if}
				<tr class="{$row_class}" align="right">
					<td  colspan="2" align="left">
						{if $pay_stub_entry.type != 40}&nbsp;&nbsp;{/if}

						{if $pay_stub_entry.type == 40}<b>{/if}
						{$pay_stub_entry.display_name} {if $pay_stub_entry.description_subscript !== NULL}[{ $pay_stub_entry.description_subscript}]{/if}
						<input type="hidden" name="data[entries][{$pay_stub_entry.tmp_type}][{$pay_stub_entry.id}][display_name]" value="{$pay_stub_entry.display_name}">
						{if $pay_stub_entry.type == 40}</b>{/if}

						{if $pay_stub_entry.pay_stub_amendment_id != ''}
							[ <a href="{urlbuilder script="../pay_stub_amendment/EditPayStubAmendment.php" values="id=$pay_stub_amendment_id" merge="FALSE"}">Edit</a> ]
						{/if}
					</td>
					<td>
						{if $pay_stub_entry.type == 10}
							{if $pay_stub_entry.pay_stub_amendment_id == ''}
								<input type="text" size="5" name="data[entries][{$pay_stub_entry.tmp_type}][{$pay_stub_entry.id}][rate]" value="{$pay_stub_entry.rate}" onChange="setModifiedEntry()">
							{else}
								{$pay_stub_entry.rate}
								<input type="hidden" name="data[entries][{$pay_stub_entry.tmp_type}][{$pay_stub_entry.id}][rate]" value="{$pay_stub_entry.rate}">
							{/if}
						{else}
							<br>
						{/if}
					</td>
					<td>
						{if $pay_stub_entry.type == 10}
							{if $pay_stub_entry.pay_stub_amendment_id == ''}
								<input type="text" size="8" name="data[entries][{$pay_stub_entry.tmp_type}][{$pay_stub_entry.id}][units]" value="{$pay_stub_entry.units}" onChange="setModifiedEntry()">
							{else}
								{$pay_stub_entry.units}
								<input type="hidden" name="data[entries][{$pay_stub_entry.tmp_type}][{$pay_stub_entry.id}][units]" value="{$pay_stub_entry.units}">
							{/if}
						{else}
							<b>{$pay_stub_entry.units}</b>
						{/if}

					</td>
					<td>
						{if $pay_stub_entry.type == 10}
							{if $pay_stub_entry.pay_stub_amendment_id == ''}
								<input type="text" size="10" name="data[entries][{$pay_stub_entry.tmp_type}][{$pay_stub_entry.id}][amount]" value="{$pay_stub_entry.amount}" onChange="setModifiedEntry()">
							{else}
								{$pay_stub_entry.amount}
								<input type="hidden" name="data[entries][{$pay_stub_entry.tmp_type}][{$pay_stub_entry.id}][amount]" value="{$pay_stub_entry.amount}">
							{/if}
						{else}
							<b>{$pay_stub_entry.amount}</b>
							<input type="hidden" name="data[entries][{$pay_stub_entry.tmp_type}][{$pay_stub_entry.id}][amount]" value="{$pay_stub_entry.amount}">
						{/if}
					</td>
					<td>
						{if $pay_stub_entry.type == 40}<b>{/if}
						{$pay_stub_entry.ytd_amount}
						<input type="hidden" name="data[entries][{$pay_stub_entry.tmp_type}][{$pay_stub_entry.id}][ytd_amount]" value="{$pay_stub_entry.ytd_amount}">
						<input type="hidden" name="data[entries][{$pay_stub_entry.tmp_type}][{$pay_stub_entry.id}][id]" value="{$pay_stub_entry.id}">
						<input type="hidden" name="data[entries][{$pay_stub_entry.tmp_type}][{$pay_stub_entry.id}][type]" value="{$pay_stub_entry.type}">
						<input type="hidden" name="data[entries][{$pay_stub_entry.tmp_type}][{$pay_stub_entry.id}][tmp_type]" value="{$pay_stub_entry.tmp_type}">
						<input type="hidden" name="data[entries][{$pay_stub_entry.tmp_type}][{$pay_stub_entry.id}][pay_stub_amendment_id]" value="{$pay_stub_entry.pay_stub_amendment_id}">
						<input type="hidden" name="data[entries][{$pay_stub_entry.tmp_type}][{$pay_stub_entry.id}][description]" value="{$pay_stub_entry.description|escape}">
						{if $pay_stub_entry.type == 40}</b>{/if}
					</td>
				</tr>
				{if $smarty.foreach.earnings.last}
					<tr>
						<td colspan="6">
							<br>
						</td>
					</tr>
				{/if}
			{/foreach}

			{* Deductions *}
			{cycle assign=row_class values="tblDataWhite,tblDataGrey"}
			{foreach name=deductions from=$data.entries.20 item=pay_stub_entry}
				{assign var="pay_stub_amendment_id" value=$pay_stub_entry.pay_stub_amendment_id}

				{if $smarty.foreach.deductions.first}
					<tr class="tblHeader" align="right">
						<td colspan="4" align="left">
							<b>{t}Deductions{/t}</b>
						</td>
						<td>
							<b>{t}Amount{/t}</b>
						</td>
						<td>
							<b>{t}YTD Amount{/t}</b>
						</td>
					</tr>
				{/if}

				<tr class="{$row_class}" align="right">
					<td colspan="4" align="left">
						{if $pay_stub_entry.type != 40}&nbsp;&nbsp;{/if}
						{if $pay_stub_entry.type == 40}<b>{/if}
						{$pay_stub_entry.display_name} {if $pay_stub_entry.description_subscript !== NULL}[{ $pay_stub_entry.description_subscript}]{/if}
						<input type="hidden" name="data[entries][{$pay_stub_entry.tmp_type}][{$pay_stub_entry.id}][display_name]" value="{$pay_stub_entry.display_name}">
						{if $pay_stub_entry.type == 40}<b>{/if}
						{if $pay_stub_entry.pay_stub_amendment_id != ''}
							[ <a href="{urlbuilder script="../pay_stub_amendment/EditPayStubAmendment.php" values="id=$pay_stub_amendment_id" merge="FALSE"}">{t}Edit{/t}</a> ]
						{/if}
					</td>
					<td>
						{if $pay_stub_entry.type == 20}
							{if $pay_stub_entry.pay_stub_amendment_id == ''}
								<input type="text" size="10" name="data[entries][{$pay_stub_entry.tmp_type}][{$pay_stub_entry.id}][amount]" value="{$pay_stub_entry.amount}" onChange="setModifiedEntry()">
							{else}
								{$pay_stub_entry.amount}
								<input type="hidden" name="data[entries][{$pay_stub_entry.tmp_type}][{$pay_stub_entry.id}][amount]" value="{$pay_stub_entry.amount}">
							{/if}
						{else}
							<b>{$pay_stub_entry.amount}</b>
							<input type="hidden" name="data[entries][{$pay_stub_entry.tmp_type}][{$pay_stub_entry.id}][amount]" value="{$pay_stub_entry.amount}">
						{/if}
						<input type="hidden" name="data[entries][{$pay_stub_entry.tmp_type}][{$pay_stub_entry.id}][type]" value="{$pay_stub_entry.type}">
					</td>
					<td>
						{if $pay_stub_entry.type == 40}<b>{/if}
						{$pay_stub_entry.ytd_amount}
						<input type="hidden" name="data[entries][{$pay_stub_entry.tmp_type}][{$pay_stub_entry.id}][ytd_amount]" value="{$pay_stub_entry.ytd_amount}">
						<input type="hidden" name="data[entries][{$pay_stub_entry.tmp_type}][{$pay_stub_entry.id}][id]" value="{$pay_stub_entry.id}">
						<input type="hidden" name="data[entries][{$pay_stub_entry.tmp_type}][{$pay_stub_entry.id}][type]" value="{$pay_stub_entry.type}">
						<input type="hidden" name="data[entries][{$pay_stub_entry.tmp_type}][{$pay_stub_entry.id}][tmp_type]" value="{$pay_stub_entry.tmp_type}">
						<input type="hidden" name="data[entries][{$pay_stub_entry.tmp_type}][{$pay_stub_entry.id}][pay_stub_amendment_id]" value="{$pay_stub_entry.pay_stub_amendment_id}">
						<input type="hidden" name="data[entries][{$pay_stub_entry.tmp_type}][{$pay_stub_entry.id}][description]" value="{$pay_stub_entry.description|escape}">
						{if $pay_stub_entry.type == 40}<b>{/if}
					</td>
				</tr>
				{if $smarty.foreach.deductions.last}
					<tr>
						<td colspan="6">
							<br>
						</td>
					</tr>
				{/if}
			{/foreach}

			{* Employer Deductions *}
			{if $permission->Check('pay_stub','view')}
			{foreach name=employer_deductions from=$data.entries.30 item=pay_stub_entry}
				{assign var="pay_stub_amendment_id" value=$pay_stub_entry.pay_stub_amendment_id}

				{if $smarty.foreach.employer_deductions.first}
					<tr class="tblHeader" align="right">
						<td colspan="4" align="left">
							<b>{t}Employer Contributions{/t}</b>
						</td>
						<td>
							<b>{t}Amount{/t}</b>
						</td>
						<td>
							<b>{t}YTD Amount{/t}</b>
						</td>
					</tr>
				{/if}

				<tr class="{$row_class}" align="right">
					<td colspan="4" align="left">
						{if $pay_stub_entry.type != 40}&nbsp;&nbsp;{/if}
						{if $pay_stub_entry.type == 40}<b>{/if}
						{$pay_stub_entry.display_name} {if $pay_stub_entry.description_subscript !== NULL}[{ $pay_stub_entry.description_subscript}]{/if}
						<input type="hidden" name="data[entries][{$pay_stub_entry.tmp_type}][{$pay_stub_entry.id}][display_name]" value="{$pay_stub_entry.display_name}">
						{if $pay_stub_entry.type == 40}</b>{/if}
						{if $pay_stub_entry.pay_stub_amendment_id != ''}
							[ <a href="{urlbuilder script="../pay_stub_amendment/EditPayStubAmendment.php" values="id=$pay_stub_amendment_id" merge="FALSE"}">{t}Edit{/t}</a> ]
						{/if}

					</td>
					<td>
						{if $pay_stub_entry.type == 30}
							{if $pay_stub_entry.pay_stub_amendment_id == ''}
								<input type="text" size="10" name="data[entries][{$pay_stub_entry.tmp_type}][{$pay_stub_entry.id}][amount]" value="{$pay_stub_entry.amount}" onChange="setModifiedEntry()">
							{else}
								{$pay_stub_entry.amount}
								<input type="hidden" name="data[entries][{$pay_stub_entry.tmp_type}][{$pay_stub_entry.id}][amount]" value="{$pay_stub_entry.amount}">
							{/if}
						{else}
							<b>{$pay_stub_entry.amount}</b>
							<input type="hidden" name="data[entries][{$pay_stub_entry.tmp_type}][{$pay_stub_entry.id}][amount]" value="{$pay_stub_entry.amount}">
						{/if}
						<input type="hidden" name="data[entries][{$pay_stub_entry.tmp_type}][{$pay_stub_entry.id}][type]" value="{$pay_stub_entry.type}">
					</td>
					<td>
						{if $pay_stub_entry.type == 40}<b>{/if}
						{$pay_stub_entry.ytd_amount}
						<input type="hidden" name="data[entries][{$pay_stub_entry.tmp_type}][{$pay_stub_entry.id}][ytd_amount]" value="{$pay_stub_entry.ytd_amount}">
						<input type="hidden" name="data[entries][{$pay_stub_entry.tmp_type}][{$pay_stub_entry.id}][id]" value="{$pay_stub_entry.id}">
						<input type="hidden" name="data[entries][{$pay_stub_entry.tmp_type}][{$pay_stub_entry.id}][type]" value="{$pay_stub_entry.type}">
						<input type="hidden" name="data[entries][{$pay_stub_entry.tmp_type}][{$pay_stub_entry.id}][tmp_type]" value="{$pay_stub_entry.tmp_type}">
						<input type="hidden" name="data[entries][{$pay_stub_entry.tmp_type}][{$pay_stub_entry.id}][pay_stub_amendment_id]" value="{$pay_stub_entry.pay_stub_amendment_id}">
						<input type="hidden" name="data[entries][{$pay_stub_entry.tmp_type}][{$pay_stub_entry.id}][description]" value="{$pay_stub_entry.description|escape}">
						{if $pay_stub_entry.type == 40}</b>{/if}
					</td>
				</tr>
				{if $smarty.foreach.employer_deductions.last}
					<tr>
						<td colspan="6">
							<br>
						</td>
					</tr>
				{/if}
			{/foreach}
			{/if}

			{* Accruals *}
			{foreach name=other from=$data.entries.50 item=pay_stub_entry}
				{assign var="pay_stub_amendment_id" value=$pay_stub_entry.pay_stub_amendment_id}

				{if $smarty.foreach.other.first}
					<tr class="tblHeader" align="right">
						<td colspan="4" align="left">
							<b>{t}Accrual{/t}</b>
						</td>
						<td>
							<b>{t}Amount{/t}</b>
						</td>
						<td>
							<b>{t}Balance{/t}</b>
						</td>
					</tr>
				{/if}

				<tr class="{$row_class}" align="right">
					<td colspan="4" align="left">
						{if $pay_stub_entry.type != 40}&nbsp;&nbsp;{/if}
						{if $pay_stub_entry.type == 40}<b>{/if}
						{$pay_stub_entry.display_name} {if $pay_stub_entry.description_subscript !== NULL}[{ $pay_stub_entry.description_subscript}]{/if}
						<input type="hidden" name="data[entries][{$pay_stub_entry.tmp_type}][{$pay_stub_entry.id}][display_name]" value="{$pay_stub_entry.display_name}">
						{if $pay_stub_entry.type == 40}</b>{/if}
						{if $pay_stub_entry.pay_stub_amendment_id != ''}
							[ <a href="{urlbuilder script="../pay_stub_amendment/EditPayStubAmendment.php" values="id=$pay_stub_amendment_id" merge="FALSE"}">{t}Edit{/t}</a> ]
						{/if}
					</td>
					<td>
						{if $pay_stub_entry.type == 50}
							{if $pay_stub_entry.pay_stub_amendment_id == ''}
								<input type="text" size="10" name="data[entries][{$pay_stub_entry.tmp_type}][{$pay_stub_entry.id}][amount]" value="{$pay_stub_entry.amount}" onChange="setModifiedEntry()">
							{else}
								{$pay_stub_entry.amount}
								<input type="hidden" name="data[entries][{$pay_stub_entry.tmp_type}][{$pay_stub_entry.id}][amount]" value="{$pay_stub_entry.amount}">
							{/if}
						{else}
							<b>{$pay_stub_entry.amount}</b>
							<input type="hidden" name="data[entries][{$pay_stub_entry.tmp_type}][{$pay_stub_entry.id}][amount]" value="{$pay_stub_entry.amount}">
						{/if}
						<input type="hidden" name="data[entries][{$pay_stub_entry.tmp_type}][{$pay_stub_entry.id}][type]" value="{$pay_stub_entry.type}">
					</td>
					<td>
						{if $pay_stub_entry.type == 40}<b>{/if}
						{$pay_stub_entry.ytd_amount}
						<input type="hidden" name="data[entries][{$pay_stub_entry.tmp_type}][{$pay_stub_entry.id}][ytd_amount]" value="{$pay_stub_entry.ytd_amount}">
						<input type="hidden" name="data[entries][{$pay_stub_entry.tmp_type}][{$pay_stub_entry.id}][id]" value="{$pay_stub_entry.id}">
						<input type="hidden" name="data[entries][{$pay_stub_entry.tmp_type}][{$pay_stub_entry.id}][type]" value="{$pay_stub_entry.type}">
						<input type="hidden" name="data[entries][{$pay_stub_entry.tmp_type}][{$pay_stub_entry.id}][tmp_type]" value="{$pay_stub_entry.tmp_type}">
						<input type="hidden" name="data[entries][{$pay_stub_entry.tmp_type}][{$pay_stub_entry.id}][pay_stub_amendment_id]" value="{$pay_stub_entry.pay_stub_amendment_id}">
						<input type="hidden" name="data[entries][{$pay_stub_entry.tmp_type}][{$pay_stub_entry.id}][description]" value="{$pay_stub_entry.description|escape}">
						{if $pay_stub_entry.type == 40}</b>{/if}
					</td>
				</tr>
				{if $smarty.foreach.other.last}
					<tr>
						<td colspan="6">
							<br>
						</td>
					</tr>
				{/if}
			{/foreach}

			{* Descriptions *}
			{foreach name=description from=$data.entry_descriptions item=description}
				{if $smarty.foreach.description.first}
					<tr>
						<td class="tblHeader" colspan="6">
							<b>{t}Notes{/t}</b>
						</td>
					</tr>
				{/if}

				<tr class="{$row_class}" >
					<td colspan="6" align="left">
						[{$description.subscript}] {$description.description}
					</td>
				</tr>
			{/foreach}

			</table>
		</div>

		<div id="contentBoxFour">
			<input type="submit" class="btnSubmit" name="action:submit" value="{t}Submit{/t}" onClick="return singleSubmitHandler(this)">
		</div>

		<input type="hidden" name="id" value="{$pay_stub_id}">
		<input type="hidden" name="filter_pay_period_id" value="{$filter_pay_period_id}">
		<input type="hidden" name="data[user_full_name]" value="{$data.user_full_name}">
		<input type="hidden" id="modified_entry" name="modified_entry" value="{$modified_entry}">
	</form>
</div>
{include file="footer.tpl"}