<?php

class Debug_Bar_Hook_Panel extends Debug_Bar_Panel {
	public function init() {
		$this->title( 'Action Log' );
		$this->set_visible( true );
	}

	public function render() {
		$hooks = Hook_Log::$hooks;
		$filter_groups = Hook_Log::$filter_groups;
		include Debug_Bar_Hook_Log::plugin_dir( 'views/render-hooks.php' );
	}
}
