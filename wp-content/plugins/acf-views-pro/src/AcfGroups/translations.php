<?php
// stub for the multilingual detection 
// labels and descriptions will be translated on the fly (from the PHPDoc comments) 

// AcfCardData.php : labels

__("Basic", "acf-views");
__("Description", "acf-views");
__("CSS classes", "acf-views");
__("ACF View", "acf-views");
__("No Posts Found Message", "acf-views");
__("Post Type", "acf-views");
__("Post Status", "acf-views");
__("Maximum number of posts", "acf-views");
__("Pool of posts", "acf-views");
__("Exclude posts", "acf-views");
__("Ignore Sticky Posts", "acf-views");
__("Sort", "acf-views");
__("Sort by", "acf-views");
__("Sort order", "acf-views");
__("Sort by Meta Field Group", "acf-views");
__("Sort by Meta Field", "acf-views");
__("Meta Filters", "acf-views");
__("Taxonomy Filters", "acf-views");
__("Rules", "acf-views");
__("Pagination", "acf-views");
__("With Pagination", "acf-views");
__("Pagination Type", "acf-views");
__("Label for the 'Load More' button", "acf-views");
__("Posts Per Page", "acf-views");
__("Layout", "acf-views");
__("Enable Layout rules", "acf-views");
__("Layout Rules", "acf-views");
__("Advanced", "acf-views");
__("Markup Preview", "acf-views");
__("CSS Code", "acf-views");
__("JS Code", "acf-views");
__("Custom Markup", "acf-views");
__("Query Preview", "acf-views");
__("Preview", "acf-views");
__("Preview", "acf-views");

// AcfCardData.php : descriptions

__("Add a short description for your views’ purpose. Only seen on the admin ACF Cards list", "acf-views");
__("Add a class name without a dot (e.g. 'class-name') or multiple classes with single space as a delimiter (e.g. 'class-name1 class-name2'). These classes are added to the wrapping HTML element. <a target='_blank' href='https://www.w3schools.com/cssref/sel_class.asp'>Learn more about CSS Classes</a>", "acf-views");
__("Assigned ACF View is used to display every post from the query results", "acf-views");
__("This message will be displayed in case there are no posts found. Leave empty to not show a message", "acf-views");
__("Filter by post type. You can select multiple items", "acf-views");
__("Filter by post status. You can select multiple items", "acf-views");
__("Use '-1' to set 'unlimited'", "acf-views");
__("Here you can manually assign specific posts. If set then the query will be limited to posts ONLY from this pool. It means the result will consist ONLY from posts from this pool, which also fit all other filters. If you want to have the same order of results like here, please choose the 'Pool of posts' option in the Sort tab", "acf-views");
__("Here you can manually exclude specific posts from the query. It means the query will ignore posts from this list, even if they fit the filters. Warning : this field can't be used together with 'Pool of posts'", "acf-views");
__("If unchecked then sticky posts will be at the top of results. <a target='_blank' href='https://wordpress.org/support/article/sticky-posts/'>Learn more about Sticky Posts</a>", "acf-views");
__("Select which field results should be sorted by. ‘Default’ keeps the default order (latest first, sticky options may affect it)", "acf-views");
__("Defines the sorting order of posts", "acf-views");
__("Select a target group", "acf-views");
__("Select a target field. Note : only fields with <a target='_blank' href='https://docs.acfviews.com/getting-started/supported-field-types'>supported field types</a> are listed here", "acf-views");
__("If enabled then instead of displaying all posts from query results, only the limited number of posts will be shown and user will be able to load more. <a target='_blank' href='https://docs.acfviews.com/guides/acf-cards/features/pagination-pro'>Read more</a>", "acf-views");
__("Defines a way in which user can load more. For 'Load More Button' and 'Page Numbers' cases a special markup will be added to the card automatically, you can style it (using the 'CSS Code' field in the 'Advanced' tab)", "acf-views");
__("Controls how many posts will be displayed initially and how many posts will be appended every time when user triggers 'Load More'. Total amount of post is limited by the 'Maximum amount of posts' field in the 'Filter' tab", "acf-views");
__("When enabled CSS layout styles are added to CSS Code in the Advanced tab. These styles are automatically updated each time. <br>Tip: If you’d like to edit the Layout CSS manually, simply disable this here. Disabling this does not remove the previously added CSS Code", "acf-views");
__("The rules control layout of card items. <br>Note: These rules are inherited from small to large. For example: If you’ve set up 'Mobile' and 'Desktop' screen rules, then 'Tablet' will have the same rules as 'Mobile' and 'Large Desktop' will have the same rules as 'Desktop'", "acf-views");
__("Output preview of HTML markup generated. Important! Publish or Update your view to see the latest markup", "acf-views");
__("Define your CSS style rules. This will be added within &lt;style&gt;&lt;/style&gt; tags ONLY to pages that have this card. <br>Don't style view fields here, ACF View has its own CSS field for this goal. <br> Magic shortcuts are available : <br> '#card' will be replaced with '.acf-card--id--X'.<br> '#card__' will be replaced with '.acf-card--id--X .acf-card__'. <br> To match items wrapper you should use '#card__items' selector, to match single item you should use '#card .acf-view' selector", "acf-views");
__("Add your own Javascript to your view. This will be added within &lt;script&gt;&lt;/script&gt; tags ONLY to pages that have this view and also will be wrapped into an anonymous function to avoid name conflicts. Don't use inline comments ('//') inside the code, otherwise it'll break the snippet.", "acf-views");
__("Warning : for users familiar with HTML. You can copy the Markup field output and add your extra elements (like heading). <br>Make sure you've kept all variables (like '&#36;name&#36;') and default HTML tags (like div.acf-card and div.acf-card__items), otherwise posts won't be displayed and pagination won't work", "acf-views");
__("For debug purposes. Here you can see the query that will be executed to get posts for this card. Important! Publish or Update your card to see the latest query", "acf-views");
__("See an output preview of your ACF Card, where you can test some CSS styles. <a target='_blank' href='https://docs.acfviews.com/guides/acf-views/features/preview'>Read more</a> <br> Styles from your front page are included in the preview (some differences may appear). <br>Note: Press 'Update' if you have changed Custom Markup (in the Advanced tab) to see the latest preview. <br> Important! Don't style your ACF View here, instead use the CSS Code field in your ACF View for this goal. <br> After testing: Copy and paste the ACF Card styles to the CSS Code field.", "acf-views");

// AcfCardData.php : buttons

__("Add Rule", "acf-views");

// AcfCardData.php : choices

__("Default", "acf-views");
__("ID", "acf-views");
__("Menu order", "acf-views");
__("Meta value", "acf-views");
__("Meta value numeric", "acf-views");
__("Author", "acf-views");
__("Title", "acf-views");
__("Name", "acf-views");
__("Type", "acf-views");
__("Date", "acf-views");
__("Modified", "acf-views");
__("Parent", "acf-views");
__("Random", "acf-views");
__("Comment count", "acf-views");
__("Pool of posts", "acf-views");
__("Ascending", "acf-views");
__("Descending", "acf-views");
__("Load More Button", "acf-views");
__("Infinity Scroll", "acf-views");
__("Page Numbers", "acf-views");

// AcfViewData.php : labels

__("Basic", "acf-views");
__("Description", "acf-views");
__("CSS classes", "acf-views");
__("With Gutenberg Block", "acf-views");
__("Fields", "acf-views");
__("Markup", "acf-views");
__("Markup Preview", "acf-views");
__("Custom Markup", "acf-views");
__("Custom Markup Variables", "acf-views");
__("Advanced", "acf-views");
__("CSS Code", "acf-views");
__("JS Code", "acf-views");
__("Preview", "acf-views");
__("Preview Object", "acf-views");
__("Preview", "acf-views");

// AcfViewData.php : descriptions

__("Add a short description for your views’ purpose. Note : This description is only seen on the admin ACF Views list", "acf-views");
__("Add a class name without a dot (e.g. “class-name”) or multiple classes with single space as a delimiter (e.g. “class-name1 class-name2”). These classes are added to the wrapping HTML element. <a target='_blank' href='https://www.w3schools.com/cssref/sel_class.asp'>Learn more about CSS Classes</a>", "acf-views");
__("If checked, a separate gutenberg block for this view will be available. <a target='_blank' href='https://docs.acfviews.com/guides/acf-views/features/gutenberg-pro'>Read more</a>", "acf-views");
__("Assign Advanced Custom Fields (ACF) to your View. <br> Tip : hover mouse on the field number column and drag to reorder", "acf-views");
__("Output preview of HTML markup generated. Important! Publish or Update your view to see the latest markup", "acf-views");
__("Warning : for users familiar with HTML. Write your own HTML markup with full control over how your view will look. You can copy the Markup field output and make your changes. <br>Make sure you've kept all variables (like '&#36;name&#36;') and wrappers (like HTML comments), otherwise field values won't be inserted. <br>Important! This field will not be updated automatically when you add or remove fields, so you have to update this field manually to reflect the new changes (you could see the Markup field for assistance). <a target='_blank' href='https://docs.acfviews.com/guides/acf-views/features/custom-markup-pro'>Read more</a>", "acf-views");
__("You can add custom variables to the custom markup using this PHP code snippet. <br>The snippet must return an associative array of values, where keys are variable names. These variables will be available in the custom markup within brackets, like '{VARIABLE_NAME}'.<br> In the snippet pre-defined following variables : '&#36;_objectId' (current data post), '&#36;_viewId' (current view id),'&#36;_fields' (an associative field values array, where keys are field identifiers). <a target='_blank' href='https://docs.acfviews.com/guides/acf-views/features/custom-markup-variables-pro'>Read more</a>", "acf-views");
__("Define your CSS style rules here or within your theme. Rules defined here will be added within &lt;style&gt;&lt;/style&gt; tags ONLY to pages that have this view. <br> Magic shortcuts are available : <br> '#view' will be replaced with '.acf-view--id--X'.<br> '#view__' will be replaced with '.acf-view--id--X .acf-view__'. It means you can use '#view__row' and it'll be replaced with '.acf-view--id--X .acf-view__row'", "acf-views");
__("Add your own Javascript to your view. This will be added within &lt;script&gt;&lt;/script&gt; tags ONLY to pages that have this view and also will be wrapped into an anonymous function to avoid name conflicts. Don't use inline comments ('//') inside the code, otherwise it'll break the snippet.", "acf-views");
__("Select a data object (which field values will be used) and press the 'Update' button to see the final markup in the preview", "acf-views");
__("Here you can see the preview of the view and play with CSS rules. <a target='_blank' href='https://docs.acfviews.com/guides/acf-views/features/preview'>Read more</a><br>Important! Press the 'Update' button after changes to see the latest markup here. <br>Your changes to the preview won't be applied to the view automatically, if you want to keep them copy amended CSS to the 'CSS Code' field and press the 'Update' button. <br> Note: styles from your front page are included in the preview (some differences may appear)", "acf-views");

// AcfViewData.php : buttons

__("Add Field", "acf-views");

// CardLayoutData.php : labels

__("Screen Size", "acf-views");
__("Layout", "acf-views");
__("Amount of Columns", "acf-views");
__("Horizontal gap", "acf-views");
__("Vertical gap", "acf-views");

// CardLayoutData.php : descriptions

__("Controls to which screen size the rule applies", "acf-views");
__("Change the type of layout", "acf-views");
__("Define how many columns each row should have. By default, columns have equal width", "acf-views");
__("Horizontal gap between items. Format: '10px'. Possible units are 'px', '%', 'em/rem'", "acf-views");
__("Vertical gap between items. Format: '10px'. Possible units are 'px', '%', 'em/rem'", "acf-views");

// CardLayoutData.php : choices

__("Mobile", "acf-views");
__("Tablet (> 576px)", "acf-views");
__("Desktop (> 992px)", "acf-views");
__("Large Desktop (> 1400px)", "acf-views");
__("Row", "acf-views");
__("Column", "acf-views");
__("Grid", "acf-views");

// DemoGroup.php : labels

__("Brand", "acf-views");
__("Model", "acf-views");
__("Price", "acf-views");
__("Website link", "acf-views");

// DemoGroup.php : choices

__("Samsung", "acf-views");
__("Nokia", "acf-views");
__("HTC", "acf-views");
__("Xiaomi", "acf-views");

// Field.php : labels

__("Field", "acf-views");
__("Label", "acf-views");
__("Link Label", "acf-views");
__("Image Size", "acf-views");
__("ACF View", "acf-views");
__("Gallery Layout", "acf-views");
__("Image Lightbox", "acf-views");
__("Field Options", "acf-views");
__("Identifier", "acf-views");
__("Default Value", "acf-views");
__("Show When Empty", "acf-views");
__("Masonry: Row Min Height", "acf-views");
__("Masonry: Gutter", "acf-views");
__("Masonry: Mobile Gutter", "acf-views");
__("Hide Google Map", "acf-views");
__("Map address format", "acf-views");
__("Values delimiter", "acf-views");

// Field.php : descriptions

__("Select a target field. Note : only fields with <a target='_blank' href='https://docs.acfviews.com/getting-started/supported-field-types'>supported field types</a> are listed here", "acf-views");
__("If filled will be added to the markup as a prefix label of the field above", "acf-views");
__("You can set the link label here. Leave empty to use the default", "acf-views");
__("Controls the size of the image, it changes the image src", "acf-views");
__("If filled then Posts within this field will be displayed using the selected View. <a target='_blank' href='https://docs.acfviews.com/guides/acf-views/features/display-fields-from-a-related-post-pro'>Read more</a>", "acf-views");
__("Select the gallery layout type. If Masonry is chosen see 'Field Options' for more settings. <a target='_blank' href='https://docs.acfviews.com/guides/acf-views/fields/gallery'>Read more</a>", "acf-views");
__("If enabled images will include a zoom icon on hover and when clicked popup with a large image", "acf-views");
__("Used in the markup, leave empty to use chosen field name. Allowed symbols : letters, numbers, underline and dash. Important! Should be unique within the group", "acf-views");
__("Set up default value, only used when the field is empty", "acf-views");
__("By default, empty fields are hidden. Turn on to show even when field has no value", "acf-views");
__("Minimum height of a row in px", "acf-views");
__("Margin between items in px", "acf-views");
__("Margin between items on mobile in px", "acf-views");
__("The Map is shown by default. Turn this on to hide the map", "acf-views");
__("Use these variables to format your map address: <br> &#36;street_number&#36;, &#36;street_name&#36;, &#36;city&#36;, &#36;state&#36;, &#36;post_code&#36;, &#36;country&#36; <br> HTML is also supported. If left empty the address is not shown.", "acf-views");
__("If multiple values are chosen, you can define their delimiter here. HTML is supported", "acf-views");

// Field.php : choices

__("Default", "acf-views");
__("Masonry", "acf-views");

// Item.php : labels

__("Field", "acf-views");
__("Group", "acf-views");
__("Sub Fields", "acf-views");
__("Sub fields", "acf-views");

// Item.php : descriptions

__("Select a target group. Use &#36;Post&#36; group to select built-in post fields, '&#36;Taxonomy&#36;' group to select taxonomies", "acf-views");
__("Setup sub fields here", "acf-views");

// Item.php : buttons

__("Add Sub Field", "acf-views");

// MetaField.php : labels

__("Group", "acf-views");
__("Field", "acf-views");
__("Comparison", "acf-views");
__("Value2", "acf-views");

// MetaField.php : descriptions

__("Select a target group", "acf-views");
__("Select a target field. Note : only fields with <a target='_blank' href='https://docs.acfviews.com/getting-started/supported-field-types'>supported field types</a> are listed here", "acf-views");
__("Controls how field value will be compared", "acf-views");
__("Value that will be compared.<br>Can be empty, in case you want to compare with empty string.<br>Use <strong>&#36;post&#36;</strong> to pick up the actual ID or <strong>&#36;post&#36;.field-name</strong> to pick up field value dynamically", "acf-views");

// MetaField.php : choices

__("Equal to", "acf-views");
__("Not Equal to", "acf-views");
__("Bigger than", "acf-views");
__("Bigger than or Equal to", "acf-views");
__("Less than", "acf-views");
__("Less than or Equal to", "acf-views");
__("Contains", "acf-views");
__("Does Not Contain", "acf-views");
__("Exists", "acf-views");
__("Does Not Exist", "acf-views");

// MetaFilter.php : labels

__("Relation", "acf-views");
__("Rules", "acf-views");

// MetaFilter.php : descriptions

__("Controls how meta rules will be joined within the meta query", "acf-views");
__("Rules for the meta query. Multiple rules are supported. <a target='_blank' href='https://docs.acfviews.com/guides/acf-cards/features/meta-filter-pro'>Read more</a> <br>If you want to see the query that was created by your input, please press the 'Update' button and have a look at the 'Query Preview' field in the 'Advanced' tab", "acf-views");

// MetaFilter.php : buttons

__("Add Rule", "acf-views");

// MetaFilter.php : choices

__("AND", "acf-views");
__("OR", "acf-views");

// MetaRule.php : labels

__("Relation", "acf-views");
__("Fields", "acf-views");

// MetaRule.php : descriptions

__("Controls how the meta fields will be joined within the meta rule", "acf-views");
__("Fields for the meta rule. Multiple fields are supported", "acf-views");

// MetaRule.php : buttons

__("Add Field", "acf-views");

// MetaRule.php : choices

__("AND", "acf-views");
__("OR", "acf-views");

// MountPoint.php : labels

__("Specific posts", "acf-views");
__("Post Types", "acf-views");
__("Mount Point", "acf-views");
__("Mount Position", "acf-views");
__("Shortcode Arguments", "acf-views");

// MountPoint.php : descriptions

__("Limit the mount point to only specific posts. Leave empty and use the 'Post Types' field to limit to specific post types", "acf-views");
__("Specific post types, to all items of which the shortcode should be mounted. Leave empty if you want to add to specific items only and use the 'Specific posts' field", "acf-views");
__("To which unique Word, String or HTML piece to Mount to. Together with the 'Mount Position' controls the placement. If left empty all the content will be used as a mount point", "acf-views");
__("Where the shortcode should be mounted", "acf-views");
__("Add arguments to the shortcode, e.g. 'user-with-roles'. Only the view/card 'id' argument is filled by default", "acf-views");

// MountPoint.php : choices

__("Before", "acf-views");
__("After", "acf-views");
__("Instead (replace)", "acf-views");

// RepeaterField.php : labels

__("Field", "acf-views");
__("Sub Field", "acf-views");
__("Identifier", "acf-views");

// RepeaterField.php : descriptions

__("This list contains fields for the selected repeater or group. Note : only fields with <a target='_blank' href='https://docs.acfviews.com/getting-started/supported-field-types'>supported field types</a> are listed here. <a target='_blank' href='https://www.advancedcustomfields.com/resources/repeater/'>Learn more about Repeater Fields</a>", "acf-views");
__("Used in the markup, leave empty to use chosen field name. Allowed symbols : letters, numbers, underline and dash. Important! Should be unique within the repeater", "acf-views");

// TaxField.php : labels

__("Taxonomy", "acf-views");
__("Comparison", "acf-views");
__("Term", "acf-views");

// TaxField.php : descriptions

__("Select a target taxonomy", "acf-views");
__("Controls how taxonomy will be compared", "acf-views");
__("Term that will be compared", "acf-views");

// TaxField.php : choices

__("Equal to", "acf-views");
__("Not Equal to", "acf-views");
__("Exists", "acf-views");
__("Does Not Exist", "acf-views");

// TaxFilter.php : labels

__("Relation", "acf-views");
__("Rules", "acf-views");

// TaxFilter.php : descriptions

__("Controls how taxonomy rules will be joined within the taxonomy query", "acf-views");
__("Rules for the taxonomy query. Multiple rules are supported. <a target='_blank' href='https://docs.acfviews.com/guides/acf-cards/features/taxonomy-filter-pro'>Read more</a> <br> If you want to see the query that was created by your input, please press the 'Update' button and have a look at the 'Query Preview' field in the 'Advanced' tab", "acf-views");

// TaxFilter.php : buttons

__("Add Rule", "acf-views");

// TaxFilter.php : choices

__("AND", "acf-views");
__("OR", "acf-views");

// TaxRule.php : labels

__("Relation", "acf-views");
__("Taxonomies", "acf-views");

// TaxRule.php : descriptions

__("Controls how the taxonomies will be joined within the taxonomy rule", "acf-views");
__("Taxonomies for the taxonomy rule. Multiple taxonomies are supported", "acf-views");

// TaxRule.php : buttons

__("Add Taxonomy", "acf-views");

// TaxRule.php : choices

__("AND", "acf-views");
__("OR", "acf-views");