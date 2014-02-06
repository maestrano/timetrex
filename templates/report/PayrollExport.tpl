{include file="header.tpl" enable_calendar=TRUE body_onload="countAllReportCriteria(); showReportDateType(); showExportSettings();"}

<script	language=JavaScript>
{literal}
var report_criteria_elements = new Array(
									'filter_user_status',
									'filter_group',
									'filter_branch',
									'filter_department',
									'filter_user_title',
									'filter_pay_period',
									'filter_include_user',
									'filter_exclude_user',
									'filter_column');

var report_date_type_elements = new Array();
report_date_type_elements['date_type_date'] = new Array('start_date', 'end_date');
report_date_type_elements['date_type_pay_period'] = new Array('src_filter_pay_period', 'filter_pay_period');
function showReportDateType() {
	for ( i in report_date_type_elements ) {
		if ( document.getElementById( i ) ) {
			if ( document.getElementById( i ).checked == true ) {
				class_name = '';
			} else {
				class_name = 'DisableFormElement';
			}

			for (var x=0; x < report_date_type_elements[i].length ; x++) {
				document.getElementById( report_date_type_elements[i][x] ).className = class_name;
			}
		}
	}
}

old_id = {/literal}'{$filter_data.export_type|default:0}'{literal};
function showExportSettings() {
	//alert('Test!');
	export_type = document.getElementById('export_type').value;

	if ( export_type == 0 ) {
		hideObject('export_settings_header');
	} else {
		showObject('export_settings_header');
	}


	new_id = 'export_settings_'+ export_type;
	hideObject(old_id);
	showObject(new_id);

	old_id = new_id;
}
{/literal}
</script>

<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">
		<form method="post" name="report" action="{$smarty.server.SCRIPT_NAME}" target="_self">
			<input type="hidden" id="action" name="action" value="">

		    <div id="contentBoxTwoEdit">

				{if !$ugdf->Validator->isValid()}
					{include file="form_errors.tpl" object="ugdf"}
				{/if}

				<table class="editTable">

				<tr class="tblHeader">
					<td colspan="3">
						{t}Saved Reports{/t}
					</td>
				</tr>

				{htmlreportsave generic_data=$generic_data object="ugdf"}

				<tr class="tblHeader">
					<td colspan="3">
						{t}Report Filter Criteria{/t}
					</td>
				</tr>

				<tr>
					<td class="cellReportRadioColumn" rowspan="2">
						<input type="radio" class="checkbox" id="date_type_date" name="filter_data[date_type]" value="date" onClick="showReportDateType();" {if $filter_data.date_type  == '' OR $filter_data.date_type == 'date'}checked{/if}>
					</td>
					<td class="cellLeftEditTableHeader">
						{t}Start Date:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="15" id="start_date" name="filter_data[start_date]" value="{getdate type="DATE" epoch=$filter_data.start_date}">
						<img src="{$BASE_URL}/images/cal.gif" id="cal_start_date" width="16" height="16" border="0" alt="Pick a date" onMouseOver="calendar_setup('start_date', 'cal_start_date', false);">
						{t}ie:{/t} {$current_user_prefs->getDateFormatExample()}
					</td>
				</tr>

				<tr>
					<td class="cellLeftEditTableHeader">
						{t}End Date:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="15" id="end_date" name="filter_data[end_date]" value="{getdate type="DATE" epoch=$filter_data.end_date}">
						<img src="{$BASE_URL}/images/cal.gif" id="cal_end_date" width="16" height="16" border="0" alt="Pick a date" onMouseOver="calendar_setup('end_date', 'cal_end_date', false);">
						{t}ie:{/t} {$current_user_prefs->getDateFormatExample()}
					</td>
				</tr>

				{capture assign=report_display_name}{t}Pay Period{/t}{/capture}
				{capture assign=report_display_plural_name}{t}Pay Periods{/t}{/capture}
				{htmlreportfilter filter_data=$filter_data date_type=true label='pay_period' display_name=$report_display_name display_plural_name=$report_display_plural_name}

				{capture assign=report_display_name}{t}Employee Status{/t}{/capture}
				{capture assign=report_display_plural_name}{t}Employee Statuses{/t}{/capture}
				{htmlreportfilter filter_data=$filter_data label='user_status' display_name=$report_display_name display_plural_name=$report_display_plural_name}

				{capture assign=report_display_name}{t}Group{/t}{/capture}
				{capture assign=report_display_plural_name}{t}Groups{/t}{/capture}
				{htmlreportfilter filter_data=$filter_data label='group' display_name=$report_display_name display_plural_name=$report_display_plural_name}

				{capture assign=report_display_name}{t}Default Branch{/t}{/capture}
				{capture assign=report_display_plural_name}{t}Branches{/t}{/capture}
				{htmlreportfilter filter_data=$filter_data label='branch' display_name=$report_display_name display_plural_name=$report_display_plural_name}

				{capture assign=report_display_name}{t}Default Department{/t}{/capture}
				{capture assign=report_display_plural_name}{t}Departments{/t}{/capture}
				{htmlreportfilter filter_data=$filter_data label='department' display_name=$report_display_name display_plural_name=$report_display_plural_name}

				{capture assign=report_display_name}{t}Employee Title{/t}{/capture}
				{capture assign=report_display_plural_name}{t}Titles{/t}{/capture}
				{htmlreportfilter filter_data=$filter_data label='user_title' display_name=$report_display_name display_plural_name=$report_display_plural_name}

				{capture assign=report_display_name}{t}Include Employees{/t}{/capture}
				{capture assign=report_display_plural_name}{t}Employees{/t}{/capture}
				{htmlreportfilter filter_data=$filter_data label='include_user' display_name=$report_display_name display_plural_name=$report_display_plural_name}

				{capture assign=report_display_name}{t}Exclude Employees{/t}{/capture}
				{capture assign=report_display_plural_name}{t}Employees{/t}{/capture}
				{htmlreportfilter filter_data=$filter_data label='exclude_user' display_name=$report_display_name display_plural_name=$report_display_plural_name}

				<tr onClick="showHelpEntry('sort')">
					<td colspan="2" class="{isvalid object="uwf" label="type" value="cellLeftEditTableHeader"}">
						{t}Export Format:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="export_type" name="filter_data[export_type]" onChange="showExportSettings();">
							{html_options options=$filter_data.export_type_options selected=$filter_data.export_type}
						</select>
					</td>
				</tr>

				<tr id="export_settings_header" class="tblHeader">
					<td colspan="3">
						{t}Export Format Settings{/t}
					</td>
				</tr>

				<tr>
					<td colspan="3">
						{*
							//
							//ADP
							//
						*}
						<table id="export_settings_adp" style="display:none" class="editTable" width="100%">
							<tr>
								<td class="cellLeftEditTableHeader">
									Company Code:
								</td>
								<td class="cellRightEditTable" colspan="2">
									<input type="text" size="15" id="adp_company_code" name="setup_data[adp][company_code]" value="{$setup_data.adp.company_code}">
								</td>
							</tr>
							<tr>
								<td class="cellLeftEditTableHeader">
									Batch ID:
								</td>
								<td class="cellRightEditTable" colspan="2">
									<input type="text" size="15" id="adp_company_code" name="setup_data[adp][batch_id]" value="{$setup_data.adp.batch_id}">
								</td>
							</tr>

							{foreach from=$setup_data.src_column_options key=column_id item=column name=columns}
								{cycle assign=row_class values="tblDataWhite,tblDataGrey"}
								{if $smarty.foreach.columns.first == TRUE}
								<tr class="tblHeader">
									<td width="33%">
										{t}Hours{/t}
									</td>
									<td width="33%">
										{t}ADP Hours{/t}
									</td>
									<td width="33%">
										{t}ADP Hours Code{/t}
									</td>
								</tr>
								{/if}
								<tr class="{$row_class}">
									<td>
										<span style="float: left">
										{if strpos($column_id, 'regular') !== FALSE}
											&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
											{assign var="adp_column_id" value="regular_time"}
											{assign var="adp_hour_code" value="REG Hours"}
										{elseif strpos($column_id, 'over_time') !== FALSE}
											<b>{t}Overtime: {/t}</b>
											{assign var="adp_column_id" value="overtime"}
											{assign var="adp_hour_code" value="O/T Hours"}
										{elseif strpos($column_id, 'premium') !== FALSE}
											<b>{t}Premium: {/t}</b>
											{assign var="adp_column_id" value="3"}
											{assign var="adp_hour_code" value="PRE"}
										{elseif strpos($column_id, 'absence') !== FALSE}
											<b>{t}Absence: {/t}</b>
											{assign var="adp_column_id" value="4"}
											{assign var="adp_hour_code" value="ABS"}
										{/if}
										</span>
										{$column}
									</td>
									<td>
										<select id="adp_hour_column" name="setup_data[adp][columns][{$column_id}][hour_column]">
											{html_options options=$setup_data.adp_hour_column_options selected=$setup_data.adp.columns.$column_id.hour_column|default:$adp_column_id}
										</select>
									</td>
									<td>
										<input type="text" size="15" id="adp_hour_code" name="setup_data[adp][columns][{$column_id}][hour_code]" value="{$setup_data.adp.columns.$column_id.hour_code|default:$adp_hour_code}">
									</td>
								</tr>
							{/foreach}
						</table>


						{*
							//
							//PayChex Preview
							//
						*}
						<table id="export_settings_paychex_preview" style="display:none" class="editTable" width="100%">
							<tr>
								<td class="cellLeftEditTableHeader">
									Client Number:
								</td>
								<td class="cellRightEditTable" colspan="2">
									<input type="text" size="15" id="paychex_preview_client_number" name="setup_data[paychex_preview][client_number]" value="{$setup_data.paychex_preview.client_number|default:"0001"}">
								</td>
							</tr>

							{foreach from=$setup_data.src_column_options key=column_id item=column name=columns}
								{cycle assign=row_class values="tblDataWhite,tblDataGrey"}
								{if $smarty.foreach.columns.first == TRUE}
								<tr class="tblHeader">
									<td width="33%">
										{t}Hours{/t}
									</td>
									<td width="33%">
										{t}Paychex Hours Code{/t}
									</td>
								</tr>
								{/if}
								<tr class="{$row_class}">
									<td>
										<span style="float: left">
										{if strpos($column_id, 'regular') !== FALSE}
											&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
										{elseif strpos($column_id, 'over_time') !== FALSE}
											<b>{t}Overtime: {/t}</b>
										{elseif strpos($column_id, 'premium') !== FALSE}
											<b>{t}Premium: {/t}</b>
										{elseif strpos($column_id, 'absence') !== FALSE}
											<b>{t}Absence: {/t}</b>
										{/if}
										</span>
										{$column}
									</td>
									<td>
										<input type="text" size="15" id="paychex_preview_hour_code" name="setup_data[paychex_preview][columns][{$column_id}][hour_code]" value="{$setup_data.paychex_preview.columns.$column_id.hour_code|default:$smarty.foreach.columns.iteration}">
									</td>
								</tr>
							{/foreach}
						</table>

                        {*
							//
							//Paychex Online Payroll CSV
							//
						*}
						<table id="export_settings_paychex_online" style="display:none" class="editTable" width="100%">
							{foreach from=$setup_data.src_column_options key=column_id item=column name=columns}
								{cycle assign=row_class values="tblDataWhite,tblDataGrey"}
								{if $smarty.foreach.columns.first == TRUE}
								<tr class="tblHeader">
									<td width="33%">
										{t}Hours{/t}
									</td>
									<td width="33%">
										{t}Earnings Code{/t}
									</td>
								</tr>
								{/if}
								<tr class="{$row_class}">
									<td>
										<span style="float: left">
										{if strpos($column_id, 'regular') !== FALSE}
											&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
											{assign var="paychex_online_hour_code" value="Regular"}
										{elseif strpos($column_id, 'over_time') !== FALSE}
											<b>{t}Overtime: {/t}</b>
											{assign var="paychex_online_hour_code" value="Overtime"}
										{elseif strpos($column_id, 'premium') !== FALSE}
											<b>{t}Premium: {/t}</b>
											{assign var="paychex_online_hour_code" value="Premium"}
										{elseif strpos($column_id, 'absence') !== FALSE}
											<b>{t}Absence: {/t}</b>
											{assign var="paychex_online_hour_code" value="Absence"}
										{/if}
										</span>
										{$column}
									</td>
									<td>
										<input type="text" size="15" id="paychex_online_hour_code" name="setup_data[paychex_online][columns][{$column_id}][hour_code]" value="{$setup_data.paychex_online.columns.$column_id.hour_code|default:$paychex_online_hour_code}">
									</td>
								</tr>
							{/foreach}
						</table>


						{*
							//
							//Ceridian InSync
							//
						*}
						<table id="export_settings_ceridian_insync" style="display:none" class="editTable" width="100%">
							{foreach from=$setup_data.src_column_options key=column_id item=column name=columns}
								{cycle assign=row_class values="tblDataWhite,tblDataGrey"}
								{if $smarty.foreach.columns.first == TRUE}
								<tr class="tblHeader">
									<td width="33%">
										{t}Hours{/t}
									</td>
									<td width="33%">
										{t}Ceridian Hours Code{/t}
									</td>
								</tr>
								{/if}
								<tr class="{$row_class}">
									<td>
										<span style="float: left">
										{if strpos($column_id, 'regular') !== FALSE}
											&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
											{assign var="ceridian_insync_hour_code" value="REG"}
										{elseif strpos($column_id, 'over_time') !== FALSE}
											<b>{t}Overtime: {/t}</b>
											{assign var="ceridian_insync_hour_code" value="OT"}
										{elseif strpos($column_id, 'premium') !== FALSE}
											<b>{t}Premium: {/t}</b>
											{assign var="ceridian_insync_hour_code" value="PRE"}
										{elseif strpos($column_id, 'absence') !== FALSE}
											<b>{t}Absence: {/t}</b>
											{assign var="ceridian_insync_hour_code" value="ABS"}
										{/if}
										</span>
										{$column}
									</td>
									<td>
										<input type="text" size="15" id="ceridian_insync_hour_code" name="setup_data[ceridian_insync][columns][{$column_id}][hour_code]" value="{$setup_data.ceridian_insync.columns.$column_id.hour_code|default:$ceridian_insync_hour_code}">
									</td>
								</tr>
							{/foreach}
						</table>


						{*
							//
							//Millenium
							//
						*}
						<table id="export_settings_millenium" style="display:none" class="editTable" width="100%">
							{foreach from=$setup_data.src_column_options key=column_id item=column name=columns}
								{cycle assign=row_class values="tblDataWhite,tblDataGrey"}
								{if $smarty.foreach.columns.first == TRUE}
								<tr class="tblHeader">
									<td width="33%">
										{t}Hours{/t}
									</td>
									<td width="33%">
										{t}Millenium Hours Code{/t}
									</td>
								</tr>
								{/if}
								<tr class="{$row_class}">
									<td>
										<span style="float: left">
										{if strpos($column_id, 'regular') !== FALSE}
											&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
											{assign var="millenium_hour_code" value="REG"}
										{elseif strpos($column_id, 'over_time') !== FALSE}
											<b>{t}Overtime: {/t}</b>
											{assign var="millenium_hour_code" value="OT"}
										{elseif strpos($column_id, 'premium') !== FALSE}
											<b>{t}Premium: {/t}</b>
											{assign var="millenium_hour_code" value="PRE"}
										{elseif strpos($column_id, 'absence') !== FALSE}
											<b>{t}Absence: {/t}</b>
											{assign var="millenium_hour_code" value="ABS"}
										{/if}
										</span>
										{$column}
									</td>
									<td>
										<input type="text" size="15" id="millenium_hour_code" name="setup_data[millenium][columns][{$column_id}][hour_code]" value="{$setup_data.millenium.columns.$column_id.hour_code|default:$millenium_hour_code}">
									</td>
								</tr>
							{/foreach}
						</table>
						{*
							//
							//Generic CSV
							//
						*}
						<table id="export_settings_csv" style="display:none" class="editTable" width="100%">
							{foreach from=$setup_data.src_column_options key=column_id item=column name=columns}
								{cycle assign=row_class values="tblDataWhite,tblDataGrey"}
								{if $smarty.foreach.columns.first == TRUE}
								<tr class="tblHeader">
									<td width="33%">
										{t}Hours{/t}
									</td>
									<td width="33%">
										{t}Hours Code{/t}
									</td>
								</tr>
								{/if}
								<tr class="{$row_class}">
									<td>
										<span style="float: left">
										{if strpos($column_id, 'regular') !== FALSE}
											&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
											{assign var="csv_hour_code" value="REGULAR"}
										{elseif strpos($column_id, 'over_time') !== FALSE}
											<b>{t}Overtime: {/t}</b>
											{assign var="csv_hour_code" value="OT"}
										{elseif strpos($column_id, 'premium') !== FALSE}
											<b>{t}Premium: {/t}</b>
											{assign var="csv_hour_code" value="PREMIUM"}
										{elseif strpos($column_id, 'absence') !== FALSE}
											<b>{t}Absence: {/t}</b>
											{assign var="csv_hour_code" value="ABSENCE"}
										{/if}
										</span>
										{$column}
									</td>
									<td>
										<input type="text" size="15" id="csv_hour_code" name="setup_data[csv][columns][{$column_id}][hour_code]" value="{$setup_data.csv.columns.$column_id.hour_code|default:$csv_hour_code}">
									</td>
								</tr>
							{/foreach}
						</table>

						{*
							//
							//Quickbooks
							//
						*}
						<table id="export_settings_quickbooks" style="display:none" class="editTable" width="100%">
							<tr>
								<td class="cellLeftEditTableHeader">
									Company Name:
								</td>
								<td class="cellRightEditTable" colspan="2">
									<input type="text" size="35" id="quickbooks_company_name" name="setup_data[quickbooks][company_name]" value="{$setup_data.quickbooks.company_name|default:$current_company->getName()}"> <b>({t}Exactly as shown in Quickbooks{/t})</b>
								</td>
							</tr>
							<tr>
								<td class="cellLeftEditTableHeader">
									Company Created Time:
								</td>
								<td class="cellRightEditTable" colspan="2">
									<input type="text" size="15" id="quickbooks_company_created_date" name="setup_data[quickbooks][company_created_date]" value="{$setup_data.quickbooks.company_created_date|default:$current_company->getCreatedDate()}"> <b>({t}Exactly as shown in exported timer list{/t})</b>
								</td>
							</tr>

							<tr>
								<td class="cellLeftEditTableHeader">
									Map PROJ Field To:
								</td>
								<td class="cellRightEditTable" colspan="2">
									<select id="quickbooks_proj" name="setup_data[quickbooks][proj]">
										{html_options options=$setup_data.quickbooks_proj_options selected=$setup_data.quickbooks.proj}
									</select>
								</td>
							</tr>

							{foreach from=$setup_data.src_column_options key=column_id item=column name=columns}
								{cycle assign=row_class values="tblDataWhite,tblDataGrey"}
								{if $smarty.foreach.columns.first == TRUE}
								<tr class="tblHeader">
									<td width="33%">
										{t}Hours{/t}
									</td>
									<td width="33%">
										{t}Quickbooks Payroll Item Name{/t}
									</td>
								</tr>
								{/if}
								<tr class="{$row_class}">
									<td>
										<span style="float: left">
										{if strpos($column_id, 'regular') !== FALSE}
											&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
											{assign var="quickbooks_hour_code" value="Regular"}
										{elseif strpos($column_id, 'over_time') !== FALSE}
											<b>{t}Overtime: {/t}</b>
											{assign var="quickbooks_hour_code" value="Overtime Hourly"}
										{elseif strpos($column_id, 'premium') !== FALSE}
											<b>{t}Premium: {/t}</b>
											{assign var="quickbooks_hour_code" value="Premium Hourly"}
										{elseif strpos($column_id, 'absence') !== FALSE}
											<b>{t}Absence: {/t}</b>
											{assign var="quickbooks_hour_code" value="Vacation Hourly"}
										{/if}
										</span>
										{$column}
									</td>
									<td>
										<input type="text" size="35" id="quickbooks_hour_code" name="setup_data[quickbooks][columns][{$column_id}][hour_code]" value="{$setup_data.quickbooks.columns.$column_id.hour_code|default:$quickbooks_hour_code}">
									</td>
								</tr>
							{/foreach}
						</table>

						{*
							//
							//SurePayroll
							//
						*}
						<table id="export_settings_surepayroll" style="display:none" class="editTable" width="100%">
							{foreach from=$setup_data.src_column_options key=column_id item=column name=columns}
								{cycle assign=row_class values="tblDataWhite,tblDataGrey"}
								{if $smarty.foreach.columns.first == TRUE}
								<tr class="tblHeader">
									<td width="33%">
										{t}Hours{/t}
									</td>
									<td width="33%">
										{t}Payroll Code{/t}
									</td>
								</tr>
								{/if}
								<tr class="{$row_class}">
									<td>
										<span style="float: left">
										{if strpos($column_id, 'regular') !== FALSE}
											&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
											{assign var="surepayroll_hour_code" value="01"}
										{elseif strpos($column_id, 'over_time') !== FALSE}
											<b>{t}Overtime: {/t}</b>
											{assign var="surepayroll_hour_code" value="02"}
										{elseif strpos($column_id, 'premium') !== FALSE}
											<b>{t}Premium: {/t}</b>
											{assign var="surepayroll_hour_code" value="08"}
										{elseif strpos($column_id, 'absence') !== FALSE}
											<b>{t}Absence: {/t}</b>
											{assign var="surepayroll_hour_code" value="05"}
										{/if}
										</span>
										{$column}
									</td>
									<td>
										<input type="text" size="15" id="surepayroll_hour_code" name="setup_data[surepayroll][columns][{$column_id}][hour_code]" value="{$setup_data.surepayroll.columns.$column_id.hour_code|default:$surepayroll_hour_code}">
									</td>
								</tr>
							{/foreach}
						</table>

						{*
							//
							//Other
							//
						*}
						<table id="export_settings_other" style="display:none" class="editTable" width="100%">
							<tr>
								<td colspan="3" align="center" bgcolor="yellow">
									<br>
									<b>{t}Is your payroll company or software package not in the list?{/t}<br><br>
									{t}Our highly skilled developers will be happy to add it for you, in most cases this can take less then 72 hours to complete, please contact our sales department for more information.{/t}</b>
									<br>
								</td>
							</tr>
						</table>

					</td>
				</tr>

				</table>
			</div>

			<div id="contentBoxFour">
				<input type="submit" name="BUTTON" value="{t}Export{/t}" onClick="selectAllReportCriteria(); this.form.target = '_self'; document.getElementById('action').name = 'action:Export';">
			</div>

			</table>
		</form>
	</div>
</div>
{include file="footer.tpl"}