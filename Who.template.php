<?php
/**
 * Simple Machines Forum (SMF)
 *
 * @package SMF
 * @author Simple Machines https://www.simplemachines.org
 * @copyright 2022 Simple Machines and individual contributors
 * @license https://www.simplemachines.org/about/smf/license.php BSD
 *
 * @version 2.1.3
 */

/**
 * This handles the Who's Online page
 */
function template_main()
{
	global $context, $settings, $scripturl, $txt;

	echo '
		<form action="', $scripturl, '?action=who" method="post" id="whoFilter" accept-charset="', $context['character_set'], '">
			<div id="display_head" class="information">
				<h2 class="display_title">', $txt['who_title'], '</h2>
			</div>
				<div class="pagesection">
					<div class="pagelinks floatleft">', $context['page_index'], '</div>
					<div class="selectbox floatright" id="upper_show">
						', $txt['who_show'], '
						<select name="show_top" onchange="document.forms.whoFilter.show.value = this.value; document.forms.whoFilter.submit();">';

	foreach ($context['show_methods'] as $value => $label)
		echo '
							<option value="', $value, '" ', $value == $context['show_by'] ? ' selected' : '', '>', $label, '</option>';
	echo '
						</select>
						<noscript>
							<input type="submit" name="submit_top" value="', $txt['go'], '" class="button">
						</noscript>
					</div>
				</div>
				<table class="table_grid">
					<thead>
						<tr class="title_bar">
							<th scope="col" class="lefttext" style="width: 40%;"><a href="', $scripturl, '?action=who;start=', $context['start'], ';show=', $context['show_by'], ';sort=user', $context['sort_direction'] != 'down' && $context['sort_by'] == 'user' ? '' : ';asc', '" rel="nofollow">', $txt['who_user'], $context['sort_by'] == 'user' ? '<span class="main_icons sort_' . $context['sort_direction'] . '"></span>' : '', '</a></th>
							<th scope="col" class="lefttext time" style="width: 20%;"><a href="', $scripturl, '?action=who;start=', $context['start'], ';show=', $context['show_by'], ';sort=time', $context['sort_direction'] == 'down' && $context['sort_by'] == 'time' ? ';asc' : '', '" rel="nofollow">', $txt['who_time'], $context['sort_by'] == 'time' ? '<span class="main_icons sort_' . $context['sort_direction'] . '"></span>' : '', '</a></th>
							<th scope="col" class="lefttext half_table">', $txt['who_action'], '</th>
						</tr>
					</thead>
					<tbody>';

	foreach ($context['members'] as $member)
	{
		echo '
						<tr class="windowbg">
							<td>';

		// Guests can't be messaged.
		if (!$member['is_guest'])
			echo '
								<span class="contact_info floatright">
									', $context['can_send_pm'] ? '<a href="' . $member['online']['href'] . '" title="' . $txt['pm_online'] . '">' : '', $settings['use_image_buttons'] ? '<span class="main_icons im_' . ($member['online']['is_online'] == 1 ? 'on' : 'off') . '" title="' . $txt['pm_online'] . '"></span>' : $member['online']['label'], $context['can_send_pm'] ? '</a>' : '', '
								</span>';

		echo '
								<span class="member', $member['is_hidden'] ? ' hidden' : '', '">
									', $member['is_guest'] ? $member['name'] : '<a href="' . $member['href'] . '" title="' . sprintf($txt['view_profile_of_username'], $member['name']) . '"' . (empty($member['color']) ? '' : ' style="color: ' . $member['color'] . ';"') . '>' . $member['name'] . '</a>', '
								</span>';

		if (!empty($member['ip']))
			echo '
								(<a href="' . $scripturl . '?action=', ($member['is_guest'] ? 'trackip' : 'profile;area=tracking;sa=ip;u=' . $member['id']), ';searchip=' . $member['ip'] . '">' . str_replace(':', ':&ZeroWidthSpace;', $member['ip']) . '</a>)';

		echo '
							</td>
							<td class="time">', $member['time'], '</td>
							<td>';

		if (is_array($member['action']))
		{
			$tag = !empty($member['action']['tag']) ? $member['action']['tag'] : 'span';

			echo '
								<', $tag, !empty($member['action']['class']) ? ' class="' . $member['action']['class'] . '"' : '', '>
									', $txt[$member['action']['label']], (!empty($member['action']['error_message']) ? $member['action']['error_message'] : ''), '
								</', $tag, '>';
		}
		else
			echo $member['action'];

		echo '
							</td>
						</tr>';
	}

	// No members?
	if (empty($context['members']))
		echo '
						<tr class="windowbg">
							<td colspan="3">
							', $txt['who_no_online_' . ($context['show_by'] == 'guests' || $context['show_by'] == 'spiders' ? $context['show_by'] : 'members')], '
							</td>
						</tr>';

	echo '
					</tbody>
				</table>
				<div class="pagesection" id="lower_pagesection">
					<div class="pagelinks floatleft" id="lower_pagelinks">', $context['page_index'], '</div>
					<div class="selectbox floatright">
						', $txt['who_show'], '
						<select name="show" onchange="document.forms.whoFilter.submit();">';

	foreach ($context['show_methods'] as $value => $label)
		echo '
							<option value="', $value, '" ', $value == $context['show_by'] ? ' selected' : '', '>', $label, '</option>';
	echo '
						</select>
						<noscript>
							<input type="submit" value="', $txt['go'], '" class="button">
						</noscript>
					</div>
				</div><!-- #lower_pagesection -->
		</form>';
}

/**
 * This displays a nice credits page
 */
function template_credits()
{
	global $context, $txt;

	// The most important part - the credits :P.
	echo '
		<div id="display_head" class="information">
			<h2 class="display_title">', $txt['credits'], '</h2>
			<p>', $txt['credits_intro'], '</p>
		</div>
		<div class="roundframe" id="credits">
			<dl>';

	foreach ($context['credits'] as $section)
		foreach ($section['groups'] as $group)
			echo '
				<dt>', isset($group['title']) ? '<b>' . $group['title'] . '</b>' : '', '</dt>
				<dd>', sprintf($txt['credits_list'], sentence_list($group['members'])), '</dd>';

	// Other software and graphics
	if (!empty($context['credits_software_graphics']))
	{
		if (!empty($context['credits_software_graphics']['graphics']))
			echo '
				<dt><b>', $txt['credits_graphics'], '</b></dt>
				<dd>', implode('</dd><dd>', $context['credits_software_graphics']['graphics']), '</dd>';

		if (!empty($context['credits_software_graphics']['software']))
			echo '
				<dt><b>', $txt['credits_software'], '</b></dt>
				<dd>', implode('</dd><dd>', $context['credits_software_graphics']['software']), '</dd>';

		if (!empty($context['credits_software_graphics']['fonts']))
			echo '
				<dt><b>', $txt['credits_fonts'], '</b></dt>
				<dd>', implode('</dd><dd>', $context['credits_software_graphics']['fonts']), '</dd>';
	}

	// How about Modifications, we all love em
	if (!empty($context['credits_modifications']) || !empty($context['copyrights']['mods']))
	{
		echo '
				<dt><b>', $txt['credits_modifications'], '</b></dt>';

		// Display the credits.
		if (!empty($context['credits_modifications']))
			echo '
				<dd>', implode('</dd><dd>', $context['credits_modifications']), '</dd>';

		// Legacy.
		if (!empty($context['copyrights']['mods']))
			echo '
				<dd>', implode('</dd><dd>', $context['copyrights']['mods']), '</dd>';
	}

	// SMF itself
	echo '
				<dt><b>', $txt['credits_forum'], ' ', $txt['credits_copyright'], '</b></dt>
				<dd>', $context['copyrights']['smf'], '</dd>
			</dl>
		</div>
	</div><!-- #credits -->';
}

?>