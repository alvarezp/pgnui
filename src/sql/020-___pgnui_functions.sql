DROP SCHEMA ___pgnui_functions CASCADE;
CREATE SCHEMA ___pgnui_functions;
GRANT USAGE ON SCHEMA ___pgnui_functions TO public;

DROP FUNCTION ___pgnui_functions.if_table_exists(_catalog varchar, _schema varchar, _table varchar);
CREATE FUNCTION ___pgnui_functions.if_table_exists(varchar, varchar, varchar) RETURNS BOOLEAN AS $$
	SELECT count(*) > 0 FROM information_schema.tables WHERE table_catalog = $1 AND table_schema = $2 AND table_name = $3;
$$ LANGUAGE SQL;
GRANT EXECUTE ON FUNCTION ___pgnui_functions.if_table_exists(varchar, varchar, varchar) TO public;
