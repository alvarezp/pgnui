<?php

function get_best_name_for_this_database($dbconn) {

		$comment_result = pg_query($dbconn, "SELECT pg_catalog.shobj_description((SELECT oid FROM pg_catalog.pg_database WHERE datname = current_catalog), 'pg_database');");
		$comment_row = pg_fetch_row($comment_result);
		$comment = $comment_row[0];

		$rawname_result = pg_query($dbconn, "SELECT current_catalog;");
		$rawname_row = pg_fetch_row($rawname_result);
		$rawname = $rawname_row[0];

		if ($comment != "") {
			return $comment;
		} else {
			return preg_replace("/_/", " ", ucfirst($rawname));
		}

}

?>


