<?php
/**
 * Felhasználói rendszer: regisztráció, bejelentkezés, fiók, frontend form-ok
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VA_User_System {

    public static function init() {
        add_action( 'init',                [ __CLASS__, 'handle_forms' ] );
        add_action( 'wp_enqueue_scripts',  [ __CLASS__, 'enqueue' ] );
        add_filter( 'login_url',           [ __CLASS__, 'custom_login_url' ], 10, 3 );
        add_filter( 'register_url',        [ __CLASS__, 'custom_register_url' ] );
        add_filter( 'logout_redirect',     [ __CLASS__, 'logout_redirect' ], 10, 1 );
    }

    /* ── URL átirányítások ─────────────────────────────── */
    public static function custom_login_url( $url, $redirect, $force_reauth ) {
        $page = get_page_by_path( 'va-bejelentkezes' );
        return $page ? get_permalink( $page ) : $url;
    }

    public static function custom_register_url( $url ) {
        $page = get_page_by_path( 'va-regisztracio' );
        return $page ? get_permalink( $page ) : $url;
    }

    public static function logout_redirect( $url ) {
        return home_url();
    }

    /* ── Enqueue ───────────────────────────────────────── */
    public static function enqueue() {
        wp_enqueue_style(  'va-user', VA_PLUGIN_URL . 'frontend/css/user.css', [], VA_VERSION );
        wp_enqueue_script( 'va-user', VA_PLUGIN_URL . 'frontend/js/user.js',  [ 'jquery' ], VA_VERSION, true );
        wp_localize_script( 'va-user', 'VA_User', [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'va_user_nonce' ),
        ]);
    }

    /* ── Form kezelés ──────────────────────────────────── */
    public static function handle_forms() {
        if ( isset( $_POST['va_action'] ) ) {
            $action = sanitize_key( $_POST['va_action'] );
            if ( $action === 'register' ) self::process_register();
            if ( $action === 'login'    ) self::process_login();
            if ( $action === 'logout'   ) self::process_logout();
            if ( $action === 'profile'  ) self::process_profile();
        }
    }

    /* ── Regisztráció ──────────────────────────────────── */
    private static function process_register() {
        if ( ! isset( $_POST['va_register_nonce'] ) ||
             ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['va_register_nonce'] ) ), 'va_register' ) ) {
            va_set_flash( 'error', 'Érvénytelen kérés.' );
            return;
        }

        $username  = sanitize_user( wp_unslash( $_POST['reg_username'] ?? '' ) );
        $email     = sanitize_email( wp_unslash( $_POST['reg_email']    ?? '' ) );
        $pass      = wp_unslash( $_POST['reg_password'] ?? '' );
        $pass2     = wp_unslash( $_POST['reg_password2'] ?? '' );
        $firstname = sanitize_text_field( wp_unslash( $_POST['reg_firstname'] ?? '' ) );
        $lastname  = sanitize_text_field( wp_unslash( $_POST['reg_lastname']  ?? '' ) );
        $phone     = sanitize_text_field( wp_unslash( $_POST['reg_phone']     ?? '' ) );
        $account_type = sanitize_key( wp_unslash( $_POST['reg_account_type'] ?? 'private' ) );
        $company_name = sanitize_text_field( wp_unslash( $_POST['reg_company_name'] ?? '' ) );
        $company_tax  = sanitize_text_field( wp_unslash( $_POST['reg_company_tax'] ?? '' ) );
        $company_seat = sanitize_text_field( wp_unslash( $_POST['reg_company_seat'] ?? '' ) );
        $contact_name = sanitize_text_field( wp_unslash( $_POST['reg_contact_name'] ?? '' ) );

        if ( ! in_array( $account_type, [ 'private', 'company' ], true ) ) {
            $account_type = 'private';
        }

        if ( empty( $username ) || empty( $email ) || empty( $pass ) ) {
            va_set_flash( 'error', 'Kérjük töltse ki a kötelező mezőket.' );
            return;
        }

        if ( empty( $_POST['terms_accept'] ) ) {
            va_set_flash( 'error', 'Az ÁSZF elfogadása kötelező.' );
            return;
        }

        if ( $account_type === 'company' ) {
            if ( $company_name === '' || $company_tax === '' || $company_seat === '' || $contact_name === '' ) {
                va_set_flash( 'error', 'Céges regisztrációnál minden cégadat mező kitöltése kötelező.' );
                return;
            }

            if ( strlen( preg_replace( '/[^0-9]/', '', $company_tax ) ) < 8 ) {
                va_set_flash( 'error', 'Kérjük érvényes adószámot adjon meg.' );
                return;
            }
        }

        if ( $pass !== $pass2 ) {
            va_set_flash( 'error', 'A két jelszó nem egyezik.' );
            return;
        }

        if ( strlen( $pass ) < 8 ) {
            va_set_flash( 'error', 'A jelszónak legalább 8 karakter hosszúnak kell lennie.' );
            return;
        }

        if ( username_exists( $username ) ) {
            va_set_flash( 'error', 'Ez a felhasználónév már foglalt.' );
            return;
        }

        if ( email_exists( $email ) ) {
            va_set_flash( 'error', 'Ez az e-mail cím már regisztrált.' );
            return;
        }

        $user_id = wp_create_user( $username, $pass, $email );
        if ( is_wp_error( $user_id ) ) {
            va_set_flash( 'error', $user_id->get_error_message() );
            return;
        }

        wp_update_user([
            'ID'         => $user_id,
            'first_name' => $firstname,
            'last_name'  => $lastname,
            'role'       => 'subscriber',
        ]);
        update_user_meta( $user_id, 'va_phone', $phone );
        update_user_meta( $user_id, 'va_account_type', $account_type );

        if ( $account_type === 'company' ) {
            update_user_meta( $user_id, 'va_company_name', $company_name );
            update_user_meta( $user_id, 'va_company_tax', $company_tax );
            update_user_meta( $user_id, 'va_company_seat', $company_seat );
            update_user_meta( $user_id, 'va_contact_name', $contact_name );
        } else {
            delete_user_meta( $user_id, 'va_company_name' );
            delete_user_meta( $user_id, 'va_company_tax' );
            delete_user_meta( $user_id, 'va_company_seat' );
            delete_user_meta( $user_id, 'va_contact_name' );
        }

        // Automatikus bejelentkezés regisztráció után
        wp_set_auth_cookie( $user_id );

        va_set_flash( 'success', 'Sikeres regisztráció! Üdvözöljük!' );

        $dashboard = get_page_by_path( 'va-fiok' );
        wp_safe_redirect( $dashboard ? get_permalink( $dashboard ) : home_url() );
        exit;
    }

    /* ── Bejelentkezés ─────────────────────────────────── */
    private static function process_login() {
        if ( ! isset( $_POST['va_login_nonce'] ) ||
             ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['va_login_nonce'] ) ), 'va_login' ) ) {
            va_set_flash( 'error', 'Érvénytelen kérés.' );
            return;
        }

        $creds = [
            'user_login'    => sanitize_user( wp_unslash( $_POST['login_username'] ?? '' ) ),
            'user_password' => wp_unslash( $_POST['login_password'] ?? '' ),
            'remember'      => ! empty( $_POST['login_remember'] ),
        ];

        $user = wp_signon( $creds, is_ssl() );
        if ( is_wp_error( $user ) ) {
            va_set_flash( 'error', 'Hibás felhasználónév vagy jelszó.' );
            return;
        }

        wp_safe_redirect( $_POST['redirect_to'] ?? home_url() );
        exit;
    }

    private static function process_logout() {
        wp_logout();
        wp_safe_redirect( home_url() );
        exit;
    }

    /* ── Profil frissítés ──────────────────────────────── */
    private static function process_profile() {
        if ( ! is_user_logged_in() ) return;
        if ( ! isset( $_POST['va_profile_nonce'] ) ||
             ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['va_profile_nonce'] ) ), 'va_profile' ) ) {
            va_set_flash( 'error', 'Érvénytelen kérés.' );
            return;
        }

        $user_id   = get_current_user_id();
        $firstname = sanitize_text_field( wp_unslash( $_POST['profile_firstname'] ?? '' ) );
        $lastname  = sanitize_text_field( wp_unslash( $_POST['profile_lastname']  ?? '' ) );
        $phone     = sanitize_text_field( wp_unslash( $_POST['profile_phone']     ?? '' ) );
        $bio       = sanitize_textarea_field( wp_unslash( $_POST['profile_bio']   ?? '' ) );

        wp_update_user([
            'ID'          => $user_id,
            'first_name'  => $firstname,
            'last_name'   => $lastname,
            'description' => $bio,
        ]);
        update_user_meta( $user_id, 'va_phone', $phone );

        // Jelszócsere ha megadva
        $new_pass  = wp_unslash( $_POST['profile_newpass']  ?? '' );
        $new_pass2 = wp_unslash( $_POST['profile_newpass2'] ?? '' );
        if ( ! empty( $new_pass ) ) {
            if ( $new_pass !== $new_pass2 ) {
                va_set_flash( 'error', 'A két jelszó nem egyezik.' );
                return;
            }
            if ( strlen( $new_pass ) < 8 ) {
                va_set_flash( 'error', 'A jelszónak legalább 8 karakter.' );
                return;
            }
            wp_set_password( $new_pass, $user_id );
            wp_set_auth_cookie( $user_id );
        }

        va_set_flash( 'success', 'Profil sikeresen frissítve.' );
        wp_safe_redirect( get_permalink( get_page_by_path( 'va-fiok' ) ) );
        exit;
    }
}
