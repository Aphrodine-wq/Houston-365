<?php
/**
 * Template Part: Column Ad Section
 * Mirrors the top/bottom ad configuration (shortcode or fallback image).
 *
 * Accepts optional $args['slot'] to render Column Ads 1-5 using the same
 * template. Defaults to slot 1 for backward compatibility.
 */

$slot = isset($args['slot']) ? absint($args['slot']) : 1;
$slot = $slot > 0 ? $slot : 1;

// Slot 1 uses legacy theme_mod keys for compatibility.
$suffix             = $slot === 1 ? '' : "_{$slot}";
$shortcode_key      = $slot === 1 ? 'hts_column_ad_shortcode' : "hts_column_ad{$suffix}_shortcode";
$fallback_key       = $slot === 1 ? 'hts_column_ad_fallback_image' : "hts_column_ad{$suffix}_fallback_image";
$fallback_link_key  = $slot === 1 ? 'hts_column_ad_fallback_link' : "hts_column_ad{$suffix}_fallback_link";

$shortcode      = get_theme_mod($shortcode_key, '');
$fallback_image = get_theme_mod($fallback_key, '');
$fallback_link  = get_theme_mod($fallback_link_key, '');
$slot_label     = $slot === 1 ? '' : ' ' . $slot;

if (!empty($shortcode)) :
?>
    <div class="column-ad-container" role="region" aria-label="<?php esc_attr_e('Advertisement', 'hts-child'); ?>">
        <div class="column-ad-inner">
            <?php echo do_shortcode($shortcode); ?>
        </div>
    </div>
<?php
elseif (!empty($fallback_image)) :
?>
    <div class="column-ad-container" role="region" aria-label="<?php esc_attr_e('Advertisement', 'hts-child'); ?>">
        <div class="column-ad-inner">
            <?php if (!empty($fallback_link)) : ?>
                <a href="<?php echo esc_url($fallback_link); ?>" target="_blank" rel="noopener noreferrer sponsored" class="column-ad-link">
                    <img src="<?php echo esc_url($fallback_image); ?>" alt="<?php esc_attr_e('Advertisement', 'hts-child'); ?>" class="column-ad-image" loading="lazy">
                </a>
            <?php else : ?>
                <img src="<?php echo esc_url($fallback_image); ?>" alt="<?php esc_attr_e('Advertisement', 'hts-child'); ?>" class="column-ad-image" loading="lazy">
            <?php endif; ?>
        </div>
    </div>
<?php
elseif (current_user_can('manage_options')) :
?>
    <div class="column-ad-container column-ad-placeholder" role="region" aria-label="<?php esc_attr_e('Advertisement placeholder', 'hts-child'); ?>">
        <div class="column-ad-inner">
            <div class="column-ad-placeholder-content">
                <p><strong><?php echo esc_html(sprintf(__('Column Ad%s Placeholder', 'hts-child'), $slot_label)); ?></strong></p>
                <p><?php printf(__('Configure in <a href="%s">Customizer &rarr; Front Page Settings</a>', 'hts-child'), admin_url('customize.php?autofocus[section]=hts_front_page')); ?></p>
            </div>
        </div>
    </div>
<?php
endif;
