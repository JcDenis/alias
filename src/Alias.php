<?php

declare(strict_types=1);

namespace Dotclear\Plugin\alias;

use Dotclear\App;
use Dotclear\Database\Cursor;
use Dotclear\Database\Statement\DeleteStatement;
use Dotclear\Database\Statement\SelectStatement;
use Exception;

/**
 * @brief       alias main class.
 * @ingroup     alias
 *
 * @author      Olivier Meunier (author)
 * @author      Jean-Christian Denis (latest)
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
class Alias
{
    /**
     * Alias table name.
     *
     * @var     string  ALIAS_TABLE_NAME
     */
    public const ALIAS_TABLE_NAME = 'alias';

    /**
     * Stack of aliases.
     *
     * @var     array<int, AliasRow>    $aliases
     */
    protected static array $aliases = [];

    /**
     * Open a database table cursor.
     *
     * @return  Cursor  The blog database table cursor
     */
    public static function openAliasCursor(): Cursor
    {
        return App::db()->con()->openCursor(App::db()->con()->prefix() . self::ALIAS_TABLE_NAME);
    }

    /**
     * Get aliases.
     *
     * @return  array<int, AliasRow>    Stack of aliases
     */
    public static function getAliases(): array
    {
        if (empty(self::$aliases)) {
            $sql = new SelectStatement();
            $rs  = $sql->from(App::db()->con()->prefix() . self::ALIAS_TABLE_NAME)
                ->columns([
                    'alias_url',
                    'alias_destination',
                    'alias_position',
                    'alias_redirect',
                ])
                ->where('blog_id = ' . $sql->quote(App::blog()->id()))
                ->order('alias_position ASC')
                ->select();

            if (!is_null($rs)) {
                $aliases = [];
                while ($rs->fetch()) {
                    $aliases[] = AliasRow::newFromRecord($rs);
                }
                self::$aliases = $aliases;
            }
        }

        return self::$aliases;
    }

    /**
     * Update aliases stack.
     *
     * @param   array<int, AliasRow>    $aliases    The alias stack
     */
    public static function updateAliases(array $aliases): void
    {
        foreach ($aliases as $row) {
            if (!is_a($row, AliasRow::class)) { // @phpstan-ignore-line
                throw new Exception(__('Invalid aliases definitions'));
            }
        }
        usort($aliases, fn ($a, $b) => $a->position <=> $b->position);

        App::db()->con()->begin();

        try {
            self::deleteAliases();
            foreach ($aliases as $k => $alias) {
                if (!empty($alias->url) && !empty($alias->destination)) {
                    self::createAlias(new AliasRow($alias->url, $alias->destination, $k + 1, $alias->redirect));
                }
            }

            App::db()->con()->commit();
        } catch (Exception $e) {
            App::db()->con()->rollback();

            throw $e;
        }
    }

    /**
     * Create an alias.
     *
     * @param   AliasRow    $alias  The new Alias descriptor
     */
    public static function createAlias(AliasRow $alias): void
    {
        if (!App::blog()->isDefined()) {
            return;
        }

        $url         = self::removeBlogUrl($alias->url);
        $destination = self::removeBlogUrl($alias->destination);

        if (empty($url)) {
            throw new Exception(__('Alias URL is empty.'));
        }
        if (empty($destination)) {
            throw new Exception(__('Alias destination is empty.'));
        }

        $cur = self::openAliasCursor();
        $cur->setField('blog_id', App::blog()->id());
        $cur->setField('alias_url', $url);
        $cur->setField('alias_destination', $destination);
        $cur->setField('alias_position', $alias->position);
        $cur->setField('alias_redirect', (int) $alias->redirect);
        $cur->insert();
    }

    /**
     * Delete an alias according to its URL.
     *
     * @param   string  $url    The alias URL
     */
    public static function deleteAlias(string $url): void
    {
        $sql = new DeleteStatement();
        $sql->from(App::db()->con()->prefix() . self::ALIAS_TABLE_NAME)
            ->where('blog_id = ' . $sql->quote(App::blog()->id()))
            ->and('alias_url = ' . $sql->quote($url))
            ->delete();
    }

    /**
     * Delete all aliases.
     */
    public static function deleteAliases(): void
    {
        $sql = new DeleteStatement();
        $sql->from(App::db()->con()->prefix() . self::ALIAS_TABLE_NAME)
            ->where('blog_id = ' . $sql->quote(App::blog()->id()))
            ->delete();
    }

    /**
     * Remove blog URL from alias URLs.
     *
     * @param   string  $url    The URL to clean
     *
     * @return  string The cleaned URL
     */
    public static function removeBlogUrl(string $url): string
    {
        return str_replace(App::blog()->url(), '', trim($url));
    }

    /**
     * Create Alias table.
     */
    public static function createTable(): bool
    {
        try {
            $s = App::db()->structure();
            $s->__get(self::ALIAS_TABLE_NAME)
                ->field('blog_id', 'varchar', 32, false)
                ->field('alias_url', 'varchar', 255, false)
                ->field('alias_destination', 'varchar', 255, false)
                ->field('alias_position', 'smallint', 0, false, 1)
                ->field('alias_redirect', 'smallint', 0, false, 0)

                ->primary('pk_alias', 'blog_id', 'alias_url')

                ->index('idx_alias_blog_id', 'btree', 'blog_id')
                ->index('idx_alias_blog_id_alias_position', 'btree', 'blog_id', 'alias_position')

                ->reference('fk_alias_blog', 'blog_id', 'blog', 'blog_id', 'cascade', 'cascade')
            ;

            App::db()->structure()->synchronize($s);

            return true;
        } catch (Exception $e) {
            App::error()->add($e->getMessage());

            return false;
        }
    }
}
