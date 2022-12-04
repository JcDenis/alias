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
    '1.3',
    [
        'requires'    => [['core', '2.24']],
        'permissions' => dcCore::app()->auth->makePermissions([
            dcAuth::PERMISSION_ADMIN,
        ]),
        'type'       => 'plugin',
        'support'    => 'https://github.com/JcDenis/alias',
        'details'    => 'https://plugins.dotaddict.org/dc2/details/alias',
        'repository' => 'https://raw.githubusercontent.com/JcDenis/alias/master/dcstore.xml',
    ]
);
