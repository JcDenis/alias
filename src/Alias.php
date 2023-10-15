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
     * @var     array<int, array<string, string>>   $aliases
     */
    protected array $aliases = [];

    /**
     * Get aliases.
     *
     * @return  array<int, array<string, string>>   Stack of aliases
     */
    public function getAliases(): array
    {
        if (!empty($this->aliases)) {
            return $this->aliases;
        }

        if (!App::blog()->isDefined()) {
            return [];
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

        $this->aliases = is_null($rs) ? [] : $rs->rows();

        return $this->aliases;
    }

    /**
     * Update aliases stack.
     *
     * @param   array<int, array<string, string>>   $aliases    The alias stack
     */
    public function updateAliases(array $aliases): void
    {
        usort($aliases, fn ($a, $b) => (int) $a['alias_position'] <=> (int) $b['alias_position']);
        foreach ($aliases as $v) {
            if (!isset($v['alias_url']) || !isset($v['alias_destination'])) {
                throw new Exception(__('Invalid aliases definitions'));
            }
        }

        App::con()->begin();

        try {
            $this->deleteAliases();
            foreach ($aliases as $k => $v) {
                if (!empty($v['alias_url']) && !empty($v['alias_destination'])) {
                    $this->createAlias($v['alias_url'], $v['alias_destination'], $k + 1, !empty($v['alias_redirect']));
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
     * @param   string  $url            The URL
     * @param   string  $destination    The destination
     * @param   int     $position       The position
     * @param   bool    $redirect       Do redirection
     */
    public function createAlias(string $url, string $destination, int $position, bool $redirect): void
    {
        if (!App::blog()->isDefined()) {
            return;
        }

        $url         = self::removeBlogUrl($url);
        $destination = self::removeBlogUrl($destination);

        if (empty($url)) {
            throw new Exception(__('Alias URL is empty.'));
        }
        if (empty($destination)) {
            throw new Exception(__('Alias destination is empty.'));
        }

        $cur = App::con()->openCursor(App::con()->prefix() . Alias::ALIAS_TABLE_NAME);
        $cur->setField('blog_id', App::blog()->id());
        $cur->setField('alias_url', (string) $url);
        $cur->setField('alias_destination', (string) $destination);
        $cur->setField('alias_position', abs((int) $position));
        $cur->setField('alias_redirect', (int) $redirect);
        $cur->insert();
    }

    /**
     * Delete an alias according to its URL.
     *
     * @param   string  $url    The alias URL
     */
    public function deleteAlias(string $url): void
    {
        if (!App::blog()->isDefined()) {
            return;
        }

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
        if (!App::blog()->isDefined()) {
            return;
        }

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
        return App::blog()->isDefined() ? str_replace(App::blog()->url(), '', trim($url)) : trim($url);
    }
}
