<?php

set_include_path(get_include_path() . PATH_SEPARATOR . '../../');

require_once ("html_control.php");

class HtmlControlDropdown extends HtmlControl {

	private $value;
	private $option_list;

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

	public function set_option_list($a) {
		$this->option_list = $a;
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

		$r .= "<span class='dropdown' id='${basename}'>\n";

		if (isset($this->value)) {
			$r .= "<input type='hidden' name='${basename}[bef][value]' value='$this->value'></input>\n";
			$r .= "<select id='${basename}[aft][value]' type='text' name='${basename}[aft][value]' class='dropdown' value='$this->value'>";
			if ($this->option_list) {
				foreach ($this->option_list as $v) {
					foreach ($v as $f) {
						$r .= " <option value='$f'>$f</option>";
					}
				}
			}
			$r .= "</select>\n";
		} else {
			$r .= "<input type='hidden' name='${basename}[bef][value]' value=''></input>\n";
			$r .= "<select id='${basename}[aft][value]' type='text' name='${basename}[aft][value]' class='dropdown' value=''>";
			if ($this->option_list) {
				foreach ($this->option_list as $v) {
					foreach ($v as $f) {
						$r .= " <option value='$f'>$f</option>";
					}
				}
			}
			$r .= "</select>\n";
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
