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
use dcUrlHandlers;
use Dotclear\Core\Process;
use Dotclear\Helper\Network\Http;

class Prepend extends Process
{
    public static function init(): bool
    {
        return self::status(My::checkContext(My::PREPEND));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        dcCore::app()->addBehavior('urlHandlerGetArgsDocument', function (dcUrlHandlers $handler): void {
            $found = $redir = false;
            $type  = '';
            $part  = $args = $_SERVER['URL_REQUEST_PART'];

            // load all Aliases
            foreach ((new Alias())->getAliases() as $v) {
                // multi alias using "/url/" to "destination"
                if (@preg_match('#^/.*/$#', $v['alias_url']) && @preg_match($v['alias_url'], $args)) {
                    $part  = preg_replace($v['alias_url'], $v['alias_destination'], $args);
                    $found = true;
                    $redir = !empty($v['alias_redirect']);

                    break;
                // single alias using "url" to "destination"
                } elseif ($v['alias_url'] == $args) {
                    $part  = $v['alias_destination'];
                    $found = true;
                    $redir = !empty($v['alias_redirect']);

                    break;
                }
            }

            // no URLs found
            if (!$found) {
                return;
            }

            // Use visible redirection
            if ($redir) {
                Http::redirect(dcCore::app()->blog->url . $part);
            }

            // regain URL type
            $_SERVER['URL_REQUEST_PART'] = $part;
            dcCore::app()->url->getArgs($part, $type, $args);

            // call real handler
            if (!$type) {
                dcCore::app()->url->callDefaultHandler($args);
            } else {
                dcCore::app()->url->callHandler($type, $args);
            }
        });

        return true;
    }
}
