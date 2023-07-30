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
use Dotclear\Core\Process;
use Dotclear\Database\Structure;
use Exception;

class Install extends Process
{
    public static function init(): bool
    {
        return self::status(My::checkContext(My::INSTALL));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        try {
            $s = new Structure(dcCore::app()->con, dcCore::app()->prefix);
            $s->__get(My::ALIAS_TABLE_NAME)
                ->field('blog_id', 'varchar', 32, false)
                ->field('alias_url', 'varchar', 255, false)
                ->field('alias_destination', 'varchar', 255, false)
                ->field('alias_position', 'smallint', 0, false, 1)
                ->field('alias_redirect', 'smallint', 0, false, 0)

                ->primary('pk_alias', 'blog_id', 'alias_url')

                ->index('idx_alias_blog_id', 'btree', 'blog_id')
                ->index('idx_alias_blog_id_alias_position', 'btree', 'blog_id', 'alias_position')

                ->reference('fk_alias_blog', 'blog_id', 'blog', 'blog_id', 'cascade', 'cascade')
            ;

            (new Structure(dcCore::app()->con, dcCore::app()->prefix))->synchronize($s);

            return true;
        } catch (Exception $e) {
            dcCore::app()->error->add($e->getMessage());

            return false;
        }
    }
}
