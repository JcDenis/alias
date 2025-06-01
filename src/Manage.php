<?php

declare(strict_types=1);

namespace Dotclear\Plugin\alias;

use Dotclear\App;
use Dotclear\Core\Process;
use Dotclear\Core\Backend\{ Notices, Page };
use Dotclear\Helper\Html\Form\{ Checkbox, Div, Form, Hidden, Input, Label, Link, Note, Number, Para, Submit, Text, Table, Thead, Tbody, Td, Th, Tr };
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

        # Update aliases
        if (isset($_POST['a']) && is_array($_POST['a'])) {
            $order = [];
            if (empty($_POST['alias_order']) && !empty($_POST['order'])) {
                $order = $_POST['order'];
                $order = array_flip($order);
            } elseif (!empty($_POST['alias_order'])) {
                $order = explode(',', (string) $_POST['alias_order']);
            }

            try {
                $stack = [];
                foreach ($_POST['a'] as $k => $alias) {
                    $stack[] = new AliasRow(
                        $alias['alias_url']         ?? '',
                        $alias['alias_destination'] ?? '',
                        (int) (array_search($k, $order) ?? 0),
                        !empty($alias['alias_redirect']),
                    );
                }
                Alias::updateAliases($stack);
                Notices::addSuccessNotice(__('Aliases successfully updated.'));
                My::redirect();
            } catch (Exception $e) {
                App::error()->add($e->getMessage());
            }
        }

        # New alias
        if (isset($_POST['alias_url'])) {
            try {
                Alias::createAlias(new AliasRow($_POST['alias_url'], $_POST['alias_destination'], count(Alias::getAliases()) + 1, !empty($_POST['alias_redirect'])));
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

        $aliases = Alias::getAliases();
        $head = App::auth()->prefs()->accessibility->nodragdrop ? '' :
            Page::jsLoad('js/jquery/jquery-ui.custom.js') .
            Page::jsLoad('js/jquery/jquery.ui.touch-punch.js') .
            My::jsLoad('dragndrop');

        Page::openModule(My::name(), $head);

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
                (new Form(My::id() . '_form'))
                    ->method('post')
                    ->action(App::backend()->getPageURL())
                    ->fields([
                        (new Para())
                            ->class('field')
                            ->items([
                                (new Input('alias_url'))
                                    ->size(50)
                                    ->maxlength(255)
                                    ->label(new Label(__('Alias URL:'), Label::OUTSIDE_LABEL_BEFORE)),
                            ]),
                        (new Para())
                            ->class('field')
                            ->items([
                                (new Input('alias_destination'))
                                    ->size(50)->maxlength(255)
                                    ->label(new Label(__('Alias destination:'), Label::OUTSIDE_LABEL_BEFORE)),
                            ]),
                        (new Note())
                            ->class('form-note')
                            ->text(sprintf(__('Do not put blog URL "%s" in fields.'), App::blog()->url())),
                        (new Para())
                            ->items([
                                (new Checkbox('alias_redirect', false))
                                    ->value(1)
                                    ->label(new Label(__('Do visible redirection to destination'), Label::OUTSIDE_LABEL_AFTER)),
                            ]),
                        (new Para())
                            ->items([
                                (new Submit(['do']))
                                    ->value(__('Save')),
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
            (new Para())
                ->class('top-add')
                ->items([
                    (new Link())
                        ->class(['button', 'add'])
                        ->href(My::manageUrl(['part' => 'new']))
                        ->text(__('New alias'))
                ])
                ->render();

            if (empty($aliases)) {
                echo (new Text('p', __('No alias')))->render();
            } else {
                $rows = [];
                $k = 1;
                foreach ($aliases as $alias) {
                    $rows[] = (new Tr('l_' . $k))
                        ->class('line')
                        ->cols([
                            (new Td())
                                ->class(['minimal', App::auth()->prefs()->accessibility->nodragdrop ? '' : 'handle'])
                                ->items([
                                    (new Number(['order[' . $k . ']'], 1, count($aliases), $k))
                                        ->class('position')
                                        ->title(__('position')),
                                ]),
                            (new Td())
                                ->class('minimal')
                                ->items([
                                    (new Input(['a[' . $k . '][alias_url]']))
                                        ->size(50)
                                        ->maxlength(255)
                                        ->value(Html::escapeHTML($alias->url)),
                                ]),
                            (new Td())
                                ->class('minimal')
                                ->items([
                                    (new Input(['a[' . $k . '][alias_destination]']))
                                        ->size(50)
                                        ->maxlength(255)
                                        ->value(Html::escapeHTML($alias->destination)),
                                ]),
                            (new Td())
                                ->class('minimal')
                                ->items([
                                    (new Checkbox(['a[' . $k . '][alias_redirect]'], $alias->redirect))
                                        ->title(sprintf(__('visible redirection to %s'), Html::escapeHTML(App::blog()->url() . $alias->destination))),
                                ]),
                        ]);
                    $k++;
                }

                echo (new Form('alias-form'))
                    ->method('post')
                    ->action(App::backend()->getPageURL())
                    ->fields([
                        (new Text('p', sprintf(__('There is %s alias.', 'There are %s aliases.', count($aliases)), count($aliases)))),
                        (new Div())
                            ->class('table-outer')
                            ->items([
                                (new Table())
                                    ->class('dragable')
                                    ->items([
                                        (new Thead())
                                            ->rows([
                                                (new Tr())
                                                    ->cols([
                                                        (new Th())
                                                            ->text(' '),
                                                        (new Th())
                                                            ->text(__('Alias URL')),
                                                        (new Th())
                                                            ->text(__('Alias destination')),
                                                        (new Th())
                                                            ->text(__('Redrection')),
                                                    ]),
                                            ]),
                                        (new Tbody('alias-list'))
                                            ->rows($rows),
                                    ]),
                            ]),
                            (new Note(__('To remove an alias, empty its URL or destination.')))
                                ->class('form-note'),
                            (new Para())
                                ->items([
                                    (new Submit(['upd']))
                                        ->value(__('Update')),
                                        ... My::hiddenFields([
                                            'part' => 'list',
                                        ]),
                                    (new Hidden('alias_order', '')),
                                ])
                    ])
                    ->render();
            }
        }

        Page::helpBlock('alias');
        Page::closeModule();
    }
}
