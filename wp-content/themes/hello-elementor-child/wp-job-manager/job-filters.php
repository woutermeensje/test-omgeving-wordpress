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
    
        <?php do_action( 'job_manager_job_filters_start', $atts ); ?>

        <!-- Basic Search -->
        <div class="search-basic">

            <div class="search_keywords">
                <input 
                    type="text" 
                    name="search_keywords" 
                    id="search_keywords" 
                    placeholder="<?php esc_attr_e( 'Zoek op woord...', 'wp-job-manager' ); ?>" 
                    value="<?php echo esc_attr( $keywords ); ?>" 
                />
            </div>

            <div class="search_location">
                <input 
                    type="text" 
                    name="search_location" 
                    id="search_location" 
                    placeholder="<?php esc_attr_e( 'Stad of plaats', 'wp-job-manager' ); ?>" 
                    value="<?php echo esc_attr( $location ); ?>" 
                />
            </div>

            <!-- Submit Button -->
            <?php if ( apply_filters( 'job_manager_job_filters_show_submit_button', true ) ) : ?>
                <div class="search_submit">
                    <input type="submit" value="<?php esc_attr_e( 'Search Jobs', 'wp-job-manager' ); ?>">
                </div>
            <?php endif; ?>
        </div>

   

        <!-- Extra Filters -->
        <div class="extra-filters">
            <!-- Sectors -->
            <div class="search_sectors">
                <select name="search_sectors[]" id="search_sectors" class="custom-multi-select" multiple="multiple">
                    <option value=""><?php esc_html_e( 'Select a sector', 'textdomain' ); ?></option>
                    <?php
                    $sectors = get_terms([
                        'taxonomy'   => 'job_sector',
                        'hide_empty' => false,
                    ]);

                    if ( ! empty( $sectors ) && ! is_wp_error( $sectors ) ) {
                        foreach ( $sectors as $sector ) {
                            echo '<option value="' . esc_attr( $sector->slug ) . '">' . esc_html( $sector->name ) . '</option>';
                        }
                    }
                    ?>
                </select>
            </div>

            <!-- Regios -->
            <div class="search_regios">
                <select name="search_regios[]" id="search_regios" class="custom-multi-select" multiple="multiple">
                    <option value=""><?php esc_html_e( "Select a regio", 'textdomain' ); ?></option>
                    <?php
                    $regios = get_terms([
                        'taxonomy'   => 'job_regio',
                        'hide_empty' => false,
                    ]);

                    if ( ! empty( $regios ) && ! is_wp_error( $regios ) ) {
                        foreach ( $regios as $regio ) {
                            echo '<option value="' . esc_attr( $regio->slug ) . '">' . esc_html( $regio->name ) . '</option>';
                        }
                    }
                    ?>
                </select>
            </div>

            <!-- Job Types -->
            <div class="search_categories">
                <select id="select-category" class="select2" multiple="multiple">
                    <?php foreach ( get_job_listing_types() as $type ) : ?>
                        <option value="<?php echo esc_attr( $type->term_taxonomy_id ); ?>">
                            <?php echo esc_html( $type->name ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <?php do_action( 'job_manager_job_filters_end', $atts ); ?>

     
   
</form>

<?php do_action( 'job_manager_job_filters_after', $atts ); ?>

<noscript>
    <?php esc_html_e( 'Your browser does not support JavaScript, or it is disabled. JavaScript must be enabled in order to view listings.', 'wp-job-manager' ); ?>
</noscript>

<script>
    jQuery(document).ready(function($) {
        // Initialize Select2 for Sectors
        $('#search_sectors').select2({
            placeholder: 'Sectoren',
            allowClear: true
        });

        // Initialize Select2 for Job Types
        $('#select-category').select2({
            placeholder: 'Type baan',
            allowClear: true
        });

        // Initialize Select2 for Regios
        $('#search_regios').select2({
            placeholder: 'Select a regio',
            allowClear: true
        });
    });
</script>
