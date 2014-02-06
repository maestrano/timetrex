{include file="header.tpl"}
{*
<script	language=JavaScript>

{literal}
function editAccrual(userID) {
	try {
		eP=window.open('{/literal}{$BASE_URL}{literal}accrual/EditPunch.php?id='+ encodeURI(punchID) +'&punch_control_id='+ encodeURI(punchControlId) +'&user_id='+ encodeURI(userID) +'&date_stamp='+ encodeURI(date) +'&status_id='+ encodeURI(statusID),"Edit_Punch","toolbar=0,status=1,menubar=0,scrollbars=1,fullscreen=no,width=580,height=470,resizable=1");
	} catch (e) {
		//DN
	}
}
{/literal}
</script>
*}
<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">
		<table class="tblList">
		<form method="get" name="accrual_balance" action="{$smarty.server.SCRIPT_NAME}">
				<tr>
					<td class="tblPagingLeft" colspan="7" align="right">
						{include file="pager.tpl" pager_data=$paging_data}
					</td>
				</tr>
				{if $permission->Check('accrual','view') OR $permission->Check('accrual','view_child')}
				<tr class="tblHeader">
					<td colspan="8">
						{t}Employee:{/t}
						<a href="javascript:navSelectBox('filter_user', 'prev');document.accrual_balance.submit()"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_prev_sm.gif"></a>
						<select name="filter_user_id" id="filter_user" onChange="this.form.submit()">
							{html_options options=$user_options selected=$filter_user_id}
						</select>
						<a href="javascript:navSelectBox('filter_user', 'next');document.accrual_balance.submit()"><img style="vertical-align: middle" src="{$IMAGES_URL}/nav_next_sm.gif"></a>
					</td>
				</tr>
				{/if}
				<tr class="tblHeader">
					<td>
						#
					</td>				
					<td>
						{capture assign=label}{t}Name{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="c.name" current_column="$sort_column" current_order="$sort_order"}
					</td>
					<td>
						{capture assign=label}{t}Balance{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="a.balance" current_column="$sort_column" current_order="$sort_order"}
					</td>
					<td>
						{t}Functions{/t}
					</td>
				</tr>
				{foreach from=$accruals item=accrual name=accrual}
					{cycle assign=row_class values="tblDataWhite,tblDataGrey"}
					{if $accrual.deleted == TRUE}
						{assign var="row_class" value="tblDataDeleted"}
					{/if}
					<tr class="{$row_class}">
						<td>
							{$smarty.foreach.accrual.iteration}
						</td>										
						<td>
							{$accrual.accrual_policy}
						</td>
						<td>
							{gettimeunit value=$accrual.balance default=TRUE}
						</td>
						<td>
							{assign var="accrual_policy_id" value=$accrual.accrual_policy_id}
							{assign var="user_id" value=$accrual.user_id}

							{if ( $permission->Check('accrual','view') OR ($permission->Check('accrual','view_child') AND $is_child === TRUE) OR ($permission->Check('accrual','view_own') AND $is_owner === TRUE ))}
								[ <a href="{urlbuilder script="ViewUserAccrualList.php" values="user_id=$user_id,accrual_policy_id=$accrual_policy_id" merge="FALSE"}">{t}View{/t}</a> ]
							{/if}
						</td>
					</tr>
				{/foreach}
				{if $permission->Check('accrual','add')}
					<tr>
						<td class="tblActionRow" colspan="7">
							<input type="submit" class="button" name="action:add" value="{t}Add{/t}">
						</td>
					</tr>
				{/if}
				<tr>
					<td class="tblPagingLeft" colspan="7" align="right">
						{include file="pager.tpl" pager_data=$paging_data}
					</td>
				</tr>
			<input type="hidden" name="sort_column" value="{$sort_column}">
			<input type="hidden" name="sort_order" value="{$sort_order}">
			<input type="hidden" name="page" value="{$paging_data.current_page}">
			<input type="hidden" name="user_id" value="{$user_id}">
			</table>
		</form>
	</div>
</div>
{include file="footer.tpl"}
