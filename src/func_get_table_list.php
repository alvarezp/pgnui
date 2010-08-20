<?

function get_best_name_for_table($dbconn, $this_table_schema, $this_table_table) {

		$comment_result = pg_query($dbconn, "SELECT obj_description('$this_table_schema.$this_table_table'::regclass, 'pg_class');");
		$comment_row = pg_fetch_row($comment_result);
		$comment = $comment_row[0];

		$visible_result = pg_query($dbconn, "SELECT pg_table_is_visible('$this_table_schema.$this_table_table'::regclass);");
		$visible_row = pg_fetch_row($visible_result);
		$visible = $visible_row[0];

		if ($comment != "") {
			return $comment;
		} elseif ($visible == "t") {
			return $this_table_table;
		} else {
			return $this_table_schema . "." . $this_table_table;
		}

}

function get_table_list($dbconn) {

	$searchpath_result = pg_query($dbconn, "SHOW search_path;");
	$searchpath_row = pg_fetch_row($searchpath_result);
	$searchpath = $searchpath_row[0];


	$r = pg_query($dbconn, "SELECT * FROM information_schema.tables WHERE table_type = 'BASE TABLE' AND is_insertable_into = 'YES' AND table_schema NOT IN ('information_schema', 'pg_catalog') AND table_schema NOT LIKE '___pgnui_%' AND table_name NOT LIKE '___pgnui_%';");

	$index = 0;
	while ($o = pg_fetch_row($r)) {
		$tables[$index]['schema'] = $o[1];
		$tables[$index]['table'] = $o[2];
		$tables[$index]['parameterstring'] = "schema=" . urlencode($tables[$index]['schema']) . "&table=" . urlencode($tables[$index]['table']);

		$this_table_schema = $tables[$index]['schema'];
		$this_table_table = $tables[$index]['table'];

		$tables[$index]['pretty_name'] = get_best_name_for_table($dbconn, $this_table_schema, $this_table_table);

		$index++;
	}

	return $tables;

}

?>


