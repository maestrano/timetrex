{include file="sm_header.tpl" authenticate=FALSE}

{include file="install/Install.js.tpl"}
<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">

		<form method="post" action="{$smarty.server.SCRIPT_NAME}" onSubmit="return submitButtonPressed">
		    <div id="contentBoxTwoEdit">

				{if !$uf->Validator->isValid()}
					{include file="form_errors.tpl" object="uf"}
				{/if}

				<table class="editTable">

				<tr>
					<td class="tblDataWhiteNH" colspan="7" align="right">
						{t}Please enter the administrator user name and password.{/t}
						<br>
						<br>
						<b>*{t}IMPORTANT{/t}*:</b> {t}Please write this information down, as you will need it later to login to{/t} {$APPLICATION_NAME}</b>
					</td>
				</tr>

				<tr onClick="showHelpEntry('user_name')">
					<td class="{isvalid object="uf" label="user_name" value="cellLeftEditTable"}">
						{t}User Name:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="user_data[user_name]" value="{$user_data.user_name}">
					</td>
				</tr>

				<tr onClick="showHelpEntry('password')">
					<td class="{isvalid object="uf" label="password" value="cellLeftEditTable"}">
						{t}Password:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="password" name="user_data[password]" value="{$user_data.password}">
					</td>
				</tr>

				<tr onClick="showHelpEntry('password')">
					<td class="{isvalid object="uf" label="password" value="cellLeftEditTable"}">
						{t}Password (confirm):{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="password" name="user_data[password2]" value="{$user_data.password2}">
					</td>
				</tr>

				<tr onClick="showHelpEntry('first_name')">
					<td class="{isvalid object="uf" label="first_name" value="cellLeftEditTable"}">
						{t}First Name:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="user_data[first_name]" value="{$user_data.first_name}">
					</td>
				</tr>

				<tr onClick="showHelpEntry('last_name')">
					<td class="{isvalid object="uf" label="last_name" value="cellLeftEditTable"}">
						{t}Last Name:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="user_data[last_name]" value="{$user_data.last_name}">
					</td>
				</tr>

				<tr onClick="showHelpEntry('work_email')">
					<td class="{isvalid object="uf" label="work_email" value="cellLeftEditTable"}">
						{t}Email:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="user_data[work_email]" value="{$user_data.work_email}">
					</td>
				</tr>

				</table>
		</div>

		<div id="contentBoxFour">
			<input type="submit" class="btnSubmit" id="next_button" name="action:back" value="{t}Back{/t}" onMouseDown="submitButtonPressed = true">
			<input type="submit" class="btnSubmit" id="next_button" name="action:next" value="{t}Next{/t}" onMouseDown="submitButtonPressed = true">
		</div>

		<input type="hidden" name="user_data[company_id]" value="{$user_data.company_id}">
		<input type="hidden" name="external_installer" value="{$external_installer}">
		</form>
	</div>
</div>
{include file="footer.tpl"}
