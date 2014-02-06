<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td colspan="6">
			<table width="100%">
				<tr align="center">
					<td width="25%">
						<img src="{$BASE_PATH}/images/timetrex_logo_wbg_small2.jpg" width="177" height="45" alt="{$APPLICATION_NAME}">
					</td>
					<td width="50%">
						<b><font size="+1">{$company_obj->getName()}</font></b><br>
						{$company_obj->getAddress1()}<br>
						{if $company_obj->getAddress2()}
							{$company_obj->getAddress2()}<br>
						{/if}
						{$company_obj->getCity()}, {$company_obj->getProvince()} {$company_obj->getPostalCode()}
					</td>
					<td width="25%" align="right">
						<table width="100%" cellpadding="0" cellspacing="0">
							<tr align="right">
								<td width="100%" nowrap>
									{t}Pay Start Date:{/t}
								</td>
								<td align="right" nowrap>
									&nbsp;<b>{getdate type="DATE" epoch=$pay_stub.start_date default=TRUE}</b>
								</td>
							</tr>
							<tr align="right" nowrap>
								<td>
									{t}Pay End Date:{/t}
								</td>
								<td align="right" nowrap>
									&nbsp;<b>{getdate type="DATE" epoch=$pay_stub.end_date default=TRUE}</b>
								</td>
							</tr>
							<tr align="right" nowrap>
								<td>
									{t}Payment Date:{/t}
								</td>
								<td align="right" nowrap>
									&nbsp;<b>{getdate type="DATE" epoch=$pay_stub.transaction_date default=TRUE}</b>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</td>
	</tr>

	<tr>
		<td align="center" colspan="6">
			<hr>
			<h3>{t}STATEMENT OF EARNINGS AND DEDUCTIONS{/t}</h3>
			<hr>
		</td>
	</tr>

	{* Earnings *}
	{foreach name=earnings from=$pay_stub.entries.10 item=pay_stub_entry}
		{if $smarty.foreach.earnings.first}
			<tr align="right">
				<td width="40%" align="left">
					<b>{t}Earnings{/t}</b>
				</td>
				<td width="15%">
					<Br>
				</td>
				<td width="10%">
					<b>{t}Rate{/t}</b>
				</td>
				<td width="10%">
					<b>{t}Hours{/t}</b>
				</td>
				<td width="10%">
					<b>{t}Amount{/t}</b>
				</td>
				{*
				<td width="15%">
					<b>{t}YTD Hours{/t}</b>
				</td>
				*}
				<td width="15%">
					<b>{t}YTD Amount{/t}</b>
				</td>
			</tr>
		{/if}

		<tr align="right">
			<td align="left">
				{if $pay_stub_entry.type != 40}&nbsp;&nbsp;{/if}
				{if $pay_stub_entry.type == 40}<b>{/if}
				{$pay_stub_entry.display_name} {if $pay_stub_entry.description_subscript !== NULL}[{ $pay_stub_entry.description_subscript}]{/if}
				{if $pay_stub_entry.type == 40}</b>{/if}
			</td>
			<td>
				<br>
			</td>
			<td>

				{if $pay_stub_entry.type == 10}
					{if $pay_stub_entry.type == 40}<b>{/if}
					{$pay_stub_entry.rate}
					{if $pay_stub_entry.type == 40}</b>{/if}
				{else}
					<br>
				{/if}

			</td>
			<td>
				{if $pay_stub_entry.type == 40}<b>{/if}
				{$pay_stub_entry.units}
				{if $pay_stub_entry.type == 40}</b>{/if}
			</td>
			<td>
				{if $pay_stub_entry.type == 40}<b>{/if}
				{$pay_stub_entry.amount}
				{if $pay_stub_entry.type == 40}</b>{/if}
			</td>
			{*
			<td>
				{if $pay_stub_entry.type == 40}<b>{/if}
				{$pay_stub_entry.ytd_units}
				{if $pay_stub_entry.type == 40}</b>{/if}
			</td>
			*}
			<td>
				{if $pay_stub_entry.type == 40}<b>{/if}
				{$pay_stub_entry.ytd_amount}
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


	{* Advance Pay *}
	{foreach name=advance_pay from=$pay_stub.entries.60 item=pay_stub_entry}
		{if $smarty.foreach.advance_pay.first}
			<tr>
				<td width="40%">
				</td>
				<td width="10%">
				</td>
				<td width="10%">
				</td>
				<td width="10%">
				</td>
				<td width="15%">
				</td>
				<td width="15%">
				</td>
			</tr>
			<tr align="right">
				<td colspan="4" align="left">
					<b>{t escape="no" 1=$pay_period_data.pay_period_number 2=$pay_period_data.annual_pay_periods}Pay Period %1 of %2{/t}</b>
				</td>
				<td>
					<b>{t}Amount{/t}</b>
				</td>
				<td>
					<b>{t}YTD Amount{/t}</b>
				</td>
			</tr>
		{/if}

		<tr align="right">
			<td colspan="4" align="left">
				{if $pay_stub_entry.type != 40}&nbsp;&nbsp;{/if}
				{if $pay_stub_entry.type == 40}<b>{/if}
				{$pay_stub_entry.display_name} {if $pay_stub_entry.description_subscript !== NULL}[{ $pay_stub_entry.description_subscript}]{/if}
				{if $pay_stub_entry.type == 40}</b>{/if}
			</td>
			<td>
				{if $pay_stub_entry.type == 40}<b>{/if}
				{$pay_stub_entry.amount}
				{if $pay_stub_entry.type == 40}</b>{/if}
			</td>
			<td>
				{if $pay_stub_entry.type == 40}<b>{/if}
				{$pay_stub_entry.ytd_amount}
				{if $pay_stub_entry.type == 40}</b>{/if}
			</td>
		</tr>
		{if $smarty.foreach.advance_pay.last}
			<tr>
				<td colspan="6">
					<br>
				</td>
			</tr>
		{/if}
	{/foreach}

	{* Deductions *}
	{foreach name=deductions from=$pay_stub.entries.20 item=pay_stub_entry}
		{if $smarty.foreach.deductions.first}
			<tr align="right">
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

		<tr align="right">
			<td colspan="4" align="left">
				{if $pay_stub_entry.type != 40}&nbsp;&nbsp;{/if}
				{if $pay_stub_entry.type == 40}<b>{/if}
				{$pay_stub_entry.display_name} {if $pay_stub_entry.description_subscript !== NULL}[{ $pay_stub_entry.description_subscript}]{/if}
				{if $pay_stub_entry.type == 40}<b>{/if}
			</td>
			<td>
				{if $pay_stub_entry.type == 40}<b>{/if}
				{$pay_stub_entry.amount}
				{if $pay_stub_entry.type == 40}<b>{/if}
			</td>
			<td>
				{if $pay_stub_entry.type == 40}<b>{/if}
				{$pay_stub_entry.ytd_amount}
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

	{* Advance Deduction *}
	{foreach name=advance_deduction from=$pay_stub.entries.65 item=pay_stub_entry}
		{if $smarty.foreach.advance_deduction.first}
			<tr align="right">
				<td colspan="4" align="left">
					<b>{t escape="no" 1=$pay_period_data.pay_period_number 2=$pay_period_data.annual_pay_periods}Pay Period %1 of %2{/t}</b>

				</td>
				<td>
					<b>{t}Amount{/t}</b>
				</td>
				<td>
					<b>{t}YTD Amount{/t}</b>
				</td>
			</tr>
		{/if}

		<tr align="right">
			<td colspan="4" align="left">
				{if $pay_stub_entry.type != 40}&nbsp;&nbsp;{/if}
				{t}Net Pay{/t}
			</td>
			<td>
				{$pay_stub_entry.amount+$pay_stub.entries.40.0.amount|number_format}
			</td>
			<td>
				{$pay_stub_entry.ytd_amount+$pay_stub.entries.40.0.ytd_amount|number_format}
			</td>
		</tr>

		<tr align="right">
			<td colspan="4" align="left">
				{if $pay_stub_entry.type != 40}&nbsp;&nbsp;{/if}
				{if $pay_stub_entry.type == 40}<b>{/if}
				{$pay_stub_entry.display_name} {if $pay_stub_entry.description_subscript !== NULL}[{ $pay_stub_entry.description_subscript}]{/if}
				{if $pay_stub_entry.type == 40}</b>{/if}
			</td>
			<td>
				{if $pay_stub_entry.type == 40}<b>{/if}
				-{$pay_stub_entry.amount}
				{if $pay_stub_entry.type == 40}</b>{/if}
			</td>
			<td>
				{if $pay_stub_entry.type == 40}<b>{/if}
				-{$pay_stub_entry.ytd_amount}
				{if $pay_stub_entry.type == 40}</b>{/if}
			</td>
		</tr>
	{/foreach}

	{if $pay_stub.advance == FALSE}
		<tr align="right">
			<td colspan="4" align="left">
				<b>
				{if count($pay_stub.entries.65) > 0}
					{t}Balance{/t}
				{else}
					{$pay_stub.entries.40.0.display_name}
				{/if}
				</b>
			</td>
			<td>
				<b>{$pay_stub.entries.40.0.amount}</b>
			</td>
			<td>
				<b>{$pay_stub.entries.40.0.ytd_amount}</b>
			</td>
		</tr>

		<tr>
			<td colspan="6">
				<br>
			</td>
		</tr>
	{/if}

	{* Employer Deductions *}
	{if $permission->Check('pay_stub','view') AND $hide_employer_rows != 1}
	{foreach name=employer_deductions from=$pay_stub.entries.30 item=pay_stub_entry}
		{if $smarty.foreach.employer_deductions.first}
			<tr align="right">
				<td colspan="4" align="left">
					<b{t}>Employer Contributions{/t}</b>
				</td>
				<td>
					<b>{t}Amount{/t}</b>
				</td>
				<td>
					<b>{t}YTD Amount{/t}</b>
				</td>
			</tr>
		{/if}

		<tr align="right">
			<td colspan="4" align="left">
				{if $pay_stub_entry.type != 40}&nbsp;&nbsp;{/if}
				{if $pay_stub_entry.type == 40}<b>{/if}
				{$pay_stub_entry.display_name} {if $pay_stub_entry.description_subscript !== NULL}[{ $pay_stub_entry.description_subscript}]{/if}
				{if $pay_stub_entry.type == 40}</b>{/if}
			</td>
			<td>
				{if $pay_stub_entry.type == 40}<b>{/if}
				{$pay_stub_entry.amount}
				{if $pay_stub_entry.type == 40}</b>{/if}
			</td>
			<td>
				{if $pay_stub_entry.type == 40}<b>{/if}
				{$pay_stub_entry.ytd_amount}
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

	{* Other *}
	{foreach name=other from=$pay_stub.entries.50 item=pay_stub_entry}
		{if $smarty.foreach.other.first}
			<tr align="right">
				<td colspan="4" align="left">
					<b>{t}Other{/t}</b>
				</td>
				<td>
					<b>{t}Amount{/t}</b>
				</td>
				<td>
					<b>{t}YTD Amount{/t}</b>
				</td>
			</tr>
		{/if}

		<tr align="right">
			<td colspan="4" align="left">
				{if $pay_stub_entry.type != 40}&nbsp;&nbsp;{/if}
				{if $pay_stub_entry.type == 40}<b>{/if}
				{$pay_stub_entry.display_name} {if $pay_stub_entry.description_subscript !== NULL}[{ $pay_stub_entry.description_subscript}]{/if}
				{if $pay_stub_entry.type == 40}</b>{/if}
			</td>
			<td>
				{if $pay_stub_entry.type == 40}<b>{/if}
				{$pay_stub_entry.amount}
				{if $pay_stub_entry.type == 40}</b>{/if}
			</td>
			<td>
				{if $pay_stub_entry.type == 40}<b>{/if}
				{$pay_stub_entry.ytd_amount}
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
	{foreach name=description from=$pay_stub_entry_descriptions item=description}
		{if $smarty.foreach.description.first}
			<tr>
				<td colspan="6">
					<b>Notes</b>
				</td>
			</tr>
		{/if}

		<tr align="left">
			<td colspan="6">
				[{$description.subscript}] {$description.description}
			</td>
		</tr>
		{if $smarty.foreach.description.last}
			<tr>
				<td colspan="6">
					<br>
				</td>
			</tr>
		{/if}
	{/foreach}

	{section name="spacer" start=0 loop=$spacer_rows}
		<tr>
			<td colspan="6">
				<br>
			</td>
		</tr>
	{/section}

	<tr align="center">
		<td colspan="6">
			<hr>
			<b><h3>{t}NON NEGOTIABLE{/t}</h3></b>
		</td>
	</tr>
	<tr align="right">
		<td colspan="1" align="center">
			<b>{t}CONFIDENTIAL{/t}</b><br>
			{$user_obj->getFullName()}<br>
			{$user_obj->getAddress1()}<br>
			{if $user_obj->getAddress2()}
				{$user_obj->getAddress2()}<br>
			{/if}
			{$user_obj->getCity()}, {$user_obj->getProvince()} {$user_obj->getPostalCode()}
		</td>
		<td colspan="3">
			<br>
		</td>
		<td colspan="2" align="left">
			{t escape="no" 1=$pay_period_data.pay_period_number 2=$pay_period_data.annual_pay_periods}Pay Period %1 of %2{/t}

			<br>
			<b>
			{if count($pay_stub.entries.65) > 0}
			{t}Balance:{/t}
			{else}
			{t}Net Pay:{/t}
			{/if}
			${if $pay_stub.advance == TRUE}{$pay_stub.entries.60.0.amount}{else}{$pay_stub.entries.40.0.amount}{/if}</b><br>
			<br>
			<font size="1">Identification #: {$pay_stub.display_id}{if $pay_stub.tainted == TRUE}T{/if}</font>
		</td>
	</tr>
	<tr align="center">
		<td colspan="6">
			<hr>
		</td>
	</tr>
</table>