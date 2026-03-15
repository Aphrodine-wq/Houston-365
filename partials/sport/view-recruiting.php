<?php
/**
 * Sport View: Recruiting
 *
 * Displays recruit posts filtered by the current sport term.
 *
 * Expected args (optional):
 * - sport_term (WP_Term)
 * - sport_slug (string)
 * - recruiting_query (WP_Query) Custom query override
 */

$sport_term = $args['sport_term'] ?? get_queried_object();
$sport_slug = $args['sport_slug'] ?? ($sport_term instanceof WP_Term ? $sport_term->slug : '');

$recruiting_query = $args['recruiting_query'] ?? null;

if (!$recruiting_query instanceof WP_Query) {
    $posts_per_page = absint(apply_filters('hts_sport_recruiting_posts_per_page', 12, $sport_term));
    $posts_per_page = $posts_per_page > 0 ? $posts_per_page : 12;

    $paged = max(
        1,
        (int) get_query_var('paged'),
        (int) get_query_var('page')
    );

    $query_args = [
        'post_type'           => 'recruit',
        'posts_per_page'      => $posts_per_page,
        'paged'               => $paged,
        'orderby'             => 'title',
        'order'               => 'ASC',
        'ignore_sticky_posts' => true,
        'no_found_rows'       => false,
        'hts_recruiting_sort' => true,
    ];

    if ($sport_slug) {
        $query_args = hts_with_sport_tax_query($query_args, $sport_slug);
    }

    $query_args = apply_filters('hts_sport_recruiting_query_args', $query_args, $sport_term);
    $recruiting_query = new WP_Query($query_args);
}

$paged = max(1, (int) $recruiting_query->get('paged', 1));
?>

<?php if ($recruiting_query->have_posts()) : ?>
    <div class="sport-roster">
        <?php
        while ($recruiting_query->have_posts()) :
            $recruiting_query->the_post();
            get_template_part('template-parts/content', 'player-roster-card', ['post_id' => get_the_ID()]);
        endwhile;
        ?>
    </div>

    <?php if ($recruiting_query->max_num_pages > 1) : ?>
        <div class="recent-posts-pagination">
            <?php
            $pagination_args = [
                'total'     => $recruiting_query->max_num_pages,
                'current'   => $paged,
                'prev_text' => __('Previous', 'hts-child'),
                'next_text' => __('Next', 'hts-child'),
            ];

            if ($sport_term instanceof WP_Term) {
                $base_url = get_term_link($sport_term);
                if (!is_wp_error($base_url) && $base_url) {
                    $pagination_args['base'] = trailingslashit($base_url) . 'Recruiting/%_%';
                    $pagination_args['format'] = 'page/%#%/';
                }
            }

            echo paginate_links($pagination_args);
            ?>
        </div>
    <?php endif; ?>
<?php else : ?>
    <div class="panel">
        <p><?php esc_html_e('No recruits found for this sport.', 'hts-child'); ?></p>
    </div>
<?php endif; ?>

<?php wp_reset_postdata(); ?>
