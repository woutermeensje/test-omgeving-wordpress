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


             <!-- Job Names -->
            <div class="search_job_names">
                <select name="search_job_names[]" id="search_job_names" class="custom-multi-select" multiple="multiple">
                    <option value=""><?php esc_html_e( "Select a job name", 'textdomain' ); ?></option>
                    <?php
                    $job_names = get_terms([
                        'taxonomy'   => 'job_name',
                        'hide_empty' => false,
                    ]);

                    if ( ! empty( $job_names ) && ! is_wp_error( $job_names ) ) {
                        foreach ( $job_names as $job_name ) {
                            echo '<option value="' . esc_attr( $job_name->slug ) . '">' . esc_html( $job_name->name ) . '</option>';
                        }
                    }
                    ?>
                </select>
            </div>

            <!-- Salary Ranges -->
            <div class="search_salary_ranges">
                <select name="search_salary_ranges[]" id="search_salary_ranges" class="custom-multi-select" multiple="multiple">
                    <option value=""><?php esc_html_e( "Select a salary range", 'textdomain' ); ?></option>

                    <!-- Numeric Salary Ranges -->
                    <optgroup label="Salarisschalen">
                        <?php
                        $numeric_ranges = [
                            '€2.000 - €3.000',
                            '€3.000 - €4.000',
                            '€4.000 - €5.000',
                            '€5.000 - €6.000',
                            '€6.000 - €8.000',
                            '€8.000 - €12.000',
                            '€5.000+'
                        ];

                        foreach ( $numeric_ranges as $range ) {
                            $term = get_term_by( 'name', $range, 'salary_range' );
                            if ( $term ) {
                                echo '<option value="' . esc_attr( $term->slug ) . '">' . esc_html( $term->name ) . '</option>';
                            }
                        }
                        ?>
                    </optgroup>

                    <!-- Other Options -->
                    <optgroup label="Overige opties">
                        <?php
                        $other_ranges = ['Op basis van CAO inschaling', 'Vrijwilligersvergoeding', 'Stage vergoeding'];
                        foreach ( $other_ranges as $range ) {
                            $term = get_term_by( 'name', $range, 'salary_range' );
                            if ( $term ) {
                                echo '<option value="' . esc_attr( $term->slug ) . '">' . esc_html( $term->name ) . '</option>';
                            }
                        }
                        ?>
                    </optgroup>
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

        // Initialize Select2 for search job names
        $('#search_job_names').select2({
            placeholder: 'Beroep',
            allowClear: true
        });

        // Initialize Select2 for salarys ranges
        $('#search_salary_ranges').select2({
            placeholder: 'Select a salary range',
            allowClear: true
        });
    });
</script>
