<?php

$fragment = new rex_fragment();

$funcs = array('create', 'delete');

$func = rex_get('func');

$msg = '';

if (in_array($func, $funcs)) {
    switch ($func) {
        case 'create':
            $account = rex_post('user');
            $group = rex_post('group');
            if ($account && $group) {
                $sql = rex_sql::factory()->setQuery('insert into naju_group_account (account_id, group_id) values(:account, :group)', [
                    'account' => $account,
                    'group' => $group
                ]);
                $success = !$sql->hasError() && 1 == $sql->getRows();

                if ($success) {
                    $msg .= '<p class="alert alert-success">Verknüpfung erstellt</p>';
                } else {
                    $msg .= '<p class="alert alert-danger">Verknüpfung konnte nicht erstellt werden</p>';
                }
            }
            break;
        case 'delete':
            $account = rex_get('user_id');
            $group = rex_get('group_id');
            if ($account && $group) {
                $sql = rex_sql::factory()->setQuery('delete from naju_group_account where account_id = :aid and group_id = :gid limit 1', [
                    'aid' => $account,
                    'gid' => $group
                ]);
                $success = !$sql->hasError() && 1 == $sql->getRows();

                if (!$success) {
                    $msg .= '<p class="alert alert-danger">Verknüpfung konnte nicht gelöscht werden</p>';
                }
            }
            break;
    }
}

$account_groups_query = <<<EOSQL
    select
        u.id as user_id,
        u.name as user_name,
        c.group_id as group_id,
        g.group_name as group_name
    from
        rex_user u
        join naju_group_account c on u.id = c.account_id
        join naju_local_group g on c.group_id = g.group_id
    order by
        u.id asc
EOSQL;

$account_groups = rex_sql::factory()->setQuery($account_groups_query)->getArray();

$account_groups_table = <<<EOHTML
    <table class="table">
        <thead>
            <tr>
                <th>Benutzer</th>
                <th>Gruppe</th>
                <th>Bearbeiten</th>
            </tr>
        </thead>
        <tbody>
EOHTML;

foreach ($account_groups as $ag) {
    $delete_params = ['func' => 'delete', 'group_id' => urlencode($ag['group_id']), 'user_id' => urldecode($ag['user_id'])];

    $account_groups_table .= '<tr>';
    $account_groups_table .= '<td>' . htmlspecialchars($ag['user_name']) . '</td>';
    $account_groups_table .= '<td>' . htmlspecialchars($ag['group_name']) . '</td>';
    $account_groups_table .= '
        <td>
            <a href="' . rex_url::currentBackendPage($delete_params) . '">' .
                '<i class="rex-icon rex-icon-delete"></i> löschen
            </a>
        </td>
    ';
    $account_groups_table .= '</tr>';
}

$account_groups_table .= '</tbody></table>';

$users = rex_sql::factory()->setQuery('select id, name from rex_user')->getArray();
$groups = rex_sql::factory()->setQuery('select group_id, group_name from naju_local_group')->getArray();

$user_opts = '';
$group_opts = '';

foreach ($users as $user) {
    $user_opts .= '<option value="' . htmlspecialchars($user['id']) . '">' . htmlspecialchars($user['name']) . '</option>';
}

foreach ($groups as $group) {
    $group_opts .= '<option value="' . htmlspecialchars($group['group_id']) . '">' . htmlspecialchars($group['group_name']) . '</option>';
}

$formaction = rex_url::currentBackendPage(['func' => 'create']);
$form = <<<EOHTML
    <form method="post" action="$formaction" style="margin: 15px;">
        <div class="form-group">
            <label for="user">Benutzer</label>
            <select name="user" id="user" class="form-control">
                $user_opts
            </select>
        </div>
        <div class="form-group">
            <label for="group">Ortsgruppe</label>
            <select name="group" id="group" class="form-control">
                $group_opts
            </select>
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-primary">Verknüpfen</button>
        </div>
    </form>
EOHTML;

$content = $msg . $account_groups_table . '<hr><h3 style="margin: 15px;">Erstellen</h3>' . $form;

$fragment->setVar('content', $content, false);
echo $fragment->parse('core/page/section.php');
