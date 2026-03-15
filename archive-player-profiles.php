<?php get_header(); ?>
<div class="container stack player-profile-archive">
    <h1 class="title">Roster</h1>

    <div class="grid player-profile-archive-grid">
        <?php if(have_posts()): while(have_posts()): the_post();
            $num=get_field('number'); $pos=get_field('position');
        ?>
            <a href="<?php the_permalink(); ?>" class="card player-profile-archive-card">
                <div class="hero"><?php the_post_thumbnail('hts-card'); ?></div>
                <div class="player-profile-archive-card-body">
                    <div class="player-profile-archive-card-header">
                        <h3 class="player-profile-archive-card-title"><?php the_title(); ?></h3>
                        <?php if($num) echo '<span class="chip">#'.esc_html($num).'</span>'; ?>
                    </div>
                    <?php if($pos) echo '<div class="meta">'.esc_html($pos).'</div>'; ?>
                </div>
            </a>
        <?php endwhile; else: ?>
            <p>No players found.</p>
        <?php endif; ?>
    </div>
    
    <div class="stack player-profile-archive-pagination"><?php the_posts_pagination(); ?></div>
</div>
<?php get_footer(); ?>