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

use dbStruct;
use dcCore;
use dcNsProcess;
use Exception;

class Install extends dcNsProcess
{
    public static function init(): bool
    {
        static::$init = defined('DC_CONTEXT_ADMIN') && dcCore::app()->newVersion(My::id(), dcCore::app()->plugins->moduleInfo(My::id(), 'version'));

        return static::$init;
    }

    public static function process(): bool
    {
        if (!static::$init) {
            return false;
        }

        try {
            $s = new dbStruct(dcCore::app()->con, dcCore::app()->prefix);
            $s->{My::ALIAS_TABLE_NAME}
                ->blog_id('varchar', 32, false)
                ->alias_url('varchar', 255, false)
                ->alias_destination('varchar', 255, false)
                ->alias_position('smallint', 0, false, 1)
                ->alias_redirect('smallint', 0, false, 0)

                ->primary('pk_alias', 'blog_id', 'alias_url')

                ->index('idx_alias_blog_id', 'btree', 'blog_id')
                ->index('idx_alias_blog_id_alias_position', 'btree', 'blog_id', 'alias_position')

                ->reference('fk_alias_blog', 'blog_id', 'blog', 'blog_id', 'cascade', 'cascade')
            ;

            (new dbStruct(dcCore::app()->con, dcCore::app()->prefix))->synchronize($s);

            return true;
        } catch (Exception $e) {
            dcCore::app()->error->add($e->getMessage());

            return false;
        }
    }
}
