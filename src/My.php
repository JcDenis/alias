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
use Dotclear\Module\MyPlugin;

/**
 * This module definitions.
 */
class My extends MyPlugin
{
    /** @var    string  Plugin table name */
    public const ALIAS_TABLE_NAME = 'alias';

    public static function checkCustomContext(int $context): ?bool
    {
        return !in_array($context, [My::BACKEND, My::MANAGE, My::MENU]) ? null :
            defined('DC_CONTEXT_ADMIN')
            && dcCore::app()->auth->check(dcCore::app()->auth->makePermissions([
                dcCore::app()->auth::PERMISSION_ADMIN,
            ]), dcCore::app()->blog->id);
    }
}
