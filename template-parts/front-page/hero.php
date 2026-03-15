<?php
/**
 * Template Part: Hero Slider
 * Displays featured posts and game recaps in a rotating hero slider
 */

// Query for Featured content
$news_post_types = function_exists('hts_get_news_post_types')
    ? hts_get_news_post_types()
    : ['post', 'game-recaps'];
$hero_args = [
    'tag'                 => 'featured',
    'post_type'           => $news_post_types,
    'posts_per_page'      => 5,
    'orderby'             => 'date',
    'order'               => 'DESC',
    'no_found_rows'       => true,
    'ignore_sticky_posts' => true,
];

$hero_query = new WP_Query($hero_args);

// If no featured posts, don't render hero at all (collapse gracefully)
if (!$hero_query->have_posts()) {
    wp_reset_postdata();
    return;
}

$post_count = $hero_query->post_count;
$is_static = ($post_count === 1);
?>

<div class="hero-container <?php echo $is_static ? 'hero-static' : 'hero-slider'; ?>" role="region" aria-label="Featured Stories">
    
    <div class="hero-track">
        <?php
        $index = 0;
        while ($hero_query->have_posts()) : $hero_query->the_post();
            $is_active = ($index === 0);
            
            // Image loading strategy: eager for first, lazy for rest (LCP optimization)
            $loading = ($index === 0) ? 'eager' : 'lazy';
            $fetchpriority = ($index === 0) ? 'high' : 'auto';
        ?>
        
        <article class="hero-slide<?php echo $is_active ? ' is-active' : ''; ?>" data-index="<?php echo esc_attr($index); ?>" aria-hidden="<?php echo $is_active ? 'false' : 'true'; ?>">
            
            <a href="<?php the_permalink(); ?>" class="hero-link" tabindex="<?php echo $is_active ? '0' : '-1'; ?>" aria-label="<?php echo esc_attr('Read: ' . get_the_title()); ?>">
                
                <div class="hero-media">
                    <?php if (has_post_thumbnail()) : ?>
                        <?php
                        the_post_thumbnail('hero-wide', [
                            'class'          => 'hero-image',
                            'loading'        => $loading,
                            'fetchpriority'  => $fetchpriority,
                            'decoding'       => 'async',
                            'alt'            => get_the_title(),
                        ]);
                        ?>
                    <?php endif; ?>
                    
                    <!-- Optional overlay gradient -->
                    <div class="hero-overlay"></div>
                </div>
                
                <div class="hero-content">

                    <?php if ($is_static) : ?>
                        <h1 class="hero-title"><?php the_title(); ?></h1>
                    <?php else : ?>
                        <h2 class="hero-title"><?php the_title(); ?></h2>
                    <?php endif; ?>
                    
                </div><!-- .hero-content -->
                
            </a><!-- .hero-link -->
            
        </article><!-- .hero-slide -->
        
        <?php
            $index++;
        endwhile;
        wp_reset_postdata();
        ?>
    </div><!-- .hero-track -->
    
    <?php if (!$is_static) : ?>
        
        <!-- Navigation Controls -->
        <div class="hero-controls" aria-label="Hero Navigation">
            <button class="hero-btn hero-prev" type="button" aria-label="Previous slide">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
            <button class="hero-btn hero-next" type="button" aria-label="Next slide">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>
        
        <!-- Dot Navigation -->
        <div class="hero-dots" role="tablist" aria-label="Slide navigation">
            <?php for ($i = 0; $i < $post_count; $i++) : ?>
                <button 
                    class="hero-dot<?php echo $i === 0 ? ' is-active' : ''; ?>" 
                    type="button"
                    role="tab"
                    aria-selected="<?php echo $i === 0 ? 'true' : 'false'; ?>"
                    aria-label="Go to slide <?php echo $i + 1; ?>"
                    data-index="<?php echo esc_attr($i); ?>"
                >
                </button>
            <?php endfor; ?>
        </div>
        
    <?php endif; ?>
    
</div><!-- .hero-container -->
