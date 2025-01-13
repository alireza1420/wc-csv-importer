<?php
/*
Plugin Name: WooCommerce CSV Importer
Plugin URI: https://github.com/yourusername/wc-csv-importer
Description: A plugin to import WooCommerce products via CSV file.
Version: 1.0
Author: Your Name
Author URI: https://yourwebsite.com/
License: GPL2
Text Domain: wc-csv-importer
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Hook to add admin menu
add_action( 'admin_menu', 'wc_csv_importer_menu' );

/**
 * Add a new submenu under WooCommerce
 */
function wc_csv_importer_menu() {
    add_submenu_page(
        'woocommerce',
        'CSV Importer',
        'CSV Importer',
        'manage_woocommerce',
        'wc-csv-importer',
        'wc_csv_importer_page'
    );
}

/**
 * Display the CSV Importer Page
 */
function wc_csv_importer_page() {
    // Check if user has submitted the form
    if ( isset( $_POST['wc_csv_importer_submit'] ) ) {
        wc_csv_importer_handle_upload();
    }

    // Display the upload form
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'WooCommerce CSV Importer', 'wc-csv-importer' ); ?></h1>
        <form enctype="multipart/form-data" method="post">
            <?php wp_nonce_field( 'wc_csv_importer_nonce', 'wc_csv_importer_nonce_field' ); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><label for="csv_file"><?php esc_html_e( 'CSV File', 'wc-csv-importer' ); ?></label></th>
                    <td><input type="file" id="csv_file" name="csv_file" accept=".csv" required /></td>
                </tr>
            </table>
            <?php submit_button( __( 'Import Products', 'wc-csv-importer' ), 'primary', 'wc_csv_importer_submit' ); ?>
        </form>
    </div>
    <?php
}

/**
 * Handle the CSV Upload and Import
 */
function wc_csv_importer_handle_upload() {
    // Check nonce for security
    if ( ! isset( $_POST['wc_csv_importer_nonce_field'] ) || ! wp_verify_nonce( $_POST['wc_csv_importer_nonce_field'], 'wc_csv_importer_nonce' ) ) {
        echo '<div class="notice notice-error"><p>' . __( 'Nonce verification failed.', 'wc-csv-importer' ) . '</p></div>';
        return;
    }

    // Check if file is uploaded without errors
    if ( isset( $_FILES['csv_file'] ) && $_FILES['csv_file']['error'] === UPLOAD_ERR_OK ) {
        $file_tmp_path = $_FILES['csv_file']['tmp_name'];
        $file_name = $_FILES['csv_file']['name'];
        $file_size = $_FILES['csv_file']['size'];
        $file_type = $_FILES['csv_file']['type'];

        $allowed_types = array( 'text/csv', 'text/plain', 'application/csv', 'application/vnd.ms-excel' );

        if ( ! in_array( $file_type, $allowed_types ) ) {
            echo '<div class="notice notice-error"><p>' . __( 'Invalid file type. Please upload a CSV file.', 'wc-csv-importer' ) . '</p></div>';
            return;
        }

        // Open the CSV file
        if ( ( $handle = fopen( $file_tmp_path, 'r' ) ) !== FALSE ) {
            $header = fgetcsv( $handle, 1000, ',' );
            if ( ! $header ) {
                echo '<div class="notice notice-error"><p>' . __( 'CSV file is empty or invalid.', 'wc-csv-importer' ) . '</p></div>';
                fclose( $handle );
                return;
            }

            $row_count = 0;
            $success_count = 0;
            $error_messages = array();

            while ( ( $data = fgetcsv( $handle, 1000, ',' ) ) !== FALSE ) {
                $row_count++;
                $product_data = array_combine( $header, $data );

                // Validate required fields
                if ( ! isset( $product_data['name'] ) || empty( $product_data['name'] ) ) {
                    $error_messages[] = sprintf( __( 'Row %d: Product name is missing.', 'wc-csv-importer' ), $row_count + 1 );
                    continue;
                }

                // Prepare product data
                $product = new WC_Product();

                $product->set_name( sanitize_text_field( $product_data['name'] ) );

                if ( isset( $product_data['description'] ) ) {
                    $product->set_description( wp_kses_post( $product_data['description'] ) );
                }

                if ( isset( $product_data['short_description'] ) ) {
                    $product->set_short_description( wp_kses_post( $product_data['short_description'] ) );
                }

                if ( isset( $product_data['price'] ) ) {
                    $product->set_regular_price( sanitize_text_field( $product_data['price'] ) );
                }

                if ( isset( $product_data['sale_price'] ) ) {
                    $product->set_sale_price( sanitize_text_field( $product_data['sale_price'] ) );
                }

                if ( isset( $product_data['sku'] ) ) {
                    $product->set_sku( sanitize_text_field( $product_data['sku'] ) );
                }

                if ( isset( $product_data['stock'] ) ) {
                    $product->set_stock_quantity( intval( $product_data['stock'] ) );
                    $product->set_manage_stock( true );
                }

                if ( isset( $product_data['categories'] ) ) {
                    $categories = array_map( 'trim', explode( ',', $product_data['categories'] ) );
                    $product->set_category_ids( wc_get_product_cat_ids( $categories ) );
                }

                if ( isset( $product_data['tags'] ) ) {
                    $tags = array_map( 'trim', explode( ',', $product_data['tags'] ) );
                    $product->set_tag_ids( wc_get_product_tag_ids( $tags ) );
                }

                if ( isset( $product_data['type'] ) ) {
                    $product->set_type( sanitize_text_field( $product_data['type'] ) );
                } else {
                    $product->set_type( 'simple' );
                }

                // Set product status
                if ( isset( $product_data['status'] ) && in_array( $product_data['status'], array( 'publish', 'draft', 'private' ), true ) ) {
                    $product->set_status( sanitize_text_field( $product_data['status'] ) );
                } else {
                    $product->set_status( 'publish' );
                }

                // Save the product
                try {
                    $product_id = $product->save();
                    if ( $product_id ) {
                        $success_count++;
                    } else {
                        $error_messages[] = sprintf( __( 'Row %d: Failed to create product.', 'wc-csv-importer' ), $row_count + 1 );
                    }
                } catch ( Exception $e ) {
                    $error_messages[] = sprintf( __( 'Row %d: %s', 'wc-csv-importer' ), $row_count + 1, $e->getMessage() );
                }
            }

            fclose( $handle );

            // Display success and error messages
            echo '<div class="notice notice-success"><p>' . sprintf( __( 'Import completed. %d products added successfully.', 'wc-csv-importer' ), $success_count ) . '</p></div>';

            if ( ! empty( $error_messages ) ) {
                echo '<div class="notice notice-error"><ul>';
                foreach ( $error_messages as $message ) {
                    echo '<li>' . esc_html( $message ) . '</li>';
                }
                echo '</ul></div>';
            }

        } else {
            echo '<div class="notice notice-error"><p>' . __( 'Failed to open the uploaded CSV file.', 'wc-csv-importer' ) . '</p></div>';
        }

    } else {
        echo '<div class="notice notice-error"><p>' . __( 'No file uploaded or there was an upload error.', 'wc-csv-importer' ) . '</p></div>';
    }
}

/**
 * Helper function to get category IDs by names. Creates categories if they don't exist.
 */
function wc_get_product_cat_ids( $categories ) {
    $cat_ids = array();
    foreach ( $categories as $category_name ) {
        $term = term_exists( $category_name, 'product_cat' );
        if ( $term !== 0 && $term !== null ) {
            $cat_ids[] = $term['term_id'];
        } else {
            $new_term = wp_insert_term( $category_name, 'product_cat' );
            if ( ! is_wp_error( $new_term ) ) {
                $cat_ids[] = $new_term['term_id'];
            }
        }
    }
    return $cat_ids;
}

/**
 * Helper function to get tag IDs by names. Creates tags if they don't exist.
 */
function wc_get_product_tag_ids( $tags ) {
    $tag_ids = array();
    foreach ( $tags as $tag_name ) {
        $term = term_exists( $tag_name, 'product_tag' );
        if ( $term !== 0 && $term !== null ) {
            $tag_ids[] = $term['term_id'];
        } else {
            $new_term = wp_insert_term( $tag_name, 'product_tag' );
            if ( ! is_wp_error( $new_term ) ) {
                $tag_ids[] = $new_term['term_id'];
            }
        }
    }
    return $tag_ids;
}
