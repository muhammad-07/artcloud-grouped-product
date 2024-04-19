<?php

/**
 * Plugin Name: Art Clouds Grouped Product
 * Description: Plugin to add group grouped product type to WooCommerce
 * Author: muhammad.begawala@gmail.com
 * Author URI: https://adbrains.in
 * Version: 1.0
 */


defined('ABSPATH') or exit;

defined( 'ABSPATH' ) || exit(); // Exit if accessed directly.

// Define Constants.
defined( 'MAGP_PLUGIN_FILE' ) || define( 'MAGP_PLUGIN_FILE', plugin_dir_path( __FILE__ ) );

if ( ! class_exists( 'MAGP_Group_Product_Type' ) ) {

    /**
     * Group product type class.
     */
    class MAGP_Group_Product_Type {

        /**
         * Constructor.
         */
        public function __construct() {
            add_action( 'woocommerce_loaded', array( $this, 'magp_load_include_group_product' ) );

            add_filter( 'product_type_selector', array( $this, 'magp_add_group_product_type' ) );

            add_filter( 'woocommerce_product_data_tabs', array( $this, 'magp_modify_woocommerce_product_data_tabs' ) );

            add_action( 'woocommerce_product_data_panels', array( $this, 'magp_add_product_data_tab_content' ) );

            add_action( 'save_post', array( $this, 'magp_save_group_product_fields' ) );

            add_action( 'woocommerce_magp_group_product_add_to_cart', array( $this, 'magp_display_add_to_cart_button_on_single' ), 30 );

            add_filter( 'woocommerce_product_add_to_cart_text', array( $this, 'magp_add_to_cart_text' ), 10, 2 );
        }

        /**
         * Load group product.
         */
        public function magp_load_include_group_product() {
            require_once MAGP_PLUGIN_FILE . 'includes/class-wc-product-magp-group-product.php';
        }

        /**
         * Group product type.
         *
         * @param array $types Product types.
         *
         * @return void
         */
        public function magp_add_group_product_type( $types ) {
            $types['magp_group_product'] = esc_html__( 'Art Clouds Group Product', 'magp' );

            return $types;
        }

        /**
         * Modify product data tabs.
         *
         * @param array $tabs List of product data tabs.
         *
         * @return array $tabs Product data tabs.
         */
        public function magp_modify_woocommerce_product_data_tabs( $tabs ) {
            if ( 'product' === get_post_type() ) {
                ?>
                    <script type='text/javascript'>
                         document.addEventListener('DOMContentLoaded', () => {
                            let optionGroupPricing = document.querySelector('.options_group.pricing');
                            !!optionGroupPricing && optionGroupPricing.classList.add('show_if_magp_group_product');

                            let stockManagement = document.querySelector('._manage_stock_field');
                            !!stockManagement && stockManagement.classList.add('show_if_magp_group_product');

                            let soldIndividuallyDiv = document.querySelector('.inventory_sold_individually');
                            let soldIndividually = document.querySelector('._sold_individually_field');
                            !!soldIndividuallyDiv && soldIndividuallyDiv.classList.add('show_if_magp_group_product');
                            !!soldIndividually && soldIndividually.classList.add('show_if_magp_group_product');

                            <?php if ( 'yes' === get_option( 'woocommerce_calc_taxes' ) ) { ?>
                                let generalProductData = document.querySelectorAll('#general_product_data > .options_group');
                                let taxDiv = !!generalProductData && Array.from(generalProductData).at(-1);
                                !!taxDiv && taxDiv.classList.add('show_if_magp_group_product');
                            <?php } ?>
                         });
                     </script>
                <?php
            }

            foreach ( $tabs as $key => $val ) {
                $product_tabs = array( 'general', 'inventory' );

                if ( ! in_array( $key, $product_tabs ) ) {
                    $tabs[ $key ]['class'][] = 'hide_if_magp_group_product';
                } else {
                    $tabs['inventory']['class'][] = 'show_if_magp_group_product';
                }
            }

            // Add your group product data tabs.
            $group_tab = array(
                'magp_group' => array(
                    'label'    => __( 'Group product settings', 'magp' ),
                    'target'   => 'magp_cusotm_product_data_html',
                    'class'    => array( 'show_if_magp_group_product' ),
                    'priority' => 21,
                ),
            );

            return array_merge( $tabs, $group_tab );
        }

        /**
         * Add product data tab content.
         *
         * @return void
         */
        public function magp_add_product_data_tab_content() {
            global $product_object;

            ?>
            <div id="magp_cusotm_product_data_html" class="panel woocommerce_options_panel">
                <div class="options_group">
                    <?php
                        woocommerce_wp_text_input(
                            array(
                                'id'          => '_magp_name',
                                'label'       => esc_html__( 'Name', 'magp' ),
                                'value'       => $product_object->get_meta( '_magp_name', true ),
                                'default'     => '',
                                'placeholder' => esc_html__( 'Enter your name', 'magp' ),
                            )
                        );
                    ?>
                </div>
            </div>
            <?php
        }

        /**
         * Save group product fields function.
         *
         * @param int $post_id Post id.
         *
         * @return void
         */
        public function magp_save_group_product_fields( $post_id ) {
            if ( ! empty( $_POST['meta-box-order-nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['meta-box-order-nonce'] ), 'meta-box-order' ) ) {
                $post_data = ! empty( $_POST ) ? wc_clean( $_POST ) : array();

                if ( ! empty( $post_data['post_type'] ) && 'product' === $post_data['post_type'] && ! empty( $post_data['product-type'] ) && 'magp_group_product' === $post_data['product-type'] ) {
                    $name = ! empty( $post_data['_magp_name'] ) ? $post_data['_magp_name'] : '';

                    update_post_meta( $post_id, '_magp_name', $name );
                    update_post_meta( $post_id, '_virtual', 'yes' );
                    update_post_meta( $post_id, '_magp_group_product_meta_key', 'yes' );
                }
            }
        }

        /**
         * Display add to cart button on single product page.
         *
         * @return void
         */
        public function magp_display_add_to_cart_button_on_single() {
            wc_get_template( 'single-product/add-to-cart/simple.php' );
        }

        /**
         * Add to cart text on the gift card product.
         *
         * @param string $text Text on add to cart button.
         * @param object $product Product data.
         *
         * @return string $text Text on add to cart button.
         */
        public function magp_add_to_cart_text( $text, $product ) {
            if ( 'magp_group_product' === $product->get_type() ) {
                $text = $product->is_purchasable() && $product->is_in_stock() ? __( 'Add to cart', 'magp' ) : $text;
            }

            return $text;
        }

    }
}

new MAGP_Group_Product_Type();