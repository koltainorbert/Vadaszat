<?php
/**
 * Template: Regisztrációs form
 */
if ( ! defined( 'ABSPATH' ) ) exit;

wp_enqueue_style(  'va-frontend', VA_PLUGIN_URL . 'frontend/css/frontend.css', [], VA_VERSION );

if ( is_user_logged_in() ) {
    $dashboard = get_page_by_path( 'va-fiok' );
    wp_safe_redirect( $dashboard ? get_permalink( $dashboard ) : home_url() );
    exit;
}
?>
<div class="va-wrap va-auth-wrap va-auth-wrap--register">
    <?php va_display_flash(); ?>

    <div class="va-auth-box">
        <h2 class="va-auth-box__title">Regisztráció</h2>

        <form method="post" class="va-register-form">
            <?php wp_nonce_field( 'va_register', 'va_register_nonce' ); ?>
            <input type="hidden" name="va_action" value="register">
            <input type="hidden" name="reg_account_type" id="va-account-type" value="private">

            <div class="va-register-grid">
                <div class="va-register-field va-register-field--full">
                    <div class="va-account-switch" role="group" aria-label="Fiók típus választó">
                        <span class="va-account-switch__label">Magánszemély</span>
                        <label class="va-account-switch__toggle" for="va-account-type-switch">
                            <input type="checkbox" id="va-account-type-switch" class="va-account-switch__input" aria-label="Céges fiók kapcsoló">
                            <span class="va-account-switch__slider" aria-hidden="true"></span>
                        </label>
                        <span class="va-account-switch__label">Cég</span>
                    </div>
                </div>

                <div class="va-form-group va-register-field">
                    <label>Keresztnév <span class="required">*</span></label>
                    <input type="text" name="reg_firstname" class="va-input" required data-typing="András|János|Péter">
                </div>
                <div class="va-form-group va-register-field">
                    <label>Vezetéknév</label>
                    <input type="text" name="reg_lastname" class="va-input" data-typing="Nagy|Kovács|Tóth">
                </div>

                <div class="va-form-group va-register-field">
                    <label>Felhasználónév <span class="required">*</span></label>
                    <input type="text" name="reg_username" class="va-input" required autocomplete="username" data-typing="vadasz1988|golyospuska|trofea_user">
                </div>

                <div class="va-form-group va-register-field">
                    <label>E-mail cím <span class="required">*</span></label>
                    <input type="email" name="reg_email" class="va-input" required autocomplete="email" data-typing="pelda@email.hu|info@cegem.hu">
                </div>

                <div class="va-form-group va-register-field">
                    <label>Telefonszám</label>
                    <input type="tel" name="reg_phone" class="va-input" placeholder="+36 30 000 0000" data-typing="+36 30 123 4567|+36 70 765 4321">
                </div>

                <div class="va-register-company va-register-field va-register-field--full" aria-hidden="true">
                    <div class="va-register-company__grid">
                        <div class="va-form-group va-register-field">
                            <label>Cégnév <span class="required">*</span></label>
                            <input type="text" name="reg_company_name" class="va-input" data-company-field="1" disabled data-typing="Minta Vadász Kft.|Erdővad Bt.">
                        </div>
                        <div class="va-form-group va-register-field">
                            <label>Adószám <span class="required">*</span></label>
                            <input type="text" name="reg_company_tax" class="va-input" data-company-field="1" disabled data-typing="12345678-2-42|98765432-1-13">
                        </div>
                        <div class="va-form-group va-register-field">
                            <label>Székhely <span class="required">*</span></label>
                            <input type="text" name="reg_company_seat" class="va-input" data-company-field="1" disabled data-typing="1123 Budapest, Minta utca 10.|6720 Szeged, Fő tér 3.">
                        </div>
                        <div class="va-form-group va-register-field">
                            <label>Személynév <span class="required">*</span></label>
                            <input type="text" name="reg_contact_name" class="va-input" data-company-field="1" disabled data-typing="Kiss Gábor|Szabó Anna">
                        </div>
                    </div>
                </div>

                <div class="va-form-group va-register-field">
                    <label>Jelszó <span class="required">*</span></label>
                    <input type="password" name="reg_password" class="va-input" required minlength="8" autocomplete="new-password">
                    <p class="va-register-help">Min. 8 karakter</p>
                </div>

                <div class="va-form-group va-register-field">
                    <label>Jelszó ismét <span class="required">*</span></label>
                    <input type="password" name="reg_password2" class="va-input" required minlength="8" autocomplete="new-password">
                </div>

                <div class="va-form-group va-register-field va-register-field--full">
                    <label class="va-check-label">
                        <input type="checkbox" name="terms_accept" required>
                        Elfogadom az <a href="<?php echo esc_url( home_url('/aszf') ); ?>" style="color:#ff0000;">általános szerződési feltételeket</a>
                    </label>
                </div>

                <div class="va-register-field va-register-field--full">
                    <button type="submit" class="va-btn va-btn--primary va-btn--block">Regisztráció</button>
                </div>
            </div>
        </form>

        <div class="va-auth-links">
            Már van fiókod? <a href="<?php echo esc_url( wp_login_url() ); ?>">Bejelentkezés</a>
        </div>
    </div>
</div>
