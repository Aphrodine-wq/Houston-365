<?php
$opinion_candidates = apply_filters('hts_opinion_slugs', ['writers-room', 'opinion', 'opinions', 'columns', 'column']);
$opinion_slug       = '';

foreach ($opinion_candidates as $candidate_slug) {
    if (get_category_by_slug($candidate_slug)) {
        $opinion_slug = $candidate_slug;
        break;
    }
}

if (empty($opinion_slug)) {
    $opinion_slug = $opinion_candidates[0];
}

get_template_part(
    'template-parts/sections/section-compact',
    null,
    [
        'section_title'   => __('Writers\' Room', 'hts-child'),
        'section_slug'    => $opinion_slug,
        'section_class'   => 'hts-section--opinion',
        'posts_per_page'  => 6,
        'grid_class'      => 'hts-compact-grid--two hts-opinion-grid',
        'show_view_more'  => true,
        'show_header_link'=> false,
        'view_more_label' => __('View all Writers\' Room posts', 'hts-child'),
        'hide_media'      => true,
    ]
);
