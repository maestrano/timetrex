<!-- Begin Footer -->
<a name="bottom">
<br>
<div id="rowFooter">
	<div class="textFooter">
		<table border="0" width="100%">
			<tr>
				<td width="10%" align="left">{if ( stristr( $smarty.server.SCRIPT_NAME, 'login') OR stristr( $smarty.server.SCRIPT_NAME, 'forgotpassword') ) AND $config_vars.other.footer_left_html != ''}{$config_vars.other.footer_left_html}{else}&nbsp;{/if}</td>
				<td align="center">
					{t}Server response time:{/t} {php}echo sprintf('%01.3f',microtime(true)-$_SERVER['REQUEST_TIME_FLOAT']);{/php} {t}seconds.{/t}
					<br>
					Copyright &copy; {$smarty.now|date_format:"%Y"} <a href="http://{$ORGANIZATION_URL}" class="footerLink">{$ORGANIZATION_NAME}</a>.
					{if getTTProductEdition() == 10}
						{if stristr( $smarty.server.SCRIPT_NAME, 'login') == FALSE}
						<br>
						<a href="http://www.timetrex.com/r.php?id=451" target="_blank"><img src="{$IMAGES_URL}/facebook_button.jpg" border="0"></a>
						<a href="http://www.timetrex.com/r.php?id=455" target="_blank"><img src="{$IMAGES_URL}/twitter_button.jpg" border="0"></a>
						{/if}
						<br>
						The Program is provided AS IS, without warranty. Licensed under <a href="http://www.fsf.org/licensing/licenses/agpl-3.0.html">AGPLv3.</a> This program is free software; you can redistribute it and/or modify it under the terms of the <a href="http://www.fsf.org/licensing/licenses/agpl-3.0.html">GNU Affero General Public License version 3</a> as published by the Free Software Foundation including the additional permission set forth in the source code header.
					{else}
						All Rights Reserved.
					{/if}
					<br><br>{* REMOVING OR CHANGING THIS LOGO IS IN STRICT VIOLATION OF THE LICENSE AGREEMENT *}<a href="http://{$ORGANIZATION_URL}"><img src="{$BASE_URL}/send_file.php?object_type=copyright" alt="Time and Attendance"></a>
				</td>
				<td width="10%" align="right">{if ( stristr( $smarty.server.SCRIPT_NAME, 'login') OR stristr( $smarty.server.SCRIPT_NAME, 'forgotpassword') ) AND $config_vars.other.footer_right_html != ''}{$config_vars.other.footer_right_html}{else}&nbsp;{/if}</td>
			</tr>
		</table>
	</div>
</div>

<div>
	{php}
		Debug::writeToLog();
		Debug::Display();
		if (Debug::getEnableDisplay() == TRUE AND Debug::getVerbosity() >= 10) {
			{/php}
			{$profiler->stopTimer('Main')}
			{$profiler->printTimers(TRUE)}
			{php}
		}
	{/php}
</div>

</div>
																																									{if $config_vars.debug.production == 1 AND $config_vars.other.disable_google_analytics != 1}<script src="http{if $smarty.server.HTTPS == TRUE}s://ssl{else}://www{/if}.google-analytics.com/urchin.js" type="text/javascript"></script><script type="text/javascript">_uacct="UA-333702-3"; __utmSetVar('Company: {if is_object($primary_company)}{$primary_company->getName()|escape}{else}N/A{/if}'); __utmSetVar('Edition: {php}echo getTTProductEditionName(){/php}'); __utmSetVar('Key: {if isset($system_settings)}{$system_settings.registration_key}{else}N/A{/if}'); __utmSetVar('Host: {$smarty.server.HTTP_HOST}'); __utmSetVar('Version: {$APPLICATION_VERSION}'); urchinTracker();</script><img src="{$IMAGES_URL}spacer.gif">{else}<!-- Company: {if is_object($primary_company)}{$primary_company->getName()|escape}{else}N/A{/if}, Edition: {php}echo getTTProductEditionName(){/php}, Key: {if isset($system_settings)}{$system_settings.registration_key}{else}N/A{/if}, Version: {$APPLICATION_VERSION} -->{/if}
</body>
</html>