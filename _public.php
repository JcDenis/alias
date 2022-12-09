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

dcCore::app()->url->register('alias', '', '^(.*)$', function ($args) {
    $o       = new dcAliases();
    $aliases = $o->getAliases();
    $part    = $args;

    foreach ($aliases as $v) {
        if (@preg_match('#^/.*/$#', $v['alias_url']) && @preg_match($v['alias_url'], $args)) {
            $part = preg_replace($v['alias_url'], $v['alias_destination'], $args);

            break;
        } elseif ($v['alias_url'] == $args) {
            $part = $v['alias_destination'];

            break;
        }
    }

    dcCore::app()->url->unregister('alias');
    dcCore::app()->url->getArgs($part, $type, $args);

    if (!$type) {
        dcCore::app()->url->callDefaultHandler($args);
    } else {
        dcCore::app()->url->callHandler($type, $args);
    }
});
