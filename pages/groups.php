<?php

$fragment = new rex_fragment();

// TODO: on create local group also setup category underneath 'Ortsgruppen'

$funcs = array('create_update', 'delete');

$func = rex_get('func');

$create_update_error = false;
$delete_error = false;

if (in_array($func, $funcs)) {
    switch ($func) {
        case 'create_update':
            $create_update_error = naju_local_group_manager::createOrUpdate(rex_post('group_name'), rex_post('group_id', 'int', null), rex_post('group_logo', 'string', null));
            break;
        case 'delete':
            $delete_error = naju_local_group_manager::delete(rex_get('group_id'));
            break;
    }
}

$msg = '';

if ($create_update_error) {
    $msg .= '<p class="alert alert-danger">Ortsgruppe konnte nicht erstellt/geändert werden</p>';
} elseif ($delete_error) {
    $msg .= '<p class="alert alert-danger">Ortsgruppe konnte nicht gelöscht werden</p>';
}

$local_groups = rex_sql::factory()->setQuery('select group_id, group_name, group_logo from naju_local_group')->getArray();
$local_group_opts = '';

$group_table = <<<EOHTML
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Logo</th>
                <th>Anpassen</th>
            </tr>
        </thead>
        <tbody>
EOHTML;

foreach ($local_groups as $group) {

    // generate row in the local groups table
    $group_table .= '<tr><td>' . htmlspecialchars($group['group_id']) . '</td><td>' . htmlspecialchars($group['group_name']) . '</td>';
    $group_table .= '<td><code>' . htmlspecialchars($group['group_logo']) . '</code></td>';

    $group_table .= '
        <td>
            <a href="' . rex_url::currentBackendPage(['func' => 'delete', 'group_id' => urlencode($group['group_id'])]) . '">' .
                '<i class="rex-icon rex-icon-delete"></i> löschen
            </a>
        </td>';

    $group_table .= '</tr>';

    // generate entry in update group select
    $local_group_opts .= '<option value="' . htmlspecialchars($group['group_id']) . '">' . htmlspecialchars($group['group_name']) . '</option>';
}

$group_table .= '</tbody></table>';

$formaction = rex_url::currentBackendPage(['func' => 'create_update']);
$form = <<<EOHTML
    <form method="post" action="$formaction" style="margin: 15px;">
        <div class="form-group">
            <label for="group-id">Gruppe zum Aktualisieren oder neu Erstellen:</label>
            <select name="group_id" id="group-id" class="form-control">
                <option value="-1">neu</option>
                $local_group_opts
            </select>
        </div>
        <div class="form-group">
            <label for="group-name">Name der Ortsgruppe:</label>
            <input type="text" name="group_name" required placeholder="Name der Gruppe" autocomplete="off" id="group-name" class="form-control">
        </div>
        <div class="form-group">
            <label for="group-logo">Logo der Ortsgruppe (optional):</label>
            <input type="text" name="group_logo" placeholder="Dateiname (Standard: naju-logo.png)" id="group-logo" class="form-control">
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-primary">Erstellen/aktualisieren</button>
        </div>
    </form>
EOHTML;

$content = $msg . $group_table . '<hr><h3 style="margin: 15px;">Neu oder aktualisieren</h3>' . $form;

$fragment->setVar('content', $content, false);
echo $fragment->parse('core/page/section.php');
