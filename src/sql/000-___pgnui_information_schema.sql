-- 1

DROP SCHEMA ___pgnui_information_schema CASCADE;
CREATE SCHEMA ___pgnui_information_schema;
GRANT USAGE ON SCHEMA ___pgnui_information_schema TO public;

DROP VIEW ___pgnui_information_schema.key_column_usage CASCADE;
CREATE VIEW ___pgnui_information_schema.key_column_usage AS
SELECT constraint_catalog, constraint_schema, constraint_name, table_catalog, table_schema, table_name, column_name, ordinal_position, COALESCE(position_in_unique_constraint, '1') AS position_in_unique_constraint
FROM information_schema.key_column_usage;
GRANT SELECT ON ___pgnui_information_schema.key_column_usage TO public;

DROP VIEW ___pgnui_information_schema.table_constraints CASCADE;
CREATE VIEW ___pgnui_information_schema.table_constraints AS
SELECT
	current_database()::information_schema.sql_identifier AS constraint_catalog,
	nc.nspname::information_schema.sql_identifier AS constraint_schema,
	c.conname::information_schema.sql_identifier AS constraint_name,
	current_database()::information_schema.sql_identifier AS table_catalog,
	nr.nspname::information_schema.sql_identifier AS table_schema,
	r.relname::information_schema.sql_identifier AS table_name, 
    CASE c.contype
        WHEN 'c'::"char" THEN 'CHECK'::text
        WHEN 'f'::"char" THEN 'FOREIGN KEY'::text
        WHEN 'p'::"char" THEN 'PRIMARY KEY'::text
        WHEN 'u'::"char" THEN 'UNIQUE'::text
        ELSE NULL::text
    END::information_schema.character_data AS constraint_type, 
    CASE
        WHEN c.condeferrable THEN 'YES'::text
        ELSE 'NO'::text
    END::information_schema.character_data AS is_deferrable, 
    CASE
        WHEN c.condeferred THEN 'YES'::text
        ELSE 'NO'::text
    END::information_schema.character_data AS initially_deferred
FROM pg_namespace nc, pg_namespace nr, pg_constraint c, pg_class r
WHERE
	nc.oid = c.connamespace
	AND nr.oid = r.relnamespace
	AND c.conrelid = r.oid
	AND r.relkind = 'r'::"char"
	AND NOT pg_is_other_temp_schema(nr.oid)
	AND (pg_has_role(r.relowner, 'USAGE'::text)
		OR has_table_privilege(r.oid, 'INSERT'::text)
		OR has_table_privilege(r.oid, 'UPDATE'::text)
		OR has_table_privilege(r.oid, 'DELETE'::text)
		OR has_table_privilege(r.oid, 'REFERENCES'::text)
		OR has_table_privilege(r.oid, 'TRIGGER'::text)
	)

UNION ALL

SELECT
	current_database()::information_schema.sql_identifier AS constraint_catalog,
	nr.nspname::information_schema.sql_identifier AS constraint_schema,
	(((((nr.oid::text || '_'::text) || r.oid::text) || '_'::text) || a.attnum::text) || '_not_null'::text)::information_schema.sql_identifier AS constraint_name,
	current_database()::information_schema.sql_identifier AS table_catalog,
	nr.nspname::information_schema.sql_identifier AS table_schema,
	r.relname::information_schema.sql_identifier AS table_name,
	'CHECK'::character varying::information_schema.character_data AS constraint_type,
	'NO'::character varying::information_schema.character_data AS is_deferrable,
	'NO'::character varying::information_schema.character_data AS initially_deferred
FROM pg_namespace nr, pg_class r, pg_attribute a
WHERE nr.oid = r.relnamespace
	AND r.oid = a.attrelid
	AND a.attnotnull
	AND a.attnum > 0
	AND NOT a.attisdropped
	AND r.relkind = 'r'::"char"
	AND NOT pg_is_other_temp_schema(nr.oid)
	AND (pg_has_role(r.relowner, 'USAGE'::text)
		OR has_table_privilege(r.oid, 'SELECT'::text)
		OR has_table_privilege(r.oid, 'INSERT'::text)
		OR has_table_privilege(r.oid, 'UPDATE'::text)
		OR has_table_privilege(r.oid, 'DELETE'::text)
		OR has_table_privilege(r.oid, 'REFERENCES'::text)
		OR has_table_privilege(r.oid, 'TRIGGER'::text))
;

GRANT SELECT ON ___pgnui_information_schema.table_constraints TO public;
