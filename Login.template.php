<?php

function template_Login_init()
{
	global $settings;

	require_once $settings['default_theme_dir'] . '/Login.template.php';
}

function template_login_override()
{
	global $context, $scripturl, $modSettings, $txt;

	echo '
			<div class="cat_bar">
				<h3 class="catbg">
					<span class="main_icons login"></span> ', $txt['login'], '
				</h3>
			</div>
			<div class="roundframe">
				<form class="login" action="', $context['login_url'], '" name="frmLogin" id="frmLogin" method="post" accept-charset="', $context['character_set'], '">';

	// Did they make a mistake last time?
	if (!empty($context['login_errors']))
		echo '
					<div class="errorbox">', implode('<br>', $context['login_errors']), '</div>';

	// Or perhaps there's some special description for this time?
	if (isset($context['description']))
		echo '
					<div class="noticebox">', $context['description'], '</div>';

	// Now just get the basic information - username, password, etc.
	echo '
					<dl>
						<dt>', $txt['username'], ':</dt>
						<dd><input type="text" name="user" value="', $context['default_username'] ?? '', '"></dd>
						<dt>', $txt['password'], ':</dt>
						<dd><input type="password" name="passwrd" value="', $context['default_password'] ?? '', '"></dd>
						<dt>', $txt['time_logged_in'], ':</dt>
						<dd>
							<select name="cookielength" id="cookielength">';

	foreach ($context['login_cookie_times'] as $cookie_time => $cookie_txt)
		echo '
								<option value="', $cookie_time, '"', $modSettings['cookieTime'] == $cookie_time ? ' selected' : '', '>', $txt[$cookie_txt], '</option>';

	echo '
							</select>
						</dd>';

	// If they have deleted their account, give them a chance to change their mind.
	if (isset($context['login_show_undelete']))
		echo '
						<dt class="alert">', $txt['undelete_account'], ':</dt>
						<dd><input type="checkbox" name="undelete"></dd>';

	echo '
					</dl>
					<p class="centertext smalltext">
						<a href="', $scripturl, '?action=reminder">', $txt['forgot_your_password'], '</a>
					</p>
					<p class="centertext">
						<input type="submit" value="', $txt['login'], '" class="button">
					</p>';

	if (!empty($modSettings['registration_method']) && $modSettings['registration_method'] == 1)
		echo '
					<p class="centertext smalltext">
						', sprintf($txt['welcome_guest_activate'], $scripturl), '
					</p>';

	echo '
					<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '">
					<input type="hidden" name="', $context['login_token_var'], '" value="', $context['login_token'], '">
					<script>
						document.forms.frmLogin.', isset($context['default_username']) && $context['default_username'] != '' ? 'passwrd' : 'user', '.focus();
					</script>
				</form>';

	if (!empty($context['can_register']))
		echo '
				<hr>
				<div class="centertext">
					', sprintf($txt['register_prompt'], $scripturl), '
				</div>';

	echo '
			</div><!-- .roundframe -->';
}

function template_kick_guest_override()
{
	global $context, $scripturl, $txt;

	echo '
			<p class="noticebox">
				', $context['kick_message'] ?? $txt['only_members_can_access'], '<br>
				', $context['can_register'] ? sprintf($txt['login_below_or_register'], $scripturl . '?action=signup', $context['forum_name_html_safe']) : $txt['login_below'], '
			</p>';

	template_login_override();
}
