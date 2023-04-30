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
use dcUrlHandlers;
use Dotclear\Helper\Network\Http;

class Prepend extends dcNsProcess
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

        dcCore::app()->addBehavior('urlHandlerGetArgsDocument', function (dcUrlHandlers $handler): void {
            $found = $redir = false;
            $type  = 'alias';
            $part  = $args = $_SERVER['URL_REQUEST_PART'];

            foreach ((new Alias())->getAliases() as $v) {
                if (@preg_match('#^/.*/$#', $v['alias_url']) && @preg_match($v['alias_url'], $args)) {
                    $part  = preg_replace($v['alias_url'], $v['alias_destination'], $args);
                    $found = true;
                    $redir = !empty($v['alias_redirect']);

                    break;
                } elseif ($v['alias_url'] == $args) {
                    $part  = $v['alias_destination'];
                    $found = true;
                    $redir = !empty($v['alias_redirect']);

                    break;
                }
            }

            if (!$found) {
                return;
            }

            if ($redir) {
                Http::redirect(dcCore::app()->blog->url . $part);
            }

            $_SERVER['URL_REQUEST_PART'] = $part;
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
