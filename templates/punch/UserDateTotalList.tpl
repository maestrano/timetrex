{include file="sm_header.tpl" enable_calendar=true body_onload="resizeWindowToFit( document.getElementById('body') );"}

<script	language=JavaScript>
{literal}
function editHour(userDateTotalID, userDateID) {
	try {
		eH=window.open('{/literal}{$BASE_URL}{literal}punch/EditUserDateTotal.php?id='+ encodeURI(userDateTotalID) +'&user_date_id='+ encodeURI(userDateID),"Edit_Hour","toolbar=0,status=1,menubar=0,scrollbars=1,fullscreen=no,width=580,height=470,resizable=1");
		if (window.focus) {
			eH.focus()
		}
	} catch (e) {
		//DN
	}
}

function changeDate(date) {
	document.getElementById("filter_date").value = date;
	document.hour_list.submit();
}

function confirmDelete() {
	confirm_result = confirmSubmit();

	if ( confirm_result == true ) {
		document.getElementById('action').value = 'delete'
		document.hour_list.submit();
	}

	return confirm_result;
}

{/literal}
</script>

<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">
		<table class="tblList">
		<form method="get" name="hour_list" action="{$smarty.server.SCRIPT_NAME}">
				<tr>
					<td class="tblPagingLeft" colspan="100" align="right">
						{include file="pager.tpl" pager_rows=$paging_rows}
					</td>
				</tr>
				<tr class="tblHeader">
					<td colspan="100">
						{if $permission->Check('punch','view')}
							{t}Employee:{/t}
							<a href="javascript:navSelectBox('filter_user', 'prev');document.hour_list.submit()"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_prev_sm.gif"></a>
							<select name="filter_user_id" id="filter_user" onChange="this.form.submit()">
								{html_options options=$user_options selected=$filter_user_id}
							</select>
							<a href="javascript:navSelectBox('filter_user', 'next');document.hour_list.submit()"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_next_sm.gif"></a>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						{/if}

						{t}Date:{/t}
						<a href="{urlbuilder script="" values="prev_week=1" merge="TRUE"}"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_first_sm.gif"></a>
						<a href="{urlbuilder script="" values="prev_day=1" merge="TRUE"}"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_prev_sm.gif"></a>

						<input type="text" size="15" id="filter_date" name="filter_date" value="{getdate type="DATE" epoch=$filter_date}" onChange="this.form.submit()">
						<img src="{$BASE_URL}/images/cal.gif" id="cal_filter_date" width="16" height="16" border="0" alt="Pick a date" onMouseOver="calendar_setup('filter_date', 'cal_filter_date', false);">
						<a href="{urlbuilder script="" values="next_day=1" merge="TRUE"}" ><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_next_sm.gif"></a>
						<a href="{urlbuilder script="" values="next_week=1" merge="TRUE"}" ><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_last_sm.gif"></a>

					</td>
				</tr>

				<tr class="tblHeader">
					<td>
						{capture assign=label}{t}Time{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="total_time" current_column="$sort_column" current_order="$sort_order"}
					</td>
					<td>
						{capture assign=label}{t}Status{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="status_id" current_column="$sort_column" current_order="$sort_order"}
					</td>
					{if $filter_system_time == 1}
					<td>
						{capture assign=label}{t}Type{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="type_id" current_column="$sort_column" current_order="$sort_order"}
					</td>
					{/if}
					<td>
						{capture assign=label}{t}Policy{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="" current_column="$sort_column" current_order="$sort_order"}
					</td>
					<td>
						{capture assign=label}{t}Branch{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="branch_id" current_column="$sort_column" current_order="$sort_order"}
					</td>
					<td>
						{capture assign=label}{t}Department{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="department_id" current_column="$sort_column" current_order="$sort_order"}
					</td>
					{if $permission->Check('job','enabled') }
					<td>
						{capture assign=label}{t}Job{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="job_id" current_column="$sort_column" current_order="$sort_order"}
					</td>
					<td>
						{capture assign=label}{t}Task{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="job_item_id" current_column="$sort_column" current_order="$sort_order"}
					</td>
					<td>
						{capture assign=label}{t}Qty{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="quantity" current_column="$sort_column" current_order="$sort_order"}
					</td>
					{/if}
					<td>
						{capture assign=label}{t}O/R{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="override" current_column="$sort_column" current_order="$sort_order"}
					</td>
					<td>
						{t}Functions{/t}
					</td>
					<td>
						<input type="checkbox" class="checkbox" name="select_all" onClick="var system_time = document.getElementById('system_time').checked; CheckAll(this);document.getElementById('system_time').checked = system_time"/>
					</td>
				</tr>
				{foreach from=$rows item=row}
					{cycle assign=row_class values="tblDataWhite,tblDataGrey"}
					{if $row.deleted == TRUE}
						{assign var="row_class" value="tblrowsDeleted"}
					{/if}
					<tr class="{$row_class}">
						<td>
							{gettimeunit value=$row.total_time}
						</td>
						<td>
							{$row.status}
						</td>
						{if $filter_system_time == 1}
						<td>
							{$row.type}
						</td>
						{/if}
						<td>
							{if ($row.absence_policy_id != '')}
								{$row.absence_policy}
							{elseif ($row.over_time_policy_id != '')}
								{$row.over_time_policy}
							{elseif ($row.premium_policy_id != '')}
								{$row.premium_policy}
							{else}
								--
							{/if}
						</td>
						<td>
							{$row.branch}
						</td>
						<td>
							{$row.department}
						</td>
						{if $permission->Check('job','enabled') }
						<td>
							{$row.job}
						</td>
						<td>
							{$row.job_item}
						</td>
						<td>
							{$row.quantity} / {$row.bad_quantity}
						</td>
						{/if}
						<td>
							{if $row.override == TRUE}
								{t}Yes{/t}
							{else}
								{t}No{/t}
							{/if}
						</td>
						<td>
							{assign var="id" value=$row.id}
							{if $permission->Check('punch','edit')}
								[ <a href="javascript:editHour({$id})">{t}Edit{/t}</a> ]
							{/if}
						</td>
						<td>
							<input type="checkbox" class="checkbox" name="ids[]" value="{$row.id}">
						</td>
					</tr>
				{/foreach}
				<tr>
					<td class="tblActionRow" colspan="100">

						{t}Show System Time:{/t} <input type="checkbox" class="checkbox" id="system_time" name="filter_system_time" value="1" onClick="this.form.submit()" {if $filter_system_time == 1}checked{/if}>

						{if $permission->Check('punch','add')}
							<input type="BUTTON" class="button" name="action" value="{t}Add{/t}" onClick="javascript:editHour('','{$user_date_id}')">
						{/if}
						{if $permission->Check('punch','delete')}
							<input type="BUTTON" class="button" name="action" value="{t}Delete{/t}" onClick="return confirmDelete();">
						{/if}
					</td>
				</tr>

				<tr>
					<td colspan="100" align="right">
						<table width="35%">
							<tr class="tblHeader">
								<td colspan="2">
									{t}Totals{/t}
								</td>
							</tr>

							{cycle assign=row_class values="tblDataWhiteNH,tblDataGreyNH"}
							<tr class="{$row_class}" nowrap>
								<td>
									{t}Worked Time{/t}
								</td>
								<td>
									{gettimeunit value=$day_total_time.worked_time default=TRUE}
								</td>
							</tr>
							{cycle assign=row_class values="tblDataWhiteNH,tblDataGreyNH"}
							<tr class="{$row_class}" nowrap>
								<td>
									{t}Total Time{/t}
								</td>
								<td>
									{gettimeunit value=$day_total_time.total_time default=TRUE}
								</td>
							</tr>
							{cycle assign=row_class values="tblDataWhiteNH,tblDataGreyNH"}
							<tr class="{$row_class}" style="font-weight: bold;" nowrap>
								<td>
									{t}Difference{/t}
								</td>
								<td width="75">
									{gettimeunit value=$day_total_time.difference default=TRUE}
								</td>
							</tr>
						</table>

					</td>
				</tr>

				<tr>
					<td class="tblPagingLeft" colspan="100" align="right">
						{include file="pager.tpl" pager_rows=$paging_rows}
					</td>
				</tr>
			<input type="hidden" name="sort_column" value="{$sort_column}">
			<input type="hidden" name="sort_order" value="{$sort_order}">
			<input type="hidden" name="page" value="{$paging_rows.current_page}">
			<input type="hidden" name="action" id="action" value="">
			</table>
		</form>
	</div>
</div>
{include file="sm_footer.tpl"}
