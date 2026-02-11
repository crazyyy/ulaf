<?php

namespace Dev4Press\Plugin\SweepPress\Admin;

use Dev4Press\v54\Core\Admin\Help as CoreHelp;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Help extends CoreHelp {
	public function __construct( $admin ) {
		parent::__construct( $admin );

		if ( $this->panel() == 'dashboard' ) {
			$this->_for_dashboard();
		} else if ( $this->panel() == 'statistics' ) {
			$this->_for_statistics();
		} else if ( $this->panel() == 'sweep' ) {
			$this->_for_sweep();
		} else if ( $this->panel() == 'sweepers' ) {
			$this->_for_sweepers();
		} else if ( $this->panel() == 'options' ) {
			$this->_for_options();
		}

		if ( $this->panel() == 'dashboard' || $this->panel() == 'statistics' || $this->panel() == 'sweep' || $this->panel() == 'jobs' ) {
			$this->_for_every_panel();
		}
	}

	private function _for_every_panel() {
		$this->tab( 'every',
			__( 'Sweeping Process', 'sweeppress' ),
			'<p>' . __( 'There are few important things to know about the sweeping process.', 'sweeppress' ) . '</p>' .
			'<ul>' .
			'<li>' . __( 'All the sweepers use SQL queries to estimate the number of records to remove and data to save from the database.', 'sweeppress' ) . '</li>' .
			'<li>' . __( 'And, every sweeper is using only SQL queries to perform the data removal. The main reason for this approach is performance, and to ensure that each sweeper finishes as fast as possible, even with huge data sets that need to be removed.', 'sweeppress' ) . '</li>' .
			'</ul>' .
			'<p>' . __( 'As for the list of records for deletion, database size you should know few more things.', 'sweeppress' ) . '</p>' .
			'<ul>' .
			'<li>' . __( 'Displayed number of records and sizes for each sweeper are just estimations. Actual data size depends on the database server configuration, indexing, encoding and more.', 'sweeppress' ) . '</li>' .
			'<li>' . __( 'Estimated size is not taking into account index that tables always have (you have option to enable estimation using index too), and is estimation based on data size, so in most cases, actual gains after sweeping can be bigger than estimation.', 'sweeppress' ) . '</li>' .
			'<li>' . __( 'After the sweeper has finished, displayed information about deleted records and recovered database space is also just an estimation!', 'sweeppress' ) . '</li>' .
			'</ul>'
		);
	}

	private function _for_dashboard() {
		$this->tab( 'auto',
			__( 'Auto Sweep', 'sweeppress' ),
			'<p>' . __( 'The fastest way to clean up the WordPress database is by using Auto Sweep.', 'sweeppress' ) . '</p>' .
			'<ul>' .
			'<li>' . __( 'Auto Sweep will run most of the sweepers with all the tasks, but not all.', 'sweeppress' ) . '</li>' .
			'<li>' . __( 'Make sure you check the list of all Sweepers included and where they can be used from.', 'sweeppress' )
			. '<br/><a href="admin.php?page=sweeppress-sweepers">' . esc_html__( 'List of all Sweepers and where they can be used', 'sweeppress' ) . '</a></li>' .
			'</ul>'
		);

		$this->tab( 'quick',
			__( 'Quick Sweep', 'sweeppress' ),
			'<p>' . __( 'If you want to quickly choose what to clean up, Quick Sweep on Dashboard will do that.', 'sweeppress' ) . '</p>' .
			'<ul>' .
			'<li>' . __( 'This method shows quick overview of only available sweepers and tasks.', 'sweeppress' ) . '</li>' .
			'<li>' . __( 'You can choose one more sweepers and tasks to run from Dashboard.', 'sweeppress' ) . '</li>' .
			'<li>' . __( 'Quick Sweep will run most of the sweepers with all the tasks.', 'sweeppress' ) . '</li>' .
			'<li>' . __( 'Make sure you check the list of all Sweepers included and where they can be used from.', 'sweeppress' )
			. '<br/><a href="admin.php?page=sweeppress-sweepers">' . esc_html__( 'List of all Sweepers and where they can be used', 'sweeppress' ) . '</a></li>' .
			'</ul>'
		);
	}

	private function _for_sweep() {
		$this->tab( 'help',
			__( 'Sweeper', 'sweeppress' ),
			'<p>' . __( 'This panel shows all the sweepers, all the tasks and more about sweepers.', 'sweeppress' ) . '</p>' .
			'<ul>' .
			'<li>' . __( 'From this panel, you can run all available sweepers.', 'sweeppress' ) . '</li>' .
			'<li>' . __( 'For each sweeper, you can show explanation and other important information about each sweeper.', 'sweeppress' ) . '</li>' .
			'</ul>'
		);
	}

	private function _for_statistics() {
		$this->tab( 'help',
			__( 'Sweeping Statistics', 'sweeppress' ),
			'<p>' . __( 'The plugin keeps basic aggregated statistics information about sweepers cleanup, removed records and saved database space.', 'sweeppress' ) . '</p>' .
			'<ul>' .
			'<li>' . __( 'The plugin keeps overall statistics and the monthly statistics. Via this panel, you can view this by choosing from the dropdown.', 'sweeppress' ) . '</li>' .
			'</ul>'
		);
	}

	private function _for_sweepers() {
		$this->tab( 'help',
			__( 'Sweepers List', 'sweeppress' ),
			'<p>' . __( 'This panel shows the list of all available sweepers with the availability indicators.', 'sweeppress' ) . '</p>' .
			'<ul>' .
			'<li>' . __( 'For each sweeper you can see the category, name and code. Code is important for use with the REST API and WP-CLI.', 'sweeppress' ) . '</li>' .
			'<li>' . __( 'Indicator columns show the sweeper modes where the each sweeper can be used. All sweepers work from the main Sweep panel, but some sweepers have various limitations when it comes to availability.', 'sweeppress' ) . '</li>' .
			'</ul>'
		);
	}

	private function _for_options() {
		$this->tab( 'help',
			__( 'Options', 'sweeppress' ),
			'<p>' . __( 'This panel shows the options available in the WordPress `Options` database table with some exceptions.', 'sweeppress' ) . '</p>' .
			'<ul>' .
			'<li>' . __( 'Options added by the WordPress are not listed on this panel and SweepPress can\'t delete these options! It is a bad idea to remove any of the options added by WordPress by default!', 'sweeppress' ) . '</li>' .
			'<li>' . __( 'Transient cache records are not displayed on this panel.', 'sweeppress' ) . '</li>' .
			'<li>' . __( 'Widgets settings can be deleted. Plugin will show if the widget is registered or not.', 'sweeppress' ) . '</li>' .
			'<li>' . __( 'Options that are detected as installed and active (belonging to plugin or theme), will not have Delete option included.', 'sweeppress' ) . '</li>' .
			'</ul>' .
			'<p>' . __( 'SweepPress uses several methods to determine the source for the options inside the WordPress Options database table. It is important to know few thing about this process.', 'sweeppress' ) . '</p>' .
			'<ul>' .
			'<li>' . __( 'There is no guarantee that all sources are identified and there is no guarantee that the information presented on this panel is 100% correct.', 'sweeppress' ) . '</li>' .
			'<li>' . __( 'To determine which plugin or theme owns settings in the Options table, plugin uses regular expressions with predefined values, and the scanner to find these settings in the plugin or theme source code.', 'sweeppress' ) . '</li>' .
			'<li>' . __( 'Detection methods depend on plugins and themes being installed in the directory that plugin or theme authors initially released for. If you change the directory names for plugins or themes, SweepPress might not be able to use all detection methods properly.', 'sweeppress' ) . '</li>' .
			'<li>' . __( 'Metadata and Options usage monitoring is most reliable method to exactly find which options and metadata are used and what is the source.', 'sweeppress' ) . '</li>' .
			'</ul>'
		);
	}
}
