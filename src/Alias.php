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
if (!defined('DC_RC_PATH')) {
    return null;
}

class dcAliases
{
    protected $aliases;

    public function __construct()
    {
    }

    public function getAliases()
    {
        if (is_array($this->aliases)) {
            return $this->aliases;
        }

        $this->aliases = [];
        $sql           = 'SELECT alias_url, alias_destination, alias_position ' .
                'FROM ' . dcCore::app()->prefix . initAlias::ALIAS_TABLE_NAME . ' ' .
                "WHERE blog_id = '" . dcCore::app()->con->escapeStr((string) dcCore::app()->blog->id) . "' " .
                'ORDER BY alias_position ASC ';
        $this->aliases = dcCore::app()->con->select($sql)->rows();

        return $this->aliases;
    }

    public function updateAliases($aliases)
    {
        usort($aliases, [$this,'sortCallback']);
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

    public function createAlias($url, $destination, $position)
    {
        if (!$url) {
            throw new Exception(__('Alias URL is empty.'));
        }

        if (!$destination) {
            throw new Exception(__('Alias destination is empty.'));
        }

        $cur                    = dcCore::app()->con->openCursor(dcCore::app()->prefix . initAlias::ALIAS_TABLE_NAME);
        $cur->blog_id           = (string) dcCore::app()->blog->id;
        $cur->alias_url         = (string) $url;
        $cur->alias_destination = (string) $destination;
        $cur->alias_position    = abs((int) $position);
        $cur->insert();
    }

    public function deleteAlias($url)
    {
        dcCore::app()->con->execute(
            'DELETE FROM ' . dcCore::app()->prefix . initAlias::ALIAS_TABLE_NAME . ' ' .
            "WHERE blog_id = '" . dcCore::app()->con->escapeStr((string) dcCore::app()->blog->id) . "' " .
            "AND alias_url = '" . dcCore::app()->con->escapeStr((string) $url) . "' "
        );
    }

    public function deleteAliases()
    {
        dcCore::app()->con->execute(
            'DELETE FROM ' . dcCore::app()->prefix . initAlias::ALIAS_TABLE_NAME . ' ' .
            "WHERE blog_id = '" . dcCore::app()->con->escapeStr((string) dcCore::app()->blog->id) . "' "
        );
    }

    protected function sortCallback($a, $b)
    {
        if ($a['alias_position'] == $b['alias_position']) {
            return 0;
        }

        return $a['alias_position'] < $b['alias_position'] ? -1 : 1;
    }
}
