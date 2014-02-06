{include file="header.tpl"}
<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">

		<form method="post" action="{$smarty.server.SCRIPT_NAME}">
		    <div id="contentBoxTwoEdit">
				{if !$ugf->Validator->isValid()}
					{include file="form_errors.tpl" object="ugf"}
				{/if}

				<table class="editTable">

				<tr onClick="showHelpEntry('parent')">
					<td class="{isvalid object="ugf" label="parent" value="cellLeftEditTable"}">
						{t}Parent:{/t}
					</td>
					<td class="cellRightEditTable">
						<select name="data[parent_id]">
							{html_options options=$parent_list_options selected=$data.parent_id}
						</select>
					</td>
				</tr>

				<tr onClick="showHelpEntry('name')">
					<td class="{isvalid object="ugf" label="name" value="cellLeftEditTable"}">
						{t}Name:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="data[name]" value="{$data.name}">
					</td>
				</tr>
			</table>
		</div>

		<div id="contentBoxFour">
			<input type="submit" class="btnSubmit" name="action:submit" value="{t}Submit{/t}" onClick="return singleSubmitHandler(this)">
		</div>

		<input type="hidden" name="data[id]" value="{$data.id}">
		<input type="hidden" name="previous_parent_id" value="{$data.previous_parent_id}">
		</form>
	</div>
</div>
{include file="footer.tpl"}
