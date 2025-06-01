<?php

declare(strict_types=1);

namespace Dotclear\Plugin\alias;

use Dotclear\App;
use Dotclear\Database\Statement\SelectStatement;
use Dotclear\Plugin\importExport\{ FlatBackupItem, FlatExport, FlatImportV2 };

/**
 * @brief       alias plugin importExport features class.
 * @ingroup     alias
 *
 * @author      Olivier Meunier (author)
 * @author      Jean-Christian Denis (latest)
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
class PluginImportExportBehaviors
{
    public static function exportFullV2(FlatExport $exp): void
    {
        $exp->exportTable(Alias::ALIAS_TABLE_NAME);
    }

    public static function exportSingleV2(FlatExport $exp, ?string $blog_id): void
    {
        $sql = new SelectStatement();
        $sql->columns(['alias_url', 'alias_destination', 'alias_position', 'alias_redirect'])
            ->from($sql->as(App::con()->prefix() . Alias::ALIAS_TABLE_NAME, 'A'))
            ->where('blog_id = ' . $sql->quote((string) $blog_id));

        $exp->export('alias', $sql->statement());
    }

    public static function importInitV2(FlatImportV2 $bk): void
    {
        $bk->__set('cur_alias', Alias::openAliasCursor());

        $bk->__set('aliases', Alias::getAliases());
    }

    public static function importFullV2(bool|FlatBackupItem $line, FlatImportV2 $bk): void
    {
        if (!is_bool($line) && $line->__name == Alias::ALIAS_TABLE_NAME) {
            $bk->__get('cur_alias')->clean();
            $bk->__get('cur_alias')->setField('blog_id', (string) $line->f('blog_id'));
            $bk->__get('cur_alias')->setField('alias_url', (string) $line->f('alias_url'));
            $bk->__get('cur_alias')->setField('alias_destination', (string) $line->f('alias_destination'));
            $bk->__get('cur_alias')->setField('alias_position', (int) $line->f('alias_position'));
            $bk->__get('cur_alias')->setField('alias_redirect', (int) $line->f('alias_redirect'));
            $bk->__get('cur_alias')->insert();
        }
    }

    public static function importSingleV2(bool|FlatBackupItem $line, FlatImportV2 $bk): void
    {
        if (!is_bool($line) && $line->__name == Alias::ALIAS_TABLE_NAME) {
            $found = false;
            foreach ($bk->__get('aliases') as $alias) {
                if ($alias->url == $line->f('alias_url')) {
                    $found = true;
                }
            } 
            if ($found) {
                Alias::deleteAlias($line->f('alias_url'));
            }
            Alias::createAlias(new AliasRow($line->f('alias_url'), $line->f('alias_destination'), (int) $line->f('alias_position'), (bool) $line->f('alias_redirect')));
        }
    }
}
