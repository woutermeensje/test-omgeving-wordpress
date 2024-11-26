<?php
/**
 * Filters in `[jobs]` shortcode.
 *
 * This template can be overridden by copying it to yourtheme/job_manager/job-filters.php.
 *
 * @package wp-job-manager
 * @version 1.38.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

wp_enqueue_script( 'wp-job-manager-ajax-filters' );

do_action( 'job_manager_job_filters_before', $atts );
?>

<form class="job_filters">
    <div class="form_flex_wrapper">
        <?php do_action( 'job_manager_job_filters_start', $atts ); ?>

        <div class="search_keywords">
            <input type="text" name="search_keywords" id="search_keywords" placeholder="<?php esc_attr_e( 'Zoek op woord...', 'wp-job-manager' ); ?>" value="<?php echo esc_attr( $keywords ); ?>" />
        </div>

        <div class="search_categories">
            <select name="search_categories[]" id="search_categories" class="custom-multi-select" multiple="multiple">
                <option value=""><?php esc_html_e( 'Any category', 'wp-job-manager' ); ?></option>
                <?php
                $terms = get_terms([
                    'taxonomy' => 'job_listing_category',
                    'orderby' => 'name',
                    'hide_empty' => true,
                ]);

                if ( ! empty( $terms ) ) {
                    foreach ( $terms as $term ) {
                        echo '<option value="' . esc_attr( $term->slug ) . '">' . esc_html( $term->name ) . '</option>';
                    }
                }
                ?>
            </select>
        </div>

        <div class="search_type">
            <select name="search_types[]" class="custom-select" id="search_types" multiple="multiple">
                <?php foreach ( get_job_listing_types() as $type ) : ?>
                    <option value="<?php echo esc_attr( $type->term_taxonomy_id ); ?>">
                        <?php echo esc_html( $type->name ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="search_location">
            <input type="text" name="search_location" id="search_location" placeholder="<?php esc_attr_e( 'Stad of plaats', 'wp-job-manager' ); ?>" value="<?php echo esc_attr( $location ); ?>" />
        </div>

        <?php do_action( 'job_manager_job_filters_end', $atts ); ?>
    </div>
</form>

<?php do_action( 'job_manager_job_filters_after', $atts ); ?>

<noscript><?php esc_html_e( 'Your browser does not support JavaScript, or it is disabled. JavaScript must be enabled in order to view listings.', 'wp-job-manager' ); ?></noscript>
