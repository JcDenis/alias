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

# Update aliases
if (isset($_POST['a']) && is_array($_POST['a'])) {
    try {
        $o->updateAliases($_POST['a']);
        http::redirect(dcCore::app()->admin->getPageURL() . '&up=1');
    } catch (Exception $e) {
        dcCore::app()->error->add($e->getMessage());
    }
}

# New alias
if (isset($_POST['alias_url'])) {
    try {
        $o->createAlias($_POST['alias_url'], $_POST['alias_destination'], count($aliases) + 1);
        http::redirect(dcCore::app()->admin->getPageURL() . '&created=1');
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
echo
'<h2>' . html::escapeHTML(dcCore::app()->blog->name) . ' &rsaquo; ' . __('Aliases') . '</h2>' .
'<h3>' . __('Aliases list') . '</h3>';

if (empty($aliases)) {
    echo '<p>' . __('No alias') . '</p>';
} else {
    echo
    '<form action="' . dcCore::app()->admin->getPageURL() . '" method="post">' .
    '<table><tr>' .
    '<td>' . __('Alias URL') . '</td>' .
    '<td>' . __('Alias destination') . '</td>' .
    '<td>' . __('Alias position') . '</td>' .
    '</tr>';

    foreach ($aliases as $k => $v) {
        echo
        '<tr>' .
        '<td>' . form::field(['a[' . $k . '][alias_url]'], 30, 255, html::escapeHTML($v['alias_url'])) . '</td>' .
        '<td>' . form::field(['a[' . $k . '][alias_destination]'], 50, 255, html::escapeHTML($v['alias_destination'])) . '</td>' .
        '<td>' . form::field(['a[' . $k . '][alias_position]'], 3, 5, html::escapeHTML($v['alias_position'])) . '</td>' .
        '</tr>';
    }

    echo '</table>' .
    '<p>' . __('To remove an alias, empty its URL or destination.') . '</p>' .
    '<p>' . dcCore::app()->formNonce() .
    '<input type="submit" value="' . __('Update') . '" /></p>' .
    '</form>';
}

echo
'<h3>' . __('New alias') . '</h3>' .
'<form action="' . dcCore::app()->admin->getPageURL() . '" method="post">' .
'<p class="field"><label>' . __('Alias URL:') . ' ' . form::field('alias_url', 50, 255) . '</label></p>' .
'<p class="field"><label>' . __('Alias destination:') . ' ' . form::field('alias_destination', 50, 255) . '</label></p>' .
'<p>' . dcCore::app()->formNonce() . '<input type="submit" value="' . __('Save') . '" /></p>' .
'</form>';

dcPage::helpBlock('alias');
?>
</body>
</html>
