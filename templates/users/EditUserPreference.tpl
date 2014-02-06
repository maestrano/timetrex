{include file="header.tpl" enable_ajax=TRUE body_onload="showDateFormat(); getTimeZoneOffset();"}

<script	language=JavaScript>
{literal}

function showDateFormat() {
	var lang = document.getElementById('language').value;

	if (lang == 'en'){
		document.getElementById('DateFormat').style.display = '';
		document.getElementById('otherDateFormat').style.display = 'none';
	}else{
		document.getElementById('otherDateFormat').style.display = '';
		document.getElementById('DateFormat').style.display = 'none';
	}
}

var loading = false;
var hwCallback = {
	getTimeZoneOffset: function(result) {
		if ( result != false ) {
			hours = result / 3600;
			if ( hours > 0 ) {
				sign = '+';
			} else {
				sign = '';
			}
			document.getElementById('time_zone_offset').innerHTML = sign+hours.toFixed(2);
		}
	}
}

var remoteHW = new AJAX_Server(hwCallback);

function getTimeZoneOffset() {
	time_zone = document.getElementById('time_zone').value;

	if ( time_zone != '') {
		remoteHW.getTimeZoneOffset( time_zone );
	}
}
{/literal}
</script>


<div id="rowContent">
  <div id="titleTab"><div class="textTitle"><span class="textTitleSub">{$title}</span></div>
</div>
<div id="rowContentInner">

		<form method="post" action="{$smarty.server.SCRIPT_NAME}">
		    <div id="contentBoxTwoEdit">
				{if !$upf->Validator->isValid()}
					{include file="form_errors.tpl" object="upf"}
				{/if}

				<table class="editTable">

				{include file="data_saved.tpl" result=$data_saved}

				{if $incomplete == 1}
					<tr id="warning">
						<td colspan="7">
							{t escape="no" 1=$APPLICATION_NAME}In order to improve your <b>%1</b> experience, please define your personal preferences.{/t}
						</td>
					</tr>
				{/if}


				{if $permission->Check('user_preference','edit') OR $permission->Check('user_preference','edit_child')}
				<tr onClick="showHelpEntry('user')">
					<td class="{isvalid object="upf" label="user" value="cellLeftEditTable"}">
						{t}Employee:{/t}
					</td>
					<td class="cellRightEditTable">
						{$pref_data.user_full_name}
					</td>
				</tr>
				{/if}

				<tr onClick="showHelpEntry('language')">
					<td class="{isvalid object="upf" label="language" value="cellLeftEditTable"}">
						{t}Language:{/t}
					</td>
					<td class="cellRightEditTable">
						<select name="pref_data[language]" onchange = "showDateFormat();" id="language">
							{html_options options=$pref_data.language_options selected=$pref_data.language}
						</select>
					</td>
				</tr>

				<tr onClick="showHelpEntry('date_format')">
					<td class="{isvalid object="upf" label="date_format" value="cellLeftEditTable"}">
						{t}Date Format:{/t}
					</td>
					<td class="cellRightEditTable">
						<select name="pref_data[date_format]" id="DateFormat" style="display:none">
							{html_options options=$pref_data.date_format_options selected=$pref_data.date_format}
						</select>

						<select name="pref_data[other_date_format]" id="otherDateFormat">
							{html_options options=$pref_data.other_date_format_options selected=$pref_data.other_date_format}
						</select>
					</td>
				</tr>

				<tr onClick="showHelpEntry('time_format')">
					<td class="{isvalid object="upf" label="time_format" value="cellLeftEditTable"}">
						{t}Time Format:{/t}
					</td>
					<td class="cellRightEditTable">
						<select name="pref_data[time_format]">
							{html_options options=$pref_data.time_format_options selected=$pref_data.time_format}
						</select>
					</td>
				</tr>

				<tr onClick="showHelpEntry('time_unit_format')">
					<td class="{isvalid object="upf" label="time_unit_format" value="cellLeftEditTable"}">
						{t}Time Units:{/t}
					</td>
					<td class="cellRightEditTable">
						<select name="pref_data[time_unit_format]">
							{html_options options=$pref_data.time_unit_format_options selected=$pref_data.time_unit_format}
						</select>
					</td>
				</tr>

				<tr onClick="showHelpEntry('time_zone')">
					<td class="{isvalid object="upf" label="time_zone" value="cellLeftEditTable"}">
						{t}Time Zone:{/t}
					</td>
					<td class="cellRightEditTable">
						<select id="time_zone" name="pref_data[time_zone]" onChange="getTimeZoneOffset()">
							{html_options options=$pref_data.time_zone_options selected=$pref_data.time_zone}
						</select>
						(GMT <span id="time_zone_offset"></span>)
					</td>
				</tr>

				<tr onClick="showHelpEntry('start_week_day')">
					<td class="{isvalid object="upf" label="start_week_day" value="cellLeftEditTable"}">
						{t}Start Weeks on:{/t}
					</td>
					<td class="cellRightEditTable">
						<select name="pref_data[start_week_day]">
							{html_options options=$pref_data.start_week_day_options selected=$pref_data.start_week_day}
						</select>
					</td>
				</tr>

				<tr onClick="showHelpEntry('items_per_page')">
					<td class="{isvalid object="upf" label="items_per_page" value="cellLeftEditTable"}">
						{t}Rows per Page:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="text" size="5" name="pref_data[items_per_page]" value="{$pref_data.items_per_page}">
					</td>
				</tr>

				<tr class="tblHeader">
					<td colspan="2">
						{t}Email Notifications{/t}
					</td>
				</tr>

				<tr onClick="showHelpEntry('email_notification_exception')">
					<td class="{isvalid object="upf" label="email_notification_exception" value="cellLeftEditTable"}">
						{t}Exceptions:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="checkbox" name="pref_data[enable_email_notification_exception]" value="1" {if $pref_data.enable_email_notification_exception == TRUE}checked{/if}>
					</td>
				</tr>

				<tr onClick="showHelpEntry('email_notification_message')">
					<td class="{isvalid object="upf" label="email_notification_message" value="cellLeftEditTable"}">
						{t}Messages:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="checkbox" name="pref_data[enable_email_notification_message]" value="1" {if $pref_data.enable_email_notification_message == TRUE}checked{/if}>
					</td>
				</tr>

				<tr onClick="showHelpEntry('email_notification_home')">
					<td class="{isvalid object="upf" label="email_notification_home" value="cellLeftEditTable"}">
						{t}Send Notifications to Home Email:{/t}
					</td>
					<td class="cellRightEditTable">
						<input type="checkbox" name="pref_data[enable_email_notification_home]" value="1" {if $pref_data.enable_email_notification_home == TRUE}checked{/if}>
					</td>
				</tr>

			</table>
		</div>

		<div id="contentBoxFour">
			<input type="submit" class="btnSubmit" name="action:submit" value="{t}Submit{/t}" onClick="return singleSubmitHandler(this)">
		</div>

		<input type="hidden" name="pref_data[id]" value="{$pref_data.id}">
		<input type="hidden" name="pref_data[user_id]" value="{$pref_data.user_id}">
		<input type="hidden" name="pref_data[user_full_name]" value="{$pref_data.user_full_name}">
		<input type="hidden" name="incomplete" value="1">
		</form>
	</div>
</div>
{include file="footer.tpl"}
