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

if (isset($_POST['form_sent']))
{

	$form = array(
		'regs_allow'			=> isset($_POST['form']['regs_allow']) ? '1' : '0',
		'regs_verify'			=> isset($_POST['form']['regs_verify']) ? '1' : '0',
		'regs_report'			=> isset($_POST['form']['regs_report']) ? '1' : '0',
	);

	foreach ($form as $key => $input)
	{
		// Only update values that have changed
		if (array_key_exists('o_'.$key, $pun_config) && $pun_config['o_'.$key] != $input)
		{
			if ($input != '' || is_int($input))
				$value = '\''.$db->escape($input).'\'';
			else
				$value = 'NULL';

			$db->query('UPDATE '.$db->prefix.'config SET conf_value='.$value.' WHERE conf_name=\'o_'.$db->escape($key).'\'') or error('Unable to update board config', __FILE__, __LINE__, $db->error());
		}
	}

	// Regenerate the config cache
	if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
		require FORUM_ROOT.'include/cache.php';

	generate_config_cache();
	clear_feed_cache();

	redirect('backstage/settings.php', $lang['Options updated redirect']);
}

$page_title = array(pun_htmlspecialchars($pun_config['o_board_title']), $lang['Admin'], $lang['Registration']);
define('FORUM_ACTIVE_PAGE', 'admin');
require FORUM_ROOT.'backstage/header.php';
generate_admin_menu('global');

?>
<h2><?php echo $lang['Registration'] ?></h2>
<form class="form-horizontal" method="post" action="settings.php">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title"><?php echo $lang['Registration subhead'] ?><span class="pull-right"><input class="btn btn-primary" type="submit" name="save" value="<?php echo $lang['Save changes'] ?>" /></span></h3>
        </div>
        <div class="panel-body">
            <fieldset>
                <div class="form-group">
                    <label class="col-sm-2 control-label"><?php echo $lang['Allow new label'] ?></label>
                    <div class="col-sm-10">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="form[regs_allow]" value="1" <?php if ($pun_config['o_regs_allow'] == '1') echo ' checked="checked"' ?> />
                                <?php echo $lang['Allow new help'] ?>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label"><?php echo $lang['Verify label'] ?></label>
                    <div class="col-sm-10">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="form[regs_verify]" value="1" <?php if ($pun_config['o_regs_verify'] == '1') echo ' checked="checked"' ?> />
                                <?php echo $lang['Verify help'] ?>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label"><?php echo $lang['Report new label'] ?></label>
                    <div class="col-sm-10">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="form[regs_report]" value="1" <?php if ($pun_config['o_regs_report'] == '1') echo ' checked="checked"' ?> />
                                <?php echo $lang['Report new help'] ?>
                            </label>
                        </div>
                    </div>
                </div>
                <hr />
                <div class="form-group">
                    <label class="col-sm-2 control-label"><?php echo $lang['Use rules label'] ?></label>
                    <div class="col-sm-10">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="form[rules]" value="1" <?php if ($pun_config['o_rules'] == '1') echo ' checked="checked"' ?> />
                                <?php echo $lang['Use rules help'] ?>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label"><?php echo $lang['Rules label'] ?></label>
                    <div class="col-sm-10">
                        <textarea class="form-control" name="form[rules_message]" rows="10" cols="55"><?php echo pun_htmlspecialchars($pun_config['o_rules_message']) ?></textarea>
						<span class="help-block"><?php echo $lang['Rules help'] ?></span>
                    </div>
                </div>
                <hr />
                <div class="form-group">
                    <label class="col-sm-2 control-label"><?php echo $lang['E-mail default label'] ?></label>
                    <div class="col-sm-10">
                        <span class="help-block"><?php echo $lang['E-mail default help'] ?></span>
                        <div class="radio">
                            <label>
                                <input type="radio" name="form[default_email_setting]" id="form_default_email_setting_0" value="0"<?php if ($pun_config['o_default_email_setting'] == '0') echo ' checked="checked"' ?> />
                                <?php echo $lang['Display e-mail label'] ?>
                            </label>
                        </div>
                        <div class="radio">
                            <label>
                                <input type="radio" name="form[default_email_setting]" id="form_default_email_setting_1" value="1"<?php if ($pun_config['o_default_email_setting'] == '1') echo ' checked="checked"' ?> />
                                <?php echo $lang['Hide allow form label'] ?>
                            </label>
                        </div>
                        <div class="radio">
                            <label>
                                <input type="radio" name="form[default_email_setting]" id="form_default_email_setting_2" value="2"<?php if ($pun_config['o_default_email_setting'] == '2') echo ' checked="checked"' ?> />
                                <?php echo $lang['Hide both label'] ?>
                            </label>
                        </div>
                    </div>
                </div>
                <hr />
                <div class="form-group">
                    <label class="col-sm-2 control-label"><?php echo $lang['Antispam API label'] ?></label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" name="form[antispam_api]" size="35" maxlength="50" value="<?php echo pun_htmlspecialchars($pun_config['o_antispam_api']) ?>" />
                        <span class="help-block"><?php printf($lang['Antispam API help'], '<a href="http://stopforumspam.com/keys">StopForumSpam.com</a>') ?></span>
                    </div>
                </div>
            </fieldset>
        </div>
    </div>
</form>
<?php

require FORUM_ROOT.'backstage/footer.php';
