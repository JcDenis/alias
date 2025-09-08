<?php

declare(strict_types=1);

namespace Dotclear\Plugin\alias;

use Dotclear\App;
use Dotclear\Core\Frontend\Url;
use Dotclear\Helper\Process\TraitProcess;
use Dotclear\Helper\Network\Http;

/**
 * @brief       alias frontend class.
 * @ingroup     alias
 *
 * @author      Olivier Meunier (author)
 * @author      Jean-Christian Denis (latest)
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
class Frontend
{
    use TraitProcess;

    public static function init(): bool
    {
        return self::status(My::checkContext(My::FRONTEND));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        App::behavior()->addBehavior('urlHandlerGetArgsDocument', function (Url $handler): void {
            $found = $redir = false;
            $type  = '';
            $part  = $args = $_SERVER['URL_REQUEST_PART'];

            // load all Aliases
            foreach (Alias::getAliases() as $alias) {
                // multi alias using "/url/" to "destination"
                if (@preg_match('#^/.*/$#', $alias->url) && @preg_match($alias->url, $args)) {
                    $part  = preg_replace($alias->url, $alias->destination, $args);
                    $found = true;
                    $redir = !empty($alias->redirect);

                    break;
                    // single alias using "url" to "destination"
                } elseif ($alias->url == $args) {
                    $part  = $alias->destination;
                    $found = true;
                    $redir = !empty($alias->redirect);

                    break;
                }
            }

            // no URLs found
            if (!$found) {
                return;
            }

            // Use visible redirection
            if ($redir) {
                Http::redirect(App::blog()->url() . $part);
            }

            // regain URL type
            $_SERVER['URL_REQUEST_PART'] = $part;
            $handler->getArgs($part, $type, $args);

            // call real handler
            if (!$type) {
                $handler->callDefaultHandler($args);
            } else {
                $handler->callHandler($type, $args);
            }
            exit;
        });

        return true;
    }
}
