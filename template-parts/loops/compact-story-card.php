<?php
/**
 * Compact Story Card
 *
 * Displays a small thumbnail, title, date, and optional excerpt for grid-based sections.
 *
 * @param array $args {
 *   @type bool   $show_excerpt Whether to show excerpt text.
 *   @type string $excerpt_text Optional precomputed excerpt.
 *   @type bool   $hide_media   Whether to hide thumbnail/media block.
 * }
 */

$placeholder   = hts_get_placeholder_image('card');
$show_excerpt  = !empty($args['show_excerpt']);
$excerpt_text  = $args['excerpt_text'] ?? '';
$hide_media    = !empty($args['hide_media']);
$card_classes  = ['hts-compact-card'];

if ($hide_media) {
    $card_classes[] = 'hts-compact-card--no-media';
}
?>

<article class="<?php echo esc_attr(implode(' ', $card_classes)); ?>" role="listitem">
    <a href="<?php the_permalink(); ?>" class="hts-compact-card__link" aria-label="<?php echo esc_attr(sprintf(__('Read: %s', 'hts-child'), get_the_title())); ?>">
        <?php if (!$hide_media) : ?>
            <div class="hts-compact-card__media">
                <?php if (has_post_thumbnail()) : ?>
                    <?php the_post_thumbnail('card-thumb', [
                        'class'    => 'hts-compact-card__image',
                        'loading'  => 'lazy',
                        'decoding' => 'async',
                        'alt'      => get_the_title(),
                    ]); ?>
                <?php else : ?>
                    <img
                        src="<?php echo esc_url($placeholder); ?>"
                        class="hts-compact-card__image hts-compact-card__image--placeholder"
                        alt="<?php esc_attr_e('Placeholder image', 'hts-child'); ?>"
                        loading="lazy"
                    />
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="hts-compact-card__body">
            <h3 class="hts-compact-card__title"><?php the_title(); ?></h3>
            <?php echo hts_render_post_meta_line(null, 'news-card-meta'); ?>
            <?php if ($show_excerpt && $excerpt_text) : ?>
                <p class="hts-compact-card__excerpt"><?php echo esc_html($excerpt_text); ?></p>
            <?php endif; ?>
        </div>
    </a>
</article>
