{include file="sm_header.tpl" is_report=TRUE }
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
						{if $filter_data.date_type == 'pay_period_ids'}
        						{t}Pay Period(s):{/t}
							{foreach from=$filter_data.pay_period_ids item=pay_period_id name=pay_period_rows}
								{$pay_period_options[$pay_period_id]}{if $smarty.foreach.pay_period_rows.first AND !$smarty.foreach.pay_period_rows.last}, {/if}
							{/foreach}
						{else}
							From: {getdate type="DATE" epoch=$filter_data.start_date default=TRUE} To: {getdate type="DATE" epoch=$filter_data.end_date default=TRUE}
						{/if}
					</td>
				<tr>
				</thead>

				<tr>

				{foreach from=$rows item=row name=rows}
					<table width="100%">

					  <thead>
					  <tr class="tblHeader">
						  <td align="left" colspan="100">
							  <table width="100%">
								  <tr>
									  <td>
										  <table class="editTable">
											  <tr>
												  <td class="cellLeftEditTable">
													  {t}Employee{/t}:
												  </td>
												  <td class="cellRightEditTable">
													  {$row.full_name} (#{$row.employee_number})
												  </td>
											  </tr>
										  </table>
									  </td>
									  <td>
										  <table class="editTable">
											  <tr>
												  <td class="cellLeftEditTable">
													  {t}Pay Period{/t}:
												  </td>
												  <td class="cellRightEditTable">
													  {$row.pay_period} ({t}Verified{/t}: {$row.verified_time_sheet})
												  </td>
											  </tr>
										  </table>
									  <td>
								  </tr>
							  </table>
						  </td>
					  </tr>

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
					  {foreach from=$row.data item=sub_row name=sub_rows}
						  {cycle assign=row_class values="tblDataWhite,tblDataGrey"}
						  <tr class="{$row_class}" {if $smarty.foreach.sub_rows.last}style="font-weight: bold;"{/if}>
							  <td>
								  {if $smarty.foreach.sub_rows.last}
									  <br>
								  {else}
									  {$smarty.foreach.sub_rows.iteration}
								  {/if}
							  </td>
							  {foreach from=$columns key=key item=column name=column}
								  <td>
									  {if $key == 'actual_time_diff_wage'}
										  {if $sub_row[$key] != '' }
											  ${$sub_row[$key]|default:"--"}
										  {else}
											  {$sub_row[$key]|default:"--"}
										  {/if}
									  {else}
										  {$sub_row[$key]|default:"--"}
									  {/if}
								  </td>
							  {/foreach}
						  </tr>
					  {/foreach}
					  <tr><td><br></td></tr>
					  </tbody>
				{foreachelse}
					<table width="100%">
					  <tr class="tblDataWhiteNH">
						  <td colspan="100">
							  {t}No results match your filter criteria.{/t}
						  </td>
					  </tr>
    				</table>
				{/foreach}
				</tr>

				<tr>
					<td class="tblHeader" colspan="100" align="center">
						{t}Generated:{/t} {getdate type="DATE+TIME" epoch=$generated_time}
					</td>
				</tr>
		  </table>
		</form>
	</div>
</div>
{include file="footer.tpl"}