<?php
/**
 * Template Part: Sidebar (Podcasts Module)
 * Displays latest podcast episodes in the right rail
 */

// Get podcast source via filter (default: category slug 'podcast')
$podcast_source = apply_filters('hts_podcast_source', [
    'type'  => 'category',  // 'category' or 'post_type'
    'value' => 'podcast',   // category slug or post type name
]);

// Build query args based on source type
if ($podcast_source['type'] === 'post_type') {
    // Use custom post type
    $podcast_args = [
        'post_type'           => $podcast_source['value'],
        'posts_per_page'      => 5,
        'orderby'             => 'date',
        'order'               => 'DESC',
        'no_found_rows'       => true,
        'ignore_sticky_posts' => true,
    ];
    $archive_link = get_post_type_archive_link($podcast_source['value']);
} else {
    // Use category (default)
    $podcast_args = [
        'post_type'           => 'post',
        'category_name'       => $podcast_source['value'],
        'posts_per_page'      => 5,
        'orderby'             => 'date',
        'order'               => 'DESC',
        'no_found_rows'       => true,
        'ignore_sticky_posts' => true,
    ];
    
    // Get category archive link
    $category = get_category_by_slug($podcast_source['value']);
    $archive_link = $category ? get_category_link($category->term_id) : '';
}

$podcast_query = new WP_Query($podcast_args);

if ($podcast_query->have_posts()) : ?>

    <section class="sidebar-module podcasts-module" aria-labelledby="podcasts-heading">
        
        <header class="module-header">
            <h2 class="module-title" id="podcasts-heading">Shows</h2>
        </header>

        <div class="podcasts-list" role="list">
            <?php
            while ($podcast_query->have_posts()) : $podcast_query->the_post();
                
                $post_id = get_the_ID();
                
                // Get runtime from custom field (e.g., 'podcast_duration', 'runtime', etc.)
                $runtime = get_post_meta($post_id, 'podcast_duration', true);
                if (empty($runtime)) {
                    $runtime = get_post_meta($post_id, 'runtime', true);
                }
                
                // Allow filtering of runtime field
                $runtime = apply_filters('hts_podcast_runtime', $runtime, $post_id);
            ?>
            
            <article class="podcast-item" role="listitem">
                <a href="<?php the_permalink(); ?>" class="podcast-link" aria-label="<?php echo esc_attr('Listen to: ' . get_the_title()); ?>">
                    
                    <div class="podcast-thumb">
                        <?php if (has_post_thumbnail()) : ?>
                            <?php
                            the_post_thumbnail('thumbnail', [
                                'class'    => 'podcast-image',
                                'loading'  => 'lazy',
                                'decoding' => 'async',
                                'alt'      => get_the_title(),
                            ]);
                            ?>
                        <?php endif; ?>
                        
                        <div class="podcast-play-icon" aria-hidden="true">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="12" cy="12" r="10" fill="currentColor" opacity="0.9"/>
                                <path d="M10 8.5L15 12L10 15.5V8.5Z" fill="white"/>
                            </svg>
                        </div>
                    </div>
                    
                    <div class="podcast-content">
                        <h3 class="podcast-title"><?php the_title(); ?></h3>
                        
                        <?php if ($runtime) : ?>
                            <span class="podcast-runtime"><?php echo esc_html($runtime); ?></span>
                        <?php endif; ?>
                    </div>
                    
                </a>
            </article>
            
            <?php endwhile; ?>
        </div><!-- .podcasts-list -->

        <?php if ($archive_link) : ?>
            <footer class="module-footer">
                <a href="<?php echo esc_url($archive_link); ?>" class="module-view-all">
                    View all shows
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M6 3L11 8L6 13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
            </footer>
        <?php endif; ?>

    </section><!-- .podcasts-module -->

<?php
endif;
wp_reset_postdata();

// Writer's Room posts configuration (sidebar module)
$opinion_candidates = apply_filters('hts_opinion_slugs', ['writers-room', 'opinion', 'opinions', 'columns', 'column']);
$opinion_slug       = '';

foreach ($opinion_candidates as $candidate_slug) {
    if (get_category_by_slug($candidate_slug)) {
        $opinion_slug = $candidate_slug;
        break;
    }
}

if (empty($opinion_slug)) {
    $opinion_slug = $opinion_candidates[0];
}

$news_post_types = function_exists('hts_get_news_post_types')
    ? hts_get_news_post_types()
    : ['post', 'game-recaps'];

$opinion_args = [
    'post_type'           => $news_post_types,
    'posts_per_page'      => 5,
    'orderby'             => 'date',
    'order'               => 'DESC',
    'ignore_sticky_posts' => true,
    'no_found_rows'       => true,
    'tax_query'           => [
        [
            'taxonomy' => 'category',
            'field'    => 'slug',
            'terms'    => $opinion_slug,
        ],
    ],
];

$opinion_query = new WP_Query($opinion_args);

if ($opinion_query->have_posts()) : ?>

    <section class="sidebar-module opinion-module" aria-labelledby="writers-room-heading">
        <header class="module-header">
            <h2 class="module-title" id="writers-room-heading"><?php esc_html_e('Writers\' Room', 'hts-child'); ?></h2>
        </header>

        <div class="opinion-list" role="list">
            <?php
            while ($opinion_query->have_posts()) :
                $opinion_query->the_post();
                $author_name = get_the_author();
                $author_id   = get_post_field('post_author', get_the_ID());
                $author_avatar = $author_id
                    ? get_avatar(
                        $author_id,
                        56,
                        '',
                        $author_name,
                        [
                            'class'    => 'opinion-avatar',
                            'alt'      => $author_name,
                            'loading'  => 'lazy',
                            'decoding' => 'async',
                        ]
                    )
                    : '';
            ?>
                <article class="opinion-item" role="listitem">
                    <a href="<?php the_permalink(); ?>" class="opinion-link" aria-label="<?php echo esc_attr(sprintf(__('Read: %s', 'hts-child'), get_the_title())); ?>">
                        <?php if (!empty($author_avatar)) : ?>
                            <div class="opinion-thumb" aria-hidden="true">
                                <?php echo $author_avatar; ?>
                            </div>
                        <?php endif; ?>
                        <div class="opinion-body">
                            <h3 class="opinion-title"><?php the_title(); ?></h3>
                            <div class="opinion-meta">
                                <?php if (!empty($author_name)) : ?>
                                    <span class="opinion-author"><?php echo esc_html($author_name); ?></span>
                                    <span class="opinion-separator" aria-hidden="true">&bull;</span>
                                <?php endif; ?>
                                <time datetime="<?php echo esc_attr(get_the_date('c')); ?>" class="opinion-date">
                                    <?php echo get_the_date(); ?>
                                </time>
                            </div>
                        </div>
                    </a>
                </article>
            <?php endwhile; ?>
        </div>
    </section>

<?php
endif;
wp_reset_postdata();

// University Releases configuration
$release_source = apply_filters('hts_university_releases_source', [
    'type'  => 'category',
    'value' => 'university-releases',
]);

$release_count = apply_filters('hts_university_releases_count', 3);

if ($release_source['type'] === 'post_type') {
    $release_args = [
        'post_type'           => $release_source['value'],
        'posts_per_page'      => $release_count,
        'orderby'             => 'date',
        'order'               => 'DESC',
        'no_found_rows'       => true,
        'ignore_sticky_posts' => true,
    ];
    $release_archive = get_post_type_archive_link($release_source['value']);
} else {
    $release_args = [
        'post_type'           => 'post',
        'category_name'       => $release_source['value'],
        'posts_per_page'      => $release_count,
        'orderby'             => 'date',
        'order'               => 'DESC',
        'no_found_rows'       => true,
        'ignore_sticky_posts' => true,
    ];
    $release_category = get_category_by_slug($release_source['value']);
    $release_archive = $release_category ? get_category_link($release_category->term_id) : '';
}

$release_query = new WP_Query($release_args);

if ($release_query->have_posts()) : ?>

    <section class="sidebar-module releases-module" aria-labelledby="releases-heading">
        <header class="module-header">
            <h2 class="module-title" id="releases-heading">University Releases</h2>
        </header>

        <div class="releases-list" role="list">
            <?php
            while ($release_query->have_posts()) : $release_query->the_post();
            ?>
                <article class="release-item" role="listitem">
                    <a href="<?php the_permalink(); ?>" class="release-link" aria-label="<?php echo esc_attr('Read: ' . get_the_title()); ?>">
                        <h3 class="release-title"><?php the_title(); ?></h3>
                        <div class="release-meta">
                            <time datetime="<?php echo esc_attr(get_the_date('c')); ?>" class="release-date">
                                <?php echo get_the_date(); ?>
                            </time>
                        </div>
                    </a>
                </article>
            <?php endwhile; ?>
        </div><!-- .releases-list -->

        <?php if ($release_archive) : ?>
            <footer class="module-footer">
                <a href="<?php echo esc_url($release_archive); ?>" class="module-view-all">
                    View all releases
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M6 3L11 8L6 13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
            </footer>
        <?php endif; ?>
    </section><!-- .releases-module -->

<?php
endif;
wp_reset_postdata();

/**
 * Column ads: stack slots 1-5 in the sidebar.
 */
do_action('hts_front_page_column_ad');

if (!has_action('hts_front_page_column_ad')) {
    get_template_part('template-parts/front-page/column-ad', null, ['slot' => 1]);
}

get_template_part('template-parts/front-page/column-ad', null, ['slot' => 2]);

// Local Businesses module (CPT)
$local_business_post_type = apply_filters(
    'hts_local_business_post_type',
    post_type_exists('local-businesses')
        ? 'local-businesses'
        : (post_type_exists('local-business') ? 'local-business' : 'local-businesses')
);

$local_business_args = [
    'post_type'           => $local_business_post_type,
    'posts_per_page'      => 5,
    'orderby'             => 'date',
    'order'               => 'DESC',
    'no_found_rows'       => true,
    'ignore_sticky_posts' => true,
];

$local_business_query = new WP_Query($local_business_args);
?>

<section class="sidebar-module local-businesses-module" aria-labelledby="local-businesses-heading">
    <header class="module-header">
        <h2 class="module-title" id="local-businesses-heading"><?php esc_html_e('Local Businesses', 'hts-child'); ?></h2>
    </header>

    <?php if ($local_business_query->have_posts()) : ?>
        <div class="local-businesses-list" role="list">
            <?php
            while ($local_business_query->have_posts()) :
                $local_business_query->the_post();
                $raw_excerpt = get_the_excerpt();
                $short_excerpt = $raw_excerpt ? wp_trim_words(wp_strip_all_tags($raw_excerpt), 14) : '';
            ?>
                <article class="local-business-item" role="listitem">
                    <a href="<?php the_permalink(); ?>" class="local-business-link" aria-label="<?php echo esc_attr(sprintf(__('Read: %s', 'hts-child'), get_the_title())); ?>">
                        <h3 class="local-business-title"><?php the_title(); ?></h3>
                        <div class="local-business-meta">
                            <time datetime="<?php echo esc_attr(get_the_date('c')); ?>" class="local-business-date">
                                <?php echo get_the_date(); ?>
                            </time>
                        </div>
                        <?php if (!empty($short_excerpt)) : ?>
                            <p class="local-business-excerpt"><?php echo esc_html($short_excerpt); ?></p>
                        <?php endif; ?>
                    </a>
                </article>
            <?php endwhile; ?>
        </div>
    <?php else : ?>
        <p class="local-businesses-empty"><?php esc_html_e('No Local Business profiles yet.', 'hts-child'); ?></p>
    <?php endif; ?>
</section>

<?php
wp_reset_postdata();

get_template_part('template-parts/front-page/column-ad', null, ['slot' => 3]);
get_template_part('template-parts/front-page/column-ad', null, ['slot' => 4]);
get_template_part('template-parts/front-page/column-ad', null, ['slot' => 5]);
?>
