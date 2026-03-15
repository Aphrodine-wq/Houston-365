<?php
/**
 * Example: Custom Badge Filter Implementation
 * 
 * This file demonstrates how to use the 'hts_post_card_badge_terms' filter
 * to customize which taxonomy terms are displayed as badges on post cards.
 * 
 * Add this code to your child theme's functions.php file or a custom plugin.
 */

// === Example 1: Use custom taxonomy for badges ===
add_filter('hts_post_card_badge_terms', function($terms, $post_id, $post_type) {
    
    // For game-recaps, show game type taxonomy instead
    if ($post_type === 'game-recaps') {
        $game_types = get_the_terms($post_id, 'game-type');
        if (!empty($game_types) && !is_wp_error($game_types)) {
            return $game_types;
        }
    }
    
    // For regular posts, use tags instead of categories
    if ($post_type === 'post') {
        $tags = get_the_terms($post_id, 'post_tag');
        if (!empty($tags) && !is_wp_error($tags)) {
            return [$tags[0]]; // Return only first tag
        }
    }
    
    return $terms;
}, 10, 3);


// === Example 2: Priority-based taxonomy display ===
add_filter('hts_post_card_badge_terms', function($terms, $post_id, $post_type) {
    
    // Define taxonomy priority order
    $taxonomy_priority = [
        'sport',          // Custom taxonomy: sport
        'season',         // Custom taxonomy: season
        'category',       // Default category
        'post_tag',       // Tags
    ];
    
    // Loop through taxonomies in priority order
    foreach ($taxonomy_priority as $taxonomy) {
        $taxonomy_terms = get_the_terms($post_id, $taxonomy);
        
        if (!empty($taxonomy_terms) && !is_wp_error($taxonomy_terms)) {
            return [$taxonomy_terms[0]]; // Return first term from highest priority taxonomy
        }
    }
    
    return $terms;
}, 10, 3);


// === Example 3: Conditional badge display based on custom field ===
add_filter('hts_post_card_badge_terms', function($terms, $post_id, $post_type) {
    
    // Check for custom badge override field
    $custom_badge = get_post_meta($post_id, 'custom_badge_label', true);
    
    if (!empty($custom_badge)) {
        // Create a fake term object for custom badge
        $fake_term = new stdClass();
        $fake_term->name = $custom_badge;
        $fake_term->slug = sanitize_title($custom_badge);
        
        return [$fake_term];
    }
    
    return $terms;
}, 10, 3);


// === Example 4: Sport-specific badges for game recaps ===
add_filter('hts_post_card_badge_terms', function($terms, $post_id, $post_type) {
    
    if ($post_type !== 'game-recaps') {
        return $terms;
    }
    
    // Get sport taxonomy
    $sports = get_the_terms($post_id, 'sport');
    
    if (!empty($sports) && !is_wp_error($sports)) {
        $sport = $sports[0];
        
        // Add custom class based on sport
        $sport->badge_class = 'badge-sport-' . $sport->slug;
        
        return [$sport];
    }
    
    return $terms;
}, 10, 3);


// === Example 5: Multiple badges (show category AND tag) ===
add_filter('hts_post_card_badge_terms', function($terms, $post_id, $post_type) {
    
    $badges = [];
    
    // Add category
    $categories = get_the_categories($post_id);
    if (!empty($categories)) {
        $badges[] = $categories[0];
    }
    
    // Add primary tag if set
    $primary_tag_id = get_post_meta($post_id, '_primary_tag', true);
    if ($primary_tag_id) {
        $primary_tag = get_term($primary_tag_id, 'post_tag');
        if (!is_wp_error($primary_tag)) {
            $badges[] = $primary_tag;
        }
    }
    
    return !empty($badges) ? $badges : $terms;
}, 10, 3);
