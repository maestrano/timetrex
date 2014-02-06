{include file="header.tpl" body_onload="TIMETREX.searchForm.onLoadShowTab();"}
<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">
		<table class="tblList">
			<tr>
				<td class="tblPagingLeft" colspan="{$total_columns}" align="right">
					{include file="pager.tpl" pager_data=$paging_data}
				</td>
			</tr>

			{if $permission->Check('pay_stub','view') OR $permission->Check('pay_stub','view_child')}
			<form method="get" name="search_form" action="{$smarty.server.SCRIPT_NAME}">
				{include file="list_tabs.tpl" section="header"}
				<tr id="adv_search" class="tblSearch" style="display: none;">
					<td colspan="{$total_columns}" class="tblSearchMainRow">
						<table id="content_adv_search" class="editTable" bgcolor="#7a9bbd">
							<tr>
								<td valign="top" width="50%">
									<table class="editTable">
										<tr id="tab_row_all" >
											<td class="cellLeftEditTable">
												{t}Employee Status:{/t}
											</td>
											<td class="cellRightEditTable">
												<select id="filter_data_status_id" name="filter_data[status_id]">
													{html_options options=$filter_data.status_options selected=$filter_data.status_id}
												</select>
											</td>
										</tr>
										<tr id="tab_row_all">
											<td class="cellLeftEditTable">
												{t}Pay Period:{/t}
											</td>
											<td class="cellRightEditTable">
												<select name="filter_data[pay_period_id]">
													{html_options options=$filter_data.pay_period_options selected=$filter_data.pay_period_id}
												</select>
											</td>
										</tr>
										<tr id="tab_row_all">
											<td class="cellLeftEditTable">
												{t}Employee:{/t}
											</td>
											<td class="cellRightEditTable">
												<select name="filter_data[user_id]">
													{html_options options=$filter_data.user_options selected=$filter_data.user_id}
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
									</table>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				{include file="list_tabs.tpl" section="saved_search"}
				{include file="list_tabs.tpl" section="global"}
			</form>
			{/if}

			<form method="post" name="pay_stubs" action="{$smarty.server.SCRIPT_NAME}">
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
						{t}Functions{/t}
					</td>
					<td>
						<input type="checkbox" class="checkbox" name="select_all" onClick="{if $permission->Check('pay_stub','view')} var hide_employer = document.getElementById('hide_employer').checked;{/if} CheckAll(this);document.getElementById('hide_employer').checked = hide_employer"/>
					</td>
				</tr>
				{foreach from=$pay_stubs item=pay_stub name=pay_stub}
					{cycle assign=row_class values="tblDataWhite,tblDataGrey"}
					{if $pay_stub.deleted == TRUE}
						{assign var="row_class" value="tblDataDeleted"}
					{/if}
					<tr class="{$row_class}">
						<td>
							{$smarty.foreach.pay_stub.iteration}
						</td>

						{foreach from=$columns key=key item=column name=column}
							<td>
								{$pay_stub[$key]|default:"--"}
							</td>
						{/foreach}

						<td>
							{assign var="pay_stub_id" value=$pay_stub.id}

							{if $permission->Check('pay_stub','view') OR ( $permission->Check('pay_stub','view_child') AND $pay_stub.is_child === TRUE ) OR ( $permission->Check('pay_stub','view_own') AND $pay_stub.is_owner === TRUE )}
								[ <a href="{urlbuilder script="PayStubList.php" values="action:view,id=$pay_stub_id" merge="FALSE"}">{t}View{/t}</a> ]
							{/if}
							{if ( $pay_stub.status_id == 10 OR $pay_stub.status_id == 25) AND ( $permission->Check('pay_stub','edit') OR ( $permission->Check('pay_stub','edit_child') AND $pay_stub.is_child === TRUE ) OR ( $permission->Check('pay_stub','edit_own') AND $pay_stub.is_owner === TRUE ) )}
								[ <a href="{urlbuilder script="EditPayStub.php" values="id=$pay_stub_id,filter_pay_period_id=$filter_pay_period_id" merge="FALSE"}">{t}Edit{/t}</a> ]
							{/if}
						</td>
						<td>
							<input type="checkbox" class="checkbox" name="ids[]" value="{$pay_stub.id}">
						</td>
					</tr>
				{/foreach}
				<tr>
					<td class="tblActionRow" colspan="{$total_columns}">
						{if $permission->Check('pay_stub','view') OR $permission->Check('pay_stub','view_own') OR $permission->Check('pay_stub','view_child')}
							{if $permission->Check('pay_stub','view') OR $permission->Check('pay_stub','view_child')}
								{t}Hide Employer Contributions:{/t}
								<input type="checkbox" id='hide_employer' class="checkbox" name="hide_employer_rows" value="1">
							{/if}
							<input type="submit" name="action:view" value="{t}View{/t}">
						{/if}

						{if $permission->Check('pay_stub','edit') OR $permission->Check('pay_stub','edit_child')}
							<input type="submit" name="action:Mark_Paid" value="{t}Mark Paid{/t}">
							<input type="submit" name="action:Mark_UnPaid" value="{t}Mark UnPaid{/t}">
						{/if}
						{if $permission->Check('pay_stub','delete') OR $permission->Check('pay_stub','delete_child')}
							<input type="submit" name="action:delete" value="{t}Delete{/t}" onClick="return confirmSubmit()">
						{/if}
						{if $permission->Check('pay_stub','undelete')}
							<input type="submit" name="action:undelete" value="{t}UnDelete{/t}">
						{/if}
					</td>
				</tr>
				{if $permission->Check('pay_stub','view') OR $permission->Check('pay_stub','view')}
				<tr>
					<td class="tblActionRow" colspan="{$total_columns}">
						<div align="left">
						<select name="export_type">
							{html_options options=$export_type_options}
						</select>
						<input type="submit" name="action:export" value="{t}Export{/t}">
						</div>
					</td>
				</tr>
				{/if}
				<tr>
					<td class="tblPagingLeft" colspan="{$total_columns}" align="right">
						{include file="pager.tpl" pager_data=$paging_data}
					</td>
				</tr>
			<input type="hidden" name="sort_column" value="{$sort_column}">
			<input type="hidden" name="sort_order" value="{$sort_order}">
			<input type="hidden" name="page" value="{$paging_data.current_page}">
			</table>
		</form>
	</div>
</div>
{include file="footer.tpl"}
