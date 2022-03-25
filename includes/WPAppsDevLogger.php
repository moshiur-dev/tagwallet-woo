<?php

namespace WPAppsDev\WCTWPG;

/**
 * Log all things!
 */
class WPAppsDevLogger {
	public static $logger;
	const LOG_FILENAME = 'wpadtwpg-log';

	/**
	 * Utilize WC logger class.
	 */
	public static function log($message, $start_time = null, $end_time = null) {
		if ( ! class_exists( 'WC_Logger' ) ) {
			return;
		}

		if ( ! WP_DEBUG ) {
			return;
		}

		if ( empty( self::$logger ) ) {
			self::$logger = wc_get_logger();
		}

		if ( ! is_null( $start_time ) ) {
			$formatted_start_time = date_i18n( get_option( 'date_format' ) . ' g:ia', $start_time );
			$end_time             = is_null( $end_time ) ? current_time( 'timestamp' ) : $end_time;
			$formatted_end_time   = date_i18n( get_option( 'date_format' ) . ' g:ia', $end_time );
			$elapsed_time         = round( abs( $end_time - $start_time ) / 60, 2 );
			$log_entry            = '====Start Log ' . $formatted_start_time . '====' . "\n" . $message . "\n";
			$log_entry .= '====End Log ' . $formatted_end_time . ' (' . $elapsed_time . ')====' . "\n\n";
		} else {
			$log_entry = '====Start Log====' . "\n" . $message . "\n" . '====End Log====' . "\n\n";
		}

		self::$logger->debug( $log_entry, [ 'source' => self::LOG_FILENAME ] );
	}
}
