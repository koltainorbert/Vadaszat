<?php
/**
 * Felhasználói rendszer: regisztráció, bejelentkezés, fiók, frontend form-ok
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VA_User_System {

    private static function ensure_roles(): void {
        if ( ! get_role( 'va_maganszemely' ) ) {
            add_role( 'va_maganszemely', 'Magánszemély', [ 'read' => true ] );
        }
        if ( ! get_role( 'va_ceg' ) ) {
            add_role( 'va_ceg', 'Cég', [ 'read' => true ] );
        }
    }

    private static function is_login_enabled(): bool {
        return get_option( 'va_enable_login', '1' ) === '1';
    }

    private static function is_register_enabled(): bool {
        return get_option( 'va_enable_register', '1' ) === '1';
    }

    private static function get_login_page_url( array $args = [] ): string {
        $page = get_page_by_path( 'va-bejelentkezes' );
        $url  = $page ? get_permalink( $page ) : home_url();
        if ( ! empty( $args ) ) {
            $url = add_query_arg( $args, $url );
        }
        return $url;
    }

    public static function init() {
        self::ensure_roles();

        add_action( 'init',                [ __CLASS__, 'handle_forms' ] );
        add_action( 'wp_enqueue_scripts',  [ __CLASS__, 'enqueue' ] );
        add_action( 'admin_init',          [ __CLASS__, 'restrict_wp_admin_for_non_admins' ] );
        add_filter( 'login_url',           [ __CLASS__, 'custom_login_url' ], 10, 3 );
        add_filter( 'register_url',        [ __CLASS__, 'custom_register_url' ] );
        add_filter( 'lostpassword_url',    [ __CLASS__, 'custom_lostpassword_url' ], 10, 2 );
        add_filter( 'show_admin_bar',      [ __CLASS__, 'filter_admin_bar_visibility' ] );
        add_filter( 'logout_redirect',     [ __CLASS__, 'logout_redirect' ], 10, 1 );
        add_action( 'login_form_lostpassword', [ __CLASS__, 'redirect_wp_lostpassword' ] );
        add_action( 'login_form_retrievepassword', [ __CLASS__, 'redirect_wp_lostpassword' ] );
        add_action( 'login_form_rp',       [ __CLASS__, 'redirect_wp_resetpass' ] );
        add_action( 'login_form_resetpass',[ __CLASS__, 'redirect_wp_resetpass' ] );
    }

    /* ── URL átirányítások ─────────────────────────────── */
    public static function custom_login_url( $url, $redirect, $force_reauth ) {
        if ( ! self::is_login_enabled() ) {
            return home_url();
        }
        $page = get_page_by_path( 'va-bejelentkezes' );
        return $page ? get_permalink( $page ) : $url;
    }

    public static function custom_register_url( $url ) {
        if ( ! self::is_register_enabled() ) {
            return home_url();
        }
        $page = get_page_by_path( 'va-regisztracio' );
        return $page ? get_permalink( $page ) : $url;
    }

    public static function custom_lostpassword_url( $url, $redirect ) {
        $args = [ 'action' => 'lostpassword' ];
        if ( ! empty( $redirect ) ) {
            $args['redirect_to'] = $redirect;
        }
        return self::get_login_page_url( $args );
    }

    public static function redirect_wp_lostpassword() {
        wp_safe_redirect( self::get_login_page_url( [ 'action' => 'lostpassword' ] ) );
        exit;
    }

    public static function redirect_wp_resetpass() {
        $key   = isset( $_REQUEST['key'] ) ? sanitize_text_field( wp_unslash( (string) $_REQUEST['key'] ) ) : '';
        $login = isset( $_REQUEST['login'] ) ? sanitize_user( wp_unslash( (string) $_REQUEST['login'] ) ) : '';

        $args = [ 'action' => 'resetpass' ];
        if ( $key !== '' ) {
            $args['key'] = $key;
        }
        if ( $login !== '' ) {
            $args['login'] = $login;
        }

        wp_safe_redirect( self::get_login_page_url( $args ) );
        exit;
    }

    public static function logout_redirect( $url ) {
        return home_url();
    }

    public static function filter_admin_bar_visibility( $show ) {
        if ( current_user_can( 'manage_options' ) ) {
            return $show;
        }
        return false;
    }

    public static function restrict_wp_admin_for_non_admins(): void {
        if ( ! is_user_logged_in() ) {
            return;
        }
        if ( current_user_can( 'manage_options' ) ) {
            return;
        }
        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
            return;
        }

        wp_safe_redirect( home_url() );
        exit;
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
            if ( $action === 'lostpassword' ) self::process_lostpassword();
            if ( $action === 'resetpass'    ) self::process_resetpass();
            if ( $action === 'logout'   ) self::process_logout();
            if ( $action === 'profile'  ) self::process_profile();
            if ( $action === 'profile_label' ) self::process_profile_label();
        }
    }

    private static function process_lostpassword() {
        if ( ! isset( $_POST['va_lostpass_nonce'] ) ||
             ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['va_lostpass_nonce'] ) ), 'va_lostpass' ) ) {
            va_set_flash( 'error', 'Érvénytelen kérés.' );
            return;
        }

        $user_login = sanitize_text_field( wp_unslash( $_POST['lost_user_login'] ?? '' ) );
        if ( $user_login === '' ) {
            va_set_flash( 'error', 'Add meg a felhasználónevedet vagy e-mail címedet.' );
            return;
        }

        $res = retrieve_password( $user_login );
        if ( is_wp_error( $res ) ) {
            $msg = $res->get_error_message();
            va_set_flash( 'error', $msg !== '' ? $msg : 'Nem sikerült jelszó-visszaállító e-mailt küldeni.' );
            return;
        }

        va_set_flash( 'success', 'Küldtünk egy e-mailt a jelszó visszaállításához.' );
        wp_safe_redirect( self::get_login_page_url( [ 'action' => 'lostpassword' ] ) );
        exit;
    }

    private static function process_resetpass() {
        if ( ! isset( $_POST['va_resetpass_nonce'] ) ||
             ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['va_resetpass_nonce'] ) ), 'va_resetpass' ) ) {
            va_set_flash( 'error', 'Érvénytelen kérés.' );
            return;
        }

        $key   = sanitize_text_field( wp_unslash( $_POST['rp_key'] ?? '' ) );
        $login = sanitize_user( wp_unslash( $_POST['rp_login'] ?? '' ) );
        $pass1 = (string) wp_unslash( $_POST['rp_pass1'] ?? '' );
        $pass2 = (string) wp_unslash( $_POST['rp_pass2'] ?? '' );

        if ( $key === '' || $login === '' ) {
            va_set_flash( 'error', 'Hiányzó visszaállítási adatok.' );
            return;
        }

        $user = check_password_reset_key( $key, $login );
        if ( is_wp_error( $user ) ) {
            va_set_flash( 'error', 'A jelszó-visszaállító link lejárt vagy érvénytelen.' );
            return;
        }

        if ( $pass1 === '' || $pass2 === '' ) {
            va_set_flash( 'error', 'Add meg az új jelszót mindkét mezőben.' );
            return;
        }

        if ( $pass1 !== $pass2 ) {
            va_set_flash( 'error', 'A két új jelszó nem egyezik.' );
            return;
        }

        if ( strlen( $pass1 ) < 8 ) {
            va_set_flash( 'error', 'Az új jelszó legalább 8 karakter legyen.' );
            return;
        }

        reset_password( $user, $pass1 );
        va_set_flash( 'success', 'Jelszó sikeresen módosítva. Most már bejelentkezhetsz.' );
        wp_safe_redirect( self::get_login_page_url() );
        exit;
    }

    /* ── Regisztráció ──────────────────────────────────── */
    private static function process_register() {
        if ( ! self::is_register_enabled() ) {
            va_set_flash( 'error', 'A regisztráció jelenleg ki van kapcsolva.' );
            return;
        }

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
            if ( $company_name === '' || $company_tax === '' || $company_seat === '' ) {
                va_set_flash( 'error', 'Céges regisztrációnál a cégnév, adószám és székhely kitöltése kötelező.' );
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

        $assigned_role = ( $account_type === 'company' ) ? 'va_ceg' : 'va_maganszemely';
        if ( ! get_role( $assigned_role ) ) {
            $assigned_role = 'subscriber';
        }

        wp_update_user([
            'ID'         => $user_id,
            'first_name' => $firstname,
            'last_name'  => $lastname,
            'role'       => $assigned_role,
        ]);
        update_user_meta( $user_id, 'va_phone', $phone );
        update_user_meta( $user_id, 'va_account_type', $account_type );

        if ( $account_type === 'company' ) {
            update_user_meta( $user_id, 'va_company_name', $company_name );
            update_user_meta( $user_id, 'va_company_tax', $company_tax );
            update_user_meta( $user_id, 'va_company_seat', $company_seat );
        } else {
            delete_user_meta( $user_id, 'va_company_name' );
            delete_user_meta( $user_id, 'va_company_tax' );
            delete_user_meta( $user_id, 'va_company_seat' );
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
        if ( ! self::is_login_enabled() ) {
            va_set_flash( 'error', 'A bejelentkezés jelenleg ki van kapcsolva.' );
            return;
        }

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

        // Platinum: egyedi rang címke
        $user_plan = class_exists( 'VA_User_Roles' ) ? VA_User_Roles::get_user_plan( $user_id ) : 'basic';
        if ( $user_plan === 'platinum' ) {
            $seller_label = sanitize_text_field( wp_unslash( $_POST['profile_seller_label'] ?? '' ) );
            update_user_meta( $user_id, 'va_seller_label', $seller_label );
        }

        wp_update_user([
            'ID'          => $user_id,
            'first_name'  => $firstname,
            'last_name'   => $lastname,
            'description' => $bio,
        ]);
        update_user_meta( $user_id, 'va_phone', $phone );

        // Profilkép törlés
        if ( ! empty( $_POST['profile_avatar_remove'] ) ) {
            delete_user_meta( $user_id, 'va_profile_avatar_id' );
        }

        // Profilkép feltöltés
        if ( ! empty( $_FILES['profile_avatar']['name'] ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/image.php';

            $uploaded = wp_handle_upload( $_FILES['profile_avatar'], [
                'test_form' => false,
                'mimes'     => [
                    'jpg|jpeg' => 'image/jpeg',
                    'png'      => 'image/png',
                    'webp'     => 'image/webp',
                ],
            ] );

            if ( ! empty( $uploaded['error'] ) ) {
                va_set_flash( 'error', 'A profilkép feltöltése sikertelen: ' . sanitize_text_field( $uploaded['error'] ) );
                wp_safe_redirect( get_permalink( get_page_by_path( 'va-fiok' ) ) );
                exit;
            }

            $file_path = $uploaded['file'] ?? '';
            $file_url  = $uploaded['url'] ?? '';
            if ( $file_path && $file_url ) {
                $filetype = wp_check_filetype( wp_basename( $file_path ), null );
                $attach_id = wp_insert_attachment( [
                    'guid'           => $file_url,
                    'post_mime_type' => $filetype['type'] ?? 'image/jpeg',
                    'post_title'     => sanitize_file_name( pathinfo( $file_path, PATHINFO_FILENAME ) ),
                    'post_content'   => '',
                    'post_status'    => 'inherit',
                    'post_author'    => $user_id,
                ], $file_path );

                if ( ! is_wp_error( $attach_id ) && $attach_id > 0 ) {
                    $attach_data = wp_generate_attachment_metadata( $attach_id, $file_path );
                    wp_update_attachment_metadata( $attach_id, $attach_data );
                    update_user_meta( $user_id, 'va_profile_avatar_id', (int) $attach_id );
                }
            }
        }

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

    /* ── Platinum: gyors címke frissítés ───────────────── */
    private static function process_profile_label() {
        if ( ! is_user_logged_in() ) return;
        if ( ! isset( $_POST['va_profile_label_nonce'] ) ||
             ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['va_profile_label_nonce'] ) ), 'va_profile_label' ) ) {
            va_set_flash( 'error', 'Érvénytelen kérés.' );
            return;
        }

        $user_id   = get_current_user_id();
        $user_plan = class_exists( 'VA_User_Roles' ) ? VA_User_Roles::get_user_plan( $user_id ) : 'basic';
        if ( $user_plan !== 'platinum' ) {
            va_set_flash( 'error', 'Ez a funkció csak Platinum csomagban érhető el.' );
            wp_safe_redirect( get_permalink( get_page_by_path( 'va-fiok' ) ) );
            exit;
        }

        $seller_label = sanitize_text_field( wp_unslash( $_POST['profile_seller_label'] ?? '' ) );
        update_user_meta( $user_id, 'va_seller_label', $seller_label );

        va_set_flash( 'success', 'Rang címke frissítve.' );
        wp_safe_redirect( get_permalink( get_page_by_path( 'va-fiok' ) ) );
        exit;
    }
}
