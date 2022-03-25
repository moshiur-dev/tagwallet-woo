<?php

namespace WPAppsDev\WCTWPG;

class Assets {
	/**
	 * The constructor.
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'register_all_scripts' ], 10 );

		if ( is_admin() ) {
			add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_scripts' ] );
		} else {
			add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_front_scripts' ] );
		}
	}

	/**
	 * Register all scripts and styles.
	 */
	public function register_all_scripts() {
		$styles  = $this->get_styles();
		$scripts = $this->get_scripts();

		$this->register_styles( $styles );
		$this->register_scripts( $scripts );

		do_action( 'wpadtwpg_register_scripts' );
	}

	/**
	 * Get registered styles.
	 *
	 * @return array
	 */
	public function get_styles() {
		$prefix         = self::get_prefix();

		// All CSS file list.
		$styles = [
			'wpadtwpg-admin' => [
				'src'     => WPADTWPG_ASSETS . 'css/wpadtwpg-admin.css',
				'deps'    => [],
				'version' => filemtime( WPADTWPG_DIR . '/assets/css/wpadtwpg-admin.css' ),
			],
			'wpadtwpg-public' => [
				'src'     => WPADTWPG_ASSETS . 'css/wpadtwpg-public.css',
				'deps'    => [],
				'version' => filemtime( WPADTWPG_DIR . '/assets/css/wpadtwpg-public.css' ),
			],
			'wpadtwpg-waitMe' => [
				'src'     => WPADTWPG_ASSETS . 'css/waitMe.min.css',
				'deps'    => [],
			],
		];

		return $styles;
	}

	/**
	 * Get all registered scripts.
	 *
	 * @return array
	 */
	public function get_scripts() {
		$prefix         = self::get_prefix();

		// All JS file list.
		$scripts = [
			// Register scripts
			'wpadtwpg-admin' => [
				'src'       => WPADTWPG_ASSETS . 'js/wpadtwpg-admin.js',
				'deps'      => [ 'jquery' ],
				'version'   => filemtime( WPADTWPG_DIR . 'assets/js/wpadtwpg-admin.js' ),
			],
			'wpadtwpg-public' => [
				'src'       => WPADTWPG_ASSETS . 'js/wpadtwpg-public.js',
				'deps'      => [ 'jquery' ],
				'version'   => filemtime( WPADTWPG_DIR . 'assets/js/wpadtwpg-public.js' ),
			],
			'wpadtwpg-waitMe' => [
				'src'       => WPADTWPG_ASSETS . 'js/waitMe.min.js',
				'deps'      => [ 'jquery' ],
			],
		];

		return $scripts;
	}

	/**
	 * Get file prefix.
	 *
	 * @return string
	 */
	public static function get_prefix() {
		$prefix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '';

		return $prefix;
	}

	/**
	 * Register scripts.
	 *
	 * @param array $scripts
	 *
	 * @return void
	 */
	public function register_scripts($scripts) {
		foreach ( $scripts as $handle => $script ) {
			$deps      = isset( $script['deps'] ) ? $script['deps'] : false;
			$in_footer = isset( $script['in_footer'] ) ? $script['in_footer'] : true;
			$version   = isset( $script['version'] ) ? $script['version'] : WPADTWPG_VERSION;

			wp_register_script( $handle, $script['src'], $deps, $version, $in_footer );
		}
	}

	/**
	 * Register styles.
	 *
	 * @param array $styles
	 *
	 * @return void
	 */
	public function register_styles($styles) {
		foreach ( $styles as $handle => $style ) {
			$deps    = isset( $style['deps'] ) ? $style['deps'] : false;
			$version = isset( $style['version'] ) ? $style['version'] : WPADTWPG_VERSION;

			wp_register_style( $handle, $style['src'], $deps, $version );
		}
	}

	/**
	 * Enqueue admin scripts.
	 */
	public function enqueue_admin_scripts($hook) {
		$default_script = [
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'admin_security' ),
		];

		$localize_data = apply_filters( 'wpadtwpg_localized_args', $default_script );

		// Enqueue scripts
		wp_enqueue_script( 'wpadtwpg-admin' );
		wp_localize_script( 'wpadtwpg-admin', 'wpadtwpg_admin', $localize_data );

		// Enqueue Styles
		wp_enqueue_style( 'wpadtwpg-admin' );

		do_action( 'wpadtwpg_enqueue_admin_scripts' );
	}

	/**
	 * Enqueue front-end scripts.
	 */
	public function enqueue_front_scripts() {
		global $post;

		$default_script = [
			'ajaxurl'       => admin_url( 'admin-ajax.php' ),
			'nonce'         => wp_create_nonce( 'public_security' ),
			'is_user_login' => is_user_logged_in(),
		];

		// Front end localize data
		$localize_data = apply_filters( 'wpadtwpg_localized_args', $default_script );

		// Enqueue scripts
		wp_enqueue_script( 'wpadtwpg-waitMe' );
		wp_enqueue_script( 'wpadtwpg-public' );
		wp_localize_script( 'wpadtwpg-public', 'wpadtwpg_public', $localize_data );

		// Enqueue Styles
		wp_enqueue_style( 'wpadtwpg-public' );
		wp_enqueue_style( 'wpadtwpg-waitMe' );

		do_action( 'wpadtwpg_enqueue_scripts' );
	}
}
