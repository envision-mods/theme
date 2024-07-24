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

/*	This template is, perhaps, the most important template in the theme. It
	contains the main template layer that displays the header and footer of
	the forum, namely with main_above and main_below. It also contains the
	menu sub template, which appropriately displays the menu; the init sub
	template, which is there to set the theme up; (init can be missing.) and
	the linktree sub template, which sorts out the link tree.

	The init sub template should load any data and set any hardcoded options.

	The main_above sub template is what is shown above the main content, and
	should contain anything that should be shown up there.

	The main_below sub template, conversely, is shown after the main content.
	It should probably contain the copyright statement and some other things.

	The linktree sub template should display the link tree, using the data
	in the $context['linktree'] variable.

	The menu sub template should display all the relevant buttons the user
	wants and or needs.

	For more information on the templating system, please see the site at:
	https://www.simplemachines.org/
*/

function fixBBC(&$codes)
{
	global $txt;

	for ($i = 0, $n = count($codes); $i < $n; $i++)
	{
		if ($codes[$i]['tag'] == 'code' && $codes[$i]['type'] == 'unparsed_equals_content')
		{
			$codes[$i]['content'] = '<div class="codeheader">' . $txt['code'] . ': ($2) <a href="#" onclick="return smfSelectText(this);" class="codeoperation">' . $txt['code_select'] . '</a></div><pre class="bbc_code">$1</pre>';
			$codes[$i]['validate'] = function(&$tag, &$data, $disabled) use ($codes, $i)
			{
				if (!isset($disabled['code']))
				{
					$codes[$i]['validate']($tag, $data, $disabled);
					$data[0] = '<div>' . str_replace('<br />', '</div><div>', $data) . '</div>';
				}
			};
		}
		if ($codes[$i]['tag'] == 'code' && $codes[$i]['type'] == 'unparsed_content')
		{
			$codes[$i]['content'] = '<div class="codeheader">' . $txt['code'] . ': <a href="#" onclick="return smfSelectText(this);" class="codeoperation">' . $txt['code_select'] . '</a></div><pre class="bbc_code">$1</pre>';
			$codes[$i]['validate'] = function(&$tag, &$data, $disabled) use ($codes, $i)
			{
				if (!isset($disabled['code']))
				{
					$codes[$i]['validate']($tag, $data, $disabled);
					$data = '<div>' . str_replace('<br />', '</div><div>', $data) . '</div>';
				}
			};
		}
	}
}

function fixSubTemplates($current_action)
{
	global $context;

	$new_sub_template = ($context['sub_template'] ?? 'main') . '_override';

	if (is_callable('template_' . $new_sub_template))
		$context['sub_template'] = $new_sub_template;
}

function template_init()
{
	global $settings, $txt;

	/* $context, $options and $txt may be available for use, but may not be fully populated yet. */

	// The version this template/theme is for. This should probably be the version of SMF it was created for.
	$settings['theme_version'] = '2.1';

	// Set the following variable to true if this theme requires the optional theme strings file to be loaded.
	$settings['require_theme_strings'] = true;

	// Set the following variable to true if this theme wants to display the avatar of the user that posted the last and the first post on the message index and recent pages.
	$settings['avatars_on_indexes'] = false;

	// Set the following variable to true if this theme wants to display the avatar of the user that posted the last post on the board index.
	$settings['avatars_on_boardIndex'] = false;
	
	// Define the Theme variants. 
	$settings['default_variant'] = 'green';
	$settings['theme_variants'] = array('blue', 'red', 'green');

	// Set the following variable to true if this theme wants to display the login and register buttons in the main forum menu.
	$settings['login_main_menu'] = true;

	// This defines the formatting for the page indexes used throughout the forum.
	$settings['page_index'] = array(
		'extra_before' => '<span class="pages">' . $txt['pages'] . '</span>',
		'previous_page' => '<span class="main_icons previous_page"></span>',
		'current_page' => '<span class="current_page">%1$d</span> ',
		'page' => '<a class="nav_page" href="{URL}">%2$s</a> ',
		'expand_pages' => '<span class="expand_pages" onclick="expandPages(this, {LINK}, {FIRST_PAGE}, {LAST_PAGE}, {PER_PAGE});"> ... </span>',
		'next_page' => '<span class="main_icons next_page"></span>',
		'extra_after' => '',
	);
	add_integration_function('integrate_bbc_codes', 'fixBBC', false);
	add_integration_function('integrate_menu_buttons', 'fixSubTemplates', false);

	// Allow css/js files to be disabled for this specific theme.
	// Add the identifier as an array key. IE array('smf_script'); Some external files might not add identifiers, on those cases SMF uses its filename as reference.
	if (!isset($settings['disable_files']))
		$settings['disable_files'] = array();
}

/**
 * The main sub template above the content.
 */
function template_html_above()
{
	global $context, $scripturl, $txt, $modSettings, $settings;

	// Show right to left, the language code, and the character set for ease of translating.
	echo '<!DOCTYPE html>
<html', $context['right_to_left'] ? ' dir="rtl"' : '', !empty($txt['lang_locale']) ? ' lang="' . str_replace("_", "-", substr($txt['lang_locale'], 0, strcspn($txt['lang_locale'], "."))) . '"' : '', '>
<head>
	<link rel="stylesheet" type="text/css" href="', $settings['theme_url'], '/css/menu.css" />
	<link rel="stylesheet" type="text/css" href="', $settings['theme_url'], '/css/icons.css" />
	<link rel="stylesheet" type="text/css" href="', $settings['theme_url'], '/css/main.css" />
	<link rel="stylesheet" type="text/css" href="', $settings['theme_url'], '/css/hljs.css" />
	<link rel="stylesheet" type="text/css" href="', $settings['theme_url'], '/css/button.css" />
	<link rel="stylesheet" type="text/css" href="', $settings['theme_url'], '/css/popup.css" />
	<link rel="stylesheet" type="text/css" href="', $settings['theme_url'], '/css/bbc.css" />
	<link rel="stylesheet" type="text/css" href="', $settings['theme_url'], '/css/sidebar.css" />
	<link rel="stylesheet" type="text/css" href="', $settings['theme_url'], '/css/form.css" />
	<meta charset="', $context['character_set'], '">';

	/*
		You don't need to manually load index.css, this will be set up for you.
		Note that RTL will also be loaded for you.
		To load other CSS and JS files you should use the functions
		loadCSSFile() and loadJavaScriptFile() respectively.
		This approach will let you take advantage of SMF's automatic CSS
		minimization and other benefits. You can, of course, manually add any
		other files you want after template_css() has been run.

	*	Short example:
			- CSS: loadCSSFile('filename.css', array('minimize' => true));
			- JS:  loadJavaScriptFile('filename.js', array('minimize' => true));
			You can also read more detailed usages of the parameters for these
			functions on the SMF wiki.

	*	Themes:
			The most efficient way of writing multi themes is to use a master
			index.css plus variant.css files. If you've set them up properly
			(through $settings['theme_variants']), the variant files will be loaded
			for you automatically.
			Additionally, tweaking the CSS for the editor requires you to include
			a custom 'jquery.sceditor.theme.css' file in the css folder if you need it.

	*	MODs:
			If you want to load CSS or JS files in here, the best way is to use the
			'integrate_load_theme' hook for adding multiple files, or using
			'integrate_pre_css_output', 'integrate_pre_javascript_output' for a single file.
	*/

	// load in any css from mods or themes so they can overwrite if wanted
	template_css();

	// load in any javascript files from mods and themes
	template_javascript();

	echo '
	<title>', $context['page_title_html_safe'], '</title>
	<meta name="viewport" content="width=device-width, initial-scale=1">';

	// Content related meta tags, like description, keywords, Open Graph stuff, etc...
	foreach ($context['meta_tags'] as $meta_tag)
	{
		echo '
	<meta';

		foreach ($meta_tag as $meta_key => $meta_value)
			echo ' ', $meta_key, '="', $meta_value, '"';

		echo '>';
	}

	/*	What is your Lollipop's color?
		Theme Authors, you can change the color here to make sure your theme's main color gets visible on tab */
	echo '
	<meta name="theme-color" content="#557EA0">';

	// Please don't index these Mr Robot.
	if (!empty($context['robot_no_index']))
		echo '
	<meta name="robots" content="noindex">';

	// Present a canonical url for search engines to prevent duplicate content in their indices.
	if (!empty($context['canonical_url']))
		echo '
	<link rel="canonical" href="', $context['canonical_url'], '">';

	// Show all the relative links, such as help, search, contents, and the like.
	echo '
	<link rel="help" href="', $scripturl, '?action=help">
	<link rel="contents" href="', $scripturl, '">', ($context['allow_search'] ? '
	<link rel="search" href="' . $scripturl . '?action=search">' : '');

	// If RSS feeds are enabled, advertise the presence of one.
	if (!empty($modSettings['xmlnews_enable']) && (!empty($modSettings['allow_guestAccess']) || $context['user']['is_logged']))
		echo '
	<link rel="alternate" type="application/rss+xml" title="', $context['forum_name_html_safe'], ' - ', $txt['rss'], '" href="', $scripturl, '?action=.xml;type=rss2', !empty($context['current_board']) ? ';board=' . $context['current_board'] : '', '">
	<link rel="alternate" type="application/atom+xml" title="', $context['forum_name_html_safe'], ' - ', $txt['atom'], '" href="', $scripturl, '?action=.xml;type=atom', !empty($context['current_board']) ? ';board=' . $context['current_board'] : '', '">';

	// If we're viewing a topic, these should be the previous and next topics, respectively.
	if (!empty($context['links']['next']))
		echo '
	<link rel="next" href="', $context['links']['next'], '">';

	if (!empty($context['links']['prev']))
		echo '
	<link rel="prev" href="', $context['links']['prev'], '">';

	// If we're in a board, or a topic for that matter, the index will be the board's index.
	if (!empty($context['current_board']))
		echo '
	<link rel="index" href="', $scripturl, '?board=', $context['current_board'], '.0">';

	// Output any remaining HTML headers. (from mods, maybe?)
	echo $context['html_headers'];

	echo '
</head>
<body id="', $context['browser_body_id'], '" class="action_', !empty($context['current_action']) ? $context['current_action'] : (!empty($context['current_board']) ?
		'messageindex' : (!empty($context['current_topic']) ? 'display' : 'home')), !empty($context['current_board']) ? ' board_' . $context['current_board'] : '', '">';
}

function template_wrapper_above()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings, $user_info;

	// If we have new PMs...
	if ($context['show_pm_popup'])
		echo '
	<div id="pm_popup" class="windowbg">
	<h5 class="catbg">
		<span class="floatright"><a href="#" onclick="closefPmPopup()"><img src="', $settings['images_url'], '/icons/quick_remove.gif" alt="', $txt['pm_popup_close'], '" /></a></span>
		', $txt['pm_popup'], ' [', $context['user']['unread_messages'], ']
	</h5>
	<p class="content">
		', str_replace('\n', '<br />', $txt['show_personal_messages']), '<br /><br />
		<a href="', $scripturl, '?action=pm" target="_blank" onclick="closefPmPopup()"><img src="', $settings['images_url'], '/icons/package_installed.gif" alt="" /> ', $txt['yes'], '</a> &nbsp;&nbsp;&nbsp; <a href="#" onclick="closefPmPopup()"><img src="', $settings['images_url'], '/icons/package_old.gif" alt="" /> ', $txt['no'], '</a>
	</p>
	</div>';

	echo '
<div id="wrapper">
<svg xmlns="http://www.w3.org/2000/svg" class=hidden> <symbol id="svg-link" viewBox="0 0 24 24"> <title>Link</title> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-link"> <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path> </svg> </symbol> <symbol id="svg-menu" viewBox="0 0 24 24"> <title>Menu</title> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-menu"> <line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line> </svg> </symbol> <symbol id="svg-arrow-right" viewBox="0 0 24 24"> <title>Expand</title> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-right"> <polyline points="9 18 15 12 9 6"></polyline> </svg> </symbol> <!-- Feather. MIT License: https://github.com/feathericons/feather/blob/master/LICENSE --> <symbol id="svg-external-link" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-external-link"> <title id="svg-external-link-title">(external link)</title> <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line> </symbol> <symbol id="svg-doc" viewBox="0 0 24 24"> <title>Document</title> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-file"> <path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path><polyline points="13 2 13 9 20 9"></polyline> </svg> </symbol> <symbol id="svg-search" viewBox="0 0 24 24"> <title>Search</title> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-search"> <circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line> </svg> </symbol> <!-- Bootstrap Icons. MIT License: https://github.com/twbs/icons/blob/main/LICENSE.md --> <symbol id="svg-copy" viewBox="0 0 16 16"> <title>Copy</title> <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-clipboard" viewBox="0 0 16 16"> <path d="M4 1.5H3a2 2 0 0 0-2 2V14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V3.5a2 2 0 0 0-2-2h-1v1h1a1 1 0 0 1 1 1V14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3.5a1 1 0 0 1 1-1h1v-1z"></path> <path d="M9.5 1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5h3zm-3-1A1.5 1.5 0 0 0 5 1.5v1A1.5 1.5 0 0 0 6.5 4h3A1.5 1.5 0 0 0 11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3z"></path> </svg> </symbol> <symbol id="svg-copied" viewBox="0 0 16 16"> <title>Copied</title> <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-clipboard-check-fill" viewBox="0 0 16 16"> <path d="M6.5 0A1.5 1.5 0 0 0 5 1.5v1A1.5 1.5 0 0 0 6.5 4h3A1.5 1.5 0 0 0 11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3Zm3 1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5h3Z"></path> <path d="M4 1.5H3a2 2 0 0 0-2 2V14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V3.5a2 2 0 0 0-2-2h-1v1A2.5 2.5 0 0 1 9.5 5h-3A2.5 2.5 0 0 1 4 2.5v-1Zm6.854 7.354-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 0 1 .708-.708L7.5 10.793l2.646-2.647a.5.5 0 0 1 .708.708Z"></path> </svg> </symbol> </svg>';

}

function template_wrapper_below()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings, $user_info;

	echo '
	</div>
    <footer id="footer">
      <svg class="footer-wave-svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 100" preserveAspectRatio="none">
        <path class="footer-wave-path" d="M851.8,100c125,0,288.3-45,348.2-64V0H0v44c3.7-1,7.3-1.9,11-2.9C80.7,22,151.7,10.8,223.5,6.3C276.7,2.9,330,4,383,9.8 c52.2,5.7,103.3,16.2,153.4,32.8C623.9,71.3,726.8,100,851.8,100z"></path>
      </svg>
      <div class="footer-content">
        <div>
          <div>
            <h2>Resources</h2>
            <ul>
              <li>
                <a target="_blank" rel="noopener noreferrer" href="https://github.com/envision-mods/portal"> Envision Portal on GitHub </a> <svg class="nav-list-link external" viewBox="0 0 24 24" aria-labelledby="svg-external-link-title"><use xlink:href="#svg-external-link"></use></svg>
              </li>
            </ul>
          </div>
          <div>
            <h2>Get Started</h2>
            <ul>
              <li>
                <a target="_blank" rel="noopener noreferrer" href="https://envision-mods.github.io/docs/">Documentation</a> <svg class="nav-list-link external" viewBox="0 0 24 24" aria-labelledby="svg-external-link-title"><use xlink:href="#svg-external-link"></use></svg>
              </li>
              <li>
                <a target="_blank" rel="noopener noreferrer" href="https://envision-mods.github.io/docs/installing.html">Installation</a> <svg class="nav-list-link external" viewBox="0 0 24 24" aria-labelledby="svg-external-link-title"><use xlink:href="#svg-external-link"></use></svg>
              </li>
            </ul>
          </div>
          <div>
            <h2>Other Sites</h2>
            <ul>
              <li>
                <a target="_blank" rel="noopener noreferrer" href="https://www.simplemachines.org/">Simple Machines</a> <svg class="nav-list-link external" viewBox="0 0 24 24" aria-labelledby="svg-external-link-title"><use xlink:href="#svg-external-link"></use></svg>
              </li>
              <li>
                <a target="_blank" rel="noopener noreferrer" href="https://mods.live627.com/">livemods</a> <svg class="nav-list-link external" viewBox="0 0 24 24" aria-labelledby="svg-external-link-title"><use xlink:href="#svg-external-link"></use></svg>
              </li>
            </ul>
          </div>
        </div>
        <div>
          <div>
            <h2>Quick Links</h2>
            <ul>
				<li><a href="', $scripturl, '?action=help">', $txt['help'], '</a>', !empty($modSettings['requireAgreement']) ? '|
				<li><a href="' . $scripturl . '?action=agreement">' . $txt['terms_and_rules'] . '</a></li>' : '', '
            </ul>
          </div>', !empty($modSettings['xmlnews_enable']) && (!empty($modSettings['allow_guestAccess']) || $context['user']['is_logged']) ? '
          <div>
            <h2>RSS</h2>
            <ul>
			<li><a href="' . $scripturl . '?action=.xml;type=rss;limit=15">Latest Posts</a></li>
			<li><a href="' . $scripturl . '?action=.xml;type=rss;sa=news;limit=15">Latest Topics</a></li>
			<li><a href="' . $scripturl . '?action=.xml;type=rss;sa=members;limit=15">Latest Members</a></li>
            </ul>
          </div>' : '', '
        </div>
	<div>
		<h2>' . $txt['hello_guest'] . ' ' . $context['user']['name'] . '</h2>';

	// If the user is logged in, display stuff like their name, new messages, etc.
	if ($context['user']['is_logged'])
	{
		echo '
						<ul>
							<li>
								<a href="', $scripturl, '?action=unread" title="', $txt['unread_since_visit'], '">', $txt['view_unread_category'], '</a>
							</li>
							<li>
								<a href="', $scripturl, '?action=unreadreplies" title="', $txt['show_unread_replies'], '">', $txt['unread_replies'], '</a>
							</li>
							<li>
			<a href="', $scripturl, '?action=profile;area=showposts">', $txt['posts'], ' (', $user_info['posts'], ')</a>
							</li>
							<li>
			<a href="', $scripturl, '?action=pm">', $txt['personal_messages'], ': ', $context['user']['messages'], ' (', $context['user']['unread_messages'] == 0 ? '' : '<b>', $context['user']['unread_messages'], ' ', $txt['new'], $context['user']['unread_messages'] == 0 ? '' : '</b>', ')</a></li>
				<li><a href="', $scripturl, '?action=profile;area=showalerts;u=', $context['user']['id'], '">', $txt['alerts'], !empty($context['user']['alerts']) ? ' (<b>' . $context['user']['alerts'] . '</b>)' : '', '</a></li>';

		// Are there any members waiting for approval?
		if (!empty($context['unapproved_members']))
			echo '
			<li><a href="', $scripturl, '?action=admin;area=viewmembers;sa=browse;type=approve">', $txt['approve_members_waiting'], ' (<b>', $context['unapproved_members'], '</b>)</a></li>';

		if (!empty($context['open_mod_reports']) && $context['show_open_reports'])
			echo '
			<li><a href="', $scripturl, '?action=moderate;area=reports">', sprintf($txt['mod_reports_waiting'], $context['open_mod_reports']), '</a></li>';
		echo '
						</ul>';
	}
	else
	{
		echo '
				<form action="', $scripturl, '?action=login2" method="post" accept-charset="UTF-8">
						<ul>
					<li><input type="text" name="user"></li>
					<li><input type="password" name="passwrd"></li>
					<li><input type="submit" value="', $txt['login'], '" class="button" /></li>
						</ul>
					<input type="hidden" name="hash_passwrd" value="" />
					<input type="hidden" name="cookielength" value="-1" />
					<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
				</form>';
	}

	echo '
		<h2>', $txt['search'], '</h2>
					<form action="', $scripturl, '?action=search2" method="post" accept-charset="', $context['character_set'], '">
						<ul>
						<li><input required type="text" name="search" title="', $txt['search'], '"></li>
					<li><input type="submit" value="', $txt['search'], '" class="button" /></li>
						</ul>';

	// Search within current topic?
	if (!empty($context['current_topic']))
		echo '			<input type="hidden" name="topic" value="', $context['current_topic'], '" />';

	// If we're on a certain board, limit it to this board ;).
	elseif (!empty($context['current_board']))
		echo '			<input type="hidden" name="brd[', $context['current_board'], ']" value="', $context['current_board'], '" />';

	echo '
					</form>
	</div>
      </div>
      <div class="footer-copyright">
			<ul>
				<li>', theme_copyright(), ' <svg class="nav-list-link external" viewBox="0 0 24 24" aria-labelledby="svg-external-link-title"><use xlink:href="#svg-external-link"></use></svg></li>
				<li><a href="#main">', $txt['go_up'], ' &#9650;</a></li>';

	// Show the load time?
	if ($context['show_load_time'])
		echo '
				<li>', sprintf($txt['page_created_full'], $context['load_time'], $context['load_queries']), '</li>';

	echo '
			</ul>
      </div>
    </footer>';
}

function template_main_above()
{
	echo '
	<div id="main">';
}

function template_main_below()
{
	echo '
	</div>';
}

function template_html_below()
{
	// Load in any javascipt that could be deferred to the end of the page
	template_javascript(true);

	echo '
	<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/10.7.1/highlight.min.js"></script>
	<script type="text/javascript"><!-- // --><![CDATA[
document.addEventListener(\'DOMContentLoaded\', () => {
  document.querySelectorAll(".bbc_code,.file_content").forEach((el) => {
    hljs.highlightElement(el);
  });
});
	// ]]></script>
</body>
</html>';
}

// Show a linktree. This is that thing that shows "My Community | General Category | General Discussion"..
function template_linktree_above()
{
	global $context, $settings, $options, $shown_linktree;

	echo '
	<div class="navigate_section" itemprop="breadcrumb">
		<ul>';

	// Each tree item has a URL and name. Some may have extra_before and extra_after.
	foreach ($context['linktree'] as $link_num => $tree)
	{
		echo '
			<li', ($link_num == count($context['linktree']) - 1) ? ' class="last"' : '', '>';

		// Show something before the link?
		if (isset($tree['extra_before']))
			echo $tree['extra_before'];

		// Show the link, including a URL if it should have one.
		echo isset($tree['url']) ? '
				<a href="' . $tree['url'] . '"><span>' . $tree['name'] . '</span></a>' : '<span>' . $tree['name'] . '</span>';

		// Show something after the link...?
		if (isset($tree['extra_after']))
			echo $tree['extra_after'];

		// Don't show a separator for the last one.
		if ($link_num != count($context['linktree']) - 1)
			echo ' »';

		echo '
			</li>';
	}
	echo '
		</ul>
	</div>';

	$shown_linktree = true;
}

function template_linktree_below()
{
}

function theme_linktree($force_show = false)
{
}

// Show the menu up top. Something like [home] [help] [profile] [logout]...
function template_menu_above()
{
	global $context, $settings, $options, $scripturl, $txt;

	echo '<header class="site-header">
				<a href="', $scripturl, '">', empty($context['header_logo_url_html_safe']) ? $context['forum_name'] : '<img src="' . $context['header_logo_url_html_safe'] . '" alt="' . $context['forum_name'] . '" />', '</a>
			<ul class="menu">';

	foreach ($context['menu_buttons'] as $act => $button)
	{
		echo '
				<li id="button_', $act, '">
					<a class="', $button['active_button'] ? 'active ' : '', '" href="', $button['href'], '"', isset($button['target']) ? ' target="' . $button['target'] . '"' : '', '>', $button['title'], '</a>';

		if (!empty($button['sub_buttons']))
		{
			echo '
					<ul>';

			foreach ($button['sub_buttons'] as $childbutton)
			{
				echo '
						<li>
							<a href="', $childbutton['href'], '"', isset($childbutton['target']) ? ' target="' . $childbutton['target'] . '"' : '', '>', $childbutton['title'], !empty($childbutton['sub_buttons']) ? '...' : '', '</a>';

				// 3rd level menus :)
				if (!empty($childbutton['sub_buttons']))
				{
					echo '
							<ul>';

					foreach ($childbutton['sub_buttons'] as $grandchildbutton)
						echo '
								<li>
									<a href="', $grandchildbutton['href'], '"', isset($grandchildbutton['target']) ? ' target="' . $grandchildbutton['target'] . '"' : '', '>', $grandchildbutton['title'], '</a>
								</li>';

					echo '
						</ul>';
				}

				echo '
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
</header>';
}

function template_menu_below()
{
}

function template_button_strip($button_strip, $direction = '', $strip_options = array())
{
	global $context, $txt;

	if (!is_array($strip_options))
		$strip_options = array();

	// Create the buttons...
	$buttons = array();
	foreach ($button_strip as $key => $value)
	{
		// As of 2.1, the 'test' for each button happens while the array is being generated. The extra 'test' check here is deprecated but kept for backward compatibility (update your mods, folks!)
		if (!isset($value['test']) || !empty($context[$value['test']]))
		{
			if (!isset($value['id']))
				$value['id'] = $key;

			$button = '
				<a class="button button_strip_' . $key . (!empty($value['active']) ? ' active' : '') . (isset($value['class']) ? ' ' . $value['class'] : '') . '" ' . (!empty($value['url']) ? 'href="' . $value['url'] . '"' : '') . ' ' . (isset($value['custom']) ? ' ' . $value['custom'] : '') . '>'.(!empty($value['icon']) ? '<span class="main_icons '.$value['icon'].'"></span>' : '').'' . $txt[$value['text']] . '</a>';

			if (!empty($value['sub_buttons']))
			{
				$button .= '
					<div class="top_menu dropmenu ' . $key . '_dropdown" style="display:none;width: 480px;">
						<div class="viewport">
							<div class="overview">
					<ul class=menu>';
				foreach ($value['sub_buttons'] as $element)
				{
					if (isset($element['test']) && empty($context[$element['test']]))
						continue;

					$button .= '
								<li style="line-height:1.5"><a href="' . $element['url'] . '">' . $txt[$element['text']];
					if (isset($txt[$element['text'] . '_desc']))
						$button .= '<div class=smalltext>' . $txt[$element['text'] . '_desc'] . '</div>';
					$button .= '</a></li>';
				}
				$button .= '
							</ul>
							</div><!-- .overview -->
						</div><!-- .viewport -->
					</div><!-- .top_menu -->';
			}

			$buttons[] = $button;
		}
	}

	// No buttons? No button strip either.
	if (empty($buttons))
		return;

	echo '
		<div class="buttonlist', !empty($direction) ? ' float' . $direction : '', '"', (empty($buttons) ? ' style="display: none;"' : ''), (!empty($strip_options['id']) ? ' id="' . $strip_options['id'] . '"' : ''), '>
			', implode('', $buttons), '
		</div>';
}

/**
 * Generate a list of quickbuttons.
 *
 * @param array $list_items An array with info for displaying the strip
 * @param string $list_class Used for integration hooks and as a class name
 * @param string $output_method The output method. If 'echo', simply displays the buttons, otherwise returns the HTML for them
 * @return void|string Returns nothing unless output_method is something other than 'echo'
 */
function template_quickbuttons($list_items, $list_class = null, $output_method = 'echo')
{
	global $txt;

	// Enable manipulation with hooks
	if (!empty($list_class))
		call_integration_hook('integrate_' . $list_class . '_quickbuttons', array(&$list_items));

	// Make sure the list has at least one shown item
	foreach ($list_items as $key => $li)
	{
		// Is there a sublist, and does it have any shown items
		if ($key == 'more')
		{
			foreach ($li as $subkey => $subli)
				if (isset($subli['show']) && !$subli['show'])
					unset($list_items[$key][$subkey]);

			if (empty($list_items[$key]))
				unset($list_items[$key]);
		}
		// A normal list item
		elseif (isset($li['show']) && !$li['show'])
			unset($list_items[$key]);
	}

	// Now check if there are any items left
	if (empty($list_items))
		return;

	// Print the quickbuttons
	$output = '
		<ul class="quickbuttons' . (!empty($list_class) ? ' quickbuttons_' . $list_class : '') . '">';

	// This is used for a list item or a sublist item
	$list_item_format = function($li)
	{
		$html = '
			<li' . (!empty($li['class']) ? ' class="' . $li['class'] . '"' : '') . (!empty($li['id']) ? ' id="' . $li['id'] . '"' : '') . (!empty($li['custom']) ? ' ' . $li['custom'] : '') . '>';

		if (isset($li['content']))
			$html .= $li['content'];
		else
			$html .= '
				<a href="' . (!empty($li['href']) ? $li['href'] : 'javascript:void(0);') . '"' . (!empty($li['javascript']) ? ' ' . $li['javascript'] : '') . '>
					' . (!empty($li['icon']) ? '<span class="main_icons ' . $li['icon'] . '"></span>' : '') . (!empty($li['label']) ? $li['label'] : '') . '
				</a>';

		$html .= '
			</li>';

		return $html;
	};

	foreach ($list_items as $key => $li)
	{
		// Handle the sublist
		if ($key == 'more')
		{
			$output .= '
			<li class="post_options">
				<a href="javascript:void(0);">' . $txt['post_options'] . '</a>
				<ul>';

			foreach ($li as $subli)
				$output .= $list_item_format($subli);

			$output .= '
				</ul>
			</li>';
		}
		// Ordinary list item
		else
			$output .= $list_item_format($li);
	}

	$output .= '
		</ul><!-- .quickbuttons -->';

	// There are a few spots where the result needs to be returned
	if ($output_method == 'echo')
		echo $output;
	else
		return $output;
}

/**
 * The upper part of the maintenance warning box
 */
function template_maint_warning_above()
{
	global $txt, $context, $scripturl;

	echo '
	<div class="errorbox" id="errors">
		<dl>
			<dt>
				<strong id="error_serious">', $txt['forum_in_maintenance'], '</strong>
			</dt>
			<dd class="error" id="error_list">
				', sprintf($txt['maintenance_page'], $scripturl . '?action=admin;area=serversettings;' . $context['session_var'] . '=' . $context['session_id']), '
			</dd>
		</dl>
	</div>';
}

/**
 * The lower part of the maintenance warning box.
 */
function template_maint_warning_below()
{

}

?>