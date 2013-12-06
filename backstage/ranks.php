<?php

/**
 * Copyright (C) 2013 ModernBB
 * Based on code by FluxBB copyright (C) 2008-2012 FluxBB
 * Based on code by Rickard Andersson copyright (C) 2002-2008 PunBB
 * License: http://www.gnu.org/licenses/gpl.html GPL version 3 or higher
 */

// Tell header.php to use the admin template
define('FORUM_ADMIN_CONSOLE', 1);

define('FORUM_ROOT', '../');
require FORUM_ROOT.'include/common.php';
require FORUM_ROOT.'include/common_admin.php';

if (!$pun_user['is_admmod']) {
    header("Location: ../login.php");
}

if ($pun_user['g_id'] != FORUM_ADMIN)
	message($lang['No permission'], false, '403 Forbidden');

// Add a rank
if (isset($_POST['add_rank']))
{
	$rank = pun_trim($_POST['new_rank']);
	$min_posts = pun_trim($_POST['new_min_posts']);

	if ($rank == '')
		message($lang['Must enter title message']);

	if ($min_posts == '' || preg_match('%[^0-9]%', $min_posts))
		message($lang['Must be integer message']);

	// Make sure there isn't already a rank with the same min_posts value
	$result = $db->query('SELECT 1 FROM '.$db->prefix.'ranks WHERE min_posts='.$min_posts) or error('Unable to fetch rank info', __FILE__, __LINE__, $db->error());
	if ($db->num_rows($result))
		message(sprintf($lang['Dupe min posts message'], $min_posts));

	$db->query('INSERT INTO '.$db->prefix.'ranks (rank, min_posts) VALUES(\''.$db->escape($rank).'\', '.$min_posts.')') or error('Unable to add rank', __FILE__, __LINE__, $db->error());

	// Regenerate the ranks cache
	if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
		require FORUM_ROOT.'include/cache.php';

	generate_ranks_cache();

	redirect('backstage/ranks.php', $lang['Rank added redirect']);
}


// Update a rank
else if (isset($_POST['update']))
{
	$id = intval(key($_POST['update']));

	$rank = pun_trim($_POST['rank'][$id]);
	$min_posts = pun_trim($_POST['min_posts'][$id]);

	if ($rank == '')
		message($lang['Must enter title message']);

	if ($min_posts == '' || preg_match('%[^0-9]%', $min_posts))
		message($lang['Must be integer message']);

	// Make sure there isn't already a rank with the same min_posts value
	$result = $db->query('SELECT 1 FROM '.$db->prefix.'ranks WHERE id!='.$id.' AND min_posts='.$min_posts) or error('Unable to fetch rank info', __FILE__, __LINE__, $db->error());
	if ($db->num_rows($result))
		message(sprintf($lang['Dupe min posts message'], $min_posts));

	$db->query('UPDATE '.$db->prefix.'ranks SET rank=\''.$db->escape($rank).'\', min_posts='.$min_posts.' WHERE id='.$id) or error('Unable to update rank', __FILE__, __LINE__, $db->error());

	// Regenerate the ranks cache
	if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
		require FORUM_ROOT.'include/cache.php';

	generate_ranks_cache();

	redirect('backstage/ranks.php', $lang['Rank updated redirect']);
}


// Remove a rank
else if (isset($_POST['remove']))
{
	$id = intval(key($_POST['remove']));

	$db->query('DELETE FROM '.$db->prefix.'ranks WHERE id='.$id) or error('Unable to delete rank', __FILE__, __LINE__, $db->error());

	// Regenerate the ranks cache
	if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
		require FORUM_ROOT.'include/cache.php';

	generate_ranks_cache();

	redirect('backstage/ranks.php', $lang['Rank removed redirect']);
}

$page_title = array(pun_htmlspecialchars($pun_config['o_board_title']), $lang['Admin'], $lang['Ranks']);
$focus_element = array('ranks', 'new_rank');
define('FORUM_ACTIVE_PAGE', 'admin');
require FORUM_ROOT.'backstage/header.php';
	generate_admin_menu('ranks');

?>
<h2><?php echo $lang['Ranks head'] ?></h2>
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title"><?php echo $lang['Add rank subhead'] ?></h3>
    </div>
    <div class="panel-body">
        <form id="ranks" method="post" action="ranks.php">
            <fieldset>
                <p><?php echo $lang['Add rank info'].' '.($pun_config['o_ranks'] == '1' ? sprintf($lang['Ranks enabled'], '<a href="features.php">'.$lang['Features'].'</a>') : sprintf($lang['Ranks disabled'], '<a href="features.php">'.$lang['Features'].'</a>')) ?></p>
                <table class="table">
                    <thead>
                        <tr>
                            <th class="col-lg-4"><?php echo $lang['Rank title label'] ?></th>
                            <th class="col-lg-4"><?php echo $lang['Minimum posts label'] ?></th>
                            <th class="col-lg-4"><?php echo $lang['Actions label'] ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><input type="text" class="form-control" name="new_rank" size="24" maxlength="50" tabindex="1" /></td>
                            <td><input type="text" class="form-control" name="new_min_posts" size="7" maxlength="7" tabindex="2" /></td>
                            <td><input class="btn btn-primary" type="submit" name="add_rank" value="<?php echo $lang['Add'] ?>" tabindex="3" /></td>
                        </tr>
                    </tbody>
                </table>
            </fieldset>
        </form>
    </div>
</div>
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title"><?php echo $lang['Edit remove subhead'] ?></h3>
    </div>
    <div class="panel-body">
        <form id="ranks" method="post" action="ranks.php">
            <fieldset>
<?php

$result = $db->query('SELECT id, rank, min_posts FROM '.$db->prefix.'ranks ORDER BY min_posts') or error('Unable to fetch rank list', __FILE__, __LINE__, $db->error());
if ($db->num_rows($result))
{

?>
                <table class="table">
                    <thead>
                        <tr>
                            <th class="col-lg-4"><?php echo $lang['Rank title label'] ?></th>
                            <th class="col-lg-4"><?php echo $lang['Minimum posts label'] ?></th>
                            <th class="col-lg-4"><?php echo $lang['Actions label'] ?></th>
                        </tr>
                    </thead>
                    <tbody>
<?php

	while ($cur_rank = $db->fetch_assoc($result))
		echo "\t\t\t\t\t\t\t\t".'<tr><td><input type="text" class="form-control" name="rank['.$cur_rank['id'].']" value="'.pun_htmlspecialchars($cur_rank['rank']).'" size="24" maxlength="50" /></td><td><input type="text" class="form-control" name="min_posts['.$cur_rank['id'].']" value="'.$cur_rank['min_posts'].'" size="7" maxlength="7" /></td><td><input class="btn btn-primary" type="submit" name="update['.$cur_rank['id'].']" value="'.$lang['Update'].'" /><input class="btn btn-danger" type="submit" name="remove['.$cur_rank['id'].']" value="'.$lang['Remove'].'" /></td></tr>'."\n";

?>
                    </tbody>
                </table>
<?php

}
else
	echo "\t\t\t\t\t\t\t".'<p>'.$lang['No ranks in list'].'</p>'."\n";

?>
            </fieldset>
        </form>
    </div>
</div>
<?php

require FORUM_ROOT.'backstage/footer.php';
