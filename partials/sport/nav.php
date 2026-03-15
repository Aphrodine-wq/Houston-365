<?php
/**
 * Sport Navigation Partial
 *
 * Sticky subnav for sport taxonomy pages
 *
 * Expected args:
 * - $sport_term (WP_Term): Current sport term object
 * - $current_view (string): Active view slug
 * - $available_views (array): Array of enabled views for this sport
 */

$sport_term = $args['sport_term'] ?? get_queried_object();
$current_view = $args['current_view'] ?? 'news';
$available_views = $args['available_views'] ?? ['news' => true];
$endpoint_map = $args['endpoint_map'] ?? hts_get_sport_view_endpoints();
$season_param = function_exists('hts_get_requested_season_param')
	? hts_get_requested_season_param()
	: '';

// Get the base URL for this sport taxonomy
$base_url_raw = get_term_link($sport_term);
$base_url = is_wp_error($base_url_raw) ? '' : trailingslashit($base_url_raw);

// Define view labels
$view_labels = [
    'news' => 'News',
    'standings' => 'Standings',
    'schedule' => 'Schedule',
    'staff' => 'Staff',
    'class' => 'Class Breakdown',
];
?>
<nav class="sport-nav">
    <div class="sport-nav-container">
        <div class="sport-nav-header">
            <h1 class="sport-nav-title"><?php echo esc_html($sport_term->name); ?></h1>
        </div>
        <ul class="sport-nav-menu">
            <?php foreach ($available_views as $view_slug => $is_enabled) : ?>
                <?php if ($is_enabled && isset($view_labels[$view_slug])) : ?>
                    <li class="sport-nav-item">
                        <?php
                        $endpoint_slug = $endpoint_map[$view_slug] ?? '';
                        $view_url = $base_url ?: '#';

                        if ($view_slug === 'news') {
                            // Prefer base taxonomy URL for the default view.
                            $view_url = $base_url ?: $view_url;
                        } elseif ($endpoint_slug && $base_url) {
                            $view_url = trailingslashit($base_url . $endpoint_slug);
                        } elseif ($base_url) {
                            $view_url = add_query_arg('view', $view_slug, $base_url);
                        }

                        if ($season_param && in_array($view_slug, ['roster', 'schedule'], true)) {
                            $view_url = add_query_arg('hts_season', $season_param, $view_url);
                        }

                        $is_active = $current_view === $view_slug;
                        ?>
                        <a
                            href="<?php echo esc_url($view_url); ?>"
                            class="sport-nav-link<?php echo $is_active ? ' active' : ''; ?>"
                        >
                            <?php echo esc_html($view_labels[$view_slug]); ?>
                        </a>
                    </li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>
    </div>
</nav>
