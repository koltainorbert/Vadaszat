<?php
/**
 * Template: Bejelentkezési form
 */
if ( ! defined( 'ABSPATH' ) ) exit;

wp_enqueue_style( 'va-frontend', VA_PLUGIN_URL . 'frontend/css/frontend.css', [], VA_VERSION );

if ( is_user_logged_in() ) {
    wp_safe_redirect( home_url() );
    exit;
}

$redirect = sanitize_url( wp_get_referer() ?: home_url() );
?>
<div class="va-wrap va-auth-wrap">
    <?php va_display_flash(); ?>

    <div class="va-auth-box">
        <h2 class="va-auth-box__title">Bejelentkezés</h2>

        <form method="post">
            <?php wp_nonce_field( 'va_login', 'va_login_nonce' ); ?>
            <input type="hidden" name="va_action"    value="login">
            <input type="hidden" name="redirect_to"  value="<?php echo esc_attr( $redirect ); ?>">

            <div class="va-form-group">
                <label>Felhasználónév / E-mail <span class="required">*</span></label>
                <input type="text" name="login_username" class="va-input" required autocomplete="username">
            </div>

            <div class="va-form-group">
                <label>Jelszó <span class="required">*</span></label>
                <input type="password" name="login_password" class="va-input" required autocomplete="current-password">
            </div>

            <div class="va-form-group" style="display:flex;justify-content:space-between;align-items:center;">
                <label class="va-check-label">
                    <input type="checkbox" name="login_remember" value="1">
                    Emlékezz rám
                </label>
                <a href="<?php echo esc_url( wp_lostpassword_url() ); ?>" style="font-size:13px;color:#ff0000;">Elfelejtett jelszó</a>
            </div>

            <button type="submit" class="va-btn va-btn--primary va-btn--block">Bejelentkezés</button>
        </form>

        <div class="va-auth-links">
            Még nincs fiókod? <a href="<?php echo esc_url( wp_registration_url() ); ?>">Regisztrálj ingyen</a>
        </div>
    </div>
</div>
