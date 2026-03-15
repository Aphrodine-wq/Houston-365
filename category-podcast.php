<?php
/**
 * Podcast category archive.
 *
 * Renders the podcast archive as stacked horizontal scrollers grouped by show tag.
 * Edit $podcast_show_tags to add/remove/reorder shows (tag slugs).
 */

get_header();

// --- Explicit list of podcast show tag slugs (display order matters).
$podcast_show_tags = [
	'red-and-blue-crew',
	'locked-on-houston',
	'the-players-lounge',
	'the-sec-morning-show',
	'the-coach-yo-show',
	'the-coach-q-show',
	'the-houston-365-podcast',
];

/*
// Optional: auto-discover all tags used by podcast posts.
// Uncomment to populate $podcast_show_tags dynamically.
$podcast_ids       = get_posts([
	'category_name'  => 'podcast',
	'fields'         => 'ids',
	'posts_per_page' => -1,
	'no_found_rows'  => true,
]);
$podcast_show_tags = [];
foreach ($podcast_ids as $podcast_id) {
	$tags = wp_get_post_terms($podcast_id, 'post_tag', ['fields' => 'slugs']);
	if (!empty($tags) && !is_wp_error($tags)) {
		$podcast_show_tags = array_merge($podcast_show_tags, $tags);
	}
}
$podcast_show_tags = array_values(array_unique(array_filter($podcast_show_tags)));
*/

// Pass configuration to the reusable scroller template part.
$podcast_category = get_category_by_slug('podcast');
set_query_var('hts_podcast_show_tags', $podcast_show_tags);
// Adjust this value to change how many episodes are shown per show row.
set_query_var('hts_podcast_posts_per_show', 10);
set_query_var('hts_podcast_category_term', $podcast_category);
?>

<div class="podcast-archive">
	<div class="container stack podcast-archive__container">
		<header class="stack podcast-archive__header">
			<h1 class="title podcast-archive__title"><?php single_cat_title(); ?></h1>
			<?php if (category_description()) : ?>
				<div class="podcast-archive__description"><?php echo wp_kses_post(category_description()); ?></div>
			<?php endif; ?>
		</header>

		<?php get_template_part('template-parts/archive', 'podcast-show-scrollers'); ?>
	</div>
</div>

<?php get_footer(); ?>
