{include file="header.tpl"}
<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">

		<form method="post" action="{$smarty.server.SCRIPT_NAME}">
		    <div id="contentBoxTwoEdit">
				{if !$dbuf->Validator->isValid()}
					{include file="form_errors.tpl" object="dbuf"}
				{/if}

				<table class="editTable">

				<tr>
					<td class="cellLeftEditTable">
						{t}Department:{/t}
					</td>
					<td class="cellRightEditTable">
						{$department_data.name}
					</td>
				</tr>

				<tr>
					<td colspan="2" class="cellLeftEditTable" style="text-align:center;">
						{t}Branches{/t}
					</td>
				</tr>

				{foreach from=$department_data.branch_data item=branch}
					<tr onClick="showHelpEntry('name')">
						<td class="{isvalid object="dbuf" label="name" value="cellLeftEditTable"}">
							{$branch.name}:
						</td>
						<td class="cellRightEditTable">
							<select name="department_data[branch_data][{$branch.id}][]" multiple>
								{html_options options=$department_data.user_options selected=$branch.user_ids}
							</select>
						</td>
					</tr>

				{/foreach}
			</table>
		</div>

		<div id="contentBoxFour">
			<input type="submit" class="btnSubmit" name="action:submit" value="{t}Submit{/t}" onClick="return singleSubmitHandler(this)">
		</div>

		<input type="hidden" name="department_data[id]" value="{$department_data.id}">
		</form>
	</div>
</div>
{include file="footer.tpl"}
