<?php

declare(strict_types=1);

namespace Dotclear\Plugin\alias;

use Dotclear\App;
use Dotclear\Core\Process;
use Dotclear\Core\Backend\{
    Notices,
    Page
};
use Dotclear\Helper\Html\Form\{
    Checkbox,
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
 * @brief       alias manage class.
 * @ingroup     alias
 *
 * @author      Olivier Meunier (author)
 * @author      Jean-Christian Denis (latest)
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
class Manage extends Process
{
    public static function init(): bool
    {
        return self::status(My::checkContext(My::MANAGE));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        if (empty($_POST) || empty($_POST['a']) && empty($_POST['alias_url'])) {
            return true;
        }

        $utils   = new Alias();
        $aliases = $utils->getAliases();

        # Update aliases
        if (isset($_POST['a']) && is_array($_POST['a'])) {
            try {
                $stack = [];
                foreach ($_POST['a'] as $alias) {
                    $stack[] = new AliasRow(
                        $alias['alias_url']         ?? '',
                        $alias['alias_destination'] ?? '',
                        (int) ($alias['alias_position'] ?? 0),
                        !empty($alias['alias_redirect']),
                    );
                }
                $utils->updateAliases($stack);
                Notices::addSuccessNotice(__('Aliases successfully updated.'));
                My::redirect();
            } catch (Exception $e) {
                App::error()->add($e->getMessage());
            }
        }

        # New alias
        if (isset($_POST['alias_url'])) {
            try {
                $utils->createAlias(new AliasRow($_POST['alias_url'], $_POST['alias_destination'], count($aliases) + 1, !empty($_POST['alias_redirect'])));
                Notices::addSuccessNotice(__('Alias successfully created.'));
                My::redirect();
            } catch (Exception $e) {
                App::error()->add($e->getMessage());
            }
        }

        return true;
    }

    public static function render(): void
    {
        if (!self::status()) {
            return;
        }

        $aliases = (new Alias())->getAliases();

        Page::openModule(My::name());

        if (($_REQUEST['part'] ?? 'list') == 'new') {
            echo
            Page::breadcrumb([
                __('Plugins')   => '',
                My::name()      => My::manageUrl(['part' => 'list']),
                __('New alias') => '',
            ]) .
            Notices::getNotices() .

            (new Div())->items([
                (new Text('h3', __('New alias'))),
                (new Form(My::id() . '_form'))->method('post')->action(App::backend()->getPageURL())->fields([
                    (new Para())->class('field')->items([
                        (new Label(__('Alias URL:'), Label::OUTSIDE_LABEL_BEFORE))->for('alias_url'),
                        (new Input('alias_url'))->size(50)->maxlength(255),
                    ]),
                    (new Para())->class('field')->items([
                        (new Label(__('Alias destination:'), Label::OUTSIDE_LABEL_BEFORE))->for('alias_destination'),
                        (new Input('alias_destination'))->size(50)->maxlength(255),
                    ]),
                    (new Note())->class('form-note')->text(sprintf(__('Do not put blog URL "%s" in fields.'), App::blog()->url())),
                    (new Para())->items([
                        (new Checkbox('alias_redirect', false))->value(1),
                        (new Label(__('Do visible redirection to destination'), Label::OUTSIDE_LABEL_AFTER))->for('alias_redirect')->class('classic'),
                    ]),
                    (new Para())->items([
                        (new Submit(['do']))->value(__('Save')),
                        ... My::hiddenFields([
                            'part' => 'new',
                        ]),
                    ]),
                ]),
            ])->render();
        } else {
            echo
            Page::breadcrumb([
                __('Plugins') => '',
                My::name()    => '',
            ]) .
            Notices::getNotices() .
            '<p class="top-add"><a class="button add" href="' .
                My::manageUrl(['part' => 'new']) .
            '">' . __('New alias') . '</a></p>';

            if (empty($aliases)) {
                echo '<p>' . __('No alias') . '</p>';
            } else {
                echo
                '<form action="' . App::backend()->getPageURL() . '" method="post">' .
                '<p>' . sprintf(__('There is %s alias.', 'There are %s aliases.', count($aliases)), count($aliases)) . '</p>' .
                '<div class="table-outer">' .
                '<table>' .
                '<caption>' . __('Aliases list') . '</caption>' .
                '<thead>' .
                '<tr>' .
                '<th class="nowrap" scope="col">' . __('Alias URL') . '</th>' .
                '<th class="nowrap" scope="col">' . __('Alias destination') . '</th>' .
                '<th class="nowrap" scope="col">' . __('Alias position') . '</th>' .
                '<th class="nowrap" scope="col">' . __('Redrection') . '</th>' .
                '</tr>' .
                '</thead><tbody>';

                foreach ($aliases as $k => $alias) {
                    echo
                    '<tr class="line" id="l_' . $k . '">' .
                    '<td class="minimal">' .
                    (new Input(['a[' . $k . '][alias_url]']))->size(50)->maxlength(255)->value(Html::escapeHTML($alias->url))->render() . '</td>' .
                    '<td class="minimal">' .
                    (new Input(['a[' . $k . '][alias_destination]']))->size(50)->maxlength(255)->value(Html::escapeHTML($alias->destination))->render() . '</td>' .
                    '<td class="minimal">' .
                    (new Number(['a[' . $k . '][alias_position]']))->min(1)->max(count($aliases))->default($alias->position)->class('position')->title(sprintf(__('position of %s'), Html::escapeHTML($alias->url)))->render() . '</td>' .
                    '<td class="maximal">' .
                    (new Checkbox(['a[' . $k . '][alias_redirect]'], $alias->redirect))->title(sprintf(__('visible redirection to %s'), Html::escapeHTML(App::blog()->url() . $alias->destination)))->render() . '</td>' .
                    '</tr>';
                }

                echo
                '</tbody></table></div>' .
                '<p class="form-note">' . __('To remove an alias, empty its URL or destination.') . '</p>' .
                (new Para())->items([
                    (new Submit(['upd']))->value(__('Update')),
                    ... My::hiddenFields([
                        'part' => 'list',
                    ]),
                ])->render() .
                '</form>';
            }
        }

        Page::helpBlock('alias');
        Page::closeModule();
    }
}
