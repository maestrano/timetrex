{include file="sm_header.tpl"}
{include file="print.css.tpl"}
<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">

		<table class="tblList">

		<form method="get" action="{$smarty.server.SCRIPT_NAME}">
				<thead>
				<tr>
					<td class="tblPagingLeft" colspan="100" align="right">
						<a href="javascript: exportReport()"><img src="{$IMAGES_URL}/excel_icon.gif"></a>
					</td>
				</tr>

				<tr class="tblHeader">
					<td colspan="100">
						{t}From:{/t} {getdate type="DATE" epoch=$filter_data.start_date default=TRUE} To: {getdate type="DATE" epoch=$filter_data.end_date default=TRUE}
					</td>
				<tr>

				<tr class="tblHeader">
					<td>
						{t}#{/t}
					</td>

					{foreach from=$columns item=column name=column}
						<td>
							{$column}
						</td>
					{/foreach}
				</tr>
				</thead>
				<tbody>
				{foreach from=$rows item=row name=rows}
					{cycle assign=row_class values="tblDataWhite,tblDataGrey"}
					<tr class="{$row_class}" {if $smarty.foreach.rows.last}style="font-weight: bold;"{/if}>
						<td>
							{if $smarty.foreach.rows.last}
								<br>
							{else}
								{$smarty.foreach.rows.iteration}
							{/if}
						</td>
						{foreach from=$columns key=key item=column name=column}
							<td>
								{if $key == 'actual_time_diff_wage'}
									{if $row[$key] != '' }
										${$row[$key]|default:"--"}
									{else}
										{$row[$key]|default:"--"}
									{/if}
								{else}
									{$row[$key]|default:"--"}
								{/if}
							</td>
						{/foreach}
					</tr>
				{foreachelse}
					<tr class="tblDataWhiteNH">
						<td colspan="100">
							{t}No results match your filter criteria.{/t}
						</td>
					</tr>
				{/foreach}
				<tr>
					<td class="tblHeader" colspan="100" align="center">
						{t}Generated:{/t} {getdate type="DATE+TIME" epoch=$generated_time}
					</td>
				</tr>
				</tbody>
			</table>
		</form>
	</div>
</div>
{include file="footer.tpl"}