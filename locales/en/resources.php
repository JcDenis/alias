<?php
/**
 * @file
 * @brief       The plugin alias resources
 * @ingroup     alias
 *
 * @author      Olivier Meunier (author)
 * @author      Jean-Christian Denis (latest)
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
foreach (['list' => 'help', 'new' => 'new'] as $page => $file) {
    \Dotclear\App::backend()->resources()->set('help', 'alias-' . $page, __DIR__ . '/help/' . $file . '.html');
}
