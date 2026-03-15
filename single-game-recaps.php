<?php
/** Single template for Game Recaps */
get_header(); ?>
<div class="container stack game-recap-layout">
<?php while (have_posts()) : the_post();
    $date_raw = get_field('game_date');
    $time_raw = get_field('game_time');
    $date = $date_raw ? DateTime::createFromFormat('Ymd',$date_raw)->format('F j, Y'): '';
    $time = $time_raw ? date('g:i a', strtotime($time_raw)): '';
    $home_away = get_field('home_away');
    $opp = get_field('opponent');
    $venue = get_field('venue');
    $hs = get_field('home_score'); $as = get_field('away_score'); $final = get_field('final_score');
    $quote = get_field('featured_quote');
    $yt = get_field('youtube_url');
    $keys = hts_lines(get_field('key_players'));
    $sport_terms = wp_get_post_terms(get_the_ID(),'sport',['fields'=>'names']);
    $season = wp_get_post_terms(get_the_ID(),'season',['fields'=>'names']);
?>
    <article class="stack">
        <header class="stack">
            <?php echo hts_render_post_meta_line(get_the_ID(), 'meta'); ?>
            <h1 class="title"><?php the_title(); ?></h1>
            <?php if (has_post_thumbnail()): ?>
                <div class="hero"><?php the_post_thumbnail('hts-hero'); ?></div>
            <?php endif; ?>
        </header>

        <div class="grid grid-2">
            <section class="stack">
                <div class="panel stack">
                    <div class="score">
                        <?php 
                            if ($final) { echo esc_html($final); }
                            elseif ($hs !== '' && $as !== '') {
                                $homeLabel = ($home_away==='home') ? 'Houston' : $opp;
                                $awayLabel = ($home_away==='away') ? 'Houston' : $opp;
                                echo esc_html("$homeLabel $hs"); echo ' <span class="vs">-</span> '; echo esc_html("$as $awayLabel");
                            }
                        ?>
                    </div>
                    <div class="meta">
                        <?php echo esc_html($date . ($time ? " @ $time" : '')); ?>
                        <?php if($venue) echo ' &bull; '.esc_html($venue); ?>
                    </div>
                </div>

                <?php if ($quote): ?>
                    <blockquote><em><?php echo esc_html($quote); ?></em></blockquote>
                <?php endif; ?>

                <?php if (!empty($keys)): ?>
                    <div class="card game-recap-key-card">
                        <h3 class="game-recap-key-title">Key Players</h3>
                        <ul class="list">
                            <?php foreach ($keys as $line) echo '<li>'.esc_html($line).'</li>'; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <div class="stack">
                    <?php the_content(); ?>
                </div>

                <?php if ($yt): ?>
                    <div class="card game-recap-video-card">
                        <?php echo wp_oembed_get($yt) ?: '<a class="btn" href="'.esc_url($yt).'" target="_blank" rel="noopener">Watch on YouTube</a>'; ?>
                    </div>
                <?php endif; ?>
            </section>

            <aside class="stack">
                <div class="card game-recap-author-card">
                    <?php echo hts_render_post_meta_line(get_the_ID(), 'meta'); ?>
                    <div class="game-recap-tag-list">
                        <?php if($sport_terms) foreach($sport_terms as $s) echo '<span class="chip">'.esc_html($s).'</span>'; ?>
                        <?php if($season) foreach($season as $s) echo '<span class="chip">'.esc_html($s).'</span>'; ?>
                        <?php if($opp) echo '<span class="chip">'.esc_html($opp).'</span>'; ?>
                        <?php if($home_away) echo '<span class="chip">'.esc_html(ucfirst($home_away)).'</span>'; ?>
                    </div>
                </div>
                <?php if (has_post_thumbnail()): ?>
                    <div class="card hero"><?php the_post_thumbnail('hts-card'); ?></div>
                <?php endif; ?>
            </aside>
        </div>
    </article>
<?php endwhile; ?>
</div>
<?php get_footer(); ?>
