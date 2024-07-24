<?php

function template_ManagePaid_init()
{
	global $settings;

	require_once $settings['default_theme_dir'] . '/ManagePaid.template.php';
}

function template_user_subscription_override()
{
	global $context, $txt, $scripturl, $modSettings;

	echo '
	<div id="paid_subscription">
		<div class="cat_bar">
			<h3 class="catbg">', $txt['subscriptions'], '</h3>
		</div>';

	if (empty($context['subscriptions']))
		echo '
		<div class="information">
			', $txt['paid_subs_none'], '
		</div>';
	else
	{
		echo '
		<div class="information">
			', $txt['paid_subs_desc'], '
		</div>
		<form action="', $scripturl, '?action=profile;u=', $context['id_member'], ';area=subscriptions;confirm" method="post" class="windowbg">';

		// Print out all the subscriptions.
		foreach ($context['subscriptions'] as $id => $subscription)
		{
			// Ignore the inactive ones...
			if (empty($subscription['active']))
				continue;

			echo '
			<div>
				<p><strong>', $subscription['name'], '</strong></p>
				<p class="smalltext">', $subscription['desc'], '</p>';

			if (!$subscription['flexible'])
				echo '
				<div><strong>', $txt['paid_duration'], ':</strong> ', $subscription['length'], '</div>';

			if ($context['user']['is_owner'])
			{
				echo '
				<strong>', $txt['paid_cost'], ':</strong>';

				if ($subscription['flexible'])
				{
					echo '
				<select name="cur[', $subscription['id'], ']">';

					// Print out the costs for this one.
					foreach ($subscription['costs'] as $duration => $value)
						echo '
					<option value="', $duration, '">', sprintf($modSettings['paid_currency_symbol'], $value), '/', $txt[$duration], '</option>';

					echo '
				</select>';
				}
				else
					echo '
				', sprintf($modSettings['paid_currency_symbol'], $subscription['costs']['fixed']);

				echo '
				<hr>
				<input type="submit" name="sub_id[', $subscription['id'], ']" value="', $txt['paid_order'], '" class="button">';
			}
			else
				echo '
				<a href="', $scripturl, '?action=admin;area=paidsubscribe;sa=modifyuser;sid=', $subscription['id'], ';uid=', $context['member']['id'], (empty($context['current'][$subscription['id']]) ? '' : ';lid=' . $context['current'][$subscription['id']]['id']), '">', empty($context['current'][$subscription['id']]) ? $txt['paid_admin_add'] : $txt['paid_edit_subscription'], '</a>';

			echo '
			</div><!-- .windowbg -->';
		}
			echo '
		</form>';
	}

	echo '
		<br class="clear">
		<div class="cat_bar">
			<h3 class="catbg">', $txt['paid_current'], '</h3>
		</div>
		<div class="information">
			', $txt['paid_current_desc'], '
		</div>
		<table class="table_grid">
			<thead>
				<tr class="title_bar">
					<th style="width: 30%">', $txt['paid_name'], '</th>
					<th>', $txt['paid_status'], '</th>
					<th>', $txt['start_date'], '</th>
					<th>', $txt['end_date'], '</th>
				</tr>
			</thead>
			<tbody>';

	if (empty($context['current']))
		echo '
				<tr class="windowbg">
					<td colspan="4">
						', $txt['paid_none_yet'], '
					</td>
				</tr>';

	foreach ($context['current'] as $sub)
	{
		if (!$sub['hide'])
			echo '
				<tr class="windowbg">
					<td>
						', ($context['user']['is_admin'] ? '<a href="' . $scripturl . '?action=admin;area=paidsubscribe;sa=modifyuser;lid=' . $sub['id'] . '">' . $sub['name'] . '</a>' : $sub['name']), '
					</td>
					<td>
						<span style="color: ', ($sub['status'] == 2 ? 'green' : ($sub['status'] == 1 ? 'red' : 'orange')), '"><strong>', $sub['status_text'], '</strong></span>
					</td>
					<td>', $sub['start'], '</td>
					<td>', $sub['end'], '</td>
				</tr>';
	}

	echo '
			</tbody>
		</table>
	</div><!-- #paid_subscription -->';
}
