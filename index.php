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

dcPage::check(dcCore::app()->auth->makePermissions([dcAuth::PERMISSION_ADMIN]));

$o       = new dcAliases();
$aliases = $o->getAliases();
$part    = $_REQUEST['part'] ?? 'list';

# Update aliases
if (isset($_POST['a']) && is_array($_POST['a'])) {
    try {
        $o->updateAliases($_POST['a']);
        dcAdminNotices::addSuccessNotice(__('Aliases successfully updated.'));
        dcCore::app()->adminurl->redirect('admin.plugin.alias');
    } catch (Exception $e) {
        dcCore::app()->error->add($e->getMessage());
    }
}

# New alias
if (isset($_POST['alias_url'])) {
    try {
        $o->createAlias($_POST['alias_url'], $_POST['alias_destination'], count($aliases) + 1);
        dcAdminNotices::addSuccessNotice(__('Alias successfully created.'));
        dcCore::app()->adminurl->redirect('admin.plugin.alias');
    } catch (Exception $e) {
        dcCore::app()->error->add($e->getMessage());
    }
}
?>
<html>
<head>
	<title><?php echo __('Aliases'); ?></title>
</head>

<body>
<?php

if ($part == 'new') {
    echo
    dcPage::breadcrumb([
        __('Plugins')   => '',
        __('Aliases')   => dcCore::app()->adminurl->get('admin.plugin.alias', ['part' => 'list']),
        __('New alias') => '',
    ]) .
    dcPage::notices() .
    '<h3>' . __('New alias') . '</h3>' .
    '<form action="' . dcCore::app()->admin->getPageURL() . '" method="post">' .
    '<p class="field"><label>' . __('Alias URL:') . ' ' . form::field('alias_url', 50, 255) . '</label></p>' .
    '<p class="field"><label>' . __('Alias destination:') . ' ' . form::field('alias_destination', 50, 255) . '</label></p>' .
    '<p class="form-note">' . sprintf(__('Do not put blog URL "%s" in fields.'), dcCore::app()->blog->url) . '</p>' .
    '<p>' .
    dcCore::app()->formNonce() .
    form::hidden('part', 'new') .
    '<input type="submit" value="' . __('Save') . '" /></p>' .
    '</form>';
} else {
    echo
    dcPage::breadcrumb([
        __('Plugins') => '',
        __('Aliases') => '',
    ]) .
    dcPage::notices() .
    '<p class="top-add"><a class="button add" href="' .
        dcCore::app()->adminurl->get('admin.plugin.alias', ['part' => 'new']) .
    '">' . __('New alias') . '</a></p>';

    if (empty($aliases)) {
        echo '<p>' . __('No alias') . '</p>';
    } else {
        echo
        '<form action="' . dcCore::app()->admin->getPageURL() . '" method="post">' .
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
            form::field(['a[' . $k . '][alias_url]'], 50, 255, html::escapeHTML($v['alias_url'])) . '</td>' .
            '<td class="maximal">' .
            form::field(['a[' . $k . '][alias_destination]'], 50, 255, html::escapeHTML($v['alias_destination'])) . '</td>' .
            '<td class="minimal">' .
            form::number(['a[' . $k . '][alias_position]'], [
                'min'        => 1,
                'max'        => count($aliases),
                'default'    => (int) $v['alias_position'],
                'class'      => 'position',
                'extra_html' => 'title="' . sprintf(__('position of %s'), html::escapeHTML($v['alias_url'])) . '"',
            ]) . '</td>' .
            '</tr>';
        }

        echo
        '</tbody></table></div>' .
        '<p class="form-note">' . __('To remove an alias, empty its URL or destination.') . '</p>' .
        '<p>' .
        dcCore::app()->formNonce() .
        form::hidden('part', 'list') .
        '<input type="submit" value="' . __('Update') . '" /></p>' .
        '</form>';
    }
}

dcPage::helpBlock('alias');
?>
</body>
</html>
