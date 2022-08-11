<?php
namespace WOPB;

defined('ABSPATH') || exit;

class RequestAPI{

    public function __construct() {
        add_action('wp_ajax_wopb_new_post', array($this, 'wopb_new_post_callback'));
        add_action('wp_ajax_wopb_search', array($this, 'wopb_search_callback'));
        add_action('wp_ajax_wopb_edit', array($this, 'wopb_edit_callback'));
        add_action('delete_post', array($this, 'delete_option_meta_action'));
    }

    public function delete_option_meta_action( $post_id ) {
        if (get_post_type( $post_id ) != 'wopb_builder') {
            return;
        }

        $conditions = get_option('wopb_builder_conditions', array());
        if($conditions){
            if(isset($conditions['archive'][$post_id])) {
                unset($conditions['archive'][$post_id]);
                update_option('wopb_builder_conditions', $conditions);
            }
        }
        delete_post_meta($post_id, '_wopb_active');
    }

    public function wopb_edit_callback() {
        if (!wp_verify_nonce($_REQUEST['_wpnonce'], 'wopb-nonce')) {
            return ;
        }
        $results = array();
        $post_id = sanitize_text_field($_POST['post_id']);
        $results['title'] = get_the_title($post_id);
        $results['type'] = get_post_meta($post_id, '_wopb_builder_type', true);

        $options = get_option('wopb_builder_conditions', array());
        if (!empty($options)) {
            $temp = isset($options['archive'][$post_id]) ? $options['archive'][$post_id] : array();
            $results['conditions'] = $temp;
            
            $temp_tax = array();
            $taxonomy_list = wopb_function()->get_taxonomy_list();
            foreach ($temp as $val) {
                $val = explode('/', $val);
                if (isset($val[3])) {
                    if ($val[2] == 'author') {
                        $user = get_user_by('id', $val[3]);
                        $author_id[] = array('id' => $val[3], 'text' => $user->user_login );
                    } else if ($val[1] == 'single' && in_array($val[2], $taxonomy_list)) {
                        $term = get_term( $val[3], $val[2] );
                        $temp_tax['single-'.$val[2]][] = array('id' => $val[3], 'text' => $term->name );
                    } else if ($val[1] == 'archive' && in_array($val[2], $taxonomy_list)) {
                        $term = get_term( $val[3], $val[2] );
                        $temp_tax[$val[2]][] = array('id' => $val[3], 'text' => $term->name );
                    } else if ($val[1] == 'single' && $val[2] == 'product') {
                        $temp_tax['single-'.$val[2]][] = array('id' => $val[3], 'text' => get_the_title($val[3]));
                    }
                }
            }
            if (!empty($author_id)) {
                $results['author_id'] = $author_id;
            }
            if (!empty($temp_tax)) {
                foreach ($temp_tax as $key => $value) {
                    $results['taxonomy'][$key] = $value;
                }
            }
        }
        
        echo json_encode($results);
        die();
    }


    public function wopb_search_callback() {
        if (!wp_verify_nonce($_REQUEST['_wpnonce'], 'wopb-nonce')) {
            return ;
        }

        $results = array();
        $_type = sanitize_text_field($_POST['type']);
        $_term = sanitize_text_field($_POST['term']);

        if($_type == 'author') {
            $users = new \WP_User_Query( array(
                'search'         => '*'.$_term.'*',
                'search_columns' => array(
                    'user_login',
                    'user_nicename',
                    'user_email'
                ),
            ) );
            $users_found = $users->get_results();
            if (!empty($users_found)) {
                foreach ($users_found as $user) {
                    $results[] = array( 'id' => $user->data->ID, 'text' => $user->data->user_login );
                }
            }
            echo json_encode($results);
            die();
        } else if ($_type == 'single_product') {
            $results = array();
            $args = array(
                's'             => $_term,
                'post_type'     => 'product',
                'orderby'       => 'date',
                'post_status'   => 'publish',
                'order'         => 'DESC',
                'posts_per_page'=> 50
            );
            $loop = new \WP_Query($args);
            if ($loop->have_posts()) {
                while ($loop->have_posts()) {
                    $loop->the_post();
                    $results[] = array('id' => get_the_ID(), 'text' => get_the_title());
                }
            }
            echo json_encode($results);
            die();
        } else {
            $args = array(
                'taxonomy'      => array( $_type ),
                'orderby'       => 'id', 
                'order'         => 'ASC',
                'hide_empty'    => true,
                'fields'        => 'all',
                'name__like'    => $_term
            );

            $terms = get_terms( $args );
            if (!empty($terms)) {
                foreach ($terms as $term) {
                    $results[] = array( 'id' => $term->term_id, 'text' => $term->name );
                }
            }
            echo json_encode($results);
            die();
        }
        die();
    }




    public function wopb_new_post_callback() {
        if (!wp_verify_nonce($_REQUEST['_wpnonce'], 'wopb-nonce')){
            return ;
        }
        $filter = sanitize_text_field($_POST['post_filter']);

        $post_id = '';
        if (sanitize_text_field($_POST['operation']) == 'insert') {
            $post_id = wp_insert_post(
                array(
                    'post_title'   => sanitize_text_field($_POST['post_title']),
                    'post_content' => '',
                    'post_type'    => 'wopb_builder',
                    'post_status'  =>  'draft'
                )
            );
        } else {
            $post_id = sanitize_text_field($_POST['post_id']);
            wp_update_post( 
                array(
                    'ID'         => $post_id,
                    'post_title' => sanitize_text_field($_POST['post_title'])
                )
            );
        }

        $conditions = get_option('wopb_builder_conditions', array());
        if (empty($conditions)) {
            $conditions = array(
                'archive' => array()
            );
        }

        if ($post_id) {
            $temp = array();
            $temp[] = 'filter/'.$filter;
            if (isset($_POST['archive'])) {
                $temp[] = 'include/archive';
            }
            if (isset($_POST['search'])) {
                $temp[] = 'include/archive/search';
            }
            $taxonomy_list = wopb_function()->get_taxonomy_list();
            foreach ($taxonomy_list as $value) {
                if (isset($_POST[$value])) {
                    $temp[] = 'include/archive/'.$value;
                } 
            }
            foreach ($taxonomy_list as $value) {
                if (isset($_POST[$value.'_id'])) {
                    if (!empty($_POST[$value.'_id'])) {
                        foreach ($_POST[$value.'_id'] as $val) {
                            $temp[] = 'include/archive/'.$value.'/'.sanitize_text_field($val);
                        }
                    }
                }
                if (isset($_POST['single_'.$value.'_id'])) {
                    if (!empty($_POST['single_'.$value.'_id'])) {
                        foreach ($_POST['single_'.$value.'_id'] as $val) {
                            $temp[] = 'include/single/'.$value.'/'.sanitize_text_field($val);
                        }
                    }
                }
            }
            if (isset($_POST['single_product_id'])) {
                if (!empty($_POST['single_product_id'])) {
                    foreach ($_POST['single_product_id'] as $val) {
                        $temp[] = 'include/single/product/'.sanitize_text_field($val);
                    }
                }
            }
            if (isset($_POST['allsingle'])) {
                $temp[] = 'include/allsingle';
            }
            $conditions['archive'][$post_id] = $temp;

            update_option('wopb_builder_conditions', $conditions);
            update_post_meta($post_id, '_wopb_builder_type', sanitize_text_field($_POST['post_filter']) );

            //echo esc_url(get_edit_post_link($post_id));
            wp_send_json_success( get_edit_post_link($post_id) );
            die();
        }
    }
}