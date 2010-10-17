CREATE LANGUAGE 'plpgsql';

DROP SCHEMA ___pgnui_column_reference_tree CASCADE;
CREATE SCHEMA ___pgnui_column_reference_tree;
GRANT USAGE ON SCHEMA ___pgnui_column_reference_tree TO public;

DROP VIEW ___pgnui_column_reference_tree.column_reference_tree;
CREATE VIEW ___pgnui_column_reference_tree.column_reference_tree AS
SELECT
rc.constraint_catalog AS c_catalog, rc.constraint_schema AS c_schema, rc.constraint_name AS c_name, 
rc.table_catalog AS rc_catalog, rc.table_schema AS rc_schema, rc.table_name AS rc_table, rc.column_name AS rc_column,
ref.table_catalog AS ref_catalog, ref.table_schema AS ref_schema, ref.table_name AS ref_table, ref.column_name AS ref_column
FROM
	___pgnui_information_schema.key_column_usage AS rc
	NATURAL JOIN information_schema.referential_constraints AS rt
	LEFT JOIN ___pgnui_information_schema.key_column_usage AS ref ON
		rt.unique_constraint_catalog = ref.constraint_catalog
		AND rt.unique_constraint_schema = ref.constraint_schema
		AND rt.unique_constraint_name = ref.constraint_name
		AND rc.position_in_unique_constraint = ref.ordinal_position
;
GRANT SELECT ON ___pgnui_column_reference_tree.column_reference_tree TO public;

DROP TYPE ___pgnui_column_reference_tree.column_def CASCADE;
CREATE TYPE ___pgnui_column_reference_tree.column_def AS (catalog_name varchar, schema_name varchar, table_name varchar, column_name varchar);

DROP FUNCTION ___pgnui_column_reference_tree.column_source(_catalog varchar, _schema varchar, _table varchar, _column varchar, OUT root_catalog varchar, OUT root_schema varchar, OUT root_table varchar, OUT root_column varchar) CASCADE;
CREATE OR REPLACE FUNCTION ___pgnui_column_reference_tree.column_source(_catalog varchar, _schema varchar, _table varchar, _column varchar, OUT root_catalog varchar, OUT root_schema varchar, OUT root_table varchar, OUT root_column varchar) RETURNS RECORD AS $$
DECLARE
	r ___pgnui_column_reference_tree.column_def%rowtype;
	next ___pgnui_column_reference_tree.column_def%rowtype;
BEGIN

	r.catalog_name = _catalog;
	r.schema_name = _schema;
	r.table_name = _table;
	r.column_name = _column;

	LOOP
		-- RAISE NOTICE 'r = %; next = %', r, next;
		SELECT INTO next ref_catalog, ref_schema, ref_table, ref_column
			FROM ___pgnui_column_reference_tree.column_reference_tree
			WHERE rc_catalog = r.catalog_name
				AND rc_schema = r.schema_name
				AND rc_table = r.table_name
				AND rc_column = r.column_name;
		EXIT WHEN NOT FOUND;
		-- RAISE NOTICE 'r = %; next = %', r, next;
		-- RAISE NOTICE '--------------';
		r = next;
	END LOOP;

	root_catalog = r.catalog_name;
	root_schema = r.schema_name;
	root_table = r.table_name;
	root_column = r.column_name;

END
$$ LANGUAGE 'plpgsql';

GRANT EXECUTE ON FUNCTION ___pgnui_column_reference_tree.column_source(_catalog varchar, _schema varchar, _table varchar, _column varchar, OUT root_catalog varchar, OUT root_schema varchar, OUT root_table varchar, OUT root_column varchar) TO public;


DROP FUNCTION ___pgnui_column_reference_tree.global_parameters() CASCADE;
CREATE OR REPLACE FUNCTION ___pgnui_column_reference_tree.global_parameters() RETURNS SETOF ___pgnui_column_reference_tree.column_def AS $$
DECLARE
	x ___pgnui_column_reference_tree.column_def%rowtype;
	r ___pgnui_column_reference_tree.column_def%rowtype;
	next ___pgnui_column_reference_tree.column_def%rowtype;
BEGIN

	FOR x IN
		SELECT table_catalog, table_schema, table_name, column_name
		FROM information_schema.key_column_usage NATURAL JOIN information_schema.table_constraints
		WHERE constraint_type = 'FOREIGN KEY'
		GROUP BY table_catalog, table_schema, table_name, column_name
		HAVING count(constraint_name) > 1
	LOOP

		RETURN QUERY SELECT * FROM ___pgnui_column_reference_tree.column_source(x.catalog_name, x.schema_name, x.table_name, x.column_name);

	END LOOP;

END
$$ LANGUAGE 'plpgsql';
GRANT EXECUTE ON FUNCTION ___pgnui_column_reference_tree.global_parameters() TO public;

DROP TYPE ___pgnui_column_reference_tree.column_dep CASCADE;
CREATE TYPE ___pgnui_column_reference_tree.column_dep AS (catalog_name varchar, schema_name varchar, table_name varchar, column_name varchar, dep_catalog_name varchar, dep_schema_name varchar, dep_table_name varchar, dep_column_name varchar);

DROP FUNCTION ___pgnui_column_reference_tree.table_column_sources(_catalog varchar, _schema varchar, _table varchar) CASCADE;
CREATE OR REPLACE FUNCTION ___pgnui_column_reference_tree.table_column_sources(_catalog varchar, _schema varchar, _table varchar) RETURNS SETOF ___pgnui_column_reference_tree.column_dep AS $$
DECLARE
	x ___pgnui_column_reference_tree.column_def%rowtype;
	r ___pgnui_column_reference_tree.column_def%rowtype;
	n ___pgnui_column_reference_tree.column_dep%rowtype;
BEGIN

	FOR x IN
		SELECT *
		FROM information_schema.columns
		WHERE table_catalog = _catalog AND table_schema = _schema AND table_name = _table
	LOOP
		SELECT INTO r * FROM ___pgnui_column_reference_tree.column_source(x.catalog_name, x.schema_name, x.table_name, x.column_name);
		n = x;
		n.dep_catalog_name = r.catalog_name;
		n.dep_schema_name = r.schema_name;
		n.dep_table_name = r.table_name;
		n.dep_column_name = r.column_name;
		RETURN NEXT n;
	END LOOP;

END
$$ LANGUAGE 'plpgsql';
GRANT EXECUTE ON FUNCTION ___pgnui_column_reference_tree.table_column_sources(_catalog varchar, _schema varchar, _table varchar) TO public;

DROP FUNCTION ___pgnui_column_reference_tree.table_column_globals(_catalog varchar, _schema varchar, _table varchar) CASCADE;
CREATE OR REPLACE FUNCTION ___pgnui_column_reference_tree.table_column_globals(_catalog varchar, _schema varchar, _table varchar) RETURNS SETOF ___pgnui_column_reference_tree.column_dep AS $$

	SELECT cd.catalog_name, cd.schema_name, cd.table_name, cd.column_name, cd.dep_catalog_name, cd.dep_schema_name, cd.dep_table_name, cd.dep_column_name
	FROM ___pgnui_column_reference_tree.table_column_sources($1, $2, $3) AS cd
		INNER JOIN (SELECT DISTINCT * FROM ___pgnui_column_reference_tree.global_parameters()) AS gp
		ON gp.catalog_name = cd.dep_catalog_name
			AND gp.schema_name = cd.dep_schema_name
			AND gp.table_name = cd.dep_table_name
			AND gp.column_name = cd.dep_column_name;

$$ LANGUAGE 'SQL';
GRANT EXECUTE ON FUNCTION ___pgnui_column_reference_tree.table_column_globals(_catalog varchar, _schema varchar, _table varchar) TO public;

DROP VIEW ___pgnui_column_reference_tree.column_foreign_key_references CASCADE;
CREATE VIEW ___pgnui_column_reference_tree.column_foreign_key_references AS
SELECT
rt.constraint_catalog, rt.constraint_schema, rt.constraint_name, rc.table_catalog AS rc_catalog, rc.table_schema AS rc_schema, rc.table_name AS rc_table, rc.column_name AS rc_column,
ref.table_catalog AS ref_catalog, ref.table_schema AS ref_schema, ref.table_name AS ref_table, ref.column_name AS ref_column
FROM
	___pgnui_information_schema.key_column_usage AS rc
	NATURAL JOIN information_schema.referential_constraints AS rt
	LEFT JOIN ___pgnui_information_schema.key_column_usage AS ref ON
		rt.unique_constraint_catalog = ref.constraint_catalog
		AND rt.unique_constraint_schema = ref.constraint_schema
		AND rt.unique_constraint_name = ref.constraint_name
		AND rc.position_in_unique_constraint = ref.ordinal_position
;
GRANT SELECT ON "___pgnui_column_reference_tree"."column_foreign_key_references" TO public;

DROP FUNCTION ___pgnui_column_reference_tree.column_foreign_key_references_table(_catalog varchar, _schema varchar, _table varchar);
CREATE FUNCTION ___pgnui_column_reference_tree.column_foreign_key_references_table(_catalog varchar, _schema varchar, _table varchar) RETURNS SETOF ___pgnui_column_reference_tree.column_foreign_key_references AS $$
	SELECT *
	FROM ___pgnui_column_reference_tree.column_foreign_key_references
	WHERE rc_catalog = $1 AND rc_schema = $2 AND rc_table = $3;
$$ LANGUAGE SQL;
GRANT EXECUTE ON FUNCTION ___pgnui_column_reference_tree.column_foreign_key_references_table(_catalog varchar, _schema varchar, _table varchar) TO public;


CREATE VIEW ___pgnui_column_reference_tree.primary_keys AS
    SELECT table_constraints.table_catalog, table_constraints.table_schema, table_constraints.table_name, key_column_usage.column_name, key_column_usage.ordinal_position FROM (information_schema.table_constraints NATURAL JOIN information_schema.key_column_usage) WHERE ((table_constraints.constraint_type)::text = 'PRIMARY KEY'::text);
GRANT SELECT ON ___pgnui_column_reference_tree.primary_keys TO public;

DROP VIEW ___pgnui_column_reference_tree.uniq_inside_fk CASCADE;
CREATE VIEW ___pgnui_column_reference_tree.uniq_inside_fk AS
SELECT
	uniq.constraint_catalog,
	uniq.constraint_schema,
	uniq.table_name AS constraint_table,
    fk.constraint_name AS fk_name,
    uniq.constraint_name AS uniq_name
FROM
    (
        SELECT
            constraint_catalog,
            constraint_schema,
            information_schema.constraint_column_usage.table_name,
			constraint_name,
            ARRAY[information_schema.constraint_column_usage.table_catalog::text, information_schema.constraint_column_usage.table_schema::text, information_schema.constraint_column_usage.table_name::text, information_schema.constraint_column_usage.column_name::text] AS cols
        FROM
            information_schema.constraint_column_usage
            LEFT JOIN information_schema.table_constraints USING (constraint_catalog, constraint_schema, constraint_name)
        WHERE constraint_type = 'UNIQUE'
    ) AS uniq,
    (
        SELECT
            constraint_catalog,
            constraint_schema,
            information_schema.constraint_column_usage.table_name,
			constraint_name,
            ARRAY[information_schema.constraint_column_usage.table_catalog::text, information_schema.constraint_column_usage.table_schema::text, information_schema.constraint_column_usage.table_name::text, information_schema.constraint_column_usage.column_name::text] AS cols
        FROM
            information_schema.constraint_column_usage
            LEFT JOIN information_schema.table_constraints USING (constraint_catalog, constraint_schema, constraint_name)
        WHERE constraint_type = 'FOREIGN KEY'
    ) AS fk
WHERE
    uniq.cols <@ fk.cols
	AND uniq.constraint_catalog::text = fk.constraint_catalog::text
	AND uniq.constraint_schema::text = fk.constraint_schema::text
	AND uniq.table_name::text = fk.table_name::text
;
GRANT SELECT ON ___pgnui_column_reference_tree.uniq_inside_fk TO public;
