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
    '2.0',
    [
        'requires'    => [['core', '2.34']],
        'permissions' => 'My',
        'type'        => 'plugin',
        'priority'    => 2,
        'support'     => 'https://github.com/JcDenis/' . $this->id . '/issues',
        'details'     => 'https://github.com/JcDenis/' . $this->id . '/',
        'repository'  => 'https://raw.githubusercontent.com/JcDenis/' . $this->id . '/master/dcstore.xml',
        'date'        => '2025-06-01T13:53:10+00:00',
    ]
);
