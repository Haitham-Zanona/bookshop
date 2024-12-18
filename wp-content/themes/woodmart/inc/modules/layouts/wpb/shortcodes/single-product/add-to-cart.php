<?php
/**
 * Add to cart shortcode.
 *
 * @package Woodmart
 */

use XTS\Modules\Layouts\Global_Data as Builder;
use XTS\Modules\Layouts\Main;
use XTS\Modules\Waitlist\Frontend as Waitlist_Frontend;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

if ( ! function_exists( 'woodmart_shortcode_single_product_add_to_cart' ) ) {
	/**
	 * Single product add to cart shortcode.
	 *
	 * @param array $settings Shortcode attributes.
	 */
	function woodmart_shortcode_single_product_add_to_cart( $settings ) {
		$default_settings = array(
			'alignment'             => 'left',
			'button_design'         => 'default',
			'design'                => 'default',
			'swatch_layout'         => 'default',
			'reset_button_position' => 'side',
			'label_position'        => 'side',
			'css'                   => '',
			'width_desktop'         => '',
			'width_tablet'          => '',
			'width_mobile'          => '',
			'product_id'            => false,
			'enable_stock_status'   => 'yes',
		);

		$settings = wp_parse_args( $settings, $default_settings );

		if ( woodmart_get_opt( 'catalog_mode' ) || ! is_user_logged_in() && woodmart_get_opt( 'login_prices' ) ) {
			return '';
		}

		$form_classes    = '';
		$wrapper_classes = apply_filters( 'vc_shortcodes_css_class', '', '', $settings );

		// Form classes.
		$form_classes .= ' wd-reset-' . woodmart_vc_get_control_data( $settings['reset_button_position'], 'desktop' ) . '-lg';
		$form_classes .= ' wd-reset-' . woodmart_vc_get_control_data( $settings['reset_button_position'], 'mobile' ) . '-md';
		$form_classes .= ' wd-label-' . woodmart_vc_get_control_data( $settings['label_position'], 'desktop' ) . '-lg';
		$form_classes .= ' wd-label-' . woodmart_vc_get_control_data( $settings['label_position'], 'mobile' ) . '-md';

		// Wrapper classes.
		if ( $settings['css'] ) {
			$wrapper_classes .= ' ' . vc_shortcode_custom_css_class( $settings['css'] );
		}
		$wrapper_classes .= ' text-' . woodmart_vc_get_control_data( $settings['alignment'], 'desktop' );
		$wrapper_classes .= ' wd-btn-design-' . $settings['button_design'];
		$wrapper_classes .= ' wd-design-' . $settings['design'];
		$wrapper_classes .= ' wd-swatch-layout-' . $settings['swatch_layout'];

		if ( 'justify' === $settings['design'] ) {
			woodmart_enqueue_inline_style( 'woo-single-prod-el-add-to-cart-opt-design-justify-builder' );

			remove_action( 'woocommerce_single_variation', 'woocommerce_single_variation' );
			add_action( 'woocommerce_before_variations_form', 'woocommerce_single_variation' );
		}

		if ( 'no' === $settings['enable_stock_status'] ) {
			$wrapper_classes .=  ' wd-stock-status-off';
		}

		Builder::get_instance()->set_data( 'form_classes', $form_classes );

		ob_start();

		Main::setup_preview( array(), $settings['product_id'] );
		global $product;
		?>
		<div class="wd-single-add-cart wd-wpb<?php echo esc_attr( $wrapper_classes ); ?>">
			<?php woocommerce_template_single_add_to_cart(); ?>

			<?php
		if ( woodmart_get_opt( 'waitlist_enabled' ) && ( ! woodmart_get_opt( 'waitlist_for_loggined' ) || is_user_logged_in() ) ) {
				$waitlist_frontend = Waitlist_Frontend::get_instance();

				if ( ( 'variable' === $product->get_type() && ! empty( $waitlist_frontend->get_out_of_stock_variations_ids( $product ) ) ) || ( 'simple' === $product->get_type() && ! $product->is_in_stock() ) ) {
					$waitlist_frontend->render_waitlist_subscribe_form();
					$waitlist_frontend->render_template_subscribe_form();
				}
			}
			?>
		</div>
		<?php
		Main::restore_preview( $settings['product_id'] );

		return ob_get_clean();
	}
}
