<?php
/**
 * Shared recent news card renderer.
 *
 * Reuses the two-column card markup/classes from the front page Recent News
 * section and keeps badge/excerpt logic centralized.
 *
 * Optional $args:
 * - excerpt_length (int) Number of words for the excerpt. Default: 14.
 */

$config = wp_parse_args($args ?? [], [
    'excerpt_length' => 14,
]);

$post_id = get_the_ID();
$label_data = function_exists('hts_get_post_card_label_data')
    ? hts_get_post_card_label_data($post_id)
    : [
        'label' => null,
        'class' => 'badge-default',
    ];
$badge_label = $label_data['label'] ?? null;
$badge_class = $label_data['class'] ?? 'badge-default';

$excerpt_source = has_excerpt()
    ? get_the_excerpt()
    : get_the_content(null, false, $post_id);
$excerpt_clean = wp_strip_all_tags($excerpt_source);
$excerpt_text  = $excerpt_clean ? wp_trim_words($excerpt_clean, absint($config['excerpt_length'])) : '';

// Build meta inline (spans only) to avoid nested anchors and keep one link wrapper.
$meta_parts = [];
$sport_term = hts_get_primary_sport_term($post_id);

if ($sport_term && !is_wp_error($sport_term)) {
    $meta_parts[] = sprintf(
        '<span class="news-card-author news-card-sport">%s</span>',
        esc_html($sport_term->name)
    );
}

$timestamp = get_post_time('U', true, $post_id);
if ($timestamp) {
    $meta_parts[] = sprintf(
        '<time class="news-card-date" datetime="%s">%s</time>',
        esc_attr(get_post_time('c', true, $post_id)),
        esc_html(human_time_diff($timestamp, current_time('timestamp')) . ' ' . __('ago', 'hts-child'))
    );
}

$author_name = get_the_author_meta('display_name', get_post_field('post_author', $post_id));
if ($author_name) {
    $meta_parts[] = sprintf(
        '<span class="news-card-author">%s %s</span>',
        esc_html__('By', 'hts-child'),
        esc_html($author_name)
    );
}

$meta_markup = '';
if (!empty($meta_parts)) {
    $separator = '<span class="news-card-separator" aria-hidden="true">&bull;</span>';
    $meta_markup = sprintf(
        '<div class="news-card-meta">%s</div>',
        implode($separator, $meta_parts)
    );
}
?>

<article class="news-card" role="listitem">
    <a href="<?php the_permalink(); ?>" class="news-card-link" aria-label="<?php echo esc_attr('Read: ' . get_the_title()); ?>">
        <div class="news-card-media">
            <?php if (has_post_thumbnail()) : ?>
                <?php
                the_post_thumbnail('medium_large', [
                    'class'    => 'news-card-image',
                    'loading'  => 'lazy',
                    'decoding' => 'async',
                    'alt'      => get_the_title(),
                ]);
                ?>
            <?php endif; ?>
        </div>

        <div class="news-card-body">
            <?php if ($badge_label) : ?>
                <span class="news-card-badge <?php echo esc_attr($badge_class); ?>"><?php echo esc_html($badge_label); ?></span>
            <?php endif; ?>

            <h3 class="news-card-title"><?php the_title(); ?></h3>

            <?php echo $meta_markup; ?>

            <?php if ($excerpt_text) : ?>
                <p class="news-card-excerpt"><?php echo esc_html($excerpt_text); ?></p>
            <?php endif; ?>
        </div>
    </a>
</article>
