<?php

declare(strict_types=1);

namespace Dotclear\Plugin\alias;

use Dotclear\Core\Process;
use Dotclear\Plugin\Uninstaller\Uninstaller;

/**
 * @brief       alias uninstall class.
 * @ingroup     alias
 *
 * @author      Olivier Meunier (author)
 * @author      Jean-Christian Denis (latest)
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
class Uninstall extends Process
{
    public static function init(): bool
    {
        return self::status(My::checkContext(My::UNINSTALL));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        Uninstaller::instance()
            ->addUserAction(
                'tables',
                'delete',
                Alias::ALIAS_TABLE_NAME
            )
            ->addUserAction(
                'plugins',
                'delete',
                My::id()
            )
            ->addUserAction(
                'versions',
                'delete',
                My::id()
            )
/*
            ->addDirectAction(
                'tables',
                'delete',
                Alias::ALIAS_TABLE_NAME
            )
            ->addDirectAction(
                'plugins',
                'delete',
                My::id()
            )
            ->addDirectAction(
                'versions',
                'delete',
                My::id()
            )
//*/
        ;

        // no custom action
        return false;
    }
}
