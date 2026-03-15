<?php
/**
 * Template Part: Main Content (Recent Posts Feed)
 * Displays latest posts and game recaps, excluding those shown in hero
 */

// Get IDs of posts already shown in hero to exclude them
$news_post_types = function_exists('hts_get_news_post_types')
    ? hts_get_news_post_types()
    : ['post', 'game-recaps'];
$hero_args = [
    'tag'                 => 'featured',
    'post_type'           => $news_post_types,
    'posts_per_page'      => 5,
    'fields'              => 'ids',
    'no_found_rows'       => true,
    'ignore_sticky_posts' => true,
];
$hero_query = new WP_Query($hero_args);
$exclude_ids = $hero_query->posts;
wp_reset_postdata();

// Also exclude all podcast posts
// Get podcast source configuration to determine how to exclude podcasts
$podcast_source = apply_filters('hts_podcast_source', [
    'type'  => 'category',
    'value' => 'podcast',
]);

if ($podcast_source['type'] === 'post_type') {
    // If podcasts are a custom post type, query and exclude them
    $podcast_args = [
        'post_type'           => $podcast_source['value'],
        'posts_per_page'      => -1,
        'fields'              => 'ids',
        'no_found_rows'       => true,
    ];
    $podcast_query = new WP_Query($podcast_args);
    $exclude_ids = array_merge($exclude_ids, $podcast_query->posts);
    wp_reset_postdata();
}

//Also exclude University Releases posts
$university_releases_args = [
    'post_type'            => 'post',
    'category_name'        => 'university-releases',
    'posts_per_page'       => -1,
    'fields'               => 'ids',
    'no_found_rows'        => true,
];
$university_releases_query = new WP_Query($university_releases_args);
$exclude_ids = array_merge($exclude_ids, $university_releases_query->posts);
wp_reset_postdata();

// Query recent posts
$posts_per_page = 18;

$recent_args = [
    'post_type'           => $news_post_types,
    'posts_per_page'      => $posts_per_page,
    'post__not_in'        => $exclude_ids,
    'orderby'             => 'date',
    'order'               => 'DESC',
    'no_found_rows'       => true,
    'ignore_sticky_posts' => true,
];

// If podcasts are a category, exclude them via tax_query
if ($podcast_source['type'] === 'category') {
    $podcast_category = get_category_by_slug($podcast_source['value']);
    if ($podcast_category) {
        $recent_args['tax_query'] = [
            [
                'taxonomy' => 'category',
                'field'    => 'term_id',
                'terms'    => $podcast_category->term_id,
                'operator' => 'NOT IN',
            ],
        ];
    }
}

$recent_query = new WP_Query($recent_args);
?>

<div class="hts-main-sections">
    <?php if ($recent_query->have_posts()) : ?>
        <section class="hts-section hts-section--recent recent-posts" aria-label="Recent News">
            
            <header class="recent-posts-header">
                <div class="recent-posts-heading">
                    <h2 class="recent-posts-title">Recent News</h2>
                    <p class="recent-posts-subtitle">Catch up on the latest news from the Houston365 crew.</p>
                </div>
            </header>

            <div class="recent-posts-grid hts-two-col-grid hts-recent-news-grid" role="list">
                <?php
                $visible_limit = 6;
                $index = 0;
                while ($recent_query->have_posts()) :
                    $recent_query->the_post();
                    $hidden_class = $index >= $visible_limit ? ' recent-news-item--hidden' : '';
                    ?>
                    <div class="recent-news-item<?php echo esc_attr($hidden_class); ?>" data-index="<?php echo esc_attr($index); ?>" role="listitem">
                        <?php get_template_part('template-parts/loops/recent-news-card'); ?>
                    </div>
                    <?php
                    $index++;
                endwhile; ?>
            </div><!-- .recent-posts-grid -->

            <?php if ($recent_query->post_count > $visible_limit) : ?>
                <div class="recent-posts-pagination">
                    <button 
                        class="load-more-btn" 
                        type="button"
                        data-batch-size="6"
                    >
                        <span class="load-more-text">More Recent News</span>
                        <span class="load-more-spinner" aria-hidden="true"></span>
                    </button>
                </div>
            <?php endif; ?>

        </section><!-- .recent-posts -->
    <?php endif; ?>

    <?php wp_reset_postdata(); ?>

    <?php get_template_part('template-parts/front-page/infeed-ad', null, ['slot' => 1]); ?>

    <?php get_template_part('template-parts/sections/section-recruiting'); ?>

    <?php get_template_part('template-parts/front-page/infeed-ad', null, ['slot' => 2]); ?>

    <?php get_template_part('template-parts/sections/section-basketball-row'); ?>
    <?php get_template_part('template-parts/sections/section-baseball-softball'); ?>

    <?php get_template_part('template-parts/front-page/infeed-ad', null, ['slot' => 3]); ?>

    <?php get_template_part('template-parts/sections/section-other-stories-row'); ?>
</div>

<?php
wp_reset_postdata();
