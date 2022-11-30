<?php
/**
 * @brief alias, a plugin for Dotclear 2
 *
 * @package Dotclear
 * @subpackage Plugin
 *
 * @author Olivier Meunier and contributors
 *
 * @copyright Jean-Christian Denis
 * @copyright GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
if (!defined('DC_CONTEXT_ADMIN')) {
    return null;
}

dcCore::app()->menu[dcAdmin::MENU_PLUGINS]->addItem(
    __('Aliases'),
    dcCore::app()->adminurl->get('admin.plugin.alias'),
    dcPage::getPF('alias/icon.png'),
    preg_match('/' . preg_quote(dcCore::app()->adminurl->get('admin.plugin.alias')) . '(&.*)?$/', $_SERVER['REQUEST_URI']),
    dcCore::app()->auth->check(dcCore::app()->auth->makePermissions([dcAuth::PERMISSION_ADMIN]), dcCore::app()->blog->id)
);

dcCore::app()->addBehavior('exportFullV2', function ($exp) {
    $exp->exportTable('alias');
});

dcCore::app()->addBehavior('exportSingleV2', function ($exp, $blog_id) {
    $exp->export(
        'alias',
        'SELECT alias_url, alias_destination, alias_position ' .
        'FROM ' . dcCore::app()->prefix . 'alias A ' .
        "WHERE A.blog_id = '" . $blog_id . "'"
    );
});

dcCore::app()->addBehavior('importInitV2', function ($bk) {
    $bk->cur_alias = dcCore::app()->con->openCursor(dcCore::app()->prefix . 'alias');
    $bk->alias     = new dcAliases();
    $bk->aliases   = $bk->alias->getAliases();
});

dcCore::app()->addBehavior('importFullV2', function ($line, $bk) {
    if ($line->__name == 'alias') {
        $bk->cur_alias->clean();

        $bk->cur_alias->blog_id           = (string) $line->blog_id;
        $bk->cur_alias->alias_url         = (string) $line->alias_url;
        $bk->cur_alias->alias_destination = (string) $line->alias_destination;
        $bk->cur_alias->alias_position    = (int) $line->alias_position;

        $bk->cur_alias->insert();
    }
});

dcCore::app()->addBehavior('importSingleV2', function ($line, $bk) {
    if ($line->__name == 'alias') {
        $found = false;
        foreach ($bk->aliases as $v) {
            if ($v['alias_url'] == $line->alias_url) {
                $found = true;
            }
        }
        if ($found) {
            $bk->alias->deleteAlias($line->alias_url);
        }
        $bk->alias->createAlias($line->alias_url, $line->alias_destination, $line->alias_position);
    }
});
