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
use dcNsProcess;
use dcPage;
use Dotclear\Helper\Html\Form\{
    Div,
    Form,
    Hidden,
    Input,
    Label,
    Note,
    Number,
    Para,
    Submit,
    Text
};
use Dotclear\Helper\Html\Html;
use Exception;

/**
 * Manage contributions list
 */
class Manage extends dcNsProcess
{
    public static function init(): bool
    {
        static::$init = defined('DC_CONTEXT_ADMIN')
            && !is_null(dcCore::app()->auth) && !is_null(dcCore::app()->blog) //nullsafe PHP < 8.0
            && dcCore::app()->auth->check(dcCore::app()->auth->makePermissions([
                dcCore::app()->auth::PERMISSION_ADMIN,
            ]), dcCore::app()->blog->id);

        return static::$init;
    }

    public static function process(): bool
    {
        if (!static::$init) {
            return false;
        }

        // nullsafe PHP < 8.0
        if (is_null(dcCore::app()->auth) || is_null(dcCore::app()->blog) || is_null(dcCore::app()->adminurl)) {
            return false;
        }

        if (empty($_POST) || empty($_POST['a']) && empty($_POST['alias_url'])) {
            return true;
        }

        $alias   = new Alias();
        $aliases = $alias->getAliases();

        # Update aliases
        if (isset($_POST['a']) && is_array($_POST['a'])) {
            try {
                $alias->updateAliases($_POST['a']);
                dcPage::addSuccessNotice(__('Aliases successfully updated.'));
                dcCore::app()->adminurl->redirect('admin.plugin.' . My::id());
            } catch (Exception $e) {
                dcCore::app()->error->add($e->getMessage());
            }
        }

        # New alias
        if (isset($_POST['alias_url'])) {
            try {
                $alias->createAlias($_POST['alias_url'], $_POST['alias_destination'], count($aliases) + 1);
                dcPage::addSuccessNotice(__('Alias successfully created.'));
                dcCore::app()->adminurl->redirect('admin.plugin.' . My::id());
            } catch (Exception $e) {
                dcCore::app()->error->add($e->getMessage());
            }
        }

        return true;
    }

    public static function render(): void
    {
        if (!static::$init) {
            return;
        }

        // nullsafe PHP < 8.0
        if (is_null(dcCore::app()->blog) || is_null(dcCore::app()->adminurl)) {
            return;
        }

        $alias   = new Alias();
        $aliases = $alias->getAliases();

        dcPage::openModule(My::name());

        if (($_REQUEST['part'] ?? 'list') == 'new') {
            echo
            dcPage::breadcrumb([
                __('Plugins')   => '',
                My::name()      => dcCore::app()->adminurl->get('admin.plugin.' . My::id(), ['part' => 'list']),
                __('New alias') => '',
            ]) .
            dcPage::notices() .

            (new Div())->items([
                (new Text('h3', __('New alias'))),
                (new Form(My::id() . '_form'))->method('post')->action(dcCore::app()->admin->getPageURL())->fields([
                    (new Para())->class('field')->items([
                        (new Label(__('Alias URL:'), Label::OUTSIDE_LABEL_BEFORE))->for('alias_url'),
                        (new Input('alias_url'))->size(50)->maxlenght(255),
                    ]),
                    (new Para())->class('field')->items([
                        (new Label(__('Alias destination:'), Label::OUTSIDE_LABEL_BEFORE))->for('alias_destination'),
                        (new Input('alias_destination'))->size(50)->maxlenght(255),
                    ]),
                    (new Note())->class('form-note')->text(sprintf(__('Do not put blog URL "%s" in fields.'), dcCore::app()->blog->url)),
                    (new Para())->items([
                        dcCore::app()->formNonce(false),
                        (new Hidden('part', 'new')),
                        (new Submit(['do']))->value(__('Save')),
                    ]),
                ]),
            ])->render();
        } else {
            echo
            dcPage::breadcrumb([
                __('Plugins') => '',
                My::name()    => '',
            ]) .
            dcPage::notices() .
            '<p class="top-add"><a class="button add" href="' .
                dcCore::app()->adminurl->get('admin.plugin.' . My::id(), ['part' => 'new']) .
            '">' . __('New alias') . '</a></p>';

            if (empty($aliases)) {
                echo '<p>' . __('No alias') . '</p>';
            } else {
                echo
                '<form action="' . dcCore::app()->admin->getPageURL() . '" method="post">' .
                '<p>' . sprintf(__('There is %s alias.', 'There are %s aliases.', count($aliases)), count($aliases)) . '</p>' .
                '<div class="table-outer">' .
                '<table>' .
                '<caption>' . __('Aliases list') . '</caption>' .
                '<thead>' .
                '<tr>' .
                '<th class="nowrap" scope="col">' . __('Alias URL') . '</th>' .
                '<th class="nowrap" scope="col">' . __('Alias destination') . '</th>' .
                '<th class="nowrap" scope="col">' . __('Alias position') . '</th>' .
                '</tr>' .
                '</thead><tbody>';

                foreach ($aliases as $k => $v) {
                    echo
                    '<tr class="line" id="l_' . $k . '">' .
                    '<td>' .
                    (new Input(['a[' . $k . '][alias_url]']))->size(50)->maxlenght(255)->value(Html::escapeHTML($v['alias_url']))->render() . '</td>' .
                    '<td class="maximal">' .
                    (new Input(['a[' . $k . '][alias_destination]']))->size(50)->maxlenght(255)->value(Html::escapeHTML($v['alias_destination']))->render() . '</td>' .
                    '<td class="minimal">' .
                    (new Number(['a[' . $k . '][alias_position]']))->min(1)->max(count($aliases))->default((int) $v['alias_position'])->class('position')->title(sprintf(__('position of %s'), Html::escapeHTML($v['alias_url'])))->render() . '</td>' .
                    '</tr>';
                }

                echo
                '</tbody></table></div>' .
                '<p class="form-note">' . __('To remove an alias, empty its URL or destination.') . '</p>' .
                (new Para())->items([
                    dcCore::app()->formNonce(false),
                    (new Hidden('part', 'list')),
                    (new Submit(['upd']))->value(__('Update')),
                ])->render() .
                '</form>';
            }
        }

        dcPage::helpBlock('alias');
        dcPage::closeModule();
    }
}
