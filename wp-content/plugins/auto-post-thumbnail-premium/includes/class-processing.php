<?php

namespace WBCR\APT;

use WAPT_Plugin;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class for generating in the background
 *
 * @author        Artem Prikhodko <webtemyk@yandex.ru>
 * @copyright (c) 2022, CreativeMotion
 * @version       1.0
 */
class Processing extends \WBCR\APT\ProcessingBase {

	/**
	 * @var string
	 */
	protected $prefix = 'wapt';

	/**
	 * @var string
	 */
	protected $action = 'generate_process';

	/**
	 * @var string
	 */
	protected $scope = 'generation';

	/**
	 * @var WAPT_Plugin
	 */
	public $plugin;

	/**
	 * Processing constructor.
	 *
	 * @param WAPT_Plugin $plugin
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
		parent::__construct();
	}

	/**
	 * @param array $post_ids
	 *
	 * @return int Count of pushed queue
	 */
	public function push_items( $post_ids = [] ) {
		/**
		 * Filters the number of posts to be processed according to the schedule.
		 *
		 * @param string $count Number of posts.
		 *
		 * @since 3.9.12
		 */
		$count = apply_filters( 'wysc/generation_schedule_count', 20 );

		if ( empty( $post_ids ) ) {
			$posts    = $this->plugin->apt->get_posts_query( [
				'has_thumb' => false,
				'count'     => $count,
			] );
			$post_ids = $posts->posts;
		}

		$this->plugin->logger->info( 'Push ' . count( $post_ids ) . ' items to queue' );
		foreach ( $post_ids as $post_id ) {
			$this->push_to_queue( $post_id );
		}

		return $this->count_queue();
	}

	/**
	 * Do task
	 *
	 * @param int $post_id
	 *
	 * @return bool
	 */
	protected function task( $post_id ) {
		$this->plugin->logger->info( 'Task - ' . $post_id );
		if ( $post_id ) {
			$this->plugin->logger->info( sprintf( 'Start generate image for post ID: %s', $post_id ) );

			$result = $this->plugin->apt->publish_post( $post_id );
			if ( $result->thumbnail_id ) {
				$this->plugin->logger->info( sprintf( 'Image generated for post ID: %s', $post_id ) );
			} else {
				$this->plugin->logger->info( sprintf( 'Image NOT generated for post ID: %s', $post_id ) );
			}
		}

		return false;
	}

	/**
	 * Fire before start handle the tasks
	 */
	protected function handle_before() {
		$this->plugin->logger->info( 'START generate process.' );
	}

	/**
	 * Fire after end handle the tasks
	 */
	protected function handle_after() {
		$this->plugin->logger->info( 'END generate process.' );
	}

	/**
	 * Fire after complete handle
	 */
	protected function handle_after_complete() {
		$this->plugin->updatePopulateOption( 'process_running', false );

	}
}
