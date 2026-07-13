<?php

declare(strict_types=1);

namespace Dotclear\Plugin\alias;

use Dotclear\Database\MetaRecord;

/**
 * @brief       alias decriptor class.
 * @ingroup     alias
 *
 * @author      Olivier Meunier (author)
 * @author      Jean-Christian Denis (latest)
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
class AliasRow
{
    public function __construct(
        public readonly string $url, 
        public readonly string $destination, 
        public readonly int $position, 
        public readonly bool $redirect
    ) {

    }

    /**
     * Create an alias row from record.
     */
    public static function newFromRecord(MetaRecord $rs): AliasRow
    {
        return new self(
            $rs->strField('alias_url'),
            $rs->strField('alias_destination'),
            $rs->intField('alias_position'),
            !empty($rs->field('alias_redirect'))
        );
    }
}
