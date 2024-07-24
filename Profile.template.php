<?php

function template_Profile_init()
{
	global $settings;

	require_once $settings['default_theme_dir'] . '/Profile.template.php';
}

function template_ignoreboards_override()
{
	global $context, $txt, $scripturl;

	echo '
	<form action="', $scripturl, '?action=profile;area=ignoreboards;save" method="post" accept-charset="', $context['character_set'], '" id="creator">
		<div class="cat_bar">
			<h3 class="catbg profile_hd">
				', $txt['profile'], '
			</h3>
		</div>
		<p class="information">', $txt['ignoreboards_info'], '</p>
		<div class="windowbg boardslist">';

	foreach ($context['categories'] as $category)
	{
		echo '
							<fieldset>
								<legend>
									', $category['name'], '
								</legend>
						<ul>';

		foreach ($category['boards'] as $board)
			echo '
							<li>
								<label style="padding-', $context['right_to_left'] ? 'right' : 'left', ': ', $board['child_level'], 'em;">
									<input type="checkbox" name="brd[', $board['id'], ']" value="', $board['id'], '"', $board['selected'] ? ' checked' : '', '>
									', $board['name'], '
								</label>
							</li>';

		echo '
						</ul>
							</fieldset>';
	}

	echo '
		<script>
			for (const div of document.forms.creator)
				if (div.nodeName == "FIELDSET")
				{
					let allChecked = true;
					for (let o of div.elements)
						if (o.nodeName == "INPUT" && o.type == "checkbox")
							allChecked &= o.checked;

					var
						a = document.createElement("legend"),
						b = document.createElement("input"),
						c = document.createElement("label");
					b.type = "checkbox";
					b.checked = allChecked;
					c.appendChild(b);
					c.appendChild(document.createTextNode(div.firstElementChild.textContent));
					a.appendChild(c);
					div.firstElementChild.replaceWith(a);
					b.addEventListener("click", function(els)
					{
						for (const o of els)
							if (o.nodeName == "INPUT" && o.type == "checkbox")
								o.checked = this.checked;
					}.bind(b, div.elements));
				}
		</script>';

	// Show the standard "Save Settings" profile button.
	template_profile_save();

	echo '
		</div><!-- .windowbg -->
	</form>
	<br>';
}

function template_tfasetup_override()
{
	global $txt, $context, $scripturl, $modSettings;

	echo '
			<div class="cat_bar">
				<h3 class="catbg">', $txt['tfa_title'], '</h3>
			</div>
			<div class="roundframe">';

	if (!empty($context['tfa_backup']))
		echo '
					<div class="smalltext error">
						', $txt['tfa_backup_used_desc'], '
					</div>';

	elseif ($modSettings['tfa_mode'] == 2)
		echo '
					<div class="smalltext">
						<strong>', $txt['tfa_forced_desc'], '</strong>
					</div>';

	echo '
					<div class="smalltext">
						', $txt['tfa_desc'], '
					</div>
						<form action="', $scripturl, '?action=profile;area=tfasetup" method="post">
							<div class="block">
								<strong>', $txt['tfa_step1'], '</strong><br>';

	if (!empty($context['tfa_pass_error']))
		echo '
								<div class="error smalltext">
									', $txt['tfa_pass_invalid'], '
								</div>';

	echo '
								<input type="password" name="oldpasswrd" size="25"', !empty($context['password_auth_failed']) ? ' class="error"' : '', !empty($context['tfa_pass_value']) ? ' value="' . $context['tfa_pass_value'] . '"' : '', '>
							</div>
							<div class="block">
								<strong>', $txt['tfa_step2'], '</strong>
								<div class="smalltext">', $txt['tfa_step2_desc'], '</div>
								<code class="bbc_code">', $context['tfa_secret'], '</code> 
							</div>
							<div class="block">
								<strong>', $txt['tfa_step3'], '</strong><br>';

	if (!empty($context['tfa_error']))
		echo '
								<div class="error smalltext">
									', $txt['tfa_code_invalid'], '
								</div>';

	echo '
								<input type="text" name="tfa_code" size="25"', !empty($context['tfa_error']) ? ' class="error"' : '', !empty($context['tfa_value']) ? ' value="' . $context['tfa_value'] . '"' : '', '>
								<input type="submit" name="save" value="', $txt['tfa_enable'], '" class="button">
							</div>
							<input type="hidden" name="', $context[$context['token_check'] . '_token_var'], '" value="', $context[$context['token_check'] . '_token'], '">
							<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '">
						</form>
						<div id="qrcode"></div>
						<script type="text/javascript">
							new QRCode(document.getElementById("qrcode"), "', $context['tfa_qr_url'], '");
						</script>';

	if (!empty($context['from_ajax']))
		echo '
					<br>
					<a href="javascript:self.close();"></a>';

	echo '
			</div><!-- .roundframe -->';
}

function template_tfadisable_override()
{
	global $txt, $context, $scripturl;

	echo '
			<div class="cat_bar">
				<h3 class="catbg">', $txt['tfadisable'], '</h3>
			</div>
			<div class="roundframe">
				<form action="', $scripturl, '?action=profile;area=tfadisable" method="post">';

	if ($context['user']['is_owner'])
		echo '
					<div class="block">
						<strong', (isset($context['modify_error']['bad_password']) || isset($context['modify_error']['no_password']) ? ' class="error"' : ''), '>', $txt['current_password'], '</strong><br>
						<input type="password" name="oldpasswrd" size="20">
					</div>';
	else
		echo '
					<div class="smalltext">
						', sprintf($txt['tfa_disable_for_user'], $context['user']['name']), '
					</div>';

	echo '
					<input type="submit" name="save" value="', $txt['tfa_disable'], '" class="button floatright">
					<input type="hidden" name="', $context[$context['token_check'] . '_token_var'], '" value="', $context[$context['token_check'] . '_token'], '">
					<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '">
					<input type="hidden" name="u" value="', $context['id_member'], '">
				</form>
			</div><!-- .roundframe -->';
}

function template_tfasetup_backup_override()
{
	global $context, $txt;

	echo '
			<div class="cat_bar">
				<h3 class="catbg">', $txt['tfa_backup_title'], '</h3>
			</div>
			<div class="roundframe">
				<div>
					<div class="smalltext">', $txt['tfa_backup_desc'], '</div>
					<code class="bbc_code">', $context['tfa_backup'], '</code>
				</div>
			</div>';
}
