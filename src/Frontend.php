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
use dcNsProcess;

class Frontend extends dcNsProcess
{
    public static function init(): bool
    {
        static::$init = defined('DC_RC_PATH');

        return static::$init;
    }

    public static function process(): bool
    {
        if (!static::$init) {
            return false;
        }

        dcCore::app()->url->register('alias', '', '^(.*)$', function (string $args): void {
            $part = $args;

            foreach ((new Alias())->getAliases() as $v) {
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

        return true;
    }
}
