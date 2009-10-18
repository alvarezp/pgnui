<?php

abstract class HtmlControl {

	abstract public function get_supported_types();
	abstract public function get_sql_value();
	abstract public function set_value_from_sql($v);
	abstract public function set_value_from_post($v);
	abstract public function get_html_static($basename);
	abstract public function get_html_editable($basename);
	abstract public function get_sql_update_from_diff($bef, $aft);

}

?>
