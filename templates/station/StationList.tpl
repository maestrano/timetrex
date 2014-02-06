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
				<tr class="tblHeader">
					<td>
						{capture assign=label}{t}Type{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="type_id" current_column="$sort_column" current_order="$sort_order"}
					</td>
					<td>
						{capture assign=label}{t}Station ID{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="station_id" current_column="$sort_column" current_order="$sort_order"}
					</td>
					<td>
						{capture assign=label}{t}Source{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="source" current_column="$sort_column" current_order="$sort_order"}
					</td>
					<td>
						{capture assign=label}{t}Description{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="source" current_column="$sort_column" current_order="$sort_order"}
					</td>
					<td>
						{capture assign=label}{t}Status{/t}{/capture}
						{include file="column_sort.tpl" label=$label sort_column="status_id" current_column="$sort_column" current_order="$sort_order"}
					</td>
					<td>
						{t}Functions{/t}
					</td>
					<td>
						<input type="checkbox" class="checkbox" name="select_all" onClick="CheckAll(this)"/>
					</td>
				</tr>
				{foreach from=$stations item=station}
					{cycle assign=row_class values="tblDataWhite,tblDataGrey"}
					{if $station.deleted == TRUE}
						{assign var="row_class" value="tblDataDeleted"}
					{/if}
					<tr class="{if is_object($current_station) AND $current_station->getStation() == $station.station}tblDataHighLight{else}{$row_class}{/if}">
						<td>
							{$station.type}
						</td>
						<td>
							{$station.short_station}
						</td>
						<td>
							{$station.source}
						</td>
						<td>
							{$station.description}
						</td>
						<td>
							{$station.status}
						</td>
						<td>
							{assign var="station_id" value=$station.id}
							{*
							{if $permission->Check('station','view')}
								[ View ]
							{/if}
							*}
							<nobr>
							{if $permission->Check('station','view')}
								[ <a href="{urlbuilder script="EditStation.php" values="id=$station_id" merge="FALSE"}">{t}Edit{/t}</a> ]
							{/if}
							{*
							{if $permission->Check('station','assign')}
								[ <a href="{urlbuilder script="EditStationUser.php" values="id=$station_id" merge="FALSE"}">{t}Employees{/t}</a> ]
							{/if}
							*}
							</nobr>
						</td>
						<td>
							<input type="checkbox" class="checkbox" name="ids[]" value="{$station.id}">
						</td>
					</tr>
				{/foreach}
				<tr>
					<td class="tblActionRow" colspan="7">
						{if $permission->Check('station','add')}
							<input type="submit" name="action:add" value="{t}Add{/t}">
						{/if}
						{if $permission->Check('station','delete')}
							<input type="submit" name="action:delete" value="{t}Delete{/t}" onClick="return confirmSubmit()">
						{/if}
						{if $permission->Check('station','undelete')}
							<input type="submit" name="action:undelete" value="{t}UnDelete{/t}">
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
