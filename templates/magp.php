<?php
/*
 * Template Name: Custom MAGP Group Product
 */

// Get the product object.
global $product;

// Get linked products.
$linked_products = $product->get_meta('_magp_linked_products', true);
var_dump($linked_products);
// Output your custom HTML here.

		$quantites_required      = false;
		$previous_post           = $post;
		$grouped_product_columns = apply_filters(
			'woocommerce_grouped_product_columns',
			array(
				'quantity',
				'label',
				'price',
			),
			$product
		);
		$show_add_to_cart_button = false;

		do_action('woocommerce_grouped_product_list_before', $grouped_product_columns, $quantites_required, $product);
?>

<div class="custom-magp-group-product">
	<h1><?php echo $product->get_name(); ?></h1>

	<?php
	if ($linked_products) {
		foreach ($linked_products as $linked_product_id) {
			$grouped_product_child = wc_get_product($linked_product_id);

			if ($grouped_product_child) {
				echo '<div class="linked-product">';
				echo '<h2>' . $grouped_product_child->get_name() . '</h2>';

				// Check if the linked product is variable.
				if ($grouped_product_child->is_type('variable')) {
					echo "VARRRRRRRRRRRRRRRR";

					wp_enqueue_script('wc-add-to-cart-variation');


					$attributes           = $grouped_product_child->get_variation_attributes();
					$available_variations = $grouped_product_child->get_available_variations();
					$variations_json      = wp_json_encode($available_variations);
					$variations_attr      = function_exists('wc_esc_json') ? wc_esc_json($variations_json) : _wp_specialchars($variations_json, ENT_QUOTES, 'UTF-8', true);

					$attribute_keys  = array_keys($attributes);

					do_action('woocommerce_before_add_to_cart_form'); ?>
					<tr>
						<td colspan="3">

							<?php

							echo '<input type="checkbox" name="' . esc_attr('quantity[' . $grouped_product_child->get_id() . ']') . '" value="' . $grouped_product_child->get_id() . '" class="wc-grouped-product-add-to-cart-checkbox" id="' . esc_attr('quantity-' . $grouped_product_child->get_id()) . '" />';
							echo '<a style="margin-left: 7px; font-weight:bold; vertical-align: super" href="#">' . $grouped_product_child->get_name() . '</a><label for="' . esc_attr('quantity-' . $grouped_product_child->get_id()) . '" class="screen-reader-text">' . esc_html__('Buy one of this item', 'woocommerce') . '</label>';

							?>
							<form class="variations_form cart" action="<?php echo esc_url(apply_filters('woocommerce_add_to_cart_form_action', $product->get_permalink())); ?>" method="post" enctype='multipart/form-data' data-product_id="<?php echo absint($product->get_id()); ?>" data-product_variations="<?php echo $variations_attr; ?>">
								<?php do_action('woocommerce_before_variations_form');
								echo '<input type="hidden" class="variation_data" id="variation_data_' . $grouped_product_child->get_id() . '" name="variation_data[' . $grouped_product_child->get_id() . ']" value="">';

								echo '<input type="hidden" class="variation_id" id="variation_id_' . $grouped_product_child->get_id() . '" name="variation_id[' . $grouped_product_child->get_id() . ']" value="">';
								echo '<input type="hidden" class="variation_product_id" id="variation_product_id_' . $grouped_product_child->get_id() . '" name="variation_product_id[' . $grouped_product_child->get_id() . ']" value="' . $grouped_product_child->get_id() . '">';

								?>

								<?php if (empty($available_variations) && false !== $available_variations) : ?>
									<p class="stock out-of-stock"><?php echo esc_html(apply_filters('woocommerce_out_of_stock_message', __('This product is currently out of stock and unavailable.', 'woocommerce'))); ?></p>
								<?php else : ?>
									<table class="variations" cellspacing="0" role="presentation">
										<tbody>
											<?php foreach ($attributes as $attribute_name => $options) : ?>
												<tr>
													<th class="label"><label for="<?php echo esc_attr(sanitize_title($attribute_name)); ?>"><?php echo wc_attribute_label($attribute_name); // WPCS: XSS ok. 
																																			?></label></th>
													<td class="value">
														<?php
														wc_dropdown_variation_attribute_options(
															array(
																'options'   => $options,
																'attribute' => $attribute_name,
																'product'   => $grouped_product_child,
															)
														);
														echo end($attribute_keys) === $attribute_name ? wp_kses_post(apply_filters('woocommerce_reset_variations_link', '<a class="reset_variations" href="#">' . esc_html__('Clear', 'woocommerce') . '</a>')) : '';
														?>
													</td>
												</tr>
											<?php endforeach; ?>
										</tbody>
									</table>
									<?php do_action('woocommerce_after_variations_table'); ?>


									<div class="single_variation_wrap">
										<?php
										/**
										 * Hook: woocommerce_before_single_variation.
										 */
										do_action('woocommerce_before_single_variation');

										/**
										 * Hook: woocommerce_single_variation. Used to output the cart button and placeholder for variation data.
										 *
										 * @since 2.4.0
										 * @hooked woocommerce_single_variation - 10 Empty div for variation data.
										 * @hooked woocommerce_single_variation_add_to_cart_button - 20 Qty and cart button.
										 */
										do_action('woocommerce_single_variation'); //DON'T REMOVE MAY BE USEFUL									

										/**
										 * Hook: woocommerce_after_single_variation.
										 */
										do_action('woocommerce_after_single_variation');
										?>
									</div>
								<?php endif; ?>

								<?php do_action('woocommerce_after_variations_form'); ?>
							</form>
						</td>
					</tr>
	<?php
					do_action('woocommerce_after_add_to_cart_form');

					// Display variations.
					// woocommerce_template_single_add_to_cart();
				} else {
					// Display regular add to cart button.
					woocommerce_template_loop_add_to_cart();
				}

				echo '</div>';
			}
		}
	}
	?>
</div>