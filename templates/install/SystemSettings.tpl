{include file="sm_header.tpl" authenticate=FALSE}

{include file="install/Install.js.tpl"}

<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">
		<table class="editTable">
		<form method="get" action="{$smarty.server.SCRIPT_NAME}" onSubmit="return submitButtonPressed" >
				<tr>
					<td class="tblPagingLeft" colspan="7" align="right">
						<br>
					</td>
				</tr>

				<tr>
					<td class="tblDataWhiteNH" colspan="7" align="right">
						{t}Please enter your site configuration information below. If you are unsure of the fields, we suggest that you use the default values.{/t}
					</td>
				</tr>

				<tr>
					<td class="cellLeftEditTable">
						{t}URL:{/t}
					</td>
					<td class="cellRightEditTable">
						<b>http://{$data.host_name}</b><input type="text" size="30" name="data[base_url]" value="{$data.base_url}"> {t}(No trailing slash){/t}
					</td>
				</tr>

				<tr>
					<td class="cellLeftEditTable">
						{t}Log Directory:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="30" name="data[log_dir]" value="{$data.log_dir}">
					</td>
				</tr>

				<tr>
					<td class="cellLeftEditTable">
						{t}Storage Directory:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="30" name="data[storage_dir]" value="{$data.storage_dir}"> (for things like attachments, logos, etc...)
					</td>
				</tr>

				<tr>
					<td class="cellLeftEditTable">
						{t}Cache Directory:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="30" name="data[cache_dir]" value="{$data.cache_dir}">
					</td>
				</tr>

				{if $install_obj->getTTProductEdition() == 10}
				<tr>
					<td class="cellLeftEditTable">
						{t escape="no" 1=$APPLICATION_NAME}Enable %1 Update Notifications:{/t}
						<br>
						<span style="font-weight: normal">
						{t}When this is enabled, administrators will receive update{/t}
						{t escape="no" 1="<font color='red'>" 2="</font>"}notices when new versions, or updated %1<b>tax formulas/tables</b>%2 are available.{/t}
						{t}These notices require your system to periodically send
						TimeTrex statistics about your installation in order to
						help us understand usage patterns and improve the product.{/t}
						</span>
					</td>
					<td class="cellRightEditTable">
						<input type="checkbox" class="checkbox" name="data[update_notify]" value="1" checked>
						<font color='red'><b>{t}*HIGHLY RECOMMENDED{/t}</b></font>
					</td>
				</tr>

				<tr>
					<td class="cellLeftEditTable">
						{t}Make statistics anonymous:{/t}
						<br>
					</td>
					<td class="cellRightEditTable">
						<input type="checkbox" class="checkbox" name="data[anonymous_update_notify]" value="1">
					</td>
				</tr>
				{/if}

				<tr>
					<td class="tblPagingLeft" colspan="7" align="right">
						<input type="submit" class="btnSubmit" id="next_button" name="action:back" value="{t}Back{/t}" onMouseDown="submitButtonPressed = true">
						<input type="submit" class="btnSubmit" id="next_button" name="action:next" value="{t}Next{/t}" onMouseDown="submitButtonPressed = true">
					</td>
				</tr>
			</table>
			<input type="hidden" name="external_installer" value="{$external_installer}">
		</form>
	</div>
</div>
{include file="footer.tpl"}