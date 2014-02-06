<html>
<head>


{literal}
<style type="text/css">
  TABLE.calendar { text-align: center; }
  TH.month { background-color: #E0E0E0; }
  TD.prev-month { text-align: left; }
  TD.next-month { text-align: right; }
  TH.day-of-week { font-size: 8pt; }
  TD.selected-day { background-color: #33CCFF; }
  TD.day { background-color: #E0E0E0; }
  TD.today { background-color: #E0E0E0; font-weight: bold; }
</style>
{/literal}


<script	language=JavaScript>
var form_element="{$form_element}";
{literal}
function copy_text() {
	date_value = document.date_form.date.value;
	time_value = document.date_form.time.value;

	date_str = date_value +' '+ time_value;

	window.opener.document.getElementById(form_element).value = date_str;

	window.close();
}

function select_date(year,month,day) {
	document.date_form.year.value = year;
	document.date_form.month.value = month;
	document.date_form.day.value = day;

	document.date_form.submit();
}

{/literal}
</script>


</head>

<body>
<form name="date_form">
<input type="hidden" name="date" value="{$date}">
<table class="calendar" width="250" border="0" cellpadding="1" cellspacing="1">
  <tr>
    <th class="month" colspan="7">
      {$month_name}&nbsp;{$year}
    </th>
  </tr>
  <tr>
    <td class="prev-month" colspan="3">
      <a href="javascript:select_date({$prev_month_end|date_format:$url_format})">
        {$prev_month_abbrev}
      </a>
    </td>
    <td></td>
    <td class="next-month" colspan="3">
      <a href="javascript:select_date({$next_month_begin|date_format:$url_format})">
        {$next_month_abbrev}
      </a>
    </td>
  </tr>
  <tr>
  {section name="day_of_week" loop=$day_of_week_abbrevs}
    <th class="day-of-week">{$day_of_week_abbrevs[day_of_week]}</th>
  {/section}
  </tr>
  {section name="row" loop=$calendar}
    <tr>
      {section name="col" loop=$calendar[row]}
        {assign var="date" value=$calendar[row][col]}
        {if $date == $selected_date}
          <td class="selected-day">{$date|date_format:"%e"}</td>
        {elseif $date|date_format:"%m" == $month}
          <td class="day">
            <a href="javascript:select_date({$date|date_format:$url_format})">
              {$date|date_format:"%e"}
            </a>
          </td>
        {else}
          <td class="day"></td>
        {/if}
      {/section}
    </tr>
  {/section}
  <tr>
    <td class="today" colspan="7">
      {if $today_url != ""}
        <a href="javascript:select_date({$today_url})">Today</a>
      {else}
        Today
      {/if}
    </td>
  </tr>
  {if $show_time == TRUE}
  <tr>
    <td class="today" colspan="7" valign="center" align="center">
		<input type="text" size="14" id="time" name="time" value="{$time}">
    </td>
  </tr>
  {else}
	<input type="hidden" name="time" value="">
  {/if}
  <tr>
    <td align="center" colspan="7" class="today" >
		<input type="BUTTON" value="Submit" ONCLICK="javascript:copy_text()">
    </td>
  </tr>
</table>
<input type="hidden" name="year" value="">
<input type="hidden" name="month" value="">
<input type="hidden" name="day" value="">
<input type="hidden" name="show_date" value="{$show_date}">
<input type="hidden" name="show_time" value="{$show_time}">
<input type="hidden" name="form_element" value="{$form_element}">
</form>
</body>
</html>