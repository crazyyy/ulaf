<?php
/**
 * Class used to implement the back-end functionalities of the "Juice" menu.
 *
 * @package daext-interlinks-manager
 */

/**
 * Class used to implement the back-end functionalities of the "Juice" menu.
 */
class Daextinma_Juice_Menu_Elements extends Daextinma_Menu_Elements {

	/**
	 * Constructor.
	 *
	 * @param object $shared The shared class.
	 * @param string $page_query_param The page query parameter.
	 * @param string $config The config parameter.
	 */
	public function __construct( $shared, $page_query_param, $config ) {

		parent::__construct( $shared, $page_query_param, $config );

		$this->menu_slug          = 'juice';
		$this->slug_plural        = 'juice';
		$this->label_singular     = __( 'Juice', 'daext-interlinks-manager' );
		$this->label_plural       = __( 'Juice', 'daext-interlinks-manager' );
		$this->primary_key        = 'category_id';
		$this->db_table           = 'category';
		$this->list_table_columns = array(
			array(
				'db_field' => 'name',
				'label'    => __( 'Name', 'daext-interlinks-manager' ),
			),
			array(
				'db_field' => 'description',
				'label'    => __( 'Description', 'daext-interlinks-manager' ),
			),
		);
		$this->searchable_fields  = array(
			'name',
			'description',
		);
	}

	/**
	 * Display the content of the body of the page.
	 *
	 * @return void
	 */
	public function display_custom_content() {

		?>

		<div id="react-root"></div>

		<?php
	}
}
