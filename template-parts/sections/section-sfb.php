<?php
get_template_part(
    'template-parts/sections/section-compact',
    null,
    [
        'section_title'  => __('Softball', 'hts-child'),
        'section_slug'   => 'sfb',
        'section_class'  => 'hts-section--sfb',
        'posts_per_page' => 3,
        'grid_class'     => 'hts-compact-grid--list',
        'show_view_more' => true,
        'show_header_link'=> false,
        'view_more_label'=> __('View all Softball stories', 'hts-child'),
        'sport_slug'     => 'sfb',
    ]
);
