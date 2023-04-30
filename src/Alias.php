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

use dcCore;
use Dotclear\Database\Statement\{
    DeleteStatement,
    SelectStatement
};
use Exception;

/**
 * plugin Alias main class
 */
class Alias
{
    /** @var    array   $aliases    Stak of aliases */
    protected array $aliases = [];

    /**
     * Get aliases.
     *
     * @return  array   Stack of aliases
     */
    public function getAliases(): array
    {
        if (!empty($this->aliases)) {
            return $this->aliases;
        }

        // nullsafe PHP < 8.0
        if (is_null(dcCore::app()->blog)) {
            return [];
        }

        $sql = new SelectStatement();
        $rs  = $sql->from(dcCore::app()->prefix . My::ALIAS_TABLE_NAME)
            ->columns([
                'alias_url',
                'alias_destination',
                'alias_position',
                'alias_redirect'
            ])
            ->where('blog_id = ' . $sql->quote((string) dcCore::app()->blog->id))
            ->order('alias_position ASC')
            ->select();

        $this->aliases = is_null($rs) ? [] : $rs->rows();

        return $this->aliases;
    }

    /**
     * Update aliases stack.
     *
     * @param   array   $aliases    The alias stack
     */
    public function updateAliases(array $aliases): void
    {
        usort($aliases, fn ($a, $b) => (int) $a['alias_position'] <=> (int) $b['alias_position']);
        foreach ($aliases as $v) {
            if (!isset($v['alias_url']) || !isset($v['alias_destination'])) {
                throw new Exception(__('Invalid aliases definitions'));
            }
        }

        dcCore::app()->con->begin();

        try {
            $this->deleteAliases();
            foreach ($aliases as $k => $v) {
                if (!empty($v['alias_url']) && !empty($v['alias_destination'])) {
                    $this->createAlias($v['alias_url'], $v['alias_destination'], $k + 1, !empty($v['alias_redirect']));
                }
            }

            dcCore::app()->con->commit();
        } catch (Exception $e) {
            dcCore::app()->con->rollback();

            throw $e;
        }
    }

    /**
     * Create an alias.
     *
     * @param   string  $url            The URL
     * @param   string  $destination    The destination
     * @param   int     $position       The position
     * @param   bool    $position       Do redirection
     */
    public function createAlias(string $url, string $destination, int $position, bool $redirect): void
    {
        // nullsafe PHP < 8.0
        if (is_null(dcCore::app()->blog)) {
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

        $cur = dcCore::app()->con->openCursor(dcCore::app()->prefix . My::ALIAS_TABLE_NAME);
        $cur->setField('blog_id', (string) dcCore::app()->blog->id);
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
        // nullsafe PHP < 8.0
        if (is_null(dcCore::app()->blog)) {
            return;
        }

        $sql = new DeleteStatement();
        $sql->from(dcCore::app()->prefix . My::ALIAS_TABLE_NAME)
            ->where('blog_id = ' . $sql->quote((string) dcCore::app()->blog->id))
            ->and('alias_url = ' . $sql->quote($url))
            ->delete();
    }

    /**
     * Delete all aliases.
     */
    public function deleteAliases(): void
    {
        // nullsafe PHP < 8.0
        if (is_null(dcCore::app()->blog)) {
            return;
        }

        $sql = new DeleteStatement();
        $sql->from(dcCore::app()->prefix . My::ALIAS_TABLE_NAME)
            ->where('blog_id = ' . $sql->quote((string) dcCore::app()->blog->id))
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
        return str_replace(dcCore::app()->blog->url, '', trim($url));
    }
}
