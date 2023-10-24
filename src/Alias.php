<?php

declare(strict_types=1);

namespace Dotclear\Plugin\alias;

use Dotclear\App;
use Dotclear\Database\Statement\{
    DeleteStatement,
    SelectStatement
};
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
    protected array $aliases = [];

    /**
     * Get aliases.
     *
     * @return  array<int, AliasRow>    Stack of aliases
     */
    public function getAliases(): array
    {
        if (!empty($this->aliases)) {
            return $this->aliases;
        }

        $sql = new SelectStatement();
        $rs  = $sql->from(App::con()->prefix() . Alias::ALIAS_TABLE_NAME)
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
            while ($rs->fetch()) {
                $this->aliases[] = AliasRow::newFromRecord($rs);
            }
        }

        return $this->aliases;
    }

    /**
     * Update aliases stack.
     *
     * @param   array<int, AliasRow>    $aliases    The alias stack
     */
    public function updateAliases(array $aliases): void
    {
        foreach ($aliases as $row) {
            if (!is_a($row, AliasRow::class)) {
                throw new Exception(__('Invalid aliases definitions'));
            }
        }
        usort($aliases, fn ($a, $b) => $a->position <=> $b->position);

        App::con()->begin();

        try {
            $this->deleteAliases();
            foreach ($aliases as $k => $alias) {
                if (!empty($alias->url) && !empty($alias->destination)) {
                    $this->createAlias(new AliasRow($alias->url, $alias->destination, $k + 1, $alias->redirect));
                }
            }

            App::con()->commit();
        } catch (Exception $e) {
            App::con()->rollback();

            throw $e;
        }
    }

    /**
     * Create an alias.
     *
     * @param   AliasRow    $alias  The new Alias descriptor
     */
    public function createAlias(AliasRow $alias):void
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

        $cur = App::con()->openCursor(App::con()->prefix() . Alias::ALIAS_TABLE_NAME);
        $cur->setField('blog_id', App::blog()->id());
        $cur->setField('alias_url', $url);
        $cur->setField('alias_destination',$destination);
        $cur->setField('alias_position', $alias->position);
        $cur->setField('alias_redirect', (int) $alias->redirect);
        $cur->insert();
    }

    /**
     * Delete an alias according to its URL.
     *
     * @param   string  $url    The alias URL
     */
    public function deleteAlias(string $url): void
    {
        $sql = new DeleteStatement();
        $sql->from(App::con()->prefix() . Alias::ALIAS_TABLE_NAME)
            ->where('blog_id = ' . $sql->quote(App::blog()->id()))
            ->and('alias_url = ' . $sql->quote($url))
            ->delete();
    }

    /**
     * Delete all aliases.
     */
    public function deleteAliases(): void
    {
        $sql = new DeleteStatement();
        $sql->from(App::con()->prefix() . Alias::ALIAS_TABLE_NAME)
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
}
