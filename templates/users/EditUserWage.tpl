{include file="header.tpl" enable_calendar=true enable_ajax=true body_onload="showWeeklyTime('weekly_time');"}

<script	language=JavaScript>
{literal}
function showWeeklyTime(objectID) {
	if(document.getElementById) {
		if ( document.getElementById(objectID).style.display == 'none' ) {
			if ( document.getElementById('type_id').value != 10 ) {
				//Show
				document.getElementById(objectID).className = '';
				document.getElementById(objectID).style.display = '';
			}
		} else {
			if ( document.getElementById('type_id').value == 10 ) {
				document.getElementById(objectID).style.display = 'none';
			}
		}
	}

}

var hwCallback = {
		getHourlyRate: function(result) {
			document.getElementById('hourly_rate').value = result;
		},
		getUserLaborBurdenPercent: function(result) {
			document.getElementById('labor_burden_percent').value = result;
		}
	}

var remoteHW = new AJAX_Server(hwCallback);

function getHourlyRate() {
	//alert('Wage: '+ document.getElementById('wage_val').value +' Time: '+ document.getElementById('weekly_time_val').value);
	if ( document.getElementById('type_id').value != 10 ) {
		remoteHW.getHourlyRate( document.getElementById('wage_val').value, document.getElementById('weekly_time_val').value, document.getElementById('type_id').value);
	}
}

function getUserLaborBurdenPercent() {
	remoteHW.getUserLaborBurdenPercent( {/literal}{$user_data->getId()}{literal} );
}
{/literal}
</script>

<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">

		<form method="post" name="wage" action="{$smarty.server.SCRIPT_NAME}">
		    <div id="contentBoxTwoEdit">
				{if !$uwf->Validator->isValid()}
					{include file="form_errors.tpl" object="uwf"}
				{/if}

				<table class="editTable">

				<tr>
					<td class="cellLeftEditTable">
						{t}Employee:{/t}
					</td>
					<td class="cellRightEditTable">
						{$user_data->getFullName()}
					</td>
				</tr>

				<tr onClick="showHelpEntry('wage_group')">
					<td class="{isvalid object="uwf" label="type" value="cellLeftEditTable"}">
						{t}Group:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="wage_group_id" name="wage_data[wage_group_id]">
							{html_options options=$wage_data.wage_group_options selected=$wage_data.wage_group_id}
						</select>
					</td>
				</tr>

				<tr onClick="showHelpEntry('type')">
					<td class="{isvalid object="uwf" label="type" value="cellLeftEditTable"}">
						{t}Type:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="type_id" name="wage_data[type]" onChange="showWeeklyTime('weekly_time'); getHourlyRate(); ">
							{html_options options=$wage_data.type_options selected=$wage_data.type}
						</select>
					</td>
				</tr>

				<tr onClick="showHelpEntry('wage')">
					<td class="{isvalid object="uwf" label="wage" value="cellLeftEditTable"}">
						{t}Wage:{/t}
					</td>
					<td class="cellRightEditTable">
						{$wage_data.currency_symbol}<input id="wage_val" size="15" type="text" name="wage_data[wage]" value="{$wage_data.wage}" onChange="getHourlyRate()"> {$wage_data.iso_code}
					</td>
				</tr>

				<tbody id="weekly_time" style="display:none" >
				<tr onClick="showHelpEntry('weekly_time')">
					<td class="{isvalid object="uwf" label="weekly_time" value="cellLeftEditTable"}">
						{t}Average Time / Week:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" id="weekly_time_val" size="6" name="wage_data[weekly_time]" value="{gettimeunit value=$wage_data.weekly_time default="0"}" onChange="getHourlyRate()"> {t}(ie: 40 hours / week){/t}
					</td>
				</tr>
				<tr onClick="showHelpEntry('hourly_rate')">
					<td class="{isvalid object="uwf" label="hourly_rate" value="cellLeftEditTable"}">
						{t}Hourly Rate:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" id="hourly_rate" size="15" name="wage_data[hourly_rate]" value="{$wage_data.hourly_rate|default:0.00}">
					</td>
				</tr>

				</tbody>

				<tr onClick="showHelpEntry('labor_burden_percent')">
					<td class="{isvalid object="uwf" label="labor_burden_percent" value="cellLeftEditTable"}">
						{t}Labor Burden Percent:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="5" id="labor_burden_percent" name="wage_data[labor_burden_percent]" value="{$wage_data.labor_burden_percent}">{t}% (ie: 25% burden){/t}
						<input type="button" value="{t}Calculate{/t}" onClick="getUserLaborBurdenPercent(); return false;"/>
					</td>
				</tr>

				<tr onClick="showHelpEntry('effective_date')">
					<td class="{isvalid object="uwf" label="effective_date" value="cellLeftEditTable"}">
						{t}Effective Date:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="15" id="effective_date" name="wage_data[effective_date]" value="{getdate type="DATE" epoch=$wage_data.effective_date}">
						<img src="{$BASE_URL}/images/cal.gif" id="calendar" width="16" height="16" border="0" alt="Pick a date" onMouseOver="calendar_setup('effective_date', 'calendar', false);">
						{if count($pay_period_boundary_date_options) > 0}
						&nbsp;&nbsp;{t}or{/t}&nbsp;&nbsp;
						<select name="wage_data[effective_date2]" onChange="{literal}if (this.value != '-1') { document.getElementById('effective_date').value = this.value }{/literal}">
							{html_options options=$pay_period_boundary_date_options selected=$tmp_effective_date}
						</select>
						{/if}
					</td>
				</tr>

				<tr onClick="showHelpEntry('note')">
					<td class="{isvalid object="uwf" label="note" value="cellLeftEditTable"}">
						{t}Note:{/t}
					</td>
					<td class="cellRightEditTable">
						<textarea rows="5" cols="45" name="wage_data[note]">{$wage_data.note|escape}</textarea>
					</td>
				</tr>

			</table>
		</div>

		<div id="contentBoxFour">
			<input type="submit" class="btnSubmit" name="action:submit" value="{t}Submit{/t}" onClick="return singleSubmitHandler(this)">
		</div>

		<input type="hidden" name="wage_data[id]" value="{$wage_data.id}">
		<input type="hidden" name="user_id" value="{$user_data->getId()}">
		<input type="hidden" name="saved_search_id" value="{$saved_search_id}">
		</form>
	</div>
</div>

{include file="footer.tpl"}
