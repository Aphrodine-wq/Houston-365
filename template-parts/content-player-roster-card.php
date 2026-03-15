<?php
/**
 * Player roster card
 *
 * Expects $args['post_id'] (optional, defaults to current post).
 */
$post_id = isset($args['post_id']) ? absint($args['post_id']) : get_the_ID();
$post_type = get_post_type($post_id);

$get_meta = function ($key) use ($post_id) {
	if (function_exists('get_field')) {
		$value = get_field($key, $post_id);
		if ($value !== null && $value !== '') {
			return $value;
		}
	}
	$value = get_post_meta($post_id, $key, true);
	return is_string($value) ? trim($value) : $value;
};

$number   = $get_meta('number');
$position = $get_meta('position');
$class    = $get_meta('class_year') ?: $get_meta('class');
$height   = $get_meta('height');
$weight   = $get_meta('weight');

$position_label = $position;
$meta_items = [];

$add_meta_item = function ($label, $value, $is_html = false) use (&$meta_items) {
	if ($value === null || $value === '') {
		return;
	}
	$meta_items[] = [
		'label' => $label,
		'value' => $value,
		'is_html' => $is_html,
	];
};

if ($post_type === 'recruit') {
	if ($position && $class) {
		$position_label = $position . ' / ' . $class;
	} elseif (!$position && $class) {
		$position_label = $class;
	}

	$add_meta_item(__('Height', 'hts-child'), $height);
	$add_meta_item(__('Weight', 'hts-child'), $weight);

	$rank_value = function_exists('hts_get_recruit_rank_value') ? hts_get_recruit_rank_value($post_id) : null;
	if ($rank_value === null) {
		$add_meta_item(__('Rank', 'hts-child'), __('NR', 'hts-child'));
	} else {
		$popcorn_icons = function_exists('hts_get_recruit_popcorn_icons')
			? hts_get_recruit_popcorn_icons($rank_value, ['class' => 'recruit-popcorn--small'])
			: '';

		if ($popcorn_icons) {
			$rank_markup = '<span class="recruit-popcorns recruit-popcorns--small">' . $popcorn_icons . '</span>';
			$add_meta_item(__('Rank', 'hts-child'), $rank_markup, true);
		} else {
			$add_meta_item(__('Rank', 'hts-child'), __('NR', 'hts-child'));
		}
	}

	$in_state_rating = $get_meta('in_state_rating');
	if ($in_state_rating === null || $in_state_rating === '') {
		$add_meta_item(__('In-State', 'hts-child'), __('N/A', 'hts-child'));
	} else {
		$add_meta_item(__('In-State', 'hts-child'), $in_state_rating);
	}
} else {
	$add_meta_item(__('Class', 'hts-child'), $class);
	$add_meta_item(__('Height', 'hts-child'), $height);
	$add_meta_item(__('Weight', 'hts-child'), $weight);
}

$card_classes = ['hts-card', 'roster-card'];
if ($post_type === 'recruit') {
	$card_classes[] = 'recruit-card';
}

$headshot = '';
$thumb_size = ($post_type === 'recruit') ? 'hts-recruit-card' : 'card-thumb';
if (has_post_thumbnail($post_id)) {
	$headshot = get_the_post_thumbnail($post_id, $thumb_size, ['class' => 'roster-card__image', 'loading' => 'lazy']);
} else {
	if (function_exists('hts_get_placeholder_image')) {
		$placeholder = hts_get_placeholder_image('card');
		$headshot    = '<img class="roster-card__image" src="' . esc_url($placeholder) . '" alt="" loading="lazy" />';
	}
}
?>
<article class="<?php echo esc_attr(implode(' ', $card_classes)); ?>" role="listitem">
	<a href="<?php echo esc_url(get_permalink($post_id)); ?>" class="roster-card__link" aria-label="<?php echo esc_attr(get_the_title($post_id)); ?>">
		<div class="hts-card-thumb roster-card__thumb">
			<?php echo $headshot; ?>
			<?php if ($number) : ?>
				<span class="roster-card__number">#<?php echo esc_html($number); ?></span>
			<?php endif; ?>
		</div>

		<div class="hts-card-inner roster-card__body">
			<div class="roster-card__header">
				<h3 class="roster-card__name"><?php echo esc_html(get_the_title($post_id)); ?></h3>
				<?php if ($position_label) : ?>
					<span class="roster-card__position"><?php echo esc_html($position_label); ?></span>
				<?php endif; ?>
			</div>

			<ul class="roster-card__meta">
				<?php foreach ($meta_items as $item) : ?>
					<li><span class="label"><?php echo esc_html($item['label']); ?></span><span class="value"><?php echo !empty($item['is_html']) ? wp_kses_post($item['value']) : esc_html($item['value']); ?></span></li>
				<?php endforeach; ?>
			</ul>
		</div>
	</a>
</article>
