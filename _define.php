<?php
/**
 * @file
 * @brief       The plugin alias definition
 * @ingroup     alias
 *
 * @defgroup    alias Plugin alias.
 *
 * Create aliases of your blog's URLs.
 *
 * @author      Olivier Meunier (author)
 * @author      Jean-Christian Denis (latest)
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
declare(strict_types=1);

$this->registerModule(
    'alias',
    "Create aliases of your blog's URLs",
    'Olivier Meunier and contributors',
    '1.11',
    [
        'requires'    => [['core', '2.28']],
        'permissions' => 'My',
        'type'        => 'plugin',
        'priority'    => 2,
        'support'     => 'https://git.dotclear.watch/JcDenis/' . basename(__DIR__) . '/issues',
        'details'     => 'https://git.dotclear.watch/JcDenis/' . basename(__DIR__) . '/src/branch/master/README.md',
        'repository'  => 'https://git.dotclear.watch/JcDenis/' . basename(__DIR__) . '/raw/branch/master/dcstore.xml',
    ]
);
