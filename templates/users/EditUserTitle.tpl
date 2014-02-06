{include file="header.tpl"}
<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">

		<form method="post" action="{$smarty.server.SCRIPT_NAME}">
		    <div id="contentBoxTwoEdit">
				{if !$utf->Validator->isValid()}
					{include file="form_errors.tpl" object="utf"}
				{/if}

			<table class="editTable">

				<tr onClick="showHelpEntry('name')">
					<td class="{isvalid object="utf" label="name" value="cellLeftEditTable"}">
						{t}Title:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="50" name="title_data[name]" value="{$title_data.name}">
					</td>
				</tr>

			</table>
		</div>

		<div id="contentBoxFour">
			<input type="submit" class="btnSubmit" name="action:submit" value="{t}Submit{/t}" onClick="return singleSubmitHandler(this)">
		</div>

		<input type="hidden" name="title_data[id]" value="{$title_data.id}">
		</form>
	</div>
</div>
{include file="footer.tpl"}
