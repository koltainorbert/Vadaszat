<?php
/**
 * Admin főosztály: menük, assets, dark SaaS shell (sidebar + topbar)
 */
if ( ! defined( "ABSPATH" ) ) exit;

class VA_Admin {

    public static function init() {
        add_action( "admin_menu",            [ __CLASS__, "register_menus" ] );
        add_action( "admin_enqueue_scripts", [ __CLASS__, "enqueue" ] );
        add_filter( "admin_body_class",      [ __CLASS__, "body_class" ] );
        add_action( "in_admin_header",       [ __CLASS__, "render_shell" ] );
    }

    /* ── Body class ─────────────────────────────────────────── */
    public static function body_class( string $classes ): string {
        if ( self::is_va_page() ) {
            $classes .= " va-admin-page";
        }
        return $classes;
    }

    /* ── Oldal detekció ─────────────────────────────────────── */
    private static function is_va_page(): bool {
        $screen = function_exists( "get_current_screen" ) ? get_current_screen() : null;
        if ( ! $screen ) return false;
        $id = $screen->id ?? "";
        return (
            strpos( $id, "vadaszapro" ) !== false ||
            strpos( $id, "va-form-builder" ) !== false ||
            strpos( $id, "va_listing" ) !== false ||
            strpos( $id, "va_auction" ) !== false
        );
    }

    /* ── Sidebar + Topbar HTML ──────────────────────────────── */
    public static function render_shell(): void {
        if ( ! self::is_va_page() ) return;

        $current_user = wp_get_current_user();
        $avatar       = get_avatar( $current_user->ID, 32 );
        $logout_url   = wp_logout_url( admin_url() );

        $page    = sanitize_key( $_GET["page"] ?? "" );
        $pt      = sanitize_key( $_GET["post_type"] ?? "" );
        $screen  = function_exists( "get_current_screen" ) ? get_current_screen() : null;
        $scr_id  = $screen ? ( $screen->id ?? "" ) : "";

        $pending = wp_count_posts( "va_listing" )->pending ?? 0;
        $auctions_enabled = function_exists( "va_auctions_enabled" ) && va_auctions_enabled();

        /* Topbar cím térkép */
        $titles = [
            "vadaszapro-dashboard"    => "Irányítópult",
            "vadaszapro"              => "Általános beállítások",
            "vadaszapro-design"       => "Design beállítások",
            "vadaszapro-layout"       => "Layout Állító",
            "vadaszapro-header-footer"=> "Fejléc & Lábléc",
            "vadaszapro-tools"        => "Export / Import",
            "vadaszapro-reklam"       => "Reklámzónák",
            "vadaszapro-hirdetes"     => "Hirdetés beállítások",
            "vadaszapro-aukcio"       => "Aukció beállítások",
            "vadaszapro-users"        => "Felhasználók",
            "va-form-builder"         => "Form Szerkesztő",
            "vadaszapro-stats"        => "Statisztika",
        ];
        if ( $pt === "va_listing" ) {
            $topbar_title = "Hirdetések";
        } elseif ( $pt === "va_auction" ) {
            $topbar_title = "Aukciók";
        } else {
            $topbar_title = $titles[ $page ] ?? "VadászApró Admin";
        }

        ?>
        <div id="va-sidebar">
            <a href="<?php echo esc_url( admin_url( "admin.php?page=vadaszapro-dashboard" ) ); ?>" class="va-sb-logo">
                <div class="va-sb-logo__icon">🎯</div>
                <div class="va-sb-logo__name">
                    VadászApró
                    <small>Admin Panel</small>
                </div>
            </a>

            <nav class="va-sb-nav">

                <?php self::sb_item( "📊", "Irányítópult", admin_url( "admin.php?page=vadaszapro-dashboard" ), $page === "vadaszapro-dashboard" ); ?>

                <?php self::sb_item( "📋", "Hirdetések", admin_url( "edit.php?post_type=va_listing" ), $pt === "va_listing" || str_contains( $scr_id, "va_listing" ), $pending > 0 ? (int)$pending : 0 ); ?>

                <?php if ( $auctions_enabled ): ?>
                <?php self::sb_item( "🔨", "Aukciók", admin_url( "edit.php?post_type=va_auction" ), $pt === "va_auction" || str_contains( $scr_id, "va_auction" ) ); ?>
                <?php endif; ?>

                <?php self::sb_item( "👥", "Felhasználók", admin_url( "admin.php?page=vadaszapro-users" ), $page === "vadaszapro-users" ); ?>

                <span class="va-sb-sep">Beállítások</span>

                <?php self::sb_item( "⚙️", "Általános", admin_url( "admin.php?page=vadaszapro" ), $page === "vadaszapro" ); ?>
                <?php self::sb_item( "🎨", "Design", admin_url( "admin.php?page=vadaszapro-design" ), $page === "vadaszapro-design" ); ?>
                <?php self::sb_item( "📐", "Layout Állító", admin_url( "admin.php?page=vadaszapro-layout" ), $page === "vadaszapro-layout" ); ?>
                <?php self::sb_item( "🗂️", "Fejléc & Lábléc", admin_url( "admin.php?page=vadaszapro-header-footer" ), $page === "vadaszapro-header-footer" ); ?>
                <?php self::sb_item( "🧩", "Form szerkesztő", admin_url( "admin.php?page=va-form-builder" ), $page === "va-form-builder" ); ?>

                <span class="va-sb-sep">Tartalom</span>

                <?php self::sb_item( "📢", "Reklámzónák", admin_url( "admin.php?page=vadaszapro-reklam" ), $page === "vadaszapro-reklam" ); ?>
                <?php self::sb_item( "📈", "Statisztika", admin_url( "admin.php?page=vadaszapro-stats" ), $page === "vadaszapro-stats" ); ?>
                <?php self::sb_item( "🔧", "Export / Import", admin_url( "admin.php?page=vadaszapro-tools" ), $page === "vadaszapro-tools" ); ?>

                <span class="va-sb-sep">Külső</span>

                <a href="<?php echo esc_url( home_url( "/" ) ); ?>" target="_blank" class="va-sb-item">
                    <span class="va-sb-item__icon">🌐</span>
                    <span class="va-sb-item__label">Weboldal ↗</span>
                </a>

            </nav>

            <div class="va-sb-footer">
                <div class="va-sb-footer__avatar"><?php echo $avatar; ?></div>
                <div class="va-sb-footer__user">
                    <span class="va-sb-footer__name"><?php echo esc_html( $current_user->display_name ); ?></span>
                    <span class="va-sb-footer__role">Adminisztrátor</span>
                </div>
                <a href="<?php echo esc_url( $logout_url ); ?>" class="va-sb-logout" title="Kijelentkezés">⏻</a>
            </div>
        </div>

        <div id="va-topbar">
            <div class="va-topbar__title">
                <?php echo esc_html( $topbar_title ); ?>
                <small>VadászApró</small>
            </div>
            <div class="va-topbar__actions">
                <a href="<?php echo esc_url( admin_url( "post-new.php?post_type=va_listing" ) ); ?>" class="va-topbar__btn va-topbar__btn--primary">
                    + Új hirdetés
                </a>
                <a href="<?php echo esc_url( home_url( "/" ) ); ?>" target="_blank" class="va-topbar__btn">
                    🌐 Weboldal
                </a>
            </div>
        </div>
        <?php
    }

    private static function sb_item( string $icon, string $label, string $href, bool $active = false, int $badge = 0 ): void {
        $cls = "va-sb-item" . ( $active ? " active" : "" );
        echo "<a href=\"" . esc_url( $href ) . "\" class=\"{$cls}\">";
        echo "<span class=\"va-sb-item__icon\">{$icon}</span>";
        echo "<span class=\"va-sb-item__label\">" . esc_html( $label ) . "</span>";
        if ( $badge > 0 ) echo "<span class=\"va-sb-item__badge\">{$badge}</span>";
        echo "</a>";
    }

    /* ── Menük ──────────────────────────────────────────────── */
    public static function register_menus() {
        $auctions_enabled = function_exists( "va_auctions_enabled" ) ? va_auctions_enabled() : true;

        add_menu_page(
            "VadászApró",
            "VadászApró",
            "manage_options",
            "vadaszapro",
            [ VA_Settings_Page::class, "render_general" ],
            "dashicons-megaphone",
            4
        );

        // Dashboard – első almenü
        add_submenu_page( "vadaszapro", "Irányítópult", "Irányítópult", "manage_options", "vadaszapro-dashboard", [ VA_Dashboard::class, "render" ] );

        add_submenu_page( "vadaszapro", "Általános beállítások",  "Általános",          "manage_options", "vadaszapro",               [ VA_Settings_Page::class, "render_general"        ] );
        add_submenu_page( "vadaszapro", "Design beállítások",     "Design",             "manage_options", "vadaszapro-design",         [ VA_Settings_Page::class, "render_design"          ] );
        add_submenu_page( "vadaszapro", "Layout Állító",          "Layout Állító",      "manage_options", "vadaszapro-layout",         [ VA_Settings_Page::class, "render_layout_builder"  ] );
        add_submenu_page( "vadaszapro", "Fejléc + Lábléc",        "Fejléc + Lábléc",   "manage_options", "vadaszapro-header-footer",  [ VA_Settings_Page::class, "render_header_footer"   ] );
        add_submenu_page( "vadaszapro", "Export / Import",        "Export / Import",   "manage_options", "vadaszapro-tools",          [ VA_Settings_Page::class, "render_tools"            ] );
        add_submenu_page( "vadaszapro", "Reklámzónák",            "Reklámzónák",       "manage_options", "vadaszapro-reklam",         [ VA_Settings_Page::class, "render_ad_zones"         ] );
        add_submenu_page( "vadaszapro", "Hirdetés beállítások",   "Hirdetések",        "manage_options", "vadaszapro-hirdetes",       [ VA_Settings_Page::class, "render_listings"         ] );
        if ( $auctions_enabled ) {
            add_submenu_page( "vadaszapro", "Aukció beállítások", "Aukciók",           "manage_options", "vadaszapro-aukcio",         [ VA_Settings_Page::class, "render_auctions"         ] );
        }
        add_submenu_page( "vadaszapro", "Felhasználók",           "Felhasználók",      "manage_options", "vadaszapro-users",          [ VA_Settings_Page::class, "render_users"            ] );
        add_submenu_page( "vadaszapro", "Form szerkesztő",        "🧩 Form szerkesztő","manage_options", "va-form-builder",           [ VA_Form_Builder::class,  "render"                 ] );
        add_submenu_page( "vadaszapro", "Statisztika",            "Statisztika",       "manage_options", "vadaszapro-stats",          [ VA_Settings_Page::class, "render_stats"            ] );
    }

    /* ── Assets ─────────────────────────────────────────────── */
    public static function enqueue( $hook ) {
        $post_types = [ "va_listing" ];
        if ( function_exists( "va_auctions_enabled" ) && va_auctions_enabled() ) {
            $post_types[] = "va_auction";
        }

        $is_va_page = strpos( $hook, "vadaszapro" ) !== false
            || strpos( $hook, "va-form-builder" ) !== false
            || in_array( get_post_type(), $post_types, true )
            || ( isset( $_GET["post_type"] ) && in_array( $_GET["post_type"], $post_types, true ) );

        if ( ! $is_va_page ) return;

        // SortableJS a form szerkesztőhöz
        if ( strpos( $hook, "va-form-builder" ) !== false ) {
            wp_enqueue_script( "sortablejs", "https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js", [], "1.15.2", true );
        }

        // Chart.js – dashboard és stats oldalon
        if ( strpos( $hook, "vadaszapro-dashboard" ) !== false || strpos( $hook, "vadaszapro-stats" ) !== false ) {
            wp_enqueue_script( "chartjs", "https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js", [], "4.4.3", true );
        }

        wp_enqueue_media();
        wp_enqueue_style(  "va-admin", VA_PLUGIN_URL . "admin/admin.css", [], VA_VERSION );
        wp_enqueue_style(  "wp-color-picker" );
        wp_enqueue_script( "va-admin", VA_PLUGIN_URL . "admin/admin.js", [ "jquery", "wp-color-picker" ], VA_VERSION, true );
    }
}