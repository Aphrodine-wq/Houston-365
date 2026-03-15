<?php
/**
 * Sport View: Schedule
 *
 * Shows upcoming and recent games for the selected sport.
 *
 * Expected args (optional):
 * - sport_term (WP_Term)
 * - sport_slug (string)
 */

$sport_term = $args['sport_term'] ?? get_queried_object();
$sport_slug = $args['sport_slug'] ?? ($sport_term instanceof WP_Term ? $sport_term->slug : '');
$season_taxonomy = $args['season_taxonomy'] ?? '';
$season_terms = $args['season_terms'] ?? [];
$selected_season = $args['selected_season'] ?? '';

$today = current_time('Y-m-d');

$post_type = post_type_exists('game') ? 'game' : 'game-recaps';

if (
	($season_taxonomy === '' || empty($season_terms) || $selected_season === '')
	&& function_exists('hts_get_sport_season_context')
) {
	$season_context = hts_get_sport_season_context($sport_term, [$post_type]);
	if ($season_taxonomy === '') {
		$season_taxonomy = $season_context['taxonomy'] ?? '';
	}
	if (empty($season_terms)) {
		$season_terms = $season_context['terms'] ?? [];
	}
	if ($selected_season === '') {
		$selected_season = $season_context['selected'] ?? '';
	}
}

$base_query_args = [
    'post_type'      => $post_type,
    'posts_per_page' => -1,
    'tax_query'      => [],
    'meta_key'       => 'game_date',
    'orderby'        => 'meta_value',
    'order'          => 'ASC',
];

if ($sport_slug) {
    $base_query_args['tax_query'][] = [
        'taxonomy' => 'sport',
        'field'    => 'slug',
        'terms'    => $sport_slug,
    ];
}

if (!empty($season_taxonomy) && !empty($selected_season)) {
	$base_query_args['tax_query'][] = [
		'taxonomy' => $season_taxonomy,
		'field'    => 'slug',
		'terms'    => $selected_season,
	];
}

$upcoming_args = $base_query_args;
$upcoming_args['order'] = 'ASC';
$upcoming_args['meta_query'] = [
    [
        'key'     => 'game_date',
        'value'   => $today,
        'compare' => '>=',
        'type'    => 'DATE',
    ],
];

$recent_args = $base_query_args;
$recent_args['order'] = 'DESC';
$recent_args['meta_query'] = [
    [
        'key'     => 'game_date',
        'value'   => $today,
        'compare' => '<',
        'type'    => 'DATE',
    ],
];

$upcoming = new WP_Query($upcoming_args);
$recent   = new WP_Query($recent_args);

$format_date_for_card = static function ($date_raw) {
    if (!$date_raw) {
        return '';
    }

    if (preg_match('/^\d{8}$/', $date_raw)) {
        return $date_raw;
    }

    $date = DateTime::createFromFormat('Y-m-d', $date_raw);
    if ($date instanceof DateTime) {
        return $date->format('Ymd');
    }

    return '';
};
?>
<?php if (!empty($season_terms)) : ?>
	<form class="sport-season-filter panel" method="get" action="<?php echo esc_url(remove_query_arg(['hts_season', 'season'], add_query_arg([]))); ?>">
		<label class="sport-season-filter__label" for="sport-season-select-schedule"><?php esc_html_e('Season', 'hts-child'); ?></label>
		<div class="sport-season-filter__controls">
			<select id="sport-season-select-schedule" name="hts_season" onchange="this.form.submit()">
				<?php foreach ($season_terms as $season_term) : ?>
					<?php if ($season_term instanceof WP_Term) : ?>
						<option value="<?php echo esc_attr($season_term->slug); ?>" <?php selected($selected_season, $season_term->slug); ?>>
							<?php echo esc_html($season_term->name); ?>
						</option>
					<?php endif; ?>
				<?php endforeach; ?>
			</select>
		</div>
	</form>
<?php endif; ?>
<div class="sport-schedule">
    <section class="sport-schedule__group" aria-label="Upcoming Games">
        <h2 class="sport-schedule__title">Upcoming Games</h2>

        <?php if ($upcoming->have_posts()) : ?>
            <div class="sport-schedule__list">
                <?php
                while ($upcoming->have_posts()) :
                    $upcoming->the_post();

                    $game_date = get_post_meta(get_the_ID(), 'game_date', true);
                    $game_time = get_post_meta(get_the_ID(), 'game_time', true);

                    $has_result = (is_numeric(get_post_meta(get_the_ID(), 'home_score', true)) && is_numeric(get_post_meta(get_the_ID(), 'away_score', true)))
                        || get_post_meta(get_the_ID(), 'final_score', true);

                    $args_for_card = [
                        'home_away'   => get_post_meta(get_the_ID(), 'home_away', true),
                        'opponent'    => get_post_meta(get_the_ID(), 'opponent', true),
                        'home_score'  => get_post_meta(get_the_ID(), 'home_score', true),
                        'away_score'  => get_post_meta(get_the_ID(), 'away_score', true),
                        'final_score' => get_post_meta(get_the_ID(), 'final_score', true),
                        'date'        => $format_date_for_card($game_date),
                        'time'        => $game_time,
                        'venue'       => get_post_meta(get_the_ID(), 'venue', true),
                        'permalink'   => $has_result ? get_permalink() : '',
                    ];

                    get_template_part('template-parts/scoreboard-card', null, $args_for_card);
                endwhile;
                ?>
            </div>
        <?php else : ?>
            <p class="sport-schedule__empty">No upcoming games scheduled.</p>
        <?php endif; ?>
    </section>

    <section class="sport-schedule__group" aria-label="Recent Results">
        <h2 class="sport-schedule__title">Recent Results</h2>

        <?php if ($recent->have_posts()) : ?>
            <div class="sport-schedule__list">
                <?php
                while ($recent->have_posts()) :
                    $recent->the_post();

                    $game_date = get_post_meta(get_the_ID(), 'game_date', true);
                    $game_time = get_post_meta(get_the_ID(), 'game_time', true);

                    $has_result = (is_numeric(get_post_meta(get_the_ID(), 'home_score', true)) && is_numeric(get_post_meta(get_the_ID(), 'away_score', true)))
                        || get_post_meta(get_the_ID(), 'final_score', true);

                    $args_for_card = [
                        'home_away'   => get_post_meta(get_the_ID(), 'home_away', true),
                        'opponent'    => get_post_meta(get_the_ID(), 'opponent', true),
                        'home_score'  => get_post_meta(get_the_ID(), 'home_score', true),
                        'away_score'  => get_post_meta(get_the_ID(), 'away_score', true),
                        'final_score' => get_post_meta(get_the_ID(), 'final_score', true),
                        'date'        => $format_date_for_card($game_date),
                        'time'        => $game_time,
                        'venue'       => get_post_meta(get_the_ID(), 'venue', true),
                        'permalink'   => $has_result ? get_permalink() : '',
                    ];

                    get_template_part('template-parts/scoreboard-card', null, $args_for_card);
                endwhile;
                ?>
            </div>
        <?php else : ?>
            <p class="sport-schedule__empty">No recent results recorded.</p>
        <?php endif; ?>
    </section>
</div>

<?php
wp_reset_postdata();
