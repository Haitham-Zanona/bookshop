<?php
woodmart_enqueue_inline_style( 'header-elements-base' );

if ( isset( $params['link'] ) ) {
	$link_attrs = '';

	if ( isset( $params['link']['url'] ) ) {
		$link_attrs = 'url:' . rawurlencode( $params['link']['url'] );
	}

	if ( ! empty( $params['link']['blank'] ) ) {
		$link_attrs .= '|target:_blank';
	}

	$params['link'] = $link_attrs;
}

if ( ! empty( $params['button_smooth_scroll'] ) ) {
	$params['button_smooth_scroll'] = 'yes';
}
if ( ! empty( $params['full_width'] ) ) {
	$params['full_width'] = 'yes';
}
if ( ! empty( $params['button_inline'] ) ) {
	$params['button_inline'] = 'yes';
}

if ( ! empty( $params['image'] ) ) {
	$params['icon_type'] = 'image';
}

if ( isset( $id ) ) {
	$params['wrapper_class'] = 'whb-' . $id;
}

if ( $params['bg_color'] || $params['bg_color_hover'] ) {
	$params['btn_classes']  = ' btn-scheme-' . $params['color_scheme'];
	$params['btn_classes'] .= ' btn-scheme-hover-' . $params['color_scheme_hover'];

	$params['bg_color']       = '';
	$params['bg_color_hover'] = '';
} else {
	$params['btn_classes'] = ' btn-color-' . $params['color'];
}

$params['is_header_button'] = true;

echo woodmart_shortcode_button( $params );
