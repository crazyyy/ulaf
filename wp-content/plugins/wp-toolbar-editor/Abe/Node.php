<?php

/**
 * A wrapper for WordPress Toolbar/Admin Bar nodes.
 *
 * @property string $title
 * @property string $href
 *
 * @property string $html
 * @property string $class
 * @property string $onclick
 * @property string $target
 * @property string $titleAttr
 * @property int $tabindex
 *
 * @property array|null $is_visible_to_actor
 */
class Abe_Node {
	const PERSISTENT_PROPERTIES = [
		'id',
		'group',
		'parent',
		'is_custom',
		'is_hidden',
		'defaults',
		'last_seen_timestamp',
	];

	const EMPTY_META_DEFAULTS = [
		'class'     => '',
		'html'      => null,
		'onclick'   => null,
		'target'    => null,
		'titleAttr' => null,
		'tabindex'  => null,
	];

	public $id;

	public $group = false;
	public $parent = null;

	public $is_custom = false;
	public $is_hidden = false;

	public $is_present_on_page = false;
	public $last_seen_timestamp = 0;

	protected $settings = [];
	protected $defaults = [
		'title'     => 'Default Title',
		'href'      => '',
		'class'     => '',
		'html'      => null,
		'onclick'   => null,
		'target'    => null,
		'titleAttr' => null,
		'tabindex'  => null,
	];

	protected $fields_supporting_shortcodes = ['title', 'href'];

	protected function __construct($id, $group = false) {
		$this->id = $id;
		$this->group = $group;
	}

	public function __get($name) {
		if ( isset($this->settings[$name]) ) {
			return $this->settings[$name];
		} else if ( array_key_exists($name, $this->defaults) ) {
			return $this->defaults[$name];
		} else {
			return null;
		}
	}

	public function __set($name, $value) {
		$this->settings[$name] = $value;
	}

	public function __isset($name) {
		if ( isset($this->settings[$name]) ) {
			return true;
		} else if ( array_key_exists($name, $this->defaults) ) {
			return isset($this->defaults[$name]);
		}
		return false;
	}

	public function hasCustomSetting($name) {
		return isset($this->settings[$name]);
	}

	/**
	 * Get node properties as an argument array that can be passed to WP_Admin_Bar::add_node().
	 *
	 * @return array
	 */
	public function toNodeArgs() {
		$args = [
			'id'     => $this->id,
			'title'  => $this->title,
			'parent' => $this->parent,
			'href'   => $this->href,
			'group'  => $this->group,
			'meta'   => [
				'html'     => $this->html,
				'class'    => $this->class,
				'onclick'  => $this->onclick,
				'title'    => $this->titleAttr,
				'target'   => $this->target,
				'tabindex' => $this->tabindex,
			],
		];

		//Apply shortcodes.
		foreach ($this->fields_supporting_shortcodes as $field) {
			if ( isset($args[$field]) && (strpos($args[$field], '[') !== false) ) {
				$args[$field] = do_shortcode($args[$field]);
			}
		}
		if ( isset($args['meta'], $args['meta']['html']) && (strpos($args['meta']['html'], '[') !== false) ) {
			$args['meta']['html'] = do_shortcode($args['meta']['html']);
		}

		//WPML support: Translate custom titles where available.
		if ( !$this->group && function_exists('icl_t') && $this->hasCustomSetting('title') ) {
			$args['title'] = icl_t(Abe_AdminBarEditor::WPML_CONTEXT, $this->getWpmlName(), $args['title']);
		}

		$args = array_filter($args, [__CLASS__, 'isNotNull']);
		$args['meta'] = array_filter($args['meta'], [__CLASS__, 'isNotNull']);

		return $args;
	}

	/**
	 * Create a node from a WP admin bar node instance.
	 *
	 * @param StdClass $nodeArgs
	 * @return Abe_Node
	 */
	public static function fromNodeArgs($nodeArgs) {
		$nodeArgs = (object)$nodeArgs;
		$isGroup = property_exists($nodeArgs, 'group') ? $nodeArgs->group : false;
		$node = new self($nodeArgs->id, $isGroup);
		$node->parent = isset($nodeArgs->parent) ? $nodeArgs->parent : null;
		$node->setDefaultsFromNodeArgs($nodeArgs);
		return $node;
	}


	/**
	 * Set node defaults based on an admin bar node.
	 *
	 * @param StdClass|array $args
	 */
	public function setDefaultsFromNodeArgs($args) {
		$defaults = is_object($args) ? get_object_vars($args) : $args;

		//Bring "meta" arguments to the base level.
		if ( isset($defaults['meta']) && is_array($defaults['meta']) ) {
			$meta = $defaults['meta'];

			//Rename "title" (i.e. title attribute) to "titleAttr" to prevent conflict
			//with the existing "title" argument that specifies the menu title.
			if ( isset($meta['title']) ) {
				$meta['titleAttr'] = $meta['title'];
				unset($meta['title']);
			}

			$defaults = array_merge($defaults, $meta);
			unset($defaults['meta']);
		}

		$this->defaults = array_merge(
			$this->defaults,
			//Make sure to reset meta defaults that are not present in the actual node.
			self::EMPTY_META_DEFAULTS,
			$defaults
		);

		//If there exists a node to copy the defaults from, that means this node is present
		//on the current page.
		$this->is_present_on_page = true;
	}

	protected static function isNotNull($value) {
		return $value !== null;
	}

	/**
	 * Retrieve node properties as a simple associative array.
	 * This is useful for storage and serialization to JSON.
	 */
	public function toArray() {
		$properties = [];
		foreach (self::PERSISTENT_PROPERTIES as $name) {
			$properties[$name] = $this->$name;
		}

		return array_merge($properties, $this->settings);
	}

	/**
	 * @param array $properties
	 * @return Abe_Node
	 */
	public static function fromArray($properties) {
		if ( is_object($properties) ) {
			$properties = get_object_vars($properties);
		}
		if ( isset($properties['defaults']) && is_object($properties['defaults']) ) {
			$properties['defaults'] = get_object_vars($properties['defaults']);
		}
		$node = new self($properties['id'], $properties['group'] ?? false);
		foreach ($properties as $name => $value) {
			$node->$name = $value;
		}
		return $node;
	}

	protected function serializeForJs() {
		$properties = $this->toArray();

		//Include presence metadata. To save bandwidth, only include non-default or unusual values.
		if ( !$this->is_present_on_page ) {
			$properties['is_present_on_page'] = $this->is_present_on_page;
		}
		if ( !empty($this->last_seen_timestamp) ) {
			$properties['last_seen_timestamp'] = $this->last_seen_timestamp;
		}

		return $properties;
	}

	/**
	 * Convert a list of nodes to an associative array of arrays.
	 * This is pretty much equivalent to calling serializeForJs() on every node.
	 *
	 * @param Abe_Node[]|StdClass[] $nodes A list of Abe_Node instances or node argument objects.
	 * @return array An array of node property arrays, indexed by node ID.
	 */
	public static function serializeNodeListForJs($nodes): array {
		$output = [];
		foreach ($nodes as $node) {
			if ( !($node instanceof self) ) {
				$node = self::fromNodeArgs($node);
				//We can assume that the node is present on the current page.
				//This is useful when serializing default nodes for the editor.
				$node->last_seen_timestamp = time();
			}
			$output[$node->id] = $node->serializeForJs();
		}
		return $output;
	}

	/**
	 * Check if this node is visible to the specified actor(s).
	 * When dealing with multiple actors, will return true if at least one of the actors can see the node.
	 *
	 * @param array $actors An array of one or more actors (e.g. "role:editor", "special:super_admin" and so on).
	 * @param string|null $userActor
	 * @return bool
	 */
	public function isVisibleTo($actors, $userActor = null) {
		if ( $this->is_hidden ) {
			//This node is completely hidden.
			return false;
		}

		//This node is visible by default, but it might be hidden from the specified $actors.
		//It will remain visible as long as at least one of the actors can see it.
		if ( isset($this->is_visible_to_actor) && !empty($this->is_visible_to_actor) ) {
			$visibleToActor = null;

			//User-specific settings have precedence.
			if ( isset($userActor, $this->is_visible_to_actor[$userActor]) ) {
				return $this->is_visible_to_actor[$userActor];
			}

			foreach ($actors as $actor) {
				if ( !isset($this->is_visible_to_actor[$actor]) ) {
					//No custom settings for this actor -> default to visible.
					return true;
				}

				if ( $visibleToActor === null ) {
					$visibleToActor = $this->is_visible_to_actor[$actor];
				} else {
					$visibleToActor = $visibleToActor || $this->is_visible_to_actor[$actor];
				}
			}

			if ( $visibleToActor !== null ) {
				return $visibleToActor;
			}
		}
		return true;
	}

	/**
	 * Create a unique name for the title setting of this node.
	 * Intended for use with WPML functions like icl_register_string().
	 *
	 * @return string
	 */
	public function getWpmlName() {
		return $this->id . '[title]';
	}
}