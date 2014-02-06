{include file="header.tpl"}
<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">
		<table class="tblList">

		<form method="get" action="{$smarty.server.SCRIPT_NAME}">
				<tr>
					<td class="tblPagingLeft" colspan="7" align="right">
						<Br>
					</td>
				</tr>
				<tr class="tblHeader">
					<td>
						{t}Level{/t}
					</td>
					<td>
						{t}Name{/t}
					</td>
				</tr>
				{foreach name=parent_groups from=$parent_groups item=parent_group}
					{cycle assign=row_class values="tblDataWhite,tblDataGrey"}
					<tr class="{$row_class}">
						<td>

							{if $smarty.foreach.parent_groups.last}
								<b>{t}FINAL{/t}</b>
							{else}
								{$parent_group.level}
							{/if}
						</td>
						<td>
							{foreach name=users from=$parent_group.users item=user}
								{$user.name}<br>
							{/foreach}
						</td>
					</tr>
				{foreachelse}
					<tr class="tblDataWhite">
						<td colspan="2">
							{t}This employee is at the final authorization level.{/t}
						</td>
					</tr>
				{/foreach}
				<tr>
					<td class="tblPagingLeft" colspan="7" align="right">
						<Br>
					</td>
				</tr>

			</table>
		</form>
	</div>
</div>
{include file="footer.tpl"}
