<?php
/**
 * Compact two-column news card
 *
 * Expected args:
 * - badge_label (string)
 * - badge_class (string)
 * - excerpt_text (string)
 */

$badge_label  = $args['badge_label'] ?? '';
$badge_class  = $args['badge_class'] ?? '';
$excerpt_text = $args['excerpt_text'] ?? '';

if ($badge_label === '' && function_exists('hts_get_post_card_label_data')) {
    $label_data = hts_get_post_card_label_data(get_the_ID());
    $badge_label = $label_data['label'] ?? '';
    $badge_class = $label_data['class'] ?? $badge_class;
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

            <?php echo hts_render_post_meta_line(null, 'news-card-meta'); ?>

            <?php if ($excerpt_text) : ?>
                <p class="news-card-excerpt"><?php echo esc_html($excerpt_text); ?></p>
            <?php endif; ?>
        </div>
    </a>
</article>
