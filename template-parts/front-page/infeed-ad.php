<?php
/**
 * Template Part: In-Feed Ad
 * Displays a leaderboard-style ad between content sections.
 *
 * Accepts optional $args['slot'] (default 1).
 */

$slot = isset($args['slot']) ? absint($args['slot']) : 1;
$slot = $slot > 0 ? $slot : 1;

$shortcode_key     = "hts_infeed_ad_{$slot}_shortcode";
$fallback_key      = "hts_infeed_ad_{$slot}_fallback_image";
$fallback_link_key = "hts_infeed_ad_{$slot}_fallback_link";

$shortcode      = get_theme_mod($shortcode_key, '');
$fallback_image = get_theme_mod($fallback_key, '');
$fallback_link  = get_theme_mod($fallback_link_key, '');

if (!empty($shortcode)) :
?>
    <div class="hts-infeed-ad" role="region" aria-label="<?php esc_attr_e('Advertisement', 'hts-child'); ?>">
        <div class="hts-infeed-ad__inner">
            <span class="hts-infeed-ad__label"><?php esc_html_e('Advertisement', 'hts-child'); ?></span>
            <?php echo do_shortcode($shortcode); ?>
        </div>
    </div>
<?php
elseif (!empty($fallback_image)) :
?>
    <div class="hts-infeed-ad" role="region" aria-label="<?php esc_attr_e('Advertisement', 'hts-child'); ?>">
        <div class="hts-infeed-ad__inner">
            <span class="hts-infeed-ad__label"><?php esc_html_e('Advertisement', 'hts-child'); ?></span>
            <?php if (!empty($fallback_link)) : ?>
                <a href="<?php echo esc_url($fallback_link); ?>" target="_blank" rel="noopener noreferrer sponsored">
                    <img src="<?php echo esc_url($fallback_image); ?>" alt="<?php esc_attr_e('Advertisement', 'hts-child'); ?>" loading="lazy">
                </a>
            <?php else : ?>
                <img src="<?php echo esc_url($fallback_image); ?>" alt="<?php esc_attr_e('Advertisement', 'hts-child'); ?>" loading="lazy">
            <?php endif; ?>
        </div>
    </div>
<?php
elseif (current_user_can('manage_options')) :
?>
    <div class="hts-infeed-ad" role="region" aria-label="<?php esc_attr_e('Advertisement placeholder', 'hts-child'); ?>">
        <div class="hts-infeed-ad__inner hts-infeed-ad__placeholder">
            <p><strong><?php echo esc_html(sprintf(__('In-Feed Ad %d Placeholder', 'hts-child'), $slot)); ?></strong></p>
            <p><?php printf(__('Configure in <a href="%s">Customizer &rarr; Front Page Settings</a>', 'hts-child'), admin_url('customize.php?autofocus[section]=hts_front_page')); ?></p>
        </div>
    </div>
<?php
endif;
