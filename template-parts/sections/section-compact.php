<?php
/**
 * Generic compact grid section for category-based stories.
 *
 * Expected $args:
 * - section_title (string) Section heading text.
 * - section_slug (string) Category slug to query.
 * - section_class (string) Additional wrapper classes.
 * - posts_per_page (int) Number of posts to load.
 * - grid_class (string) Extra grid class modifiers.
 * - show_view_more (bool) Whether to show a footer link.
 * - view_more_label (string) Optional label for the footer link.
 * - view_more_link (string) Optional explicit URL for "View more".
 * - sport_slug (string) Optional sport taxonomy slug to filter by.
 */

$defaults = [
    'section_title'   => '',
    'section_slug'    => '',
    'section_class'   => '',
    'posts_per_page'  => 4,
    'grid_class'      => '',
    'show_view_more'  => false,
    'show_header_link'=> true,
    'view_more_label' => __('View More', 'hts-child'),
    'view_more_link'  => '',
    'show_excerpt'    => false,
    'excerpt_length'  => 18,
    'sport_slug'      => '',
    'hide_media'      => false,
];

$config = wp_parse_args($args, $defaults);

if (empty($config['section_slug']) && empty($config['sport_slug'])) {
    return;
}

$posts_per_page = absint($config['posts_per_page']);
$posts_per_page = $posts_per_page > 0 ? $posts_per_page : $defaults['posts_per_page'];

$news_post_types = function_exists('hts_get_news_post_types')
    ? hts_get_news_post_types()
    : ['post', 'game-recaps'];

$query_args = [
    'post_type'           => $news_post_types,
    'posts_per_page'      => $posts_per_page,
    'orderby'             => 'date',
    'order'               => 'DESC',
    'ignore_sticky_posts' => true,
    'no_found_rows'       => true,
];

$sport_slug = $config['sport_slug'];

if (!empty($sport_slug)) {
    $query_args = hts_with_sport_tax_query($query_args, $sport_slug);
} elseif (!empty($config['section_slug'])) {
    $query_args['tax_query'] = [
        [
            'taxonomy' => 'category',
            'field'    => 'slug',
            'terms'    => $config['section_slug'],
        ],
    ];
}

$section_query = new WP_Query($query_args);

if (!$section_query->have_posts()) {
    wp_reset_postdata();
    return;
}

$category      = !empty($config['section_slug']) ? get_category_by_slug($config['section_slug']) : null;
$sport_term    = !empty($sport_slug) ? get_term_by('slug', $sport_slug, 'sport') : null;
$archive_link  = $config['view_more_link'] ?: ($sport_term ? get_term_link($sport_term) : ($category ? get_category_link($category->term_id) : ''));
$view_more     = $config['show_view_more'] && $archive_link;
$wrapper_class = trim('hts-section ' . $config['section_class']);
$grid_class    = trim('hts-compact-grid ' . $config['grid_class']);
?>

<section class="<?php echo esc_attr($wrapper_class); ?>">
    <header class="hts-section__header">
        <h2 class="hts-section__title"><?php echo esc_html($config['section_title']); ?></h2>
        <?php if ($view_more && !empty($config['show_header_link'])) : ?>
            <a class="hts-section__more" href="<?php echo esc_url($archive_link); ?>">
                <?php echo esc_html($config['view_more_label']); ?>
            </a>
        <?php endif; ?>
    </header>

    <div class="<?php echo esc_attr($grid_class); ?>" role="list">
        <?php
        while ($section_query->have_posts()) :
            $section_query->the_post();

            $excerpt_text = '';
            if (!empty($config['show_excerpt'])) {
                $excerpt_source = has_excerpt()
                    ? get_the_excerpt()
                    : get_the_content(null, false, get_the_ID());
                $excerpt_clean = wp_strip_all_tags($excerpt_source);
                $excerpt_text  = $excerpt_clean ? wp_trim_words($excerpt_clean, absint($config['excerpt_length'])) : '';
            }

            get_template_part(
                'template-parts/loops/compact-story-card',
                null,
                [
                    'show_excerpt' => !empty($config['show_excerpt']),
                    'excerpt_text' => $excerpt_text,
                    'hide_media'   => !empty($config['hide_media']),
                ]
            );
        endwhile;
        ?>
    </div>

    <?php if ($view_more) : ?>
        <footer class="hts-section__footer">
            <a class="hts-section__cta" href="<?php echo esc_url($archive_link); ?>">
                <?php echo esc_html($config['view_more_label']); ?>
            </a>
        </footer>
    <?php endif; ?>
</section>

<?php
wp_reset_postdata();
