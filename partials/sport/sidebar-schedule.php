<?php
/**
 * Sidebar schedule block for a sport.
 *
 * Expected $args:
 * - query (WP_Query): Games query (required)
 * - sport_name (string)
 * - year (string|int)
 * - full_schedule_url (string)
 */

$games            = $args['query'] ?? null;
$sport_name       = $args['sport_name'] ?? '';
$year             = $args['year'] ?? current_time('Y');
$full_schedule_url = $args['full_schedule_url'] ?? '';

if (!$games instanceof WP_Query) {
	return;
}

$format_date = static function ($raw_date) {
	if (!$raw_date) {
		return '';
	}

	// Support Ymd or Y-m-d.
	if (preg_match('/^\d{8}$/', $raw_date)) {
		$date = DateTime::createFromFormat('Ymd', $raw_date);
	} else {
		$date = DateTime::createFromFormat('Y-m-d', $raw_date);
	}

	return $date instanceof DateTime ? $date->format('M j') : '';
};

$format_time = static function ($raw_time) {
	if (!$raw_time) {
		return '';
	}

	$timestamp = strtotime($raw_time);
	return $timestamp ? date('g:i a', $timestamp) : '';
};
?>
<section class="panel sport-sidebar-schedule" aria-label="<?php echo esc_attr__('Schedule', 'hts-child'); ?>">
	<header class="sport-sidebar-schedule__header">
		<h2 class="sport-sidebar-schedule__title">
			<?php echo esc_html(trim($year . ' ' . $sport_name . ' Schedule')); ?>
		</h2>
		<?php if ($full_schedule_url) : ?>
			<a class="sport-sidebar-schedule__link" href="<?php echo esc_url($full_schedule_url); ?>">
				<?php esc_html_e('View Full Schedule', 'hts-child'); ?> &#8250;
			</a>
		<?php endif; ?>
	</header>

	<?php if ($games->have_posts()) : ?>
		<ul class="sport-sidebar-schedule__list">
			<?php while ($games->have_posts()) : $games->the_post(); ?>
				<?php
				$post_id   = get_the_ID();
				$home_away = get_post_meta($post_id, 'home_away', true);
				$opponent  = get_post_meta($post_id, 'opponent', true) ?: get_the_title();
				$game_date = get_post_meta($post_id, 'game_date', true);
				$game_time = get_post_meta($post_id, 'game_time', true);
				$home_score = get_post_meta($post_id, 'home_score', true);
				$away_score = get_post_meta($post_id, 'away_score', true);

				$is_numeric_score = is_numeric($home_score) && is_numeric($away_score);
				$game_url = $is_numeric_score ? get_permalink($post_id) : '';

				if ($home_away === 'home') {
					$our_score = $home_score;
					$opp_score = $away_score;
					$opponent_label = sprintf(__('vs %s', 'hts-child'), $opponent);
				} else {
					$our_score = $away_score;
					$opp_score = $home_score;
					$opponent_label = sprintf(__('at %s', 'hts-child'), $opponent);
				}

				$result_text = '';
				if ($is_numeric_score) {
					$is_win      = (int) $our_score > (int) $opp_score;
					$prefix      = $is_win ? __('W', 'hts-child') : __('L', 'hts-child');
					$result_text = sprintf('%s %s-%s', $prefix, $our_score, $opp_score);
				}

				$date_text = $format_date($game_date);
				$time_text = $format_time($game_time);
				?>
				<li class="sport-sidebar-schedule__item">
					<?php if ($game_url) : ?><a class="sport-sidebar-schedule__linkwrap" href="<?php echo esc_url($game_url); ?>"><?php endif; ?>
						<div class="sport-sidebar-schedule__opponent">
							<?php echo esc_html($opponent_label); ?>
						</div>
						<div class="sport-sidebar-schedule__meta">
							<span class="date"><?php echo esc_html(trim($date_text . ($time_text ? ' @ ' . $time_text : ''))); ?></span>
							<?php if ($result_text) : ?>
								<span class="result"><?php echo esc_html($result_text); ?></span>
							<?php endif; ?>
						</div>
					<?php if ($game_url) : ?></a><?php endif; ?>
				</li>
			<?php endwhile; ?>
		</ul>
	<?php else : ?>
		<p class="sport-sidebar-schedule__empty"><?php esc_html_e('No games scheduled yet.', 'hts-child'); ?></p>
	<?php endif; ?>
</section>
