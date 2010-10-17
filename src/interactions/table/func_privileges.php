<?
function has_table_privilege($dbconn, $table, $privilege) {
	$can_insert_r = pg_query_params($dbconn, "SELECT has_table_privilege($1, $2);", array($table, $privilege));
	if ($can_insert_r != NULL) {
		$can_insert_a = pg_fetch_all($can_insert_r);
		$r = $can_insert_a[0]['has_table_privilege'];
		return $r == "t";
	} else {
		return NULL;
	}
}

?>
