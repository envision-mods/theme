<?php
/**
 * Simple Machines Forum (SMF)
 *
 * @package SMF
 * @author Simple Machines https://www.simplemachines.org
 * @copyright 2022 Simple Machines and individual contributors
 * @license https://www.simplemachines.org/about/smf/license.php BSD
 *
 * @version 2.1.0
 */

function template_Search_init()
{
	global $settings;

	require_once $settings['default_theme_dir'] . '/Search.template.php';
}

function template_main_override()
{
	global $context, $txt, $scripturl, $modSettings;

	echo '
	<form action="', $scripturl, '?action=search2" method="post" accept-charset="', $context['character_set'], '" name="searchform">';

	if (!empty($context['search_errors']))
		echo '
		<div class="errorbox">
			', implode('<br>', $context['search_errors']['messages']), '
		</div>';

	if (!empty($context['search_ignored']))
		echo '
		<div class="noticebox">
			', $txt['search_warning_ignored_word' . (count($context['search_ignored']) == 1 ? '' : 's')], ': ', implode(', ', $context['search_ignored']), '
		</div>';

	echo '
		<div class="cat_bar">
			<h3 class="catbg">
				<span class="main_icons filter"></span>', $txt['set_parameters'], '
			</h3>
		</div>
		<div id="advanced_search" class="roundframe">
			<dl class="settings" id="search_options">
				<dt>
					<strong><label for="searchfor">', $txt['search_for'], ':</label></strong>
				</dt>
				<dd>
					<input type="search" name="search" id="searchfor" ', !empty($context['search_params']['search']) ? ' value="' . $context['search_params']['search'] . '"' : '', ' maxlength="', $context['search_string_limit'], '" size="40">';

	if (empty($modSettings['search_simple_fulltext']))
		echo '
					<br><em class="smalltext">', $txt['search_example'], '</em>';

	echo '
				</dd>

				<dt>
					<label for="searchtype">', $txt['search_match'], ':</label>
				</dt>
				<dd>
					<select name="searchtype" id="searchtype">
						<option value="1"', empty($context['search_params']['searchtype']) ? ' selected' : '', '>', $txt['all_words'], '</option>
						<option value="2"', !empty($context['search_params']['searchtype']) ? ' selected' : '', '>', $txt['any_words'], '</option>
					</select>
				</dd>
				<dt>
					<label for="userspec">', $txt['by_user'], ':</label>
				</dt>
				<dd>
					<input id="userspec" type="text" name="userspec" value="', empty($context['search_params']['userspec']) ? '*' : $context['search_params']['userspec'], '" size="40">
				</dd>
				<dt>
					<label for="sort">', $txt['search_order'], ':</label>
				</dt>
				<dd>
					<select id="sort" name="sort">
						<option value="relevance|desc">', $txt['search_orderby_relevant_first'], '</option>
						<option value="num_replies|desc">', $txt['search_orderby_large_first'], '</option>
						<option value="num_replies|asc">', $txt['search_orderby_small_first'], '</option>
						<option value="id_msg|desc">', $txt['search_orderby_recent_first'], '</option>
						<option value="id_msg|asc">', $txt['search_orderby_old_first'], '</option>
					</select>
				</dd>
				<dt class="righttext options">',
					$txt['search_options'], ':
				</dt>
				<dd class="options">
					<ul>
						<li>
							<input type="checkbox" name="show_complete" id="show_complete" value="1"', !empty($context['search_params']['show_complete']) ? ' checked' : '', '>
							<label for="show_complete">', $txt['search_show_complete_messages'], '</label>
						</li>
						<li>
							<input type="checkbox" name="subject_only" id="subject_only" value="1"', !empty($context['search_params']['subject_only']) ? ' checked' : '', '>
							<label for="subject_only">', $txt['search_subject_only'], '</label>
						</li>
					</ul>
				</dd>
				<dt class="between">',
					$txt['search_post_age'], ':
				</dt>
				<dd>
					<label for="minage">', $txt['search_between'], ' </label>
					<input type="number" name="minage" id="minage" value="', empty($context['search_params']['minage']) ? '0' : $context['search_params']['minage'], '" size="5" maxlength="4">
					<label for="maxage"> ', $txt['search_and'], ' </label>
					<input type="number" name="maxage" id="maxage" value="', empty($context['search_params']['maxage']) ? '9999' : $context['search_params']['maxage'], '" size="5" maxlength="4"> ', $txt['days_word'], '
				</dd>
			</dl>
			<script>
				createEventListener(window);
				window.addEventListener("load", initSearch, false);
			</script>
			<input type="hidden" name="advanced" value="1">';

	// Require an image to be typed to save spamming?
	if ($context['require_verification'])
		echo '
			<p>
				<strong>', $txt['verification'], ':</strong>
				', template_control_verification($context['visual_verification_id'], 'all'), '
			</p>';

	// If $context['search_params']['topic'] is set, that means we're searching just one topic.
	if (!empty($context['search_params']['topic']))
		echo '
			<p>
				', $txt['search_specific_topic'], ' &quot;', $context['search_topic']['link'], '&quot;.
			</p>
			<input type="hidden" name="topic" value="', $context['search_topic']['id'], '">
			<input type="submit" name="b_search" value="', $txt['search'], '" class="button">';

	if (empty($context['search_params']['topic']))
	{
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
			<div class="padding">
				<input type="submit" name="b_search" value="', $txt['search'], '" class="button floatright">
			</div>
		</div>
		<script>
			for (const div of document.forms.searchform)
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
	}

	echo '
	</form>
	<script>
		var oAddMemberSuggest = new smc_AutoSuggest({
			sSelf: \'oAddMemberSuggest\',
			sSessionId: smf_session_id,
			sSessionVar: smf_session_var,
			sControlId: \'userspec\',
			sSearchType: \'member\',
			bItemList: false
		});
	</script>';
}
