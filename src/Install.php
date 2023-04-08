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
if (!defined('DC_CONTEXT_ADMIN')) {
    return null;
}

try {
    if (!dcCore::app()->newVersion(
        basename(__DIR__),
        dcCore::app()->plugins->moduleInfo(basename(__DIR__), 'version')
    )) {
        return null;
    }

    $s = new dbStruct(dcCore::app()->con, dcCore::app()->prefix);
    $s->{initAlias::ALIAS_TABLE_NAME}
        ->blog_id('varchar', 32, false)
        ->alias_url('varchar', 255, false)
        ->alias_destination('varchar', 255, false)
        ->alias_position('smallint', 0, false, 1)

        ->primary('pk_alias', 'blog_id', 'alias_url')

        ->index('idx_alias_blog_id', 'btree', 'blog_id')
        ->index('idx_alias_blog_id_alias_position', 'btree', 'blog_id', 'alias_position')

        ->reference('fk_alias_blog', 'blog_id', 'blog', 'blog_id', 'cascade', 'cascade')
    ;

    $si      = new dbStruct(dcCore::app()->con, dcCore::app()->prefix);
    $changes = $si->synchronize($s);

    return true;
} catch (Exception $e) {
    dcCore::app()->error->add($e->getMessage());
}

return false;
