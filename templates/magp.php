<?php
/*
 * Template Name: Custom MAGP Group Product
 */

// Get the product object.
global $product;

// Get linked products.
$linked_products = $product->get_meta('_magp_linked_products', true);

$post_object        = get_post($product->get_id());
$post               = $post_object;

$quantites_required      = false;
$previous_post           = $post ?? null;
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
<style>
	.wc-grouped-product-add-to-cart-checkbox {
		-webkit-appearance: none;
		-moz-appearance: none;
		appearance: none;
		width: 18px;
		height: 18px;
		border-radius: 4px;
		padding: 2px;
		position: relative;
		background-color: #EB3E97;
	}

	.wc-grouped-product-add-to-cart-checkbox::before {
		content: '';
		display: block;
		width: 11px;
		height: 6px;
		position: absolute;
		top: 38%;
		left: 50%;
		transform: translate(-50%, -50%) rotate(-45deg);
		border-color: #fff;
		border-style: solid;
		border-width: 0 0 2px 2px;
		opacity: 0;
	}

	.wc-grouped-product-add-to-cart-checkbox:checked::before {
		opacity: 1;
	}

	.single_variation {
		margin-bottom: 0 !important;
	}

	.variations_button {
		display: none !important;
	}
</style>
<div class="custom-magp-group-product">
	<h1><?php echo $product->get_name(); ?></h1>

	<table cellspacing="0" class="woocommerce-grouped-product-list group_table">
		<tbody>
			<?php
			$added_script = false;
			if ($linked_products) {
				foreach ($linked_products as $linked_product_id) {
					$grouped_product_child = wc_get_product($linked_product_id);

					if ($grouped_product_child) {
						// echo '<div class="linked-product">';
						// echo '<h2>' . $grouped_product_child->get_name() . '</h2>';


						if ($grouped_product_child->is_type('variable')) {

							if(!$added_script){
							wp_enqueue_script('wc-add-to-cart-variation');
							$added_script = true;
							}

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
							$post_object        = get_post($grouped_product_child->get_id());
							$quantites_required = $quantites_required || ($grouped_product_child->is_purchasable() && !$grouped_product_child->has_options());
							$post               = $post_object; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
							setup_postdata($post);

							if ($grouped_product_child->is_in_stock()) {
								$show_add_to_cart_button = true;
							}

							echo '<tr id="product-' . esc_attr($grouped_product_child->get_id()) . '" class="woocommerce-grouped-product-list-item ' . esc_attr(implode(' ', wc_get_product_class('', $grouped_product_child))) . '">';

							// Output columns for each product.
							foreach ($grouped_product_columns as $column_id) {
								do_action('woocommerce_grouped_product_list_before_' . $column_id, $grouped_product_child);

								switch ($column_id) {
									case 'quantity':
										ob_start();

										if (!$grouped_product_child->is_purchasable() || $grouped_product_child->has_options() || !$grouped_product_child->is_in_stock()) {
											echo '<input type="checkbox" disabled="disabled" class="wc-grouped-product-add-to-cart-checkbox" /> Product is currently unavailable';

											// woocommerce_template_loop_add_to_cart();
										} else { //if ($grouped_product_child->is_sold_individually()) {
											// echo  $grouped_product_child->get_id();
											echo '<input type="checkbox" name="' . esc_attr('quantity[' . $grouped_product_child->get_id() . ']') . '" value="' . $grouped_product_child->get_id() . '" class="wc-grouped-product-add-to-cart-checkbox" id="' . esc_attr('quantity-' . $grouped_product_child->get_id()) . '" />';
											echo '<label for="' . esc_attr('quantity-' . $grouped_product_child->get_id()) . '" class="screen-reader-text">' . esc_html__('Buy one of this item', 'woocommerce') . '</label>';
										}
										// else {
										// 	echo "AAA";
										// 	do_action('woocommerce_before_add_to_cart_quantity');

										// 	woocommerce_quantity_input(
										// 		array(
										// 			'input_name'  => 'quantity[' . $grouped_product_child->get_id() . ']',
										// 			'input_value' => isset($_POST['quantity'][$grouped_product_child->get_id()]) ? wc_stock_amount(wc_clean(wp_unslash($_POST['quantity'][$grouped_product_child->get_id()]))) : '', // phpcs:ignore WordPress.Security.NonceVerification.Missing
										// 			'min_value'   => apply_filters('woocommerce_quantity_input_min', 0, $grouped_product_child),
										// 			'max_value'   => apply_filters('woocommerce_quantity_input_max', $grouped_product_child->get_max_purchase_quantity(), $grouped_product_child),
										// 			'placeholder' => '0',
										// 		)
										// 	);

										// 	do_action('woocommerce_after_add_to_cart_quantity');
										// }

										$value = ob_get_clean();
										break;
									case 'label':
										$value  = '<label for="product-' . esc_attr($grouped_product_child->get_id()) . '">';
										$value .= $grouped_product_child->is_visible() ? '<a style="font-weight:bold" href="' . esc_url(apply_filters('woocommerce_grouped_product_list_link', $grouped_product_child->get_permalink(), $grouped_product_child->get_id())) . '">' . $grouped_product_child->get_name() . '</a>' : $grouped_product_child->get_name();
										$value .= '</label>';
										break;
									case 'price':
										$value = $grouped_product_child->get_price_html() . wc_get_stock_html($grouped_product_child);
										break;
									default:
										$value = '';
										break;
								}

								echo '<td class="woocommerce-grouped-product-list-item__' . esc_attr($column_id) . '">' . apply_filters('woocommerce_grouped_product_list_column_' . $column_id, $value, $grouped_product_child) . '</td>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

								do_action('woocommerce_grouped_product_list_after_' . $column_id, $grouped_product_child);
							}

							echo '</tr>';
						}

						// echo '</div>';
					}
				}
				$post = $previous_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
				setup_postdata($post);

				do_action('woocommerce_grouped_product_list_after', $grouped_product_columns, $quantites_required, $product);
			}
			?>
		</tbody>
	</table>

	<button class="single_add_to_cart_button button alt wp-element-button" id="add-selected-to-cart"><?php echo esc_html($product->single_add_to_cart_text()); ?></button>
	<script>
		var variation_data = '';

		document.addEventListener('DOMContentLoaded', function() {
			// $(".single_add_to_cart_button").hide();
			document.getElementById('add-selected-to-cart').addEventListener('click', function() {
				var selectedProducts = [];
				var variation_ids = [];
				var variation_product_ids = [];
				var variation_datas = [];
				// var checkboxes = document.querySelectorAll('.wc-grouped-product-add-to-cart-checkbox');

				jQuery('.wc-grouped-product-add-to-cart-checkbox').each(function() {
					if (jQuery(this).is(":checked")) {
						k = jQuery(this).val();
						selectedProducts.push(jQuery(this).val());
						console.log(jQuery("#variation_id_" + k).val() || "000" + k);
						variation_ids.push(jQuery("#variation_id_" + k).val() || 0);
						variation_product_ids.push(jQuery("#variation_product_id_" + k).val() || 0);
						variation_datas.push(jQuery("#variation_data_" + k).val() || 0);

					}

				});
				console.log("selectedProducts" + selectedProducts);
				console.log("variation_ids" + variation_ids);
				console.log("variation_product_ids" + variation_product_ids);
				console.log("variation_datas" + variation_datas);
				addToCart(selectedProducts, variation_ids, variation_product_ids, variation_datas);
			});

			function addToCart(products, variation_ids, variation_product_ids, variation_datas) {
				var data = {
					action: 'add_grouped_products_to_cart',
					products: products,
					variation_id: variation_ids,
					variation_product_id: variation_product_ids,
					variation_data: variation_datas
				};

				jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) {

					console.log(response);
					response = JSON.stringify(response);
					console.log(response.success);
					window.location = '/cart';
					if (response && response.success == true) {
						window.location = '/cart';
					}
				});
			}
		});
	</script>

	<script>
		document.addEventListener('DOMContentLoaded', function() {
			jQuery('.variations_form').on('change', '.variations select', function() {

				var variationData = {};
				var $form = jQuery(this).closest('.variations_form');

				$form.find('.variations select').each(function() {
					var attribute = jQuery(this).data('attribute_name');
					var value = jQuery(this).val();
					variationData[attribute] = value;
				});

				$form.trigger('variation_selected', [variationData]);
			});
		});
		jQuery('.variations_form').on('variation_selected', function(event, variationData) {
			var $form = jQuery(this);
			$form.find('.variation_data').val(JSON.stringify(variationData));
			$form.find('.variation_id').val(variationData.variation_id);
		});
	</script>
</div>