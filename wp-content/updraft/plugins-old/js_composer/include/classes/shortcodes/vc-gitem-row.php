<?php
/**
 * Class that handles specific [vc_gitem_row] shortcode.
 *
 * @see js_composer/include/templates/shortcodes/vc_row.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

require_once vc_path_dir( 'SHORTCODES_DIR', 'vc-row.php' );

/**
 * Class WPBakeryShortCode_Vc_Gitem_Row
 */
class WPBakeryShortCode_Vc_Gitem_Row extends WPBakeryShortCode_Vc_Row {
	/**
	 * Get layout control buttons.
	 *
	 * @return string
	 */
	public function getLayoutsControl() {
		global $vc_row_layouts;
		$controls_layout = '<span class="vc_row_layouts vc_control">';
		foreach ( array_slice( $vc_row_layouts, 0, 4 ) as $layout ) {
			$controls_layout .= '<a class="vc_control-set-column set_columns" data-cells="' . $layout['cells'] . '" data-cells-mask="' . $layout['mask'] . '" title="' . $layout['title'] . '"><i class="vc-composer-icon vc-c-icon-' . $layout['icon_class'] . '"></i></a> ';
		}
		$controls_layout .= '</span>';

		return $controls_layout;
	}
}
