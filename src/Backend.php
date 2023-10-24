<?php

declare(strict_types=1);

namespace Dotclear\Plugin\alias;

use Dotclear\App;
use Dotclear\Core\Process;

/**
 * @brief       alias backend class.
 * @ingroup     alias
 *
 * @author      Olivier Meunier (author)
 * @author      Jean-Christian Denis (latest)
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
class Backend extends Process
{
    public static function init(): bool
    {
        return self::status(My::checkContext(My::BACKEND));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        My::addBackendMenuItem();

        App::behavior()->addBehaviors([
            'exportFullV2'   => PluginImportExportBehaviors::exportFullV2(...),
            'importInitV2'   => PluginImportExportBehaviors::importInitV2(...),
            'importFullV2'   => PluginImportExportBehaviors::importFullV2(...),
            'importSingleV2' => PluginImportExportBehaviors::importSingleV2(...),
        ]);

        return true;
    }
}
