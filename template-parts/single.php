<?php
/*
 * Single post template with 2/3 content and 1/3 ad sidebar.
 *
 * Mirrors Blocksy's core single template but wraps the content and ad widget
 * area in a consistent two-column layout so all single posts share the same
 * structure.
 *
 * @package Blocksy Child
 */

if (have_posts()) {
	the_post();
}

if (
	function_exists('blc_get_content_block_that_matches')
	&&
	blc_get_content_block_that_matches([
		'template_type' => 'single',
		'template_subtype' => 'canvas'
	])
) {
	echo blc_render_content_block(
		blc_get_content_block_that_matches([
			'template_type' => 'single',
			'template_subtype' => 'canvas'
		])
	);
	have_posts();
	wp_reset_query();
	return;
}

/**
 * Note to code reviewers: This line doesn't need to be escaped.
 * Function blocksy_output_hero_section() used here escapes the value properly.
 */
if (apply_filters('blocksy:single:has-default-hero', true)) {
	echo blocksy_output_hero_section([
		'type' => 'type-2'
	]);
}

$page_structure = blocksy_get_page_structure();

$container_class = 'ct-container-full';
$data_container_output = '';

if ($page_structure === 'none' || blocksy_post_uses_vc()) {
	$container_class = 'ct-container';

	if ($page_structure === 'narrow') {
		$container_class = 'ct-container-narrow';
	}
} else {
	$data_container_output = 'data-content="' . $page_structure . '"';
}

$sidebar_attr = '';

?>

	<div
		class="<?php echo esc_attr(trim($container_class)); ?>"
		<?php echo wp_kses_post($sidebar_attr); ?>
		<?php echo $data_container_output; ?>
		<?php echo blocksy_get_v_spacing(); ?>>

		<?php do_action('blocksy:single:container:top'); ?>

		<div class="hts-single-layout">
			<div class="hts-single-main">
				<div class="hts-single-card">
					<?php
						/**
						 * Note to code reviewers: This line doesn't need to be escaped.
						 * Function blocksy_single_content() used here escapes the value properly.
						 */
						echo blocksy_single_content();
					?>
				</div>
			</div>

			<?php if (hts_should_render_single_ads_sidebar()) : ?>
				<aside
					class="hts-single-ads-sidebar"
					aria-label="<?php esc_attr_e('Single Post Ads Sidebar', 'hts-child'); ?>">
					<div class="hts-single-ads-inner" data-sticky="sidebar">
						<?php
						// Widgets placed here are the standard single-post ad placements.
						dynamic_sidebar('single-post-ads');
						?>
					</div>
				</aside>
			<?php endif; ?>
		</div>

		<?php do_action('blocksy:single:container:bottom'); ?>
	</div>

<?php

blocksy_display_page_elements('separated');

have_posts();
wp_reset_query();
