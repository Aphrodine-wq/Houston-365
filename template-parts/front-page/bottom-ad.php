<?php
/**
 * Template Part: Bottom Ad Section
 * Displays either an ad plugin shortcode or a fallback banner image with optional link
 */

// Retrieve Customizer settings
$shortcode      = get_theme_mod('hts_bottom_ad_shortcode', '');
$fallback_image = get_theme_mod('hts_bottom_ad_fallback_image', '');
$fallback_link  = get_theme_mod('hts_bottom_ad_fallback_link', '');

// If shortcode exists, render it
if (!empty($shortcode)) :
?>
    <div class="bottom-ad-container" role="region" aria-label="Advertisement">
        <div class="bottom-ad-inner">
            <?php echo do_shortcode($shortcode); ?>
        </div>
    </div>
<?php
// Otherwise, render fallback image if available
elseif (!empty($fallback_image)) :
?>
    <div class="bottom-ad-container" role="region" aria-label="Advertisement">
        <div class="bottom-ad-inner">
            <?php if (!empty($fallback_link)) : ?>
                <a href="<?php echo esc_url($fallback_link); ?>" target="_blank" rel="noopener noreferrer sponsored" class="bottom-ad-link">
                    <img src="<?php echo esc_url($fallback_image); ?>" alt="<?php esc_attr_e('Advertisement', 'hts-child'); ?>" class="bottom-ad-image" width="970" height="250" loading="lazy">
                </a>
            <?php else : ?>
                <img src="<?php echo esc_url($fallback_image); ?>" alt="<?php esc_attr_e('Advertisement', 'hts-child'); ?>" class="bottom-ad-image" width="970" height="250" loading="lazy">
            <?php endif; ?>
        </div>
    </div>
<?php
// If no ad configured, render a placeholder for logged-in admins
elseif (current_user_can('manage_options')) :
?>
    <div class="bottom-ad-container bottom-ad-placeholder" role="region" aria-label="Advertisement Placeholder">
        <div class="bottom-ad-inner">
            <div class="bottom-ad-placeholder-content">
                <p><strong><?php _e('Bottom Ad Placeholder', 'hts-child'); ?></strong></p>
                <p><?php printf(__('Configure in <a href="%s">Customizer &rarr; Front Page Settings</a>', 'hts-child'), admin_url('customize.php?autofocus[section]=hts_front_page')); ?></p>
            </div>
        </div>
    </div>
<?php
endif;
