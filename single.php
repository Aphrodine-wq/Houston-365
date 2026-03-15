<?php
/**
 * Single post template loader for Blocksy Child.
 *
 * Ensures our child template part is used for all single posts.
 */

get_header();

if (
	! function_exists('elementor_theme_do_location')
	||
	! elementor_theme_do_location('single')
) {
	get_template_part('template-parts/single');
}

get_footer();
