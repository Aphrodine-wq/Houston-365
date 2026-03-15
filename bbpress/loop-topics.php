<?php

/**
 * Topics Loop
 *
 * @package bbPress
 * @subpackage Theme
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

do_action( 'bbp_template_before_topics_loop' ); ?>

<div class="h365-board-header" aria-hidden="true">
	<span class="h365-board-header-status">
		<span class="screen-reader-text"><?php esc_html_e( 'Status', 'bbpress' ); ?></span>
	</span>
	<span class="h365-board-header-main"><?php esc_html_e( 'Thread Title / Author', 'bbpress' ); ?></span>
	<span class="h365-board-header-stats"><?php esc_html_e( 'Stats', 'bbpress' ); ?></span>
	<span class="h365-board-header-last"><?php esc_html_e( 'Last Post', 'bbpress' ); ?></span>
</div>

<ul id="bbp-forum-<?php bbp_forum_id(); ?>" class="bbp-topics h365-forum-board">
	<li class="bbp-header bbp-header--sr">
		<ul class="forum-titles">
			<li class="bbp-topic-title"><?php esc_html_e( 'Topic', 'bbpress' ); ?></li>
			<li class="bbp-topic-reply-count"><?php bbp_show_lead_topic()
				? esc_html_e( 'Replies', 'bbpress' )
				: esc_html_e( 'Posts',   'bbpress' );
			?></li>
			<li class="bbp-topic-freshness"><?php esc_html_e( 'Last Post', 'bbpress' ); ?></li>
		</ul>
	</li>

	<li class="bbp-body">

		<?php while ( bbp_topics() ) : bbp_the_topic(); ?>

			<?php bbp_get_template_part( 'loop', 'single-topic' ); ?>

		<?php endwhile; ?>

	</li>

	<li class="bbp-footer">
		<div class="tr">
			<p>
				<span class="td colspan<?php echo ( bbp_is_user_home() && ( bbp_is_favorites() || bbp_is_subscriptions() ) ) ? '5' : '4'; ?>">&nbsp;</span>
			</p>
		</div><!-- .tr -->
	</li>
</ul><!-- #bbp-forum-<?php bbp_forum_id(); ?> -->

<?php do_action( 'bbp_template_after_topics_loop' );
