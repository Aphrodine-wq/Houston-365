<?php

/**
 * Topics Loop - Single
 *
 * @package bbPress
 * @subpackage Theme
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

?>

<?php
$topic_id          = bbp_get_topic_id();
$reply_count       = bbp_get_topic_reply_count( $topic_id, true );
$last_active_time  = bbp_get_topic_last_active_time( $topic_id );
$last_active_ts    = $last_active_time ? strtotime( $last_active_time ) : 0;
$current_ts        = current_time( 'timestamp' );
$new_window        = (int) apply_filters( 'hts_bbp_topic_new_window', 2 * HOUR_IN_SECONDS );

if ( ! $last_active_ts ) {
	$last_active_ts = (int) get_post_time( 'U', true, $topic_id );
}

$is_new      = $last_active_ts && ( $current_ts - $last_active_ts ) <= $new_window;
$time_since  = $last_active_ts ? human_time_diff( $last_active_ts, $current_ts ) : '';
$last_author = bbp_get_author_link(
	array(
		'post_id' => bbp_get_topic_last_active_id(),
		'size'    => 14,
		'type'    => 'name',
	)
);
?>

<ul id="bbp-topic-<?php bbp_topic_id(); ?>" <?php bbp_topic_class(); ?>>
	<li class="bbp-topic-avatar">
		<?php
		echo bbp_get_topic_author_link(
			array(
				'type' => 'avatar',
				'size' => 44,
			)
		);
		?>
	</li>

	<li class="bbp-topic-main">
		<div class="bbp-topic-heading">
			<div class="bbp-topic-title-wrap">
				<?php do_action( 'bbp_theme_before_topic_title' ); ?>

				<a class="bbp-topic-permalink" href="<?php bbp_topic_permalink(); ?>"><?php bbp_topic_title(); ?></a>

				<?php if ( $is_new ) : ?>
					<span class="bbp-topic-new"><?php esc_html_e( 'New', 'bbpress' ); ?></span>
				<?php endif; ?>

				<?php do_action( 'bbp_theme_after_topic_title' ); ?>

				<?php bbp_topic_pagination(); ?>
			</div>

			<?php if ( bbp_is_user_home() ) : ?>
				<div class="bbp-topic-actions">
					<?php if ( bbp_is_favorites() ) : ?>

						<span class="bbp-row-actions">

							<?php do_action( 'bbp_theme_before_topic_favorites_action' ); ?>

							<?php bbp_topic_favorite_link(
								array(
									'before'    => '',
									'favorite'  => '+',
									'favorited' => '&times;',
								)
							); ?>

							<?php do_action( 'bbp_theme_after_topic_favorites_action' ); ?>

						</span>

					<?php elseif ( bbp_is_subscriptions() ) : ?>

						<span class="bbp-row-actions">

							<?php do_action( 'bbp_theme_before_topic_subscription_action' ); ?>

							<?php bbp_topic_subscription_link(
								array(
									'before'      => '',
									'subscribe'   => '+',
									'unsubscribe' => '&times;',
								)
							); ?>

							<?php do_action( 'bbp_theme_after_topic_subscription_action' ); ?>

						</span>

					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>

		<?php do_action( 'bbp_theme_before_topic_meta' ); ?>

		<p class="bbp-topic-meta">
			<?php do_action( 'bbp_theme_before_topic_started_by' ); ?>

			<span class="bbp-topic-started-by">
				<?php
				printf(
					/* translators: %1$s: Topic author link */
					esc_html__( 'Started by %1$s', 'bbpress' ),
					bbp_get_topic_author_link(
						array(
							'size' => 14,
							'type' => 'name',
						)
					)
				);
				?>
			</span>

			<?php do_action( 'bbp_theme_after_topic_started_by' ); ?>
		</p>

		<?php do_action( 'bbp_theme_after_topic_meta' ); ?>

		<?php bbp_topic_row_actions(); ?>
	</li>

	<li class="bbp-topic-stats">
		<div class="bbp-topic-stat bbp-topic-stat-replies">
			<span class="bbp-topic-stat-count"><?php echo esc_html( number_format_i18n( $reply_count ) ); ?></span>
			<span class="bbp-topic-stat-label">
				<?php echo esc_html( _n( 'Reply', 'Replies', $reply_count, 'bbpress' ) ); ?>
			</span>
		</div>
	</li>

	<li class="bbp-topic-last-post">
		<div class="bbp-topic-last-activity">
			<span class="bbp-topic-last-replier"><?php echo wp_kses_post( $last_author ); ?></span>
			<span class="bbp-topic-last-verb"><?php esc_html_e( 'replied', 'bbpress' ); ?></span>
			<?php if ( $time_since ) : ?>
				<span class="bbp-topic-last-time"><?php echo esc_html( $time_since ); ?></span>
				<span class="bbp-topic-last-suffix"><?php esc_html_e( 'ago', 'bbpress' ); ?></span>
			<?php else : ?>
				<span class="bbp-topic-last-time"><?php esc_html_e( 'just now', 'bbpress' ); ?></span>
			<?php endif; ?>
		</div>
	</li>
</ul><!-- #bbp-topic-<?php bbp_topic_id(); ?> -->
