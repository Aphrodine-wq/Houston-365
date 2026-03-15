<?php
get_template_part(
    'template-parts/sections/section-compact',
    null,
    [
        'section_title'  => __("Women's Basketball", 'hts-child'),
        'section_slug'   => 'wbb',
        'section_class'  => 'hts-section--wbb',
        'posts_per_page' => 3,
        'grid_class'     => 'hts-compact-grid--list',
        'show_view_more' => true,
        'show_header_link'=> false,
        'view_more_label'=> __("View all Women's Basketball stories", 'hts-child'),
        'sport_slug'     => 'wbb',
    ]
);
