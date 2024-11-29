<html>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" integrity="sha512-KfkfwYDsLkIlwQp6LFnl8zNdLGxu9YAA1QvwINks4PhcElQSvqcyVLLD9aMhXd13uQjoXtEKNosOWaZqXgel0g==" crossorigin="anonymous" referrerpolicy="no-referrer" />


</html>


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

        <select id="select-category" class="select2" multiple="multiple">
  <?php foreach ( get_job_listing_types() as $type ) : ?>
    <option value="<?php echo esc_attr( $type->term_taxonomy_id ); ?>">
      <?php echo esc_html( $type->name ); ?>
    </option>
  <?php endforeach; ?>
</select>

        <div class="search_location">
            <input type="text" name="search_location" id="search_location" placeholder="<?php esc_attr_e( 'Stad of plaats', 'wp-job-manager' ); ?>" value="<?php echo esc_attr( $location ); ?>" />
        </div>

        <?php do_action( 'job_manager_job_filters_end', $atts ); ?>
    </div>
</form>

<?php do_action( 'job_manager_job_filters_after', $atts ); ?>

<noscript><?php esc_html_e( 'Your browser does not support JavaScript, or it is disabled. JavaScript must be enabled in order to view listings.', 'wp-job-manager' ); ?></noscript>


<script>
jQuery(document).ready(function($) {
  $('#select-category').select2({
    placeholder: "Select Category",
    allowClear: true
  });

  // AJAX actie bij wijziging van de dropdown
  $('#select-category').on('change', function() {
    var selectedCategories = $(this).val(); // Haal geselecteerde categorieën op
    $.ajax({
      url: '/wp-admin/admin-ajax.php', // Standaard WordPress AJAX URL
      type: 'POST',
      data: {
        action: 'filter_jobs', // Custom AJAX actie
        categories: selectedCategories
      },
      success: function(response) {
        if (response.jobs.length > 0) {
          $('#job-results').html(response.html); // Update resultaten
        } else {
          $('#job-results').html('<p>No jobs found</p>'); // Toon fallbackbericht
        }
      },
      error: function() {
        $('#job-results').html('<p>Error loading jobs. Please try again.</p>');
      }
    });
  });
});

</script>