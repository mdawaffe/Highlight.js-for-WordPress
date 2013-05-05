<?php

/*
 * Plugin Name: Highlight.js
 */

global $highlight_js_hashes;

add_action( 'wp_enqueue_scripts', 'highlight_js_enqueue' );

function highlight_js_enqueue() {
	wp_enqueue_script( 'highlight-js', plugins_url( 'highlight.js/highlight.pack.js', __FILE__ ), array(), '7.3-vassilevsky-php' . mt_rand(), true );
	wp_enqueue_style( 'highlight-js', plugins_url( 'highlight.js/styles/tomorrow-night-bright.css', __FILE__ ), array(), '7.3' );
}

add_filter( 'the_content', 'highlight_js_protect_code_blocks', 1 );

function highlight_js_protect_code_blocks( $content ) {
	global $highlight_js_protect_code_blocks;

	$old = $GLOBALS['shortcode_tags'];
	remove_all_shortcodes();
	add_shortcode( 'code', 'highlight_js_shortcode_protect' );

	$content = do_shortcode( $content );

	$GLOBALS['shortcode_tags'] = $old;
	return $content;
}

function highlight_js_shortcode_protect( $atts, $content = null ) {
	global $highlight_js_protect_code_blocks;

	$content = trim( $content, "\r\n" );
	$lang = isset( $atts['lang'] ) ? " lang='{$atts['lang']}'" : '';

	$hash = md5( $content );

	$highlight_js_protect_code_blocks[$hash] = $content;

	return "[codehash{$lang} hash='{$hash}']";
}

add_shortcode( 'codehash', 'highlight_js_shortcode' );

function highlight_js_shortcode( $atts ) {
	global $highlight_js_protect_code_blocks;

	if ( !isset( $atts['hash'] ) ) {
		return "ERROR";
	}

	if ( !isset( $highlight_js_protect_code_blocks[$atts['hash']] ) ) {
		return "ERROR";
	}

	$code = htmlspecialchars( $highlight_js_protect_code_blocks[$atts['hash']], ENT_QUOTES );

	$classes = array();
	if ( isset( $atts['lang'] ) ) {
		switch ( $atts['lang'] ) {
		case 'md' :
			$classes[] = 'markdown';
			break;
		case 'js' :
			$classes[] = 'javascript';
			break;
		case 'html' :
			$classes[] = 'xml';
			break;
		default :
			$classes[] = $atts['lang'];
		}
	}

	if ( $classes ) {
		$class = ' class="' . join( ' ', array_map( 'esc_attr', $classes ) ) . '"';
	} else {
		$class = '';
	}

	return "<pre><code{$class}>{$code}</code></pre>";
}
