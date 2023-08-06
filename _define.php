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
if (!defined('DC_RC_PATH')) {
    return null;
}

$this->registerModule(
    'alias',
    "Create aliases of your blog's URLs",
    'Olivier Meunier and contributors',
    '1.8',
    [
        'requires'    => [['core', '2.27']],
        'permissions' => dcCore::app()->auth->makePermissions([
            dcCore::app()->auth::PERMISSION_ADMIN,
        ]),
        'type'       => 'plugin',
        'priority'   => 2,
        'support'    => 'https://git.dotclear.watch/JcDenis/alias/issues',
        'details'    => 'https://git.dotclear.watch/JcDenis/alias/src/branch/master/README.md',
        'repository' => 'https://git.dotclear.watch/JcDenis/alias/raw/branch/master/dcstore.xml',
    ]
);
