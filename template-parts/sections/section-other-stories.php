<?php
$video_news_candidates = apply_filters('hts_video_news_slugs', ['video-news', 'video', 'videos']);
$video_news_slug       = '';

foreach ($video_news_candidates as $candidate_slug) {
    if (get_category_by_slug($candidate_slug)) {
        $video_news_slug = $candidate_slug;
        break;
    }
}

if (empty($video_news_slug)) {
    $video_news_slug = $video_news_candidates[0];
}

get_template_part(
    'template-parts/sections/section-compact',
    null,
    [
        'section_title'   => __('Video News', 'hts-child'),
        'section_slug'    => $video_news_slug,
        'section_class'   => 'hts-section--video-news',
        'posts_per_page'  => 6,
        'grid_class'      => 'hts-compact-grid--two hts-video-news-grid',
        'show_view_more'  => true,
        'show_header_link'=> false,
        'view_more_label' => __('View all Video News', 'hts-child'),
    ]
);
