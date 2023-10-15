<?php

declare(strict_types=1);

namespace Dotclear\Plugin\alias;

use Dotclear\App;
use Dotclear\Core\Process;
use Dotclear\Plugin\importExport\{
    FlatBackupItem,
    FlatExport,
    FlatImportV2
};

/**
 * @brief       alias backend class.
 * @ingroup     alias
 *
 * @author      Olivier Meunier (author)
 * @author      Jean-Christian Denis (latest)
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
class Backend extends Process
{
    public static function init(): bool
    {
        return self::status(My::checkContext(My::BACKEND));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        My::addBackendMenuItem();

        App::behavior()->addBehaviors([
            'exportFullV2' => function (FlatExport $exp): void {
                $exp->exportTable(Alias::ALIAS_TABLE_NAME);
            },
            'exportSingleV2' => function (FlatExport $exp, ?string $blog_id): void {
                $exp->export(
                    'alias',
                    'SELECT alias_url, alias_destination, alias_position ' .
                    'FROM ' . App::con()->prefix() . Alias::ALIAS_TABLE_NAME . ' A ' .
                    "WHERE A.blog_id = '" . $blog_id . "'"
                );
            },
            'importInitV2' => function (FlatImportV2 $bk): void {
                $bk->cur_alias = App::con()->openCursor(App::con()->prefix() . Alias::ALIAS_TABLE_NAME);
                $bk->alias     = new Alias();
                $bk->aliases   = $bk->alias->getAliases();
            },
            'importFullV2' => function (/*bool|FlatBackupItem */$line, FlatImportV2 $bk): void {
                if ($line->__name == Alias::ALIAS_TABLE_NAME) {
                    $bk->cur_alias->clean();
                    $bk->cur_alias->setField('blog_id', (string) $line->blog_id);
                    $bk->cur_alias->setField('alias_url', (string) $line->alias_url);
                    $bk->cur_alias->setField('alias_destination', (string) $line->alias_destination);
                    $bk->cur_alias->setField('alias_position', (int) $line->alias_position);
                    $bk->cur_alias->insert();
                }
            },
            'importSingleV2' => function (/*bool|FlatBackupItem */$line, FlatImportV2 $bk): void {
                if ($line->__name == Alias::ALIAS_TABLE_NAME) {
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
