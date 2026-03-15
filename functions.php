<?php
// === Helper: Get Placeholder Image URL ===
function hts_get_placeholder_image($type = 'card') {
	$stylesheet_uri = get_stylesheet_directory_uri();
	$placeholders = [
		'hero' => $stylesheet_uri . '/assets/images/hero-placeholder.jpg',
		'card' => $stylesheet_uri . '/assets/images/card-placeholder.jpg',
		'podcast' => $stylesheet_uri . '/assets/images/podcast-placeholder.jpg',
	];

	return isset($placeholders[$type]) ? $placeholders[$type] : $placeholders['card'];
}

// === Helper: Get Placeholder Dimensions ===
function hts_get_placeholder_dimensions($type = 'card') {
	$dimensions = [
		'hero' => ['width' => 1600, 'height' => 900],
		'card' => ['width' => 480, 'height' => 320],
		'podcast' => ['width' => 150, 'height' => 150],
	];

	return isset($dimensions[$type]) ? $dimensions[$type] : $dimensions['card'];
}

/**
 * Return the post types allowed on news surfaces.
 *
 * @return array
 */
function hts_get_news_post_types() {
	$post_types = apply_filters('hts_news_post_types', ['post', 'game-recaps']);
	$post_types = array_values(array_unique(array_filter(array_map('sanitize_key', (array) $post_types))));

	return !empty($post_types) ? $post_types : ['post'];
}

/**
 * Return the recruiting rank value (0-5) for a recruit post.
 *
 * @param int|null $post_id
 * @return int|null
 */
function hts_get_recruit_rank_value($post_id = null) {
	$post_id = $post_id ? intval($post_id) : get_the_ID();
	if (!$post_id) {
		return null;
	}

	$rank_raw = null;
	if (function_exists('get_field')) {
		$rank_raw = get_field('h365_rank', $post_id);
	}

	if ($rank_raw === null || $rank_raw === '') {
		$rank_raw = get_post_meta($post_id, 'h365_rank', true);
	}

	if (!is_numeric($rank_raw)) {
		return null;
	}

	$rank = (int) $rank_raw;
	return max(0, min(5, $rank));
}

/**
 * Build popcorn rating icons for a recruit.
 *
 * @param int|null $rank
 * @param array    $args
 * @return string
 */
function hts_get_recruit_popcorn_icons($rank, array $args = []) {
	$max = isset($args['max']) ? absint($args['max']) : 5;
	if ($max <= 0) {
		$max = 5;
	}

	$icon = isset($args['icon']) && $args['icon']
		? $args['icon']
		: get_stylesheet_directory_uri() . '/assets/images/popcorn.png';

	$extra_class = isset($args['class']) ? trim((string) $args['class']) : '';
	$base_class  = 'recruit-popcorn' . ($extra_class ? ' ' . $extra_class : '');
	$rank_value  = is_numeric($rank) ? max(0, min($max, (int) $rank)) : null;
	$output      = '';

	for ($i = 1; $i <= $max; $i++) {
		$active_class = ($rank_value !== null && $i <= $rank_value) ? ' is-active' : ' is-muted';
		$output      .= '<img class="' . esc_attr($base_class . $active_class) . '" src="' . esc_url($icon) . '" alt="" loading="lazy">';
	}

	return $output;
}

/**
 * Order recruiting queries by in-state rating (numeric) with unrated last.
 *
 * @param array    $clauses
 * @param WP_Query $query
 * @return array
 */
function hts_apply_recruiting_sort_clauses($clauses, $query) {
	if (!$query instanceof WP_Query || !$query->get('hts_recruiting_sort')) {
		return $clauses;
	}

	global $wpdb;
	$alias = 'hts_in_state_rating';
	$meta_key = 'in_state_rating';

	if (strpos($clauses['join'], $alias) === false) {
		$clauses['join'] .= $wpdb->prepare(
			" LEFT JOIN {$wpdb->postmeta} AS {$alias} ON ({$wpdb->posts}.ID = {$alias}.post_id AND {$alias}.meta_key = %s)",
			$meta_key
		);
	}

	$groupby = $clauses['groupby'] ?? '';
	if (stripos($groupby, "{$wpdb->posts}.ID") === false) {
		$clauses['groupby'] = $groupby ? $groupby . ', ' . "{$wpdb->posts}.ID" : "{$wpdb->posts}.ID";
	}

	$clauses['orderby'] = "({$alias}.meta_value IS NULL OR {$alias}.meta_value = '') ASC, CAST({$alias}.meta_value AS DECIMAL(10,4)) ASC, {$wpdb->posts}.post_title ASC";

	return $clauses;
}
add_filter('posts_clauses', 'hts_apply_recruiting_sort_clauses', 10, 2);

/**
 * Allowlist for single-post ads sidebar post types.
 *
 * Update via the hts_single_ads_allowed_post_types filter.
 *
 * @return array|string
 */
function hts_get_single_ads_allowed_post_types() {
	return apply_filters('hts_single_ads_allowed_post_types', ['post', 'recruit', 'player-profiles']);
}

/**
 * Determine whether the single-post ads sidebar should render.
 *
 * @return bool
 */
function hts_should_render_single_ads_sidebar() {
	$allowed_post_types = hts_get_single_ads_allowed_post_types();
	$is_bbpress_page = function_exists('is_bbpress') && is_bbpress();

	return is_active_sidebar('single-post-ads')
		&& is_singular($allowed_post_types)
		&& !is_page()
		&& !is_page('login')
		&& !$is_bbpress_page;
}

/**
 * Return the primary sport term for a post (first assigned term).
 *
 * @param int|null $post_id
 * @return WP_Term|null
 */
function hts_get_primary_sport_term($post_id = null) {
	$post_id = $post_id ? intval($post_id) : get_the_ID();
	if (!$post_id) {
		return null;
	}

	$terms = wp_get_post_terms($post_id, 'sport');
	if (empty($terms) || is_wp_error($terms)) {
		return null;
	}

	return $terms[0];
}

/**
 * Uppercase a card label for display.
 *
 * @param string $label
 * @return string
 */
function hts_uppercase_card_label($label) {
	$label = trim((string) $label);
	if ($label === '') {
		return $label;
	}

	if (function_exists('mb_strtoupper')) {
		return mb_strtoupper($label, 'UTF-8');
	}

	return strtoupper($label);
}

/**
 * Resolve the badge class for a card label.
 *
 * @param string $source
 * @param string $slug
 * @return string
 */
function hts_get_post_card_label_class($source, $slug) {
	$slug = sanitize_title($slug);
	$styled_slugs = apply_filters('hts_post_card_label_styled_slugs', [
		'writers-room',
		'opinion',
		'opinions',
		'columns',
		'column',
		'video-news',
		'video',
		'videos',
		'recruiting',
		'mbb',
		'wbb',
		'basketball',
		'mens-basketball',
		'womens-basketball',
		'bsb',
		'baseball',
		'sfb',
		'softball',
	]);
	$styled_slugs = array_values(array_filter(array_map('sanitize_title', (array) $styled_slugs)));

	if ($slug && in_array($slug, $styled_slugs, true)) {
		return 'badge-' . $slug;
	}

	if ($source === 'category' && $slug) {
		return 'badge-category badge-' . $slug;
	}

	if ($source === 'sport' && $slug) {
		return 'badge-default badge-' . $slug;
	}

	return 'badge-default';
}

/**
 * Resolve the card label based on categories and sport taxonomy.
 *
 * @param int|null $post_id
 * @return array{label:?string,class:string,slug:string,source:string}
 */
function hts_get_post_card_label_data($post_id = null) {
	$post_id = $post_id ? intval($post_id) : get_the_ID();
	if (!$post_id) {
		return [
			'label'  => null,
			'class'  => '',
			'slug'   => '',
			'source' => '',
		];
	}

	$post_type  = get_post_type($post_id);
	$label      = '';
	$slug       = '';
	$source     = '';
	$badge_terms = apply_filters('hts_post_card_badge_terms', [], $post_id, $post_type);

	if (!empty($badge_terms)) {
		$first_term = reset($badge_terms);
		if ($first_term instanceof WP_Term) {
			$label  = $first_term->name;
			$slug   = sanitize_title($first_term->slug);
			$source = $first_term->taxonomy ?: 'custom';
		}
	}

	if ($label === '') {
		$ignored_slugs = apply_filters('hts_post_card_label_ignored_category_slugs', ['uncategorized']);
		$ignored_slugs = array_values(array_filter(array_map('sanitize_title', (array) $ignored_slugs)));
		$categories = get_the_category($post_id);

		if (!empty($categories) && !is_wp_error($categories)) {
			foreach ($categories as $category) {
				if (!$category instanceof WP_Term) {
					continue;
				}

				$category_slug = sanitize_title($category->slug);
				if ($category_slug === '' || in_array($category_slug, $ignored_slugs, true)) {
					continue;
				}

				$label  = $category->name;
				$slug   = $category_slug;
				$source = 'category';
				break;
			}
		}
	}

	if ($label === '') {
		$sport_term = hts_get_primary_sport_term($post_id);
		if ($sport_term && !is_wp_error($sport_term)) {
			$label  = $sport_term->name;
			$slug   = sanitize_title($sport_term->slug);
			$source = 'sport';
		}
	}

	if ($label === '') {
		$label  = apply_filters('hts_post_card_label_fallback', __('News', 'hts-child'), $post_id);
		$source = 'fallback';
		$slug   = '';
	}

	$label = hts_uppercase_card_label($label);
	$class = hts_get_post_card_label_class($source, $slug);

	$data = [
		'label'  => $label,
		'class'  => $class,
		'slug'   => $slug,
		'source' => $source,
	];

	return apply_filters('hts_post_card_label_data', $data, $post_id, $post_type);
}

/**
 * Append a sport taxonomy filter to WP_Query args.
 *
 * @param array       $args
 * @param string|null $sport_slug
 * @return array
 */
function hts_with_sport_tax_query(array $args, $sport_slug = null) {
	$sport_slug = $sport_slug ? sanitize_title($sport_slug) : '';
	if (!$sport_slug) {
		return $args;
	}

	$tax_query   = isset($args['tax_query']) ? (array) $args['tax_query'] : [];
	$tax_query[] = [
		'taxonomy' => 'sport',
		'field'    => 'slug',
		'terms'    => [$sport_slug],
	];

	$args['tax_query'] = $tax_query;
	return $args;
}

/**
 * Render a sport-aware meta line using existing meta classes.
 *
 * Output order: Sport term (linked) � Time since published � By Author.
 *
 * @param int|null    $post_id
 * @param string      $container_class
 * @return string
 */
function hts_render_post_meta_line($post_id = null, $container_class = 'news-card-meta') {
	$post_id  = $post_id ? intval($post_id) : get_the_ID();
	$parts    = [];
	$sport    = hts_get_primary_sport_term($post_id);

	if ($sport) {
		$link = get_term_link($sport);
		if (!is_wp_error($link)) {
			$parts[] = sprintf(
				'<a class="news-card-author news-card-sport" href="%s">%s</a>',
				esc_url($link),
				esc_html($sport->name)
			);
		}
	}

	$timestamp = get_post_time('U', true, $post_id);
	if ($timestamp) {
		$parts[] = sprintf(
			'<time class="news-card-date" datetime="%s">%s</time>',
			esc_attr(get_post_time('c', true, $post_id)),
			esc_html(human_time_diff($timestamp, current_time('timestamp')) . ' ' . __('ago', 'hts-child'))
		);
	}

	$author_name = get_the_author_meta('display_name', get_post_field('post_author', $post_id));
	if ($author_name) {
		/* translators: %s author name */
		$parts[] = sprintf(
			'<span class="news-card-author">%s %s</span>',
			esc_html__('By', 'hts-child'),
			esc_html($author_name)
		);
	}

	if (empty($parts)) {
		return '';
	}

	$separator = '<span class="news-card-separator" aria-hidden="true">&bull;</span>';

	return sprintf(
		'<div class="%s">%s</div>',
		esc_attr($container_class),
		implode($separator, $parts)
	);
}

// === Styles & fonts ===
add_action( 'wp_enqueue_scripts', function() {
    $stylesheet_dir = get_stylesheet_directory();
    $stylesheet_uri = get_stylesheet_directory_uri();

    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );

    // Enqueue front-page.css with filemtime versioning
    $front_page_css = $stylesheet_dir . '/styles/front-page.css';
    if ( file_exists( $front_page_css ) ) {
        wp_enqueue_style( 'home', $stylesheet_uri . '/styles/front-page.css', ['parent-style'], filemtime( $front_page_css ) );
    }

    wp_enqueue_style( 'hts-child', get_stylesheet_uri(), ['parent-style'], wp_get_theme()->get('Version') );
    wp_add_inline_style( 'hts-child', ':root{--ink:#0f172a;--muted:#64748b;--light:#f1f5f9;--brand:#1d4ed8;--brand-2:#9333ea} .container{max-width:1100px;margin:0 auto;padding:0 1rem} .chip{display:inline-flex;align-items:center;gap:.4rem;padding:.25rem .6rem;border-radius:999px;background:var(--light);color:var(--ink);font-size:.85rem} .stack>*{margin-block:0} .stack>*+*{margin-block-start:1rem} .grid{display:grid;gap:1rem} .grid-2{grid-template-columns:1fr} @media(min-width:800px){.grid-2{grid-template-columns:1.2fr .8fr}} .card{background:#fff;border:1px solid #e5e7eb;border-radius:16px;overflow:hidden;box-shadow:0 6px 18px rgba(2,6,23,.04)} .meta{color:var(--muted);font-size:.95rem} .title{font-size:clamp(1.6rem,2.5vw,2.2rem);line-height:1.2;margin:.3rem 0 .6rem} .hero{border-radius:18px;overflow:hidden;aspect-ratio:16/9;background:#0b1220;position:relative} .hero img{width:100%;height:100%;object-fit:cover;display:block;opacity:.92} .score{display:flex;align-items:center;gap:1rem;font-weight:700;font-size:clamp(1.4rem,4vw,2rem)} .score .vs{opacity:.6;font-weight:600} .panel{border:1px solid #e5e7eb;border-radius:16px;padding:1rem;background:#fff} .btn{display:inline-flex;align-items:center;gap:.5rem;padding:.55rem .9rem;border-radius:12px;border:1px solid #e5e7eb;background:#fff} .list{padding-left:1.1rem} .list li{margin:.35rem 0} blockquote{border-left:4px solid var(--brand);padding-left:1rem;color:#0b1324;background:linear-gradient(90deg,#eff6ff,transparent);border-radius:6px 12px 12px 6px} .badge{background:linear-gradient(90deg,var(--brand),var(--brand-2));color:#fff;border-radius:10px;padding:.2rem .6rem;font-size:.85rem} .num-badge{position:absolute;top:.75rem;left:.75rem;background:#111827;color:#fff;border-radius:12px;padding:.2rem .55rem;font-weight:700} .pill{background:#111827;color:#fff;border-radius:999px;padding:.25rem .6rem;font-size:.82rem} .kgrid{display:grid;grid-template-columns:repeat(2,1fr);gap:.6rem} @media(min-width:700px){.kgrid{grid-template-columns:repeat(4,1fr)}}' );

    $component_style = $stylesheet_dir . '/styles/template-parts.css';
    if ( file_exists( $component_style ) ) {
        wp_enqueue_style(
            'hts-template-parts',
            $stylesheet_uri . '/styles/template-parts.css',
            ['hts-child'],
            filemtime( $component_style )
        );
    }

    // Reuse home layout styles (news cards, grids) on front page, sport archives, and podcast archive
    $home_css = $stylesheet_dir . '/styles/home.css';
    $enqueue_home_css = (is_front_page() || is_tax('sport') || is_category('podcast'));
    $enqueue_home_css = apply_filters('hts_enqueue_home_styles', $enqueue_home_css);

    if ( file_exists( $home_css ) && $enqueue_home_css ) {
        wp_enqueue_style(
            'hts-home',
            $stylesheet_uri . '/styles/home.css',
            ['hts-child'],
            filemtime( $home_css )
        );
    }

    if (is_front_page()) {
        // Enqueue hero CSS for the rotator
        $hero_css = $stylesheet_dir . '/styles/hero.css';
        if ( file_exists( $hero_css ) ) {
            wp_enqueue_style(
                'hts-hero',
                $stylesheet_uri . '/styles/hero.css',
                ['hts-home'],
                filemtime( $hero_css )
            );
        }
        // Enqueue JS for the rotator
        wp_enqueue_script('home-hero', get_stylesheet_directory_uri().'/js/hero.js', [], filemtime(get_stylesheet_directory().'/js/hero.js'), true);
    }

    $template_styles = [
        'hts-profile-cards' => [
            'condition' => is_singular('recruit') || is_singular('player-profiles'),
            'file' => '/styles/profile-cards.css',
        ],
        'hts-single-player-profiles' => [
            'condition' => is_singular('player-profiles'),
            'file' => '/styles/single-player-profiles.css',
        ],
        'hts-archive-player-profiles' => [
            'condition' => is_post_type_archive('player-profiles'),
            'file' => '/styles/archive-player-profiles.css',
        ],
        'hts-single-game-recaps' => [
            'condition' => is_singular('game-recaps'),
            'file' => '/styles/single-game-recaps.css',
        ],
        'hts-archive-game-recaps' => [
            'condition' => is_post_type_archive('game-recaps'),
            'file' => '/styles/archive-game-recaps.css',
        ],
        'hts-category-podcast' => [
            'condition' => is_category('podcast'),
            'file' => '/styles/category-podcast.css',
        ],
        'hts-tax-sport' => [
            'condition' => is_tax('sport'),
            'file' => '/styles/sport.css',
        ],
        'hts-single-recruit' => [
            'condition' => is_singular('recruit'),
            'file' => '/styles/single-recruit.css',
        ],
    ];

    foreach ( $template_styles as $handle => $config ) {
        if ( empty( $config['condition'] ) ) {
            continue;
        }

        $file = $stylesheet_dir . $config['file'];
        if ( ! file_exists( $file ) ) {
            continue;
        }

        wp_enqueue_style(
            $handle,
            $stylesheet_uri . $config['file'],
            ['hts-child'],
            filemtime( $file )
        );
    }

    // PMPro login card styles (only when the login markup is present).
    $pmpro_login_css = $stylesheet_dir . '/styles/pmpro-login.css';
    $enqueue_pmpro_login_css = false;

    if ( function_exists( 'pmpro_is_login_page' ) && pmpro_is_login_page() ) {
        $enqueue_pmpro_login_css = true;
    } elseif ( is_page() ) {
        if ( is_page( 'login' ) ) {
            $enqueue_pmpro_login_css = true;
        } else {
            $post = get_post();
            if ( $post instanceof WP_Post ) {
                $content = (string) $post->post_content;
                if ( has_shortcode( $content, 'pmpro_login' ) || has_shortcode( $content, 'pmpro-login' ) ) {
                    $enqueue_pmpro_login_css = true;
                } elseif ( function_exists( 'has_block' ) ) {
                    if (
                        has_block( 'pmpro/login', $content )
                        || has_block( 'paid-memberships-pro/login', $content )
                    ) {
                        $enqueue_pmpro_login_css = true;
                    }
                }
            }
        }
    }

    if ( $enqueue_pmpro_login_css && file_exists( $pmpro_login_css ) ) {
        wp_enqueue_style(
            'hts-pmpro-login',
            $stylesheet_uri . '/styles/pmpro-login.css',
            ['hts-child'],
            filemtime( $pmpro_login_css )
        );
    }

    $pmpro_pages_css = $stylesheet_dir . '/styles/pmpro-pages.css';
    $enqueue_pmpro_pages_css = false;

    if (function_exists('pmpro_is_account_page') && pmpro_is_account_page()) {
        $enqueue_pmpro_pages_css = true;
    } elseif (function_exists('pmpro_is_levels_page') && pmpro_is_levels_page()) {
        $enqueue_pmpro_pages_css = true;
    } elseif (function_exists('pmpro_is_checkout') && pmpro_is_checkout()) {
        $enqueue_pmpro_pages_css = true;
    } elseif (function_exists('pmpro_is_member_profile_edit_page') && pmpro_is_member_profile_edit_page()) {
        $enqueue_pmpro_pages_css = true;
    } elseif (function_exists('pmpro_is_billing_page') && pmpro_is_billing_page()) {
        $enqueue_pmpro_pages_css = true;
    } elseif (function_exists('pmpro_is_invoice_page') && pmpro_is_invoice_page()) {
        $enqueue_pmpro_pages_css = true;
    } elseif (function_exists('pmpro_is_cancel_page') && pmpro_is_cancel_page()) {
        $enqueue_pmpro_pages_css = true;
    }

    if (!$enqueue_pmpro_pages_css && is_page()) {
        $pmpro_page_slugs = [
            'membership-account',
            'membership-levels',
            'membership-checkout',
            'your-profile',
        ];

        if (is_page($pmpro_page_slugs)) {
            $enqueue_pmpro_pages_css = true;
        } else {
            $post = get_post();
            if ($post instanceof WP_Post) {
                $content = (string) $post->post_content;
                $shortcodes = [
                    'pmpro_account',
                    'pmpro_levels',
                    'pmpro_checkout',
                    'pmpro_member_profile_edit',
                    'pmpro_billing',
                    'pmpro_invoice',
                    'pmpro_cancel',
                ];

                foreach ($shortcodes as $shortcode) {
                    if (has_shortcode($content, $shortcode)) {
                        $enqueue_pmpro_pages_css = true;
                        break;
                    }
                }

                if (!$enqueue_pmpro_pages_css && function_exists('has_block')) {
                    $blocks = [
                        'pmpro/account',
                        'pmpro/levels',
                        'pmpro/checkout',
                        'pmpro/member-profile-edit',
                    ];

                    foreach ($blocks as $block) {
                        if (has_block($block, $content)) {
                            $enqueue_pmpro_pages_css = true;
                            break;
                        }
                    }
                }
            }
        }
    }

    if ($enqueue_pmpro_pages_css && !$enqueue_pmpro_login_css && file_exists($pmpro_pages_css)) {
        wp_enqueue_style(
            'hts-pmpro-pages',
            $stylesheet_uri . '/styles/pmpro-pages.css',
            ['hts-child'],
            filemtime($pmpro_pages_css)
        );
    }

    // Podcast archive scroller interactions
    if (is_category('podcast')) {
        $podcast_js = $stylesheet_dir . '/js/podcast-scrollers.js';
        if (file_exists($podcast_js)) {
            wp_enqueue_script(
                'hts-podcast-scrollers',
                $stylesheet_uri . '/js/podcast-scrollers.js',
                [],
                filemtime($podcast_js),
                true
            );
        }
    }

    // bbPress board + topic styles, loaded only on bbPress views.
    if ( function_exists( 'is_bbpress' ) && is_bbpress() ) {
        // Guard against legacy forum styles conflicting with the board/topic UI.
        wp_dequeue_style( 'hts-forums' );
        wp_deregister_style( 'hts-forums' );

        $bbpress_board_file = $stylesheet_dir . '/styles/bbpress-board.css';
        $bbpress_topic_file = $stylesheet_dir . '/styles/bbpress-topic.css';

        $is_board_context = false;
        if ( function_exists( 'bbp_is_forum_archive' ) && bbp_is_forum_archive() ) {
            $is_board_context = true;
        } elseif ( function_exists( 'bbp_is_single_forum' ) && bbp_is_single_forum() ) {
            $is_board_context = true;
        } elseif ( function_exists( 'bbp_is_topic_archive' ) && bbp_is_topic_archive() ) {
            $is_board_context = true;
        }

        if ( $is_board_context && file_exists( $bbpress_board_file ) ) {
            wp_enqueue_style(
                'hts-bbpress-board',
                $stylesheet_uri . '/styles/bbpress-board.css',
                ['hts-child'],
                filemtime( $bbpress_board_file )
            );
        }

        $is_topic_context = false;
        if ( function_exists( 'bbp_is_single_topic' ) && bbp_is_single_topic() ) {
            $is_topic_context = true;
        } elseif ( function_exists( 'bbp_is_single_reply' ) && bbp_is_single_reply() ) {
            $is_topic_context = true;
        } elseif ( function_exists( 'bbp_is_topic_edit' ) && bbp_is_topic_edit() ) {
            $is_topic_context = true;
        } elseif ( function_exists( 'bbp_is_reply_edit' ) && bbp_is_reply_edit() ) {
            $is_topic_context = true;
        }

        if ( $is_topic_context && file_exists( $bbpress_topic_file ) ) {
            wp_enqueue_style(
                'hts-bbpress-topic',
                $stylesheet_uri . '/styles/bbpress-topic.css',
                ['hts-child'],
                filemtime( $bbpress_topic_file )
            );
        }
    }
});

add_action('enqueue_block_editor_assets', function() {
	$editor_css = get_stylesheet_directory() . '/styles/editor.css';

	if (file_exists($editor_css)) {
		wp_enqueue_style(
			'hts-editor',
			get_stylesheet_directory_uri() . '/styles/editor.css',
			['wp-edit-blocks'],
			filemtime($editor_css)
		);
	}

	$inline_css = ':root :where(.editor-styles-wrapper){--theme-headings-color:#111827 !important;--theme-text-color:#111827 !important;}'
		. ':root .editor-styles-wrapper{--theme-headings-color:#111827 !important;--theme-text-color:#111827 !important;}'
		. '.editor-styles-wrapper .editor-post-title__input[data-is-placeholder-visible="true"]::before{color:#6b7280 !important;opacity:1;}';

	$inline_handles = [
		'wp-block-editor',
		'ct-editor',
		'ct-editor-styles',
		'blocksy-editor',
		'blocksy-editor-styles',
		'wp-edit-blocks',
		'hts-editor',
	];

	$inline_handle = '';
	foreach ($inline_handles as $handle) {
		if (wp_style_is($handle, 'enqueued') || wp_style_is($handle, 'registered')) {
			$inline_handle = $handle;
			break;
		}
	}

	if (!$inline_handle) {
		$inline_handle = 'hts-editor-inline';
		wp_register_style($inline_handle, false, [], null);
		wp_enqueue_style($inline_handle);
	}

	wp_add_inline_style($inline_handle, $inline_css);
});

function hts_find_taxonomies_by_label(array $labels) {
	$labels = array_filter(array_map('strtolower', array_map('trim', $labels)));
	if (empty($labels) || !function_exists('get_taxonomies')) {
		return [];
	}

	$matches = [];
	foreach (get_taxonomies([], 'objects') as $slug => $taxonomy) {
		if (!$taxonomy || !$slug) {
			continue;
		}

		$candidates = [];
		if (!empty($taxonomy->label)) {
			$candidates[] = $taxonomy->label;
		}
		if (!empty($taxonomy->labels->name)) {
			$candidates[] = $taxonomy->labels->name;
		}
		if (!empty($taxonomy->labels->singular_name)) {
			$candidates[] = $taxonomy->labels->singular_name;
		}

		foreach ($candidates as $candidate) {
			if (in_array(strtolower($candidate), $labels, true)) {
				$matches[] = $slug;
				break;
			}
		}
	}

	return array_values(array_unique($matches));
}

function hts_get_season_taxonomy() {
	static $cached = null;

	if ($cached !== null) {
		return $cached;
	}

	$taxonomy_exists = function_exists('taxonomy_exists') ? 'taxonomy_exists' : null;
	$season_taxonomies = $taxonomy_exists
		? array_values(array_filter([
			'season',
			'seasons',
		], $taxonomy_exists))
		: [];

	if (empty($season_taxonomies)) {
		$season_taxonomies = hts_find_taxonomies_by_label(['Season', 'Seasons']);
	}

	$season_taxonomies = array_values(array_unique(array_filter(array_map('sanitize_key', (array) $season_taxonomies))));
	$cached = $season_taxonomies[0] ?? '';

	return $cached;
}

function hts_sort_season_terms_desc(array $terms) {
	usort($terms, static function ($term_a, $term_b) {
		$a_label = $term_a instanceof WP_Term ? ($term_a->name ?: $term_a->slug) : '';
		$b_label = $term_b instanceof WP_Term ? ($term_b->name ?: $term_b->slug) : '';
		$a_year  = (int) preg_replace('/\D/', '', $a_label);
		$b_year  = (int) preg_replace('/\D/', '', $b_label);

		if ($a_year === $b_year) {
			return strcasecmp($b_label, $a_label);
		}

		return ($a_year < $b_year) ? 1 : -1;
	});

	return $terms;
}

function hts_get_season_terms_sorted($taxonomy) {
	$taxonomy = sanitize_key($taxonomy);
	if (!$taxonomy || !function_exists('taxonomy_exists') || !taxonomy_exists($taxonomy)) {
		return [];
	}

	$terms = get_terms([
		'taxonomy'   => $taxonomy,
		'hide_empty' => false,
	]);

	if (is_wp_error($terms) || empty($terms)) {
		return [];
	}

	return hts_sort_season_terms_desc($terms);
}

function hts_get_requested_season_param() {
	if (isset($_GET['hts_season']) && !is_array($_GET['hts_season'])) {
		return sanitize_text_field(wp_unslash($_GET['hts_season']));
	}

	if (isset($_GET['season']) && !is_array($_GET['season'])) {
		return sanitize_text_field(wp_unslash($_GET['season']));
	}

	return '';
}

function hts_get_sport_season_terms($sport_slug, $post_types, $season_taxonomy = '') {
	$sport_slug = sanitize_title($sport_slug);
	if (!$sport_slug) {
		return [];
	}

	$season_taxonomy = $season_taxonomy !== '' ? $season_taxonomy : hts_get_season_taxonomy();
	$season_taxonomy = sanitize_key($season_taxonomy);
	if (!$season_taxonomy || !function_exists('taxonomy_exists') || !taxonomy_exists($season_taxonomy)) {
		return [];
	}

	$post_types = array_values(array_unique(array_filter(array_map('sanitize_key', (array) $post_types))));
	if (empty($post_types)) {
		return [];
	}

	$sport_term = get_term_by('slug', $sport_slug, 'sport');
	if (!$sport_term instanceof WP_Term) {
		return [];
	}

	$post_ids = get_posts([
		'post_type'              => $post_types,
		'posts_per_page'         => -1,
		'fields'                 => 'ids',
		'no_found_rows'          => true,
		'update_post_meta_cache' => false,
		'update_post_term_cache' => false,
		'tax_query'              => [
			[
				'taxonomy' => 'sport',
				'field'    => 'term_id',
				'terms'    => $sport_term->term_id,
			],
		],
	]);

	if (empty($post_ids)) {
		return [];
	}

	$terms = wp_get_object_terms($post_ids, $season_taxonomy);
	if (is_wp_error($terms) || empty($terms)) {
		return [];
	}

	return hts_sort_season_terms_desc($terms);
}

function hts_get_latest_season_slug_for_sport($sport_slug, $post_types, $season_taxonomy = '', $season_terms = null) {
	$season_terms = $season_terms !== null
		? $season_terms
		: hts_get_sport_season_terms($sport_slug, $post_types, $season_taxonomy);

	$latest = $season_terms[0] ?? null;
	return $latest instanceof WP_Term ? $latest->slug : '';
}

function hts_get_latest_season_for_sport($sport_term, $season_taxonomy, $post_types) {
	$sport_slug = $sport_term instanceof WP_Term ? $sport_term->slug : sanitize_title($sport_term);
	if (!$sport_slug) {
		return '';
	}

	$season_taxonomy = $season_taxonomy !== '' ? $season_taxonomy : hts_get_season_taxonomy();
	return hts_get_latest_season_slug_for_sport($sport_slug, $post_types, $season_taxonomy);
}

function hts_get_sport_season_context($sport_term, $post_types) {
	$season_taxonomy = hts_get_season_taxonomy();
	if (!$season_taxonomy) {
		return [
			'taxonomy' => '',
			'terms'    => [],
			'selected' => '',
		];
	}

	$terms = hts_get_season_terms_sorted($season_taxonomy);
	$selected = hts_get_requested_season_param();

	$sport_slug = $sport_term instanceof WP_Term ? $sport_term->slug : sanitize_title($sport_term);
	$available_terms = $sport_slug ? hts_get_sport_season_terms($sport_slug, $post_types, $season_taxonomy) : [];
	$available_slugs = [];

	if (!empty($available_terms)) {
		foreach ($available_terms as $available_term) {
			if ($available_term instanceof WP_Term && $available_term->slug) {
				$available_slugs[] = $available_term->slug;
			}
		}
	}

	if ($selected !== '' && !empty($available_slugs) && !in_array($selected, $available_slugs, true)) {
		$selected = '';
	}

	if ($selected !== '' && empty($available_slugs)) {
		$selected = '';
	}

	if ($selected === '') {
		$selected = $sport_slug
			? hts_get_latest_season_slug_for_sport($sport_slug, $post_types, $season_taxonomy, $available_terms)
			: '';
	}

	return [
		'taxonomy' => $season_taxonomy,
		'terms'    => $terms,
		'selected' => $selected,
	];
}

function hts_get_hidden_taxonomy_slug_candidates() {
	return [
		'season',
		'seasons',
		'recruiting-class',
		'recruiting_class',
		'recruiting-classes',
		'recruiting_classes',
		'recruitingclass',
		'opponent',
		'opponents',
		'position',
		'positions',
	];
}

function hts_taxonomy_label_matches_hidden_targets(array $args) {
	$targets = [
		'season',
		'seasons',
		'recruiting class',
		'recruiting classes',
		'opponent',
		'opponents',
		'position',
		'positions',
	];

	$candidates = [];
	if (!empty($args['label'])) {
		$candidates[] = $args['label'];
	}
	if (!empty($args['labels']['name'])) {
		$candidates[] = $args['labels']['name'];
	}
	if (!empty($args['labels']['singular_name'])) {
		$candidates[] = $args['labels']['singular_name'];
	}

	foreach ($candidates as $candidate) {
		$candidate = strtolower(trim((string) $candidate));
		if ($candidate && in_array($candidate, $targets, true)) {
			return true;
		}
	}

	return false;
}

function hts_filter_taxonomy_editor_args($args, $taxonomy, $object_type) {
	$taxonomy = sanitize_key($taxonomy);
	if (!$taxonomy) {
		return $args;
	}

	$slug_targets = hts_get_hidden_taxonomy_slug_candidates();
	if (!in_array($taxonomy, $slug_targets, true) && !hts_taxonomy_label_matches_hidden_targets((array) $args)) {
		return $args;
	}

	$target_post_types = ['game-recaps', 'game', 'player-profiles', 'recruit', 'recruits'];
	$object_types = array_map('sanitize_key', (array) $object_type);
	if (!empty($object_types) && empty(array_intersect($object_types, $target_post_types))) {
		return $args;
	}

	$args['meta_box_cb'] = false;
	$args['show_in_quick_edit'] = false;
	$args['show_in_rest'] = false;

	return $args;
}
add_filter('register_taxonomy_args', 'hts_filter_taxonomy_editor_args', 10, 3);

function hts_get_hidden_taxonomy_meta_box_map() {
	$map = [];
	$add = function ($taxonomy, array $post_types) use (&$map) {
		$taxonomy = sanitize_key($taxonomy);
		if (!$taxonomy) {
			return;
		}

		$post_types = array_values(array_unique(array_filter(array_map('sanitize_key', $post_types))));
		if (empty($post_types)) {
			return;
		}

		if (function_exists('post_type_exists')) {
			$post_types = array_values(array_filter($post_types, 'post_type_exists'));
			if (empty($post_types)) {
				return;
			}
		}

		$existing = isset($map[$taxonomy]) ? $map[$taxonomy] : [];
		$map[$taxonomy] = array_values(array_unique(array_merge($existing, $post_types)));
	};

	$taxonomy_exists = function_exists('taxonomy_exists') ? 'taxonomy_exists' : null;

	$season_taxonomies = $taxonomy_exists
		? array_values(array_filter([
			'season',
			'seasons',
		], $taxonomy_exists))
		: [];

	if (empty($season_taxonomies)) {
		$season_taxonomies = hts_find_taxonomies_by_label(['Season', 'Seasons']);
	}

	foreach ($season_taxonomies as $taxonomy) {
		$add($taxonomy, ['player-profiles', 'game-recaps', 'game']);
	}

	$recruiting_taxonomies = $taxonomy_exists
		? array_values(array_filter([
			'recruiting-class',
			'recruiting_class',
			'recruiting-classes',
			'recruiting_classes',
			'recruitingclass',
		], $taxonomy_exists))
		: [];

	if (empty($recruiting_taxonomies)) {
		$recruiting_taxonomies = hts_find_taxonomies_by_label(['Recruiting Class', 'Recruiting Classes']);
	}

	foreach ($recruiting_taxonomies as $taxonomy) {
		$add($taxonomy, ['recruit', 'recruits', 'player-profiles']);
	}

	$opponent_taxonomies = $taxonomy_exists
		? array_values(array_filter([
			'opponent',
			'opponents',
		], $taxonomy_exists))
		: [];

	if (empty($opponent_taxonomies)) {
		$opponent_taxonomies = hts_find_taxonomies_by_label(['Opponent', 'Opponents']);
	}

	foreach ($opponent_taxonomies as $taxonomy) {
		$add($taxonomy, ['game-recaps', 'game']);
	}

	$position_taxonomies = $taxonomy_exists
		? array_values(array_filter([
			'position',
			'positions',
		], $taxonomy_exists))
		: [];

	if (empty($position_taxonomies)) {
		$position_taxonomies = hts_find_taxonomies_by_label(['Position', 'Positions']);
	}

	foreach ($position_taxonomies as $taxonomy) {
		$add($taxonomy, ['player-profiles']);
	}

	return apply_filters('hts_hidden_taxonomy_meta_boxes', $map);
}

function hts_remove_hidden_taxonomy_meta_boxes($post_type, $post) {
	if (!function_exists('remove_meta_box')) {
		return;
	}

	$map = hts_get_hidden_taxonomy_meta_box_map();
	foreach ($map as $taxonomy => $post_types) {
		if (!in_array($post_type, $post_types, true)) {
			continue;
		}

		foreach (['side', 'normal'] as $context) {
			remove_meta_box($taxonomy . 'div', $post_type, $context);
			remove_meta_box('tagsdiv-' . $taxonomy, $post_type, $context);
		}
	}
}

add_action('add_meta_boxes', 'hts_remove_hidden_taxonomy_meta_boxes', 99, 2);

function hts_disable_hidden_taxonomy_quick_edit() {
	if (!function_exists('get_current_screen')) {
		return;
	}

	$screen = get_current_screen();
	if (!$screen || $screen->base !== 'edit' || empty($screen->post_type)) {
		return;
	}

	if (!function_exists('taxonomy_exists')) {
		return;
	}

	$map = hts_get_hidden_taxonomy_meta_box_map();
	foreach ($map as $taxonomy => $post_types) {
		if (!in_array($screen->post_type, $post_types, true)) {
			continue;
		}
		if (!taxonomy_exists($taxonomy)) {
			continue;
		}

		global $wp_taxonomies;
		if (isset($wp_taxonomies[$taxonomy])) {
			$wp_taxonomies[$taxonomy]->show_in_quick_edit = false;
		}
	}
}
add_action('current_screen', 'hts_disable_hidden_taxonomy_quick_edit');
/**
 * Enqueue custom header styles after parent Blocksy assets.
 */
function hts_enqueue_header_styles() {
	$header_css = get_stylesheet_directory() . '/styles/header.css';

	if ( file_exists( $header_css ) ) {
		wp_enqueue_style(
			'hts-header',
			get_stylesheet_directory_uri() . '/styles/header.css',
			[ 'hts-child' ],
			filemtime( $header_css )
		);
	}
}
add_action( 'wp_enqueue_scripts', 'hts_enqueue_header_styles', 20 );

/**
 * Retrieve the endpoints used for sport taxonomy subviews.
 *
 * @return array<string,string> Map of internal view slugs to endpoint slugs.
 */
function hts_get_sport_view_endpoints() {
    $endpoints = [
        'news'      => 'news',
        'recruiting' => 'recruiting',
        'standings' => 'standings',
        'roster'    => 'roster',
        'schedule'  => 'schedule',
        'staff'     => 'staff',
        'class'     => 'class-breakdown',
    ];

    return apply_filters('hts_sport_view_endpoints', $endpoints);
}

/**
 * Determine the rewrite mask for sport taxonomy endpoints.
 *
 * @return int
 */
function hts_get_taxonomy_endpoint_mask() {
    if (defined('EP_TAXONOMY')) {
        return EP_TAXONOMY;
    }

    if (defined('EP_ALL_ARCHIVES')) {
        return EP_ALL_ARCHIVES;
    }

    return EP_ALL;
}

/**
 * Register rewrite endpoints for sport taxonomy subviews.
 */
function hts_register_sport_view_endpoints() {
    $endpoints = hts_get_sport_view_endpoints();

    foreach (array_unique(array_filter($endpoints)) as $endpoint_slug) {
        add_rewrite_endpoint($endpoint_slug, hts_get_taxonomy_endpoint_mask());
    }
}

add_action('init', 'hts_register_sport_view_endpoints');

add_action('after_switch_theme', function () {
    hts_register_sport_view_endpoints();
    flush_rewrite_rules();
});

/**
 * Register custom query vars used for sport subviews.
 *
 * @param array $vars
 * @return array
 */
function hts_register_query_vars($vars) {
    $vars[] = 'hts_view';
    return $vars;
}
add_filter('query_vars', 'hts_register_query_vars');

/**
 * Add custom rewrite rules for sport subviews.
 *
 * Maps sport/{sport-slug}/roster and /schedule to index.php?sport={slug}&hts_view={view}.
 */
function hts_register_sport_rewrite_rules() {
    $taxonomy  = get_taxonomy('sport');
    $base_slug = $taxonomy && !empty($taxonomy->rewrite['slug']) ? trim($taxonomy->rewrite['slug'], '/') : 'sport';

    $roster_pattern           = sprintf('^%s/([^/]+)/roster/?$', preg_quote($base_slug, '/'));
    $schedule_pattern         = sprintf('^%s/([^/]+)/schedule/?$', preg_quote($base_slug, '/'));
    $recruiting_pattern       = sprintf('^%s/([^/]+)/Recruiting/?$', preg_quote($base_slug, '/'));
    $recruiting_pattern_lower = sprintf('^%s/([^/]+)/recruiting/?$', preg_quote($base_slug, '/'));
    $recruiting_page_pattern  = sprintf('^%s/([^/]+)/Recruiting/page/([0-9]+)/?$', preg_quote($base_slug, '/'));
    $recruiting_page_lower    = sprintf('^%s/([^/]+)/recruiting/page/([0-9]+)/?$', preg_quote($base_slug, '/'));

    add_rewrite_rule($recruiting_page_pattern, 'index.php?sport=$matches[1]&hts_view=recruiting&paged=$matches[2]', 'top');
    add_rewrite_rule($recruiting_page_lower, 'index.php?sport=$matches[1]&hts_view=recruiting&paged=$matches[2]', 'top');
    add_rewrite_rule($roster_pattern, 'index.php?sport=$matches[1]&hts_view=roster', 'top');
    add_rewrite_rule($schedule_pattern, 'index.php?sport=$matches[1]&hts_view=schedule', 'top');
    add_rewrite_rule($recruiting_pattern, 'index.php?sport=$matches[1]&hts_view=recruiting', 'top');
    add_rewrite_rule($recruiting_pattern_lower, 'index.php?sport=$matches[1]&hts_view=recruiting', 'top');
}
add_action('init', 'hts_register_sport_rewrite_rules');
/**
 * Canonicalize sport recruiting URLs to the capitalized endpoint.
 */
function hts_canonicalize_sport_recruiting_url() {
    if (!is_tax('sport')) {
        return;
    }

    $view = sanitize_key((string) get_query_var('hts_view'));
    if ($view !== 'recruiting' && !get_query_var('recruiting')) {
        return;
    }

    $sport_term = get_queried_object();
    if (!$sport_term instanceof WP_Term) {
        return;
    }

    $base_url = get_term_link($sport_term);
    if (is_wp_error($base_url)) {
        return;
    }

    $paged = max(1, (int) get_query_var('paged'), (int) get_query_var('page'));
    $canonical = trailingslashit($base_url) . 'Recruiting/';

    if ($paged > 1) {
        $canonical = trailingslashit($canonical . 'page/' . $paged);
    }

    $request_uri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';
    $path = $request_uri ? parse_url($request_uri, PHP_URL_PATH) : '';

    if (!$path || stripos($path, '/recruiting') === false) {
        return;
    }

    if (strpos($path, '/Recruiting') !== false) {
        return;
    }

    $target = $canonical;
    if (!empty($_GET)) {
        $target = add_query_arg(wp_unslash($_GET), $target);
    }

    wp_safe_redirect($target, 301);
    exit;
}
add_action('template_redirect', 'hts_canonicalize_sport_recruiting_url');


/**
 * Flush rewrite rules once after adding new sport subviews.
 */
function hts_maybe_flush_sport_rewrite_rules() {
    $option_key = 'hts_sport_view_rewrite_version';
    $version    = 'recruiting-2026-01-01';

    if (get_option($option_key) === $version) {
        return;
    }

    hts_register_sport_view_endpoints();
    hts_register_sport_rewrite_rules();
    flush_rewrite_rules();
    update_option($option_key, $version, false);
}
add_action('init', 'hts_maybe_flush_sport_rewrite_rules');


/**
 * Ensure sport taxonomy archives use the news post type allowlist.
 */
add_action('pre_get_posts', function ($query) {
	if (is_admin() || !$query->is_main_query()) {
		return;
	}

	if ($query->is_tax('sport')) {
		$view = sanitize_key((string) $query->get('hts_view'));
		$is_recruiting_view = ($view === 'recruiting') || (bool) $query->get('recruiting');
		if ($is_recruiting_view) {
			$posts_per_page = 12;
			$query->set('post_type', 'recruit');
			$query->set('posts_per_page', $posts_per_page);
			$query->set('posts_per_archive_page', $posts_per_page);
			$query->set('orderby', 'title');
			$query->set('order', 'ASC');
			$query->set('hts_recruiting_sort', true);
			return;
		}

		$news_post_types = function_exists('hts_get_news_post_types')
			? hts_get_news_post_types()
			: ['post', 'game-recaps'];
		$query->set('post_type', $news_post_types);
	}
}, 99);

// === Image sizes for cards & heroes ===
add_action('after_setup_theme', function () {
	add_image_size('hts-hero', 1600, 900, true);
	add_image_size('hts-card', 640, 400, true);
	add_image_size('card-thumb', 480, 320, true);
	add_image_size('hts-recruit-card', 600, 750, false);
	add_image_size('hero-wide', 1600, 900, true);
	add_theme_support('post_thumbnails');
});

// === Single post ads sidebar (2/3 + 1/3 layout) ===
add_action('widgets_init', function () {
	register_sidebar([
		/* translators: sidebar name shown in Appearance > Widgets */
		'name'          => __('Single Post Ads Sidebar', 'hts-child'),
		'id'            => 'single-post-ads',
		/* translators: sidebar description for single post ad widgets */
		'description'   => __('Sidebar used on single post pages for Google Ads / Advanced Ads.', 'hts-child'),
		'before_widget' => '<div id="%1$s" class="ct-widget %2$s">',
		'after_widget'  => '</div>',
		'before_title'  => '<h3 class="ct-widget-title">',
		'after_title'   => '</h3>',
	]);
});

// === PublishPress Authors: hide box on podcast singles ===
add_filter('pp_multiple_authors_filter_should_display_author_box', function ($display) {
	if ($display && is_singular('post') && has_category('podcast')) {
		return false;
	}

	return $display;
});

// === Sport sub-navigation menu locations ===
add_action('after_setup_theme', function () {
	register_nav_menus([
		'football_subnav'          => __('Football Subnav', 'hts-child'),
		'mens_basketball_subnav'   => __('Men\'s Basketball Subnav', 'hts-child'),
		'womens_basketball_subnav' => __('Women\'s Basketball Subnav', 'hts-child'),
		'baseball_subnav'          => __('Baseball Subnav', 'hts-child'),
		'softball_subnav'          => __('Softball Subnav', 'hts-child'),
	]);
});

/**
 * Render a sport-specific sub-navigation menu.
 *
 * @param string|WP_Term $sport Sport slug or term object.
 * @param array         $args  Optional config (controls_html).
 */
function hts_render_sport_subnav($sport, array $args = []) {
	$sport_slug = $sport instanceof WP_Term ? $sport->slug : sanitize_title($sport);
	if (!$sport_slug) {
		return;
	}

	$sport_term = $sport instanceof WP_Term ? $sport : get_term_by('slug', $sport_slug, 'sport');

	$location_map = apply_filters('hts_sport_subnav_locations', [
		'football'          => 'football_subnav',
		'mbb'               => 'mens_basketball_subnav',
		'wbb'               => 'womens_basketball_subnav',
		'bsb'               => 'baseball_subnav',
		'sfb'               => 'softball_subnav',
	]);

	$location = $location_map[$sport_slug] ?? '';
	if (!$location || !has_nav_menu($location)) {
		return;
	}

	global $wp;
	$current_url = home_url(add_query_arg([], $wp->request));
	$current_url = trailingslashit($current_url);

	$season_param = hts_get_requested_season_param();

	$base_url = '';
	if ($sport_term instanceof WP_Term) {
		$base_url = get_term_link($sport_term);
		$base_url = is_wp_error($base_url) ? '' : trailingslashit($base_url);
	}

	$roster_url     = $base_url ? trailingslashit($base_url . 'roster') : '';
	$schedule_url   = $base_url ? trailingslashit($base_url . 'schedule') : '';
	$recruiting_url = $base_url ? trailingslashit($base_url . 'Recruiting') : '';
	$roster_url_with_season = ($season_param && $roster_url) ? add_query_arg('hts_season', $season_param, $roster_url) : $roster_url;
	$is_roster_view = (get_query_var('hts_view') === 'roster') || (bool) get_query_var('roster');
	$is_recruiting_view = (get_query_var('hts_view') === 'recruiting') || (bool) get_query_var('recruiting');

	$recruiting_sports = apply_filters('hts_sport_recruiting_subnav_slugs', [
		'football',
		'mbb',
		'wbb',
		'mens-basketball',
		'womens-basketball',
	]);

	$show_recruiting = in_array($sport_slug, $recruiting_sports, true);
	$controls_html = isset($args['controls_html']) ? (string) $args['controls_html'] : '';

	$items_filter = function ($items, $args) use ($location, $base_url, $roster_url, $schedule_url, $roster_url_with_season, $recruiting_url, $is_roster_view, $is_recruiting_view, $show_recruiting) {
		if ($args->theme_location !== $location) {
			return $items;
		}

		$build_item = function ($url, $label, $is_active) {
			$classes = ['menu-item', 'roster-nav-item'];
			if ($is_active) {
				$classes[] = 'active';
				$classes[] = 'current-menu-item';
			}
		
			return sprintf(
				'<li class="%s"><a href="%s">%s</a></li>',
				esc_attr(implode(' ', $classes)),
				esc_url($url),
				esc_html($label)
			);
		};

		if (!$base_url || !preg_match_all('/<li[^>]*>.*?<\/li>/s', $items, $matches)) {
			$items_out = $items;
			if ($roster_url && stripos($items_out, esc_url($roster_url)) === false) {
				$items_out .= $build_item($roster_url_with_season, __('Roster', 'hts-child'), $is_roster_view);
			}
			if ($show_recruiting && $recruiting_url && stripos($items_out, esc_url($recruiting_url)) === false) {
				$items_out .= $build_item($recruiting_url, __('Recruiting', 'hts-child'), $is_recruiting_view);
			}
			return $items_out;
		}

		$normalize_path = static function ($url) {
			$path = wp_parse_url($url, PHP_URL_PATH);
			if (!$path) {
				return '';
			}
			return trailingslashit(strtolower($path));
		};

		$expected_paths = [
			'news'      => $normalize_path($base_url),
			'roster'    => $normalize_path($roster_url),
			'schedule'  => $normalize_path($schedule_url),
			'recruiting'=> $show_recruiting ? $normalize_path($recruiting_url) : '',
		];

		$items_by_key = [
			'news'      => '',
			'roster'    => '',
			'schedule'  => '',
			'recruiting'=> '',
		];
		$remaining = [];

		foreach ($matches[0] as $item_html) {
			$href = '';
			if (preg_match('/href=["\']([^"\']+)["\']/i', $item_html, $href_match)) {
				$href = $href_match[1];
			}
			$path = $href ? $normalize_path($href) : '';
			$matched = false;
			foreach ($expected_paths as $key => $expected_path) {
				if ($expected_path && $path === $expected_path) {
					$items_by_key[$key] = $item_html;
					$matched = true;
					break;
				}
			}
			if (!$matched) {
				$remaining[] = $item_html;
			}
		}

		if (!$items_by_key['roster'] && $roster_url) {
			$items_by_key['roster'] = $build_item($roster_url_with_season, __('Roster', 'hts-child'), $is_roster_view);
		}

		if ($show_recruiting && !$items_by_key['recruiting'] && $recruiting_url) {
			$items_by_key['recruiting'] = $build_item($recruiting_url, __('Recruiting', 'hts-child'), $is_recruiting_view);
		}

		$order = ['news', 'roster', 'schedule', 'recruiting'];
		$ordered_items = '';
		foreach ($order as $key) {
			if ($key === 'recruiting' && !$show_recruiting) {
				continue;
			}
			if (!empty($items_by_key[$key])) {
				$ordered_items .= $items_by_key[$key];
			}
		}

		if (!empty($remaining)) {
			$ordered_items .= implode('', $remaining);
		}

		return $ordered_items;
	};

	$active_filter = function ($classes, $item) use ($current_url, $roster_url, $recruiting_url, $is_roster_view, $is_recruiting_view) {
		$item_url = trailingslashit($item->url);
		$current  = ($item_url === $current_url || strpos($current_url, $item_url) === 0);

		if ($is_roster_view && $roster_url && strtolower($item_url) === strtolower(trailingslashit($roster_url))) {
			$current = true;
		}

		if ($is_recruiting_view && $recruiting_url && strtolower($item_url) === strtolower(trailingslashit($recruiting_url))) {
			$current = true;
		}

		if ($current) {
			$classes[] = 'active';
			$classes[] = 'current-menu-item';
		}

		return $classes;
	};

	$link_filter = function ($atts, $item) use ($season_param, $roster_url, $schedule_url) {
		if (!$season_param || empty($item->url)) {
			return $atts;
		}

		$normalize_path = static function ($url) {
			$path = wp_parse_url($url, PHP_URL_PATH);
			if (!$path) {
				return '';
			}
			return trailingslashit(strtolower($path));
		};

		$item_path = $normalize_path($item->url);
		$roster_path = $roster_url ? $normalize_path($roster_url) : '';
		$schedule_path = $schedule_url ? $normalize_path($schedule_url) : '';

		if ($item_path && ($item_path === $roster_path || $item_path === $schedule_path)) {
			$atts['href'] = add_query_arg('hts_season', $season_param, $item->url);
		}

		return $atts;
	};

	add_filter('wp_nav_menu_items', $items_filter, 10, 2);
	add_filter('nav_menu_css_class', $active_filter, 10, 2);
	add_filter('nav_menu_link_attributes', $link_filter, 10, 2);

	$menu_output = wp_nav_menu([
		'theme_location'  => $location,
		'menu_class'      => 'hts-sport-subnav__list',
		'depth'           => 1,
		'fallback_cb'     => false,
		'container'       => false,
		'echo'            => false,
	]);

	remove_filter('wp_nav_menu_items', $items_filter, 10);
	remove_filter('nav_menu_css_class', $active_filter, 10);
	remove_filter('nav_menu_link_attributes', $link_filter, 10);

	if (!$menu_output) {
		return;
	}

	echo '<nav class="hts-sport-subnav">';
	echo '<div class="hts-sport-subnav__row">';
	echo $menu_output;
	if ($controls_html !== '') {
		echo '<div class="hts-sport-subnav__controls">' . $controls_html . '</div>';
	}
	echo '</div>';
	echo '</nav>';
}



/**
 * Render sport schedule sidebar block.
 *
 * @param string|WP_Term $sport Sport slug or term.
 */
function hts_render_sport_schedule_sidebar($sport) {
	$sport_slug = $sport instanceof WP_Term ? $sport->slug : sanitize_title($sport);
	if (!$sport_slug) {
		return;
	}

	$sport_term = $sport instanceof WP_Term ? $sport : get_term_by('slug', $sport_slug, 'sport');
	$sport_name = $sport_term instanceof WP_Term ? $sport_term->name : ucwords(str_replace('-', ' ', $sport_slug));
	$year       = current_time('Y');

	$post_type = post_type_exists('game') ? 'game' : 'game-recaps';

	$query_args = [
		'post_type'      => $post_type,
		'posts_per_page' => 8,
		'orderby'        => 'meta_value',
		'order'          => 'ASC',
		'meta_key'       => 'game_date',
		'meta_type'      => 'DATE',
		'tax_query'      => [
			[
				'taxonomy' => 'sport',
				'field'    => 'slug',
				'terms'    => $sport_slug,
			],
		],
	];

	$query = new WP_Query($query_args);

	$full_schedule_url = '';
	if ($sport_term instanceof WP_Term) {
		$link = get_term_link($sport_term);
		if (!is_wp_error($link)) {
			$full_schedule_url = trailingslashit($link) . 'schedule/';
		}
	}

	get_template_part(
		'partials/sport/sidebar',
		'schedule',
		[
			'query'             => $query,
			'sport_name'        => $sport_name,
			'year'              => $year,
			'full_schedule_url' => $full_schedule_url,
		]
	);

	wp_reset_postdata();
}

// === Sports taxonomy/category sync ===
require_once get_stylesheet_directory() . '/inc/sports-taxonomy-sync.php';

// === Preload first hero image for LCP ===
add_action('wp_head', function() {
	if (!is_front_page()) {
		return;
	}

	// Query first featured post for preload
	$news_post_types = function_exists('hts_get_news_post_types')
		? hts_get_news_post_types()
		: ['post', 'game-recaps'];
	$hero_args = [
		'tag'                 => 'featured',
		'post_type'           => $news_post_types,
		'posts_per_page'      => 1,
		'fields'              => 'ids',
		'no_found_rows'       => true,
		'ignore_sticky_posts' => true,
	];

	$hero_query = new WP_Query($hero_args);

	if ($hero_query->have_posts()) {
		$post_id = $hero_query->posts[0];
		if (has_post_thumbnail($post_id)) {
			$image_id = get_post_thumbnail_id($post_id);
			$image_url = wp_get_attachment_image_url($image_id, 'hero-wide');

			if ($image_url) {
				echo '<link rel="preload" as="image" href="' . esc_url($image_url) . '" fetchpriority="high">' . "\n";
			}
		}
	}
	wp_reset_postdata();
}, 5);

// === Customizer: Front Page Top Ad ===
add_action('customize_register', function($wp_customize) {
    // Add Front Page Section
    $wp_customize->add_section('hts_front_page', [
        'title'    => __('Front Page Settings', 'hts-child'),
        'priority' => 30,
    ]);

    // Top Ad Shortcode
    $wp_customize->add_setting('hts_top_ad_shortcode', [
        'default'           => '',
        'sanitize_callback' => 'wp_kses_post',
        'transport'         => 'refresh',
    ]);

    $wp_customize->add_control('hts_top_ad_shortcode', [
        'label'       => __('Top Ad Shortcode', 'hts-child'),
        'description' => __('Enter an Advanced Ads or Ad Inserter shortcode (e.g., [adinserter block="1"]). Leave empty to show fallback banner.', 'hts-child'),
        'section'     => 'hts_front_page',
        'type'        => 'textarea',
    ]);

    // Fallback Banner Image
    $wp_customize->add_setting('hts_top_ad_fallback_image', [
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ]);

    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'hts_top_ad_fallback_image', [
        'label'       => __('Fallback Banner Image', 'hts-child'),
        'description' => __('Upload a banner image (970�250 recommended). Shown if no shortcode is provided.', 'hts-child'),
        'section'     => 'hts_front_page',
    ]));

    // Fallback Banner Link
    $wp_customize->add_setting('hts_top_ad_fallback_link', [
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ]);

    $wp_customize->add_control('hts_top_ad_fallback_link', [
        'label'       => __('Fallback Banner Link URL', 'hts-child'),
        'description' => __('Optional URL for fallback banner (opens in new tab).', 'hts-child'),
        'section'     => 'hts_front_page',
        'type'        => 'url',
    ]);

    // Bottom Ad Shortcode
    $wp_customize->add_setting('hts_bottom_ad_shortcode', [
        'default'           => '',
        'sanitize_callback' => 'wp_kses_post',
        'transport'         => 'refresh',
    ]);

    $wp_customize->add_control('hts_bottom_ad_shortcode', [
        'label'       => __('Bottom Ad Shortcode', 'hts-child'),
        'description' => __('Enter an Advanced Ads or Ad Inserter shortcode (e.g., [adinserter block="2"]). Leave empty to show fallback banner.', 'hts-child'),
        'section'     => 'hts_front_page',
        'type'        => 'textarea',
    ]);

    // Bottom Ad Fallback Banner Image
    $wp_customize->add_setting('hts_bottom_ad_fallback_image', [
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ]);

    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'hts_bottom_ad_fallback_image', [
        'label'       => __('Bottom Ad Fallback Banner Image', 'hts-child'),
        'description' => __('Upload a banner image (970�250 recommended). Shown if no shortcode is provided.', 'hts-child'),
        'section'     => 'hts_front_page',
    ]));

    // Bottom Ad Fallback Banner Link
    $wp_customize->add_setting('hts_bottom_ad_fallback_link', [
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ]);

    $wp_customize->add_control('hts_bottom_ad_fallback_link', [
        'label'       => __('Bottom Ad Fallback Banner Link URL', 'hts-child'),
        'description' => __('Optional URL for fallback banner (opens in new tab).', 'hts-child'),
        'section'     => 'hts_front_page',
        'type'        => 'url',
    ]);

    // Column Ad Shortcode
    $wp_customize->add_setting('hts_column_ad_shortcode', [
        'default'           => '',
        'sanitize_callback' => 'wp_kses_post',
        'transport'         => 'refresh',
    ]);

    $wp_customize->add_control('hts_column_ad_shortcode', [
        'label'        => __('Column Ad Shortcode', 'hts-child'),
        'description'  => __('Enter an Advanced Ads or Ad Inserter shortcode (e.g., [adinserter block="3"]). Leave empty to show fallback banner.', 'hts-child'),
        'section'      => 'hts_front_page',
        'type'         => 'textarea',
    ]);

    // Column Ad Fallback Banner Image
    $wp_customize->add_setting('hts_column_ad_fallback_image', [
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ]);

    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'hts_column_ad_fallback_image', [
        'label'       => __('Column Ad Fallback Banner Image', 'hts-child'),
        'description' => __('Upload a banner image (300x600 recommended). Shown if no shortcode is provided.', 'hts-child'),
        'section'     => 'hts_front_page',
    ]));

    // Column Ad Fallback Banner Link
    $wp_customize->add_setting('hts_column_ad_fallback_link', [
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ]);

    $wp_customize->add_control('hts_column_ad_fallback_link', [
        'label'       => __('Bottom Ad Fallback Banner Link URL', 'hts-child'),
        'description' => __('Optional URL for fallback banner (opens in new tab).', 'hts-child'),
        'section'     => 'hts_front_page',
        'type'        => 'url',
    ]);

    // Center Banner Ad (behind basketball row)
    $wp_customize->add_setting('hts_center_ad_shortcode', [
        'default'           => '',
        'sanitize_callback' => 'wp_kses_post',
        'transport'         => 'refresh',
    ]);

    $wp_customize->add_control('hts_center_ad_shortcode', [
        'label'       => __('Center Banner Ad Shortcode', 'hts-child'),
        'description' => __('Optional shortcode for the center banner behind the basketball row.', 'hts-child'),
        'section'     => 'hts_front_page',
        'type'        => 'textarea',
    ]);

    $wp_customize->add_setting('hts_center_ad_fallback_image', [
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ]);

    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'hts_center_ad_fallback_image', [
        'label'       => __('Center Banner Fallback Image', 'hts-child'),
        'description' => __('Upload a wide banner image (e.g., 1200x300) shown if no shortcode is provided.', 'hts-child'),
        'section'     => 'hts_front_page',
    ]));

    $wp_customize->add_setting('hts_center_ad_fallback_link', [
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ]);

    $wp_customize->add_control('hts_center_ad_fallback_link', [
        'label'       => __('Center Banner Fallback Link URL', 'hts-child'),
        'description' => __('Optional URL for the center banner (opens in new tab).', 'hts-child'),
        'section'     => 'hts_front_page',
        'type'        => 'url',
    ]);

    // Column Ads 2-5 (right rail)
    $column_ad_slots = [
        2 => __('Column Ad 2', 'hts-child'),
        3 => __('Column Ad 3', 'hts-child'),
        4 => __('Column Ad 4', 'hts-child'),
        5 => __('Column Ad 5', 'hts-child'),
    ];

    foreach ($column_ad_slots as $slot => $label) {
        $shortcode_key     = "hts_column_ad_{$slot}_shortcode";
        $fallback_image_key = "hts_column_ad_{$slot}_fallback_image";
        $fallback_link_key  = "hts_column_ad_{$slot}_fallback_link";

        $wp_customize->add_setting($shortcode_key, [
            'default'           => '',
            'sanitize_callback' => 'wp_kses_post',
            'transport'         => 'refresh',
        ]);

        $wp_customize->add_control($shortcode_key, [
            'label'       => sprintf(__('%s Shortcode', 'hts-child'), $label),
            'description' => __('Enter an Advanced Ads or Ad Inserter shortcode. Leave empty to use the fallback image.', 'hts-child'),
            'section'     => 'hts_front_page',
            'type'        => 'textarea',
        ]);

        $wp_customize->add_setting($fallback_image_key, [
            'default'           => '',
            'sanitize_callback' => 'esc_url_raw',
        ]);

        $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, $fallback_image_key, [
            'label'       => sprintf(__('%s Fallback Image', 'hts-child'), $label),
            'description' => __('Upload a banner image (300x600 recommended). Shown if no shortcode is provided.', 'hts-child'),
            'section'     => 'hts_front_page',
        ]));

        $wp_customize->add_setting($fallback_link_key, [
            'default'           => '',
            'sanitize_callback' => 'esc_url_raw',
        ]);

        $wp_customize->add_control($fallback_link_key, [
            'label'       => sprintf(__('%s Fallback Link URL', 'hts-child'), $label),
            'description' => __('Optional URL for the fallback banner (opens in new tab).', 'hts-child'),
            'section'     => 'hts_front_page',
            'type'        => 'url',
        ]);
    }
});

// === Hook: Render Top Ad Section ===
add_action('hts_front_page_top_ad', function(...$args) {
    get_template_part('template-parts/front-page/top-ad');
});

// === Hook: Render Bottom Ad Section ===
add_action('hts_front_page_bottom_ad', function(...$args) {
    get_template_part('template-parts/front-page/bottom-ad');
});

// === Hook: Render Hero Section ===
add_action('hts_front_page_hero', function(...$args) {
    get_template_part('template-parts/front-page/hero');
});

// === Hook: Render Main Content Section ===
add_action('hts_front_page_main_content', function(...$args) {
    get_template_part('template-parts/front-page/main-content');
});

// === Hook: Render Sidebar Section ===
add_action('hts_front_page_sidebar', function(...$args) {
    get_template_part('template-parts/front-page/sidebar');
});

// === Enqueue Load More JS ===
add_action('wp_enqueue_scripts', function() {
    if (!is_front_page()) {
        return;
    }

    $script_path = get_stylesheet_directory() . '/js/load-more.js';
    if (file_exists($script_path)) {
        wp_enqueue_script(
            'hts-load-more',
            get_stylesheet_directory_uri() . '/js/load-more.js',
            [],
            filemtime($script_path),
            true
        );
    }
}, 20);

// === Kinsta Cache Compatibility ===
// Ensure front page is cacheable (no auth cookies, no bypasses)
add_action('send_headers', function() {
	if (is_front_page() && !is_user_logged_in() && !wp_doing_ajax()) {
		// Send cache-friendly headers (use WordPress API, not raw header())
		header('Cache-Control: public, max-age=3600, s-maxage=3600');
	}
}, 10);

// === Dashboard Widget: Content Guide ===
add_action('wp_dashboard_setup', 'hts_register_content_guide_widget');

function hts_register_content_guide_widget() {
	if (!hts_user_can_view_content_guide_widget()) {
		return;
	}

	wp_add_dashboard_widget(
		'hts_editor_guide',
		'Houston News – Content Guide',
		'hts_render_dashboard_widget'
	);
}

function hts_user_can_view_content_guide_widget() {
	if (current_user_can('edit_others_posts') || current_user_can('manage_options')) {
		return true;
	}

	$allow_authors = apply_filters('hts_content_guide_allow_authors', false);
	return $allow_authors && current_user_can('edit_posts');
}

function hts_get_content_guide_config() {
	$sport_taxonomy = function_exists('hts_get_sport_taxonomy_slug')
		? hts_get_sport_taxonomy_slug()
		: 'sport';
	$sport_taxonomy = sanitize_key($sport_taxonomy);

	$sport_terms = [
		'Baseball',
		'Football',
		'Softball',
		"Men's Basketball",
		"Women's Basketball",
	];

	$config = [
		'version' => '2025.12',
		'sections' => [
			[
				'title' => 'Global Rules',
				'type' => 'list',
				'items' => [
					'Use only approved post categories: Shows, Video News, Recruiting, University Releases, Writer\'s Room, Premium.',
					'Apply the Sports taxonomy to Posts, Games, Player Profiles, and Recruits.',
					'Allowed Sports terms: ' . implode(', ', $sport_terms) . '.',
					'Premium content requires the Premium category and Paid Memberships Pro "Require Membership".',
				],
			],
			[
				'title' => 'Classification Matrix',
				'type' => 'matrix',
				'rows' => [
					[
						'label' => 'Category',
						'value' => 'Content type/series (Shows, Video News, Recruiting, University Releases, Writer\'s Room, Premium).',
					],
					[
						'label' => 'Sports taxonomy',
						'value' => 'Sport/team classification for Posts, Games, Player Profiles, and Recruits.',
					],
					[
						'label' => 'Tags',
						'value' => 'Series, campaigns, and Featured hero selections.',
					],
				],
			],
			[
				'title' => 'Post Type Standards',
				'type' => 'subsections',
				'sections' => [
					[
						'title' => 'Regular Posts',
						'items' => [
							'Choose a primary category from the approved list.',
							'Add a Sports taxonomy term when the story is sport-specific.',
							'Use tags for series, events, or Featured placement.',
						],
					],
					[
						'title' => 'Games / Game Recaps',
						'items' => [
							'Use the Game Recaps post type.',
							'Assign the correct Sports taxonomy term.',
							'Include opponent details and score in the content.',
						],
					],
					[
						'title' => 'Player Profiles',
						'items' => [
							'Use the Player Profiles post type.',
							'Assign the correct Sports taxonomy term.',
							'Include position/class details and a featured image.',
						],
					],
					[
						'title' => 'Recruits',
						'items' => [
							'Use the Recruits post type.',
							'Assign the correct Sports taxonomy term.',
							'Use the Recruiting category when appropriate.',
						],
					],
				],
			],
			[
				'title' => 'Modules / Feeds',
				'type' => 'subsections',
				'sections' => [
					[
						'title' => 'Hero slider',
						'items' => [
							'Tag posts or game recaps with Featured.',
							'The hero slider shows the 5 most recent featured items.',
						],
					],
					[
						'title' => 'Shows module',
						'items' => [
							'Assign the Shows category to show-related posts.',
							'The Shows module pulls recent items from the Shows category.',
						],
					],
				],
			],
			[
				'title' => 'Featured Image Sizes',
				'type' => 'list',
				'items' => [
					'Hero: 1600 x 900',
					'Cards: 480 x 320',
					'Shows: 150 x 150',
				],
			],
			[
				'title' => 'Author Checklist',
				'type' => 'list',
				'items' => [
					'Add a featured image at the recommended size.',
					'Assign a primary category.',
					'Assign a Sports taxonomy term when applicable.',
					'Add tags for series or Featured placement.',
					'If Premium, enable Paid Memberships Pro "Require Membership".',
					'Preview the post and verify links.',
				],
			],
		],
		'quick_links' => [
			[
				'label' => 'All Posts',
				'url' => admin_url('edit.php'),
			],
			[
				'label' => 'Add New Post',
				'url' => admin_url('post-new.php'),
			],
			[
				'label' => 'Game Recaps',
				'url' => admin_url('edit.php?post_type=game-recaps'),
			],
			[
				'label' => 'Add Game Recap',
				'url' => admin_url('post-new.php?post_type=game-recaps'),
			],
			[
				'label' => 'Player Profiles',
				'url' => admin_url('edit.php?post_type=player-profiles'),
			],
			[
				'label' => 'Add Player Profile',
				'url' => admin_url('post-new.php?post_type=player-profiles'),
			],
			[
				'label' => 'Recruits',
				'url' => admin_url('edit.php?post_type=recruit'),
			],
			[
				'label' => 'Add Recruit',
				'url' => admin_url('post-new.php?post_type=recruit'),
			],
			[
				'label' => 'Manage Tags',
				'url' => admin_url('edit-tags.php?taxonomy=post_tag'),
			],
			[
				'label' => 'Manage Categories',
				'url' => admin_url('edit-tags.php?taxonomy=category'),
			],
			[
				'label' => 'Manage Sports Terms',
				'url' => admin_url('edit-tags.php?taxonomy=' . $sport_taxonomy),
			],
		],
	];

	return apply_filters('hts_content_guide_config', $config);
}

function hts_render_dashboard_widget() {
	$guide = hts_get_content_guide_config();
	$sections = isset($guide['sections']) ? (array) $guide['sections'] : [];

	echo '<div class="hts-content-guide">';

	if (!empty($guide['version'])) {
		echo '<p class="description">' . esc_html('Version: ' . $guide['version']) . '</p>';
	}

	$section_count = count($sections);
	foreach ($sections as $index => $section) {
		if (!empty($section['title'])) {
			echo '<h3>' . esc_html($section['title']) . '</h3>';
		}

		$type = isset($section['type']) ? $section['type'] : 'list';

		if ('matrix' === $type) {
			hts_render_content_guide_matrix($section);
		} elseif ('subsections' === $type) {
			$subsections = isset($section['sections']) ? (array) $section['sections'] : [];
			foreach ($subsections as $subsection) {
				if (!empty($subsection['title'])) {
					echo '<h4>' . esc_html($subsection['title']) . '</h4>';
				}
				hts_render_content_guide_list($subsection);
			}
		} else {
			hts_render_content_guide_list($section);
		}

		if ($index < $section_count - 1) {
			echo '<hr />';
		}
	}

	if (!empty($guide['quick_links'])) {
		echo '<hr />';
		echo '<h3>' . esc_html('Quick Links') . '</h3>';
		echo '<ul class="ul-disc">';
		foreach ((array) $guide['quick_links'] as $link) {
			if (empty($link['label']) || empty($link['url'])) {
				continue;
			}
			echo '<li><a href="' . esc_url($link['url']) . '">' . esc_html($link['label']) . '</a></li>';
		}
		echo '</ul>';
	}

	echo '</div>';
}

function hts_render_content_guide_list(array $section) {
	$items = isset($section['items']) ? (array) $section['items'] : [];
	if (empty($items)) {
		return;
	}

	echo '<ul class="ul-disc">';
	foreach ($items as $item) {
		if ('' === (string) $item) {
			continue;
		}
		echo '<li>' . esc_html($item) . '</li>';
	}
	echo '</ul>';
}

function hts_render_content_guide_matrix(array $section) {
	$rows = isset($section['rows']) ? (array) $section['rows'] : [];
	if (empty($rows)) {
		return;
	}

	echo '<ul class="ul-disc">';
	foreach ($rows as $row) {
		if (empty($row['label']) || empty($row['value'])) {
			continue;
		}
		echo '<li><strong>' . esc_html($row['label']) . ':</strong> ' . esc_html($row['value']) . '</li>';
	}
	echo '</ul>';
}

/**
 * Filter: Customize Post Card Badge Taxonomies
 *
 * Allows developers to control which taxonomy terms are displayed as badges on post cards.
 *
 * @param array  $terms     Array of term objects to display. Empty by default.
 * @param int    $post_id   The post ID.
 * @param string $post_type The post type (e.g., 'post', 'game-recaps').
 * @return array Array of term objects to display as badges.
 *
 * Example usage in functions.php or custom plugin:
 *
 * // Use tags instead of categories for regular posts
 * add_filter('hts_post_card_badge_terms', function($terms, $post_id, $post_type) {
 *     if ($post_type === 'post') {
 *         $tags = get_the_terms($post_id, 'post_tag');
 *         if (!empty($tags) && !is_wp_error($tags)) {
 *             return [$tags[0]];
 *         }
 *     }
 *     return $terms;
 * }, 10, 3);
 *
 * // Use custom 'sport' taxonomy for game recaps
 * add_filter('hts_post_card_badge_terms', function($terms, $post_id, $post_type) {
 *     if ($post_type === 'game-recaps') {
 *         $sports = get_the_terms($post_id, 'sport');
 *         if (!empty($sports) && !is_wp_error($sports)) {
 *             return $sports;
 *         }
 *     }
 *     return $terms;
 * }, 10, 3);
 *
 * See template-parts/front-page/example-badge-filter.php for more examples.
 */

// === Tiny helpers to parse line lists (for Highlights / Social / Key Players) ===
function hts_lines($text){ $arr=preg_split('/\r\n|\r|\n/', (string)$text); return array_values(array_filter(array_map('trim', $arr))); }

// Key Players
$players = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) get_field('key_players'))));
foreach ($players as $line) {
  echo '<li>'.esc_html($line).'</li>';
}

// Highlights
$highlights = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) get_field('highlights'))));
foreach ($highlights as $line) {
  [$label, $url] = array_map('trim', array_pad(explode('|', $line, 2), 2, ''));
  if ($label && $url) {
    echo '<li><a href="'.esc_url($url).'" target="_blank" rel="noopener">'.esc_html($label).'</a></li>';
  }
}

// Social Links
$social = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) get_field('social_links'))));
foreach ($social as $line) {
  [$platform, $url] = array_map('trim', array_pad(explode('|', $line, 2), 2, ''));
  if ($platform && $url) {
    echo '<li><a href="'.esc_url($url).'" target="_blank" rel="noopener">'.esc_html($platform).'</a></li>';
  }
}



