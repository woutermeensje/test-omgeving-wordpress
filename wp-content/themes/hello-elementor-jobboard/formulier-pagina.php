<?php
/**
 * Template Name: Formulier-pagina
 */

get_header();
?>

<div class="container">
    <h1><?php the_title(); ?></h1>
    
    <form method="post" action="">
        <label for="naam">Naam:</label>
        <input type="text" name="naam" id="naam" required>

        <label for="email">E-mail:</label>
        <input type="email" name="email" id="email" required>

        <label for="bericht">Bericht:</label>
        <textarea name="bericht" id="bericht" required></textarea>

        <input type="submit" name="verzenden" value="Verzenden">
    </form>

    <?php
    // Verwerk formulier
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verzenden'])) {
        $naam = sanitize_text_field($_POST['naam']);
        $email = sanitize_email($_POST['email']);
        $bericht = sanitize_textarea_field($_POST['bericht']);

        echo "<p>Bedankt voor je bericht, $naam!</p>";
    }
    ?>
</div>

<?php
get_footer();
?>
