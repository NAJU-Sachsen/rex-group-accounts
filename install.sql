
create table if not exists naju_local_group (
	group_id int(10) unsigned not null auto_increment,
	group_name varchar(75) not null,
	group_logo varchar(50) default null,
	group_link int(10) unsigned default null,
	group_internal tinyint(1) unsigned not null default 0,
	primary key(group_id),
	foreign key fk_local_group_article (group_link) references rex_article(id)
);

create table if not exists naju_group_account (
	account_id int(10) unsigned not null,
	group_id int(10) unsigned not null,
	foreign key fk_account_user (account_id) references rex_user(id),
	foreign key fk_group_local_group (group_id) references naju_local_group(group_id),
	unique key id_account_group (account_id, group_id)
);
