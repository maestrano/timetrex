<table border="0" cellpadding="2" cellspacing="0">
    <tr height="3">
		<td width="20"></td>
		<td width="70"></td>
		<td width="70"></td>
		<td width="70"></td>
		<td width="70"></td>
		<td width="70"></td>
		<td width="70"></td>
		<td width="70"></td>
		<td width="70"></td>
		<td width="70"></td>
    </tr>

	<tr>
		<td rowspan="3" colspan="5" align="center" valign="center">
			{*
			<b><font size="+1">{$company_obj->getName()}</font></b><br>
			{$company_obj->getAddress1()}<br>
			{if $company_obj->getAddress2()}
				{$company_obj->getAddress2()}<br>
			{/if}
			{$company_obj->getCity()}, {$company_obj->getProvince()} {$company_obj->getPostalCode()}
			*}
			<br>
		</td>
		<td rowspan="3" colspan="3" align="center" valign="top">
			{*
			{t}YOUR BANK NAME{/t}<br>
			{t}YOUR BANK ADDRESS{/t}<br>
			{t}City, Province, Postal Code{/t}
			*}
			<br>
		</td>
		<td colspan="2">
			<br>
		</td>
	</tr>
	<tr>
		<td rowspan="2" colspan="1" align="right" valign="center">
			{* {$pay_stub.display_id} *}
		</td>
		<td colspan="1">
			<br>
		</td>
	</tr>
	<tr>
		<td colspan="1">
			<br>
		</td>
	</tr>

	<tr>
		<td colspan="10">
			<br>
		</td>
	</tr>

	<tr height="20">
		<td>
			<br>
		</td>
		<td colspan="6" align="left">
			{if $pay_stub.advance == TRUE}{$pay_stub.entries.15.0.amount_words}{else}{$pay_stub.entries.40.0.amount_words}{/if}
			{if $pay_stub.advance == TRUE}{$pay_stub.entries.15.0.amount_cents}{else}{$pay_stub.entries.40.0.amount_cents}{/if}/100
			{* One Hundred and twenty some odd dollars----------------31/100 *}
		</td>
		<td colspan="1" align="right">
			{$pay_stub.transaction_date_display}
		</td>
		<td colspan="2" align="right">
			${if $pay_stub.advance == TRUE}{$pay_stub.entries.15.0.amount}{else}{$pay_stub.entries.40.0.amount}{/if}
		</td>
	</tr>
	<tr>
		<td>
			<br>
		</td>
		<td colspan="4">
			{$user_obj->getFullName()}<br>
			{$user_obj->getAddress1()}<br>
			{if $user_obj->getAddress2()}
				{$user_obj->getAddress2()}<br>
			{/if}
			{$user_obj->getCity()}, {$user_obj->getProvince()} {$user_obj->getPostalCode()}
		</td>
		<td colspan="1">
			<br>
		</td>
		<td colspan="4" align="center">
			{*
			<b><font size="+1">{$company_obj->getName()}</font></b><br>
			*}
			<br>
		</td>
	</tr>
	{* <font size="1">Identification #: {$pay_stub.display_id}{if $pay_stub.tainted == TRUE}T{/if}</font> *}
</table>