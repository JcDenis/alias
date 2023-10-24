<?php

declare(strict_types=1);

namespace Dotclear\Plugin\alias;

use Dotclear\App;
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
    public readonly string $url;
    public readonly string $destination;
    public readonly int $position;
    public readonly bool $redirect;

    public function __construct(?string $url, ?string $destination, null|string|int $position, null|int|string|bool $redirect)
    {
        $this->url         = (string) $url;
        $this->destination = (string) $destination;
        $this->position    = (int) $position;
        $this->redirect    = !empty($redirect);
    }

    public static function newFromRecord(MetaRecord $rs): AliasRow
    {
        return new self(
            (string) $rs->field('alias_url'),
            (string) $rs->field('alias_destination'),
            (int) $rs->field('alias_position'),
            !empty($rs->field('alias_redirect'))
        );
    }
}