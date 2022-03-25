<?php
/**
 *
 * Helper functions for this plugin.
 *
 * @since 1.0.0
 */

// File Security Check
defined( 'ABSPATH' ) || exit;

/**
 * Debug data.
 *
 * @param   array/string  $data
 *
 * @return  void
 */
function wpadtwpg_print( $data ) {
	if( ! WP_DEBUG ) return;
	echo '<pre>';
	if( is_array( $data ) || is_object( $data ) ) {
		print_r( $data );
	}
	else {
		echo $data;
	}
	echo '</pre>';
}

/**
 * Get settings option value.
 *
 * @param   string  $option
 * @param   string  $section
 * @param   string  $default
 *
 * @return  array/string
 */
function wpadtwpg_get_option( $option, $section, $default = '' ) {
	$options = get_option( $section );

	if ( isset( $options[$option] ) ) {
		return $options[$option];
	}

	return $default;
}

/**
 * Get other templates passing attributes and including the file.
 *
 * Search for the template and include the file.
 *
 * @see wpadtwpg_locate_template()
 *
 * @param 	string 	$template_name 	Template to load.
 * @param 	array 	$args Args 		(optional) Passed arguments for the template file.
 * @param 	string 	$template_path 	(optional) Path to templates.
 * @param 	string 	$default_path 	(optional) Default path to template files.
 *
 */
function wpadtwpg_get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
	$cache_key = sanitize_key( implode( '-', array( 'template', $template_name, $template_path, $default_path, WPADTWPG_VERSION ) ) );
	$template  = (string) wp_cache_get( $cache_key, WPADTWPG_NAME );

	if ( ! $template ) {
		$template = wpadtwpg_locate_template( $template_name, $template_path, $default_path );
		wp_cache_set( $cache_key, $template, WPADTWPG_NAME );
	}

	// Allow 3rd party plugin filter template file from their plugin.
	$filter_template = apply_filters( 'wpadtwpg_get_template', $template, $template_name, $args, $template_path, $default_path );

	if ( $filter_template !== $template ) {
		if ( ! file_exists( $filter_template ) ) {
			/* translators: %s template */
			wpadtwpg_doing_it_wrong( __FUNCTION__, sprintf( __( '%s does not exist.', 'wpappsdev-tagwallet-gateway' ), '<code>' . $template . '</code>' ), '1.0.0' );
			return;
		}
		$template = $filter_template;
	}

	$action_args = array(
		'template_name' => $template_name,
		'template_path' => $template_path,
		'located'       => $template,
		'args'          => $args,
	);

	if ( ! empty( $args ) && is_array( $args ) ) {
		if ( isset( $args['action_args'] ) ) {
			wpadtwpg_doing_it_wrong(
				__FUNCTION__,
				__( 'action_args should not be overwritten when calling wpadtwpg_get_template.', 'wpappsdev-tagwallet-gateway' ),
				'1.0.0'
			);
			unset( $args['action_args'] );
		}
		extract( $args ); // @codingStandardsIgnoreLine
	}

	do_action( 'wpadtwpg_before_template_part', $action_args['template_name'], $action_args['template_path'], $action_args['located'], $action_args['args'] );

	include $action_args['located'];

	do_action( 'wpadtwpg_after_template_part', $action_args['template_name'], $action_args['template_path'], $action_args['located'], $action_args['args'] );
}

/**
 * Like wpadtwpg_get_template, but returns the HTML instead of outputting.
 *
 * @see wpadtwpg_get_template
 *
 * @param 	string $template_name Template name.
 * @param 	array  $args          Arguments. (default: array).
 * @param 	string $template_path Template path. (default: '').
 * @param 	string $default_path  Default path. (default: '').
 *
 * @return string
 */
function wpadtwpg_get_template_html( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
	ob_start();
	wpadtwpg_get_template( $template_name, $args, $template_path, $default_path );
	return ob_get_clean();
}

/**
 * Locate a template and return the path for inclusion.
 *
 * Locate the called template.
 * Search Order:
 * 1. /themes/theme-name/plugins-name/$template_name
 * 2. /plugins/plugins-name/partials/templates/$template_name.
 *
 * @param 	string $template_name 	Template to load.
 * @param 	string $template_path 	(optional) Path to templates.
 * @param 	string $default_path 	(optional) Default path to template files.
 *
 * @return 	string $template 		Path to the template file.
 */
function wpadtwpg_locate_template( $template_name, $template_path = '', $default_path = '' ) {

	// Set variable to search in templates folder of theme.
	if ( ! $template_path ) :
		$template_path = get_template_directory() . '/' . WPADTWPG_NAME . '/';
	endif;
	// Set default plugin templates path.
	if ( ! $default_path ) :
		$default_path = WPADTWPG_DIR . 'views/';
	endif;
	// Search template file in theme folder.
	$template = locate_template( [ $template_path . $template_name, $template_name ] );
	// Get plugins template file.
	if ( ! $template ) :
		$template = $default_path . $template_name;
	endif;

	return apply_filters( 'wpadtwpg_locate_template', $template, $template_name, $template_path, $default_path );
}

/**
 * Wrapper for wpadtwpg_doing_it_wrong.
 *
 * @param string $function Function used.
 * @param string $message Message to log.
 * @param string $version Version the message was added in.
 */
function wpadtwpg_doing_it_wrong( $function, $message, $version ) {
	// @codingStandardsIgnoreStart
	$message .= ' Backtrace: ' . wp_debug_backtrace_summary();

	if ( is_ajax() ) {
		do_action( 'wpadtwpg_doing_it_wrong_run', $function, $message, $version );
		error_log( "{$function} was called incorrectly. {$message}. This message was added in version {$version}." );
	} else {
		_doing_it_wrong( $function, $message, $version );
	}
	// @codingStandardsIgnoreEnd
}