<?php
$post_id = $args['post_id'] ?? get_the_ID();
$num = get_field('number', $post_id);
$pos = get_field('position', $post_id);
?>
<a href ="<?php echo esc_url(get_permalink($post_id)); ?>" class="hts-card">
    <div class="hts-card-thumb">
        <?php echo get_the_post_thumbnail($post_id, 'card_thumb'); ?>
    </div>
    <div class="hts-card-inner">
        <div>
            <h3><?php echo esc_html(get_the_title($post_id)); ?></h3>
            <?php if($num) echo '<span class="hts-chip">#'.esc_html($num).'</span>'; ?>
        </div>
        <?php if($pos) echo '<div class="hts-meta">'.esc_html($pos).'</div>'; ?>
    </div>
</a>