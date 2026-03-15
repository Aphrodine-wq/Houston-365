<?php
/**
 * Sport taxonomy archive.
 *
 * Renders a sport landing page with shared news cards and a schedule sidebar.
 */

$sport_term = get_queried_object();
$hts_view   = sanitize_key((string) get_query_var('hts_view'));
$is_roster_view   = ($hts_view === 'roster') || (bool) get_query_var('roster');
$is_schedule_view = ($hts_view === 'schedule') || (bool) get_query_var('schedule');
$is_recruiting_view = ($hts_view === 'recruiting') || (bool) get_query_var('recruiting');

if ($is_roster_view) {
	add_filter('blocksy:hero:enabled', '__return_false');
	add_filter('blocksy:page-title:enabled', '__return_false');
	add_filter('get_the_archive_title', '__return_empty_string', 10, 1);
	add_filter('get_search_form', '__return_empty_string', 10, 1);
}

get_header();

if (!$sport_term instanceof WP_Term) {
	get_footer();
	return;
}

$sport_slug = $sport_term->slug;
$sport_name = $sport_term->name;

$news_post_types = function_exists('hts_get_news_post_types')
	? hts_get_news_post_types()
	: ['post', 'game-recaps'];
$post_types = apply_filters('hts_sport_archive_post_types', $news_post_types, $sport_term);
$post_types = array_values(array_unique(array_filter(array_map('sanitize_key', (array) $post_types))));
$post_types = array_values(array_intersect($news_post_types, $post_types));

if (empty($post_types)) {
	$post_types = $news_post_types;
}

$posts_per_page = absint(apply_filters('hts_sport_archive_posts_per_page', get_option('posts_per_page'), $sport_term));
$posts_per_page = $posts_per_page > 0 ? $posts_per_page : get_option('posts_per_page');

$paged = max(
	1,
	(int) get_query_var('paged'),
	(int) get_query_var('page')
);

$sport_query  = null;
$roster_query = null;
$roster_season_context = null;
$schedule_season_context = null;

if ($is_roster_view) {
	$roster_season_context = function_exists('hts_get_sport_season_context')
		? hts_get_sport_season_context($sport_term, ['player-profiles'])
		: [
			'taxonomy' => '',
			'terms'    => [],
			'selected' => '',
		];

	$roster_query_args = [
		'post_type'      => 'player-profiles',
		'posts_per_page' => 12,
		'paged'          => $paged,
		'orderby'        => [
			'meta_value_num' => 'ASC',
			'title'          => 'ASC',
		],
		'meta_key'       => 'number',
		'order'          => 'ASC',
		'no_found_rows'  => false,
	];

	if ($sport_term instanceof WP_Term) {
		$roster_query_args['tax_query'] = [
			[
				'taxonomy' => 'sport',
				'field'    => 'term_id',
				'terms'    => $sport_term->term_id,
			],
		];
	}

	if (
		!empty($roster_season_context['taxonomy'])
		&& !empty($roster_season_context['selected'])
	) {
		$roster_query_args['tax_query'] = isset($roster_query_args['tax_query'])
			? (array) $roster_query_args['tax_query']
			: [];
		$roster_query_args['tax_query'][] = [
			'taxonomy' => $roster_season_context['taxonomy'],
			'field'    => 'slug',
			'terms'    => $roster_season_context['selected'],
		];
	}

	$roster_query_args = apply_filters('hts_sport_roster_query_args', $roster_query_args, $sport_term);
	$roster_query      = new WP_Query($roster_query_args);
} elseif ($is_schedule_view) {
	$schedule_post_type = post_type_exists('game') ? 'game' : 'game-recaps';
	$schedule_season_context = function_exists('hts_get_sport_season_context')
		? hts_get_sport_season_context($sport_term, [$schedule_post_type])
		: [
			'taxonomy' => '',
			'terms'    => [],
			'selected' => '',
		];
} elseif (!$is_schedule_view && !$is_recruiting_view) {
	$query_args = [
		'post_type'           => $post_types,
		'posts_per_page'      => $posts_per_page,
		'paged'               => $paged,
		'orderby'             => 'date',
		'order'               => 'DESC',
		'ignore_sticky_posts' => true,
		'no_found_rows'       => false,
		'tax_query'           => [
			[
				'taxonomy' => 'sport',
				'field'    => 'term_id',
				'terms'    => $sport_term->term_id,
			],
		],
	];

	// Allow upstream filters to modify the archive query (e.g., exclude IDs shown elsewhere).
	$query_args  = apply_filters('hts_sport_archive_query_args', $query_args, $sport_term);
	$sport_query = new WP_Query($query_args);
}
?>

<?php
$subnav_controls = '';
if ($is_roster_view && !empty($roster_season_context) && !empty($roster_season_context['terms'])) {
	ob_start();
	?>
	<form class="hts-subnav-season" method="get" action="<?php echo esc_url(remove_query_arg(['hts_season', 'season'], add_query_arg([]))); ?>">
		<select class="hts-subnav-select" name="hts_season" aria-label="<?php esc_attr_e('Season', 'hts-child'); ?>" onchange="this.form.submit()">
			<?php foreach ($roster_season_context['terms'] as $season_term) : ?>
				<?php if ($season_term instanceof WP_Term) : ?>
					<option value="<?php echo esc_attr($season_term->slug); ?>" <?php selected($roster_season_context['selected'] ?? '', $season_term->slug); ?>>
						<?php echo esc_html($season_term->name); ?>
					</option>
				<?php endif; ?>
			<?php endforeach; ?>
		</select>
	</form>
	<?php
	$subnav_controls = ob_get_clean();
}
?>

<div class="sport-page">
	<div class="container sport-container">
		<header class="stack sport-archive__header">
			<h1 class="title sport-archive__title"><?php echo esc_html($sport_name); ?></h1>

			<?php
			if (function_exists('hts_render_sport_subnav')) {
				$subnav_args = $subnav_controls ? ['controls_html' => $subnav_controls] : [];
				hts_render_sport_subnav($sport_term->slug, $subnav_args);
			}
			?>
		</header>

		<div class="sport-layout">
			<main class="sport-main">
				<?php if ($is_roster_view) : ?>
					<?php
					get_template_part(
						'partials/sport/view',
						'roster',
						[
							'sport_term'   => $sport_term,
							'sport_slug'   => $sport_slug,
							'roster_query' => $roster_query,
							'season_taxonomy' => $roster_season_context['taxonomy'] ?? '',
							'season_terms'    => $roster_season_context['terms'] ?? [],
							'selected_season' => $roster_season_context['selected'] ?? '',
						]
					);
					?>
				<?php elseif ($is_schedule_view) : ?>
					<?php
					get_template_part(
						'partials/sport/view',
						'schedule',
						[
							'sport_term' => $sport_term,
							'sport_slug' => $sport_slug,
							'season_taxonomy' => $schedule_season_context['taxonomy'] ?? '',
							'season_terms'    => $schedule_season_context['terms'] ?? [],
							'selected_season' => $schedule_season_context['selected'] ?? '',
						]
					);
					?>
				<?php elseif ($is_recruiting_view) : ?>
					<?php
					get_template_part(
						'partials/sport/view',
						'recruiting',
						[
							'sport_term' => $sport_term,
							'sport_slug' => $sport_slug,
						]
					);
					?>
				<?php elseif ($sport_query && $sport_query->have_posts()) : ?>

					<div class="recent-posts">
						<div class="recent-posts-grid hts-two-col-grid hts-recent-news-grid" role="list">
							<?php
							while ($sport_query->have_posts()) :
								$sport_query->the_post();
								get_template_part('template-parts/loops/recent-news-card');
							endwhile;
							?>
						</div>

						<?php if ($sport_query->max_num_pages > 1) : ?>
							<div class="recent-posts-pagination">
								<?php
								echo paginate_links([
									'total'     => $sport_query->max_num_pages,
									'current'   => $paged,
									'prev_text' => __('Previous', 'hts-child'),
									'next_text' => __('Next', 'hts-child'),
								]);
								?>
							</div>
						<?php endif; ?>
					</div>
				<?php else : ?>
					<p><?php esc_html_e('No stories found for this sport yet.', 'hts-child'); ?></p>
				<?php endif; ?>
			</main>

			<aside class="sport-sidebar">
				<?php
				if (function_exists('hts_render_sport_schedule_sidebar')) {
					hts_render_sport_schedule_sidebar($sport_term);
				}
				?>
			</aside>
		</div>
	</div>
</div>

<?php
wp_reset_postdata();
get_footer();
