<?php

class schema_updates {

    static function ensure_article_index() {
        $sql = rex_sql::factory()
            ->setQuery("SELECT * FROM information_schema.statistics WHERE table_name = 'rex_article' AND index_name = 'legacy_id' LIMIT 1")
            ->execute();
            
        if ($sql->getRows() == 0) {
            rex_logger::factory()
                ->log(E_USER_WARNING, 'Creating index on rex_article.id');

            rex_sql_table::get(rex::getTable('rex_article'))
                ->ensureIndex(new rex_sql_index('legacy_id', ['id'], rex_sql_index::UNIQUE))
                ->alter();
        } else {
            rex_logger::factory()
                ->log(E_USER_WARNING, 'Index on rex_article.id already exists');
        }
    }

}
