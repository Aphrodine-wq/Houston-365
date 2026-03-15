<?php
/**
 * Sport taxonomy helpers and syncing to categories for custom post types.
 */

/**
 * Map sport values to term slugs/labels.
 *
 * @return array<string,array{label:string,synonyms?:string[]}>
 */
function hts_get_sport_term_map() {
    $map = [
        'football' => [
            'label'    => 'Football',
            'synonyms' => ['football', 'fb'],
        ],
        'mbb' => [
            'label'    => "Men's Basketball",
            'synonyms' => ["men's basketball", 'mens basketball', 'men’s basketball', 'mbb'],
        ],
        'wbb' => [
            'label'    => "Women's Basketball",
            'synonyms' => ["women's basketball", 'womens basketball', 'women’s basketball', 'wbb'],
        ],
        'bsb' => [
            'label'    => 'Baseball',
            'synonyms' => ['baseball', 'bsb'],
        ],
        'sfb' => [
            'label'    => 'Softball',
            'synonyms' => ['softball', 'sfb'],
        ],
        'oth' => [
            'label'    => 'Other Stories',
            'synonyms' => ['other', 'other stories', 'oth', 'misc'],
        ],
    ];

    $map = apply_filters('hts_sport_term_map', $map);
    // Backwards compatibility with the previous filter name.
    return apply_filters('hts_sport_category_map', $map);
}

/**
 * Return the slug for the custom sport taxonomy.
 *
 * @return string
 */
function hts_get_sport_taxonomy_slug() {
    return apply_filters('hts_sport_taxonomy_slug', 'sport');
}

/**
 * Normalize sport value for comparison.
 */
function hts_normalize_sport_value($value) {
    $normalized = strtolower(trim((string) $value));
    $normalized = str_replace(['’', '`', '´'], "'", $normalized);
    return $normalized;
}

/**
 * Resolve a sport value to a term slug from the mapping.
 *
 * @param string $sport_value Raw value from ACF/meta/terms.
 * @return string Empty string if no match.
 */
function hts_map_sport_value_to_term_slug($sport_value) {
    if (empty($sport_value)) {
        return '';
    }

    $map        = hts_get_sport_term_map();
    $normalized = hts_normalize_sport_value($sport_value);

    foreach ($map as $slug => $config) {
        $label     = isset($config['label']) ? hts_normalize_sport_value($config['label']) : '';
        $synonyms  = isset($config['synonyms']) ? array_map('hts_normalize_sport_value', (array) $config['synonyms']) : [];
        $candidates = array_filter(array_merge([$slug, $label], $synonyms));

        if (in_array($normalized, $candidates, true)) {
            return $slug;
        }
    }

    return '';
}

/**
 * Ensure sport taxonomy terms exist (runs once).
 */
function hts_ensure_sport_terms_exist() {
    $option_key = 'hts_sport_terms_seeded';

    if (get_option($option_key)) {
        return;
    }

    $taxonomy = hts_get_sport_taxonomy_slug();

    if (!$taxonomy || !taxonomy_exists($taxonomy)) {
        return;
    }

    $map        = hts_get_sport_term_map();
    $seeded_all = true;

    foreach ($map as $slug => $config) {
        $label = isset($config['label']) ? $config['label'] : $slug;
        $term  = get_term_by('slug', $slug, $taxonomy);

        if ($term instanceof WP_Term && !is_wp_error($term)) {
            continue;
        }

        $result = wp_insert_term($label, $taxonomy, ['slug' => $slug]);

        if (is_wp_error($result)) {
            if ('term_exists' === $result->get_error_code()) {
                continue;
            }
            $seeded_all = false;
        }
    }

    if ($seeded_all) {
        update_option($option_key, 1, false);
    }
}
add_action('after_switch_theme', 'hts_ensure_sport_terms_exist');

/**
 * Attach categories/tags to CPTs in case the CPTs don't declare them.
 */
function hts_register_categories_for_custom_posts() {
    $post_types = ['game-recaps', 'player-profiles'];

    foreach ($post_types as $type) {
        register_taxonomy_for_object_type('category', $type);
        register_taxonomy_for_object_type('post_tag', $type);
    }
}
add_action('init', 'hts_register_categories_for_custom_posts', 20);

/**
 * Fetch the sport value from ACF/meta/taxonomy.
 *
 * @param int $post_id
 * @return string
 */
function hts_get_sport_field_value($post_id) {
    $field_keys = apply_filters('hts_sport_field_keys', ['sport', 'sports', 'game_sport', 'player_sport']);

    if (function_exists('get_field')) {
        foreach ($field_keys as $key) {
            $value = get_field($key, $post_id);
            if (!empty($value)) {
                if (is_array($value)) {
                    if (isset($value['value'])) {
                        return (string) $value['value'];
                    }
                    return (string) reset($value);
                }
                return (string) $value;
            }
        }
    }

    foreach ($field_keys as $key) {
        $meta = get_post_meta($post_id, $key, true);
        if (!empty($meta)) {
            return (string) $meta;
        }
    }

    // Fallback: use custom taxonomy "sport" if assigned.
    $terms = wp_get_post_terms($post_id, 'sport');
    if (!empty($terms) && !is_wp_error($terms)) {
        $first = reset($terms);
        return $first->slug ?: $first->name;
    }

    return '';
}

/**
 * Sync the sport value to the mapped category.
 */
function hts_sync_sport_field_to_category($post_id, $post, $update) {
    $target_post_types = ['game-recaps', 'player-profiles'];

    if (!in_array($post->post_type, $target_post_types, true)) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    $map                  = hts_get_sport_term_map();
    $sport_value          = hts_get_sport_field_value($post_id);
    $resolved_category    = hts_map_sport_value_to_term_slug($sport_value);
    $sport_category_slugs = array_keys($map);

    // Gather existing non-sport categories to preserve them.
    $existing_terms      = get_the_category($post_id);
    $preserve_term_ids   = [];

    if (!empty($existing_terms) && !is_wp_error($existing_terms)) {
        foreach ($existing_terms as $term) {
            if (!in_array($term->slug, $sport_category_slugs, true)) {
                $preserve_term_ids[] = (int) $term->term_id;
            }
        }
    }

    if ($resolved_category) {
        $resolved_config = isset($map[$resolved_category]) ? $map[$resolved_category] : ['label' => $resolved_category];
        $term            = get_term_by('slug', $resolved_category, 'category');

        if (!$term || is_wp_error($term)) {
            $inserted = wp_insert_term(
                isset($resolved_config['label']) ? $resolved_config['label'] : $resolved_category,
                'category',
                ['slug' => $resolved_category]
            );
            if (!is_wp_error($inserted) && isset($inserted['term_id'])) {
                $term_id = (int) $inserted['term_id'];
            } else {
                return;
            }
        } else {
            $term_id = (int) $term->term_id;
        }

        $terms_to_set = array_unique(array_merge([$term_id], $preserve_term_ids));
        wp_set_post_terms($post_id, $terms_to_set, 'category', false);
    } else {
        // Remove sport categories but keep any non-sport categories.
        wp_set_post_terms($post_id, $preserve_term_ids, 'category', false);
    }
}
add_action('save_post', 'hts_sync_sport_field_to_category', 20, 3);
