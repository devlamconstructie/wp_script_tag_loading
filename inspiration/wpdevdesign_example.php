<?php

add_action( 'wp_head', 'wpdd_load_scripts' );
function wpdd_load_scripts1() {
	wp_print_script_tag(
		array(
			'id' => 'swiffy-slider',
			'src' => esc_url( 'https://cdn.jsdelivr.net/npm/swiffy-slider@1.4.1/dist/js/swiffy-slider.min.js' ),
			'defer' => true
		)
	);

	wp_print_script_tag(
		array(
			'id' => 'isotope',
			'src' => esc_url( 'https://unpkg.com/isotope-layout@3/dist/isotope.pkgd.min.js' ),
			'defer' => true
		)
	);
}

/** internal */

add_action( 'wp_head', 'wpdd_load_scripts' );
function wpdd_load_scripts2() {
	wp_print_script_tag(
		array(
			'id' => 'swiffy-slider',
			'src' => esc_url( plugin_dir_url(__FILE__) . 'assets/js/swiffy-slider.min.js' ),
			'defer' => true
		)
	);
}

/** conditional */
add_action( 'wp_head', 'wpdd_load_scripts' );
function wpdd_load_scripts3() {
	if ( is_singular( 'post' ) || is_page( 'sample-page' ) ) { // load on single posts and a static Page having the slug of "sample-page"
		wp_print_script_tag(
			array(
				'id' => 'swiffy-slider',
				'src' => esc_url( 'https://cdn.jsdelivr.net/npm/swiffy-slider@1.4.1/dist/js/swiffy-slider.min.js' ),
				'defer' => true
			)
		);
	} // end of if
}

add_action( 'wp_head', 'wpdd_load_scripts' );
function wpdd_load_scripts() {
	// if this is NOT the site's static homepage, abort.
	if ( ! is_front_page() ) {
		return;
	}
	
	wp_print_script_tag(
		array(
			'id' => 'swiffy-slider',
			'src' => esc_url( 'https://cdn.jsdelivr.net/npm/swiffy-slider@1.4.1/dist/js/swiffy-slider.min.js' ),
			'defer' => true
		)
	);
}