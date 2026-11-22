<?php

declare(strict_types=1);

namespace Dotclear\Plugin\alias\ClaudetteMcp;

use Dotclear\App;
use Dotclear\Plugin\alias\Alias;
use Dotclear\Plugin\alias\AliasRow;
use Dotclear\Plugin\ClaudetteMcp\McpTool;
use Dotclear\Plugin\ClaudetteMcp\McpToolException;
use Exception;

/**
 * @brief       alias tool for the plugin ClaudetteMcp: add an alias.
 * @ingroup     alias
 *
 * Only simple aliases: a regular expression could catch many addresses of the blog,
 * including the one of the connector. Regular expressions stay in the admin page. No deletion.
 *
 * @author      Jean-Christian Denis
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
class AddAlias extends McpTool
{
    public function name(): string
    {
        return 'add_alias';
    }

    /**
     * Family of tools for the keys of ClaudetteMcp (string, not constant: older ClaudetteMcp versions have none).
     */
    public function family(): string
    {
        return 'extras';
    }

    public function title(): string
    {
        return 'Add an URL alias';
    }

    public function description(): string
    {
        return 'Add an alias of a blog address (plugin alias), last in the list: visitors of "url" see the page at "destination", ' .
            'or are redirected to it if "redirect" is true (better for an address which changed). ' .
            'Addresses are relative to the blog address (see list_aliases), ex: "old-post" to "post/2024/01/01/New-post". ' .
            'Regular expressions are not allowed here. Aliases can not be deleted here. Only do this when the user explicitly asks for it.';
    }

    public function properties(): array
    {
        return [
            'url' => [
                'type'        => 'string',
                'description' => 'The alias address, relative to the blog address.',
            ],
            'destination' => [
                'type'        => 'string',
                'description' => 'The address of the page to show, relative to the blog address.',
            ],
            'redirect' => [
                'type'        => 'boolean',
                'description' => 'Redirect visitors to the destination instead of showing it at the alias address. Default: true.',
            ],
        ];
    }

    public function required(): array
    {
        return ['url', 'destination'];
    }

    public function readOnly(): bool
    {
        return false;
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
        $url         = ltrim(Alias::removeBlogUrl($this->str($args, 'url')), '/');
        $destination = ltrim(Alias::removeBlogUrl($this->str($args, 'destination')), '/');

        if ($url === '' || $destination === '') {
            throw new McpToolException('An alias needs an address and a destination');
        }
        if (str_ends_with($url, '/') || preg_match('#^[a-z][a-z0-9+.-]*://#i', $url . $destination)) {
            throw new McpToolException('Use addresses relative to the blog address, without regular expression');
        }
        if ($url === $destination) {
            throw new McpToolException('The alias and its destination are the same address');
        }
        // The address of the connector itself
        if (preg_match('#^api(/|$)#', $url)) {
            throw new McpToolException('This address can not have an alias');
        }

        $aliases = Alias::getAliases();

        try {
            Alias::createAlias(new AliasRow($url, $destination, count($aliases) + 1, $this->bool($args, 'redirect', true)));
        } catch (Exception $e) {
            throw new McpToolException($e->getMessage());
        }

        return [
            'url'         => $url,
            'destination' => $destination,
            'redirect'    => $this->bool($args, 'redirect', true),
            'address'     => App::blog()->url() . $url,
        ];
    }
}
