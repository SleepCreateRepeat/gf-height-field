<?php
/**
 * Plugin Name: Gravity Forms Custom Fields
 * Description: Adds custom Height and Weight fields to Gravity Forms with unit toggling and conversion.
 * Version: 4.0.1
 * Author: sleep. create. repeat.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Custom Height Field Class
 */
class GF_Field_Height extends GF_Field {
	public $type = 'height';

	public function get_form_editor_field_title() {
		return esc_attr__( 'Height', 'gravityforms' );
	}

	public function get_form_editor_field_settings() {
		return array(
			'label_setting',
			'description_setting',
			'conditional_logic_field_setting',
		);
	}

	public function get_form_editor_button() {
		return array(
			'group' => 'advanced_fields',
			'text'  => esc_attr__( 'Height', 'gravityforms' ),
		);
	}

	public function get_field_input( $form, $value = '', $entry = null ) {
		$unit = isset( $this->heightUnit ) ? $this->heightUnit : 'imperial';
		$metric_value = '';
		$imperial_ft = '';
		$imperial_in = '';

		if ( ! empty( $value ) ) {
			if ( isset( $value['metric'] ) ) {
				$metric_value = round( $value['metric'] );
				$inches = round($metric_value / 2.54);
				$imperial_ft = floor($inches / 12);
				$imperial_in = $inches % 12;
			} elseif ( isset( $value['imperial'] ) ) {
				$imperial_ft = $value['imperial']['ft'];
				$imperial_in = $value['imperial']['in'];
				$metric_value = round(( $imperial_ft * 12 + $imperial_in ) * 2.54);
			}
		}

		$form_id = $form['id'];
		$id = (int) $this->id;
		ob_start();
		?>
		<div class="ginput_container ginput_container_height">
			<div class="gf-height-field">
				<div class="height-unit-toggle">
					<button type="button" class="height-unit-button <?php echo $unit === 'imperial' ? 'active' : ''; ?>" data-unit="imperial">
						<?php esc_html_e( 'FT/IN', 'gravityforms' ); ?>
					</button>
					<button type="button" class="height-unit-button <?php echo $unit === 'metric' ? 'active' : ''; ?>" data-unit="metric">
						<?php esc_html_e( 'CM', 'gravityforms' ); ?>
					</button>
				</div>
				<div class="height-imperial" style="display: <?php echo $unit === 'imperial' ? 'flex' : 'none'; ?>;">
					<div class="height-field-group">
						<input type="text" name="input_<?php echo $id; ?>[imperial][ft]" value="<?php echo esc_attr( $imperial_ft ); ?>" />
						<span class="height-label"><?php esc_html_e( 'FT', 'gravityforms' ); ?></span>
					</div>
					<div class="height-field-group">
						<input type="text" name="input_<?php echo $id; ?>[imperial][in]" value="<?php echo esc_attr( $imperial_in ); ?>" />
						<span class="height-label"><?php esc_html_e( 'IN', 'gravityforms' ); ?></span>
					</div>
				</div>
				<div class="height-metric" style="display: <?php echo $unit === 'metric' ? 'block' : 'none'; ?>;">
					<div class="height-field-group">
						<input type="text" class="metric-value" name="input_<?php echo $id; ?>[metric]" value="<?php echo esc_attr( $metric_value ); ?>" />
						<span class="height-label"><?php esc_html_e( 'CM', 'gravityforms' ); ?></span>
					</div>
				</div>
			</div>
		</div>
		<script>
            (function(jQuery) {
                jQuery(document).ready(function() {
                    function updateHeightFields(baseCm, container) {
                        if (isNaN(baseCm) || baseCm <= 0) return;

                        const roundedCm = Math.round(baseCm);
                        container.find('.metric-value').val(roundedCm);
                        const totalInches = baseCm / 2.54;
                        const feet = Math.floor(totalInches / 12);
                        const inches = Math.round(totalInches % 12);
                        container.find('input[name*="[imperial][ft]"]').val(feet);
                        container.find('input[name*="[imperial][in]"]').val(inches);
                    }

                    jQuery('.gf-height-field input').on('input', function() {
                        const container = jQuery(this).closest('.gf-height-field');
                        const activeInput = jQuery(this);
                        let baseCm = 0;

                        if (activeInput.hasClass('metric-value')) {
                            baseCm = parseFloat(activeInput.val());
                        } else if (activeInput.attr('name').includes('[imperial]')) {
                            const feet = parseFloat(container.find('input[name*="[imperial][ft]"]').val()) || 0;
                            const inches = parseFloat(container.find('input[name*="[imperial][in]"]').val()) || 0;
                            baseCm = (feet * 12 + inches) * 2.54;
                        }

                        if (!isNaN(baseCm) && baseCm > 0) {
                            updateHeightFields(baseCm, container);
                        }
                    });

                    jQuery('.height-unit-button').on('click', function() {
                        const container = jQuery(this).closest('.gf-height-field');
                        container.find('.height-unit-button').removeClass('active');
                        jQuery(this).addClass('active');

                        const selectedUnit = jQuery(this).data('unit');
                        container.find('.height-imperial, .height-metric').hide();
                        container.find('.height-' + selectedUnit).show();
                    });
                });
            })(jQuery);
		</script>
		<?php
		return ob_get_clean();
	}
}

// Register the Height field.
GF_Fields::register( new GF_Field_Height() );

/**
 * Custom Weight Field Class
 */
class GF_Field_Weight extends GF_Field {
	public $type = 'weight';

	public function get_form_editor_field_title() {
		return esc_attr__( 'Weight', 'gravityforms' );
	}

	public function get_form_editor_field_settings() {
		return array(
			'label_setting',
			'description_setting',
			'conditional_logic_field_setting',
		);
	}

	public function get_form_editor_button() {
		return array(
			'group' => 'advanced_fields',
			'text'  => esc_attr__( 'Weight', 'gravityforms' ),
		);
	}

	public function get_field_input( $form, $value = '', $entry = null ) {
		$unit = isset( $this->weightUnit ) ? $this->weightUnit : 'imperial';
		$metric_value = '';
		$imperial_st = '';
		$imperial_lbs = '';
		$lbs_value = '';

		if ( ! empty( $value ) ) {
			if ( isset( $value['metric'] ) ) {
				$metric_value = round( $value['metric'], 1 );
				$lbs_value = round( $metric_value * 2.20462 );
				$imperial_st = floor( $lbs_value / 14 );
				$imperial_lbs = $lbs_value % 14;
			} elseif ( isset( $value['imperial'] ) ) {
				$imperial_st = intval( $value['imperial']['st'] );
				$imperial_lbs = intval( $value['imperial']['lbs'] );
				$lbs_value = $imperial_st * 14 + $imperial_lbs;
				$metric_value = round( $lbs_value / 2.20462, 1 );
			} elseif ( isset( $value['lbs'] ) ) {
				$lbs_value = round( $value['lbs'] );
				$metric_value = round( $lbs_value / 2.20462, 1 );
				$imperial_st = floor( $lbs_value / 14 );
				$imperial_lbs = $lbs_value % 14;
			}
		}

		$form_id = $form['id'];
		$id = (int) $this->id;
		ob_start();
		?>
		<div class="ginput_container ginput_container_weight">
			<div class="gf-weight-field">
				<div class="weight-unit-toggle">
					<button type="button" class="weight-unit-button <?php echo $unit === 'imperial' ? 'active' : ''; ?>" data-unit="imperial">
						<?php esc_html_e( 'ST/LBS', 'gravityforms' ); ?>
					</button>
					<button type="button" class="weight-unit-button <?php echo $unit === 'lbs' ? 'active' : ''; ?>" data-unit="lbs">
						<?php esc_html_e( 'LBS', 'gravityforms' ); ?>
					</button>
					<button type="button" class="weight-unit-button <?php echo $unit === 'metric' ? 'active' : ''; ?>" data-unit="metric">
						<?php esc_html_e( 'KG', 'gravityforms' ); ?>
					</button>
				</div>
				<div class="weight-imperial" style="display: <?php echo $unit === 'imperial' ? 'flex' : 'none'; ?>;">
					<div class="weight-field-group">
						<input type="text" class="imperial-st" name="input_<?php echo $id; ?>[imperial][st]" value="<?php echo esc_attr( $imperial_st ); ?>" />
						<span class="weight-label"><?php esc_html_e( 'ST', 'gravityforms' ); ?></span>
					</div>
					<div class="weight-field-group">
						<input type="text" class="imperial-lbs" name="input_<?php echo $id; ?>[imperial][lbs]" value="<?php echo esc_attr( $imperial_lbs ); ?>" />
						<span class="weight-label"><?php esc_html_e( 'LBS', 'gravityforms' ); ?></span>
					</div>
				</div>
				<div class="weight-lbs" style="display: <?php echo $unit === 'lbs' ? 'block' : 'none'; ?>;">
					<div class="weight-field-group">
						<input type="text" class="lbs-value" name="input_<?php echo $id; ?>[lbs]" value="<?php echo esc_attr( $lbs_value ); ?>" />
						<span class="weight-label"><?php esc_html_e( 'LBS', 'gravityforms' ); ?></span>
					</div>
				</div>
				<div class="weight-metric" style="display: <?php echo $unit === 'metric' ? 'block' : 'none'; ?>;">
					<div class="weight-field-group">
						<input type="text" class="metric-value" name="input_<?php echo $id; ?>[metric]" value="<?php echo esc_attr( $metric_value ); ?>" />
						<span class="weight-label"><?php esc_html_e( 'KG', 'gravityforms' ); ?></span>
					</div>
				</div>
			</div>
		</div>
		<script>
            (function(jQuery) {
                jQuery(document).ready(function() {
                    function updateWeightFields(baseKg, container, activeInput) {
                        if (isNaN(baseKg) || baseKg <= 0) return;

                        const lbs = baseKg * 2.20462;
                        const totalLbs = Math.round(lbs);
                        const st = Math.floor(totalLbs / 14);
                        const remLbs = totalLbs - st * 14;

                        if (!activeInput.hasClass('metric-value')) {
                            const roundedKg = Math.round(baseKg);
                            container.find('.metric-value').val(roundedKg);
                        }

                        if (!activeInput.hasClass('imperial-st')) {
                            container.find('.imperial-st').val(st);
                        }

                        if (!activeInput.hasClass('imperial-lbs')) {
                            container.find('.imperial-lbs').val(remLbs);
                        }

                        if (!activeInput.hasClass('lbs-value')) {
                            container.find('.lbs-value').val(totalLbs);
                        }
                    }

                    let debounceTimeout;

                    jQuery('.gf-weight-field input').on('input', function() {
                        const container = jQuery(this).closest('.gf-weight-field');
                        const activeInput = jQuery(this);

                        clearTimeout(debounceTimeout);

                        debounceTimeout = setTimeout(function() {
                            let baseKg = 0;

                            if (activeInput.hasClass('metric-value')) {
                                baseKg = parseFloat(activeInput.val());
                            } else if (activeInput.hasClass('lbs-value')) {
                                baseKg = parseFloat(activeInput.val()) / 2.20462;
                            } else if (activeInput.hasClass('imperial-st') || activeInput.hasClass('imperial-lbs')) {
                                const stVal = parseFloat(container.find('.imperial-st').val()) || 0;
                                const lbsVal = parseFloat(container.find('.imperial-lbs').val()) || 0;

                                baseKg = (stVal * 14 + lbsVal) / 2.20462;
                            }

                            if (!isNaN(baseKg) && baseKg > 0) {
                                updateWeightFields(baseKg, container, activeInput);
                            }
                        }, 300);
                    });

                    jQuery('.weight-unit-button').on('click', function() {
                        const container = jQuery(this).closest('.gf-weight-field');
                        container.find('.weight-unit-button').removeClass('active');
                        jQuery(this).addClass('active');

                        const selectedUnit = jQuery(this).data('unit');
                        container.find('.weight-imperial, .weight-lbs, .weight-metric').hide();
                        container.find('.weight-' + selectedUnit).show();
                    });
                });
            })(jQuery);
		</script>
		<?php
		return ob_get_clean();
	}
}

// Register the Weight field.
GF_Fields::register( new GF_Field_Weight() );

/**
 * Enqueue CSS for all fields
 */
add_action( 'wp_enqueue_scripts', 'gf_custom_fields_enqueue_styles' );
function gf_custom_fields_enqueue_styles() {
	if ( is_admin() ) {
		return;
	}

	wp_enqueue_style(
		'gf-custom-fields-style',
		plugin_dir_url( __FILE__ ) . 'css/gf-custom-fields.css',
		array(),
		'1.0'
	);
}
