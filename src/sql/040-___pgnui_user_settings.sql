DROP SCHEMA ___pgnui_user_setting_skel CASCADE;
CREATE SCHEMA ___pgnui_user_settings_skel;

DROP TABLE ___pgnui_user_settings_skel.global_parameters_values CASCADE;
CREATE TABLE ___pgnui_user_settings_skel.global_parameters_values (
	catalog_name varchar,
	schema_name varchar,
	table_name varchar,
	column_name varchar,
	value varchar,
	PRIMARY KEY (catalog_name, schema_name, table_name, column_name)
);

