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
            $name = rex_post('group_name');
            $id = rex_post('group_id', 'int', null);
            $logo = rex_post('group_logo', 'string', null);
            $link = rex_post('group_link', 'int', null);
            $internal = rex_post('group_internal', 'int', 0);
            $create_update_error = naju_local_group_manager::createOrUpdate($name, $id, $logo, $link, $internal);
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

$local_groups = rex_sql::factory()->setQuery('select group_id, group_name, group_logo, group_link, group_internal from naju_local_group')->getArray();
$local_group_opts = '';

$group_table = <<<EOHTML
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Logo</th>
                <th>Artikel</th>
                <th>Anpassen</th>
            </tr>
        </thead>
        <tbody>
EOHTML;

foreach ($local_groups as $group) {

    $group_id = htmlspecialchars($group['group_id']);
    $group_name = htmlspecialchars($group['group_name']);
    $group_logo = htmlspecialchars($group['group_logo']);
    $group_link = htmlspecialchars($group['group_link']);
    $group_internal = $group['group_internal'];

    // generate row in the local groups table
    $group_table .= '<tr id="local-group-' . $group_id . '"';  // closing tag on next line
    $group_table .= ' data-group-name="' . $group_name . '" data-group-logo="' . $group_logo . '" data-group-link="' . $group_link . '" data-group-internal="' . $group_internal .'">';


    $internal_icon = $group_internal ? ' <span data-toggle="tooltip" title="Gruppe ist intern"><i class="fa fa-lock"></i></span>' : '';
    $group_table .= '<td>' . $group_id . '</td><td>' . $group_name . $internal_icon . '</td>';
    $group_table .= '<td><code>' . $group_logo . '</code></td>';
    if ($group['group_link']) {
        $group_table .= '<td><a href="' . rex_getUrl($group['group_link']) . '">' . rex_getUrl($group['group_link']) . '</a></td>';
    } else {
        $group_table .= '<td>---</td>';
    }

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
            <label for="group-link">ID des Gruppenartikels (optional):</label>
            <input type="number" name="group_link" placeholder="ID des Artikels" id="group-link" class="form-control">
        </div>
        <div class="form-group">
            <input type="hidden" name="group_internal" value="0">
            <div class="form-check">
                <input type="checkbox" name="group_internal" value="1" id="group-internal" class="form-check-input">
                <label for="group-internal" class="form-check-label">Gruppe ist intern</label>
            </div>
            <small class="form-text text-muted">
                Interne Gruppen werden nicht in Veranstaltungs-bezogenen Auswahlmenüs (z.B. im Kalender) angezeigt, können aber Büros, etc. besitzen.
            </small>
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-primary">Erstellen/aktualisieren</button>
        </div>
    </form>
EOHTML;

$form_init_script = <<<EOJS
    <script type="text/javascript">
        const groupSelect = document.getElementById("group-id");
        const groupNameEdit = document.getElementById("group-name");
        const groupLogoEdit = document.getElementById("group-logo");
        const groupLinkEdit = document.getElementById("group-link");
        const groupInternalEdit = document.getElementById("group-internal");
        groupSelect.addEventListener("change", (ev) => {
            const groupId = ev.target.selectedOptions[0].value;
            if (groupId == -1) {
                groupNameEdit.value = "";
                groupLogoEdit.value = "";
                groupLinkEdit.value = "";
            } else {
                const selectedGroup = document.getElementById("local-group-" + groupId);
                groupNameEdit.value = selectedGroup.dataset.groupName;
                groupLogoEdit.value = selectedGroup.dataset.groupLogo;
                groupLinkEdit.value = selectedGroup.dataset.groupLink;
                groupInternalEdit.checked = selectedGroup.dataset.groupInternal == "1";
            }
        });
    </script>
EOJS;

$content = $msg . $group_table . '<hr><h3 style="margin: 15px;">Neu oder aktualisieren</h3>' . $form . $form_init_script;

$fragment->setVar('content', $content, false);
echo $fragment->parse('core/page/section.php');
