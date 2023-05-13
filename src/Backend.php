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
declare(strict_types=1);

namespace Dotclear\Plugin\alias;

use dcAdmin;
use dcCore;
use dcNsProcess;
use dcPage;
use Dotclear\Plugin\importExport\FlatBackupItem;
use Dotclear\Plugin\importExport\FlatExport;
use Dotclear\Plugin\importExport\FlatImportV2;

class Backend extends dcNsProcess
{
    public static function init(): bool
    {
        static::$init = defined('DC_CONTEXT_ADMIN')
            && !is_null(dcCore::app()->auth) && !is_null(dcCore::app()->blog) //nullsafe PHP < 8.0
            && dcCore::app()->auth->check(dcCore::app()->auth->makePermissions([
                dcCore::app()->auth::PERMISSION_ADMIN,
            ]), dcCore::app()->blog->id);

        return static::$init;
    }

    public static function process(): bool
    {
        if (!static::$init) {
            return false;
        }

        // nullsafe PHP < 8.0
        if (is_null(dcCore::app()->auth) || is_null(dcCore::app()->blog) || is_null(dcCore::app()->adminurl)) {
            return false;
        }

        dcCore::app()->menu[dcAdmin::MENU_PLUGINS]->addItem(
            My::name(),
            dcCore::app()->adminurl->get('admin.plugin.' . My::id()),
            dcPage::getPF(My::id() . '/icon.svg'),
            preg_match('/' . preg_quote(dcCore::app()->adminurl->get('admin.plugin.' . My::id())) . '(&.*)?$/', $_SERVER['REQUEST_URI']),
            dcCore::app()->auth->check(dcCore::app()->auth->makePermissions([dcCore::app()->auth::PERMISSION_ADMIN]), dcCore::app()->blog->id)
        );

        dcCore::app()->addBehaviors([
            'exportFullV2' => function (FlatExport $exp): void {
                $exp->exportTable(My::ALIAS_TABLE_NAME);
            },
            'exportSingleV2' => function (FlatExport $exp, ?string $blog_id): void {
                $exp->export(
                    'alias',
                    'SELECT alias_url, alias_destination, alias_position ' .
                    'FROM ' . dcCore::app()->prefix . My::ALIAS_TABLE_NAME . ' A ' .
                    "WHERE A.blog_id = '" . $blog_id . "'"
                );
            },
            'importInitV2' => function (FlatImportV2 $bk): void {
                $bk->cur_alias = dcCore::app()->con->openCursor(dcCore::app()->prefix . My::ALIAS_TABLE_NAME);
                $bk->alias     = new Alias();
                $bk->aliases   = $bk->alias->getAliases();
            },
            'importFullV2' => function (bool|FlatBackupItem $line, FlatImportV2 $bk): void {
                if ($line->__name == My::ALIAS_TABLE_NAME) {
                    $bk->cur_alias->clean();
                    $bk->cur_alias->setField('blog_id', (string) $line->blog_id);
                    $bk->cur_alias->setField('alias_url', (string) $line->alias_url);
                    $bk->cur_alias->setField('alias_destination', (string) $line->alias_destination);
                    $bk->cur_alias->setField('alias_position', (int) $line->alias_position);
                    $bk->cur_alias->insert();
                }
            },
            'importSingleV2' => function (bool|FlatBackupItem $line, FlatImportV2 $bk): void {
                if ($line->__name == My::ALIAS_TABLE_NAME) {
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
            },
        ]);

        return true;
    }
}
