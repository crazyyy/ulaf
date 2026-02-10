<?php
namespace Yipresser\AdminOptimizer\Modules;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Taxonomies_Settings class
 */
class Taxonomies_Settings {
	/**
	 * Settings fields
	 *
	 * @var array
	 */
	protected $setting_fields;

	/**
	 * User defined taxonomies
	 *
	 * @var array
	 */
	protected $taxonomies;

	/**
	 * Constructor
	 *
	 * @param array $taxonomies User defined taxonomies.
	 */
	public function __construct( $taxonomies ) {
		$this->taxonomies = $taxonomies;
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	/**
	 * Add Settings fields
	 *
	 * @return void
	 */
	private function add_setting_fields() {
		$this->setting_fields = [
			'required' => [
				[
					'id'          => 'taxonomy-slug',
					'type'        => 'text',
					'name'        => 'taxonomy[slug]',
					'key'         => 'slug',
					'key_type'    => 'option',
					'value'       => '',
					'label'       => __( 'Slug', 'admin-optimizer' ),
					'description' => __( 'The "slug" is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens.', 'admin-optimizer' ),
					'required'    => true,
				],
				[
					'id'          => 'taxonomy-name',
					'type'        => 'text',
					'name'        => 'taxonomy[labels][name]',
					'key'         => 'name',
					'key_type'    => 'label',
					'value'       => '',
					'label'       => __( 'Taxonomy Name', 'admin-optimizer' ),
					'description' => __( 'The name of the custom taxonomy. Usually plural.', 'admin-optimizer' ),
					'required'    => true,
				],
				[
					'id'          => 'taxonomy-singular-label',
					'type'        => 'text',
					'name'        => 'taxonomy[labels][singular_name]',
					'key'         => 'singular_name',
					'key_type'    => 'label',
					'value'       => '',
					'label'       => __( 'Singular Label', 'admin-optimizer' ),
					'description' => __( 'The singular label of the custom taxonomy.', 'admin-optimizer' ),
					'required'    => true,
				],
				[
					'id'          => 'taxonomy-description',
					'type'        => 'textarea',
					'name'        => 'taxonomy[description]',
					'key'         => 'description',
					'key_type'    => 'option',
					'value'       => '',
					'label'       => __( 'Description', 'admin-optimizer' ),
					'description' => __( 'Describe what this custom taxonomy is used for.', 'admin-optimizer' ),
					'required'    => false,
				],
			],
			'labels'   => [
				[
					'id'          => 'taxonomy-search-items-label',
					'type'        => 'text',
					'name'        => 'taxonomy[labels][search_items]',
					'key'         => 'search_items',
					'key_type'    => 'label',
					'value'       => '',
					'label'       => __( 'Search Items', 'admin-optimizer' ),
					'description' => __( 'Label for searching plural items. Default is \'Search Tags\' / \'Search Categories\'.', 'admin-optimizer' ),
					'required'    => false,
					'placeholder' => __( '(e.g. Search Books)', 'admin-optimizer' ),
				],
				[
					'id'          => 'taxonomy-popular-items-label',
					'type'        => 'text',
					'name'        => 'taxonomy[labels][popular_items]',
					'key'         => 'popular_items',
					'key_type'    => 'label',
					'value'       => '',
					'label'       => __( 'Popular Items', 'admin-optimizer' ),
					'description' => __( 'This label is only used for non-hierarchical taxonomies. Default \'Popular Tags\'.', 'admin-optimizer' ),
					'required'    => false,
					'placeholder' => '',
				],
				[
					'id'          => 'taxonomy-all-items-label',
					'type'        => 'text',
					'name'        => 'taxonomy[labels][all_items]',
					'key'         => 'all_items',
					'key_type'    => 'label',
					'value'       => '',
					'label'       => __( 'All Items', 'admin-optimizer' ),
					'description' => __( 'Label to signify all items in a submenu link. Default is \'All Tags\' / \'All Categories\'.', 'admin-optimizer' ),
					'required'    => false,
					'placeholder' => '',
				],
				[
					'id'          => 'taxonomy-parent-item-label',
					'type'        => 'text',
					'name'        => 'taxonomy[labels][parent_item]',
					'key'         => 'parent_item',
					'key_type'    => 'label',
					'value'       => '',
					'label'       => __( 'Parent Item', 'admin-optimizer' ),
					'description' => __( 'This label is only used for hierarchical taxonomies. Default \'Parent Category\'.', 'admin-optimizer' ),
					'required'    => false,
					'placeholder' => '',
				],
				[
					'id'          => 'taxonomy-parent-item-colon-label',
					'type'        => 'text',
					'name'        => 'taxonomy[labels][parent_item_colon]',
					'key'         => 'parent_item_colon',
					'key_type'    => 'label',
					'value'       => '',
					'label'       => __( 'Parent Item Colon', 'admin-optimizer' ),
					'description' => __( 'The same as parent_item, but with colon : in the end.', 'admin-optimizer' ),
					'required'    => false,
					'placeholder' => '',
				],
				[
					'id'          => 'taxonomy-name-field-description-label',
					'type'        => 'text',
					'name'        => 'taxonomy[labels][name_field_description]',
					'key'         => 'name_field_description',
					'key_type'    => 'label',
					'value'       => '',
					'label'       => __( 'Name Field Description', 'admin-optimizer' ),
					'description' => __( 'Description for the Name field on Edit Tags screen. Default \'The name is how it appears on your site\'.', 'admin-optimizer' ),
					'required'    => false,
					'placeholder' => '',
				],
				[
					'id'          => 'taxonomy-slug-field-description-label',
					'type'        => 'text',
					'name'        => 'taxonomy[labels][slug_field_description]',
					'key'         => 'slug_field_description',
					'key_type'    => 'label',
					'value'       => '',
					'label'       => __( 'Slug Field Description', 'admin-optimizer' ),
					'description' => __( 'Description for the Slug field on Edit Tags screen. Default \'The “slug” is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens\'.', 'admin-optimizer' ),
					'required'    => false,
					'placeholder' => '',
				],
				[
					'id'          => 'taxonomy-parent-field-description-label',
					'type'        => 'text',
					'name'        => 'taxonomy[labels][parent_field_description]',
					'key'         => 'parent_field_description',
					'key_type'    => 'label',
					'value'       => '',
					'label'       => __( 'Parent Field Description', 'admin-optimizer' ),
					'description' => __( 'Description for the Parent field on Edit Tags screen. Default \'Assign a parent term to create a hierarchy\'.', 'admin-optimizer' ),
					'required'    => false,
					'placeholder' => '',
				],
				[
					'id'          => 'taxonomy-desc-field-description-label',
					'type'        => 'text',
					'name'        => 'taxonomy[labels][desc_field_description]',
					'key'         => 'desc_field_description',
					'key_type'    => 'label',
					'value'       => '',
					'label'       => __( 'Desc Field Description', 'admin-optimizer' ),
					'description' => __( 'Description for the Description field on Edit Tags screen. Default \'The description is not prominent by default; however, some themes may show it\'.', 'admin-optimizer' ),
					'required'    => false,
					'placeholder' => '',
				],
				[
					'id'          => 'taxonomy-edit-item-label',
					'type'        => 'text',
					'name'        => 'taxonomy[labels][edit_item]',
					'key'         => 'edit_item',
					'key_type'    => 'label',
					'value'       => '',
					'label'       => __( 'Edit Item', 'admin-optimizer' ),
					'description' => __( 'Label for editing a taxonomy. Default \'Edit Tag\'/\'Edit Category\'.', 'admin-optimizer' ),
					'required'    => false,
					'placeholder' => '',
				],
				[
					'id'          => 'taxonomy-view-item-label',
					'type'        => 'text',
					'name'        => 'taxonomy[labels][view_item]',
					'key'         => 'view_item',
					'key_type'    => 'label',
					'value'       => '',
					'label'       => __( 'View Item', 'admin-optimizer' ),
					'description' => __( 'Label for viewing a taxonomy. Default \'View Tag\'/\'View Category\'.', 'admin-optimizer' ),
					'required'    => false,
					'placeholder' => '',
				],
				[
					'id'          => 'taxonomy-update-item-label',
					'type'        => 'text',
					'name'        => 'taxonomy[labels][update_item]',
					'key'         => 'update_item',
					'key_type'    => 'label',
					'value'       => '',
					'label'       => __( 'Update Item', 'admin-optimizer' ),
					'description' => __( 'Label for updating a taxonomy. Default \'Update Tag\'/\'Update Category\'.', 'admin-optimizer' ),
					'required'    => false,
					'placeholder' => '',
				],
				[
					'id'          => 'taxonomy-add-new-item-label',
					'type'        => 'text',
					'name'        => 'taxonomy[labels][add_new_item]',
					'key'         => 'add_new_item',
					'key_type'    => 'label',
					'value'       => '',
					'label'       => __( 'Add New Item', 'admin-optimizer' ),
					'description' => __( 'Label for adding a new taxonomy. Default is \'Add Tag\' / \'Add Category\'.', 'admin-optimizer' ),
					'required'    => false,
					'placeholder' => '',
				],
				[
					'id'          => 'taxonomy-new-item-name-label',
					'type'        => 'text',
					'name'        => 'taxonomy[labels][new_item_name]',
					'key'         => 'new_item_name',
					'key_type'    => 'label',
					'value'       => '',
					'label'       => __( 'New Item Name', 'admin-optimizer' ),
					'description' => __( 'Label for adding a new taxonomy. Default is \'New Tag Name\' / \'New Category Name\'.', 'admin-optimizer' ),
					'required'    => false,
					'placeholder' => '',
				],
				[
					'id'          => 'taxonomy-template-name-label',
					'type'        => 'text',
					'name'        => 'taxonomy[labels][template_name]',
					'key'         => 'template_name',
					'key_type'    => 'label',
					'value'       => '',
					'label'       => __( 'Template Name', 'admin-optimizer' ),
					'description' => __( 'Default \'Tag Archives\'/\'Category Archives\'.', 'admin-optimizer' ),
					'required'    => false,
					'placeholder' => '',
				],
				[
					'id'          => 'taxonomy-separate-items-with-commas-label',
					'type'        => 'text',
					'name'        => 'taxonomy[labels][separate_items_with_commas]',
					'key'         => 'separate_items_with_commas',
					'key_type'    => 'label',
					'value'       => '',
					'label'       => __( 'Separate Items with commas', 'admin-optimizer' ),
					'description' => __( 'This label is only used for non-hierarchical taxonomies. Default \'Separate tags with commas\', used in the meta box.', 'admin-optimizer' ),
					'required'    => false,
					'placeholder' => '',
				],
				[
					'id'          => 'taxonomy-add-remove-items-label',
					'type'        => 'text',
					'name'        => 'taxonomy[labels][add_or_remove_items]',
					'key'         => 'add_or_remove_items',
					'key_type'    => 'label',
					'value'       => '',
					'label'       => __( 'Add or remove items', 'admin-optimizer' ),
					'description' => __( 'This label is only used for non-hierarchical taxonomies. Default \'Add or remove tags\', used in the meta box when JavaScript is disabled.', 'admin-optimizer' ),
					'required'    => false,
					'placeholder' => '',
				],
				[
					'id'          => 'taxonomy-choose-from-most-used-label',
					'type'        => 'text',
					'name'        => 'taxonomy[labels][choose_from_most_used]',
					'key'         => 'choose_from_most_used',
					'key_type'    => 'label',
					'value'       => '',
					'label'       => __( 'Choose from most used', 'admin-optimizer' ),
					'description' => __( 'This label is only used on non-hierarchical taxonomies. Default \'Choose from the most used tags\', used in the meta box.', 'admin-optimizer' ),
					'required'    => false,
					'placeholder' => '',
				],
				[
					'id'          => 'taxonomy-not-found-label',
					'type'        => 'text',
					'name'        => 'taxonomy[labels][not_found]',
					'key'         => 'not_found',
					'key_type'    => 'label',
					'value'       => '',
					'label'       => __( 'Not Found', 'admin-optimizer' ),
					'description' => __( 'Default \'No tags found\'/\'No categories found\', used in the meta box and taxonomy list table.', 'admin-optimizer' ),
					'required'    => false,
					'placeholder' => '',
				],
				[
					'id'          => 'taxonomy-no-terms-label',
					'type'        => 'text',
					'name'        => 'taxonomy[labels][no_terms]',
					'key'         => 'no_terms',
					'key_type'    => 'label',
					'value'       => '',
					'label'       => __( 'No Terms', 'admin-optimizer' ),
					'description' => __( 'Default \'No tags\'/\'No categories\', used in the posts and media list tables.', 'admin-optimizer' ),
					'required'    => false,
					'placeholder' => '',
				],
				[
					'id'          => 'taxonomy-filter-by-item-label',
					'type'        => 'text',
					'name'        => 'taxonomy[labels][filter_by_item]',
					'key'         => 'filter_by_item',
					'key_type'    => 'label',
					'value'       => '',
					'label'       => __( 'Filter by Item', 'admin-optimizer' ),
					'description' => __( 'This label is only used for hierarchical taxonomies. Default \'Filter by category\', used in the posts list table.', 'admin-optimizer' ),
					'required'    => false,
					'placeholder' => '',
				],
				[
					'id'          => 'taxonomy-items-list-navigation-label',
					'type'        => 'text',
					'name'        => 'taxonomy[labels][items_list_navigation]',
					'key'         => 'items_list_navigation',
					'key_type'    => 'label',
					'value'       => '',
					'label'       => __( 'Items List Navigation', 'admin-optimizer' ),
					'description' => __( 'Label for the table pagination hidden heading.', 'admin-optimizer' ),
					'required'    => false,
					'placeholder' => '',
				],
				[
					'id'          => 'taxonomy-items-list-label',
					'type'        => 'text',
					'name'        => 'taxonomy[labels][items_list]',
					'key'         => 'items_list',
					'key_type'    => 'label',
					'value'       => '',
					'label'       => __( 'Items List', 'admin-optimizer' ),
					'description' => __( 'Label for the table hidden heading.', 'admin-optimizer' ),
					'required'    => false,
					'placeholder' => '',
				],
				[
					'id'          => 'taxonomy-most-used-label',
					'type'        => 'text',
					'name'        => 'taxonomy[labels][most_used]',
					'key'         => 'most_used',
					'key_type'    => 'label',
					'value'       => '',
					'label'       => __( 'Most Used', 'admin-optimizer' ),
					'description' => __( 'Title for the Most Used tab. Default \'Most Used\'.', 'admin-optimizer' ),
					'required'    => false,
					'placeholder' => '',
				],
				[
					'id'          => 'taxonomy-back-to-items-label',
					'type'        => 'text',
					'name'        => 'taxonomy[labels][back_to_items]',
					'key'         => 'back_to_items',
					'key_type'    => 'label',
					'value'       => '',
					'label'       => __( 'Back to Items', 'admin-optimizer' ),
					'description' => __( 'Label displayed after a term has been updated.', 'admin-optimizer' ),
					'required'    => false,
					'placeholder' => '',
				],
				[
					'id'          => 'taxonomy-items-link-label',
					'type'        => 'text',
					'name'        => 'taxonomy[labels][item_link]',
					'key'         => 'item_link',
					'key_type'    => 'label',
					'value'       => '',
					'label'       => __( 'Items Link', 'admin-optimizer' ),
					'description' => __( 'Used in the block editor. Title for a navigation link block variation. Default \'Tag Link\'/\'Category Link\'.', 'admin-optimizer' ),
					'required'    => false,
					'placeholder' => '',
				],
				[
					'id'          => 'taxonomy-items-link-description-label',
					'type'        => 'text',
					'name'        => 'taxonomy[labels][item_link_description]',
					'key'         => 'item_link_description',
					'key_type'    => 'label',
					'value'       => '',
					'label'       => __( 'Items Link Description', 'admin-optimizer' ),
					'description' => __( 'Used in the block editor. Description for a navigation link block variation. Default \'A link to a tag\'/\'A link to a category\'.', 'admin-optimizer' ),
					'required'    => false,
					'placeholder' => '',
				],
			],
		];
		$custom_post_types    = get_post_types(
			[
				'_builtin' => false,
				'show_ui'  => 'true',
			],
			'objects'
		);
		$custom_post_types    = array_diff_key( $custom_post_types, array_fill_keys( [ 'lazyblocks','lazyblocks_templates','attachment', 'wp_block', 'wp_navigation' ], true ) );

		$taxonomy = [];

		foreach ( $custom_post_types as $custom_post_type ) {
			$taxonomy[ $custom_post_type->name ] = $custom_post_type->label;
		}
		$taxonomy                        = array_merge(
			[
				'post' => 'Post',
				'page' => 'Page',
			],
			$taxonomy
		);
		$taxonomys_field                 = [
			'id'       => 'taxonomy-posttype',
			'type'     => 'checkboxes',
			'choices'  => $taxonomy,
			'checked'  => [],
			'name'     => 'taxonomy[posttypes][]',
			'key'      => 'posttypes',
			'key_type' => 'posttypes',
			'label'    => __( 'Associate with the following post types', 'admin-optimizer' ),
			'required' => false,
		];
		$this->setting_fields['options'] = [
			[
				'id'          => 'taxonomy-public',
				'type'        => 'select',
				'choices'     => [
					'0' => __( 'No', 'admin-optimizer' ),
					'1' => __( 'Yes', 'admin-optimizer' ),
				],
				'name'        => 'taxonomy[public]',
				'key'         => 'public',
				'key_type'    => 'option',
				'value'       => '',
				'label'       => __( 'Public', 'admin-optimizer' ),
				'description' => __( 'Is this taxonomy intended for use publicly either via the admin interface or by front-end users?', 'admin-optimizer' ),
				'required'    => false,
			],
			[
				'id'          => 'taxonomy-publicly-queryable',
				'type'        => 'select',
				'choices'     => [
					'-1' => __( 'Leave it as default', 'admin-optimizer' ),
					'1'  => __( 'Yes', 'admin-optimizer' ),
					'0'  => __( 'No', 'admin-optimizer' ),

				],
				'name'        => 'taxonomy[publicly_queryable]',
				'key'         => 'publicly_queryable',
				'key_type'    => 'option',
				'value'       => '',
				'label'       => __( 'Publicly queryable', 'admin-optimizer' ),
				'description' => __( 'Can queries be performed on the front end for this taxonomy? The default is inherited from Public.', 'admin-optimizer' ),
				'required'    => false,
			],
			[
				'id'          => 'taxonomy-hierarchical',
				'type'        => 'select',
				'choices'     => [
					'0' => __( 'No', 'admin-optimizer' ),
					'1' => __( 'Yes', 'admin-optimizer' ),
				],
				'name'        => 'taxonomy[hierarchical]',
				'key'         => 'hierarchical',
				'key_type'    => 'option',
				'value'       => '',
				'label'       => __( 'Hierarchical', 'admin-optimizer' ),
				'description' => __( 'Is this taxonomy hierarchical? Default no', 'admin-optimizer' ),
				'required'    => false,
			],
			[
				'id'          => 'taxonomy-show-ui',
				'type'        => 'select',
				'choices'     => [
					'-1' => __( 'Leave it as default', 'admin-optimizer' ),
					'1'  => __( 'Yes', 'admin-optimizer' ),
					'0'  => __( 'No', 'admin-optimizer' ),

				],
				'name'        => 'taxonomy[show_ui]',
				'key'         => 'show_ui',
				'key_type'    => 'option',
				'value'       => '',
				'label'       => __( 'Show UI', 'admin-optimizer' ),
				'description' => __( 'Allow a UI for managing this taxonomy in the admin? The default is inherited from Public.', 'admin-optimizer' ),
				'required'    => false,
			],
			[
				'id'          => 'taxonomy-show-in-menu',
				'type'        => 'select',
				'choices'     => [
					'-1' => __( 'Leave it as default', 'admin-optimizer' ),
					'1'  => __( 'Yes', 'admin-optimizer' ),
					'0'  => __( 'No', 'admin-optimizer' ),

				],
				'name'        => 'taxonomy[show_in_menu]',
				'key'         => 'show_in_menu',
				'key_type'    => 'option',
				'value'       => '',
				'label'       => __( 'Show in Menu', 'admin-optimizer' ),
				'description' => __( 'Whether to show the taxonomy in the admin menu. If true, the taxonomy is shown as a submenu of the object type menu. If false, no menu is shown. The default is inherited from show_ui.', 'admin-optimizer' ),
				'required'    => false,
			],
			[
				'id'          => 'taxonomy-show-in-nav-menus',
				'type'        => 'select',
				'choices'     => [
					'-1' => __( 'Leave it as default', 'admin-optimizer' ),
					'1'  => __( 'Yes', 'admin-optimizer' ),
					'0'  => __( 'No', 'admin-optimizer' ),

				],
				'name'        => 'taxonomy[show_in_nav_menus]',
				'key'         => 'show_in_nav_menus',
				'key_type'    => 'option',
				'value'       => '',
				'label'       => __( 'Show in Nav Menu', 'admin-optimizer' ),
				'description' => __( 'Makes this taxonomy available for selection in navigation menus. The default is the same as Public.', 'admin-optimizer' ),
				'required'    => false,
			],
			[
				'id'          => 'taxonomy-show-in-rest',
				'type'        => 'select',
				'choices'     => [
					'1' => __( 'Yes', 'admin-optimizer' ),
					'0' => __( 'No', 'admin-optimizer' ),

				],
				'name'        => 'taxonomy[show_in_rest]',
				'key'         => 'show_in_rest',
				'key_type'    => 'option',
				'value'       => '',
				'label'       => __( 'Show in REST API', 'admin-optimizer' ),
				'description' => __( 'Include the taxonomy in the REST API? Set this to yes for the taxonomy to be available in the block editor.', 'admin-optimizer' ),
				'required'    => false,
			],
			[
				'id'          => 'taxonomy-rest-base',
				'type'        => 'text',
				'name'        => 'taxonomy[rest_base]',
				'key'         => 'rest_base',
				'key_type'    => 'option',
				'value'       => '',
				'label'       => __( 'REST API Base', 'admin-optimizer' ),
				'description' => __( 'Change the base URL of REST API route. Default is this taxonomy slug.', 'admin-optimizer' ),
				'required'    => false,
				'placeholder' => __( 'Leave blank if you are not sure.', 'admin-optimizer' ),
			],
			[
				'id'          => 'taxonomy-rest-namespace',
				'type'        => 'text',
				'name'        => 'taxonomy[rest_namespace]',
				'key'         => 'rest_namespace',
				'key_type'    => 'option',
				'value'       => '',
				'label'       => __( 'REST Namespace', 'admin-optimizer' ),
				'description' => __( 'Change the namespace URL of REST API route. Default is "wp/v2".', 'admin-optimizer' ),
				'required'    => false,
				'placeholder' => __( 'Leave blank if you are not sure.', 'admin-optimizer' ),
			],
			[
				'id'          => 'taxonomy-rest-controller-class',
				'type'        => 'text',
				'name'        => 'taxonomy[rest_controller_class]',
				'key'         => 'rest_controller_class',
				'key_type'    => 'option',
				'value'       => '',
				'label'       => __( 'REST Controller Class', 'admin-optimizer' ),
				'description' => __( 'REST API controller class name. Default is \'WP_REST_Posts_Controller\'.', 'admin-optimizer' ),
				'required'    => false,
				'placeholder' => __( 'Leave blank if you are not sure.', 'admin-optimizer' ),
			],
			[
				'id'          => 'taxonomy-show-tagcloud',
				'type'        => 'select',
				'choices'     => [
					'1' => __( 'Yes', 'admin-optimizer' ),
					'0' => __( 'No', 'admin-optimizer' ),

				],
				'name'        => 'taxonomy[show_tagcloud]',
				'key'         => 'show_tagcloud',
				'key_type'    => 'option',
				'value'       => '',
				'label'       => __( 'Show Tagcloud', 'admin-optimizer' ),
				'description' => __( 'Whether to list the taxonomy in the Tag Cloud Widget controls. If not set, the default is inherited from show_ui.', 'admin-optimizer' ),
				'required'    => false,
			],
			[
				'id'          => 'taxonomy-show-in-quick-edit',
				'type'        => 'select',
				'choices'     => [
					'1' => __( 'Yes', 'admin-optimizer' ),
					'0' => __( 'No', 'admin-optimizer' ),

				],
				'name'        => 'taxonomy[show_in_quick_edit]',
				'key'         => 'show_in_quick_edit',
				'key_type'    => 'option',
				'value'       => '',
				'label'       => __( 'Show in Quick Edit', 'admin-optimizer' ),
				'description' => __( 'Whether to show the taxonomy in the quick/bulk edit panel. If not set, the default is inherited from show_ui.', 'admin-optimizer' ),
				'required'    => false,
			],
			[
				'id'          => 'taxonomy-show-admin-column',
				'type'        => 'select',
				'choices'     => [
					'0' => __( 'No', 'admin-optimizer' ),
					'1' => __( 'Yes', 'admin-optimizer' ),
				],
				'name'        => 'taxonomy[show_admin_column]',
				'key'         => 'show_admin_column',
				'key_type'    => 'option',
				'value'       => '',
				'label'       => __( 'Show Admin Column', 'admin-optimizer' ),
				'description' => __( 'Whether to display a column for the taxonomy on its post type listing screens. Default no.', 'admin-optimizer' ),
				'required'    => false,
			],
			[
				'id'          => 'taxonomy-use-rewrite',
				'type'        => 'select',
				'choices'     => [
					'-1' => __( 'Leave it as default', 'admin-optimizer' ),
					'1'  => __( 'Yes', 'admin-optimizer' ),
					'0'  => __( 'No', 'admin-optimizer' ),
				],
				'name'        => 'taxonomy[use_rewrite]',
				'key'         => 'use_rewrite',
				'key_type'    => 'option',
				'value'       => '',
				'label'       => __( 'Rewrite', 'admin-optimizer' ),
				'description' => __( 'Triggers the handling of rewrites for this taxonomy. Default true.', 'admin-optimizer' ),
				'required'    => false,
			],
			[
				'id'          => 'taxonomy-rewrite-slug',
				'type'        => 'text',
				'name'        => 'taxonomy[rewrite][slug]',
				'key'         => 'rewrite_slug',
				'key_type'    => 'rewrite',
				'value'       => '',
				'label'       => __( 'Custom Rewrite Slug', 'admin-optimizer' ),
				'description' => __( 'Custom taxonomy slug to use instead of the default.', 'admin-optimizer' ),
				'required'    => false,
			],
			[
				'id'          => 'taxonomy-rewrite-with-front',
				'type'        => 'select',
				'choices'     => [
					'-1' => __( 'Leave it as default', 'admin-optimizer' ),
					'1'  => __( 'Yes', 'admin-optimizer' ),
					'0'  => __( 'No', 'admin-optimizer' ),
				],
				'name'        => 'taxonomy[rewrite][with_front]',
				'key'         => 'with_front',
				'key_type'    => 'rewrite',
				'value'       => '',
				'label'       => __( 'With Front', 'admin-optimizer' ),
				'description' => __( 'Should the permalink structure be prepended with the front base. (example: if your permalink structure is /blog/, then your permalink will be /blog/books/). Default is true', 'admin-optimizer' ),
				'required'    => false,
			],
			[
				'id'          => 'taxonomy-rewrite-hierarchical',
				'type'        => 'select',
				'choices'     => [
					'-1' => __( 'Leave it as default', 'admin-optimizer' ),
					'0'  => __( 'No', 'admin-optimizer' ),
					'1'  => __( 'Yes', 'admin-optimizer' ),
				],
				'name'        => 'taxonomy[rewrite][hierarchical]',
				'key'         => 'hierarchical',
				'key_type'    => 'rewrite',
				'value'       => '',
				'label'       => __( 'Hierarchical', 'admin-optimizer' ),
				'description' => __( 'Either hierarchical rewrite tag or not. Default No.', 'admin-optimizer' ),
				'required'    => false,
			],
			[
				'id'          => 'taxonomy-query-var',
				'type'        => 'select',
				'choices'     => [
					'1' => __( 'Yes', 'admin-optimizer' ),
					'0' => __( 'No', 'admin-optimizer' ),
				],
				'name'        => 'taxonomy[query_var]',
				'key'         => 'query_var',
				'key_type'    => 'option',
				'value'       => '',
				'label'       => __( 'Query Var', 'admin-optimizer' ),
				'description' => __( 'Sets the query_var key for this taxonomy. Default is true', 'admin-optimizer' ),
				'required'    => false,
			],
			$taxonomys_field,
		];
		$this->setting_fields['capabilities'] = [
			[
				'id'          => 'taxonomy-capability-manage-terms',
				'type'        => 'text',
				'name'        => 'taxonomy[capabilities][manage_terms]',
				'key'         => 'manage_terms',
				'key_type'    => 'capabilities',
				'value'       => '',
				'label'       => __( 'Manage Terms', 'admin-optimizer' ),
				'description' => __( 'Default \'manage_categories\'.', 'admin-optimizer' ),
				'required'    => false,
			],
			[
				'id'          => 'taxonomy-capability-edit-terms',
				'type'        => 'text',
				'name'        => 'taxonomy[capabilities][edit_terms]',
				'key'         => 'edit_terms',
				'key_type'    => 'capabilities',
				'value'       => '',
				'label'       => __( 'Edit Terms', 'admin-optimizer' ),
				'description' => __( 'Default \'manage_categories\'.', 'admin-optimizer' ),
				'required'    => false,
			],
			[
				'id'          => 'taxonomy-capability-delete-terms',
				'type'        => 'text',
				'name'        => 'taxonomy[capabilities][delete_terms]',
				'key'         => 'delete_terms',
				'key_type'    => 'capabilities',
				'value'       => '',
				'label'       => __( 'Delete Terms', 'admin-optimizer' ),
				'description' => __( 'Default \'manage_categories\'.', 'admin-optimizer' ),
				'required'    => false,
			],
			[
				'id'          => 'taxonomy-capability-assign-terms',
				'type'        => 'text',
				'name'        => 'taxonomy[capabilities][assign_terms]',
				'key'         => 'assign_terms',
				'key_type'    => 'capabilities',
				'value'       => '',
				'label'       => __( 'Assign Terms', 'admin-optimizer' ),
				'description' => __( 'Default \'edit_posts\'.', 'admin-optimizer' ),
				'required'    => false,
			],
		];
	}

	/**
	 * Render Settings page
	 *
	 * @return void
	 */
	public function render_settings_page() {
		$message         = '';
		$error           = false;
		$action          = isset( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : '';
		$render_settings = true;
		$this->add_setting_fields();
		if ( ! in_array( $action, [ 'add', 'save-edit', 'edit', 'delete', 'bulk-delete' ], true ) ) {
			$action = '';
		}
		switch ( $action ) {
			case 'add':
				check_admin_referer( 'adminoptim-add-taxonomy', 'nonce' );
				$post_data = isset( $_REQUEST['taxonomy'] ) ? wp_unslash( $_REQUEST['taxonomy'] ) : [];  // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				$response  = $this->handle_post_data( $post_data );
				if ( is_wp_error( $response ) ) {
					$error   = true;
					$message = $response->get_error_message();
				} else {
					$message = $response->message;
				}
				break;
			case 'delete':
				if ( ! isset( $_REQUEST['slug'] ) ) {
					break;
				}
				$slug = sanitize_title_with_dashes( wp_unslash( $_REQUEST['slug'] ), '', 'save' );
				check_admin_referer( 'delete-taxonomy_' . $slug, 'nonce' );
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_die(
						'<h1>' . esc_html__( 'You need a higher level of permission.', 'admin-optimizer' ) . '</h1>' .
						'<p>' . esc_html__( 'Sorry, you are not allowed to delete this item.', 'admin-optimizer' ) . '</p>',
						403
					);
				}

				if ( isset( $this->taxonomies[ $slug ] ) ) {
					unset( $this->taxonomies[ $slug ] );
				}
				update_option( Taxonomies::OPTION_NAME, $this->taxonomies, false );
				$message = __( 'Taxonomy deleted successfully.', 'admin-optimizer' );
				break;
			case 'bulk-delete':
				check_admin_referer( 'bulk-custom_taxonomies' );
				$slugs = isset( $_REQUEST['slugs'] ) ? wp_unslash( $_REQUEST['slugs'] ) : []; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				if ( ! empty( $slugs ) ) {
					foreach ( $slugs as $slug ) {
						$slug = sanitize_title_with_dashes( $slug, '', 'save' );
						if ( isset( $this->taxonomies[ $slug ] ) ) {
							unset( $this->taxonomies[ $slug ] );
						} else {
							wp_die( esc_html__( 'You attempted to edit a taxonomy that does not exist. Perhaps it was deleted?', 'admin-optimizer' ) );
						}
					}
					update_option( Taxonomies::OPTION_NAME, $this->taxonomies, false );
					$message = __( 'Taxonomy deleted successfully.', 'admin-optimizer' );
				} else {
					wp_die( esc_html__( 'Missing taxonomy slugs.', 'admin-optimizer' ) );
				}

				break;
			case 'edit':
				if ( ! isset( $_REQUEST['slug'] ) ) {
					break;
				}

				$taxonomy_slug = sanitize_title_with_dashes( wp_unslash( $_REQUEST['slug'] ), '', 'save' );
				check_admin_referer( 'edit-taxonomy_' . $taxonomy_slug, 'nonce' );
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_die(
						'<h1>' . esc_html( 'You need a higher level of permission.' ) . '</h1>' .
						'<p>' . esc_html( 'Sorry, you are not allowed to delete this item.' ) . '</p>',
						403
					);
				}
				$this->render_edit_page( $taxonomy_slug );
				$render_settings = false;
				break;
			case 'save-edit':
				if ( ! isset( $_REQUEST['slug'] ) ) {
					wp_die( esc_html( 'You attempted to save a taxonomy with improper parameter. Please try again!' ) );
				}

				$slug = sanitize_title_with_dashes( wp_unslash( $_REQUEST['slug'] ), '', 'save' );
				check_admin_referer( 'save-edit-taxonomy_' . $slug, 'nonce' );

				$post_data = isset( $_REQUEST['taxonomy'] ) ? wp_unslash( $_REQUEST['taxonomy'] ) : [];  // phpcs:ignore  WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				if ( empty( $this->taxonomies[ $slug ] ) ) {
					wp_die( esc_html( 'You attempted to edit a taxonomy that does not exist. Perhaps it was deleted?' ) );
				}
				$post_data['slug'] = $slug;
				$response = $this->handle_post_data( $post_data, 'save-edit', $slug );
				if ( is_wp_error( $response ) ) {
					$error   = true;
					$message = $response->get_error_message();
				} else {
					$message = $response->message;
				}
				break;
		}

		if ( $render_settings ) {
			$this->render_setting_fields( $message, $error );
		}
	}

	/**
	 * Render Settings fields
	 *
	 * @param string $message  Message.
	 * @param bool   $error Is there any error?.
	 *
	 * @return void
	 */
	public function render_setting_fields( $message = '', $error = false ) {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Admin Optimizer - Custom Taxonomies', 'admin-optimizer' ); ?></h1>
			<?php
			if ( ! empty( $message ) ) {
					$class = $error ? 'error' : 'updated';
					wp_admin_notice(
						$message,
						[
							'id'                 => 'message',
							'additional_classes' => array( $class ),
							'dismissible'        => true,
						]
					);
			}
			?>
			<div id="ajax-response"></div>
			<div id="col-container" class="wp-clearfix">
				<div id="col-left">
					<div class="form-wrap">
						<h2><?php esc_html_e( 'Add New Taxonomy', 'admin-optimizer' ); ?></h2>
						<form id="addtaxonomy" method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=' . Taxonomies::MENU_SLUG ), null, '&' ); ?>">
							<?php wp_nonce_field( 'adminoptim-add-taxonomy', 'nonce' ); ?>
							<input type="hidden" name="action" value="add">
							<?php
							$this->output_setting_field( $this->setting_fields['required'] );
							?>
							<div id="cpt-accordion">
								<h3><?php esc_html_e( 'Additional Labels', 'admin-optimizer' ); ?></h3>
								<div class="inside">
									<?php $this->output_setting_field( $this->setting_fields['labels'] ); ?>
								</div>
								<h3><?php esc_html_e( 'Capabilities', 'admin-optimizer' ); ?></h3>
								<div class="inside">
									<?php $this->output_setting_field( $this->setting_fields['capabilities'] ); ?>
								</div>
								<h3><?php esc_html_e( 'More Options', 'admin-optimizer' ); ?></h3>
								<div class="inside">
									<?php $this->output_setting_field( $this->setting_fields['options'] ); ?>
								</div>
							</div>
							<p class="submit">
								<?php
								submit_button(
									__( 'Add New Taxonomy', 'admin-optimizer' ),
									'primary',
									'submit',
									false
								);
								?>
								<span class="spinner"></span>
							</p>
						</form></div>
				</div>
				<div id="col-right">
					<form id="bulk-delete-poststatus" method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=' . Taxonomies::MENU_SLUG ), null, '&' ); ?>">
					<?php
					$taxonomies_list_table = new Taxonomies_List_Table();
					$taxonomies_list_table->prepare_items();
					$taxonomies_list_table->display();
					?>
					</form>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Output Settings fields
	 *
	 * @param array  $setting_fields List of Settings fields to output.
	 * @param string $action Type of action to handle the data.
	 * @param array  $taxonomy List of Taxonomies.
	 *
	 * @return void
	 */
	private function output_setting_field( array $setting_fields, $action = 'add', $taxonomy = [] ) {
		if ( 'edit' === $action && empty( $taxonomy ) ) {
			$action = 'add';
		}
		if ( ! empty( $setting_fields ) ) {
			foreach ( $setting_fields as $field ) {
				$required_class = $field['required'] ? ' form-required' : '';
				switch ( $field['type'] ) {
					case 'text':
						$placeholder = $field['placeholder'] ?? '';
						$disabled    = '';
						if ( 'edit' === $action && ! empty( $field['key'] ) ) {
							if ( 'label' === $field['key_type'] ) {
								$field['value'] = $taxonomy['labels'][ $field['key'] ] ?? '';
							} elseif ( 'rewrite' === $field['key_type'] ) {
								$field['value'] = $taxonomy['rewrite'][ $field['key'] ] ?? '';
							} elseif ( 'capabilities' === $field['key_type'] ) {
								$field['value'] = $taxonomy['capabilities'][ $field['key'] ] ?? '';
							} else {
								$field['value'] = $taxonomy[ $field['key'] ] ?? '';
							}
							if ( 'slug' === $field['key'] ) {
								$disabled = ' disabled';
							}
						}
						?>
						<div class="form-field<?php echo esc_attr( $required_class ); ?>">
							<label for="<?php echo esc_attr( $field['id'] ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
							<input name="<?php echo esc_attr( $field['name'] ); ?>" id="<?php echo esc_attr( $field['id'] ); ?>" type="text" value="<?php echo esc_attr( $field['value'] ); ?>" size="40" aria-required="<?php echo ( $field['required'] ? 'true' : 'false' ); ?>" aria-describedby="<?php echo esc_attr( $field['id'] ); ?>-description" placeholder="<?php echo esc_attr( $placeholder ); ?>" <?php echo esc_attr( $disabled ); ?>/>
							<p id="<?php echo esc_attr( $field['id'] ); ?>-description"><?php echo esc_html( $field['description'] ); ?></p>
						</div>
						<?php
						break;
					case 'number':
						if ( 'edit' === $action && ! empty( $field['key'] ) ) {
							$field['value'] = $taxonomy[ $field['key'] ] ?? '';
						}
						?>
						<div class="form-field<?php echo esc_attr( $required_class ); ?>">
							<label for="<?php echo esc_attr( $field['id'] ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
							<input name="<?php echo esc_attr( $field['name'] ); ?>" id="<?php echo esc_attr( $field['id'] ); ?>" type="number" min="<?php echo intval( $field['min'] ); ?>" max="<?php echo intval( $field['max'] ); ?>" step="1" value="<?php echo esc_attr( $field['value'] ); ?>" size="3" aria-required="<?php echo ( $field['required'] ? 'true' : 'false' ); ?>" aria-describedby="<?php echo esc_attr( $field['id'] ); ?>-description"/>
							<p id="<?php echo esc_attr( $field['id'] ); ?>-description"><?php echo esc_html( $field['description'] ); ?></p>
						</div>
						<?php
						break;
					case 'textarea':
						if ( 'edit' === $action && ! empty( $field['key'] ) ) {
							$field['value'] = $taxonomy[ $field['key'] ] ?? '';
						}
						?>
						<div class="form-field<?php echo esc_attr( $required_class ); ?>">
							<label for="<?php echo esc_attr( $field['id'] ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
							<textarea name="<?php echo esc_attr( $field['name'] ); ?>" id="<?php echo esc_attr( $field['id'] ); ?>" rows="5" cols="40" aria-describedby="<?php echo esc_attr( $field['id'] ); ?>-description"><?php echo esc_html( $field['value'] ); ?></textarea>
							<p id="<?php echo esc_attr( $field['id'] ); ?>-description"><?php echo esc_html( $field['description'] ); ?></p>
						</div>
						<?php
						break;
					case 'select':
						if ( 'edit' === $action && ! empty( $field['key'] ) ) {
							if ( 'rewrite' === $field['key_type'] ) {
								if ( isset( $taxonomy['rewrite'] ) && is_array( $taxonomy['rewrite'] ) && array_key_exists( $field['key'], $taxonomy['rewrite'] ) ) {
									$field['value'] = $taxonomy['rewrite'][ $field['key'] ] ? '1' : '0';
								} else {
									$field['value'] = '-1';
								}
							} elseif ( 'rewrite' === $field['key'] ) {
								$field['value'] = array_key_exists( 'use_rewrite', $taxonomy ) ? strval( intval( $taxonomy['use_rewrite'] ) ) : '1';
							} else {
								$field['value'] = $taxonomy[ $field['key'] ] ?? '';
							}
						}
						?>
						<div class="form-field<?php echo esc_attr( $required_class ); ?>">
							<label for="<?php echo esc_attr( $field['id'] ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
							<select name="<?php echo esc_attr( $field['name'] ); ?>" id="<?php echo esc_attr( $field['id'] ); ?>" class="postform" aria-describedby="<?php echo esc_attr( $field['id'] ); ?>-description">
								<?php foreach ( $field['choices'] as $value => $label ) : ?>
									<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, $field['value'] ); ?>><?php echo esc_html( $label ); ?></option>
								<?php endforeach; ?>
							</select>
							<p id="<?php echo esc_attr( $field['id'] ); ?>-description"><?php echo esc_html( $field['description'] ); ?></p>
						</div>
						<?php
						break;
					case 'checkboxes':
						if ( 'edit' === $action && ! empty( $field['key'] ) ) {
							if ( 'posttypes' === $field['key_type'] ) {
								$field['checked'] = $taxonomy['posttypes'] ?? '';
							}
						}
						?>
						<div class="form-field">
							<table class="form-table" role="presentation">
								<tbody>
								<tr>
									<th scope="row"><?php echo esc_html( $field['label'] ); ?></th>
									<td>
										<?php
										foreach ( $field['choices'] as $name => $label ) :
											$checked = '';
											if ( is_array( $field['checked'] ) && in_array( $name, $field['checked'], true ) ) {
												$checked = 'checked';
											}
											?>
											<label for="<?php echo esc_attr( $field['id'] ) . '-' . esc_attr( $name ); ?>">
												<input id="<?php echo esc_attr( $field['id'] ) . '-' . esc_attr( $name ); ?>" name="<?php echo esc_attr( $field['name'] ); ?>" type="checkbox" value="<?php echo esc_attr( $name ); ?>" <?php echo esc_attr( $checked ); ?>><?php echo esc_html( $label ); ?>
											</label><br/>
										<?php endforeach; ?>
									</td>
								</tr>
								</tbody>
							</table>
						</div>
						<?php
						break;
				}
			}
		}
	}

	/**
	 * Handle post data
	 *
	 * @param array  $post_data  Post data.
	 * @param string $action Type of action to handle the data.
	 * @param string $slug  Slug of the taxonomy.
	 *
	 * @return object|\WP_Error
	 */
	private function handle_post_data( $post_data, $action = '', $slug = '' ) {

		if ( empty( $post_data['slug'] ) && empty( $slug ) ) {
			return new \WP_Error( '400', __( 'The taxonomy slug cannot be empty.', 'admin-optimizer' ) );

		} elseif ( empty( $post_data['labels']['name'] ) ) {
			return new \WP_Error( '400', __( 'The taxonomy name cannot be empty.', 'admin-optimizer' ) );

		} elseif ( empty( $post_data['labels']['singular_name'] ) ) {
			return new \WP_Error( '400', __( 'The taxonomy singular name cannot be empty.', 'admin-optimizer' ) );

		} else {
			$taxonomy            = [];
			$existing_taxonomies = get_taxonomies();
			// check if taxonomy already exists?
			if ( isset( $post_data['slug'] ) ) {
				$post_data['slug'] = sanitize_title_with_dashes( $post_data['slug'], '', 'save' );
			}

			if ( 'save-edit' === $action ) {
				$post_data['slug'] = $slug;
				if ( empty( $this->taxonomies[ $post_data['slug'] ] ) ) {
					return new \WP_Error( '400', __( 'You attempted to save a taxonomy that does not exist. Perhaps it was deleted?', 'admin-optimizer' ) );
				}
			} elseif ( in_array( $post_data['slug'], $existing_taxonomies, true ) ) {
					return new \WP_Error( '400', __( 'The taxonomy already exist.', 'admin-optimizer' ) );
			}

			$taxonomy['slug'] = $post_data['slug'];
			unset( $post_data['slug'] );
			if ( ! empty( $post_data['description'] ) ) {
				$taxonomy['description'] = sanitize_textarea_field( $post_data['description'] );
				unset( $post_data['description'] );
			}
			foreach ( $post_data['labels'] as $label_key => $value ) {
				if ( empty( $value ) ) {
					unset( $post_data['labels'][ $label_key ] );
				}
			}
			$taxonomy['labels'] = array_map( 'sanitize_text_field', $post_data['labels'] );
			unset( $post_data['labels'] );
			array_walk_recursive( $post_data, 'sanitize_text_field' );
			foreach ( $post_data as $key => $value ) {
				if ( '' === $value ) {
					continue;
				}
				if ( '0' === $value || '-1' === $value ) {
					$taxonomy[ $key ] = (bool) $value;
					continue;
				}
				if ( is_array( $value ) ) {
					$temp_arr = [];
					foreach ( $value as $sub_key => $sub_value ) {
						if ( empty( $sub_value ) || '-1' === $sub_value ) {
							continue;
						}
						$temp_arr[ $sub_key ] = $sub_value;
					}
					if ( empty( $temp_arr ) ) {
						continue;
					}
				}
				$taxonomy[ $key ] = $value;
			}
			if ( isset( $taxonomy['use_rewrite'] ) && '0' === $taxonomy['use_rewrite'] ) {
				$taxonomy['rewrite'] = false;
			}
			$this->taxonomies[ $taxonomy['slug'] ] = $taxonomy;
			update_option( Taxonomies::OPTION_NAME, $this->taxonomies, false );
			if ( 'save-edit' === $action ) {
				$message = __( 'Taxonomy edited successfully.', 'admin-optimizer' );
			} else {
				$message = __( 'Taxonomy added successfully.', 'admin-optimizer' );
			}

			return (object) [
				'status'  => 'success',
				'message' => $message,
			];
		}
	}

	/**
	 * Render Edit page
	 *
	 * @param string $slug  Slug of taxonomy.
	 * @param string $message Message.
	 * @param bool   $error Error?.
	 *
	 * @return void
	 */
	public function render_edit_page( $slug, $message = '', $error = false ) {
		?>
		<div class="wrap">
			<?php
			if ( ! isset( $this->taxonomies[ $slug ] ) ) {
				wp_die( esc_html( 'You attempted to edit a taxonomy that does not exist. Perhaps it was deleted?' ) );
			}
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die(
					'<h1>' . esc_html( 'You need a higher level of permission.' ) . '</h1>' .
					'<p>' . esc_html( 'Sorry, you are not allowed to edit this item.' ) . '</p>',
					403
				);
			}
			?>
			<h1><?php esc_html_e( 'Admin Optimizer - Edit Taxonomy', 'admin-optimizer' ); ?></h1>
			<?php
			if ( ! empty( $message ) ) {
				$class = $error ? 'error' : 'updated';
				wp_admin_notice(
					$message,
					[
						'id'                 => 'message',
						'additional_classes' => array( $class ),
						'dismissible'        => true,
					]
				);
			}
			?>
			<div id="ajax-response"></div>
			<div class="form-wrap">
				<form id="edittag" method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=' . Taxonomies::MENU_SLUG ), null, '&' ); ?>">
					<input type="hidden" name="action" value="save-edit" >
					<input type="hidden" name="slug" value="<?php echo esc_attr( $slug ); ?>">
					<?php wp_nonce_field( 'save-edit-taxonomy_' . $slug, 'nonce' ); ?>
					<?php
					$this->output_setting_field( $this->setting_fields['required'], 'edit', $this->taxonomies[ $slug ] );
					?>
					<div id="cpt-accordion">
						<h3><?php esc_html_e( 'Additional Labels', 'admin-optimizer' ); ?></h3>
						<div class="inside">
							<?php
							$this->output_setting_field(
								$this->setting_fields['labels'],
								'edit',
								$this->taxonomies[ $slug ]
							);
							?>
						</div>
						<h3><?php esc_html_e( 'Capabilities', 'admin-optimizer' ); ?></h3>
						<div class="inside">
							<?php $this->output_setting_field( $this->setting_fields['capabilities'], 'edit', $this->taxonomies[ $slug ] ); ?>
						</div>
						<h3><?php esc_html_e( 'More Options', 'admin-optimizer' ); ?></h3>
						<div class="inside">
							<?php
							$this->output_setting_field(
								$this->setting_fields['options'],
								'edit',
								$this->taxonomies[ $slug ]
							);
							?>
						</div>
					</div>
					<p><?php submit_button( 'Save Taxonomy', 'primary', 'submit', false ); ?> <a class="cancel-settings-link" href="<?php echo esc_url( admin_url( 'admin.php?page=' . Taxonomies::MENU_SLUG ) ); ?>"><?php esc_html_e( 'Cancel', 'admin-optimizer' ); ?></a></p>
				</form>
			</div>
		</div>
		<?php
	}

	/**
	 * Enqueue scripts and style
	 *
	 * @param string $hook_suffix Check if we are on the right page to enqueue the script.
	 *
	 * @return void
	 */
	public function enqueue_scripts( $hook_suffix ) {
		if ( str_contains( $hook_suffix, Taxonomies::MENU_SLUG ) ) {
			wp_enqueue_script( 'adminoptim-taxonomies-settings', Taxonomies::MODULE_URL . 'assets/js/taxonomies-settings.min.js', [ 'jquery-ui-accordion' ], filemtime( Taxonomies::MODULE_PATH . 'assets/js/taxonomies-settings.min.js' ), true );
			wp_enqueue_style( 'adminoptim-taxonomies-settings', Taxonomies::MODULE_URL . 'assets/css/taxonomies-settings.min.css', [], filemtime( Taxonomies::MODULE_PATH . 'assets/css/taxonomies-settings.min.css' ) );
		}
	}
}