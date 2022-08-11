<?php
/**
 * Breakpoints Background.
 *
 * @package ghostkit
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'WP_Background_Process' ) ) {
    require_once ghostkit()->plugin_path . 'vendor/deliciousbrains/wp-background-processing/wp-background-processing.php';
}

/**
 * GhostKit_Breakpoints_Background class
 */
class GhostKit_Breakpoints_Background extends WP_Background_Process {
    /**
     * Name of Cron Action Task.
     *
     * @var string
     */
    protected $action = 'ghostkit_run_breakpoints_processing';

    /**
     * Cron Interval.
     *
     * @var integer
     */
    protected $cron_interval = 2;

    /**
     * Cron Queue Lock Time.
     *
     * @var integer
     */
    protected $queue_lock_time = 25;

    /**
     * Task
     *
     * Override this method to perform any actions required on each
     * queue item. Return the modified item for further processing
     * in the next pass through. Or, return false to remove the
     * item from the queue.
     *
     * @param mixed $item Queue item to iterate over.
     *
     * @return mixed
     */
    protected function task( $item ) {
        new GhostKit_Scss_Compiler( $item );
        return false;
    }

    /**
     * Schedule event
     */
    protected function schedule_event() {
        if ( ! wp_next_scheduled( $this->cron_hook_identifier ) ) {
            wp_schedule_event( time() + ( 60 * $this->cron_interval ), $this->cron_interval_identifier, $this->cron_hook_identifier );
        }
    }

    /**
     * Dispatch
     *
     * @access public
     * @return void
     */
    public function dispatch() {
        // Schedule the cron healthcheck.
        $this->schedule_event();
    }

    /**
     * Schedule cron healthcheck
     *
     * @access public
     *
     * @param mixed $schedules Schedules.
     *
     * @return mixed
     */
    public function schedule_cron_healthcheck( $schedules ) {
        // phpcs:ignore
        $interval = apply_filters( $this->identifier . '_cron_interval', 5 );

        if ( property_exists( $this, 'cron_interval' ) ) {
            // phpcs:ignore
            $interval = apply_filters( $this->identifier . '_cron_interval', $this->cron_interval );
        }

        // Adds every 5 minutes to the existing schedules.
        $schedules[ $this->identifier . '_cron_interval' ] = array(
            'interval' => (int) MINUTE_IN_SECONDS * $interval,
            // translators: %d - Interval.
            'display'  => sprintf( __( 'Every %d Minutes', 'ghostkit' ), $interval ),
        );

        return $schedules;
    }

}
