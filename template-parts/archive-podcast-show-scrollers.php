<?php
/**
 * Podcast show scrollers for archive views.
 *
 * Expected $args or query vars:
 * - show_tags           Array of tag slugs (display order). Query var: hts_podcast_show_tags.
 * - posts_per_page      Int posts per show. Query var: hts_podcast_posts_per_show. Default 10.
 * - category_term|slug  Category to scope queries. Query var: hts_podcast_category_term. Default 'podcast'.
 *
 * Reuse by calling:
 * set_query_var('hts_podcast_show_tags', ['show-a', 'show-b']);
 * get_template_part('template-parts/archive', 'podcast-show-scrollers');
 */

$show_tags = $args['show_tags'] ?? get_query_var('hts_podcast_show_tags', []);
$show_tags = is_array($show_tags) ? $show_tags : [];
$show_tags = array_values(array_filter($show_tags));

$posts_per_page = isset($args['posts_per_page'])
	? absint($args['posts_per_page'])
	: absint(get_query_var('hts_podcast_posts_per_show', 10));
$posts_per_page = $posts_per_page > 0 ? $posts_per_page : 10;

$category_term = $args['category_term'] ?? get_query_var('hts_podcast_category_term');
$category_slug = '';

if ($category_term instanceof WP_Term) {
	$category_slug = $category_term->slug;
} elseif (!empty($args['category_slug'])) {
	$category_slug = sanitize_title($args['category_slug']);
}

$category_slug = $category_slug ?: 'podcast';
$has_any_posts = false;
?>

<div class="hts-podcast-show-stack">
	<?php foreach ($show_tags as $raw_tag_slug) :
		$tag_slug = sanitize_title($raw_tag_slug);
		if (!$tag_slug) {
			continue;
		}

		$tag_term = get_term_by('slug', $tag_slug, 'post_tag');
		if (!$tag_term) {
			// Fallback to name lookup if a slug wasn't provided.
			$tag_term = get_term_by('name', $raw_tag_slug, 'post_tag');
		}

		$tag_label    = $tag_term instanceof WP_Term ? $tag_term->name : ucwords(str_replace('-', ' ', $raw_tag_slug));
		$heading_id   = 'hts-podcast-show-' . sanitize_html_class($tag_slug);
		$scroller_id  = 'hts-podcast-scroller-' . sanitize_html_class($tag_slug);

		$query_args = [
			'post_type'           => 'post',
			'posts_per_page'      => $posts_per_page,
			'orderby'             => 'date',
			'order'               => 'DESC',
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
			'post_status'         => 'publish',
			'tax_query'           => [
				[
					'taxonomy' => 'category',
					'field'    => 'slug',
					'terms'    => [$category_slug],
				],
				[
					'taxonomy' => 'post_tag',
					'field'    => 'slug',
					'terms'    => [$tag_slug],
				],
			],
		];

		/**
		 * Filter the podcast show query arguments.
		 *
		 * @param array  $query_args   WP_Query args.
		 * @param string $tag_slug     Current show tag slug.
		 * @param string $category_slug Category slug for podcasts.
		 */
		$query_args = apply_filters('hts_podcast_show_query_args', $query_args, $tag_slug, $category_slug);

		$show_query = new WP_Query($query_args);

		if ($show_query->have_posts()) :
			$has_any_posts = true;
			?>
			<section class="hts-podcast-show-section" aria-labelledby="<?php echo esc_attr($heading_id); ?>">
				<div class="hts-podcast-show-section__header">
					<h2 class="hts-podcast-show-title" id="<?php echo esc_attr($heading_id); ?>"><?php echo esc_html($tag_label); ?></h2>
					<div class="hts-podcast-show-controls">
						<button
							type="button"
							class="hts-podcast-show-nav hts-podcast-show-nav--prev"
							data-scroll-target="<?php echo esc_attr($scroller_id); ?>"
							aria-label="<?php echo esc_attr(sprintf(__('Scroll %s left', 'hts-child'), $tag_label)); ?>"
						>&lsaquo;</button>
						<button
							type="button"
							class="hts-podcast-show-nav hts-podcast-show-nav--next"
							data-scroll-target="<?php echo esc_attr($scroller_id); ?>"
							aria-label="<?php echo esc_attr(sprintf(__('Scroll %s right', 'hts-child'), $tag_label)); ?>"
						>&rsaquo;</button>
					</div>
				</div>

				<div
					class="hts-podcast-show-scroller"
					id="<?php echo esc_attr($scroller_id); ?>"
					data-hts-podcast-scroller="true"
					role="list"
				>
					<?php while ($show_query->have_posts()) : $show_query->the_post(); ?>
						<div class="hts-podcast-show-card">
							<?php get_template_part('template-parts/loops/recent-news-card'); ?>
						</div>
					<?php endwhile; ?>
				</div>
			</section>
			<?php
		endif;

		wp_reset_postdata();
	endforeach; ?>
</div>

<?php if (!$has_any_posts) : ?>
	<div class="hts-podcast-archive-fallback stack">
		<?php if (have_posts()) : ?>
			<h2 class="hts-podcast-show-title"><?php esc_html_e('Latest podcast posts', 'hts-child'); ?></h2>
			<div class="recent-posts-grid hts-two-col-grid hts-recent-news-grid" role="list">
				<?php while (have_posts()) : the_post(); ?>
					<?php get_template_part('template-parts/loops/recent-news-card'); ?>
				<?php endwhile; ?>
			</div>

			<div class="recent-posts-pagination">
				<?php the_posts_pagination(); ?>
			</div>
		<?php else : ?>
			<p class="hts-podcast-archive-empty"><?php esc_html_e('No podcast episodes found.', 'hts-child'); ?></p>
		<?php endif; ?>
	</div>
<?php endif; ?>

<?php wp_reset_postdata(); ?>
