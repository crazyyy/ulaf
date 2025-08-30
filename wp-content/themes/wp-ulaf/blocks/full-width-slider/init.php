<?php
  /**
   *  Author: Vitalii A | @knaipa
   *  URL: https://github.com/crazyyy/wp-framework
   *  Initialize Gutenberg Blocks customizations and initiations
   */

  /**
   * Register a custom Gutenberg block type: Block Example.
   * This function registers a custom Gutenberg block type called "Block Example".
   * It sets various attributes and options for the block, such as name, title, description,
   * category, icon, keywords, render template, enqueue styles and scripts, and block supports.
   * The block is registered using the acf_register_block_type() function from the Advanced Custom Fields (ACF) plugin.
   */
  function register_block_full_width_slider() {
    acf_register_block_type(array(
      'name' => 'full-width-slider',
      'title' => 'Full Width Slider',
      'active' => true,
      'description' => '',
      'category' => 'common',
      'icon' => 'dashicons-images-alt',
      'keywords' => array(),
      'post_types' => array(),
      'mode' => 'preview',
      'align' => '',
      'align_text' => 'center',
      'align_content' => 'center',
      'render_template' => 'blocks/full-width-slider/block.php',
      'render_callback' => '',
      'enqueue_style' => 'blocks/full-width-slider/styles.css',
      'enqueue_script' => 'blocks/full-width-slider/scripts.css',
      'enqueue_assets' => '',
      'supports' => array(
        'anchor' => false,
        'align' => true,
        'align_text' => false,
        'align_content' => false,
        'full_height' => false,
        'mode' => true,
        'multiple' => true,
        'example' => array(),
        'jsx' => false,
      ),
    ));
  }
  add_action('acf/init', 'register_block_full_width_slider');
