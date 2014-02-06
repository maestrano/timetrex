{include file="header.tpl" body_onload="changeType();changeSpecialDay();"}
<script	language=JavaScript>

{literal}

function changeType() {
	if(document.getElementById) {
		document.getElementById('static').style.display = 'none';
		document.getElementById('dynamic-20').style.display = 'none';
		document.getElementById('dynamic-30').style.display = 'none';

		if ( document.getElementById('type_id').value == 10 ) {
			//Show Static
			document.getElementById('static').className = '';
			document.getElementById('static').style.display = '';

			//document.getElementById('dynamic').style.display = 'none';

		} else if ( document.getElementById('type_id').value == 20 ) {
			//Show Dynamic
			document.getElementById('dynamic-20').className = '';
			document.getElementById('dynamic-20').style.display = '';

			//document.getElementById('static').style.display = 'none';
		} else if ( document.getElementById('type_id').value == 30 ) {
			//Show Dynamic
			document.getElementById('dynamic-30').className = '';
			document.getElementById('dynamic-30').style.display = '';

			document.getElementById('static').className = '';
			document.getElementById('static').style.display = '';

			document.getElementById('month').className = '';
			document.getElementById('month').style.display = '';

			//document.getElementById('static').style.display = 'none';
		}

	}
}

function changeSpecialDay() {
	if(document.getElementById) {
		if ( document.getElementById('special_day').value != 0 ) {
			//Hide Static and Dynamic
			document.getElementById('type').style.display = 'none';
			document.getElementById('static').style.display = 'none';
			document.getElementById('dynamic-20').style.display = 'none';
			document.getElementById('dynamic-30').style.display = 'none';
			document.getElementById('month').style.display = 'none';

		} else {
			document.getElementById('type').className = '';
			document.getElementById('type').style.display = '';

			changeType();
		}
	}
}

{/literal}
</script>
<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">

		<form method="post" name="wage" action="{$smarty.server.SCRIPT_NAME}">
		    <div id="contentBoxTwoEdit">
				{if !$rhf->Validator->isValid()}
					{include file="form_errors.tpl" object="rhf"}
				{/if}

				<table class="editTable">

				<tr onClick="showHelpEntry('name')">
					<td class="{isvalid object="rhf" label="name" value="cellLeftEditTable"}">
						{t}Name:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" name="data[name]" value="{$data.name}">
					</td>
				</tr>
				<tr onClick="showHelpEntry('special_day')">
					<td class="{isvalid object="rhf" label="special_day" value="cellLeftEditTable"}">
						{t}Special Day:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="special_day" name="data[special_day_id]" onChange="changeSpecialDay();">
							{html_options options=$data.special_day_options selected=$data.special_day_id}
						</select>
					</td>
				</tr>

				<tbody id="type" >

				<tr onClick="showHelpEntry('type')">
					<td class="{isvalid object="rhf" label="type" value="cellLeftEditTable"}">
						{t}Type:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="type_id" name="data[type_id]" onChange="changeType();">
							{html_options options=$data.type_options selected=$data.type_id}
						</select>
					</td>
				</tr>

				</tbody>

				<tbody id="dynamic-20" style="display:none" >

				<tr onClick="showHelpEntry('week_interval')">
					<td class="{isvalid object="rhf" label="week_interval" value="cellLeftEditTable"}">
						{t}Week Interval:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="week_interval" name="data[week_interval]">
							{html_options options=$data.week_interval_options selected=$data.week_interval}
						</select>
					</td>
				</tr>

				<tr onClick="showHelpEntry('day_of_week')">
					<td class="{isvalid object="rhf" label="day_of_week" value="cellLeftEditTable"}">
						{t}Day of the Week:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="day_of_week" name="data[day_of_week_20]">
							{html_options options=$data.day_of_week_options selected=$data.day_of_week}
						</select>
					</td>
				</tr>

				</tbody>

				<tbody id="dynamic-30" style="display:none" >

				<tr onClick="showHelpEntry('day_of_week')">
					<td class="{isvalid object="rhf" label="day_of_week" value="cellLeftEditTable"}">
						{t}Day of the Week:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="day_of_week" name="data[day_of_week_30]">
							{html_options options=$data.day_of_week_options selected=$data.day_of_week}
						</select>
					</td>
				</tr>

				<tr onClick="showHelpEntry('pivot_day_direction')">
					<td class="{isvalid object="rhf" label="pivot_day_direction" value="cellLeftEditTable"}">
						{t}Pivot Day Direction:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="pivot_day_direction" name="data[pivot_day_direction_id]">
							{html_options options=$data.pivot_day_direction_options selected=$data.pivot_day_direction_id}
						</select>
					</td>
				</tr>

				</tbody>


				<tbody id="static" >

				<tr onClick="showHelpEntry('day_of_month')">
					<td class="{isvalid object="rhf" label="day_of_month" value="cellLeftEditTable"}">
						{t}Day of the Month:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="day_of_month" name="data[day_of_month]">
							{html_options options=$data.day_of_month_options selected=$data.day_of_month}
						</select>
					</td>
				</tr>

				</tbody>

				<tbody id="month" >

				<tr onClick="showHelpEntry('month')">
					<td class="{isvalid object="rhf" label="month" value="cellLeftEditTable"}">
						{t}Month:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="month_of_year" name="data[month]">
							{html_options options=$data.month_of_year_options selected=$data.month}
						</select>
					</td>
				</tr>

				</tbody>

				<tr onClick="showHelpEntry('always_week_day_id')">
					<td class="{isvalid object="rhf" label="always_week_day_id" value="cellLeftEditTable"}">
						{t}Always On Week Day:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="always_week_day" name="data[always_week_day_id]">
							{html_options options=$data.always_week_day_options selected=$data.always_week_day_id}
						</select>
					</td>
				</tr>

			</table>
		</div>

		<div id="contentBoxFour">
			<input type="submit" class="btnSubmit" name="action:submit" value="{t}Submit{/t}" onClick="return singleSubmitHandler(this)">
		</div>

		<input type="hidden" name="data[id]" value="{$data.id}">
		</form>
	</div>
</div>
{include file="footer.tpl"}
