<?php get_header(); ?>
<div class="container stack game-recap-archive">
  <h1 class="title">Game Recaps</h1>

  <form method="get" class="card game-recap-archive-filter">
    <?php
      $sport_terms = get_terms(['taxonomy'=>'sport','hide_empty'=>true]);
      $season_terms= get_terms(['taxonomy'=>'season','hide_empty'=>true]);
      $qs = $_GET;
      $curSport = isset($qs['sport']) ? sanitize_text_field($qs['sport']) : '';
      $curSeason = function_exists('hts_get_requested_season_param')
        ? hts_get_requested_season_param()
        : (isset($qs['hts_season']) ? sanitize_text_field($qs['hts_season']) : (isset($qs['season']) ? sanitize_text_field($qs['season']) : ''));
    ?>
    <select name="sport" class="btn">
      <option value="">All Sports</option>
      <?php foreach($sport_terms as $t) printf('<option %s value="%s">%s</option>', selected($curSport,$t->slug,false), esc_attr($t->slug), esc_html($t->name)); ?>
    </select>
    <select name="hts_season" class="btn">
      <option value="">All Seasons</option>
      <?php foreach($season_terms as $t) printf('<option %s value="%s">%s</option>', selected($curSeason,$t->slug,false), esc_attr($t->slug), esc_html($t->name)); ?>
    </select>
    <button class="btn" type="submit">Filter</button>
  </form>

  <?php
    $taxq=[];
    if($curSport)  $taxq[]=['taxonomy'=>'sport','field'=>'slug','terms'=>$curSport];
    if($curSeason) $taxq[]=['taxonomy'=>'season','field'=>'slug','terms'=>$curSeason];
    $q = new WP_Query([
      'post_type'=>'game-recaps','paged'=>max(1,get_query_var('paged')),
      'tax_query'=>!empty($taxq)?$taxq:[],
    ]);
  ?>

  <div class="grid game-recap-archive-grid">
    <?php if($q->have_posts()): while($q->have_posts()): $q->the_post(); ?>
      <a href="<?php the_permalink(); ?>" class="card stack game-recap-archive-card">
        <div class="hero"><?php the_post_thumbnail('hts-card'); ?></div>
        <div class="game-recap-archive-card-body">
          <div class="meta"><?php echo get_the_date(); ?></div>
          <h3 class="game-recap-archive-card-title"><?php the_title(); ?></h3>
        </div>
      </a>
    <?php endwhile; wp_reset_postdata(); else: ?>
      <p>No recaps yet.</p>
    <?php endif; ?>
  </div>

  <div class="stack game-recap-archive-pagination"><?php the_posts_pagination(); ?></div>
</div>
<?php get_footer(); ?>
