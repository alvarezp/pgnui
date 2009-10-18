<?php

function record_columns_to_rowid($table_columns, $r) {

	$cid = 0;
	foreach($r as $col_name => $d):
		if ($table_columns[$cid][position_in_pk] > 0) {
			if ($rowid != "") $rowid .= "&";
			$rowid .= urlencode(urlencode($col_name)) . "=" . urlencode(urlencode($d)) . "";
		}
		$cid++;
	endforeach;

	return $rowid;
}

function rowid_to_record_columns($rowid) {

}

function rowid_to_where($rowid) {
	$string = urldecode($rowid);
	$string = preg_replace ("/&/", " AND ", $string);
	$string = preg_replace ("/=(.*) AND/", "='$1' AND", $string);
	$string = preg_replace ("/=(.*)$/", "='$1'", $string);
	$string = urldecode($string);

	return $string;
}

?>
