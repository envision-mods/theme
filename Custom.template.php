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

/**
 * This actually displays the board index
 */
function template_boards(array $boards): void
{
	global $context, $txt, $scripturl;

		/* Each board in each category's boards has:
		new (is it new?), id, name, description, moderators (see below), link_moderators (just a list.),
		children (see below.), link_children (easier to use.), children_new (are they new?),
		topics (# of), posts (# of), link, href, and last_post. (see below.) */
		foreach ($boards as $board)
		{
			echo '
				<div id="board_', $board['id'], '" class="up_contain ', (!empty($board['css_class']) ? $board['css_class'] : ''), '">
					<div class="board_icon">
						', function_exists('template_bi_' . $board['type'] . '_icon') ? call_user_func('template_bi_' . $board['type'] . '_icon', $board) : template_bi_board_icon($board), '
					</div>
					<div class="info">
						', function_exists('template_bi_' . $board['type'] . '_info') ? call_user_func('template_bi_' . $board['type'] . '_info', $board) : template_bi_board_info($board), '
					</div><!-- .info -->';

			// Show some basic information about the number of posts, etc.
			echo '
						', function_exists('template_bi_' . $board['type'] . '_stats') ? call_user_func('template_bi_' . $board['type'] . '_stats', $board) : template_bi_board_stats($board), '
						', function_exists('template_bi_' . $board['type'] . '_lastpost') ? call_user_func('template_bi_' . $board['type'] . '_lastpost', $board) : template_bi_board_lastpost($board);

			// Won't somebody think of the children!
			if (function_exists('template_bi_' . $board['type'] . '_children'))
				call_user_func('template_bi_' . $board['type'] . '_children', $board);
			else
				template_bi_board_children($board);

			echo '
				</div><!-- #board_[id] -->';
		}
}

/**
 * Outputs the board icon for a standard board.
 *
 * @param array $board Current board information.
 */
function template_bi_board_icon($board)
{
	global $context, $scripturl;

	echo '
		<a href="', ($context['user']['is_guest'] ? $board['href'] : $scripturl . '?action=unread;board=' . $board['id'] . '.0;children'), '" class="board_', $board['board_class'], '"', !empty($board['board_tooltip']) ? ' title="' . $board['board_tooltip'] . '"' : '', '></a>';
}

/**
 * Outputs the board icon for a redirect.
 *
 * @param array $board Current board information.
 */
function template_bi_redirect_icon($board)
{
	global $context, $scripturl;

	echo '
		<a href="', $board['href'], '" class="board_', $board['board_class'], '"', !empty($board['board_tooltip']) ? ' title="' . $board['board_tooltip'] . '"' : '', '></a>';
}

/**
 * Outputs the board info for a standard board or redirect.
 *
 * @param array $board Current board information.
 */
function template_bi_board_info($board)
{
	global $context, $scripturl, $txt;

	echo '
		<a class="subject mobile_subject" href="', $board['href'], '" id="b', $board['id'], '">
			', $board['name'], '
		</a>';

	// Has it outstanding posts for approval?
	if ($board['can_approve_posts'] && ($board['unapproved_posts'] || $board['unapproved_topics']))
		echo '
		<a href="', $scripturl, '?action=moderate;area=postmod;sa=', ($board['unapproved_topics'] > 0 ? 'topics' : 'posts'), ';brd=', $board['id'], ';', $context['session_var'], '=', $context['session_id'], '" title="', sprintf($txt['unapproved_posts'], $board['unapproved_topics'], $board['unapproved_posts']), '" class="moderation_link amt">!</a>';

	echo '
		<div class="board_description">', $board['description'], '</div>';

	// Show the "Moderators: ". Each has name, href, link, and id. (but we're gonna use link_moderators.)
	if (!empty($board['link_moderators']))
		echo '
		<p class="moderators">', count($board['link_moderators']) == 1 ? $txt['moderator'] : $txt['moderators'], ': ', implode(', ', $board['link_moderators']), '</p>';
}

/**
 * Outputs the board stats for a standard board.
 *
 * @param array $board Current board information.
 */
function template_bi_board_stats($board)
{
	global $txt;

	echo '
		<p>
			', $txt['posts'], ': ', comma_format($board['posts']), '<br>', $txt['board_topics'], ': ', comma_format($board['topics']), '
		</p>';
}

/**
 * Outputs the board stats for a redirect.
 *
 * @param array $board Current board information.
 */
function template_bi_redirect_stats($board)
{
	global $txt;

	echo '
		<p>
			', $txt['redirects'], ': ', comma_format($board['posts']), '
		</p>';
}

/**
 * Outputs the board lastposts for a standard board or a redirect.
 * When on a mobile device, this may be hidden if no last post exists.
 *
 * @param array $board Current board information.
 */
function template_bi_board_lastpost($board)
{
	if (!empty($board['last_post']['id']))
					echo '
						<p>
						', $board['last_post']['link'], '
						', $GLOBALS['txt']['by'], ' ', $board['last_post']['member']['link'] , '<br />
						', timeformat((int) $board['last_post']['time']),'
						</p>';
}

/**
 * Outputs the board children for a standard board.
 *
 * @param array $board Current board information.
 */
function template_bi_board_children($board)
{
	global $txt, $scripturl, $context;

	// Show the "Child Boards: ". (there's a link_children but we're going to bold the new ones...)
	if (!empty($board['children']))
	{
		// Sort the links into an array with new boards bold so it can be imploded.
		$children = array();
		/* Each child in each board's children has:
			id, name, description, new (is it new?), topics (#), posts (#), href, link, and last_post. */
		foreach ($board['children'] as $child)
		{
			if (!$child['is_redirect'])
				$child['link'] = '' . ($child['new'] ? '<a href="' . $scripturl . '?action=unread;board=' . $child['id'] . '" title="' . $txt['new_posts'] . ' (' . $txt['board_topics'] . ': ' . comma_format($child['topics']) . ', ' . $txt['posts'] . ': ' . comma_format($child['posts']) . ')" class="new_posts">' . $txt['new'] . '</a> ' : '') . '<a href="' . $child['href'] . '" ' . ($child['new'] ? 'class="board_new_posts" ' : '') . 'title="' . ($child['new'] ? $txt['new_posts'] : $txt['old_posts']) . ' (' . $txt['board_topics'] . ': ' . comma_format($child['topics']) . ', ' . $txt['posts'] . ': ' . comma_format($child['posts']) . ')">' . $child['name'] . '</a>';
			else
				$child['link'] = '<a href="' . $child['href'] . '" title="' . comma_format($child['posts']) . ' ' . $txt['redirects'] . ' - ' . $child['short_description'] . '">' . $child['name'] . '</a>';

			// Has it posts awaiting approval?
			if ($child['can_approve_posts'] && ($child['unapproved_posts'] || $child['unapproved_topics']))
				$child['link'] .= ' <a href="' . $scripturl . '?action=moderate;area=postmod;sa=' . ($child['unapproved_topics'] > 0 ? 'topics' : 'posts') . ';brd=' . $child['id'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '" title="' . sprintf($txt['unapproved_posts'], $child['unapproved_topics'], $child['unapproved_posts']) . '" class="moderation_link amt">!</a>';

			$children[] = $child['new'] ? '<strong>' . $child['link'] . '</strong>' : $child['link'];
		}

		echo '
				<div><div>', implode('</div><div>', $children), '</div></div>';
	}
}