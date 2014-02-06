{include file="header.tpl" enable_ajax=true body_onload="showAccrualType();showType()"}

<script	language=JavaScript>
{literal}
function showType() {
	if ( document.getElementById('type_id').value == 50 ) {
		document.getElementById('accrual').style.display = 'none';
	} else {
		document.getElementById('accrual').className = '';
		document.getElementById('accrual').style.display = '';
	}

	getNextPayStubAccountOrderByTypeId();
}
function showAccrualType() {
	if ( document.getElementById('accrual_id').value != 0) {
		document.getElementById('accrual_type').className = '';
		document.getElementById('accrual_type').style.display = '';

	} else {
		document.getElementById('accrual_type').style.display = 'none';
	}
}

var hwCallback = {
		getNextPayStubAccountOrderByTypeId: function(result) {
			if ( result != false ) {
				document.getElementById('ps_order').value = result;
			}
		}
	}

var remoteHW = new AJAX_Server(hwCallback);

function getNextPayStubAccountOrderByTypeId() {
	remoteHW.getNextPayStubAccountOrderByTypeId( document.getElementById('type_idsv').value );
}
{/literal}
</script>

<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">

		<form method="post" name="wage" action="{$smarty.server.SCRIPT_NAME}">
		    <div id="contentBoxTwoEdit">
				{if !$pseaf->Validator->isValid()}
					{include file="form_errors.tpl" object="pseaf"}
				{/if}

				<table class="editTable">

				<tr onClick="showHelpEntry('status')">
					<td class="{isvalid object="pseaf" label="status" value="cellLeftEditTable"}">
						{t}Status:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="status_id" name="data[status_id]">
							{html_options options=$data.status_options selected=$data.status_id}
						</select>
					</td>
				</tr>

				<tr onClick="showHelpEntry('type')">
					<td class="{isvalid object="pseaf" label="type" value="cellLeftEditTable"}">
						{t}Type:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="type_id" name="data[type_id]" onChange="showType();">
							{html_options options=$data.type_options selected=$data.type_id}
						</select>
					</td>
				</tr>

				<tr onClick="showHelpEntry('name')">
					<td class="{isvalid object="pseaf" label="name" value="cellLeftEditTable"}">
						{t}Name:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="30" name="data[name]" value="{$data.name}">
					</td>
				</tr>

				<tr onClick="showHelpEntry('ps_order')">
					<td class="{isvalid object="pseaf" label="ps_order" value="cellLeftEditTable"}">
						{t}Order:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="6" id="ps_order" name="data[order]" value="{$data.order}">
					</td>
				</tr>

				<tbody id="accrual" style="display:none" >
				<tr onClick="showHelpEntry('accrual_pay_stub_entry_account_id')">
					<td class="{isvalid object="pseaf" label="accrual_pay_stub_entry_account_id" value="cellLeftEditTable"}">
						{t}Accrual:{/t}
					</td>
					<td class="cellRightEditTable">
						<select name="data[accrual_id]" id="accrual_id" onChange="showAccrualType();">
							{html_options options=$data.accrual_options selected=$data.accrual_id}
						</select>
					</td>
				</tr>
					<tbody id="accrual_type" style="display:none" >
					<tr onClick="showHelpEntry('accrual_type_id')">
						<td class="{isvalid object="pseaf" label="accrual_type_id" value="cellLeftEditTable"}">
							{t}Accrual Type:{/t}
						</td>
						<td class="cellRightEditTable">
							<select name="data[accrual_type_id]">
								{html_options options=$data.accrual_type_options selected=$data.accrual_type_id}
							</select>
						</td>
					</tr>
					</tbody>
				</tbody>

				<tr onClick="showHelpEntry('debit_account')">
					<td class="{isvalid object="pseaf" label="debit_account" value="cellLeftEditTable"}">
						{t}Debit Account:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="40" name="data[debit_account]" value="{$data.debit_account}">
					</td>
				</tr>

				<tr onClick="showHelpEntry('credit_account')">
					<td class="{isvalid object="pseaf" label="credit_account" value="cellLeftEditTable"}">
						{t}Credit Account:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="40" name="data[credit_account]" value="{$data.credit_account}">
					</td>
				</tr>

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
