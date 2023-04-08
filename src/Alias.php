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
use Exception;

class Alias
{
    protected array $aliases;

    public function __construct()
    {
    }

    public function getAliases(): array
    {
        if (!empty($this->aliases)) {
            return $this->aliases;
        }

        $this->aliases = [];
        $sql           = 'SELECT alias_url, alias_destination, alias_position ' .
                'FROM ' . dcCore::app()->prefix . My::ALIAS_TABLE_NAME . ' ' .
                "WHERE blog_id = '" . dcCore::app()->con->escapeStr((string) dcCore::app()->blog->id) . "' " .
                'ORDER BY alias_position ASC ';
        $this->aliases = dcCore::app()->con->select($sql)->rows();

        return $this->aliases;
    }

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
                    $this->createAlias($v['alias_url'], $v['alias_destination'], $k + 1);
                }
            }

            dcCore::app()->con->commit();
        } catch (Exception $e) {
            dcCore::app()->con->rollback();

            throw $e;
        }
    }

    public function createAlias(string $url, string $destination, int $position): void
    {
        if (!$url) {
            throw new Exception(__('Alias URL is empty.'));
        }

        if (!$destination) {
            throw new Exception(__('Alias destination is empty.'));
        }

        $cur = dcCore::app()->con->openCursor(dcCore::app()->prefix . My::ALIAS_TABLE_NAME);
        $cur->setField('blog_id', (string) dcCore::app()->blog->id);
        $cur->setField('alias_url', (string) $url);
        $cur->setField('alias_destination', (string) $destination);
        $cur->setField('alias_position', abs((int) $position));
        $cur->insert();
    }

    public function deleteAlias(string $url): void
    {
        dcCore::app()->con->execute(
            'DELETE FROM ' . dcCore::app()->prefix . My::ALIAS_TABLE_NAME . ' ' .
            "WHERE blog_id = '" . dcCore::app()->con->escapeStr((string) dcCore::app()->blog->id) . "' " .
            "AND alias_url = '" . dcCore::app()->con->escapeStr((string) $url) . "' "
        );
    }

    public function deleteAliases(): void
    {
        dcCore::app()->con->execute(
            'DELETE FROM ' . dcCore::app()->prefix . My::ALIAS_TABLE_NAME . ' ' .
            "WHERE blog_id = '" . dcCore::app()->con->escapeStr((string) dcCore::app()->blog->id) . "' "
        );
    }
}
