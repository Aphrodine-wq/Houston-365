<?php
get_template_part(
    'template-parts/sections/section-compact',
    null,
    [
        'section_title'  => __('Recruiting News', 'hts-child'),
        'section_slug'   => 'recruiting',
        'section_class'  => 'hts-section--recruiting',
        'posts_per_page' => 12,
        'grid_class'     => 'hts-compact-grid--three',
        'show_excerpt'   => true,
        'excerpt_length' => 18,
        'show_view_more' => true,
        'show_header_link'=> false,
        'view_more_label'=> __('View all Recruiting stories', 'hts-child'),
    ]
);
