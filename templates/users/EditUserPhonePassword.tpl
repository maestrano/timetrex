{include file="header.tpl"}
<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">

		<form method="post" action="{$smarty.server.SCRIPT_NAME}">
		    <div id="contentBoxTwoEdit">
				{if !$uf->Validator->isValid()}
					{include file="form_errors.tpl" object="uf"}
				{/if}

				<table class="editTable">

				{*
				<tr id="warning" >
					<td colspan="2">
						<div align="center">{t escape="no"}To Sign-In/Out over the telephone dial <b>1-800-714-5153</b>, press "<b>8</b>" at the menu and follow the prompts.{/t}</div>
					</td>
				</tr>
				*}

				<tr onClick="showHelpEntry('user_name')">
					<td class="{isvalid object="uf" label="user_name" value="cellLeftEditTable"}">
						{t}User Name:{/t}
					</td>
					<td class="cellRightEditTable">
						{$user_data.user_name}
					</td>
				</tr>

				<tr onClick="showHelpEntry('phone_id')">
					<td class="{isvalid object="uf" label="phone_id" value="cellLeftEditTable"}">
						{t}Quick Punch ID:{/t}
					</td>
					<td class="cellRightEditTable">
						{$user_data.phone_id|default:"N/A"}
					</td>
				</tr>

				{if $user_data.phone_password !== NULL}
					<tr onClick="showHelpEntry('phone_password')">
						<td class="{isvalid object="uf" label="phone_password" value="cellLeftEditTable"}">
							{t}Current Quick Punch Password:{/t}
						</td>
						<td class="cellRightEditTable">
							<input type="password" name="user_data[current_password]" value="{$user_data.current_password}">
						</td>
					</tr>
				{/if}

				<tr onClick="showHelpEntry('phone_password')">
					<td class="{isvalid object="uf" label="phone_password" value="cellLeftEditTable"}">
						{t}New Quick Punch Password:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="password" name="user_data[password]" value="">
					</td>
				</tr>

				<tr onClick="showHelpEntry('phone_password')">
					<td class="{isvalid object="uf" label="phone_password" value="cellLeftEditTable"}">
						{t}New Quick Punch Password (confirm):{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="password" name="user_data[password2]" value="">
					</td>
				</tr>
			</table>
		</div>

		<div id="contentBoxFour">
			<input type="submit" class="btnSubmit" name="action:submit" value="{t}Submit{/t}" onClick="return singleSubmitHandler(this)">
		</div>

		<input type="hidden" name="user_data[id]" value="{$user_data.id}">
		</form>
	</div>
</div>
{include file="footer.tpl"}
