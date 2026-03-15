<?php
/**
 * Sport View: Roster
 *
 * Displays player profile cards filtered by the current sport term.
 *
 * Expected args (optional):
 * - sport_term (WP_Term)
 * - sport_slug (string)
 * - roster_query (WP_Query) Custom query override
 */

$sport_term = $args['sport_term'] ?? get_queried_object();
$sport_slug = $args['sport_slug'] ?? ($sport_term instanceof WP_Term ? $sport_term->slug : '');

$roster_query = $args['roster_query'] ?? null;
$season_taxonomy = $args['season_taxonomy'] ?? '';
$season_terms = $args['season_terms'] ?? [];
$selected_season = $args['selected_season'] ?? '';

if (
	($season_taxonomy === '' || empty($season_terms) || $selected_season === '')
	&& function_exists('hts_get_sport_season_context')
) {
	$season_context = hts_get_sport_season_context($sport_term, ['player-profiles']);
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

if (!$roster_query instanceof WP_Query) {
	$paged = max(
		1,
		(int) get_query_var('paged'),
		(int) get_query_var('page')
	);

    $query_args = [
        'post_type'      => 'player-profiles',
        'posts_per_page' => 12,
        'paged'          => $paged,
        'orderby'        => [
            'meta_value_num' => 'ASC',
            'title'          => 'ASC',
        ],
        'meta_key'       => 'number',
        'order'          => 'ASC',
        'no_found_rows'  => false,
    ];

    if ($sport_slug) {
        $query_args['tax_query'] = [
            [
                'taxonomy' => 'sport',
                'field'    => 'slug',
                'terms'    => $sport_slug,
            ],
        ];
    }

	if (!empty($season_taxonomy) && !empty($selected_season)) {
		$query_args['tax_query'] = isset($query_args['tax_query']) ? (array) $query_args['tax_query'] : [];
		$query_args['tax_query'][] = [
			'taxonomy' => $season_taxonomy,
			'field'    => 'slug',
			'terms'    => $selected_season,
		];
	}

	if (function_exists('apply_filters')) {
		$query_args = apply_filters('hts_sport_roster_query_args', $query_args, $sport_term);
	}

	$roster_query = new WP_Query($query_args);
}
?>
<?php if ($roster_query->have_posts()) : ?>
    <div class="sport-roster">
        <?php
        while ($roster_query->have_posts()) :
            $roster_query->the_post();
            get_template_part('template-parts/content', 'player-roster-card', ['post_id' => get_the_ID()]);
        endwhile;
        ?>
    </div>

	<?php if ($roster_query->max_num_pages > 1) : ?>
		<div class="recent-posts-pagination">
			<?php
			$paged = max(1, (int) $roster_query->get('paged', 1));
			$base = add_query_arg('paged', '%#%');
			echo paginate_links([
				'base'      => $base,
				'format'    => '',
				'total'     => $roster_query->max_num_pages,
				'current'   => $paged,
				'prev_text' => __('Previous', 'hts-child'),
				'next_text' => __('Next', 'hts-child'),
			]);
			?>
		</div>
	<?php endif; ?>
<?php else : ?>
    <div class="panel">
        <?php
        $empty_message = __('No profiles published', 'hts-child');
        $season_label = '';

        if (!empty($selected_season)) {
            foreach ($season_terms as $season_term) {
                if ($season_term instanceof WP_Term && $season_term->slug === $selected_season) {
                    $season_label = $season_term->name;
                    break;
                }
            }

            if ($season_label === '') {
                $season_label = $selected_season;
            }
        }

        if ($sport_term instanceof WP_Term && $season_label !== '') {
            $empty_message = sprintf(
                __('No profiles published for %1$s in %2$s.', 'hts-child'),
                $sport_term->name,
                $season_label
            );
        } elseif ($sport_term instanceof WP_Term) {
            $empty_message = sprintf(
                __('No profiles published for %s.', 'hts-child'),
                $sport_term->name
            );
        }
        ?>
        <p><?php echo esc_html($empty_message); ?></p>
    </div>
<?php endif; ?>

<?php wp_reset_postdata(); ?>
