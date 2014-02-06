<html><head><meta content="text/html; charset=ISO-8859-1" http-equiv="content-type"><title></title></head><body>
<table border="0" cellpadding="2" cellspacing="0">
    <tr height="14">
		<td width="25"></td>
		<td width="25"></td>
		<td width="25"></td>
		<td width="25"></td>
		<td width="25"></td>
		<td width="25"></td>
		<td width="25"></td>
		<td width="25"></td>
		<td width="25"></td>
		<td width="25"></td>
		<td width="25"></td>
		<td width="25"></td>
		<td width="25"></td>
		<td width="25"></td>
		<td width="25"></td>
		<td width="25"></td>
		<td width="25"></td>
		<td width="25"></td>
		<td width="25"></td>
		<td width="25"></td>
		<td width="25"></td>
		<td width="18"></td>
    </tr>

	<tr height="8">
		<td colspan="22"></td>
	</tr>

	<tr height="20">
		<td></td>
		<td colspan="5">{if $print != 1}{$roe.serial}{else}<br>{/if}</td>
		<td colspan="2"><br></td>
		<td colspan="5"><br></td>
		<td colspan="2"><br></td>
		<td colspan="5"><br></td>
	</tr>

	<tr height="{$print_offset+8}">
		<td colspan="22"></td>
	</tr>

	<tr height="20">
		<td></td>
		<td colspan="10" rowspan="5" align="left">
			<br>
			<br>
			{$company_obj->getName()}<br>
			{$company_obj->getAddress1()}<br>
			{if $company_obj->getAddress2()}
				{$company_obj->getAddress2()}<br>
			{/if}
			{$company_obj->getCity()}, {$company_obj->getProvince()}<br>
		</td>
		<td colspan="2"><br></td>
		<td colspan="2"><br></td>
		<td colspan="5"><br></td>
	</tr>

	<tr height="{$print_offset+8}">
		<td colspan="22"></td>
	</tr>

	<tr height="20">
		<td></td>
		<td colspan="2"><br></td>
		<td colspan="2"><br></td>
		<td colspan="5">{$roe.pay_period_type}</td>
	</tr>

	<tr height="{$print_offset+8}">
		<td colspan="22"></td>
	</tr>

	<tr height="20">
		<td></td>
		<td colspan="3" align="center">{$company_obj->getPostalCode()}</td>
		<td colspan="1"><br></td>
		<td colspan="5">{$user_obj->getSIN()}</td>
	</tr>

	<tr height="{$print_offset+10}">
		<td colspan="22"></td>
	</tr>

	<tr height="20">
		<td></td>
		<td colspan="11" rowspan="6" align="left">
			<br>
			{$user_obj->getFullName()}<br>
			{$user_obj->getAddress1()}<br>
			{if $user_obj->getAddress2()}
				{$user_obj->getAddress2()}<br>
			{/if}
			{$user_obj->getCity()}, {$user_obj->getProvince()}<br>
			{$user_obj->getPostalCode()}

		</td>
		<td colspan="6"><br></td>
		<td colspan="1" align="right">{$roe.first_date_arr.mday}</td>
		<td colspan="1" align="right">{$roe.first_date_arr.mon}</td>
		<td colspan="2" align="right">{$roe.first_date_arr.year}</td>
	</tr>

	<tr height="{$print_offset+8}">
		<td colspan="22"></td>
	</tr>

	<tr height="20">
		<td></td>
		<td colspan="6"><br></td>
		<td colspan="1" align="right">{$roe.last_date_arr.mday}</td>
		<td colspan="1" align="right">{$roe.last_date_arr.mon}</td>
		<td colspan="2" align="right">{$roe.last_date_arr.year}</td>
	</tr>

	<tr height="{$print_offset*2+8}">
		<td colspan="22"></td>
	</tr>

	<tr height="20">
		<td></td>
		<td colspan="6"><br></td>
		<td colspan="1" align="right">{$roe.pay_period_end_date_arr.mday}</td>
		<td colspan="1" align="right">{$roe.pay_period_end_date_arr.mon}</td>
		<td colspan="2" align="right">{$roe.pay_period_end_date_arr.year}</td>
	</tr>

	<tr height="{$print_offset*-2+16}">
		<td colspan="22"></td>
	</tr>

	<tr height="20">
		<td></td>
		<td colspan="11"></td>
		<td colspan="6"><br></td>
		<td colspan="1" align="right">{if $roe.recall_date > 0 }{$roe.recall_date_arr.mday}{else}<br>{/if}</td>
		<td colspan="1" align="right">{if $roe.recall_date > 0 }{$roe.recall_date_arr.mon}{else}<br>{/if}</td>
		<td colspan="2" align="right">{if $roe.recall_date > 0 }{$roe.recall_date_arr.year}{else}<br>{/if}</td>
	</tr>

	<tr height="{$print_offset+13}">
		<td colspan="22"></td>
	</tr>

	<tr height="20">
		<td></td>
		<td colspan="10" align="right">{$roe.insurable_hours}</td>
		<td colspan="8"><br></td>
		<td colspan="1" align="right">{$roe.code_id}</td>
	</tr>

	<tr height="{$print_offset+8}">
		<td colspan="22"></td>
	</tr>

	<tr height="20">
		<td></td>
		<td colspan="11"><br></td>
		<td colspan="9" align="right">{$created_user_obj->getFullName()}</td>
	</tr>

	<tr height="20">
		<td></td>
		<td colspan="10" align="right">{$roe.insurable_earnings}</td>
		<td colspan="4"><br></td>
		<td colspan="5" align="left">&nbsp;&nbsp;&nbsp;&nbsp;{$created_user_obj->getWorkPhone()}</td>
	</tr>

	<tr height="{$print_offset*5+47}">
		<td colspan="22"><br></td>
	</tr>
	<tr height="100">
		<td colspan="22"><br></td>
	</tr>
	<tr height="100">
		<td colspan="22"><br></td>
	</tr>
	<tr height="100">
		<td colspan="22"><br></td>
	</tr>

	<tr height="20">
		<td><br></td>
		<td colspan="2" align="left" valign="top">X</td>
		<td colspan="3"><br></td>
		<td colspan="6" align="left">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$created_user_obj->getWorkPhone()}</td>
	</tr>

	<tr height="{$print_offset*3+22}">
		<td colspan="22"></td>
	</tr>

	<tr height="20">
		<td></td>
		<td colspan="8"></td>
		<td colspan="9" align="center">{$created_user_obj->getFullName()}</td>
		<td colspan="1" align="right">{$roe.created_date_arr.mday}</td>
		<td colspan="1" align="right">{$roe.created_date_arr.mon}</td>
		<td colspan="2" align="right">{$roe.created_date_arr.year}</td>
	</tr>
</table>
<br>
</body></html>
