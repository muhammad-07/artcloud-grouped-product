<?php
/**
 * Define custom product type.
 *
 * @package WooCommerce Group Product Type
 */

defined( 'ABSPATH' ) || exit(); // Exit if accessed directly.

if ( ! class_exists( 'WC_Product_MAGP_Group_Product' ) ) {

    /**
     * Group Product class.
     */
    class WC_Product_MAGP_Group_Product extends WC_Product {

        /**
         * Constructor of this class.
         *
         * @param object $product product.
         */
        // public $product_type, $virtual;
        public function __construct( $product ) {
            $this->product_type = 'magp_group_product';
            $this->virtual      = 'yes';
            $this->supports[]   = 'ajax_add_to_cart';

            parent::__construct( $product );
        }

        /**
         * Return the product type.
         *
         * @return string
         */
        public function get_type() {
            return 'magp_group_product';
        }

    }
}