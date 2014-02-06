{include file="header.tpl" enable_ajax=TRUE body_onload="TIMETREX.searchForm.onLoadShowTab(); showProvince();"}

<script	language=JavaScript>
{literal}
var loading = false;
var hwCallback = {
		getProvinceOptions: function(result) {
			if ( result != false ) {
				province_obj = document.getElementById('province');
				selected_province = document.getElementById('selected_province').value;

				populateSelectBox( province_obj, result, selected_province);
			}
			loading = false;
		}
	}

var remoteHW = new AJAX_Server(hwCallback);

function showProvince() {
	if ( document.getElementById('selected_tab').value != '' ) {
		country = document.getElementById('country').value;
		remoteHW.getProvinceOptions( country );
	}
}
{/literal}
</script>

{if isset($notice_data.message) AND $notice_data.message != '' AND ( DEPLOYMENT_ON_DEMAND == FALSE OR ( DEPLOYMENT_ON_DEMAND == TRUE AND isset($config_vars.other.primary_company_id) AND $config_vars.other.primary_company_id == $current_company->getId() ) )}
	<div id="rowError" align="center">{t}WARNING{/t}: {$notice_data.message}.</div>
{/if}

<div id="rowContent">
	<div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">
		<table class="tblList" id="user_tbl">
			<form method="get" name="search_form" action="{$smarty.server.SCRIPT_NAME}">
				<tr>
					<td class="tblPagingLeft" colspan="{$total_columns}" align="right">
						{include file="pager.tpl" pager_data=$paging_data}
					</td>
				</tr>

				{include file="list_tabs.tpl" section="header" tab_onclick="showProvince();"}
				<tr id="adv_search" class="tblSearch" style="display: none">
					<td colspan="{$total_columns}" class="tblSearchMainRow">
						<table id="content_adv_search" class="editTable" bgcolor="#7a9bbd">
							<tr>
								<td valign="top" width="50%">
									<table class="editTable">
										{if $permission->Check('company','view')}
										<tr id="tab_row_all" >
											<td class="cellLeftEditTable">
												{t}Company:{/t}
											</td>
											<td class="cellRightEditTable">
												<select id="filter_data_company_id" name="filter_data[company_id]">
													{html_options options=$filter_data.company_options selected=$filter_data.company_id}
												</select>
											</td>
										</tr>
										{/if}
										<tr id="tab_row_all" >
											<td class="cellLeftEditTable">
												{t}Status:{/t}
											</td>
											<td class="cellRightEditTable">
												<select id="filter_data_status_id" name="filter_data[status_id]">
													{html_options options=$filter_data.status_options selected=$filter_data.status_id}
												</select>
											</td>
										</tr>
										<tr id="tab_row_all">
											<td class="cellLeftEditTable">
												{t}First Name:{/t}
											</td>
											<td class="cellRightEditTable">
												<input type="text" name="filter_data[first_name]" value="{$filter_data.first_name}">
											</td>
										</tr>
										<tr id="tab_row_all">
											<td class="cellLeftEditTable">
												{t}Last Name:{/t}
											</td>
											<td class="cellRightEditTable">
												<input type="text" name="filter_data[last_name]" value="{$filter_data.last_name}">
											</td>
										</tr>
										<tr id="tab_row_adv_search">
											<td class="cellLeftEditTable">
												{t}Home Phone:{/t}
											</td>
											<td class="cellRightEditTable">
												<input type="text" name="filter_data[home_phone]" value="{$filter_data.home_phone}">
											</td>
										</tr>
										<tr id="tab_row_adv_search">
											<td class="cellLeftEditTable">
												Employee Number:
											</td>
											<td class="cellRightEditTable">
												<input type="text" name="filter_data[employee_number]" value="{$filter_data.employee_number}">
											</td>
										</tr>
										<tr id="tab_row_adv_search">
											<td class="cellLeftEditTable">
												{t}SIN / SSN:{/t}
											</td>
											<td class="cellRightEditTable">
												<input type="text" name="filter_data[sin]" value="{$filter_data.sin}">
											</td>
										</tr>
										<tr id="tab_row_adv_search">
											<td class="cellLeftEditTable">
												{t}Sex:{/t}
											</td>
											<td class="cellRightEditTable">
												<select name="filter_data[sex_id]">
													{html_options options=$filter_data.sex_options selected=$filter_data.sex_id}
												</select>
											</td>
										</tr>

									</table>
								</td>
								<td valign="top" width="50%">
									<table class="editTable">
										<tr id="tab_row_all">
											<td class="cellLeftEditTable">
												{t}Group:{/t}
											</td>
											<td class="cellRightEditTable">
												<select name="filter_data[group_id]">
													{html_options options=$filter_data.group_options selected=$filter_data.group_id}
												</select>
											</td>
										</tr>
										<tr id="tab_row_all">
											<td class="cellLeftEditTable">
												{t}Default Branch:{/t}
											</td>
											<td class="cellRightEditTable">
												<select name="filter_data[default_branch_id]">
													{html_options options=$filter_data.branch_options selected=$filter_data.default_branch_id}
												</select>
											</td>
										</tr>
										<tr id="tab_row_all">
											<td class="cellLeftEditTable">
												{t}Default Department:{/t}
											</td>
											<td class="cellRightEditTable">
												<select name="filter_data[default_department_id]">
													{html_options options=$filter_data.department_options selected=$filter_data.default_department_id}
												</select>
											</td>
										</tr>
										<tr id="tab_row_adv_search">
											<td class="cellLeftEditTable">
												{t}Title:{/t}
											</td>
											<td class="cellRightEditTable">
												<select name="filter_data[title_id]">
													{html_options options=$filter_data.title_options selected=$filter_data.title_id}
												</select>
											</td>
										</tr>
										<tr id="tab_row_adv_search">
											<td class="cellLeftEditTable">
												{t}Country:{/t}
											</td>
											<td class="cellRightEditTable">
												<select id="country" name="filter_data[country]" onChange="showProvince()">
													{html_options options=$filter_data.country_options selected=$filter_data.country}
												</select>
											</td>
										</tr>
										<tr id="tab_row_adv_search">
											<td class="cellLeftEditTable">
												{t}Province / State:{/t}
											</td>
											<td class="cellRightEditTable">
												<select id="province" name="filter_data[province]" onChange="document.getElementById('selected_province').value = this.value">
												</select>
												<input type="hidden" id="selected_province" value="{$filter_data.province}">
											</td>
										</tr>
										<tr id="tab_row_adv_search">
											<td class="cellLeftEditTable">
												{t}City:{/t}
											</td>
											<td class="cellRightEditTable">
												<input type="text" name="filter_data[city]" value="{$filter_data.city}">
											</td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				{include file="list_tabs.tpl" section="saved_search"}
				{include file="list_tabs.tpl" section="global"}
			</form>
			<form method="get" name="listForm" action="{$smarty.server.SCRIPT_NAME}">
				<tr class="tblHeader">
					<td>
						{t}#{/t}
					</td>
					{foreach from=$columns key=column_id item=column name=column}
						<td>
							{include file="column_sort.tpl" label=$column sort_column=$column_id current_column="$sort_column" current_order="$sort_order"}
						</td>
					{/foreach}
					<td>
						{t}Functions{/t}<br>
						{if $permission->Check('wage','view')
							OR $permission->Check('user_tax','edit')
							OR $permission->Check('pay_stub_amendment','view')
							OR $permission->Check('roe','view')}
						[ <a href="javascript:toggleColumnObject('user_tbl', 'user_funcs')">{t}Employee{/t}</a> ]
						[ <a href="javascript:toggleColumnObject('user_tbl', 'payroll_funcs')">{t}Payroll{/t}</a> ]{/if}
					</td>
					<td>
						<input type="checkbox" class="checkbox" name="select_all" onClick="CheckAll(this)"/>
					</td>
				</tr>
				{foreach from=$users item=user name=user}
					{cycle assign=row_class values="tblDataWhite,tblDataGrey"}
					{if $user.deleted == TRUE OR $user.status_id > 10}
						{assign var="row_class" value="tblDataDeleted"}
					{/if}
					<tr class="{$row_class}">
						<td>
							{$smarty.foreach.user.iteration}
						</td>

						{foreach from=$columns key=key item=column name=column}
							<td>
								{$user[$key]|default:"--"}
							</td>
						{/foreach}

						<td id="col_user_funcs">
							{assign var="user_id" value=$user.id}
							{assign var="company_id" value=$user.company_id}

							{if isset($config_vars.other.primary_company_id) AND $config_vars.other.primary_company_id != $user.company_id
								AND $permission->Check('company','view') AND $permission->Check('company','login_other_user')}
								[ <a href="{urlbuilder script="EditUser.php" values="action:login=1,id=$user_id,company_id=$company_id,saved_search_id=$saved_search_id" merge="FALSE"}">{t}Login{/t}</a> ]
							{/if}

							{if $permission->Check('user','edit') OR ( $permission->Check('user','edit_child') AND $user.is_child === TRUE ) OR ( $permission->Check('user','edit_own') AND $user.is_owner === TRUE ) }
								[ <a href="{urlbuilder script="EditUser.php" values="id=$user_id,company_id=$company_id,saved_search_id=$saved_search_id" merge="FALSE"}">{t}Edit{/t}</a> ]
							{/if}

							{if $permission->Check('user_preference','edit') OR ( $permission->Check('user_preference','edit_child') AND $user.is_child === TRUE ) OR ( $permission->Check('user_preference','edit_own') AND $user.is_owner === TRUE )}
								[ <a href="{urlbuilder script="EditUserPreference.php" values="user_id=$user_id" merge="FALSE"}">{t}Prefs{/t}</a> ]
							{/if}
							{if $user.map_url != ''}
							  [ <a href="{$user.map_url}" target="_blank">{t}Map{/t}</a> ]
							{/if}
						</td>
						<td id="col_payroll_funcs" style="display:none">
							<nobr>

							{if $permission->Check('wage','view') OR ( $permission->Check('wage','view_child') AND $user.is_child === TRUE ) OR ( $permission->Check('wage','view_own') AND $user.is_owner === TRUE )}
								[ <a href="{urlbuilder script="UserWageList.php" values="user_id=$user_id,saved_search_id=$saved_search_id" merge="FALSE"}">{t}Wage{/t}</a> ]
							{/if}

							{if $permission->Check('user_tax_deduction','view') OR ( $permission->Check('user_tax_deduction','view_child') AND $user.is_child === TRUE ) OR ( $permission->Check('user_tax_deduction','view_own') AND $user.is_owner === TRUE )}
								[ <a href="{urlbuilder script="UserDeductionList.php" values="user_id=$user_id,saved_search_id=$saved_search_id" merge="FALSE"}">{t}Tax{/t}</a> ]
							{/if}


							{if $permission->Check('pay_stub_amendment','view') OR ( $permission->Check('pay_stub_amendment','view_child') AND $user.is_child === TRUE ) OR ( $permission->Check('pay_stub_amendment','view_own') AND $user.is_owner === TRUE )}
								[ <a href="{urlbuilder script="../pay_stub_amendment/PayStubAmendmentList.php" values="filter_user_id=$user_id" merge="FALSE"}">{t}PS Amendment{/t}</a> ]
							{/if}

							{if $user.country == 'CA' AND ( $permission->Check('roe','view') OR ( $permission->Check('roe','view_child') AND $user.is_child === TRUE ) OR ( $permission->Check('roe','view_own') AND $user.is_owner === TRUE ) )}
								[ <a href="{urlbuilder script="../roe/ROEList.php" values="user_id=$user_id" merge="FALSE"}">{t}ROE{/t}</a> ]
							{/if}

							{if $permission->Check('user','edit_bank') OR ( $permission->Check('user','edit_child_bank') AND $user.is_child === TRUE ) OR ( $permission->Check('user','edit_own_bank') AND $user.is_owner === TRUE )}
								[ <a href="{urlbuilder script="../bank_account/EditBankAccount.php" values="user_id=$user_id" merge="FALSE"}">{t}Bank{/t}</a> ]
							{/if}
							</nobr>
						</td>
						<td>
							<input type="checkbox" class="checkbox" name="ids[]" value="{$user.id}">
						</td>
					</tr>
				{/foreach}
				<tr>
					<td class="tblActionRow" colspan="10">
						{if $permission->Check('user','add')}
							<input type="submit" name="action:add" value="{t}Add{/t}">
						{/if}
						{if $permission->Check('user','delete') OR $permission->Check('user','delete_own') OR $permission->Check('user','delete_child')}
							<input type="submit" name="action:delete" value="{t}Delete{/t}" onClick="return confirmSubmit()">
						{/if}
						{if $permission->Check('user','undelete')}
							<input type="submit" name="action:undelete" value="{t}UnDelete{/t}">
						{/if}
					</td>
				</tr>
				<tr>
					<td class="tblPagingLeft" colspan="10" align="right">
						{include file="pager.tpl" pager_data=$paging_data}
					</td>
				</tr>
			<input type="hidden" name="sort_column" value="{$sort_column}">
			<input type="hidden" name="sort_order" value="{$sort_order}">
			<input type="hidden" name="saved_search_id" value="{$saved_search_id}">
			<input type="hidden" name="page" value="{$paging_data.current_page}">
			<input type="hidden" name="company_id" value="{$filter_data.company_id}">
			</table>
		</form>
	</div>
</div>
{include file="footer.tpl"}
