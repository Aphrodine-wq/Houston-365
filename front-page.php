<?php
/**
 * Template Name: Front Page
 * Description: Semantic front page layout with ad zones, hero, content grid, and extensible hooks
 */

get_header();
?>

<div class="front-page-wrapper">
    <div class="hero-sidebar-wrapper">

        <div class="hero-stack">

            <section class="hero" aria-label="Featured Content">
                <?php
                /**
                 * Hook: hts_front_page_hero
                 * Render hero rotator, featured stories, or primary visual content
                 */
                do_action('hts_front_page_hero');

                // Fallback: load hero template part if hook is not used
                if (!has_action('hts_front_page_hero')) {
                    get_template_part('template-parts/front-page/hero');
                }
                ?>
            </section>

            <main class="main-content" role="main">
                <?php
                /**
                 * Hook: hts_front_page_main_content
                 * Render primary content: latest posts, featured sections, etc.
                 */
                do_action('hts_front_page_main_content');

                // Fallback: load main content template part
                if (!has_action('hts_front_page_main_content')) {
                    get_template_part('template-parts/front-page/main-content');
                }
                ?>
            </main>
        </div><!-- .hero-stack -->

        <aside class="sidebar" role="complementary">
            <?php
            /**
             * Hook: hts_front_page_sidebar
             * Render sidebar widgets, trending posts, newsletter signup, etc.
             */
            do_action('hts_front_page_sidebar');

            if (!has_action('hts_front_page_sidebar')) {
                get_template_part('template-parts/front-page/sidebar');
            }
            ?>
        </aside>
    </div><!-- .hero-sidebar-wrapper -->

    <section class="bottom-ad" aria-label="Advertisement">
        <?php
        /**
         * Hook: hts_front_page_bottom_ad
         * Render advertisement or sponsorship content below main content
         */
        do_action('hts_front_page_bottom_ad');
        ?>
    </section>
</div><!-- .front-page-wrapper -->

<?php
get_footer();
