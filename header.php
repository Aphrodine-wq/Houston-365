<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Blocksy
 */

?><!doctype html>
<html <?php language_attributes(); ?><?php echo blocksy_html_attr() ?>>
<head>
	<?php do_action('blocksy:head:start') ?>

	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, viewport-fit=cover">
	<link rel="profile" href="https://gmpg.org/xfn/11">

	<?php wp_head(); ?>
	<?php do_action('blocksy:head:end') ?>
</head>

<?php
	ob_start();
	blocksy_output_header();
	$global_header = ob_get_clean();
?>

<body <?php body_class(); ?> <?php echo blocksy_body_attr() ?>>

<?php
        if (function_exists('wp_body_open')) {
                wp_body_open();
        }
?>

<div id="main-container">

        <div class="hts-top-header">
                <div class="hts-top-header__inner">
                        <div class="hts-top-header__logo">
                                <?php if (function_exists('the_custom_logo') && has_custom_logo()) : ?>
                                        <?php the_custom_logo(); ?>
                                <?php else : ?>
                                        <a href="<?php echo esc_url(home_url('/')); ?>" class="hts-top-header__site-title"><?php bloginfo('name'); ?></a>
                                <?php endif; ?>
                        </div>

                        <div class="hts-top-header__ad">
                                <?php get_template_part('template-parts/front-page/top-ad'); ?>
                        </div>
                </div>
        </div>

        <?php
                do_action('blocksy:header:before');

                echo $global_header;

		do_action('blocksy:header:after');
		do_action('blocksy:content:before');
	?>

	<main <?php echo blocksy_main_attr() ?>>

		<?php
			do_action('blocksy:content:top');
			blocksy_before_current_template();
		?>
