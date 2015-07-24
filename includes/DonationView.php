<?php

class DonationView {

	public function __construct() {

	}	

	public function render( $view, $echo = true ) {
		$view = strtolower($view);

		$filepath = GDONATE_VIEWS . "/" . $view . ".php";

		if( file_exists($filepath) ) {
			if( $echo ) {
				require $filepath;
			} else {
				ob_start();

				require $filepath;

				$html = ob_get_contents();

				ob_end_clean();

				return $html;
			}
		}
	}
}