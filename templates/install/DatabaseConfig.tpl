{include file="sm_header.tpl" authenticate=FALSE body_onload="showDatabaseTypeWarning();"}

{include file="install/Install.js.tpl"}

<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">
		<table class="editTable">
		<form method="get" action="{$smarty.server.SCRIPT_NAME}">
				<tr>
					<td class="tblPagingLeft" colspan="7" align="right">
						<br>
					</td>
				</tr>

				{if $data.test_connection !== NULL}
				<tr>
					{if $data.test_connection === TRUE}
						<td class="tblDataWarning" colspan="2">
						{t escape="no"}Connection test to your database as a non-privileged user has <b>SUCCEEDED</b>! You may continue.{/t}
						</td>
					{elseif $data.test_connection === FALSE}
						<td class="tblDataERROR" colspan="2">
						{t escape="no"}Connection test to your database as a non-privileged user has <b>FAILED</b>! Please correct them and try again.{/t}
						</td>
					{/if}
				</tr>
				{/if}

				{if $data.test_priv_connection !== NULL}
				<tr>
					{if $data.test_priv_connection === FALSE}
						<td class="tblDataERROR" colspan="2">
						{t escape="no"}Connection test to your database as a privileged user has <b>FAILED</b>! Please correct the user name/password and try again.{/t}
						</td>
					{/if}

				</tr>
				{/if}

				{if $data.database_engine == FALSE }
				<tr>
					<td class="tblDataERROR" colspan="2">
					Your MySQL database does not support the <b>InnoDB</b> storage engine which is required for {$APPLICATION_NAME} to use transactions and ensure data integrity. Please add <b>InnoDB</b> support to MySQL before continuing.
					</td>
				</tr>
				{/if}

				<tr>
					<td class="tblDataWhiteNH" colspan="7" align="right">
						{t}Please enter your database configuration information below. If you are unsure, use the default values.{/t}
					</td>
				</tr>

				<tr>
					<td class="cellLeftEditTable">
						Database Type:
					</td>
					<td class="cellRightEditTable">
						<select id='type' name="data[type]" onChange="showDatabaseTypeWarning()">
							{html_options options=$data.type_options selected=$data.type}
						</select>
					</td>
				</tr>

				<tr>
					<td class="cellLeftEditTable">
						{t}Host Name:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="data[host]" value="{$data.host}">
					</td>
				</tr>

				<tr>
					<td class="cellLeftEditTable">
						{t}Database Name:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="data[database_name]" value="{$data.database_name}">
					</td>
				</tr>

				<tr>
					<td class="cellLeftEditTable">
						{t}User Name for{/t} {$APPLICATION_NAME}:
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="data[user]" value="{$data.user}">
					</td>
				</tr>

				<tr>
					<td class="cellLeftEditTable">
						{t}Password for{/t} {$APPLICATION_NAME}:
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="data[password]" value="{$data.password}">
					</td>
				</tr>

				<tr>
					<td class="tblDataWhiteNH" colspan="2">
						{t}Privileged Database User Name / Password. This is only used to create the database schema if the above user does not have permissions to do so.{/t}
					</td>
				</tr>

				<tr>
					<td class="cellLeftEditTable">
						{t}Privileged Database User Name:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="data[priv_user]" value="{$data.priv_user}"> (ie: root, postgres)
					</td>
				</tr>

				<tr>
					<td class="cellLeftEditTable">
						{t}Privileged Database User Password:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="data[priv_password]" value="{$data.priv_password}">
					</td>
				</tr>

{*
				<tr>
					<td class="tblDataWhiteNH" colspan="2">
						{t escape="no" 1=$install_obj->getPHPConfigFile()}<b>Note:</b> Your PHP configuration file (php.ini) is located at:<br> %1{/t}
					</td>
				</tr>
*}
				<tr>
					<td class="tblPagingLeft" colspan="7" align="right">
						<input type="submit" id="next_button" name="action:back" value="{t}Back{/t}">
						<input type="submit" id="test_connection" name="action:test_connection" value="{t}Test Connection{/t}">
						<input type="submit" id="next_button" name="action:Next" value="{t}Next{/t}" onClick="return confirmSubmit('{t}Installing/Upgrading the TimeTrex database may take up to 10 minutes. Please do not stop the process in any way, including pressing STOP or BACK in your web browser, doing so may leave your database in an unusable state.{/t}')">
					</td>
				</tr>
			</table>
			<input type="hidden" name="external_installer" value="{$external_installer}">
		</form>
	</div>
</div>
{include file="footer.tpl"}