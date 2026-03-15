<?php
/**
 * Single template for Recruit CPT.
 *
 * Uses the standard 2/3 content + 1/3 ads layout so sidebar placements stay in the right column.
 *
 * @package Blocksy Child
 */

if (!have_posts()) {
	get_header();
	echo '<div class="ct-container"><p>' . esc_html__('Recruit not found.', 'hts-child') . '</p></div>';
	get_footer();
	return;
}

get_header();
the_post();

// Mirror Blocksy container handling used by the default single template.
$page_structure = function_exists('blocksy_get_page_structure') ? blocksy_get_page_structure() : '';
$container_class = 'ct-container-full';
$data_container_output = '';

if ($page_structure === 'none' || (function_exists('blocksy_post_uses_vc') && blocksy_post_uses_vc())) {
	$container_class = 'ct-container';

	if ($page_structure === 'narrow') {
		$container_class = 'ct-container-narrow';
	}
} elseif ($page_structure) {
	$data_container_output = 'data-content="' . esc_attr($page_structure) . '"';
}

$spacing_attr = function_exists('blocksy_get_v_spacing') ? blocksy_get_v_spacing() : '';

// Collect all ACF fields up front to avoid repeated lookups.
$acf_fields     = function_exists('get_fields') ? (array) get_fields(get_the_ID()) : [];
$position       = isset($acf_fields['position']) ? trim((string) $acf_fields['position']) : '';
$height         = isset($acf_fields['height']) ? trim((string) $acf_fields['height']) : '';
$weight         = isset($acf_fields['weight']) ? trim((string) $acf_fields['weight']) : '';
$high_school    = isset($acf_fields['high_school']) ? trim((string) $acf_fields['high_school']) : '';
$city           = isset($acf_fields['hometown_city']) ? trim((string) $acf_fields['hometown_city']) : '';
$state          = isset($acf_fields['hometown_state']) ? trim((string) $acf_fields['hometown_state']) : '';
$class     = isset($acf_fields['class']) ? trim((string) $acf_fields['class']) : '';
$highlights_url = isset($acf_fields['highlights_url']) ? trim((string) $acf_fields['highlights_url']) : '';

$rank_raw   = $acf_fields['h365_rank'] ?? null;
$rank_value = is_numeric($rank_raw) ? max(0, min(5, (int) $rank_raw)) : null;

$in_state_raw = $acf_fields['in_state_rating'] ?? null;
if ($in_state_raw === null || $in_state_raw === '') {
	$in_state_raw = get_post_meta(get_the_ID(), 'in_state_rating', true);
}
$in_state_display = is_numeric($in_state_raw) ? trim((string) $in_state_raw) : '';

$offers  = isset($acf_fields['offers']) && is_array($acf_fields['offers']) ? $acf_fields['offers'] : [];
$gallery = isset($acf_fields['recruit_gallery']) && is_array($acf_fields['recruit_gallery']) ? $acf_fields['recruit_gallery'] : [];

$location      = trim($city . ($city && $state ? ', ' : '') . $state);
$has_offers    = !empty($offers);
$timeline_href = $has_offers ? '#recruit-offers' : '';
$thumbnail_id  = get_post_thumbnail_id(get_the_ID());

// Cache school lookups to avoid repeated ACF calls per offer row.
$school_cache = [];

?>

<div
	class="<?php echo esc_attr(trim($container_class)); ?>"
	<?php echo $data_container_output; ?>
	<?php echo $spacing_attr; ?>>

	<?php do_action('blocksy:single:container:top'); ?>

	<div class="hts-single-layout recruit-single-layout">
		<div class="hts-single-main">
			<article class="recruit-single">
				<div class="hts-single-card recruit-single-card">
					<div class="recruit-header-grid">
						<div class="recruit-card recruit-hero-card">
							<div class="recruit-headshot">
								<?php
								if ($thumbnail_id) {
									echo wp_get_attachment_image(
										$thumbnail_id,
										'full',
										false,
										[
											'class'   => 'recruit-headshot-img',
											'loading' => 'eager',
											'alt'     => esc_attr(get_the_title()),
										]
									);
								} else {
									echo '<div class="recruit-headshot-fallback" aria-hidden="true"></div>';
								}
								?>
							</div>
							<div class="recruit-hero-meta">
								<h1 class="recruit-name"><?php the_title(); ?></h1>
								<?php if ($timeline_href) : ?>
									<a class="recruit-timeline-link" href="<?php echo esc_url($timeline_href); ?>"><?php esc_html_e('Timeline', 'hts-child'); ?></a>
								<?php endif; ?>
							</div>
						</div>

						<div class="recruit-card recruit-details-card">
							<div class="recruit-card-header">
								<h3><?php esc_html_e('Recruit Info', 'hts-child'); ?></h3>
							</div>
							<div class="recruit-card-body recruit-details-body">
								<div class="recruit-detail-grid">
									<?php if ($position) : ?>
										<div class="recruit-detail">
											<span class="recruit-detail-label"><?php esc_html_e('Position', 'hts-child'); ?></span>
											<strong class="recruit-detail-value"><?php echo esc_html($position); ?></strong>
										</div>
									<?php endif; ?>
									<?php if ($height) : ?>
										<div class="recruit-detail">
											<span class="recruit-detail-label"><?php esc_html_e('Height', 'hts-child'); ?></span>
											<strong class="recruit-detail-value"><?php echo esc_html($height); ?></strong>
										</div>
									<?php endif; ?>
									<?php if ($weight) : ?>
										<div class="recruit-detail">
											<span class="recruit-detail-label"><?php esc_html_e('Weight', 'hts-child'); ?></span>
											<strong class="recruit-detail-value"><?php echo esc_html($weight); ?></strong>
										</div>
									<?php endif; ?>
									<?php if ($high_school || $location || $class) : ?>
										<div class="recruit-detail-trio recruit-info-row-secondary">
											<?php if ($high_school) : ?>
												<div class="recruit-detail">
													<span class="recruit-detail-label"><?php esc_html_e('High School', 'hts-child'); ?></span>
													<strong class="recruit-detail-value"><?php echo esc_html($high_school); ?></strong>
												</div>
											<?php endif; ?>
											<?php if ($location) : ?>
												<div class="recruit-detail">
													<span class="recruit-detail-label"><?php esc_html_e('Hometown', 'hts-child'); ?></span>
													<strong class="recruit-detail-value"><?php echo esc_html($location); ?></strong>
												</div>
											<?php endif; ?>
											<?php if ($class) : ?>
												<div class="recruit-detail">
													<span class="recruit-detail-label"><?php esc_html_e('Class', 'hts-child'); ?></span>
													<strong class="recruit-detail-value"><?php echo esc_html($class); ?></strong>
												</div>
											<?php endif; ?>
										</div>
									<?php endif; ?>
								</div>

								<?php if ($highlights_url) : ?>
									<a class="btn recruit-highlights-btn" href="<?php echo esc_url($highlights_url); ?>" target="_blank" rel="noopener">
										<?php esc_html_e('Watch Highlights', 'hts-child'); ?>
									</a>
								<?php endif; ?>
							</div>
						</div>

						<div class="recruit-card recruit-ranking-card">
							<div class="recruit-card-header">
								<h3><?php esc_html_e('Player Ratings', 'hts-child'); ?></h3>
							</div>
							<div class="recruit-card-body recruit-ranking-body">
								<div class="recruit-ranking-metric">
									<span class="recruit-ranking-metric-label"><?php esc_html_e('In-State Rating', 'hts-child'); ?></span>
									<span class="recruit-ranking-metric-value">
										<?php
										if ($in_state_display !== '') {
											echo esc_html($in_state_display);
										} else {
											esc_html_e('N/A', 'hts-child');
										}
										?>
									</span>
								</div>
								<div class="recruit-ranking-header">
									<span class="recruit-ranking-label"><?php esc_html_e('Houston365 Ranking', 'hts-child'); ?></span>
									<span class="recruit-ranking-score">
										<?php
										if ($rank_value !== null) {
											echo esc_html($rank_value) . esc_html__('/5', 'hts-child');
										} else {
											esc_html_e('N/A', 'hts-child');
										}
										?>
									</span>
								</div>
								<div class="recruit-ranking-icons" aria-hidden="true">
									<?php
									if (function_exists('hts_get_recruit_popcorn_icons')) {
										echo hts_get_recruit_popcorn_icons($rank_value);
									} else {
										$popcorn_icon = get_stylesheet_directory_uri() . '/assets/images/popcorn.png';
										for ($i = 1; $i <= 5; $i++) {
											$active_class = ($rank_value !== null && $i <= $rank_value) ? ' is-active' : ' is-muted';
											echo '<img class="recruit-popcorn' . esc_attr($active_class) . '" src="' . esc_url($popcorn_icon) . '" alt="" loading="lazy">';
										}
									}
									?>
								</div>
							</div>
						</div>
					</div>

					<section class="recruit-section recruit-offers" id="recruit-offers">
						<div class="recruit-section-heading">
							<h2><?php esc_html_e('Offers', 'hts-child'); ?></h2>
						</div>

						<div class="recruit-offers-table-wrap" role="region" aria-label="<?php esc_attr_e('Offers timeline', 'hts-child'); ?>">
							<table class="recruit-offers-table">
								<thead>
									<tr>
										<th scope="col"><?php esc_html_e('School', 'hts-child'); ?></th>
										<th scope="col"><?php esc_html_e('Offer', 'hts-child'); ?></th>
										<th scope="col"><?php esc_html_e('Interest', 'hts-child'); ?></th>
										<th scope="col"><?php esc_html_e('Visit Date', 'hts-child'); ?></th>
									</tr>
								</thead>
								<tbody>
									<?php if ($has_offers) : ?>
										<?php foreach ($offers as $row) :
											$school_obj = $row['school'] ?? null;
											$school_id  = 0;

											if ($school_obj instanceof WP_Post) {
												$school_id = $school_obj->ID;
											} elseif (is_numeric($school_obj)) {
												$school_id  = (int) $school_obj;
												$school_obj = get_post($school_id);
											}

											if ($school_id && !isset($school_cache[$school_id])) {
												$school_cache[$school_id] = [
													'logo'       => function_exists('get_field') ? get_field('school_logo', $school_id) : '',
													'short_name' => function_exists('get_field') ? get_field('school_short_name', $school_id) : '',
													'title'      => $school_obj instanceof WP_Post ? $school_obj->post_title : '',
												];
											}

											$school_name = $school_id && isset($school_cache[$school_id]['short_name']) && $school_cache[$school_id]['short_name']
												? $school_cache[$school_id]['short_name']
												: ($school_obj instanceof WP_Post ? $school_obj->post_title : '');

											$school_name = $school_name ? $school_name : __('TBD', 'hts-child');

											$logo_field = $school_id && isset($school_cache[$school_id]['logo']) ? $school_cache[$school_id]['logo'] : '';
											$logo_id    = 0;
											if (is_array($logo_field) && isset($logo_field['ID'])) {
												$logo_id = (int) $logo_field['ID'];
											} elseif (is_numeric($logo_field)) {
												$logo_id = (int) $logo_field;
											}

											$logo_html = $logo_id
												? wp_get_attachment_image($logo_id, 'thumbnail', false, ['class' => 'recruit-offer-logo', 'loading' => 'lazy'])
												: '';

											$offered_label = !empty($row['offered']) ? esc_html__('Yes', 'hts-child') : esc_html__('No', 'hts-child');
											$interest      = isset($row['interest']) ? trim((string) $row['interest']) : '';
											$visit_raw     = isset($row['visit_date']) ? trim((string) $row['visit_date']) : '';
											$visit_display = '';

											if ($visit_raw) {
												$visit_date = DateTime::createFromFormat('Ymd', $visit_raw) ?: DateTime::createFromFormat('Y-m-d', $visit_raw);
												if ($visit_date instanceof DateTime) {
													$visit_display = $visit_date->format('M j, Y');
												} elseif (strtotime($visit_raw)) {
													$visit_display = date_i18n(get_option('date_format'), strtotime($visit_raw));
												}
											}
											?>
											<tr>
												<td class="recruit-offer-school">
													<div class="recruit-offer-school-inner">
														<?php if ($logo_html) : ?>
															<span class="recruit-offer-logo-wrap"><?php echo wp_kses_post($logo_html); ?></span>
														<?php endif; ?>
														<span class="recruit-offer-name"><?php echo esc_html($school_name); ?></span>
													</div>
												</td>
												<td><?php echo esc_html($offered_label); ?></td>
												<td><?php echo $interest ? esc_html($interest) : '&mdash;'; ?></td>
												<td><?php echo $visit_display ? esc_html($visit_display) : '&mdash;'; ?></td>
											</tr>
										<?php endforeach; ?>
									<?php else : ?>
										<tr>
											<td colspan="4" class="recruit-offer-empty"><?php esc_html_e('No offers reported yet.', 'hts-child'); ?></td>
										</tr>
									<?php endif; ?>
								</tbody>
							</table>
						</div>
					</section>

					<section class="recruit-section recruit-report">
						<div class="recruit-section-heading">
							<h2><?php esc_html_e('Scouting Report', 'hts-child'); ?></h2>
						</div>
						<div class="recruit-report-body">
							<?php the_content(); ?>
						</div>
					</section>

					<?php if (!empty($gallery)) : ?>
						<section class="recruit-section recruit-gallery-section">
							<div class="recruit-section-heading">
								<h2><?php esc_html_e('Gallery', 'hts-child'); ?></h2>
							</div>
							<div class="recruit-gallery" aria-label="<?php esc_attr_e('Recruit gallery', 'hts-child'); ?>">
								<?php
								foreach ($gallery as $image) {
									$image_id = 0;
									if (is_array($image) && isset($image['ID'])) {
										$image_id = (int) $image['ID'];
									} elseif (is_numeric($image)) {
										$image_id = (int) $image;
									}

									if (!$image_id) {
										continue;
									}

									$image_html = wp_get_attachment_image(
										$image_id,
										'large',
										false,
										[
											'class'   => 'recruit-gallery-image',
											'loading' => 'lazy',
										]
									);

									if ($image_html) {
										echo '<div class="recruit-gallery-frame" role="group">' . wp_kses_post($image_html) . '</div>';
									}
								}
								?>
							</div>
						</section>
					<?php endif; ?>
				</div>
			</article>
		</div>

		<?php if (hts_should_render_single_ads_sidebar()) : ?>
			<aside
				class="hts-single-ads-sidebar"
				aria-label="<?php esc_attr_e('Single Post Ads Sidebar', 'hts-child'); ?>">
				<div class="hts-single-ads-inner" data-sticky="sidebar">
					<?php dynamic_sidebar('single-post-ads'); ?>
				</div>
			</aside>
		<?php endif; ?>
	</div>

	<?php do_action('blocksy:single:container:bottom'); ?>
</div>

<?php

if (function_exists('blocksy_display_page_elements')) {
	blocksy_display_page_elements('separated');
}

have_posts();
wp_reset_query();

get_footer();
