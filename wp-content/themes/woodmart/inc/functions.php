<?php
use Elementor\Plugin;
use XTS\Registry;

if ( ! defined( 'WOODMART_THEME_DIR' ) ) {
	exit( 'No direct script access allowed' );
};

if ( ! function_exists( 'woodmart_get_opt' ) ) {
	function woodmart_get_opt( $slug = '', $default = false ) {
		global $woodmart_options, $xts_woodmart_options;

		$opt = $default;

		if ( isset( $xts_woodmart_options[ $slug ] ) ) {
			$opt = $xts_woodmart_options[ $slug ];

			return apply_filters( 'woodmart_option', $opt, $slug );
		}

		if ( isset( $woodmart_options[ $slug ] ) ) {
			$opt = $woodmart_options[ $slug ];

			return apply_filters( 'woodmart_option', $opt, $slug );
		}

		return apply_filters( 'woodmart_option', $opt, $slug );
	}
}

if ( ! function_exists( 'woodmart_is_opt_changed' ) ) {
	function woodmart_is_opt_changed( $slug ) {
		global $xts_woodmart_options;

		$global_options = get_option( 'xts-woodmart-options' );
		$current        = isset( $xts_woodmart_options[ $slug ] ) ? $xts_woodmart_options[ $slug ] : false;
		$global         = isset( $global_options[ $slug ] ) ? $global_options[ $slug ] : false;

		if ( is_array( $current ) && is_array( $global ) ) {
			foreach ( $current as $key => $data ) {
				if ( ! isset( $global[ $key ] ) || $data != $global[ $key ] ) {
					return true;
				}
			}
		} else {
			return (string) $current != (string) $global;
		}
	}
}

if ( ! function_exists( 'woodmart_page_ID' ) ) {
	function woodmart_page_ID() {
		return Registry::getInstance()->layout->get_page_id();
	}
}

if ( ! function_exists( 'woodmart_get_content_class' ) ) {
	function woodmart_get_content_class() {
		return Registry::getInstance()->layout->get_content_class();
	}
}

if ( ! function_exists( 'woodmart_get_sidebar_class' ) ) {
	function woodmart_get_sidebar_class() {
		return Registry::getInstance()->layout->get_sidebar_class();
	}
}

if ( ! function_exists( 'woodmart_get_page_layout' ) ) {
	function woodmart_get_page_layout() {
		return Registry::getInstance()->layout->get_page_layout();
	}
}

if ( ! function_exists( 'woodmart_get_main_container_class' ) ) {
	function woodmart_get_main_container_class() {
		return Registry::getInstance()->layout->get_main_container_class();
	}
}

if ( ! function_exists( 'woodmart_get_carousel_breakpoints' ) ) {
	/**
	 * Added global breakpoints for carousel config.
	 *
	 * @return array
	 */
	function woodmart_get_carousel_breakpoints() {
		return apply_filters(
			'woodmart_get_carousel_breakpoints',
			array(
				'1025'   => 'lg',
				'768.98' => 'md',
				'0'      => 'sm',
			)
		);
	}
}

if ( ! function_exists( 'woodmart_pjax_with_pagination_fix' ) ) {
	/**
	 * Fix for pagination with PJAX.
	 *
	 * @param string $link Link.
	 *
	 * @return false|string
	 */
	function woodmart_pjax_with_pagination_fix( $link ) {
		return remove_query_arg( '_pjax', $link );
	}

	add_filter( 'paginate_links', 'woodmart_pjax_with_pagination_fix' );
}

if ( ! function_exists( 'woodmart_is_css_encode' ) ) {
	function woodmart_is_css_encode( $data ) {
		return strlen( $data ) > 50;
	}
}

if( ! function_exists( 'woodmart_enqueue_gallery_script' ) ) {
	function woodmart_enqueue_gallery_script( $html5 ) {
		if ( woodmart_get_opt( 'single_post_justified_gallery' ) ) {
			woodmart_enqueue_js_library( 'magnific' );
			woodmart_enqueue_js_library( 'justified' );
			woodmart_enqueue_inline_style( 'justified' );
			woodmart_enqueue_js_script( 'mfp-popup' );
			woodmart_enqueue_inline_style( 'mfp-popup' );
		}

		return $html5;
	}

	add_filter( 'use_default_gallery_style', 'woodmart_enqueue_gallery_script' );
}

if( ! function_exists( 'woodmart_get_blog_shortcode_ajax' ) ) {
	function woodmart_get_blog_shortcode_ajax() {
		if( ! empty( $_POST['atts'] ) ) {
			$atts = woodmart_clean( $_POST['atts'] );
			$paged = ( empty( $_POST['paged'] ) ) ? 2 : sanitize_text_field( (int) $_POST['paged'] ) + 1;
			$atts['ajax_page'] = $paged;

			if ( ! empty( $atts['offset'] ) ) {
				$atts['offset'] = (int) $atts['offset'] + (int) $paged * (int) $atts['items_per_page'];
			}

			if ( isset( $atts['elementor'] ) && $atts['elementor'] ) {
				$data = woodmart_elementor_blog_template( $atts );
			} else {
				$data = woodmart_shortcode_blog( $atts );
			}

			wp_send_json( $data );

			die();
		}
	}
	add_action( 'wp_ajax_woodmart_get_blog_shortcode', 'woodmart_get_blog_shortcode_ajax' );
	add_action( 'wp_ajax_nopriv_woodmart_get_blog_shortcode', 'woodmart_get_blog_shortcode_ajax' );
}

if( ! function_exists( 'woodmart_get_portfolio_shortcode_ajax' ) ) {
	function woodmart_get_portfolio_shortcode_ajax() {
		if( ! empty( $_POST['atts'] ) ) {
			$atts = woodmart_clean( $_POST['atts'] );
			$paged = (empty($_POST['paged'])) ? 2 : sanitize_text_field( (int) $_POST['paged'] ) + 1;
			$atts['ajax_page'] = $paged;

			if ( isset( $atts['elementor'] ) && $atts['elementor'] ) {
				$data = woodmart_elementor_portfolio_template( $atts );
			} else {
				$data = woodmart_shortcode_portfolio( $atts );
			}

			wp_send_json( $data );

			die();
		}
	}

	add_action( 'wp_ajax_woodmart_get_portfolio_shortcode', 'woodmart_get_portfolio_shortcode_ajax' );
	add_action( 'wp_ajax_nopriv_woodmart_get_portfolio_shortcode', 'woodmart_get_portfolio_shortcode_ajax' );
}

if ( ! function_exists( 'woodmart_get_color_value' ) ) {
	function woodmart_get_color_value( $key, $default ) {
		$color = woodmart_get_opt( $key );

		if ( isset( $color['idle'] ) && $color['idle'] ) {
			return $color['idle'];
		} else {
			return $default;
		}
	}
}

if ( ! function_exists( 'woodmart_get_css_animation' ) ) {
	function woodmart_get_css_animation( $css_animation ) {
		$output = '';
		if ( $css_animation && $css_animation != 'none' ) {
			wp_enqueue_style( 'animate-css' );
			wp_enqueue_style( 'vc_animate-css' );

			woodmart_enqueue_js_library( 'waypoints' );
			woodmart_enqueue_js_script( 'animations-offset' );
			$output = ' wd-off-anim wpb_animate_when_almost_visible wpb_' . $css_animation . ' ' . $css_animation;

			$output .= ' wd-anim-name_' . $css_animation;
		}
		return $output;
	}
}

if( ! function_exists( 'woodmart_get_user_panel_params' ) ) {
	function woodmart_get_user_panel_params() {
		return apply_filters( 'woodmart_get_user_panel_params', array(
			array(
				'type' => 'textfield',
				'heading' => esc_html__( 'Title', 'woodmart' ),
				'param_name' => 'title',
			)
		));
	}
}

if( ! function_exists( 'woodmart_vc_get_link_attr' ) ) {
	function woodmart_vc_get_link_attr( $link ) {
		$link = ( '||' === $link ) ? '' : $link;
		$link = woodmart_vc_build_link( $link );
		return $link;
	}
}

if( ! function_exists( 'woodmart_get_link_attributes' ) ) {
	function woodmart_get_link_attributes( $link, $popup = false ) {
		//parse link
		$link = woodmart_vc_get_link_attr( $link );
		$use_link = false;
		if ( isset( $link['url'] ) && strlen( $link['url'] ) > 0 ) {
			$use_link = true;
			$a_href = apply_filters( 'woodmart_extra_menu_url', $link['url'] );
			if ( $popup ) $a_href = $link['url'];
			$a_title = $link['title'];
			$a_target = $link['target'];
			$a_rel    = $link['rel'];
		}

		$attributes = array();

		if ( $use_link ) {
			$attributes[] = 'href="' . trim( $a_href ) . '"';
			$attributes[] = 'title="' . esc_attr( trim( $a_title ) ) . '"';
			if ( ! empty( $a_target ) ) {
				$attributes[] = 'target="' . esc_attr( trim( $a_target ) ) . '"';
			}
			if ( ! empty( $a_rel ) ) {
				$attributes[] = 'rel="' . esc_attr( trim( $a_rel ) ) . '"';
			}
		}

		$attributes = implode( ' ', $attributes );

		return $attributes;
	}
}

if ( ! function_exists( 'woodmart_get_taxonomies_by_ids_autocomplete' ) ) {
	/**
	 * Autocomplete by taxonomies ids.
	 *
	 * @since 1.0.0
	 *
	 * @param array $ids Posts ids.
	 *
	 * @return array
	 */
	function woodmart_get_taxonomies_by_ids_autocomplete( $ids ) {
		$output = array();

		if ( ! $ids ) {
			return $output;
		}

		if ( ! is_array( $ids ) ) {
			$ids = array( $ids );
		}

		foreach ( $ids as $id ) {
			$term = get_term( $id );

			if ( $term && ! is_wp_error( $term ) ) {
				$output[ $term->term_id ] = array(
					'name'  => $term->name,
					'value' => $term->term_id,
				);
			}
		}

		return $output;
	}
}

if ( ! function_exists( 'woodmart_get_taxonomies_by_query_autocomplete' ) ) {
	/**
	 * Autocomplete by taxonomies.
	 *
	 * @since 1.0.0
	 */
	function woodmart_get_taxonomies_by_query_autocomplete() {
		check_ajax_referer( 'woodmart_get_taxonomies_by_query_autocomplete_nonce', 'security' );

		$output = array();

		$args = array(
			'number'     => 5,
			'taxonomy'   => $_POST['value'], // phpcs:ignore
			'search'     => isset( $_POST['params']['term'] ) ? $_POST['params']['term'] : '', // phpcs:ignore.
			'hide_empty' => false,
		);

		$terms = get_terms( $args );

		if ( count( $terms ) > 0 ) { // phpcs:ignore
			foreach ( $terms as $term ) {
				$output[] = array(
					'id'   => $term->term_id,
					'text' => $term->name,
				);
			}
		}

		echo wp_json_encode( $output );
		die();
	}

	add_action( 'wp_ajax_woodmart_get_taxonomies_by_query_autocomplete', 'woodmart_get_taxonomies_by_query_autocomplete' );
	add_action( 'wp_ajax_nopriv_woodmart_get_taxonomies_by_query_autocomplete', 'woodmart_get_taxonomies_by_query_autocomplete' );
}

if ( ! function_exists( 'woodmart_get_post_by_ids_autocomplete' ) ) {
	/**
	 * Autocomplete by post ids.
	 *
	 * @since 1.0.0
	 *
	 * @param array $ids Posts ids.
	 *
	 * @return array
	 */
	function woodmart_get_post_by_ids_autocomplete( $ids ) {
		$output = array();

		if ( ! $ids ) {
			return $output;
		}

		if ( ! is_array( $ids ) ) {
			$ids = array( $ids );
		}

		foreach ( $ids as $id ) {
			$post = get_post( $id );

			if ( $post ) {
				$output[ $post->ID ] = array(
					'name'  => $post->post_title . ' ID:(' . $post->ID . ')',
					'value' => $post->ID,
				);
			}
		}

		return $output;
	}
}

if ( ! function_exists( 'woodmart_get_post_by_query_autocomplete' ) ) {
	/**
	 * Autocomplete by post.
	 *
	 * @since 1.0.0
	 */
	function woodmart_get_post_by_query_autocomplete() {
		check_ajax_referer( 'woodmart_get_post_by_query_autocomplete_nonce', 'security' );

		$output = array();

		$args = array(
			'post_type'   => $_POST['value'],
			's'           => isset( $_POST['params']['term'] ) ? $_POST['params']['term'] : '', // phpcs:ignore
			'post_status' => 'publish',
			'numberposts' => apply_filters( 'woodmart_get_numberposts_by_query_autocomplete', 20 ),
			'exclude'     => isset( $_POST['selected'] ) ? $_POST['selected'] : array(),
		);

		$posts = get_posts( $args );

		if ( count( $posts ) > 0 ) { // phpcs:ignore
			foreach ( $posts as $value ) {
				$output[] = array(
					'id'   => $value->ID,
					'text' => $value->post_title . ' ID:(' . $value->ID . ')',
				);
			}
		}

		echo wp_json_encode( $output );
		die();
	}

	add_action( 'wp_ajax_woodmart_get_post_by_query_autocomplete', 'woodmart_get_post_by_query_autocomplete' );
	add_action( 'wp_ajax_nopriv_woodmart_get_post_by_query_autocomplete', 'woodmart_get_post_by_query_autocomplete' );
}

if ( ! function_exists( 'woodmart_product_attributes_array' ) ) {
	function woodmart_product_attributes_array() {

		if ( ! function_exists( 'wc_get_attribute_taxonomies' ) ) {
			return array();
		}
		$attributes = array();

		foreach ( wc_get_attribute_taxonomies() as $attribute ) {
			$attributes[ 'pa_' . $attribute->attribute_name ] = array(
				'name'  => $attribute->attribute_label,
				'value' => 'pa_' . $attribute->attribute_name,
			);
		}

		return $attributes;
	}
}

if ( ! function_exists( 'woodmart_get_pages_array' ) ) {
	/**
	 * Get all pages array
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	function woodmart_get_pages_array() {
		$pages = array();

		foreach ( get_pages() as $page ) {
			$pages[ $page->ID ] = array(
				'name'  => $page->post_title,
				'value' => $page->ID,
			);
		}

		return $pages;
	}
}

if( ! function_exists( 'woodmart_body_class' ) ) {
	function woodmart_body_class( $classes ) {

		$page_id = woodmart_page_ID();

		$site_width = woodmart_get_opt( 'site_width' );
		$product_design = woodmart_product_design();
		$product_sticky = woodmart_product_sticky();

		$ajax_shop = woodmart_get_opt( 'ajax_shop' );
		$hide_sidebar_mobile = woodmart_get_opt( 'shop_hide_sidebar' );
		$hide_sidebar_tablet = woodmart_get_opt( 'shop_hide_sidebar_tablet' );
		$hide_sidebar_desktop = woodmart_get_opt( 'shop_hide_sidebar_desktop' );
		$catalog_mode = woodmart_get_opt( 'catalog_mode' );
		$categories_toggle = woodmart_get_opt( 'categories_toggle' );
		$sticky_footer = woodmart_get_opt( 'sticky_footer' );
		$dark = woodmart_get_opt( 'dark_version' );
		$form_fields_style = ( woodmart_get_opt( 'form_fields_style' ) ) ? woodmart_get_opt( 'form_fields_style' ) : 'square';
		$form_border_width = woodmart_get_opt( 'form_border_width' );
		$single_post_design = woodmart_get_opt( 'single_post_design' );
		$main_sidebar_mobile = woodmart_get_opt( 'hide_main_sidebar_mobile' );

		if ( $single_post_design == 'large_image' && is_single() ) {
			$classes[] = 'single-post-large-image';
		}

		$classes[] = 'wrapper-' . $site_width;

		if ( 'underlined' === $form_fields_style ) {
			$classes[] = 'form-style-' . $form_fields_style;
		} else {
			$classes[] = woodmart_get_old_classes( ' form-style-' . $form_fields_style );
		}

		$classes[] = woodmart_get_old_classes( ' form-border-width-' . $form_border_width );

		if( is_singular( 'product' ) ) {
			$classes[] = 'woodmart-product-design-' . $product_design;
			if( $product_sticky ) {
				$classes[] = 'woodmart-product-sticky-on';
				wp_enqueue_script( 'imagesloaded' );
				woodmart_enqueue_js_library( 'sticky-kit' );
				woodmart_enqueue_js_script( 'sticky-details' );
			}
		}

		if ( woodmart_woocommerce_installed() && ( is_shop() || is_product_category() ) && ( $hide_sidebar_desktop && $sticky_footer ) ) {
			$classes[] = 'no-sticky-footer';
		}elseif( $sticky_footer ){
			wp_enqueue_script( 'imagesloaded' );
			woodmart_enqueue_js_script( 'sticky-footer' );
			$classes[] = 'sticky-footer-on';
		}

		if ( $dark ) {
			$classes[] = 'global-color-scheme-light';
		}

		if ( $catalog_mode ) {
			$classes[] = 'catalog-mode-on';
		}

		if ( $categories_toggle ) {
			$classes[] = 'categories-accordion-on';
		}

		if ( woodmart_is_shop_archive() ) {
			$classes[] = 'woodmart-archive-shop';
		} else if( woodmart_is_portfolio_archive() ) {
			$classes[] = 'woodmart-archive-portfolio';
		} else if( woodmart_is_blog_archive() ) {
			$classes[] = 'woodmart-archive-blog';
		}

		//Header banner
		if ( ! woodmart_get_opt( 'header_close_btn' ) && woodmart_get_opt( 'header_banner' ) && ! isset( $GLOBALS['wd_maintenance'] ) ) {
			$classes[] = 'header-banner-display';
		}
		if ( woodmart_get_opt( 'header_banner' ) && ! isset( $GLOBALS['wd_maintenance'] ) ) {
			$classes[] = 'header-banner-enabled';
		}

		if ( $ajax_shop ) {
			$classes[] = 'woodmart-ajax-shop-on';
		}

		if( $hide_sidebar_mobile && ( woodmart_woocommerce_installed() && ( is_shop() || is_product_category() || is_product_tag() || woodmart_is_product_attribute_archive() ) ) || $main_sidebar_mobile && ( ! woodmart_woocommerce_installed() || ( ! is_shop() && ! is_product_category() && ! is_product_tag() && ! woodmart_is_product_attribute_archive() ) ) ) {
			$classes[] = 'offcanvas-sidebar-mobile';
		}

		if( $hide_sidebar_tablet ) {
			$classes[] = 'offcanvas-sidebar-tablet';
		}

		if( $hide_sidebar_desktop ) {
			$classes[] = 'offcanvas-sidebar-desktop';
		}

		if ( ! is_user_logged_in() && woodmart_get_opt( 'login_prices' ) ) {
			$classes[] = 'login-see-prices';
		}

		if ( woodmart_get_opt( 'sticky_notifications' ) ) {
			$classes[] = 'notifications-sticky';
		}

		if ( woodmart_get_opt( 'sticky_toolbar' ) && ! woodmart_is_maintenance_active() ) {
			$classes[] = 'sticky-toolbar-on';
		}
		if ( woodmart_get_opt( 'hide_larger_price' ) ) {
			$classes[] = 'hide-larger-price';
		}

		if ( ( is_singular( 'product' ) || is_singular( 'woodmart_layout' ) ) && woodmart_get_opt( 'single_sticky_add_to_cart' ) ) {
			$classes[] = 'wd-sticky-btn-on';

			if ( woodmart_get_opt( 'mobile_single_sticky_add_to_cart' ) ) {
				$classes[] = 'wd-sticky-btn-on-mb';
			}
		}

		$classes = array_merge( $classes, woodmart_get_header_body_classes() );

		return $classes;
	}

	add_filter('body_class', 'woodmart_body_class');
}

/**
 * ------------------------------------------------------------------------------------------------
 * Filter wp_title
 * ------------------------------------------------------------------------------------------------
 */

if( ! function_exists( 'woodmart_wp_title' ) ) {
	function woodmart_wp_title( $title, $sep ) {
		global $paged, $page;

		if ( is_feed() )
			return $title;

		// Add the site name.
		$title .= get_bloginfo( 'name' );

		// Add the site description for the home/front page.
		$site_description = get_bloginfo( 'description', 'display' );
		if ( $site_description && ( is_home() || is_front_page() ) )
			$title = "$title $sep $site_description";

		// Add a page number if necessary.
		if ( $paged >= 2 || $page >= 2 )
			$title = "$title $sep " . sprintf( esc_html__( 'Page %s', 'woodmart' ), max( $paged, $page ) );

		return $title;
	}

	add_filter( 'wp_title', 'woodmart_wp_title', 10, 2 );
}

/**
 * ------------------------------------------------------------------------------------------------
 * Get predefined footer configuration by index
 * ------------------------------------------------------------------------------------------------
 */

if( ! function_exists( 'woodmart_get_footer_config' ) ) {
	function woodmart_get_footer_config( $index ) {

		if( $index > 20 || $index < 1) {
			$index = 1;
		}

		$configs = apply_filters( 'woodmart_footer_configs_array', array(
			1 => array(
				'cols' => array(
					'col-12'
				),

			),
			2 => array(
				'cols' => array(
					'col-12 col-sm-6',
					'col-12 col-sm-6',
				),
			),
			3 => array(
				'cols' => array(
					'col-12 col-sm-4',
					'col-12 col-sm-4',
					'col-12 col-sm-4',
				),
			),
			4 => array(
				'cols' => array(
					'col-12 col-sm-6 col-lg-3',
					'col-12 col-sm-6 col-lg-3',
					'col-12 col-sm-6 col-lg-3',
					'col-12 col-sm-6 col-lg-3',
				),
			),
			5 => array(
				'cols' => array(
					'col-12 col-sm-6 col-md-4 col-lg-2',
					'col-12 col-sm-6 col-md-4 col-lg-2',
					'col-12 col-sm-6 col-md-4 col-lg-2',
					'col-12 col-sm-6 col-md-4 col-lg-2',
					'col-12 col-sm-6 col-md-4 col-lg-2',
					'col-12 col-sm-6 col-md-4 col-lg-2',
				),
			),
			6 => array(
				'cols' => array(
					'col-12 col-sm-4 col-lg-3',
					'col-12 col-sm-4 col-lg-6',
					'col-12 col-sm-4 col-lg-3',
				),
			),
			7 => array(
				'cols' => array(
					'col-12 col-sm-4 col-lg-6',
					'col-12 col-sm-4 col-lg-3',
					'col-12 col-sm-4 col-lg-3',
				),
			),
			8 => array(
				'cols' => array(
					'col-12 col-sm-4 col-lg-3',
					'col-12 col-sm-4 col-lg-3',
					'col-12 col-sm-4 col-lg-6',
				),
			),
			9 => array(
				'cols' => array(
					'col-12',
					'col-12 col-sm-6 col-lg-3',
					'col-12 col-sm-6 col-lg-3',
					'col-12 col-sm-6 col-lg-3',
					'col-12 col-sm-6 col-lg-3',
				),
			),
			10 => array(
				'cols' => array(
					'col-12 col-md-6',
					'col-12 col-md-6',
					'col-12 col-sm-6 col-lg-3',
					'col-12 col-sm-6 col-lg-3',
					'col-12 col-sm-6 col-lg-3',
					'col-12 col-sm-6 col-lg-3',
				),
			),
			11 => array(
				'cols' => array(
					'col-12 col-md-6',
					'col-12 col-md-6',
					'col-12 col-sm-6 col-md-3 col-lg-2',
					'col-12 col-sm-6 col-md-3 col-lg-2',
					'col-12 col-sm-6 col-md-3 col-lg-2',
					'col-12 col-sm-6 col-md-3 col-lg-2',
					'col-12 col-lg-4',
				),
			),
			12 => array(
				'cols' => array(
					'col-12',
					'col-12 col-sm-6 col-md-3 col-lg-2',
					'col-12 col-sm-6 col-md-3 col-lg-2',
					'col-12 col-sm-6 col-md-3 col-lg-2',
					'col-12 col-sm-6 col-md-3 col-lg-2',
					'col-12 col-lg-4',
				),
			),
			13 => array(
				'cols' => array(
					'col-12 col-sm-6 col-lg-3',
					'col-12 col-sm-6 col-lg-3',
					'col-12 col-sm-4 col-lg-2',
					'col-12 col-sm-4 col-lg-2',
					'col-12 col-sm-4 col-lg-2',
				),
			),
		) );

		return (isset( $configs[$index] )) ? $configs[$index] : array();
	}
}

// **********************************************************************//
// ! Obtain real page ID (shop page, blog, portfolio or simple page)
// **********************************************************************//

/**
 * This function is called once when initializing WOODMART_Layout object
 * then you can use function woodmart_page_ID to get current page id
 */
if ( ! function_exists( 'woodmart_get_the_ID' ) ) {
	function woodmart_get_the_ID( $args = array() ) {
		global $post;

		$page_id = 0;

		$page_for_posts    = get_option( 'page_for_posts' );
		$page_for_shop     = get_option( 'woocommerce_shop_page_id' );
		$page_for_projects = woodmart_get_portfolio_page_id();
		$custom_404_id     = woodmart_get_opt( 'custom_404_page' );

		if ( isset( $post->ID ) ) {
			$page_id = $post->ID;
		}

		if ( isset( $post->ID ) && ( is_singular( 'page' ) || is_singular( 'post' ) ) ) {
			$page_id = $post->ID;
		} elseif ( is_home() || is_singular( 'post' ) || is_search() || is_tag() || is_category() || is_date() || is_author() ) {
			$page_id = $page_for_posts;
		} elseif ( is_archive() && get_post_type() === 'portfolio' ) {
			$page_id = $page_for_projects;
		}

		if( woodmart_woocommerce_installed() && function_exists( 'is_shop' )  ) {
			if( isset( $args['singulars'] ) && in_array( 'product', $args['singulars']) && is_singular( "product" ) ) {
				// keep post id
			} else if( is_shop() || is_product_category() || is_product_tag() || woodmart_is_product_attribute_archive() ) {
				$page_id = $page_for_shop;
			}
		}

		if( is_404() && ( $custom_404_id != 'default' || ! empty( $custom_404_id ) ) ) $page_id = $custom_404_id;

		return $page_id;
	}
}



// **********************************************************************//
// ! Function to get HTML block content
// **********************************************************************//

if( ! function_exists( 'woodmart_get_html_block' ) ) {
	function woodmart_get_html_block($id) {
		$id = apply_filters( 'wpml_object_id', $id, 'cms_block', true );
		$post = get_post( $id );
		$content = '';

		if ( ! $post || $post->post_type != 'cms_block' || ! $id ) {
			return;
		}

		if ( woodmart_is_elementor_installed() && Plugin::$instance->documents->get( $id )->is_built_with_elementor() ) {
			$content .= woodmart_elementor_get_content( $id );
		} else {
			$shortcodes_custom_css = get_post_meta( $id, '_wpb_shortcodes_custom_css', true );
			$woodmart_shortcodes_custom_css = get_post_meta( $id, 'woodmart_shortcodes_custom_css', true );

			if ( ! empty( $shortcodes_custom_css ) || ! empty( $woodmart_shortcodes_custom_css ) ) {
				$content .= '<style data-type="vc_shortcodes-custom-css">';
				if ( ! empty( $shortcodes_custom_css ) ) {
					$content .= $shortcodes_custom_css;
				}

				if ( ! empty( $woodmart_shortcodes_custom_css ) ) {
					$content .= $woodmart_shortcodes_custom_css;
				}
				$content .= '</style>';
			}

			$post_content = $post->post_content;

			if ( ! str_contains( $post_content, '[vc_row' ) && ! has_blocks( $post_content ) ) {
				$post_content = wpautop( $post_content );
			}

			$content .= do_shortcode( $post_content );
		}

		return $content;
	}

}

if( ! function_exists( 'woodmart_get_static_blocks_array' ) ) {
	function woodmart_get_static_blocks_array( $new = false, $empty = false ) {
		$args = array( 'posts_per_page' => 500, 'post_type' => 'cms_block' );
		$blocks_posts = get_posts( $args );
		$array = array();
		foreach ( $blocks_posts as $post ) :
			if ( $new ) {
				if ( $empty ) {
					$array[''] = array(
						'name'  => esc_html__( 'Select', 'woodmart' ),
						'value' => '',
					);
				}
				$array[ $post->ID ] = array(
					'name'  => $post->post_title . ' (ID:' . $post->ID . ')',
					'value' => $post->ID,
				);
			} else {
				if ( $empty ) {
					$array[ esc_html__( 'Select', 'woodmart' ) ] = '';
				}
				$array[ $post->post_title . ' (ID:' . $post->ID . ')' ] = $post->ID;
			}
		endforeach;
		return $array;
	}
}

if ( ! function_exists( 'woodmart_get_theme_settings_html_blocks_array' ) ) {
	/**
	 * Function to get array of HTML Blocks in theme settings array style.
	 *
	 * @return array
	 */
	function woodmart_get_theme_settings_html_blocks_array() {
		return woodmart_get_static_blocks_array( true );
	}
}

if ( ! function_exists( 'woodmart_get_html_blocks_array_with_empty' ) ) {
	/**
	 * Function to get array of HTML Blocks in WPB element array style.
	 *
	 * @return array
	 */
	function woodmart_get_html_blocks_array_with_empty() {
		return woodmart_get_static_blocks_array( false, true );
	}
}

if ( ! function_exists( 'woodmart_get_elementor_html_blocks_array' ) ) {
	/**
	 * Function to get array of HTML Blocks.
	 *
	 * @return array
	 */
	function woodmart_get_elementor_html_blocks_array() {
		$output = array();

		$posts = get_posts(
			array(
				'posts_per_page' => 500, // phpcs:ignore
				'post_type'      => 'cms_block',
			)
		);

		$output['0'] = esc_html__( 'Select', 'woodmart' );

		foreach ( $posts as $post ) {
			$output[ $post->ID ] = $post->post_title;
		}

		return $output;
	}
}
// **********************************************************************//
// ! Set excerpt length and more btn
// **********************************************************************//

if ( ! function_exists( 'woodmart_excerpt_length' ) ) {
	function woodmart_excerpt_length( $length ) {
		return 20;
	}

	add_filter( 'excerpt_length', 'woodmart_excerpt_length', 999 );
}

if ( ! function_exists( 'woodmart_new_excerpt_more' ) ) {
	function woodmart_new_excerpt_more( $more ) {
		return '';
	}

	add_filter('excerpt_more', 'woodmart_new_excerpt_more');
}

// **********************************************************************//
// ! Add scroll to top buttom
// **********************************************************************//

add_action( 'woodmart_before_wp_footer', 'woodmart_scroll_top_btn' );

if( ! function_exists( 'woodmart_scroll_top_btn' ) ) {
	function woodmart_scroll_top_btn( $more ) {
		if ( ! woodmart_get_opt( 'scroll_top_btn' ) ) {
			return;
		}

		woodmart_enqueue_js_script( 'scroll-top' );
		woodmart_enqueue_inline_style( 'scroll-top' );
		?>
		<a href="#" class="scrollToTop" aria-label="<?php esc_attr_e( 'Scroll to top button', 'woodmart' ); ?>"></a>
		<?php
	}
}


// **********************************************************************//
// ! Return related posts args array
// **********************************************************************//

if( ! function_exists( 'woodmart_get_related_posts_args' ) ) {
	function woodmart_get_related_posts_args( $post_id ) {
		$taxs = wp_get_post_tags( $post_id );
		$args = array();

		if ( $taxs ) {
			$tax_ids = array();

			foreach ( $taxs as $individual_tax ) {
				$tax_ids[] = $individual_tax->term_id;
			}

			$args = array(
				'tag__in'             => $tax_ids,
				'post__not_in'        => array( $post_id ),
				'showposts'           => 12,
				'ignore_sticky_posts' => 1,
			);

		}

		return $args;
	}
}

if( ! function_exists( 'woodmart_get_related_projects_args' ) ) {
	function woodmart_get_related_projects_args( $post_id ) {
		$taxs = wp_get_post_terms( $post_id, 'project-cat' );
		$args = array();
		if ( $taxs ) {
			$tax_ids = array();
			foreach( $taxs as $individual_tax ) $tax_ids[] = $individual_tax->term_id;

			$args = array(
				'post_type' => 'portfolio',
				'post__not_in' => array( $post_id ),
				'tax_query' => array(
					array(
						'taxonomy' => 'project-cat',
						'terms' => $tax_ids,
						'include_children' => false
					),
				)
			);
		}

		return $args;
	}
}

// **********************************************************************//
// ! Load menu drodpwns with AJAX actions
// **********************************************************************//

if( ! function_exists('woodmart_load_html_dropdowns_action') ) {
	function woodmart_load_html_dropdowns_action() {
		$response = array(
			'status' => 'error',
			'message' => 'Can\'t load HTML blocks with AJAX',
			'data' => array(),
		);

		if ( woodmart_is_elementor_installed() && ! apply_filters( 'woodmart_enqueue_html_dropdowns_inline_style', false ) && isset( $_REQUEST['action'] ) && 'woodmart_load_html_dropdowns' === $_REQUEST['action'] ) {
			add_filter( 'elementor/frontend/builder_content/before_print_css', '__return_false' );
		}

		if( class_exists('WPBMap') )
			WPBMap::addAllMappedShortcodes();

		if( isset( $_POST['ids'] ) && is_array( $_POST['ids'] ) ) {
			$ids = woodmart_clean( $_POST['ids'] );
			foreach ($ids as $id) {
				$id = (int) $id;
				$content = woodmart_get_html_block($id);
				if( ! $content ) continue;

				$response['status'] = 'success';
				$response['message'] = 'At least one HTML block loaded';
				$response['data'][$id] = $content;
			}
		}

		wp_send_json( $response );
	}
	add_action( 'wp_ajax_woodmart_load_html_dropdowns', 'woodmart_load_html_dropdowns_action' );
	add_action( 'wp_ajax_nopriv_woodmart_load_html_dropdowns', 'woodmart_load_html_dropdowns_action' );
}

// **********************************************************************//
// ! // Deletes first gallery shortcode and returns content (http://stackoverflow.com/questions/17224100/wordpress-remove-shortcode-and-save-for-use-elsewhere)
// **********************************************************************//

if( ! function_exists( 'woodmart_strip_shortcode_gallery' ) ) {
	function woodmart_strip_shortcode_gallery( $content ) {
		preg_match_all( '/'. get_shortcode_regex() .'/s', $content, $matches, PREG_SET_ORDER );
		if ( ! empty( $matches ) ) {
			foreach ( $matches as $shortcode ) {
				if ( 'gallery' === $shortcode[2] ) {
					$pos = strpos( $content, $shortcode[0] );
					if ($pos !== false)
						return substr_replace( $content, '', $pos, strlen($shortcode[0]) );
				}
			}
		}
		return $content;
	}
}


// **********************************************************************//
// ! Get exceprt from post content
// **********************************************************************//

if( ! function_exists( 'woodmart_excerpt_from_content' ) ) {
	function woodmart_excerpt_from_content($post_content, $limit, $shortcodes = '') {
		// Strip shortcodes and HTML tags
		if ( empty( $shortcodes )) {
			$post_content = preg_replace("/\[caption(.*)\[\/caption\]/i", '', $post_content);
			$post_content = preg_replace('`\[[^\]]*\]`','',$post_content);
		}

		$post_content = trim( stripslashes( wp_filter_nohtml_kses( $post_content ) ) );

		if ( woodmart_get_opt( 'blog_words_or_letters' ) == 'letter' ) {
			$excerpt = mb_substr( $post_content, 0, $limit );
			if ( mb_strlen( $excerpt ) >= $limit ) {
				$excerpt .= '...';
			}
		}else{
			$limit++;
			$excerpt = explode(' ', $post_content, $limit);
			if ( count( $excerpt) >= $limit ) {
				array_pop( $excerpt );
				$excerpt = implode( " ", $excerpt ) . '...';
			} else {
				$excerpt = implode( " ", $excerpt );
			}
		}

		$excerpt = strip_tags( $excerpt );

		if ( trim( $excerpt ) == '...' ) {
			return '';
		}

		return $excerpt;
	}
}

// **********************************************************************//
// ! Get portfolio taxonomies dropdown
// **********************************************************************//

if( ! function_exists( 'woodmart_get_projects_cats_array') ) {
	function woodmart_get_projects_cats_array() {
		$return = array('All' => '');

		if( ! post_type_exists( 'portfolio' ) ) return array();

		$cats = get_terms( 'project-cat' );

		foreach ($cats as $key => $cat) {
			$return[$cat->name] = $cat->term_id;
		}

		return $return;
	}
}

// **********************************************************************//
// ! Get menus dropdown
// **********************************************************************//

if( ! function_exists( 'woodmart_get_menus_array') ) {
	function woodmart_get_menus_array( $style = 'default' ) {
		$output = array();

		$menus = wp_get_nav_menus();

		if ( 'elementor' === $style ) {
			$output[''] = esc_html__( 'Select', 'woodmart' );
		}

		foreach ( $menus as $menu ) {
			if ( 'elementor' === $style ) {
				$output[ $menu->term_id ] = $menu->name;
			} else {
				$output[ $menu->name ] = $menu->name;
			}
		}

		return $output;
	}
}

// **********************************************************************//
// ! Get registered sidebars dropdown
// **********************************************************************//

if(!function_exists('woodmart_get_sidebars_array')) {
	function woodmart_get_sidebars_array( $new = false ) {
		global $wp_registered_sidebars;
		$sidebars = array();
		if ( $new ) {
			$sidebars['none'] = array(
				'name'  => esc_html__( 'None', 'woodmart' ),
				'value' => 'none'
			);
		} else {
			$sidebars['none'] = 'none';
		}
		foreach( $wp_registered_sidebars as $id => $sidebar ) {
			if ( $new ) {
				$sidebars[$id] = array(
					'name' => $sidebar[ 'name' ],
					'value' => $id
				);
			} else {
				$sidebars[ $id ] = $sidebar[ 'name' ];
			}
		}
		return $sidebars;
	}
}

if ( ! function_exists( 'woodmart_get_theme_settings_sidebars_array' ) ) {
	/**
	 * Get sidebars array in theme settings array style.
	 *
	 * @return array
	 */
	function woodmart_get_theme_settings_sidebars_array() {
		return woodmart_get_sidebars_array( true );
	}
}

// **********************************************************************//
// ! Get page id by template name
// **********************************************************************//

if( ! function_exists( 'woodmart_pages_ids_from_template' ) ) {
	function woodmart_pages_ids_from_template( $name ) {
		$pages = get_pages(array(
			'meta_key' => '_wp_page_template',
			'meta_value' => $name . '.php'
		));

		$return = array();

		foreach($pages as $page){
			$return[] = $page->ID;
		}

		return $return;
	}
}

// **********************************************************************//
// ! Get content of the SVG icon located in images/svg folder
// **********************************************************************//
if( ! function_exists( 'woodmart_get_svg_content' ) ) {
	function woodmart_get_svg_content($name) {
		$folder = WOODMART_THEMEROOT . '/images/svg';
		$file = $folder . '/' . $name . '.svg';

		return (file_exists( $file )) ? woodmart_get_any_svg( $file ) : false;
	}
}

if( ! function_exists( 'woodmart_get_any_svg' ) ) {
	function woodmart_get_any_svg( $file, $id = false ) {
		$content = function_exists( 'woodmart_get_svg' ) ? woodmart_get_svg( $file ) : '';
		$start_tag = '<svg';
		if( $id ) {
			$pattern = "/id=\"(\w)+\"/";
			if( preg_match($pattern, $content) ) {
				$content = preg_replace($pattern, "id=\"" . $id . "\"", $content, 1);
			} else {
				$content = preg_replace( "/<svg/", "<svg id=\"" . $id . "\"", $content);
			}
		}
		// Strip doctype
		$position = strpos($content, $start_tag);
		$content = substr($content, $position);

		return $content;
	}
}

// **********************************************************************//
//  Function return vc_row with gradient.
// **********************************************************************//
if( ! function_exists( 'woodmart_get_gradient_attr' ) ) {
	function woodmart_get_gradient_attr( $output, $obj, $attr ) {
		if ( isset( $attr['woodmart_gradient_switch'] ) && $attr['woodmart_gradient_switch'] == 'yes' ) {
			$gradient_css = woodmart_get_gradient_css( $attr['woodmart_color_gradient'] );
			$output = preg_replace_callback('/wd-row-gradient-enable.*?>/',
				function ( $matches ) use( $gradient_css ) {
					return strtolower( $matches[0] . '<div class="woodmart-row-gradient wd-fill" style="' . $gradient_css . '"></div>' );
				}, $output );
		}
		return $output;
	}
}

add_filter( 'vc_shortcode_output', 'woodmart_get_gradient_attr', 10, 3 );

// **********************************************************************//
//  Function return gradient css.
// **********************************************************************//
if( ! function_exists( 'woodmart_get_gradient_css' ) ) {
	function woodmart_get_gradient_css( $gradient_attr ) {
		$gradient_css = explode( '|', $gradient_attr );
		$css          = $gradient_css[1];
		$webkit_css   = $gradient_css[1];

		$css = str_replace( array( 'left', 'top', 'right', 'bottom' ), array( 'to side1', 'to side2', 'to side3', 'to side4' ), $css );
		$css = str_replace( array( 'side1', 'side2', 'side3', 'side4' ), array( 'right', 'bottom', 'left', 'top' ), $css );

		$result  = 'background-image:-webkit-' . $webkit_css . ';';
		$result .= 'background-image:' . $css . ';';

		return $result;
	}
}

// **********************************************************************//
// ! Append :hover to CSS selectors array
// **********************************************************************//
if( ! function_exists( 'woodmart_append_hover_state' ) ) {
	function woodmart_append_hover_state( $selectors , $focus = false ) {
		$selectors = explode(',', $selectors[0]);

		$return = array();

		foreach ($selectors as $selector) {
			$return[] = $selector . ':hover';
			( $focus ) ? $return[] .= $selector . ':focus' : false ;
		}

		return implode(',', $return);
	}
}


// **********************************************************************//
// Woodmart twitter process links
// **********************************************************************//
if( ! function_exists( 'woodmart_twitter_process_links' ) ) {
	function woodmart_twitter_process_links( $tweet ) {

		// Is the Tweet a ReTweet - then grab the full text of the original Tweet
		if( isset( $tweet->retweeted_status ) ) {
			// Split it so indices count correctly for @mentions etc.
			$rt_section = current( explode( ":", $tweet->text ) );
			$text = $rt_section.": ";
			// Get Text
			$text .= $tweet->retweeted_status->text;
		} else {
			// Not a retweet - get Tweet
			$text = $tweet->text;
		}

		// NEW Link Creation from clickable items in the text
		$text = preg_replace( '/((http)+(s)?:\/\/[^<>\s]+)/i', '<a href="$0" target="_blank" rel="nofollow noopener">$0</a>', $text );
		// Clickable Twitter names
		$text = preg_replace( '/[@]+([A-Za-z0-9-_]+)/', '<a href="https://x.com/$1" target="_blank" rel="nofollow noopener">@\\1</a>', $text );
		// Clickable Twitter hash tags
		$text = preg_replace( '/[#]+([A-Za-z0-9-_]+)/', '<a href="https://x.com/search?q=%23$1" target="_blank" rel="nofollow noopener">$0</a>', $text );
		// END TWEET CONTENT REGEX
		return $text;

	}
}

if ( ! function_exists( 'woodmart_get_grid_el_columns' ) ) {
	function woodmart_get_grid_el_columns( $columns ) {
		if ( empty( $columns ) ) {
			return false;
		}

		if ( is_array( $columns ) && isset( $columns['size'] ) ) {
			$columns = $columns['size'];
		}

		if ( in_array( $columns, array( 5, 7, 8, 9, 10, 11 ) ) ) {
			$columns = str_replace( '.', '_', round( 100 / $columns, 1 ) );
			if ( ! strpos( $columns, '_' ) ) {
				$columns = $columns . '_0';
			}
		} else {
			$columns = 12 / $columns;
		}

		return $columns;
	}
}

if ( ! function_exists( 'woodmart_get_col_sizes' ) ) {
	function woodmart_get_col_sizes( $desktop_columns ) {
		$desktop_columns = (int) $desktop_columns;

		$sizes = array(
			'1'  => array(
				'desktop' => '1',
				'tablet'  => '1',
				'mobile'  => '1',
			),
			'2'  => array(
				'desktop' => '2',
				'tablet'  => '2',
				'mobile'  => '1',
			),
			'3'  => array(
				'desktop' => '3',
				'tablet'  => '3',
				'mobile'  => '1',
			),
			'4'  => array(
				'desktop' => '4',
				'tablet'  => '4',
				'mobile'  => '1',
			),
			'5'  => array(
				'desktop' => '5',
				'tablet'  => '4',
				'mobile'  => '2',
			),
			'6'  => array(
				'desktop' => '6',
				'tablet'  => '4',
				'mobile'  => '2',
			),
			'7'  => array(
				'desktop' => '7',
				'tablet'  => '4',
				'mobile'  => '2',
			),
			'8'  => array(
				'desktop' => '8',
				'tablet'  => '4',
				'mobile'  => '2',
			),
			'9'  => array(
				'desktop' => '9',
				'tablet'  => '4',
				'mobile'  => '2',
			),
			'10' => array(
				'desktop' => '10',
				'tablet'  => '4',
				'mobile'  => '2',
			),
			'11' => array(
				'desktop' => '11',
				'tablet'  => '4',
				'mobile'  => '2',
			),
			'12' => array(
				'desktop' => '12',
				'tablet'  => '4',
				'mobile'  => '2',
			),
		);

		return isset( $sizes[ $desktop_columns ] ) ? $sizes[ $desktop_columns ] : $sizes['3'];
	}
}

if ( ! function_exists( 'woodmart_get_grid_attrs' ) ) {
	function woodmart_get_grid_attrs( $settings ) {
		$desktop_columns = isset( $settings['columns'] ) ? $settings['columns'] : '3';
		$tablet_columns  = isset( $settings['columns_tablet'] ) ? $settings['columns_tablet'] : 'auto';
		$mobile_columns  = isset( $settings['columns_mobile'] ) ? $settings['columns_mobile'] : 'auto';

		if ( isset( $tablet_columns['size'] ) ) {
			$tablet_columns = $tablet_columns['size'];
		}
		if ( isset( $mobile_columns['size'] ) ) {
			$mobile_columns = $mobile_columns['size'];
		}

		$auto_columns = woodmart_get_col_sizes( $desktop_columns );
		$style_attrs  = '';

		if ( ! $tablet_columns || 'auto' === $tablet_columns ) {
			$tablet_columns = $auto_columns['tablet'];
		}

		if ( ! $mobile_columns || 'auto' === $mobile_columns ) {
			$mobile_columns = $auto_columns['mobile'];
		}

		$style_attrs .= '--wd-col-lg:' . $desktop_columns . ';';
		$style_attrs .= '--wd-col-md:' . $tablet_columns . ';';
		$style_attrs .= '--wd-col-sm:' . $mobile_columns . ';';

		if ( isset( $settings['spacing'] ) && '' !== (string) $settings['spacing'] ) {
			$style_attrs .= '--wd-gap-lg:' . $settings['spacing'] . 'px;';
		}

		if ( isset( $settings['spacing_tablet'] ) && 'false' === $settings['spacing_tablet'] ) {
			$settings['spacing_tablet'] = '';
		}
		if ( isset( $settings['spacing_mobile'] ) && 'false' === $settings['spacing_mobile'] ) {
			$settings['spacing_mobile'] = '';
		}

		if ( isset( $settings['spacing_tablet'] ) && '' !== (string) $settings['spacing_tablet'] && ( empty( $settings['spacing'] ) || $settings['spacing'] !== $settings['spacing_tablet'] ) ) {
			$style_attrs .= '--wd-gap-md:' . $settings['spacing_tablet'] . 'px;';
		}

		if ( isset( $settings['spacing'], $settings['spacing_mobile'] ) && ! $settings['spacing_mobile'] && in_array( (int) $settings['spacing'], array( 20, 30 ), true ) ) {
			$settings['spacing_mobile'] = 10;
		}

		if ( isset( $settings['spacing_mobile'] ) && '' !== (string) $settings['spacing_mobile'] && ( empty( $settings['spacing_tablet'] ) || $settings['spacing_tablet'] !== $settings['spacing_mobile'] ) ) {
			$style_attrs .= '--wd-gap-sm:' . $settings['spacing_mobile'] . 'px;';
		}

		return $style_attrs;
	}
}

if ( ! function_exists( 'woodmart_get_grid_el_class_new' ) ) {
	function woodmart_get_grid_el_class_new( $loop = 0, $different_sizes = false, $desktop_columns = 3,
		$tablet_columns = 4, $mobile_columns = 1 ) {
		$items_wide   = woodmart_get_wide_items_array( $different_sizes );
		$auto_columns = woodmart_get_col_sizes( $desktop_columns );
		$classes      = '';

		if ( 'auto' === $tablet_columns || empty( $tablet_columns ) ) {
			$tablet_columns = $auto_columns['tablet'];
		}

		if ( 'auto' === $mobile_columns || empty( $mobile_columns ) ) {
			$mobile_columns = $auto_columns['mobile'];
		}

		$desktop_columns_class = woodmart_get_grid_el_columns( $desktop_columns );
		$tablet_columns_class  = woodmart_get_grid_el_columns( $tablet_columns );

		if ( $different_sizes && ( in_array( $loop, $items_wide, true ) ) ) {
			$desktop_columns_class *= 2;
			$tablet_columns_class  *= 2;
		}

		$sizes = array(
			array(
				'name'  => 'col-lg',
				'value' => $desktop_columns_class,
			),
			array(
				'name'  => 'col-md',
				'value' => $tablet_columns_class,
			),
			array(
				'name'  => 'col',
				'value' => woodmart_get_grid_el_columns( $mobile_columns ),
			),
		);

		foreach ( $sizes as $value ) {
			$classes .= ' ' . $value['name'] . '-' . $value['value'];
		}

		if ( $loop > 0 && $desktop_columns > 0 ) {
			if ( 0 === ( $loop - 1 ) % $desktop_columns || 1 === $desktop_columns ) {
				$classes .= ' first ';
			}
			if ( 0 === $loop % $desktop_columns ) {
				$classes .= ' last ';
			}
		}

		return $classes;
	}
}

/**
 * ------------------------------------------------------------------------------------------------
 * Function to prepare classes for grid element (column)
 * ------------------------------------------------------------------------------------------------
 */

if ( ! function_exists( 'woodmart_get_grid_el_class' ) ) {
	function woodmart_get_grid_el_class( $loop = 0, $columns = 4, $different_sizes = false, $xs_size = false, $sm_size = 4, $lg_size = 3, $md_size = false ) {
		$classes = '';

		$items_wide = woodmart_get_wide_items_array( $different_sizes );

		if ( ! $xs_size ) {
			$xs_size = apply_filters( 'woodmart_grid_xs_default', 6 );
		}

		if ( $columns > 0 ) {
			$lg_size = 12 / $columns;
		}

		if ( ! $md_size ) {
			$md_size = $lg_size;
		}

		if ( $columns > 4 ) {
			$md_size = 3;
		}

		if ( $columns <= 3 ) {
			if ( $columns == 1 ) {
				$sm_size = 12;
				$xs_size = 12;
			} else {
				$sm_size = 6;
			}
		}

		// every third element make 2 times larger (for isotope grid)
		if ( $different_sizes && ( in_array( $loop, $items_wide ) ) ) {
			$lg_size *= 2;
			$md_size *= 2;
		}

		if ( in_array( $columns, array( 5, 7, 8, 9, 10, 11 ) ) ) {
			$lg_size = str_replace( '.', '_', round( 100 / $columns, 1 ) );
			if ( ! strpos( $lg_size, '_' ) ) {
				$lg_size = $lg_size . '_0';
			}
		}

		$sizes = array(
			array(
				'name'  => 'col-lg',
				'value' => $lg_size,
			),
			array(
				'name'  => 'col-md',
				'value' => $md_size,
			),
			array(
				'name'  => 'col-sm',
				'value' => $sm_size,
			),
			array(
				'name'  => 'col',
				'value' => $xs_size,
			),
		);

		foreach ( $sizes as $value ) {
			$classes .= ' ' . $value['name'] . '-' . $value['value'];
		}

		if ( $loop > 0 && $columns > 0 ) {
			if ( 0 == ( $loop - 1 ) % $columns || 1 == $columns ) {
				$classes .= ' first ';
			}
			if ( 0 == $loop % $columns ) {
				$classes .= ' last ';
			}
		}

		return $classes;
	}
}


if ( ! function_exists( 'woodmart_get_wide_items_array' ) ) {
	function woodmart_get_wide_items_array( $different_sizes = false ) {
		$items_wide = apply_filters( 'woodmart_wide_items', array( 5, 6, 7, 8, 13, 14 ) );

		if ( is_array( $different_sizes ) ) {
			$items_wide = apply_filters( 'woodmart_wide_items', $different_sizes );
		}

		return $items_wide;
	}
}


// **********************************************************************//
// Woodmart Get Settings JS
// **********************************************************************//
if ( ! function_exists('woodmart_settings_js') ) {
	function woodmart_settings_js() {

		$custom_js          = woodmart_get_opt( 'custom_js' );
		$js_ready           = woodmart_get_opt( 'js_ready' );

		ob_start();

		if( ! empty( $custom_js ) || ! empty( $js_ready ) ): ?>
			<?php if( ! empty( $custom_js ) ): ?>
				<?php echo woodmart_get_opt( 'custom_js' ); ?>
			<?php endif; ?>
			<?php if( ! empty( $js_ready ) ): ?>
				jQuery(document).ready(function() {
				<?php echo woodmart_get_opt( 'js_ready' ); ?>
				});
			<?php endif; ?>
		<?php endif;

		return ob_get_clean();
	}
}

// **********************************************************************//
// Print text size css
// **********************************************************************//
if ( ! function_exists( 'woodmart_responsive_text_size_css' ) ) {
	function woodmart_responsive_text_size_css( $id, $class, $data, $action = 'echo' ) {
		if ( 'return' == $action ) {
			return '#'. $id . ' .'. $class .'{font-size:' . $data . 'px;line-height:' . intval( $data + 10 ) . 'px;}';
		} else {
			echo '#'. $id . ' .'. $class .'{font-size:' . $data . 'px;line-height:' . intval( $data + 10 ) . 'px;}';
		}
	}
}

if ( ! function_exists( 'woodmart_custom_404_page' ) ) {
	/**
	 * Function to set custom 404 page.
	 *
	 * @param $template
	 *
	 * @return mixed|string
	 */
	function woodmart_custom_404_page( $template ) {
		global $wp_query;
		$custom_404 = woodmart_get_opt( 'custom_404_page' );

		if ( $custom_404 === 'default' || empty( $custom_404 ) ) {
			return $template;
		}

		$wp_query->query( 'page_id=' . $custom_404 );
		$wp_query->the_post();
		$template = get_page_template();
		rewind_posts();

		return $template;
	}

	add_filter( '404_template', 'woodmart_custom_404_page', 999 );
}

if ( ! function_exists( 'woodmart_android_browser_bar_color' ) ) {
	/**
	 * Display cart widget side
	 *
	 * @since 1.0.0
	 */
	function woodmart_android_browser_bar_color() {
		$color = woodmart_get_opt( 'android_browser_bar_color' );

		if ( ! empty( $color['idle'] )  ) {
			echo '<meta name="theme-color" content="'. $color['idle'] .'">';
		}
	}

	add_filter( 'wp_head', 'woodmart_android_browser_bar_color' );
}

if ( ! function_exists( 'woodmart_settings_css' ) ) {
	function woodmart_settings_css() {
		$custom_product_background = get_post_meta( get_the_ID(),  '_woodmart_product-background', true );

		ob_start();

		echo '<style>';

		?>

		<?php if ( ! empty( $custom_product_background ) ): ?>
			.single-product .main-page-wrapper{
			background-color: <?php echo esc_html( $custom_product_background ); ?> !important;
			}
		<?php endif ?>

		<?php

		echo '</style>';

		echo ob_get_clean();
	}
	add_action( 'wp_head', 'woodmart_settings_css', 200 );
}

if ( ! function_exists( 'woodmart_remove_jquery_migrate' ) ) {
	/**
	 * Remove JQuery migrate.
	 *
	 * @param WP_Scripts $scripts wp script object.
	 */
	function woodmart_remove_jquery_migrate( $scripts ) {
		if ( ! is_admin() && isset( $scripts->registered['jquery'] ) && woodmart_get_opt( 'remove_jquery_migrate', false ) ) {
			$script = $scripts->registered['jquery'];
			if ( $script->deps ) {
				$script->deps = array_diff( $script->deps, array( 'jquery-migrate' ) );
			}
		}
	}

	add_action( 'wp_default_scripts', 'woodmart_remove_jquery_migrate' );
}
