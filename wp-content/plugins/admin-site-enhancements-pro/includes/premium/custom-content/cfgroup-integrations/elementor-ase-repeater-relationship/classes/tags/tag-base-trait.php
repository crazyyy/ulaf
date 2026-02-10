<?php

namespace ElementorAseRepeaterRelationship\DynamicTags;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once ELEMENTOR_ASE_REPEATER_RELATIONSHIP_PATH . 'classes/data/repeater-data-trait.php';

trait TagBaseTrait {
    use \ElementorAseRepeaterRelationship\Data\RepeaterDataTrait;
    protected $configurator;
    protected $controls;

    public function __construct( $data = [] ) {
        parent::__construct( $data );
        $this->configurator = \ElementorAseRepeaterRelationship\Configurator::instance();
        $this->controls = \ElementorAseRepeaterRelationship\Controls\DynamicTagControls::instance();
    }
}

abstract class AseRepeaterTagBase extends \Elementor\Core\DynamicTags\Data_Tag {
    use TagBaseTrait;
}
