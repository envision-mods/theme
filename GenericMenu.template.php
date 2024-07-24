<?php

function template_generic_menu_dropdown_above()
{
	global $context, $scripturl, $txt;

	// Which menu are we rendering?
	$context['cur_menu_id'] = isset($context['cur_menu_id']) ? $context['cur_menu_id'] + 1 : 1;
	$menu_context = $context['menu_data_' . $context['cur_menu_id']];

	if (function_exists('template_admin') && $context['user']['is_admin'])
		echo '
			<form action="', $scripturl, '?action=admin;area=search" method="post" accept-charset="', $context['character_set'], '" style="margin: 0.5em 0;">
				<input type="text" name="search_term" placeholder="', $txt['admin_search'], '" class="input_text" />
				<select name="search_type">
					<option value="internal"', (empty($context['admin_preferences']['sb']) || $context['admin_preferences']['sb'] == 'internal' ? ' selected="selected"' : ''), '>', $txt['admin_search_type_internal'], '</option>
					<option value="member"', (!empty($context['admin_preferences']['sb']) && $context['admin_preferences']['sb'] == 'member' ? ' selected="selected"' : ''), '>', $txt['admin_search_type_member'], '</option>
					<option value="online"', (!empty($context['admin_preferences']['sb']) && $context['admin_preferences']['sb'] == 'online' ? ' selected="selected"' : ''), '>', $txt['admin_search_type_online'], '</option>
				</select>
				<input type="submit" value="', $txt['search'], '" class="button" />
			</form>';

	template_generic_menu($menu_context);
	template_generic_menu_tabs($menu_context);
}

/**
 * Part of the admin layer - used with generic_menu_dropdown_above to close the admin content div.
 */
function template_generic_menu_dropdown_below()
{
}

function template_generic_menu($menu_context)
{
	global $context;

	echo '
					<nav><ul class="menu">';

	// Main areas first.
	foreach ($menu_context['sections'] as $section)
	{
		echo '
						<li ', !empty($section['areas']) ? 'class="subsections"' : '', '><a class="', !empty($section['selected']) ? 'active ' : '', '" href="', $section['url'], $menu_context['extra_parameters'], '">', $section['title'], !empty($section['amt']) ? ' <span class="amt">' . $section['amt'] . '</span>' : '', '</a>
							<ul>';

		// For every area of this section show a link to that area (bold if it's currently selected.)
		// @todo Code for additional_items class was deprecated and has been removed. Suggest following up in Sources if required.
		foreach ($section['areas'] as $i => $area)
		{
			// Not supposed to be printed?
			if (empty($area['label']))
				continue;

			echo '
								<li', !empty($area['subsections']) && empty($area['hide_subsections']) ? ' class="subsections"' : '', '>
									<a class="', $area['icon_class'], !empty($area['selected']) ? ' chosen ' : '', '" href="', (isset($area['url']) ? $area['url'] : $menu_context['base_url'] . ';area=' . $i), $menu_context['extra_parameters'], '">', $area['icon'], $area['label'], !empty($area['amt']) ? ' <span class="amt">' . $area['amt'] . '</span>' : '', '</a>';

			if (!empty($area['subsections']) && empty($area['hide_subsections']))
			{
				echo '
									<ul>';

				foreach ($area['subsections'] as $sa => $sub)
				{
					if (!empty($sub['disabled']))
						continue;

					$url = isset($sub['url']) ? $sub['url'] : (isset($area['url']) ? $area['url'] : $menu_context['base_url'] . ';area=' . $i) . ';sa=' . $sa;

					echo '
										<li>
											<a ', !empty($sub['selected']) ? 'class="chosen" ' : '', ' href="', $url, $menu_context['extra_parameters'], '">', $sub['label'], !empty($sub['amt']) ? ' <span class="amt">' . $sub['amt'] . '</span>' : '', '</a>
										</li>';
				}

				echo '
									</ul>';
			}

			echo '
								</li>';
		}
		echo '
							</ul>
						</li>';
	}

	echo '
					</ul><!-- .dropmenu -->
				</nav><!-- .generic_menu -->';
}

function template_generic_menu_tabs($menu_context)
{
	global $context, $settings, $scripturl, $txt;

	$context['tabs'] = $context['tabs'] ?? $menu_context['sections'][$menu_context['current_section']]['areas'][$menu_context['current_area']]['subsections'] ?? [];
	$tab_context = $menu_context['tab_data'];

	echo '
	<div id="display_head" class="information">
			<h2 class="display_title">';

	// Exactly how many tabs do we have?
	foreach ($context['tabs'] as $id => $tab)
	{
		// Can this not be accessed?
		if (!empty($tab['disabled']))
		{
			$tab_context['tabs'][$id]['disabled'] = true;
			continue;
		}

		// Did this not even exist - or do we not have a label?
		if (!isset($tab_context['tabs'][$id]))
			$tab_context['tabs'][$id] = array('label' => $tab['label']);
		elseif (!isset($tab_context['tabs'][$id]['label']))
			$tab_context['tabs'][$id]['label'] = $tab['label'];

		// Has a custom URL defined in the main admin structure?
		if (isset($tab['url']) && !isset($tab_context['tabs'][$id]['url']))
			$tab_context['tabs'][$id]['url'] = $tab['url'];
		// Any additional paramaters for the url?
		if (isset($tab['add_params']) && !isset($tab_context['tabs'][$id]['add_params']))
			$tab_context['tabs'][$id]['add_params'] = $tab['add_params'];
		// Has it been deemed selected?
		if (!empty($tab['is_selected']))
			$tab_context['tabs'][$id]['is_selected'] = true;
		// Does it have its own help?
		if (!empty($tab['help']))
			$tab_context['tabs'][$id]['help'] = $tab['help'];
		// Is this the last one?
		if (!empty($tab['is_last']) && !isset($tab_context['override_last']))
			$tab_context['tabs'][$id]['is_last'] = true;
	}

	// Find the selected tab
	foreach ($tab_context['tabs'] as $sa => $tab)
		if (!empty($tab['is_selected']) || (isset($menu_context['current_subsection']) && $menu_context['current_subsection'] == $sa))
		{
			$selected_tab = $tab;
			$tab_context['tabs'][$sa]['is_selected'] = true;
		}

	// Show a help item?
	if (!empty($selected_tab['icon_class']) || !empty($tab_context['icon_class']) || !empty($selected_tab['icon']) || !empty($tab_context['icon']) || !empty($selected_tab['help']) || !empty($tab_context['help']))
	{
		if (!empty($selected_tab['icon_class']) || !empty($tab_context['icon_class']))
			echo '
							<span class="', !empty($selected_tab['icon_class']) ? $selected_tab['icon_class'] : $tab_context['icon_class'], ' icon"></span>';
		elseif (!empty($selected_tab['icon']) || !empty($tab_context['icon']))
			echo '
							<img src="', $settings['images_url'], '/icons/', !empty($selected_tab['icon']) ? $selected_tab['icon'] : $tab_context['icon'], '" alt="" class="icon">';

		if (!empty($selected_tab['help']) || !empty($tab_context['help']))
			echo '
							<a href="', $scripturl, '?action=helpadmin;help=', !empty($selected_tab['help']) ? $selected_tab['help'] : $tab_context['help'], '" onclick="return reqOverlayDiv(this.href);" class="help"><span class="main_icons help" title="', $txt['help'], '"></span></a>';

	}
			echo $tab_context['title'];

	echo '
		</h2>
		', $selected_tab['description'] ?? $tab_context['description'], '
	</div>
		<ul class="button-group">';

		// Print out all the items in this tab.
		foreach ($tab_context['tabs'] as $sa => $tab)
		{
			if (!empty($tab['disabled']))
				continue;

			if (!empty($tab['is_selected']))
			{
				echo '
			<li>
				<a class="active button" href="', isset($tab['url']) ? $tab['url'] : $menu_context['base_url'] . ';area=' . $menu_context['current_area'] . ';sa=' . $sa, $menu_context['extra_parameters'], isset($tab['add_params']) ? $tab['add_params'] : '', '"><span class="firstlevel">', $tab['label'], '</span></a>
			</li>';
			}
			else
				echo '
			<li>
				<a class="button" href="', isset($tab['url']) ? $tab['url'] : $menu_context['base_url'] . ';area=' . $menu_context['current_area'] . ';sa=' . $sa, $menu_context['extra_parameters'], isset($tab['add_params']) ? $tab['add_params'] : '', '"><span class="firstlevel">', $tab['label'], '</span></a>
			</li>';
		}

		// the end of tabs
		echo '
		</ul>';
}

?>