<?php
/**
 * @brief alias, a plugin for Dotclear 2
 *
 * @package Dotclear
 * @subpackage Plugin
 *
 * @author Olivier Meunier and contributors
 *
 * @copyright Jean-Crhistian Denis
 * @copyright GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
if (!defined('DC_RC_PATH')) {
    return null;
}

dcCore::app()->url->register('alias', '', '^(.*)$', ['urlAlias','alias']);

class urlAlias extends dcUrlHandlers
{
    public static function alias($args)
    {
        $o       = new dcAliases();
        $aliases = $o->getAliases();

        foreach ($aliases as $v) {
            if (@preg_match('#^/.*/$#', $v['alias_url']) && @preg_match($v['alias_url'], $args)) {
                self::callAliasHandler(preg_replace($v['alias_url'], $v['alias_destination'], $args));

                return;
            } elseif ($v['alias_url'] == $args) {
                self::callAliasHandler($v['alias_destination']);

                return;
            }
        }

        self::callAliasHandler($args);
    }

    public static function callAliasHandler($part)
    {
        dcCore::app()->url->unregister('alias');
        dcCore::app()->url->getArgs($part, $type, $args);

        if (!$type) {
            dcCore::app()->url->callDefaultHandler($args);
        } else {
            dcCore::app()->url->callHandler($type, $args);
        }
    }
}
