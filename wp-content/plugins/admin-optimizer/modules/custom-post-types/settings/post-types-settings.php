<?php
namespace Yipresser\AdminOptimizer\Modules;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post_Types_Settings class
 */
class Post_Types_Settings {

	/**
	 * List of Settings fields
	 *
	 * @var array
	 */
	protected $setting_fields;

	/**
	 * User defined Post Types
	 *
	 * @var array
	 */
	protected $post_types;

	/**
	 * Constructor
	 *
	 * @param array $post_types User defined post types.
	 */
	public function __construct( $post_types ) {
		$this->post_types = $post_types;
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	/**
	 * Set up Settings fields
	 *
	 * @return void
	 */
	private function add_setting_fields() {
		$this->setting_fields = [
			'required' => [
				[
					'id'          => 'post-type-slug',
					'type'        => 'text',
					'name'        => 'posttype[slug]',
					'key'         => 'slug',
					'key_type'    => 'option',
					'value'       => '',
					'label'       => __( 'Slug', 'admin-optimizer' ),
					'description' => __( 'The "slug" is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens.', 'admin-optimizer' ),
					'required'    => true,
				],
				[
					'id'          => 'post-type-name',
					'type'        => 'text',
					'name'        => 'posttype[labels][name]',
					'key'         => 'name',
					'key_type'    => 'label',
					'value'       => '',
					'label'       => __( 'Post Type Name', 'admin-optimizer' ),
					'description' => __( 'The name of the custom post type. Usually plural.', 'admin-optimizer' ),
					'required'    => true,
				],
				[
					'id'          => 'post-type-singular-label',
					'type'        => 'text',
					'name'        => 'posttype[labels][singular_name]',
					'key'         => 'singular_name',
					'key_type'    => 'label',
					'value'       => '',
					'label'       => __( 'Singular Label', 'admin-optimizer' ),
					'description' => __( 'The singular label of the custom post type.', 'admin-optimizer' ),
					'required'    => true,
				],
				[
					'id'          => 'post-type-description',
					'type'        => 'textarea',
					'name'        => 'posttype[description]',
					'key'         => 'description',
					'key_type'    => 'option',
					'value'       => '',
					'label'       => __( 'Description', 'admin-optimizer' ),
					'description' => __( 'Describe what this custom post type is used for.', 'admin-optimizer' ),
					'required'    => false,
				],
			],
			'labels'   => [
				[
					'id'          => 'post-type-add-new-label',
					'type'        => 'text',
					'name'        => 'posttype[labels][add_new]',
					'key'         => 'add_new',
					'key_type'    => 'label',
					'value'       => '',
					'label'       => __( 'Add New', 'admin-optimizer' ),
					'description' => __( 'Label for adding a new item. Default is \'Add Post\' / \'Add Page\'.', 'admin-optimizer' ),
					'required'    => false,
					'placeholder' => __( '(e.g. Add New)', 'admin-optimizer' ),
				],
				[
					'id'          => 'post-type-add-new-item-label',
					'type'        => 'text',
					'name'        => 'posttype[labels][add_new_item]',
					'key'         => 'add_new_item',
					'key_type'    => 'label',
					'value'       => '',
					'label'       => __( 'Add New Item', 'admin-optimizer' ),
					'description' => __( 'Label for adding a new singular item. Default is \'Add Post\' / \'Add Page\'.', 'admin-optimizer' ),
					'required'    => false,
					'placeholder' => __( '(e.g. Add New Book)', 'admin-optimizer' ),
				],
				[
					'id'          => 'post-type-edit-item-label',
					'type'        => 'text',
					'name'        => 'posttype[labels][edit_item]',
					'key'         => 'edit_item',
					'key_type'    => 'label',
					'value'       => '',
					'label'       => __( 'Edit Item', 'admin-optimizer' ),
					'description' => __( 'Label for editing a new singular item. Default is \'Edit Post\' / \'Edit Page\'.', 'admin-optimizer' ),
					'required'    => false,
					'placeholder' => __( '(e.g. Edit Book)', 'admin-optimizer' ),
				],
				[
					'id'          => 'post-type-view-item-label',
					'type'        => 'text',
					'name'        => 'posttype[labels][view_item]',
					'key'         => 'view_item',
					'key_type'    => 'label',
					'value'       => '',
					'label'       => __( 'View Item', 'admin-optimizer' ),
					'description' => __( 'Label for viewing a singular item. Default is \'View Post\' / \'View Page\'.', 'admin-optimizer' ),
					'required'    => false,
					'placeholder' => __( '(e.g. View Book)', 'admin-optimizer' ),
				],
				[
					'id'          => 'post-type-view-items-label',
					'type'        => 'text',
					'name'        => 'posttype[labels][view_items]',
					'key'         => 'view_items',
					'key_type'    => 'label',
					'value'       => '',
					'label'       => __( 'View Items', 'admin-optimizer' ),
					'description' => __( 'Label for viewing post type archives. Default is \'View Posts\' / \'View Pages\'.', 'admin-optimizer' ),
					'required'    => false,
					'placeholder' => __( '(e.g. View Books)', 'admin-optimizer' ),
				],
				[
					'id'          => 'post-type-search-items-label',
					'type'        => 'text',
					'name'        => 'posttype[labels][search_items]',
					'key'         => 'search_items',
					'key_type'    => 'label',
					'value'       => '',
					'label'       => __( 'Search Items', 'admin-optimizer' ),
					'description' => __( 'Label for searching plural items. Default is \'Search Post\' / \'Search Pages\'.', 'admin-optimizer' ),
					'required'    => false,
					'placeholder' => __( '(e.g. Search Books)', 'admin-optimizer' ),
				],
				[
					'id'          => 'post-type-not-found-label',
					'type'        => 'text',
					'name'        => 'posttype[labels][not_found]',
					'key'         => 'not_found',
					'key_type'    => 'label',
					'value'       => '',
					'label'       => __( 'Not Found', 'admin-optimizer' ),
					'description' => __( 'Label used when no items are found. Default is \'No posts found\' / \'No pages found\'.', 'admin-optimizer' ),
					'required'    => false,
					'placeholder' => __( '(e.g. No books found)', 'admin-optimizer' ),
				],
				[
					'id'          => 'post-type-not-found-trash-label',
					'type'        => 'text',
					'name'        => 'posttype[labels][not_found_in_trash]',
					'key'         => 'not_found_in_trash',
					'key_type'    => 'label',
					'value'       => '',
					'label'       => __( 'Not Found  in Trash', 'admin-optimizer' ),
					'description' => __( 'Label used when no items are in the Trash. Default is \'No posts found in Trash\' / \'No pages found in Trash\'.', 'admin-optimizer' ),
					'required'    => false,
					'placeholder' => __( '(e.g. No books found in Trash)', 'admin-optimizer' ),
				],
				[
					'id'          => 'post-type-parent-item-colon-label',
					'type'        => 'text',
					'name'        => 'posttype[labels][parent_item_colon]',
					'key'         => 'parent_item_colon',
					'key_type'    => 'label',
					'value'       => '',
					'label'       => __( 'Parent Item', 'admin-optimizer' ),
					'description' => __( 'Label used to prefix parents of hierarchical items. Not used on non-hierarchical post types. Default is \'Parent Page:\'.', 'admin-optimizer' ),
					'required'    => false,
					'placeholder' => __( '(e.g. Parent Book)', 'admin-optimizer' ),
				],
				[
					'id'          => 'post-type-all-items-label',
					'type'        => 'text',
					'name'        => 'posttype[labels][all_items]',
					'key'         => 'all_items',
					'key_type'    => 'label',
					'value'       => '',
					'label'       => __( 'All Items', 'admin-optimizer' ),
					'description' => __( 'Label to signify all items in a submenu link. Default is \'All Posts\' / \'All Pages\'.', 'admin-optimizer' ),
					'required'    => false,
					'placeholder' => __( '(e.g. All Books)', 'admin-optimizer' ),
				],
				[
					'id'          => 'post-type-archives-label',
					'type'        => 'text',
					'name'        => 'posttype[labels][archives]',
					'key'         => 'archives',
					'key_type'    => 'label',
					'value'       => '',
					'label'       => __( 'Archives', 'admin-optimizer' ),
					'description' => __( 'Label for archives in nav menus. Default is \'Post Archives\' / \'Page Archives\'.', 'admin-optimizer' ),
					'required'    => false,
					'placeholder' => __( '(e.g. Book Archives)', 'admin-optimizer' ),
				],
				[
					'id'          => 'post-type-attributes-label',
					'type'        => 'text',
					'name'        => 'posttype[labels][attributes]',
					'key'         => 'attributes',
					'key_type'    => 'label',
					'value'       => '',
					'label'       => __( 'Archives', 'admin-optimizer' ),
					'description' => __( 'Label for the attributes meta box. Default is \'Post Attributes\' / \'Page Attributes\'.', 'admin-optimizer' ),
					'required'    => false,
					'placeholder' => __( '(e.g. Book Attributes)', 'admin-optimizer' ),
				],
				[
					'id'          => 'post-type-insert-into-item-label',
					'type'        => 'text',
					'name'        => 'posttype[labels][insert_into_item]',
					'key'         => 'insert_into_item',
					'key_type'    => 'label',
					'value'       => '',
					'label'       => __( 'Insert Into Item', 'admin-optimizer' ),
					'description' => __( 'Label for the media frame button. Default is \'Insert into post\' / \'Insert into page\'.', 'admin-optimizer' ),
					'required'    => false,
					'placeholder' => __( '(e.g. Insert into book)', 'admin-optimizer' ),
				],
				[
					'id'          => 'post-type-uploaded-to-this-item-label',
					'type'        => 'text',
					'name'        => 'posttype[labels][uploaded_to_this_item]',
					'key'         => 'uploaded_to_this_item',
					'key_type'    => 'label',
					'value'       => '',
					'label'       => __( 'Uploaded to this Item', 'admin-optimizer' ),
					'description' => __( 'Label for the media frame filter. Default is \'Uploaded to this post\' / \'Uploaded to this page\'.', 'admin-optimizer' ),
					'required'    => false,
					'placeholder' => __( '(e.g. Uploaded to this book)', 'admin-optimizer' ),
				],
				[
					'id'          => 'post-type-featured-image-label',
					'type'        => 'text',
					'name'        => 'posttype[labels][featured_image]',
					'key'         => 'featured_image',
					'key_type'    => 'label',
					'value'       => '',
					'label'       => __( 'Featured Image', 'admin-optimizer' ),
					'description' => __( 'Label for the featured image meta box title. Default is \'Featured image\'.', 'admin-optimizer' ),
					'required'    => false,
					'placeholder' => __( '(e.g. Featured image for book)', 'admin-optimizer' ),
				],
				[
					'id'          => 'post-type-set-featured-image-label',
					'type'        => 'text',
					'name'        => 'posttype[labels][set_featured_image]',
					'key'         => 'set_featured_image',
					'key_type'    => 'label',
					'value'       => '',
					'label'       => __( 'Set Featured Image', 'admin-optimizer' ),
					'description' => __( 'Label for setting the featured image. Default is \'Set Featured image\'.', 'admin-optimizer' ),
					'required'    => false,
					'placeholder' => __( '(e.g. Set featured image for book)', 'admin-optimizer' ),
				],
				[
					'id'          => 'post-type-remove-featured-image-label',
					'type'        => 'text',
					'name'        => 'posttype[labels][remove_featured_image]',
					'key'         => 'remove_featured_image',
					'key_type'    => 'label',
					'value'       => '',
					'label'       => __( 'Remove Featured Image', 'admin-optimizer' ),
					'description' => __( 'Label for setting the featured image. Default is \'Remove featured image\'.', 'admin-optimizer' ),
					'required'    => false,
					'placeholder' => __( '(e.g. Remove featured image for book)', 'admin-optimizer' ),
				],
				[
					'id'          => 'post-type-use-featured-image-label',
					'type'        => 'text',
					'name'        => 'posttype[labels][use_featured_image]',
					'key'         => 'use_featured_image',
					'key_type'    => 'label',
					'value'       => '',
					'label'       => __( 'Use Featured Image', 'admin-optimizer' ),
					'description' => __( 'Label in the media frame for using a featured image. Default is \'Use as featured image\'.', 'admin-optimizer' ),
					'required'    => false,
					'placeholder' => __( '(e.g. Use as featured image this book)', 'admin-optimizer' ),
				],
				[
					'id'          => 'post-type-menu-name-label',
					'type'        => 'text',
					'name'        => 'posttype[labels][menu_name]',
					'key'         => 'menu_name',
					'key_type'    => 'label',
					'value'       => '',
					'label'       => __( 'Menu Name', 'admin-optimizer' ),
					'description' => __( 'Label for the menu name. Default is the same as post type name.', 'admin-optimizer' ),
					'required'    => false,
					'placeholder' => __( '(e.g. Books)', 'admin-optimizer' ),
				],
				[
					'id'          => 'post-type-filter-items-list-label',
					'type'        => 'text',
					'name'        => 'posttype[labels][filter_items_list]',
					'key'         => 'filter_items_list',
					'key_type'    => 'label',
					'value'       => '',
					'label'       => __( 'Filter Items List', 'admin-optimizer' ),
					'description' => __( 'Label for the table views hidden heading. Default is \'Filter posts list\' / \'Filter pages list\'.', 'admin-optimizer' ),
					'required'    => false,
					'placeholder' => __( '(e.g. Filter books list)', 'admin-optimizer' ),
				],
				[
					'id'          => 'post-type-filter-by-date-label',
					'type'        => 'text',
					'name'        => 'posttype[labels][filter_by_date]',
					'key'         => 'filter_by_date',
					'key_type'    => 'label',
					'value'       => '',
					'label'       => __( 'Filter by Date', 'admin-optimizer' ),
					'description' => __( 'Label for the date filter in list tables. Default is \'Filter by date\'.', 'admin-optimizer' ),
					'required'    => false,
					'placeholder' => __( '(e.g. Filter books by date)', 'admin-optimizer' ),
				],
				[
					'id'          => 'post-type-items-list-navigation-label',
					'type'        => 'text',
					'name'        => 'posttype[labels][items_list_navigation]',
					'key'         => 'items_list_navigation',
					'key_type'    => 'label',
					'value'       => '',
					'label'       => __( 'Items List Navigation', 'admin-optimizer' ),
					'description' => __( 'Label for the table pagination hidden heading. Default is \'Posts list navigation\' / \'Pages list navigation\'.', 'admin-optimizer' ),
					'required'    => false,
					'placeholder' => __( '(e.g. Books list navigation)', 'admin-optimizer' ),
				],
				[
					'id'          => 'post-type-items-list-label',
					'type'        => 'text',
					'name'        => 'posttype[labels][items_list]',
					'key'         => 'items_list',
					'key_type'    => 'label',
					'value'       => '',
					'label'       => __( 'Items List', 'admin-optimizer' ),
					'description' => __( 'Label for the table hidden heading. Default is \'Posts list\' / \'Pages list\'.', 'admin-optimizer' ),
					'required'    => false,
					'placeholder' => __( '(e.g. Books list)', 'admin-optimizer' ),
				],
				[
					'id'          => 'post-type-items-published-label',
					'type'        => 'text',
					'name'        => 'posttype[labels][item_published]',
					'key'         => 'item_published',
					'key_type'    => 'label',
					'value'       => '',
					'label'       => __( 'Items Published', 'admin-optimizer' ),
					'description' => __( 'Label used when an item is published. Default is \'Post published.\' / \'Page published.\'.', 'admin-optimizer' ),
					'required'    => false,
					'placeholder' => __( '(e.g. Books published)', 'admin-optimizer' ),
				],
				[
					'id'          => 'post-type-items-published-privately-label',
					'type'        => 'text',
					'name'        => 'posttype[labels][item_published_privately]',
					'key'         => 'item_published_privately',
					'key_type'    => 'label',
					'value'       => '',
					'label'       => __( 'Items Published Privately', 'admin-optimizer' ),
					'description' => __( 'Label used when an item is published privately. Default is \'Post published privately.\' / \'Page published privately.\'.', 'admin-optimizer' ),
					'required'    => false,
					'placeholder' => __( '(e.g. Books published privately)', 'admin-optimizer' ),
				],
				[
					'id'          => 'post-type-items-reverted-to-draft-label',
					'type'        => 'text',
					'name'        => 'posttype[labels][item_reverted_to_draft]',
					'key'         => 'item_reverted_to_draft',
					'key_type'    => 'label',
					'value'       => '',
					'label'       => __( 'Items Reverted to Draft', 'admin-optimizer' ),
					'description' => __( 'Label used when an item is switched to a draft. Default is \'Post reverted to draft.\' / \'Page reverted to draft.\'', 'admin-optimizer' ),
					'required'    => false,
					'placeholder' => __( '(e.g. Books reverted to draft)', 'admin-optimizer' ),
				],
				[
					'id'          => 'post-type-items-trashed-label',
					'type'        => 'text',
					'name'        => 'posttype[labels][item_trashed]',
					'key'         => 'item_trashed',
					'key_type'    => 'label',
					'value'       => '',
					'label'       => __( 'Items Trashed', 'admin-optimizer' ),
					'description' => __( 'Label used when an item is moved to Trash. Default is \'Post trashed.\' / \'Page trashed.\'', 'admin-optimizer' ),
					'required'    => false,
					'placeholder' => __( '(e.g. Books trashed)', 'admin-optimizer' ),
				],
				[
					'id'          => 'post-type-items-trashed-label',
					'type'        => 'text',
					'name'        => 'posttype[labels][item_trashed]',
					'key'         => 'item_trashed',
					'key_type'    => 'label',
					'value'       => '',
					'label'       => __( 'Items Trashed', 'admin-optimizer' ),
					'description' => __( 'Label used when an item is moved to Trash. Default is \'Post trashed.\' / \'Page trashed.\'', 'admin-optimizer' ),
					'required'    => false,
					'placeholder' => __( '(e.g. Books trashed)', 'admin-optimizer' ),
				],
				[
					'id'          => 'post-type-items-scheduled-label',
					'type'        => 'text',
					'name'        => 'posttype[labels][item_scheduled]',
					'key'         => 'item_scheduled',
					'key_type'    => 'label',
					'value'       => '',
					'label'       => __( 'Items Scheduled', 'admin-optimizer' ),
					'description' => __( 'Label used when an item is scheduled. Default is \'Post scheduled.\' / \'Page scheduled.\'', 'admin-optimizer' ),
					'required'    => false,
					'placeholder' => __( '(e.g. Book scheduled)', 'admin-optimizer' ),
				],
				[
					'id'          => 'post-type-items-updated-label',
					'type'        => 'text',
					'name'        => 'posttype[labels][item_updated]',
					'key'         => 'item_updated',
					'key_type'    => 'label',
					'value'       => '',
					'label'       => __( 'Items Updated', 'admin-optimizer' ),
					'description' => __( 'Label used when an item is updated. Default is \'Post updated.\' / \'Page updated.\'', 'admin-optimizer' ),
					'required'    => false,
					'placeholder' => __( '(e.g. Book updated)', 'admin-optimizer' ),
				],
				[
					'id'          => 'post-type-items-link-label',
					'type'        => 'text',
					'name'        => 'posttype[labels][item_link]',
					'key'         => 'item_link',
					'key_type'    => 'label',
					'value'       => '',
					'label'       => __( 'Items Link', 'admin-optimizer' ),
					'description' => __( 'Title for a navigation link block variation. Default is \'Post Link\' / \'Page Link\'', 'admin-optimizer' ),
					'required'    => false,
					'placeholder' => __( '(e.g. Book Link)', 'admin-optimizer' ),
				],
				[
					'id'          => 'post-type-items-link-description-label',
					'type'        => 'text',
					'name'        => 'posttype[labels][item_link_description]',
					'key'         => 'item_link_description',
					'key_type'    => 'label',
					'value'       => '',
					'label'       => __( 'Items Link Description', 'admin-optimizer' ),
					'description' => __( 'Description for a navigation link block variation. Default is \'A link to a post.\' / \'A link to a page.\'', 'admin-optimizer' ),
					'required'    => false,
					'placeholder' => __( '(e.g. A link to a book.)', 'admin-optimizer' ),
				],
				[
					'id'          => 'post-type-items-link-description-label',
					'type'        => 'text',
					'name'        => 'posttype[labels][item_link_description]',
					'key'         => 'item_link_description',
					'key_type'    => 'label',
					'value'       => '',
					'label'       => __( 'Items Link Description', 'admin-optimizer' ),
					'description' => __( 'Description for a navigation link block variation. Default is \'A link to a post.\' / \'A link to a page.\'', 'admin-optimizer' ),
					'required'    => false,
					'placeholder' => __( '(e.g. A link to a book.)', 'admin-optimizer' ),
				],
			],
		];
		$taxonomies           = get_taxonomies( [ 'public' => true ], 'objects' );
		$setting_tax_field    = [];
		foreach ( $taxonomies as $taxonomy ) {
			if ( 'post_format' === $taxonomy->name || empty( $taxonomy->label ) ) {
				continue;}
			$setting_tax_field[ $taxonomy->name ] = $taxonomy->label;
		}
		$taxonomies_field                = [
			'id'       => 'posttype-taxonomy',
			'type'     => 'checkboxes',
			'choices'  => $setting_tax_field,
			'checked'  => [],
			'name'     => 'posttype[taxonomies][]',
			'key'      => 'taxonomies',
			'key_type' => 'taxonomies',
			'label'    => __( 'Taxonomies', 'admin-optimizer' ),
			'required' => false,
		];
		$this->setting_fields['options'] = [
			[
				'id'          => 'posttype-public',
				'type'        => 'select',
				'choices'     => [
					'0' => __( 'No', 'admin-optimizer' ),
					'1' => __( 'Yes', 'admin-optimizer' ),
				],
				'name'        => 'posttype[public]',
				'key'         => 'public',
				'key_type'    => 'option',
				'value'       => '',
				'label'       => __( 'Public', 'admin-optimizer' ),
				'description' => __( 'Is this post type intended for use publicly either via the admin interface or by front-end users?', 'admin-optimizer' ),
				'required'    => false,
			],
			[
				'id'          => 'posttype-hierarchical',
				'type'        => 'select',
				'choices'     => [
					'0' => __( 'No', 'admin-optimizer' ),
					'1' => __( 'Yes', 'admin-optimizer' ),
				],
				'name'        => 'posttype[hierarchical]',
				'key'         => 'hierarchical',
				'key_type'    => 'option',
				'value'       => '',
				'label'       => __( 'Hierarchical', 'admin-optimizer' ),
				'description' => __( 'Is this post type hierarchical (e.g. page)?', 'admin-optimizer' ),
				'required'    => false,
			],
			[
				'id'          => 'posttype-exclude-from-search',
				'type'        => 'select',
				'choices'     => [
					'-1' => __( 'Leave it as default', 'admin-optimizer' ),
					'1'  => __( 'Yes', 'admin-optimizer' ),
					'0'  => __( 'No', 'admin-optimizer' ),

				],
				'name'        => 'posttype[exclude_from_search]',
				'key'         => 'exclude_from_search',
				'key_type'    => 'option',
				'value'       => '',
				'label'       => __( 'Exclude from search', 'admin-optimizer' ),
				'description' => __( 'Exclude posts with this post type from front end search results? Default is the opposite value of Public', 'admin-optimizer' ),
				'required'    => false,
			],
			[
				'id'          => 'posttype-publicly-queryable',
				'type'        => 'select',
				'choices'     => [
					'-1' => __( 'Leave it as default', 'admin-optimizer' ),
					'1'  => __( 'Yes', 'admin-optimizer' ),
					'0'  => __( 'No', 'admin-optimizer' ),

				],
				'name'        => 'posttype[publicly_queryable]',
				'key'         => 'publicly_queryable',
				'key_type'    => 'option',
				'value'       => '',
				'label'       => __( 'Publicly queryable', 'admin-optimizer' ),
				'description' => __( 'Can queries be performed on the front end for this post type? The default is inherited from Public.', 'admin-optimizer' ),
				'required'    => false,
			],
			[
				'id'          => 'posttype-show-ui',
				'type'        => 'select',
				'choices'     => [
					'-1' => __( 'Leave it as default', 'admin-optimizer' ),
					'1'  => __( 'Yes', 'admin-optimizer' ),
					'0'  => __( 'No', 'admin-optimizer' ),

				],
				'name'        => 'posttype[show_ui]',
				'key'         => 'show_ui',
				'key_type'    => 'option',
				'value'       => '',
				'label'       => __( 'Show UI', 'admin-optimizer' ),
				'description' => __( 'Allow a UI for managing this post type in the admin? The default is inherited from Public.', 'admin-optimizer' ),
				'required'    => false,
			],
			[
				'id'          => 'posttype-show-in-nav-menu',
				'type'        => 'select',
				'choices'     => [
					'-1' => __( 'Leave it as default', 'admin-optimizer' ),
					'1'  => __( 'Yes', 'admin-optimizer' ),
					'0'  => __( 'No', 'admin-optimizer' ),

				],
				'name'        => 'posttype[show_in_nav_menus]',
				'key'         => 'show_in_nav_menus',
				'key_type'    => 'option',
				'value'       => '',
				'label'       => __( 'Show in Nav Menu', 'admin-optimizer' ),
				'description' => __( 'Makes this post type available for selection in navigation menus. The default is the same as Public.', 'admin-optimizer' ),
				'required'    => false,
			],
			[
				'id'          => 'posttype-show-in-admin-bar',
				'type'        => 'select',
				'choices'     => [
					'-1' => __( 'Leave it as default', 'admin-optimizer' ),
					'1'  => __( 'Yes', 'admin-optimizer' ),
					'0'  => __( 'No', 'admin-optimizer' ),

				],
				'name'        => 'posttype[show_in_admin_bar]',
				'key'         => 'show_in_admin_bar',
				'key_type'    => 'option',
				'value'       => '',
				'label'       => __( 'Show in Admin Bar', 'admin-optimizer' ),
				'description' => __( 'Makes this post type available via the admin bar', 'admin-optimizer' ),
				'required'    => false,
			],
			[
				'id'          => 'posttype-show-in-rest',
				'type'        => 'select',
				'choices'     => [
					'1' => __( 'Yes', 'admin-optimizer' ),
					'0' => __( 'No', 'admin-optimizer' ),

				],
				'name'        => 'posttype[show_in_rest]',
				'key'         => 'show_in_rest',
				'key_type'    => 'option',
				'value'       => '',
				'label'       => __( 'Show in REST API', 'admin-optimizer' ),
				'description' => __( 'Include the post type in the REST API? Set this to yes for the post type to be available in the block editor.', 'admin-optimizer' ),
				'required'    => false,
			],
			[
				'id'          => 'posttype-rest-base',
				'type'        => 'text',
				'name'        => 'posttype[rest_base]',
				'key'         => 'rest_base',
				'key_type'    => 'option',
				'value'       => '',
				'label'       => __( 'REST API Base', 'admin-optimizer' ),
				'description' => __( 'Change the base URL of REST API route. Default is this post_type slug.', 'admin-optimizer' ),
				'required'    => false,
				'placeholder' => __( 'Leave blank if you are not sure.', 'admin-optimizer' ),
			],
			[
				'id'          => 'posttype-rest-namespace',
				'type'        => 'text',
				'name'        => 'posttype[rest_namespace]',
				'key'         => 'rest_namespace',
				'key_type'    => 'option',
				'value'       => '',
				'label'       => __( 'REST Namespace', 'admin-optimizer' ),
				'description' => __( 'Change the namespace URL of REST API route. Default is "wp/v2".', 'admin-optimizer' ),
				'required'    => false,
				'placeholder' => __( 'Leave blank if you are not sure.', 'admin-optimizer' ),
			],
			[
				'id'          => 'posttype-rest-controller-class',
				'type'        => 'text',
				'name'        => 'posttype[rest_controller_class]',
				'key'         => 'rest_controller_class',
				'key_type'    => 'option',
				'value'       => '',
				'label'       => __( 'REST Controller Class', 'admin-optimizer' ),
				'description' => __( 'REST API controller class name. Default is \'WP_REST_Posts_Controller\'.', 'admin-optimizer' ),
				'required'    => false,
				'placeholder' => __( 'Leave blank if you are not sure.', 'admin-optimizer' ),
			],
			[
				'id'          => 'posttype-autosave-rest-controller-class',
				'type'        => 'text',
				'name'        => 'posttype[autosave_rest_controller_class]',
				'key'         => 'autosave_rest_controller_class',
				'key_type'    => 'option',
				'value'       => '',
				'label'       => __( 'Autosave REST Controller Class', 'admin-optimizer' ),
				'description' => __( 'REST API controller class name. Default is \'WP_REST_Autosaves_Controller\'.', 'admin-optimizer' ),
				'required'    => false,
				'placeholder' => __( 'Leave blank if you are not sure.', 'admin-optimizer' ),
			],
			[
				'id'          => 'posttype-revisions-rest-controller-class',
				'type'        => 'text',
				'name'        => 'posttype[revisions_rest_controller_class]',
				'key'         => 'revisions_rest_controller_class',
				'key_type'    => 'option',
				'value'       => '',
				'label'       => __( 'Revisions REST Controller Class', 'admin-optimizer' ),
				'description' => __( 'REST API controller class name. Default is \'WP_REST_Revisions_Controller\'.', 'admin-optimizer' ),
				'required'    => false,
				'placeholder' => __( 'Leave blank if you are not sure.', 'admin-optimizer' ),
			],
			[
				'id'          => 'posttype-menu-position',
				'type'        => 'number',
				'min'         => 5,
				'max'         => 100,
				'name'        => 'posttype[menu_position]',
				'key'         => 'menu_position',
				'key_type'    => 'option',
				'value'       => '',
				'label'       => __( 'Menu Position', 'admin-optimizer' ),
				'description' => __( 'The position in the menu order the post type should appear. Range between 5-100', 'admin-optimizer' ),
				'required'    => false,
			],
			[
				'id'          => 'posttype-menu-icon',
				'type'        => 'text',
				'name'        => 'posttype[menu_icon]',
				'key'         => 'menu_icon',
				'key_type'    => 'option',
				'value'       => '',
				'label'       => __( 'Menu Icon', 'admin-optimizer' ),
				'description' => __(
					'The URL to the icon to be used for this menu. Pass a base64-encoded SVG using a data URI, which will be colored to match the color scheme — this should begin with \'data:image/svg+xml;base64\'. Pass the name of a Dashicons helper class to use a font icon, e.g.
                                    \'dashicons-chart-pie\'. Pass \'none\' to leave it empty so an icon 
                                    can beadded via CSS. Defaults to use the posts icon.',
					'admin-optimizer'
				),
				'required'    => false,
				'placeholder' => __( 'Leave blank to use the default.', 'admin-optimizer' ),
			],
			[
				'id'          => 'posttype-capability-type',
				'type'        => 'text',
				'name'        => 'posttype[capability_type]',
				'key'         => 'capability_type',
				'key_type'    => 'option',
				'value'       => '',
				'label'       => __( 'Capability Type', 'admin-optimizer' ),
				'description' => __( 'The post type to use for checking read, edit, and delete capabilities. A comma-separated second value can be used for plural version.', 'admin-optimizer' ),
				'required'    => false,
			],
			[
				'id'          => 'posttype-map-meta-cap',
				'type'        => 'select',
				'choices'     => [
					'0' => __( 'No', 'admin-optimizer' ),
					'1' => __( 'Yes', 'admin-optimizer' ),
				],
				'name'        => 'posttype[map_meta_cap]',
				'key'         => 'map_meta_cap',
				'key_type'    => 'option',
				'value'       => '',
				'label'       => __( 'Map Meta Cap', 'admin-optimizer' ),
				'description' => __( 'Whether to use the internal default meta capability handling. Default \'No\'.', 'admin-optimizer' ),
				'required'    => false,
			],
			[
				'id'       => 'posttype-supports',
				'type'     => 'checkboxes',
				'choices'  => [
					'title'           => __( 'Title', 'admin-optimizer' ),
					'editor'          => __( 'Editor', 'admin-optimizer' ),
					'thumbnail'       => __( 'Featured Image', 'admin-optimizer' ),
					'excerpt'         => __( 'Excerpt', 'admin-optimizer' ),
					'trackbacks'      => __( 'Trackbacks', 'admin-optimizer' ),
					'custom-fields'   => __( 'Custom Fields', 'admin-optimizer' ),
					'comments'        => __( 'Comments', 'admin-optimizer' ),
					'revisions'       => __( 'Revisions', 'admin-optimizer' ),
					'author'          => __( 'Author', 'admin-optimizer' ),
					'page-attributes' => __( 'Page Attributes', 'admin-optimizer' ),
					'post-formats'    => __( 'Post Formats', 'admin-optimizer' ),
				],
				'checked'  => [ 'title', 'editor' ],
				'name'     => 'posttype[supports][]',
				'key'      => 'supports',
				'key_type' => 'supports',
				'label'    => __( 'Supports', 'admin-optimizer' ),
				'required' => false,
			],
			[
				'id'          => 'posttype-custom-supports',
				'type'        => 'text',
				'name'        => 'posttype[custom_supports]',
				'key'         => 'custom_supports',
				'key_type'    => 'option',
				'value'       => '',
				'label'       => __( 'Custom Supports', 'admin-optimizer' ),
				'description' => __( 'Add custom supports here', 'admin-optimizer' ),
				'required'    => false,
			],
			[
				'id'          => 'posttype-register-meta-box-cb',
				'type'        => 'text',
				'name'        => 'posttype[register_meta_box_cb]',
				'key'         => 'register_meta_box_cb',
				'key_type'    => 'option',
				'value'       => '',
				'label'       => __( 'Meta Box Callback', 'admin-optimizer' ),
				'description' => __( 'Provide a callback function that sets up the meta boxes for the edit form. Do `remove_meta_box()` and `add_meta_box()` calls in the callback. Default null.', 'admin-optimizer' ),
				'required'    => false,
			],
			[
				'id'          => 'posttype-has-archive',
				'type'        => 'select',
				'choices'     => [
					'0' => __( 'No', 'admin-optimizer' ),
					'1' => __( 'Yes', 'admin-optimizer' ),
				],
				'name'        => 'posttype[has_archive]',
				'key'         => 'has_archive',
				'key_type'    => 'option',
				'value'       => '',
				'label'       => __( 'Has Archive', 'admin-optimizer' ),
				'description' => __( 'Is there post type archives for this post type? Default false.', 'admin-optimizer' ),
				'required'    => false,
			],
			[
				'id'          => 'posttype-rewrite',
				'type'        => 'select',
				'choices'     => [
					'1' => __( 'Yes', 'admin-optimizer' ),
					'0' => __( 'No', 'admin-optimizer' ),
				],
				'name'        => 'posttype[do_rewrite]',
				'key'         => 'rewrite',
				'key_type'    => 'option',
				'value'       => '',
				'label'       => __( 'Rewrite', 'admin-optimizer' ),
				'description' => __( 'Triggers the handling of rewrites for this post type. Default true.', 'admin-optimizer' ),
				'required'    => false,
			],
			[
				'id'          => 'posttype-rewrite-slug',
				'type'        => 'text',
				'name'        => 'posttype[rewrite][slug]',
				'key'         => 'rewrite_slug',
				'key_type'    => 'rewrite',
				'value'       => '',
				'label'       => __( 'Custom Rewrite Slug', 'admin-optimizer' ),
				'description' => __( 'Custom post type slug to use instead of the default.', 'admin-optimizer' ),
				'required'    => false,
			],
			[
				'id'          => 'posttype-rewrite-with-front',
				'type'        => 'select',
				'choices'     => [
					'1' => __( 'Yes', 'admin-optimizer' ),
					'0' => __( 'No', 'admin-optimizer' ),
				],
				'name'        => 'posttype[rewrite][with_front]',
				'key'         => 'with_front',
				'key_type'    => 'rewrite',
				'value'       => '',
				'label'       => __( 'With Front', 'admin-optimizer' ),
				'description' => __( 'Should the permalink structure be prepended with the front base. (example: if your permalink structure is /blog/, then your permalink will be /blog/books/). Default is true', 'admin-optimizer' ),
				'required'    => false,
			],
			[
				'id'          => 'posttype-can-export',
				'type'        => 'select',
				'choices'     => [
					'1' => __( 'Yes', 'admin-optimizer' ),
					'0' => __( 'No', 'admin-optimizer' ),
				],
				'name'        => 'posttype[can_export]',
				'key'         => 'can_export',
				'key_type'    => 'option',
				'value'       => '',
				'label'       => __( 'Can Export', 'admin-optimizer' ),
				'description' => __( 'Whether to allow this post type to be exported. Default true.', 'admin-optimizer' ),
				'required'    => false,
			],
			[
				'id'          => 'posttype-delete-with-user',
				'type'        => 'select',
				'choices'     => [
					'-1' => __( 'Leave it as default', 'admin-optimizer' ),
					'1'  => __( 'Yes', 'admin-optimizer' ),
					'0'  => __( 'No', 'admin-optimizer' ),
				],
				'name'        => 'posttype[delete_with_user]',
				'key'         => 'delete_with_user',
				'key_type'    => 'option',
				'value'       => '',
				'label'       => __( 'Delete with user', 'admin-optimizer' ),
				'description' => __( 'Whether to delete posts of this type when deleting a user. If yes, posts of this type belonging to the user will be moved to Trash when the user is deleted. If no, posts of this type belonging to the user will *not* be trashed or deleted. If not set (the default), posts are trashed if post type supports the \'author\' feature. Otherwise posts are not trashed or deleted.', 'admin-optimizer' ),
				'required'    => false,
			],
			[
				'id'          => 'posttype-query-var',
				'type'        => 'select',
				'choices'     => [
					'1' => __( 'Yes', 'admin-optimizer' ),
					'0' => __( 'No', 'admin-optimizer' ),
				],
				'name'        => 'posttype[query_var]',
				'key'         => 'query_var',
				'key_type'    => 'option',
				'value'       => '',
				'label'       => __( 'Query Var', 'admin-optimizer' ),
				'description' => __( 'Sets the query_var key for this post type. Default is true', 'admin-optimizer' ),
				'required'    => false,
			],
			[
				'id'          => 'posttype-custom-query-var',
				'type'        => 'text',
				'name'        => 'posttype[custom_query_var]',
				'key'         => 'custom_query_var',
				'key_type'    => 'option',
				'value'       => '',
				'label'       => __( 'Custom Query Var', 'admin-optimizer' ),
				'description' => __( 'Custom query var slug to use instead of the default.', 'admin-optimizer' ),
				'required'    => false,
			],
			$taxonomies_field,
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
				check_admin_referer( 'adminoptim-add-post-type', 'nonce' );
				$post_data = isset( $_REQUEST['posttype'] ) ? wp_unslash( $_REQUEST['posttype'] ) : [];  // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
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
				check_admin_referer( 'delete-post-type_' . $slug, 'nonce' );
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_die(
						'<h1>' . esc_html( 'You need a higher level of permission.' ) . '</h1>' .
						'<p>' . esc_html( 'Sorry, you are not allowed to delete this item.' ) . '</p>',
						403
					);
				}

				if ( isset( $this->post_types[ $slug ] ) ) {
					unset( $this->post_types[ $slug ] );
				}
				update_option( Post_Types::OPTION_NAME, $this->post_types, false );
				$message = __( 'Post Type deleted successfully.', 'admin-optimizer' );
				break;
			case 'bulk-delete':
				check_admin_referer( 'bulk-custom_post_types' );
				$slugs = isset( $_REQUEST['slugs'] ) ? wp_unslash( $_REQUEST['slugs'] ) : []; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				if ( ! empty( $slugs ) ) {
					foreach ( $slugs as $slug ) {
						$slug = sanitize_title_with_dashes( $slug, '', 'save' );
						if ( isset( $this->post_types[ $slug ] ) ) {
							unset( $this->post_types[ $slug ] );
						} else {
							wp_die( esc_html( 'You attempted to edit a post type that does not exist. Perhaps it was deleted?' ) );
						}
					}
					update_option( Post_Types::OPTION_NAME, $this->post_types, false );
					$message = __( 'Post Type deleted successfully.', 'admin-optimizer' );
				} else {
					wp_die( esc_html__( 'Missing post type slugs.', 'admin-optimizer' ) );
				}

				break;
			case 'edit':
				if ( ! isset( $_REQUEST['slug'] ) ) {
					break;
				}

				$post_status_slug = sanitize_title_with_dashes( wp_unslash( $_REQUEST['slug'] ), '', 'save' );
				check_admin_referer( 'edit-post-type_' . $post_status_slug, 'nonce' );
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_die(
						'<h1>' . esc_html( 'You need a higher level of permission.' ) . '</h1>' .
						'<p>' . esc_html( 'Sorry, you are not allowed to delete this item.' ) . '</p>',
						403
					);
				}
				$this->render_edit_page( $post_status_slug );
				$render_settings = false;
				break;
			case 'save-edit':
				if ( ! isset( $_REQUEST['slug'] ) ) {
					wp_die( esc_html( 'You attempted to save a post type with improper parameter. Please try again!' ) );
				}

				$slug = sanitize_title_with_dashes( wp_unslash( $_REQUEST['slug'] ), '', 'save' );
				check_admin_referer( 'save-edit-post-type_' . $slug, 'nonce' );

				$post_data = isset( $_REQUEST['posttype'] ) ? wp_unslash( $_REQUEST['posttype'] ) : []; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				if ( empty( $this->post_types[ $slug ] ) ) {
					wp_die( esc_html( 'You attempted to edit a post type that does not exist. Perhaps it was deleted?' ) );
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
	 * @param bool   $error  Post error.
	 *
	 * @return void
	 */
	public function render_setting_fields( $message = '', $error = false ) {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Admin Optimizer - Custom Post Types', 'admin-optimizer' ); ?></h1>
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
			<p><?php esc_html_e( 'Create custom post types.', 'admin-optimizer' ); ?></p>
			<div id="col-container" class="wp-clearfix">
				<div id="col-left">
					<div class="form-wrap">
						<h2><?php esc_html_e( 'Add New Post Types', 'admin-optimizer' ); ?></h2>
						<form id="addposttype" method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=' . Post_Types::MENU_SLUG ), null, '&' ); ?>">
							<?php wp_nonce_field( 'adminoptim-add-post-type', 'nonce' ); ?>
							<input type="hidden" name="action" value="add">
							<?php
							$this->output_setting_field( $this->setting_fields['required'] );
							?>
							<div id="cpt-accordion">
								<h3><?php esc_html_e( 'Additional Labels', 'admin-optimizer' ); ?></h3>
								<div class="inside">
									<?php $this->output_setting_field( $this->setting_fields['labels'] ); ?>
								</div>
								<h3><?php esc_html_e( 'More Options', 'admin-optimizer' ); ?></h3>
								<div class="inside">
									<?php $this->output_setting_field( $this->setting_fields['options'] ); ?>
								</div>
							</div>
							<p class="submit">
								<?php
								submit_button(
									__( 'Add New Post Type', 'admin-optimizer' ),
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
					<form id="bulk-delete-poststatus" method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=adminoptimizer-post-types' ), null, '&' ); ?>">
					<?php
					$posttypes_list_table = new Post_Types_List_Table();
					$posttypes_list_table->prepare_items();
					$posttypes_list_table->display();
					?>
					</form>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Output list of Settings fields
	 *
	 * @param array  $setting_fields List of Settings fields.
	 * @param string $action Type of action to handle data.
	 * @param array  $post_type List of Post Types.
	 *
	 * @return void
	 */
	private function output_setting_field( array $setting_fields, $action = 'add', $post_type = [] ) {
		if ( 'edit' === $action && empty( $post_type ) ) {
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
								$field['value'] = $post_type['labels'][ $field['key'] ] ?? '';
							} elseif ( 'rewrite' === $field['key_type'] ) {
								$field['value'] = $post_type['rewrite'][ $field['key'] ] ?? '';
							} else {
								$field['value'] = $post_type[ $field['key'] ] ?? '';
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
							$field['value'] = $post_type[ $field['key'] ] ?? '';
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
							$field['value'] = $post_type[ $field['key'] ] ?? '';
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
							if ( 'rewrite' === $field['key'] ) {
								$field['value'] = array_key_exists( 'do_rewrite', $post_type ) ? strval( intval( $post_type['do_rewrite'] ) ) : '1';
							} elseif ( 'with_front' === $field['key'] ) {
								if ( is_array( $post_type['rewrite'] ) && isset( $post_type['rewrite']['with_front'] ) ) {
									$field['value'] = $post_type['rewrite']['with_front'] ? '1' : '0';
								} else {
									$field['value'] = '1';
								}
							} else {
								$field['value'] = $post_type[ $field['key'] ] ?? '';
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
							if ( 'taxonomies' === $field['key_type'] ) {
								$field['checked'] = $post_type['taxonomies'] ?? '';
							} elseif ( 'supports' === $field['key_type'] ) {
								$field['checked'] = $post_type['supports'] ?? '';
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
	 * @param array  $post_data Post data.
	 * @param string $action Type of action for handling post data.
	 * @param string $slug Post Type slug.
	 *
	 * @return object|\WP_Error
	 */
	private function handle_post_data( $post_data, $action = '', $slug = '' ) {

		if ( empty( $post_data['slug'] ) ) {
			return new \WP_Error( '400', __( 'The post type slug cannot be empty.', 'admin-optimizer' ) );

		} elseif ( empty( $post_data['labels']['name'] ) ) {
			return new \WP_Error( '400', __( 'The post type name cannot be empty.', 'admin-optimizer' ) );

		} elseif ( empty( $post_data['labels']['singular_name'] ) ) {
			return new \WP_Error( '400', __( 'The post type singular name cannot be empty.', 'admin-optimizer' ) );

		} else {
			$post_type           = [];
			$existing_post_types = get_post_types();
			// check if post type already exists?
			$post_data['slug'] = sanitize_title_with_dashes( $post_data['slug'], '', 'save' );

			if ( 'save-edit' === $action ) {
				$post_data['slug'] = $slug;
				if ( empty( $this->post_types[ $post_data['slug'] ] ) ) {
					return new \WP_Error( '400', __( 'You attempted to save a post type that does not exist. Perhaps it was deleted?', 'admin-optimizer' ) );
				}
			} elseif ( in_array( $post_data['slug'], $existing_post_types, true ) ) {
					return new \WP_Error( '400', __( 'The post type already exist.', 'admin-optimizer' ) );
			}

			$post_type['slug'] = $post_data['slug'];
			unset( $post_data['slug'] );
			if ( ! empty( $post_data['description'] ) ) {
				$post_type['description'] = sanitize_textarea_field( $post_data['description'] );
				unset( $post_data['description'] );
			}
			foreach ( $post_data['labels'] as $label_key => $value ) {
				if ( empty( $value ) ) {
					unset( $post_data['labels'][ $label_key ] );
				}
			}
			$post_type['labels'] = array_map( 'sanitize_text_field', $post_data['labels'] );
			unset( $post_data['labels'] );
			if ( '0' === $post_data['do_rewrite'] ) {
				$post_type['rewrite'] = false;
			} else {
				$post_type['rewrite']               = [];
				$post_type['rewrite']['slug']       = isset( $post_data['rewrite']['slug'] ) ? sanitize_title_with_dashes( $post_data['rewrite']['slug'] ) : '';
				$post_type['rewrite']['with_front'] = isset( $post_data['rewrite']['with_front'] ) ? (bool) $post_data['rewrite']['with_front'] : true;
			}
			unset( $post_data['rewrite'] );
			$post_type['do_rewrite'] = in_array( $post_data['do_rewrite'], [ '0', '1' ], true ) ? $post_data['do_rewrite'] : '1';
			unset( $post_data['do_rewrite'] );
			array_walk_recursive( $post_data, 'sanitize_text_field' );
			foreach ( $post_data as $key => $value ) {
				if ( empty( $value ) || '-1' === $value ) {
					continue;
				}
				if ( '0' === $value || '1' === $value ) {
					$post_type[ $key ] = (bool) $value;
					continue;
				}
				if ( 'supports' === $key && is_array( $value ) ) {
					$post_type['supports'] = $value;
					continue;
				}
				if ( 'custom_supports' === $key ) {
					// need to check if custom_supports contains any invalid characters.
					$custom_supports = [ 'trim', explode( ',', $value ) ];
					if ( empty( $post_type['supports'] ) ) {
						$post_type['supports'] = $custom_supports;
					} else {
						$post_type['supports'] = array_merge( $post_type['supports'], $custom_supports );
					}
					$post_type['custom_supports'] = $value;
					continue;
				}
				$post_type[ $key ] = $value;
			}
			$this->post_types[ $post_type['slug'] ] = $post_type;
			update_option( Post_Types::OPTION_NAME, $this->post_types, false );
			if ( 'save-edit' === $action ) {
				$message = __( 'Post Type edited successfully.', 'admin-optimizer' );
			} else {
				$message = __( 'Post Type added successfully.', 'admin-optimizer' );
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
	 * @param string $slug  Post type slug.
	 * @param string $message Message.
	 * @param bool   $error Is there error?.
	 *
	 * @return void
	 */
	public function render_edit_page( $slug, $message = '', $error = false ) {
		?>
		<div class="wrap">
			<?php
			if ( ! isset( $this->post_types[ $slug ] ) ) {
				wp_die( esc_html( 'You attempted to edit a post type that does not exist. Perhaps it was deleted?' ) );
			}
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die(
					'<h1>' . esc_html( 'You need a higher level of permission.' ) . '</h1>' .
					'<p>' . esc_html( 'Sorry, you are not allowed to edit this item.' ) . '</p>',
					403
				);
			}
			?>
			<h1><?php esc_html_e( 'Admin Optimizer - Edit Post Type', 'admin-optimizer' ); ?></h1>
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
				<form id="edittag" method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=' . Post_Types::MENU_SLUG ), null, '&' ); ?>">
					<input type="hidden" name="action" value="save-edit" >
					<input type="hidden" name="slug" value="<?php echo esc_attr( $slug ); ?>">
					<?php wp_nonce_field( 'save-edit-post-type_' . $slug, 'nonce' ); ?>
					<?php
					$this->output_setting_field( $this->setting_fields['required'], 'edit', $this->post_types[ $slug ] );
					?>
					<div id="cpt-accordion">
						<h3><?php esc_html_e( 'Additional Labels', 'admin-optimizer' ); ?></h3>
						<div class="inside">
							<?php $this->output_setting_field( $this->setting_fields['labels'], 'edit', $this->post_types[ $slug ] ); ?>
						</div>
						<h3><?php esc_html_e( 'More Options', 'admin-optimizer' ); ?></h3>
						<div class="inside">
							<?php $this->output_setting_field( $this->setting_fields['options'], 'edit', $this->post_types[ $slug ] ); ?>
						</div>
					</div>
					<p><?php submit_button( 'Save Post Type', 'primary', 'submit', false ); ?> <a class="cancel-settings-link" href="<?php echo esc_url( admin_url( 'admin.php?page=' . Post_Types::MENU_SLUG ) ); ?>"><?php esc_html_e( 'Cancel', 'admin-optimizer' ); ?></a></p>
				</form>
			</div>
		</div>
		<?php
	}

	/**
	 * Enqueue scripts and style
	 *
	 * @param string $hook_suffix Check if we are on the right page before enqueueing scripts.
	 *
	 * @return void
	 */
	public function enqueue_scripts( $hook_suffix ) {
		if ( str_contains( $hook_suffix, Post_Types::MENU_SLUG ) ) {
			wp_enqueue_script( 'adminoptim-post-types-settings', Post_Types::MODULE_URL . 'assets/js/post-types-settings.min.js', [ 'jquery-ui-accordion' ], filemtime( Post_Types::MODULE_PATH . 'assets/js/post-types-settings.min.js' ), true );
			wp_enqueue_style( 'adminoptim-post-types-settings', Post_Types::MODULE_URL . 'assets/css/post-types-settings.min.css', [], filemtime( Post_Types::MODULE_PATH . 'assets/css/post-types-settings.min.css' ) );
		}
	}
}