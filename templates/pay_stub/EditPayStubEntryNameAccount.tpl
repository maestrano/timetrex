{include file="header.tpl"}
<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">

		<form method="post" action="{$smarty.server.SCRIPT_NAME}">
		    <div id="contentBoxTwoEdit">
				{if !$psenalf->Validator->isValid()}
					{include file="form_errors.tpl" object="psenalf"}
				{/if}

				<table class="editTable">
				<tr class="tblHeader">
					<td >
						{t}Account{/t}
					</td>
					<td>
						{t}Debit Account Number{/t}
					</td>
					<td>
						{t}Credit Account Number{/t}
					</td>
				</tr>

				{foreach from=$name_account_data item=name_account}
					{if $name_account.display_type == TRUE}
					<tr class="tblDataGreyNH">
						<td colspan="3">
							<b>{$name_account.type}</b>
						</td>
					</tr>

					{/if}

					<input type="hidden" name="name_account_data[{$name_account.pay_stub_entry_name_id}][id]" value="{$name_account.id}">
					<tr onClick="showHelpEntry('date_format')">
						<td class="{isvalid object="psenalf" label="date_format" value="cellLeftEditTable"}">
							{$name_account.pay_stub_entry_description}:
						</td>
						<td class="cellRightEditTable">
							<input type="text" size="5" name="name_account_data[{$name_account.pay_stub_entry_name_id}][debit_account]" value="{$name_account.debit_account}">
						</td>
						<td class="cellRightEditTable">
							<input type="text" size="5" name="name_account_data[{$name_account.pay_stub_entry_name_id}][credit_account]" value="{$name_account.credit_account}">
						</td>
					</tr>

				{/foreach}
			</table>
		</div>

		<div id="contentBoxFour">
			<input type="submit" class="btnSubmit" name="action" value="Submit">
		</div>

		</form>
	</div>
</div>
{include file="footer.tpl"}