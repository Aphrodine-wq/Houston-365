<?php
/**
 * Single template for Player Profiles.
 *
 * Uses the standard 2/3 content + 1/3 ads layout so sidebar placements stay in the right column.
 *
 * @package Blocksy Child
 */

if (!have_posts()) {
	get_header();
	echo '<div class="ct-container"><p>' . esc_html__('Player profile not found.', 'hts-child') . '</p></div>';
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

$num = get_field('number');
$pos = get_field('position');
$height = get_field('height');
$weight = get_field('weight');
$home = get_field('hometown');
$hs = get_field('high_school');
$year = get_field('year_class');
$bio = get_field('bio');
$stars = get_field('recruiting_stars');
$high = hts_lines(get_field('highlights'));
$social = hts_lines(get_field('social_links'));
$sport = wp_get_post_terms(get_the_ID(), 'sport', ['fields' => 'names']);
$season = wp_get_post_terms(get_the_ID(), 'season', ['fields' => 'names']);
$thumbnail_id = get_post_thumbnail_id(get_the_ID());
$stars_value = is_numeric($stars) ? max(0, min(5, (int) $stars)) : null;
?>

<div
	class="<?php echo esc_attr(trim($container_class)); ?>"
	<?php echo $data_container_output; ?>
	<?php echo $spacing_attr; ?>>

	<?php do_action('blocksy:single:container:top'); ?>

	<div class="hts-single-layout player-profile-layout">
		<div class="hts-single-main">
			<article class="player-profile-single">
				<div class="hts-single-card recruit-single-card player-profile-single-card">
					<div class="recruit-header-grid">
						<div class="recruit-card recruit-hero-card player-profile-hero-card">
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
								<?php if ($num) : ?>
									<span class="num-badge">#<?php echo esc_html($num); ?></span>
								<?php endif; ?>
							</div>
							<div class="recruit-hero-meta">
								<h1 class="recruit-name"><?php the_title(); ?></h1>
								<?php
								if (function_exists('hts_render_post_meta_line')) {
									echo hts_render_post_meta_line(get_the_ID(), 'meta');
								}
								?>
								<div class="player-profile-meta">
									<?php if ($sport) : ?>
										<?php foreach ($sport as $s) echo '<span class="chip">' . esc_html($s) . '</span>'; ?>
									<?php endif; ?>
									<?php if ($season) : ?>
										<?php foreach ($season as $s) echo '<span class="chip">' . esc_html($s) . '</span>'; ?>
									<?php endif; ?>
								</div>
							</div>
						</div>

						<div class="recruit-card recruit-details-card player-profile-details-card">
							<div class="recruit-card-header">
								<h3><?php esc_html_e('Player Info', 'hts-child'); ?></h3>
							</div>
							<div class="recruit-card-body recruit-details-body">
								<div class="recruit-detail-grid">
									<?php if ($pos) : ?>
										<div class="recruit-detail">
											<span class="recruit-detail-label"><?php esc_html_e('Position', 'hts-child'); ?></span>
											<strong class="recruit-detail-value"><?php echo esc_html($pos); ?></strong>
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
											<strong class="recruit-detail-value"><?php echo esc_html($weight); ?> lbs</strong>
										</div>
									<?php endif; ?>
									<?php if ($year || $home || $hs) : ?>
										<div class="recruit-detail-trio recruit-info-row-secondary">
											<?php if ($year) : ?>
												<div class="recruit-detail">
													<span class="recruit-detail-label"><?php esc_html_e('Class', 'hts-child'); ?></span>
													<strong class="recruit-detail-value"><?php echo esc_html($year); ?></strong>
												</div>
											<?php endif; ?>
											<?php if ($home) : ?>
												<div class="recruit-detail">
													<span class="recruit-detail-label"><?php esc_html_e('Hometown', 'hts-child'); ?></span>
													<strong class="recruit-detail-value"><?php echo esc_html($home); ?></strong>
												</div>
											<?php endif; ?>
											<?php if ($hs) : ?>
												<div class="recruit-detail">
													<span class="recruit-detail-label"><?php esc_html_e('High School', 'hts-child'); ?></span>
													<strong class="recruit-detail-value"><?php echo esc_html($hs); ?></strong>
												</div>
											<?php endif; ?>
										</div>
									<?php endif; ?>
								</div>
							</div>
						</div>

						<div class="recruit-card recruit-ranking-card player-profile-ranking-card">
							<div class="recruit-card-header">
								<h3><?php esc_html_e('Player Ratings', 'hts-child'); ?></h3>
							</div>
							<div class="recruit-card-body recruit-ranking-body">
								<div class="recruit-ranking-header">
									<span class="recruit-ranking-label"><?php esc_html_e('Recruiting Stars', 'hts-child'); ?></span>
									<span class="recruit-ranking-score">
										<?php
										if ($stars_value !== null) {
											echo esc_html($stars_value) . esc_html__('/5', 'hts-child');
										} else {
											esc_html_e('N/A', 'hts-child');
										}
										?>
									</span>
								</div>
								<div class="recruit-ranking-icons" aria-hidden="true">
									<?php
									if (function_exists('hts_get_recruit_popcorn_icons')) {
										echo hts_get_recruit_popcorn_icons($stars_value);
									} else {
										$popcorn_icon = get_stylesheet_directory_uri() . '/assets/images/popcorn.png';
										for ($i = 1; $i <= 5; $i++) {
											$active_class = ($stars_value !== null && $i <= $stars_value) ? ' is-active' : ' is-muted';
											echo '<img class="recruit-popcorn' . esc_attr($active_class) . '" src="' . esc_url($popcorn_icon) . '" alt="" loading="lazy">';
										}
									}
									?>
								</div>
							</div>
						</div>
					</div>

					<div class="grid grid-2 player-profile-content-grid">
						<section class="stack">
							<?php if ($bio) : ?>
								<div class="stack card player-profile-bio-card">
									<h3 class="player-profile-section-title"><?php esc_html_e('Bio', 'hts-child'); ?></h3>
									<div class="stack player-profile-section-content"><?php echo wp_kses_post($bio); ?></div>
								</div>
							<?php endif; ?>

							<?php if (!empty($high)) : ?>
								<div class="stack card player-profile-highlights-card">
									<h3 class="player-profile-section-title"><?php esc_html_e('Highlights', 'hts-child'); ?></h3>
									<ul class="list player-profile-highlights-list">
										<?php foreach ($high as $line) {
											[$label, $url] = array_map('trim', array_pad(explode('|', $line, 2), 2, ''));
											if ($label && $url) {
												echo '<li><a href="' . esc_url($url) . '" target="_blank" rel="noopener">' . esc_html($label) . '</a></li>';
											}
										} ?>
									</ul>
								</div>
							<?php endif; ?>
						</section>

						<aside class="stack">
							<?php if (!empty($social)) : ?>
								<div class="card player-profile-social-card">
									<h3 class="player-profile-card-title"><?php esc_html_e('Follow', 'hts-child'); ?></h3>
									<ul class="list">
										<?php foreach ($social as $line) {
											[$platform, $url] = array_map('trim', array_pad(explode('|', $line, 2), 2, ''));
											if ($platform && $url) {
												echo '<li><a href="' . esc_url($url) . '" target="_blank" rel="noopener">' . esc_html($platform) . '</a></li>';
											}
										} ?>
									</ul>
								</div>
							<?php endif; ?>
							<div class="card player-profile-published-card">
								<div class="meta"><?php esc_html_e('Published', 'hts-child'); ?></div>
								<strong><?php echo get_the_date(); ?></strong>
							</div>
						</aside>
					</div>

					<section class="stack player-profile-content-body">
						<?php the_content(); ?>
					</section>
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
