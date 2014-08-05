<?php

/**
 * File Description
 *
 * @author timothybjacobs
 * @since  7/28/14
 */
class ITEWPR_ProductFeature_WPRoles extends IT_Exchange_Product_Feature_Abstract {
	/**
	 *
	 */
	function __construct() {
		$args = array(
			'slug'          => 'wp-roles',
			'metabox_title' => __( 'Assign WP Roles', IT_Exchange_WP_Roles_Addon::SLUG )
		);

		$this->metabox_title = $args['metabox_title'];

		parent::IT_Exchange_Product_Feature_Abstract( $args );
	}

	/**
	 * This echos the feature metabox.
	 *
	 * @since 1.7.27
	 * @return void
	 */
	function print_metabox( $post ) {
		$selected = it_exchange_get_product_feature( $post->ID, $this->slug );
		?>
		<p><?php _e( "Use these options to assign a role to a customer upon successful purchase of this product.", IT_Exchange_WP_Roles_Addon::SLUG ); ?></p>
		<p><?php _e( "If purchasing a membership product, the users role will be reverted to the default role when their subscription expires", IT_Exchange_WP_Roles_Addon::SLUG ); ?></p>

		<label for="itewpr-role-select"><?php _e( "Select a role", IT_Exchange_WP_Roles_Addon::SLUG ); ?></label>
		<select id="itewpr-role-select" name="ite_wpr_role_select">
			<option value="-1"> -- Disabled --</option>
			<?php wp_dropdown_roles( $selected ); ?>
		</select>

	<?php
	}

	/**
	 * This saves the value
	 *
	 * @since 1.7.27
	 *
	 * @return void
	 */
	function save_feature_on_product_save() {
		// Abort if we don't have a product ID
		$product_id = empty( $_POST['ID'] ) ? false : $_POST['ID'];
		if ( ! $product_id ) {
			return;
		}

		if ( ! empty( $_POST['ite_wpr_role_select'] ) && $_POST['ite_wpr_role_select'] != '-1' ) {
			$selected = sanitize_text_field( $_POST['ite_wpr_role_select'] );
		} else {
			$selected = false;
		}

		it_exchange_update_product_feature( $product_id, $this->slug, $selected );
	}

	/**
	 * This updates the feature for a product
	 *
	 * @since 1.7.27
	 *
	 * @param integer $product_id the product id
	 * @param mixed   $new_value  the new value
	 * @param array   $options
	 *
	 * @return boolean
	 */
	function save_feature( $product_id, $new_value, $options = array() ) {
		if ( ! it_exchange_get_product( $product_id ) ) {
			return false;
		}

		$new_value = sanitize_text_field( $new_value );

		return update_post_meta( $product_id, '_it-exchange-' . $this->slug, $new_value );
	}

	/**
	 * Return the product's features
	 *
	 * @since 1.7.27
	 *
	 * @param mixed   $existing   the values passed in by the WP Filter API. Ignored here.
	 * @param integer $product_id the WordPress post ID
	 * @param array   $options
	 *
	 * @return string product feature
	 */
	function get_feature( $existing, $product_id, $options = array() ) {
		return get_post_meta( $product_id, '_it-exchange-' . $this->slug, true );
	}

	/**
	 * Does the product have the feature?
	 *
	 * @since 1.7.27
	 *
	 * @param mixed   $result Not used by core
	 * @param integer $product_id
	 * @param array   $options
	 *
	 * @return boolean
	 */
	function product_has_feature( $result, $product_id, $options = array() ) {
		if ( false === it_exchange_product_supports_feature( $product_id, $this->slug ) ) {
			return false;
		}

		return (boolean) it_exchange_get_product_feature( $product_id, $this->slug );
	}

	/**
	 * Does the product support this feature?
	 *
	 * This is different than if it has the feature, a product can
	 * support a feature but might not have the feature set.
	 *
	 * @since 1.7.27
	 *
	 * @param mixed   $result Not used by core
	 * @param integer $product_id
	 * @param array   $options
	 *
	 * @return boolean
	 */
	function product_supports_feature( $result, $product_id, $options = array() ) {
		$product_type = it_exchange_get_product_type( $product_id );
		if ( ! it_exchange_product_type_supports_feature( $product_type, $this->slug ) ) {
			return false;
		}

		return true;
	}

}