<?php

schema_updates::ensure_article_index();

// update to 0.2.0
if (rex_version::compare($this->getVersion(), '0.2', '<')) {
    rex_sql_table::get('naju_local_group')
        ->ensureColumn(new rex_sql_column('group_internal', 'tinyint(1) unsigned not null default 0'))
        ->alter();
}
