<?php
/**
 * Uninstall cleanup.
 *
 * @package CountdownTimerBlock
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_option( 'countdown-timer-block_options' );
