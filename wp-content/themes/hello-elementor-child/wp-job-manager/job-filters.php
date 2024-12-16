<body>

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
                    <input type="submit" value="<?php esc_attr_e( 'Doorzoek Vacatures', 'wp-job-manager' ); ?>">
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


</body>

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

    });
</script>


<style>


<style>
    /* Container styling for all dropdowns */
    .search_sectors, .search_regios, .search_job_names, .search_salary_ranges, .search_categories {
        width: 100%; /* Make it responsive */
    }

    .select2-container--default .select2-search--inline .select2-search__field::placeholder {
        color: #8A8A8A; /* Placeholder text color */
        font-size: 16px; /* Font size */
        font-weight: 300; /* Bold font weight */
        font-family: 'Poppins', sans-serif; /* Use a clean font */
    }

    /* Select2 dropdown styling for all dropdowns */
    .search_sectors .select2-container,
    .search_regios .select2-container,
    .search_job_names .select2-container,
    .search_salary_ranges .select2-container,
    .search_categories .select2-container {
        width: 100% !important; /* Ensure it spans the full width of the container */
        font-family: Poppins; /* Use a clean font */
        font-size: 15px; /* Standard font size */
        color: black;
    }

    /* Input box styling */
    .search_sectors .select2-container--default .select2-selection--multiple,
    .search_regios .select2-container--default .select2-selection--multiple,
    .search_job_names .select2-container--default .select2-selection--multiple,
    .search_salary_ranges .select2-container--default .select2-selection--multiple,
    .search_categories .select2-container--default .select2-selection--multiple {
        background-color: white; /* Light gray background */
        border: 1px solid #0a6b8d; /* Light border */
        border-radius: 5px; /* Rounded corners */
        padding: 5px; /* Padding inside the input */
        min-height: 40px; /* Ensure consistent height */
        transition: border-color 0.3s ease; /* Smooth transition */
        position: relative; /* Set position to relative for icon adjustments */
        padding-right: 30px; /* Space for dropdown icon */
    }

    /* Input hover effect */
    .search_sectors .select2-container--default .select2-selection--multiple:hover,
    .search_regios .select2-container--default .select2-selection--multiple:hover,
    .search_job_names .select2-container--default .select2-selection--multiple:hover,
    .search_salary_ranges .select2-container--default .select2-selection--multiple:hover,
    .search_categories .select2-container--default .select2-selection--multiple:hover {
        border-color: #0a6b8d; /* Change border color on hover */
        border-width: 2px; /* Make border 2px on hover */
    }

    /* Selected items styling */
    .search_sectors .select2-container--default .select2-selection--multiple .select2-selection__choice,
    .search_regios .select2-container--default .select2-selection--multiple .select2-selection__choice,
    .search_job_names .select2-container--default .select2-selection--multiple .select2-selection__choice,
    .search_salary_ranges .select2-container--default .select2-selection--multiple .select2-selection__choice,
    .search_categories .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #0073e6; /* Blue background for selected items */
        color: #fff; /* White text */
        border: none; /* Remove border */
        border-radius: 3px; /* Slightly rounded edges */
        padding: 3px 10px; /* Add spacing inside tags */
        margin: 2px; /* Space between tags */
        font-size: 12px; /* Smaller font size for tags */
    }

    /* Remove button on selected items */
    .search_sectors .select2-container--default .select2-selection--multiple .select2-selection__choice__remove,
    .search_regios .select2-container--default .select2-selection--multiple .select2-selection__choice__remove,
    .search_job_names .select2-container--default .select2-selection--multiple .select2-selection__choice__remove,
    .search_salary_ranges .select2-container--default .select2-selection--multiple .select2-selection__choice__remove,
    .search_categories .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: #fff; /* White remove button */
        margin-right: 5px; /* Space around remove button */
        font-size: 12px; /* Smaller size for remove icon */
        cursor: pointer; /* Pointer cursor on hover */
    }

    /* Placeholder styling */
    .search_sectors .select2-container--default .select2-selection--multiple .select2-search--inline .select2-search__field,
    .search_regios .select2-container--default .select2-selection--multiple .select2-search--inline .select2-search__field,
    .search_job_names .select2-container--default .select2-selection--multiple .select2-search--inline .select2-search__field,
    .search_salary_ranges .select2-container--default .select2-selection--multiple .select2-search--inline .select2-search__field,
    .search_categories .select2-container--default .select2-selection--multiple .select2-search--inline .select2-search__field {
        color: black; /* Placeholder text color */
    }

    /* Dropdown styling */
    .search_sectors .select2-container--default .select2-results__option,
    .search_regios .select2-container--default .select2-results__option,
    .search_job_names .select2-container--default .select2-results__option,
    .search_salary_ranges .select2-container--default .select2-results__option,
    .search_categories .select2-container--default .select2-results__option {
        padding: 8px 10px; /* Add padding inside dropdown options */
        font-size: 14px; /* Standard font size */
    }

    /* Highlighted option in dropdown */
    .search_sectors .select2-container--default .select2-results__option--highlighted,
    .search_regios .select2-container--default .select2-results__option--highlighted,
    .search_job_names .select2-container--default .select2-results__option--highlighted,
    .search_salary_ranges .select2-container--default .select2-results__option--highlighted,
    .search_categories .select2-container--default .select2-results__option--highlighted {
        background-color: #0a6b8d; /* Blue highlight */
        color: #fff; /* White text */
    }

    /* Add dropdown icon */
    .search_sectors .select2-container--default .select2-selection--multiple:after,
    .search_regios .select2-container--default .select2-selection--multiple:after,
    .search_job_names .select2-container--default .select2-selection--multiple:after,
    .search_salary_ranges .select2-container--default .select2-selection--multiple:after,
    .search_categories .select2-container--default .select2-selection--multiple:after {
        content: '\25BC'; /* Unicode character for a downward arrow */
        font-size: 12px; /* Size of the arrow */
        color: black; /* Arrow color */
        position: absolute;
        right: 10px; /* Position it 10px from the right edge */
        top: 50%; /* Vertically center */
        transform: translateY(-50%); /* Align vertically */
        pointer-events: none; /* Prevent the icon from interfering with user interactions */
    }




    @media only screen and (max-width: 768px) {
    .extra-filters {
        display: block; /* Stack filters vertically */
    }

    .extra-filters > div {
        margin-bottom: 15px; /* Space between stacked filters */
    }

    select.custom-multi-select {
        font-size: 16px; /* Slightly larger font for easier tapping */
    }
}

</style>
