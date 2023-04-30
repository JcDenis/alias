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
use Dotclear\Plugin\Uninstaller\Uninstaller;

class Uninstall extends dcNsProcess
{
    public static function init(): bool
    {
        static::$init = defined('DC_CONTEXT_ADMIN');

        return static::$init;
    }

    public static function process(): bool
    {
        if (!static::$init || !dcCore::app()->plugins->moduleExists('Uninstaller')) {
            return false;
        }

        Uninstaller::instance()
            ->addUserAction(
                'tables',
                'delete',
                My::ALIAS_TABLE_NAME
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
                My::ALIAS_TABLE_NAME
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