<?php

/**
 * Plugin Name: Art Clouds Grouped Product
 * Description: Plugin to add custom grouped product type to WooCommerce
 * Author: muhammad.begawala@gmail.com
 * Author URI: https://adbrains.in
 * Version: 1.0
 */


defined('ABSPATH') or exit;

add_action('init', 'register_m_group_product_type');
function register_m_group_product_type()
{
    class WC_Product_Artcloud_Group extends WC_Product
    {
        public $product_type;
        public function __construct($product)
        {
            $this->product_type = 'm_group';
            parent::__construct($product);
        }
    }
}


add_filter('product_type_selector', 'add_m_group_product_type');
function add_m_group_product_type($types)
{
    $types['m_group'] = __('Artcloud group product', 'dm_product');
    return $types;
}


add_filter('woocommerce_product_data_tabs', 'm_group_product_tab');
function m_group_product_tab($tabs)
{

    $tabs['m_group'] = array(
        'label' => __('Artcloud group Product', 'dm_product'),
        'target' => 'm_group_product_options',
        'class' => 'show_if_m_group_product',
    );
    return $tabs;
}


add_action('woocommerce_product_data_panels', 'm_group_product_tab_product_tab_content');
function m_group_product_tab_product_tab_content()
{
?><div id='m_group_product_options' class='panel woocommerce_options_panel'><?php
                                                                            ?><div class='options_group'><?php


                                woocommerce_wp_checkbox(array(
                                    'id' => '_enable_custom_product',
                                    'label' => __('Enable Custom product Type'),
                                ));


                                woocommerce_wp_text_input(
                                    array(
                                        'id' => 'm_group_product_info',
                                        'label' => __('Artcloud group product details', 'dm_product'),
                                        'placeholder' => 'Insert text to be shown on the front end here',
                                        'desc_tip' => 'true',
                                        'description' => __('Enter Artcloud group product Info.', 'dm_product'),
                                        'type' => 'text'
                                    )
                                );
                                ?></div>
    </div><?php
        }

        add_action('woocommerce_process_product_meta', 'save_m_group_product_settings');

        function save_m_group_product_settings($post_id)
        {

            $enable_custom_product = isset($_POST['_enable_custom_product']) ? 'yes' : 'no';
            update_post_meta($post_id, '_enable_custom_product', $enable_custom_product);
            $m_group_product_info = $_POST['m_group_product_info'];

            if (!empty($m_group_product_info)) {
                update_post_meta($post_id, 'm_group_product_info', esc_attr($m_group_product_info));
            }
        }


        add_action('woocommerce_single_product_summary', 'm_group_product_front');

        function m_group_product_front()
        {
            global $product;
            if ('m_group' == $product->get_type()) {
                echo (get_post_meta($product->get_id(), 'm_group_product_info')[0]);
            }
        }
