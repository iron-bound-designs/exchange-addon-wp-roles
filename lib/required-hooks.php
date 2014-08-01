<?php
/**
 * File Description
 *
 * @author timothybjacobs
 * @since  7/28/14
 */

/**
 * Assign the selected role to the transaction customer
 *
 * @param $transaction_id int
 * @param $store_old      boolean
 */
function it_exchange_wpr_assign_role( $transaction_id, $store_old = true ) {

	if ( ! it_exchange_transaction_is_cleared_for_delivery( $transaction_id ) ) {
		return;
	}

	$user_id = it_exchange_get_transaction_customer_id( $transaction_id );
	$user    = get_user_by( 'id', $user_id );

	foreach ( it_exchange_get_transaction_products( $transaction_id ) as $product ) {
		$product_id = $product['product_id'];

		if ( it_exchange_product_has_feature( $product_id, 'wp-roles' ) ) {

			if ( $store_old ) {
				update_user_meta( $user_id, '_it_exchange_wpr_prev_role', $user->roles[0] );
			}

			$role_slug = it_exchange_get_product_feature( $product_id, 'wp-roles' );
			$user->set_role( $role_slug );

			return;
		}
	}
}

/**
 * Revert user's role to previous role
 * before it was set
 *
 * @param $transaction_id int
 */
function it_exchange_wpr_revert_role( $transaction_id ) {
	$user_id = it_exchange_get_transaction_customer_id( $transaction_id );
	$user    = get_user_by( 'id', $user_id );

	$prev_role = get_user_meta( $user_id, '_it_exchange_wpr_prev_role', true );

	if ( empty( $prev_role ) ) {
		$prev_role = get_option( 'default_role', 'subscriber' );
	}

	$user->set_role( $prev_role );
}


/**
 * When a transaction is made that is cleared for delivery
 * assign requested roles.
 *
 * @param $transaction_id int
 */
function it_exchange_wpr_assign_role_on_transaction_success( $transaction_id ) {

	if ( ! it_exchange_transaction_is_cleared_for_delivery( $transaction_id ) ) {
		return;
	}

	it_exchange_wpr_assign_role( $transaction_id );
}

add_action( 'it_exchange_add_transaction_success', 'it_exchange_wpr_assign_role_on_transaction_success' );

/**
 * When a transaction is transitioned from cleared to not cleared,
 * assign requested roles.
 *
 * @param $transaction        IT_Exchange_Transaction
 * @param $old_status         string
 * @param $old_status_cleared bool
 */
function it_exchange_wpr_assign_role_on_cleared_for_delivery( $transaction, $old_status, $old_status_cleared ) {
	$old_status_cleared = (bool) $old_status_cleared;

	/*
	 * We have to re-pull the transaction from the database,
	 * so that the transaction status is properly determined
	 */
	$transaction = it_exchange_get_transaction( $transaction->ID );

	/*
	 * If the transaction is cleared for delivery now,
	 * and the transaction wasn't cleared before this transition,
	 * assign the requested role.
	 */
	if ( it_exchange_transaction_is_cleared_for_delivery( $transaction ) && $old_status_cleared == false ) {
		it_exchange_wpr_assign_role( $transaction->ID );

		return;
	}

	/*
	 * If the transaction is no longer cleared for delivery,
	 * and the transaction was previously cleared,
	 * revert to previous role.
	 */
	if ( it_exchange_transaction_is_cleared_for_delivery( $transaction ) == false && $old_status_cleared == true ) {
		it_exchange_wpr_revert_role( $transaction->ID );

		return;
	}
}

add_action( 'it_exchange_update_transaction_status', 'it_exchange_wpr_assign_role_on_cleared_for_delivery', 10, 3 );

/**
 * When a subscriber status is changed,
 * update the roles.
 *
 * @param $transaction IT_Exchange_Transaction
 * @param $status      string
 */
function it_exchange_wpr_assign_role_on_subscriber_status_change( $transaction, $status ) {

	if ( $status == 'deactivated' || $status == 'cancelled' ) {
		it_exchange_wpr_revert_role( $transaction->ID );

		return;
	}

	if ( $status == 'active' ) {
		it_exchange_wpr_assign_role( $transaction->ID, false );

		return;
	}

}

add_action( 'it_exchange_recurring_payments_addon_update_transaction_subscriber_status', 'it_exchange_wpr_assign_role_on_subscriber_status_change', 10, 2 );