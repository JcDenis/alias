<?php

declare(strict_types=1);

namespace Dotclear\Plugin\alias;

use Dotclear\App;
use Dotclear\Module\MyPlugin;

/**
 * @brief       alias My helper.
 * @ingroup     alias
 *
 * @author      Olivier Meunier (author)
 * @author      Jean-Christian Denis (latest)
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
class My extends MyPlugin
{
    public static function checkCustomContext(int $context): ?bool
    {
        return match ($context) {
            My::BACKEND, My::MANAGE, My::MENU => App::task()->checkContext('BACKEND')
                && App::auth()->check(App::auth()->makePermissions([
                    App::auth()::PERMISSION_ADMIN,
                ]), App::blog()->id()),

            default => null,
        };
    }
}
