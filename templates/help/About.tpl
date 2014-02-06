{include file="header.tpl"}
<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title} {$APPLICATION_NAME}</span></div>
</div>
<div id="rowContentInner">
		<table class="editTable">
		<form method="get" action="{$smarty.server.SCRIPT_NAME}">
				<tr>
					<td class="tblPagingLeft" colspan="7" align="right">
						<br>
					</td>
				</tr>

				<tr class="tblDataWhiteNH">
					<td colspan="2">
						<br>
						<a href="http://{$ORGANIZATION_URL}"><img src="{$BASE_URL}/send_file.php?object_type=primary_company_logo" style="width:auto; height:42px;" alt="Time And Attendance"></a>
						<br>
						<br>
					</td>
				</tr>

				{if $data.new_version == 1}
				<tr class="tblDataWarning">
					<td colspan="2">
						<br>
						{t escape="no"}<b>NOTICE:</b> There is a new version of <b>{$APPLICATION_NAME}</b> available.{/t}
						<br>
						{t}This version may contain tax table updates necessary for accurate payroll calculation, we recommend that you upgrade as soon as possible.{/t}
						<br>
						{t escape="no"}The latest version can be downloaded from:{/t} <a href="http://{$ORGANIZATION_URL}/?upgrade=1" target="_blank"><b>{$ORGANIZATION_URL}</b></a>
						<br>
						<br>
					</td>
				</tr>
				{/if}

				<tr>
					<td colspan="2" class="cellLeftEditTable">
						<div align="center">{t}System Information{/t}</div>
					</td>
				</tr>

				<tr onClick="showHelpEntry('version')">
					<td class="cellLeftEditTable">
						{t}Product Edition:{/t}
					</td>
					<td class="cellRightEditTable">
						{$data.product_edition}
					</td>
				</tr>

				<tr onClick="showHelpEntry('version')">
					<td class="cellLeftEditTable">
						{t}Version:{/t}
					</td>
					<td class="cellRightEditTable">
						{$data.system_version}
						{if DEPLOYMENT_ON_DEMAND == FALSE}
							<input type="submit" name="action:Check_For_Updates" value="{t}Check For Updates{/t}"/>
						{/if}
					</td>
				</tr>

				<tr onClick="showHelpEntry('tax_engine_version')">
					<td class="cellLeftEditTable">
						{t}Tax Engine Version:{/t}
					</td>
					<td class="cellRightEditTable">
						{$data.tax_engine_version}
					</td>
				</tr>

				<tr onClick="showHelpEntry('tax_data_version')">
					<td class="cellLeftEditTable">
						{t}Tax Data Version:{/t}
					</td>
					<td class="cellRightEditTable">
						{$data.tax_data_version}
					</td>
				</tr>

				{if DEPLOYMENT_ON_DEMAND == FALSE}
				<tr onClick="showHelpEntry('version')">
					<td class="cellLeftEditTable">
						{t}Registration Key:{/t}
					</td>
					<td class="cellRightEditTable">
						{$data.registration_key|default:"N/A"}
					</td>
				</tr>
				{/if}

				<tr onClick="showHelpEntry('version')">
					<td class="cellLeftEditTable">
						{t}Maintenance Jobs Last Ran:{/t}
					</td>
					<td class="cellRightEditTable">
						{if $data.cron.last_run_date != ''}
							{getdate type="DATE+TIME" epoch=$data.cron.last_run_date}
						{else}
							{t}Never{/t}
						{/if}
					</td>
				</tr>

				{if isset($data.license_data)}
					<tr>
						<td colspan="2" class="cellLeftEditTable" {if isset($data.license_data.message) AND $data.license_data.message != ''}style="font-weight: bold; background-color: red"{/if}>
							<div align="center">
								{t}License Information{/t}
								{if isset($data.license_data.message) AND $data.license_data.message != ''}
									<br>
									{t}WARNING:{/t} {$data.license_data.message}!
								{/if}
							</div>
						</td>
					</tr>
					<tr onClick="showHelpEntry('license_upload')">
						<td class="cellLeftEditTable">
							{t}Upload License:{/t} <a href="javascript:Upload('license','');"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_popup.gif"></a>
						</td>
						<td class="cellRightEditTable">
							<b>{t}Click the "..." icon to upload a license file.{/t}</b>
						</td>
					</tr>
					{if isset($data.license_data) AND $data.license_data.organization_name != ''}
						<tr onClick="showHelpEntry('license_product_name')">
							<td class="cellLeftEditTable">
								{t}Product:{/t}
							</td>
							<td class="cellRightEditTable">
								{$data.license_data.product_name}
							</td>
						</tr>

						<tr onClick="showHelpEntry('license_organization_name')">
							<td class="cellLeftEditTable">
								{t}Company:{/t}
							</td>
							<td class="cellRightEditTable">
								{$data.license_data.organization_name}
							</td>
						</tr>

						<tr onClick="showHelpEntry('license_version')">
							<td class="cellLeftEditTable">
								{t}Version:{/t}
							</td>
							<td class="cellRightEditTable">
								{$data.license_data.major_version}.{$data.license_data.minor_version}.X
							</td>
						</tr>

						<tr onClick="showHelpEntry('license_active_employee_licenses')">
							<td class="cellLeftEditTable">
								{t}Active Employee Licenses:{/t}
							</td>
							<td class="cellRightEditTable">
								{$data.license_data.active_employee_licenses}
							</td>
						</tr>

						<tr onClick="showHelpEntry('license_issue_date')">
							<td class="cellLeftEditTable">
								{t}Issue Date:{/t}
							</td>
							<td class="cellRightEditTable">
								{$data.license_data.issue_date}
							</td>
						</tr>

						<tr onClick="showHelpEntry('license_expire_date')">
							<td class="cellLeftEditTable">
								{t}Expire Date:{/t}
							</td>
							<td class="cellRightEditTable">
								{$data.license_data.expire_date_display}
							</td>
						</tr>
					{/if}
				{/if}

				<tr>
					<td colspan="2" class="cellLeftEditTable">
						<div align="center">{t}Schema Version{/t}</div>
					</td>
				</tr>

				{if $data.schema_version_group_A != '' }
				<tr onClick="showHelpEntry('schema_version')">
					<td class="cellLeftEditTable">
						{t}Group A:{/t}
					</td>
					<td class="cellRightEditTable">
						{$data.schema_version_group_A}
					</td>
				</tr>
				{/if}

				{if $data.schema_version_group_B != '' }
				<tr onClick="showHelpEntry('schema_version')">
					<td class="cellLeftEditTable">
						{t}Group B:{/t}
					</td>
					<td class="cellRightEditTable">
						{$data.schema_version_group_B}
					</td>
				</tr>
				{/if}

				{if $data.schema_version_group_T != '' }
				<tr onClick="showHelpEntry('schema_version')">
					<td class="cellLeftEditTable">
						{t}Group T:{/t}
					</td>
					<td class="cellRightEditTable">
						{$data.schema_version_group_T}
					</td>
				</tr>
				{/if}

				{if count($data.user_counts) > 0}
				<tr>
					<td colspan="2" class="cellLeftEditTable">
						<div align="center">{t}Employees (Active / InActive){/t}</div>
					</td>
				</tr>
				{foreach from=$data.user_counts item=user_counts}
					<tr>
						<td class="cellLeftEditTable">
							{$user_counts.label}:
						</td>
						<td class="cellRightEditTable">
							{$user_counts.max_active_users} / {$user_counts.max_inactive_users}
						</td>
					</tr>
				{/foreach}
				{/if}

				<tr>
					<td class="tblPagingLeft" colspan="7" align="right">
						<br>
					</td>
				</tr>
			</table>
		</form>
	</div>
</div>
{include file="footer.tpl"}