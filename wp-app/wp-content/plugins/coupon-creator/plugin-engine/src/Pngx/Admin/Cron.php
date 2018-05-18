<?php
//If Direct Access Kill the Script
if ( $_SERVER['SCRIPT_FILENAME'] == __FILE__ ) {
	die( 'Access denied.' );
}


/*
* Abstract Cron Class for Processing Individual Posts, Pages, or Custom Post Types
*
*/


abstract class Pngx__Admin__Cron {

	/**
	 * @var string
	 */
	protected $scheduled_task = 'pngx_cron_job';

	/**
	 * Number of items to be processed in a single batch.
	 *
	 * @var int
	 */
	protected $batch_size = 100;

	/**
	 * Number of items in the current batch processed so far.
	 *
	 * @var int
	 */
	protected $processed = 0;

	/**
	 * @var int
	 */
	protected $current_id = 0;


	/**
	 * Construct
	 */
	abstract public function __construct();

	/*public function __construct() {

		add_action( 'cctor_activate', array( $this, 'register_scheduled_task' ) );

		add_action( $this->scheduled_task, array( $this, 'process_queue' ), 20, 0 );

		add_action( 'cctor_deactivate', array( $this, 'clear_scheduled_task' ) );
	}*/

	/**
	 * Runs upon plugin update, registering the task to batch process recurring expiration
	 */
	public function register_scheduled_task() {

		if ( ! wp_next_scheduled( $this->scheduled_task ) ) {
			/**
			 * Filter the interval at which to process recurring expiration queues.
			 *
			 * By default ever 30mins is specified, however other intervals of
			 * "hourly", "twicedaily" and "daily" could be substituted.
			 *
			 * @see wp_schedule_event() or 'cron_schedules'
			 */
			$interval = apply_filters( $this->scheduled_task . '_interval', 'every_30mins' );

			wp_schedule_event( time(), $interval, $this->scheduled_task );
		}
	}

	/**
	 * Expected to fire upon plugin deactivation.
	 */
	public function clear_scheduled_task() {
		wp_clear_scheduled_hook( $this->scheduled_task );
	}
	/**
	 * Processes the next item in que
	 *
	 * @param int $batch_size
	 */
	public function process_queue( $batch_size = null ) {
		if ( null === $batch_size ) {
			/**
			 * Filter the amount of post objects to process
			 *
			 * @param int $default_batch_size
			 */
			$this->batch_size = (int) apply_filters( $this->scheduled_task . '_batch_size', 100 );
		} else {
			$this->batch_size = (int) $batch_size;
		}

		while ( $this->next_waiting_item() ) {
			if ( ! $this->do_processing() ) {
				break;
			}
		}
	}

	/**
	 * Obtains the post ID of the next item
	 *
	 */
	abstract protected function next_waiting_item();


	/**
	 * Process the Current Item
	 *
	 */
	abstract protected function do_processing();


	/**
	 * Determines if the batch job is complete.
	 *
	 * @return bool
	 */
	protected function batch_complete() {
		return ( $this->processed >= $this->batch_size );
	}
}