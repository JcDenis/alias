<?php

declare(strict_types=1);

namespace Dotclear\Plugin\alias\ClaudetteMcp;

use Dotclear\App;
use Dotclear\Plugin\alias\Alias;
use Dotclear\Plugin\ClaudetteMcp\McpTool;

/**
 * @brief       alias tool for the plugin ClaudetteMcp: list aliases.
 * @ingroup     alias
 *
 * ClaudetteMcp connects an AI assistant to the blog.
 * These classes are only loaded by ClaudetteMcp, which is not required by alias.
 *
 * @author      Jean-Christian Denis
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
class ListAliases extends McpTool
{
    public function name(): string
    {
        return 'list_aliases';
    }

    public function title(): string
    {
        return 'List URL aliases';
    }

    public function description(): string
    {
        return 'List the aliases of the blog addresses (plugin alias), in order: an address of the blog (url) which shows another one (destination), ' .
            'or redirects to it if "redirect" is true. An url between "/" is a regular expression. Addresses are relative to the blog address.';
    }

    /**
     * Like in backend: aliases are for blog administrators.
     */
    public function available(): bool
    {
        return $this->can(App::auth()::PERMISSION_ADMIN);
    }

    public function call(array $args): array
    {
        $aliases = [];
        foreach (Alias::getAliases() as $alias) {
            $aliases[] = [
                'url'         => $alias->url,
                'destination' => $alias->destination,
                'redirect'    => $alias->redirect,
            ];
        }

        return [
            'blog_url' => App::blog()->url(),
            'aliases'  => $aliases,
        ];
    }
}
