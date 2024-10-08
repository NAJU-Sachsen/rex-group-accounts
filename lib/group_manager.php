<?php

class naju_local_group_manager
{
    public static function createOrUpdate($group_name, $id = null, $logo = null, $link = null, $internal = false)
    {
        if (!$group_name) {
            return false;
        }

        // if $id isset it means update, else create
        if (isset($id) && $id != -1) {
            $update_query = <<<EOSQL
                update naju_local_group
                set group_name = :name, group_logo = :logo, group_link = :link, group_internal = :internal
                where group_id = :id
                limit 1;
EOSQL;
            $sql = rex_sql::factory()->setQuery($update_query, ['id' => $id, 'name' => $group_name, 'logo' => $logo, 'link' => $link, "internal" => $internal]);
        } else {
            $insert_query = <<<EOSQL
                insert into
                    naju_local_group (group_name, group_logo, group_link, group_internal)
                values (:name, :logo, :link, :internal)
EOSQL;
            $sql = rex_sql::factory()->setQuery($insert_query, ['name' => $group_name, 'logo' => $logo, 'link' => $link, 'internal' => $internal]);
        }

        $success = !$sql->hasError() && 1 !== $sql->getRows();
        return $success;
    }

    public static function delete($id)
    {
        if (!$id) {
            return false;
        }

        $sql = rex_sql::factory()->setQuery('delete from naju_local_group where group_id = :id limit 1', ['id' => $id]);
        $success = !$sql->hasError() && 1 !== $sql->getRows();
        return $success;
    }

}
