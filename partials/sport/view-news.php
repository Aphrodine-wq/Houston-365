<?php
/**
 * Sport View: News
 *
 * Renders the default sport news feed.
 *
 * Expected args (all optional):
 * - sport_term (WP_Term)
 * - sport_slug (string)
 * - sport_name (string)
 * - news_query (WP_Query) Custom query override
 */

$sport_term = $args['sport_term'] ?? get_queried_object();

$news_query = $args['news_query'] ?? null;
$global_wp_query = $GLOBALS['wp_query'] ?? null;

$query = ($news_query instanceof WP_Query) ? $news_query : $global_wp_query;
$uses_custom_query = $query instanceof WP_Query && $query !== $global_wp_query;

if (!$query instanceof WP_Query) {
    echo '<p>No stories available.</p>';
    return;
}
?>
<?php if ($query->have_posts()) : ?>
    <div class="sport-news-feed">
        <?php
        while ($query->have_posts()) :
            $query->the_post();
            ?>
            <article <?php post_class('sport-news-card'); ?>>
                <?php if (has_post_thumbnail()) : ?>
                    <div class="sport-news-card__thumb">
                        <?php the_post_thumbnail('large'); ?>
                    </div>
                <?php endif; ?>
                <div>
                    <h2 class="sport-news-card__title">
                        <a href="<?php the_permalink(); ?>">
                            <?php the_title(); ?>
                        </a>
                    </h2>
                    <?php echo hts_render_post_meta_line(null, 'sport-news-card__meta news-card-meta'); ?>
                    <div class="sport-news-card__excerpt">
                        <?php the_excerpt(); ?>
                    </div>
                </div>
            </article>
        <?php endwhile; ?>
    </div>

    <div class="sport-news-pagination">
        <?php
        if ($uses_custom_query) {
            $pagination_links = paginate_links([
                'total'   => (int) $query->max_num_pages,
                'current' => max(1, (int) $query->get('paged', 1)),
            ]);

            if ($pagination_links) {
                echo '<nav class="pagination">' . $pagination_links . '</nav>';
            }
            wp_reset_postdata();
        } else {
            the_posts_pagination([
                'mid_size'  => 2,
                'prev_text' => __('Previous', 'houston-news'),
                'next_text' => __('Next', 'houston-news'),
            ]);
        }
        ?>
    </div>
<?php else : ?>
    <div class="panel">
        <p>No news posts found for this sport.</p>
    </div>
<?php endif; ?>
