<?php

require_once ("html_control.php");

class HtmlControlTextbox extends HtmlControl {

	private $value;

	public function get_supported_types() {
		return array(
			array(type => 'character varying', priority => '0')
		);
	}

	public function get_sql_value() {
		if (isset($his->value)) {
			return $this->value;
		} else {
			return NULL;
		}
	}

	public function set_value_from_sql($v) {
		$this->value = $v;
	}

	public function set_value_from_post($v) {
		$this->value = $v;
	}

	public function get_sql_update_from_diff($bef, $aft) {
		if ($bef[value] != $aft[value]) {
			if ($aft[value] == '') {
				return array(change => 'yes', value => NULL);
			} else {
				return array(change => 'yes', value => $aft[value]);
			}
		}
	}

	public function get_html_editable($basename) {
		$r = "";

		$r .= "<span class='textbox' id='${basename}'>\n";

		if (isset($this->value)) {
			$r .= "<input type='hidden' name='${basename}[bef][value]' value='$this->value'></input>\n";
			$r .= "<input id='${basename}[aft][value]' type='text' name='${basename}[aft][value]' class='textbox' value='$this->value'></input>\n";
		} else {
			$r .= "<input type='hidden' name='${basename}[bef][value]' value=''></input>\n";
			$r .= "<input id='${basename}[aft][value]' type='text' name='${basename}[aft][value]' class='textbox' value=''></input>\n";
		}
		$r .= "</span>";

		return $r;
	}

	public function get_html_static($basename) {
		$r = "";

		$r .= "<span class='textbox' id='${basename}'>\n";

		if (isset($this->value)) {
			$r .= "<span class='textbox_known'>$this->value</span>\n";
		} else {
			$r .= "<span class='textbox_unknown'>Not set</span>\n";
		}
		$r .= "</span>";

		return $r;
	}

}

?>
