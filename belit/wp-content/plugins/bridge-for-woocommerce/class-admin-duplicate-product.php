<?php

/**
 * Duplicate product functionality
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('WC_Bridge_Admin_Duplicate_Product')) {

    /**
     * WC_Bridge_Admin_Duplicate_Product Class.
     */
    class WC_Bridge_Admin_Duplicate_Product {

        /**
         * Constructor.
         */
        public function __construct() {
            add_action('tc_after_event_duplication', 'WC_Bridge_Admin_Duplicate_Product::duplicate_event_ticket_types', 10, 4);
        }

        function duplicate_event_ticket_types($new_event_id, $old_event_id, $caller, $caller_id) {
            global $wpdb;

            $old_event = new TC_Event($old_event_id);

            $old_ticket_types = WC_Bridge_Admin_Duplicate_Product::tc_wc_get_event_ticket_types(array('publish', 'draft', 'pending', 'private'), $old_event_id, false);

            $old_and_new_ticket_types = array();
            
            foreach ($old_ticket_types as $old_ticket_type_id) {
                $new_id = WC_Bridge_Admin_Duplicate_Product::duplicate_product_action($old_ticket_type_id, $new_event_id);
                $old_and_new_ticket_types[] = array($old_ticket_type_id, $new_id);
            }
            
            do_action('tc_after_product_duplication', $new_event_id, $old_event_id, $caller, $caller_id, $old_and_new_ticket_types);
        }

        public static function tc_wc_get_event_ticket_types($post_status, $event_id, $show_variations) {

            $args = array(
                'post_type' => array('product'),
                'post_status' => $post_status,
                'posts_per_page' => -1,
                'meta_query' => array(
                    'relation' => 'OR',
                    array(
                        'key' => '_event_name',
                        'compare' => '=',
                        'value' => $event_id
                    ),
                    array(
                        'key' => 'event_name',
                        'compare' => '=',
                        'value' => $event_id
                    ),
                )
            );

            $ticket_types = get_posts($args);

            $ticket_type_index = 0;

            foreach ($ticket_types as $ticket_type) {
                if (get_post_type($ticket_type->ID) == 'product') {

                    $variations_args = array(
                        'posts_per_page' => -1,
                        'post_type' => 'product_variation',
                        'post_status' => $post_status,
                        'post_parent' => $ticket_type->ID
                    );

                    $ticket_type_variations = get_posts($variations_args);
                    if (count($ticket_type_variations)) {
                        //Product has variations
                        if (apply_filters('woo_modify_get_event_ticket_types_show_variations', $show_variations) == true) {
                            $ticket_types = array_merge($ticket_types, $ticket_type_variations);
                        }
                    }
                }
                $ticket_type_index++;
            }

            foreach ($ticket_types as $ticket_type) {
                $ticket_ids[] = (int) $ticket_type->ID;
            }

            return $ticket_ids;
        }

        /**
         * Duplicate a product action.
         */
        public static function duplicate_product_action($product_id, $new_event_id) {
            $post = WC_Bridge_Admin_Duplicate_Product::get_product_to_duplicate($product_id);
            $new_id = WC_Bridge_Admin_Duplicate_Product::duplicate_product($post);
            update_post_meta($new_id, '_event_name', $new_event_id);
            return $new_id;
        }

        /**
         * Function to create the duplicate of the product.
         *
         * @param mixed $post
         * @param int $parent (default: 0)
         * @param string $post_status (default: '')
         * @return int
         */
        public static function duplicate_product($post, $parent = 0, $post_status = '') {
            global $wpdb;

            $new_post_author = wp_get_current_user();
            $new_post_date = current_time('mysql');
            $new_post_date_gmt = get_gmt_from_date($new_post_date);

            if ($parent > 0) {
                $post_parent = $parent;
                $post_status = $post_status ? $post_status : 'publish';
                $suffix = '';
                $post_title = $post->post_title;
            } else {
                $post_parent = $post->post_parent;
                $post_status = $post->post_status;//$post_status ? $post_status : 'publish';
                $suffix = '';
                $post_title = $post->post_title . $suffix;
            }

            // Insert the new template in the post table
            $wpdb->insert(
                    $wpdb->posts, array(
                'post_author' => $new_post_author->ID,
                'post_date' => $new_post_date,
                'post_date_gmt' => $new_post_date_gmt,
                'post_content' => $post->post_content,
                'post_content_filtered' => $post->post_content_filtered,
                'post_title' => $post_title,
                'post_excerpt' => $post->post_excerpt,
                'post_status' => $post_status,
                'post_type' => $post->post_type,
                'comment_status' => $post->comment_status,
                'ping_status' => $post->ping_status,
                'post_password' => $post->post_password,
                'to_ping' => $post->to_ping,
                'pinged' => $post->pinged,
                'post_modified' => $new_post_date,
                'post_modified_gmt' => $new_post_date_gmt,
                'post_parent' => $post_parent,
                'menu_order' => $post->menu_order,
                'post_mime_type' => $post->post_mime_type
                    )
            );

            $new_post_id = $wpdb->insert_id;

            // Set title for variations
            if ('product_variation' === $post->post_type) {
                $post_title = sprintf(__('Variation #%s of %s', 'tc'), absint($new_post_id), esc_html(get_the_title($post_parent)));
                $wpdb->update(
                        $wpdb->posts, array(
                    'post_title' => $post_title,
                        ), array(
                    'ID' => $new_post_id
                        )
                );
            }

            // Set name and GUID
            //if (!in_array($post_status, array('draft', 'pending', 'auto-draft'))) {
            $wpdb->update(
                    $wpdb->posts, array(
                'post_name' => wp_unique_post_slug(sanitize_title($post_title, $new_post_id), $new_post_id, $post_status, $post->post_type, $post_parent),
                'guid' => get_permalink($new_post_id),
                    ), array(
                'ID' => $new_post_id
                    )
            );
            //}
            // Copy the taxonomies
            WC_Bridge_Admin_Duplicate_Product::duplicate_post_taxonomies($post->ID, $new_post_id, $post->post_type);

            // Copy the meta information
            WC_Bridge_Admin_Duplicate_Product::duplicate_post_meta($post->ID, $new_post_id);

            // Copy the children (variations)
            $exclude = apply_filters('woocommerce_duplicate_product_exclude_children', false);

            if (!$exclude && ( $children_products = get_children('post_parent=' . $post->ID . '&post_type=product_variation') )) {
                foreach ($children_products as $child) {
                    WC_Bridge_Admin_Duplicate_Product::duplicate_product(WC_Bridge_Admin_Duplicate_Product::get_product_to_duplicate($child->ID), $new_post_id, $child->post_status);
                }
            }

            // Clear cache
            clean_post_cache($new_post_id);

            return $new_post_id;
        }

        /**
         * Get a product from the database to duplicate.
         *
         * @param mixed $id
         * @return WP_Post|bool
         * @todo Returning false? Need to check for it in...
         * @see duplicate_product
         */
        public static function get_product_to_duplicate($id) {
            global $wpdb;

            $id = absint($id);

            if (!$id) {
                return false;
            }

            $post = $wpdb->get_results("SELECT * FROM $wpdb->posts WHERE ID=$id");

            if (isset($post->post_type) && 'revision' === $post->post_type) {
                $id = $post->post_parent;
                $post = $wpdb->get_results("SELECT * FROM $wpdb->posts WHERE ID=$id");
            }

            return $post[0];
        }

        /**
         * Copy the taxonomies of a post to another post.
         *
         * @param mixed $id
         * @param mixed $new_id
         * @param mixed $post_type
         */
        public static function duplicate_post_taxonomies($id, $new_id, $post_type) {
            $exclude = array_filter(apply_filters('woocommerce_duplicate_product_exclude_taxonomies', array()));
            $taxonomies = array_diff(get_object_taxonomies($post_type), $exclude);

            foreach ($taxonomies as $taxonomy) {
                $post_terms = wp_get_object_terms($id, $taxonomy);
                $post_terms_count = sizeof($post_terms);

                for ($i = 0; $i < $post_terms_count; $i++) {
                    wp_set_object_terms($new_id, $post_terms[$i]->slug, $taxonomy, true);
                }
            }
        }

        /**
         * Copy the meta information of a post to another post.
         *
         * @param mixed $id
         * @param mixed $new_id
         */
        public static function duplicate_post_meta($id, $new_id) {
            global $wpdb;

            $sql = $wpdb->prepare("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id = %d", absint($id));
            $exclude = array_map('esc_sql', array_filter(apply_filters('tc_wc_bridge_duplicate_product_exclude_meta', array('total_sales', '_wc_average_rating', '_wc_rating_count', '_wc_review_count'))));

            if (sizeof($exclude)) {
                $sql .= " AND meta_key NOT IN ( '" . implode("','", $exclude) . "' )";
            }

            $post_meta = $wpdb->get_results($sql);

            if (sizeof($post_meta)) {
                $sql_query_sel = array();
                $sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";

                foreach ($post_meta as $post_meta_row) {
                    $sql_query_sel[] = $wpdb->prepare("SELECT %d, %s, %s", $new_id, $post_meta_row->meta_key, $post_meta_row->meta_value);
                }

                $sql_query .= implode(" UNION ALL ", $sql_query_sel);
                $wpdb->query($sql_query);
            }
        }

    }

}

return new WC_Bridge_Admin_Duplicate_Product();
