{include file="header.tpl"}
<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">
		<table class="tblList">
		<form method="get" action="{$smarty.server.SCRIPT_NAME}">
				<tr>
					<td class="tblPagingLeft" colspan="7" align="right">
						{include file="pager.tpl" pager_data=$paging_data}
					</td>
				</tr>

				{include file="no_policy_group_notice.tpl" show_no_policy_group_notice=$show_no_policy_group_notice}

				<tr class="tblHeader">
					<td>
						{t}#{/t}
					</td>
					<td>
						{capture assign=label}{t}Name{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="name" current_column="$sort_column" current_order="$sort_order"}
					</td>
					<td>
						{capture assign=label}{t}Punch Type{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="punch_type_id" current_column="$sort_column" current_order="$sort_order"}
					</td>
					<td>
						{capture assign=label}{t}Interval{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="interval" current_column="$sort_column" current_order="$sort_order"}
					</td>
					<td>
						{t}Functions{/t}
					</td>
					<td>
						<input type="checkbox" class="checkbox" name="select_all" onClick="CheckAll(this)"/>
					</td>
				</tr>
				{foreach from=$policies name=policies item=policy}
					{cycle assign=row_class values="tblDataWhite,tblDataGrey"}
					{if $policy.deleted == TRUE}
						{assign var="row_class" value="tblDataDeleted"}
					{elseif $policy.assigned_policy_groups == 0}
						{assign var="row_class" value="tblDataWarning"}
					{/if}
					<tr class="{$row_class}">
						<td>
							{$smarty.foreach.policies.iteration}
						</td>
						<td>
							{$policy.name}
						</td>
						<td>
							{$policy.punch_type}
						</td>
						<td>
							{gettimeunit value=$policy.interval}
						</td>
						<td>
							{assign var="policy_id" value=$policy.id}
							{if $permission->Check('round_policy','edit')}
								[ <a href="{urlbuilder script="EditRoundIntervalPolicy.php" values="id=$policy_id" merge="FALSE"}">{t}Edit{/t}</a> ]
							{/if}
						</td>
						<td>
							<input type="checkbox" class="checkbox" name="ids[]" value="{$policy.id}">
						</td>
					</tr>
				{/foreach}
				<tr>
					<td class="tblActionRow" colspan="7">
						{if $permission->Check('round_policy','add')}
							<input type="submit" class="button" name="action:add" value="{t}Add{/t}">
						{/if}
						{if $permission->Check('round_policy','delete')}
						 <input type="submit" class="button" name="action:delete" value="{t}Delete{/t}" onClick="return confirmSubmit()">
						{/if}
						{if $permission->Check('round_policy','undelete')}
							<input type="submit" class="button" name="action:undelete" value="{t}UnDelete{/t}">
						{/if}
					</td>
				</tr>
				<tr>
					<td class="tblPagingLeft" colspan="7" align="right">
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
