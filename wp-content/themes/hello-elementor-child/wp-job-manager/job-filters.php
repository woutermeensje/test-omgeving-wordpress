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

        <div>

    
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
        <p class="text-under-filter">Stel een job alert in!<strong>Of plaats een vacature in ons netwerk!</strong></p>
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

    /* -----------------------------------
   General Form Styling (Main Wrapper)
-------------------------------------- */
.job_filters {
    display: flex;
    flex-direction: column;
    gap: 20px; /* Space between rows */
    margin-bottom: 0px; /* Add your background image */
    padding: 35px 35px 0 35px; /* Add padding for top, left, and right, but bottom 0 */
    border-radius: 15px; /* Rounded corners */
    margin-top: 0px; 
    background: black; 
}

.showing_jobs {
    display: none !important; 
}

form.job_filters {
	background: white; 
    background: transparent; /* Remove background color */

}

/* -----------------------------------
   Flex Wrapper for All Input Fields
-------------------------------------- */
.form_flex_wrapper {
    display: flex;
    flex-wrap: wrap;
    gap: 15px; /* Space between individual elements */
    align-items: flex-start;
}


/* -----------------------------------
   Search Basic Design
-------------------------------------- */
.search-basic {
    display: flex; /* Enable flexbox layout */
    flex-direction: row; /* Stack elements horizontally */
    gap: 10px; /* Add 10px gap between elements */
    width: 100%; /* Make sure it spans the full width */
    border-radius: 5px; 
    padding: 25px; /* Add padding for a better look */
    box-shadow: 0px 10px 40px -5px rgba(0, 0, 0, 0.15);
    margin: auto; 
    border: 1px solid #0a6b8d; /* Light border */
    background: white; 
}

.search-basic > div {
    width: 100%; /* Each child div takes up 70% width */
}


.search-basic input[type="text"],
.search-basic input[type="submit"] {
    width: 100%; /* Inputs and buttons span full width */
    padding: 10px; /* Add padding for a better look */
    box-sizing: border-box; /* Ensure padding doesn't affect total width */
    font-size: 16px; /* Adjust font size for better readability */
}

.input#search_keywords {
    border: none !important; /* Removes the border */
    outline: none; /* Removes the outline when focused */
}

.search-basic input[type="submit"] {
    background-color: #0a6b8d; /* Add button background color */
    color: #ffffff; /* Button text color */
    border: none; /* Remove border */
    border-radius: 5px; /* Rounded corners */
    cursor: pointer; /* Pointer cursor on hover */
    transition: background-color 0.3s ease; /* Smooth hover effect */
    font-family: Balgin Bold; /* Use a clean font */
}

.search-basic input[type="submit"]:hover {
    background-color: #005bb5; /* Darker shade on hover */
}


.search_submit input[type="submit"] {
    background-color: #0a6b8d; /* Primary button color */
    color: #fff; /* Text color */
    font-size: 16px; /* Font size */
    font-weight: bold; /* Text weight */
    padding: 12px 24px; /* Padding for size */
    border: none; /* Remove border */
    border-radius: 8px; /* Rounded corners */
    cursor: pointer; /* Change cursor on hover */
    transition: background-color 0.3s ease, transform 0.2s ease; /* Smooth hover effects */
}

.search_submit input[type="submit"]:hover {
    background-color: #0a6b8d; /* Darker blue on hover */
    transform: translateY(-2px); /* Slight lift effect */
}

.search_submit input[type="submit"]:active {
    background-color: #0a6b8d; /* Even darker blue when clicked */
    transform: translateY(0); /* Reset lift on click */
}

.search_submit {
    text-align: center; /* Center-align the button in its container */
}


/* -----------------------------------
   Styling: Extra filters
-------------------------------------- */

.extra-filters {
    display: flex; /* Enable flexbox layout */
    flex-direction: row; /* Stack elements horizontally */
    gap: 10px; /* Add 10px gap between elements */
    width: 100%; /* Make sure it spans the full width */
    border-radius: 5px; 
    padding: 25px; 
}

.extra-filters > div {
    width: 100%; /* Each child div takes up 70% width */
}

.text-in-form {
    margin-top: 0px;
    display: flex;
    justify-content: center; /* Centers the content horizontally */
    align-items: center; /* Centers the content vertically */
    font-size: 13px; /* Adjust font size for better readability */
}







    @media only screen and (max-width: 768px) {
    .extra-filters {
        display: block; /* Stack filters vertically */
        box-shadow: 0px 10px 40px -5px rgba(0, 0, 0, 0.15);
        border-radius: 5px; /* Rounded corners */
        border: 1px solid #0a6b8d; /* Light border */
    }

    .extra-filters > div {
        margin-bottom: 15px; /* Space between stacked filters */
    }

    select.custom-multi-select {
        font-size: 16px; /* Slightly larger font for easier tapping */
    }

    .search-basic {
        display: block; 
        border-radius: 5px; /* Rounded corners */
        border: 1px solid #0a6b8d; /* Light border */
    }

    .search-basic  > div{ 
        padding: 15px;

    }
}

.text-under-filter {
    margin: auto; 
    text-align: center; /* Center the text */
}


</style>



