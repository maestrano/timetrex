{include file="header.tpl" enable_calendar=true enable_ajax=TRUE body_onload="showCalculation(); filterIncludeCount(); filterExcludeCount(); filterUserCount();"}

{include file="company/EditCompanyDeduction.js.tpl"}

<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">

		<form method="post" name="wage" action="{$smarty.server.SCRIPT_NAME}">
		    <div id="contentBoxTwoEdit">
				{if !$cdf->Validator->isValid()}
					{include file="form_errors.tpl" object="cdf"}
				{/if}

				<table class="editTable">

				<tr onClick="showHelpEntry('status')">
					<td class="{isvalid object="cdf" label="status" value="cellLeftEditTable"}">
						{t}Status:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="status_id" name="data[status_id]">
							{html_options options=$data.status_options selected=$data.status_id}
						</select>
					</td>
				</tr>

				<tr onClick="showHelpEntry('type')">
					<td class="{isvalid object="cdf" label="type" value="cellLeftEditTable"}">
						{t}Type:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="type_id" name="data[type_id]">
							{html_options options=$data.type_options selected=$data.type_id}
						</select>
					</td>
				</tr>

				<tr onClick="showHelpEntry('name')">
					<td class="{isvalid object="cdf" label="name" value="cellLeftEditTable"}">
						{t}Name:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="30" name="data[name]" value="{$data.name}">
					</td>
				</tr>

				<tr class="tblHeader">
					<td colspan="2" >
						{t}Eligibility Criteria{/t}
					</td>
				</tr>

				<tr onClick="showHelpEntry('start_date')">
					<td class="{isvalid object="rscf" label="start_date" value="cellLeftEditTable"}">
						{t}Start Date{/t}:
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="15" id="start_date" name="data[start_date]" value="{getdate type="DATE" epoch=$data.start_date}">
						<img src="{$BASE_URL}/images/cal.gif" id="cal_start_date" width="16" height="16" border="0" alt="Pick a date" onMouseOver="calendar_setup('start_date', 'cal_start_date', false);">
						{t}ie{/t}: {$current_user_prefs->getDateFormatExample()} <b>{t}(Leave blank for no start date){/t}</b>
					</td>
				</tr>

				<tr onClick="showHelpEntry('end_date')">
					<td class="{isvalid object="rscf" label="end_date" value="cellLeftEditTable"}">
						{t}End Date{/t}:
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="15" id="end_date" name="data[end_date]" value="{getdate type="DATE" epoch=$data.end_date}">
						<img src="{$BASE_URL}/images/cal.gif" id="cal_end_date" width="16" height="16" border="0" alt="Pick a date" onMouseOver="calendar_setup('end_date', 'cal_end_date', false);">
						{t}ie{/t}: {$current_user_prefs->getDateFormatExample()} <b>{t}(Leave blank for no end date){/t}</b>
					</td>
				</tr>

				<tr onClick="showHelpEntry('minimum_length_of_service')">
					<td class="{isvalid object="udf" label="minimum_length_of_service" value="cellLeftEditTable"}">
						{t}Minimum Length Of Service:{/t}
					</td>
					<td class="cellRightEditTable">
						<input size="3" type="text" name="data[minimum_length_of_service]" value="{$data.minimum_length_of_service}">
						<select id="" name="data[minimum_length_of_service_unit_id]">
							{html_options options=$data.length_of_service_unit_options selected=$data.minimum_length_of_service_unit_id}
						</select>
					</td>
				</tr>

				<tr onClick="showHelpEntry('maximum_length_of_service')">
					<td class="{isvalid object="udf" label="maximum_length_of_service" value="cellLeftEditTable"}">
						{t}Maximum Length Of Service:{/t}
					</td>
					<td class="cellRightEditTable">
						<input size="3" type="text" name="data[maximum_length_of_service]" value="{$data.maximum_length_of_service}">
						<select id="" name="data[maximum_length_of_service_unit_id]">
							{html_options options=$data.length_of_service_unit_options selected=$data.maximum_length_of_service_unit_id}
						</select>
					</td>
				</tr>

				<tr onClick="showHelpEntry('minimum_user_age')">
					<td class="{isvalid object="udf" label="minimum_user_age" value="cellLeftEditTable"}">
						{t}Minimum Employee Age:{/t}
					</td>
					<td class="cellRightEditTable">
						<input size="3" type="text" name="data[minimum_user_age]" value="{$data.minimum_user_age}"> {t}years{/t}
					</td>
				</tr>

				<tr onClick="showHelpEntry('maximum_user_age')">
					<td class="{isvalid object="udf" label="maximum_user_age" value="cellLeftEditTable"}">
						{t}Maximum Employee Age:{/t}
					</td>
					<td class="cellRightEditTable">
						<input size="3" type="text" name="data[maximum_user_age]" value="{$data.maximum_user_age}"> {t}years{/t}
					</td>
				</tr>

				<tr class="tblHeader">
					<td colspan="2" >
						{t}Calculation Criteria{/t}
					</td>
				</tr>

				<tr onClick="showHelpEntry('calculation')">
					<td class="{isvalid object="cdf" label="calculation" value="cellLeftEditTable"}">
						{t}Calculation:{/t}
					</td>
					<td class="cellRightEditTable">
						{if $data.id == ''}
						<select id="calculation_id" name="data[calculation_id]" onChange="showCalculation()">
							{html_options options=$data.calculation_options selected=$data.calculation_id}
						</select>
						{else}
							{$data.calculation_options[$data.calculation_id]}
							<input type="hidden" id="calculation_id" name="data[calculation_id]" value="{$data.calculation_id}">
						{/if}
						<input type="hidden" id="old_calculation_id" value="{$data.calculation_id}">
					</td>
				</tr>

				<tbody id="country" {if $data.country == NULL AND $data.country == FALSE}style="display:none"{/if}>
				<tr onClick="showHelpEntry('country')">
					<td class="{isvalid object="cdf" label="country" value="cellLeftEditTable"}">
						{t}Country:{/t}
					</td>
					<td class="cellRightEditTable">
						{if $data.id == ''}
						<select id="country_id" name="data[country]" onChange="showCalculation('country')">
							{html_options options=$data.country_options selected=$data.country}
						</select>
						{else}
							{$data.country_options[$data.country]}
							<input type="hidden" id="country_id" name="data[country]" value="{$data.country}">
						{/if}
						<input type="hidden" id="old_country" value="{$data.country}">
					</td>
				</tr>
				</tbody>

				<tbody id="province" {if $data.calculation_id != 200 AND $data.calculation_id != 300}style="display:none"{/if}>
				<tr onClick="showHelpEntry('province')">
					<td class="{isvalid object="cdf" label="province" value="cellLeftEditTable"}">
						{t}Province / State:{/t}
					</td>
					<td class="cellRightEditTable">
						{if $data.id == ''}
						<select id="province_id" name="data[province]" onChange="showCalculation('province')">
							{*{html_options options=$data.province_options selected=$data.province}*}
						</select>
						{else}
							{$data.province_options[$data.province]}
							<input type="hidden" id="province_id" name="data[province]" value="{$data.province}">
						{/if}
						<input type="hidden" id="old_province_id" value="{$data.province}">
						<input type="hidden" id="selected_province" value="{$data.province}">
					</td>
				</tr>
				</tbody>

				<tbody id="district" {if $data.calculation_id != 300}style="display:none"{/if}>
				<tr onClick="showHelpEntry('district')">
					<td class="{isvalid object="cdf" label="district" value="cellLeftEditTable"}">
						{t}District / County:{/t}
					</td>
					<td class="cellRightEditTable">
						{if $data.id == ''}
							<select id="district_id" name="data[district]" onChange="showCalculation('district')">
							</select>
						{else}
							{$data.district_options[$data.district]}
							<input type="hidden" id="district_id" name="data[district]" value="{$data.district}">
						{/if}
						<input type="hidden" id="old_district_id" value="{$data.district}">
						<input type="hidden" id="selected_district" value="{$data.district}">
					</td>
				</tr>
				</tbody>

				{include file="company/EditCompanyDeductionUserValues.tpl"}

				<tr onClick="showHelpEntry('pay_stub_entry_account')">
					<td class="{isvalid object="cdf" label="pay_stub_entry_account" value="cellLeftEditTable"}">
						{t}Pay Stub Account:{/t}
					</td>
					<td class="cellRightEditTable">
						<select name="data[pay_stub_entry_account_id]">
							{html_options options=$data.pay_stub_entry_account_options selected=$data.pay_stub_entry_account_id}
						</select>
					</td>
				</tr>

				<tr onClick="showHelpEntry('calculation_order')">
					<td class="{isvalid object="cdf" label="calcluorder" value="cellLeftEditTable"}">
						{t}Calculation Order:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="6" name="data[calculation_order]" value="{$data.calculation_order}">
					</td>
				</tr>

				<tbody id="filter_include_on" style="display:none" >
				<tr>
					<td class="{isvalid object="cdf" label="include_pay_stub_entry_account" value="cellLeftEditTable"}" nowrap>
						<b>{t}Include Pay Stub Accounts:{/t}</b><a href="javascript:toggleRowObject('filter_include_on');toggleRowObject('filter_include_off');filterIncludeCount();"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_top_sm.gif"></a>
					</td>
					<td colspan="3">
						<table class="editTable">
						<tr class="tblHeader">
							<td colspan="3">
								{t}Pay Stub Account Value{/t}:
								<select name="data[include_account_amount_type_id]">
									{html_options options=$data.account_amount_type_options selected=$data.include_account_amount_type_id}
								</select>
							</td>
						</td>

						<tr class="tblHeader">
							<td>
								{t}Pay Stub Accounts{/t}
							</td>
							<td>
								<br>
							</td>
							<td>
								{t}Included Pay Stub Accounts{/t}
							</td>
						</tr>
						<tr>
							<td class="cellRightEditTable" width="49%" align="center">
								<input type="button" name="Select All" value="{t}Select All{/t}" onClick="selectAll(document.getElementById('src_filter_include'))">
								<input type="button" name="Un-Select" value="{t}Un-Select All{/t}" onClick="unselectAll(document.getElementById('src_filter_include'))">
								<br>
								<select name="src_include_id" id="src_filter_include" style="width:100%;margin:5px 0 5px 0;" size="{select_size array=$data.pay_stub_entry_account_options}" multiple>
									{html_options options=$data.include_pay_stub_entry_account_options}
								</select>
							</td>
							<td class="cellRightEditTable" style="vertical-align: middle;" width="1">
								<a href="javascript:moveItem(document.getElementById('src_filter_include'), document.getElementById('filter_include')); uniqueSelect(document.getElementById('filter_include')); sortSelect(document.getElementById('filter_include'));resizeSelect(document.getElementById('src_filter_include'), document.getElementById('filter_include'), {select_size array=$data.pay_stub_entry_account_options})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_last.gif"></a>
								<br>
								<a href="javascript:moveItem(document.getElementById('filter_include'), document.getElementById('src_filter_include')); uniqueSelect(document.getElementById('src_filter_include')); sortSelect(document.getElementById('src_filter_include'));resizeSelect(document.getElementById('src_filter_include'), document.getElementById('filter_include'), {select_size array=$data.pay_stub_entry_account_options})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_first.gif"></a>
							</td>
							<td class="cellRightEditTable" width="49%" align="center">
								<input type="button" name="Select All" value="{t}Select All{/t}" onClick="selectAll(document.getElementById('filter_include'))">
								<input type="button" name="Un-Select" value="{t}Un-Select All{/t}" onClick="unselectAll(document.getElementById('filter_include'))">
								<br>
								<select name="data[include_pay_stub_entry_account_ids][]" id="filter_include" style="width:100%;margin:5px 0 5px 0;" size="{select_size array=$data.pay_stub_entry_account_options}" multiple>
									{html_options options=$filter_include_options selected=$data.include_pay_stub_entry_account_ids}
								</select>
							</td>
						</tr>
						</table>
					</td>
				</tr>
				</tbody>
				<tbody id="filter_include_off">
				<tr>
					<td class="{isvalid object="cdf" label="user" value="cellLeftEditTable"}" nowrap>
						<b>{t}Include Pay Stub Accounts:{/t}</b><a href="javascript:toggleRowObject('filter_include_on');toggleRowObject('filter_include_off');uniqueSelect(document.getElementById('filter_include'), document.getElementById('src_filter_user')); sortSelect(document.getElementById('filter_include'));resizeSelect(document.getElementById('src_filter_include'), document.getElementById('filter_include'), {select_size array=$data.pay_stub_entry_account_options})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_bottom_sm.gif"></a>
					</td>
					<td class="cellRightEditTable" colspan="100">
						<span id="filter_include_count">0</span> {t}Included Pay Stub Accounts Currently Selected, Click the arrow to modify.{/t}
					</td>
				</tr>
				</tbody>

				<tbody id="filter_exclude_on" style="display:none" >
				<tr>
					<td class="{isvalid object="cdf" label="exclude_pay_stub_entry_account" value="cellLeftEditTable"}" nowrap>
						<b>{t}Exclude Pay Stub Accounts:{/t}</b><a href="javascript:toggleRowObject('filter_exclude_on');toggleRowObject('filter_exclude_off');filterExcludeCount();"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_top_sm.gif"></a>
					</td>
					<td colspan="3">
						<table class="editTable">
						<tr class="tblHeader">
							<td colspan="3">
								{t}Pay Stub Account Value{/t}:
								<select name="data[exclude_account_amount_type_id]">
									{html_options options=$data.account_amount_type_options selected=$data.exclude_account_amount_type_id}
								</select>
							</td>
						</td>

						<tr class="tblHeader">
							<td>
								{t}Pay Stub Accounts{/t}
							</td>
							<td>
								<br>
							</td>
							<td>
								{t}Excluded Pay Stub Accounts{/t}
							</td>
						</tr>
						<tr>
							<td class="cellRightEditTable" width="49%" align="center">
								<input type="button" name="Select All" value="{t}Select All{/t}" onClick="selectAll(document.getElementById('src_filter_exclude'))">
								<input type="button" name="Un-Select" value="{t}Un-Select All{/t}" onClick="unselectAll(document.getElementById('src_filter_exclude'))">
								<br>
								<select name="src_exclude_id" id="src_filter_exclude" style="width:100%;margin:5px 0 5px 0;" size="{select_size array=$data.pay_stub_entry_account_options}" multiple>
									{html_options options=$data.exclude_pay_stub_entry_account_options}
								</select>
							</td>
							<td class="cellRightEditTable" style="vertical-align: middle;" width="1">
								<a href="javascript:moveItem(document.getElementById('src_filter_exclude'), document.getElementById('filter_exclude')); uniqueSelect(document.getElementById('filter_exclude')); sortSelect(document.getElementById('filter_exclude'));resizeSelect(document.getElementById('src_filter_exclude'), document.getElementById('filter_exclude'), {select_size array=$data.pay_stub_entry_account_options})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_last.gif"></a>
								<br>
								<a href="javascript:moveItem(document.getElementById('filter_exclude'), document.getElementById('src_filter_exclude')); uniqueSelect(document.getElementById('src_filter_exclude')); sortSelect(document.getElementById('src_filter_exclude'));resizeSelect(document.getElementById('src_filter_exclude'), document.getElementById('filter_exclude'), {select_size array=$data.pay_stub_entry_account_options})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_first.gif"></a>
							</td>
							<td class="cellRightEditTable" width="49%" align="center">
								<input type="button" name="Select All" value="{t}Select All{/t}" onClick="selectAll(document.getElementById('filter_exclude'))">
								<input type="button" name="Un-Select" value="{t}Un-Select All{/t}" onClick="unselectAll(document.getElementById('filter_exclude'))">
								<br>
								<select name="data[exclude_pay_stub_entry_account_ids][]" id="filter_exclude" style="width:100%;margin:5px 0 5px 0;" size="{select_size array=$data.pay_stub_entry_account_options}" multiple>
									{html_options options=$filter_exclude_options selected=$data.exclude_pay_stub_entry_account_ids}
								</select>
							</td>
						</tr>
						</table>
					</td>
				</tr>
				</tbody>
				<tbody id="filter_exclude_off">
				<tr>
					<td class="{isvalid object="cdf" label="user" value="cellLeftEditTable"}" nowrap>
						<b>{t}Exclude Pay Stub Accounts:{/t}</b><a href="javascript:toggleRowObject('filter_exclude_on');toggleRowObject('filter_exclude_off');uniqueSelect(document.getElementById('filter_exclude'), document.getElementById('src_filter_user')); sortSelect(document.getElementById('filter_exclude'));resizeSelect(document.getElementById('src_filter_exclude'), document.getElementById('filter_exclude'), {select_size array=$data.pay_stub_entry_account_options})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_bottom_sm.gif"></a>
					</td>
					<td class="cellRightEditTable" colspan="100">
						<span id="filter_exclude_count">0</span> {t}Excluded Pay Stub Accounts Currently Selected, Click the arrow to modify.{/t}
					</td>
				</tr>
				</tbody>

				<tbody id="filter_user_on" style="display:none" >
				<tr>
					<td class="{isvalid object="cdf" label="user" value="cellLeftEditTable"}" nowrap>
						<b>{t}Employees:{/t}</b><a href="javascript:toggleRowObject('filter_user_on');toggleRowObject('filter_user_off');filterUserCount();"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_top_sm.gif"></a>
					</td>
					<td colspan="3">
						<table class="editTable">
						<tr class="tblHeader">
							<td>
								{t}Unassigned Employees{/t}
							</td>
							<td>
								<br>
							</td>
							<td>
								{t}Assigned Employees{/t}
							</td>
						</tr>
						<tr>
							<td class="cellRightEditTable" width="49%" align="center">
								<input type="button" name="Select All" value="{t}Select All{/t}" onClick="selectAll(document.getElementById('src_filter_user'))">
								<input type="button" name="Un-Select" value="{t}Un-Select All{/t}" onClick="unselectAll(document.getElementById('src_filter_user'))">
								<br>
								<select name="src_user_id" id="src_filter_user" style="width:100%;margin:5px 0 5px 0;" size="{select_size array=$data.user_options}" multiple>
									{html_options options=$data.user_options}
								</select>
							</td>
							<td class="cellRightEditTable" style="vertical-align: middle;" width="1">
								<a href="javascript:moveItem(document.getElementById('src_filter_user'), document.getElementById('filter_user')); uniqueSelect(document.getElementById('filter_user')); sortSelect(document.getElementById('filter_user'));resizeSelect(document.getElementById('src_filter_user'), document.getElementById('filter_user'), {select_size array=$data.user_options})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_last.gif"></a>
								<br>
								<a href="javascript:moveItem(document.getElementById('filter_user'), document.getElementById('src_filter_user')); uniqueSelect(document.getElementById('src_filter_user')); sortSelect(document.getElementById('src_filter_user'));resizeSelect(document.getElementById('src_filter_user'), document.getElementById('filter_user'), {select_size array=$data.user_options})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_first.gif"></a>
								<br>
								<br>
								<br>
								<a href="javascript:UserSearch('src_filter_user','filter_user');"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_popup.gif"></a>
							</td>
							<td class="cellRightEditTable" width="49%" align="center">
								<input type="button" name="Select All" value="{t}Select All{/t}" onClick="selectAll(document.getElementById('filter_user'))">
								<input type="button" name="Un-Select" value="{t}Un-Select All{/t}" onClick="unselectAll(document.getElementById('filter_user'))">
								<br>
								<select name="data[user_ids][]" id="filter_user" style="width:100%;margin:5px 0 5px 0;" size="{select_size array=$data.user_options}" multiple>
									{html_options options=$filter_user_options selected=$data.user_ids}
								</select>
							</td>
						</tr>
						</table>
					</td>
				</tr>
				</tbody>
				<tbody id="filter_user_off">
				<tr>
					<td class="{isvalid object="cdf" label="user" value="cellLeftEditTable"}" nowrap>
						<b>{t}Employees:{/t}</b><a href="javascript:toggleRowObject('filter_user_on');toggleRowObject('filter_user_off');uniqueSelect(document.getElementById('filter_user'), document.getElementById('src_filter_user')); sortSelect(document.getElementById('filter_user'));resizeSelect(document.getElementById('src_filter_user'), document.getElementById('filter_user'), {select_size array=$data.user_options})"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_bottom_sm.gif"></a>
					</td>
					<td class="cellRightEditTable" colspan="100">
						<span id="filter_user_count">0</span> {t}Employees Currently Selected, Click the arrow to modify.{/t}
					</td>
				</tr>
				</tbody>

			</table>
		</div>

		<div id="contentBoxFour">
			<input type="submit" class="btnSubmit" name="action:submit" value="{t}Submit{/t}" onClick="selectAll(document.getElementById('filter_include'));selectAll(document.getElementById('filter_exclude'));selectAll(document.getElementById('filter_user'));">
		</div>

		<input type="hidden" id="id" name="data[id]" value="{$data.id}">
		</form>
	</div>
</div>
{include file="footer.tpl"}
