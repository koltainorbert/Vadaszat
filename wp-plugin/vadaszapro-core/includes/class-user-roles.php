<?php
/**
 * Felhasználói Tervek (Plans) + Hirdetés Kiemelés (Boost) rendszer
 *
 * Tervek:
 *  basic    – Ingyenes, 1 aktív hirdetés egyszerre,          kiemelés 7 naponta
 *  silver   – Fizetős havi,  max 5 hirdetés/hónap,           kiemelés 5 naponta
 *  gold     – Fizetős éves, max 10 hirdetés/hónap,           kiemelés 3 naponta
 *  platinum – Egyedi feltételek, admin állítja a limiteket,  kiemelés 3 naponta (vagy custom)
 *
 * Storage:
 *  wp_usermeta  va_plan                       → plan slug
 *  wp_usermeta  va_plan_listing_limit         → platinum custom havi limit
 *  wp_usermeta  va_plan_boost_cooldown        → platinum custom cooldown (nap)
 *  wp_usermeta  va_plan_note                  → admin megjegyzés (platinum)
 *  wp_postmeta  va_boost_time                 → utolsó boost Unix timestamp
 *  wp_postmeta  va_boost_user_{ID}_last       → per-user per-post utolsó boost
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VA_User_Roles {

    /** Runtime cache a plans config-hoz (wp_options overlay) */
    private static ?array $_cfg_cache = null;

    /* ── Plan definíciók (alap értékek / fallback) ───────────── */
    const PLANS = [
        'basic'    => [
            'label'         => 'Basic',
            'color'         => '#888888',
            'bg'            => 'rgba(136,136,136,.15)',
            'icon'          => '🥉',
            'monthly_limit' => 1,       // max aktív hirdetés (basis=active)
            'boost_cooldown'=> 7,       // napok
            'basis'         => 'active',// 'active' = összes aktív | 'monthly' = havi
            'description'   => 'Ingyenes alap csomag – 1 aktív hirdetés',
        ],
        'silver'   => [
            'label'         => 'Silver',
            'color'         => '#c0c0c0',
            'bg'            => 'rgba(192,192,192,.15)',
            'icon'          => '🥈',
            'monthly_limit' => 5,
            'boost_cooldown'=> 5,
            'basis'         => 'monthly',
            'description'   => 'Havi előfizetés – 5 hirdetés/hó',
        ],
        'gold'     => [
            'label'         => 'Gold',
            'color'         => '#ffd700',
            'bg'            => 'rgba(255,215,0,.15)',
            'icon'          => '🥇',
            'monthly_limit' => 10,
            'boost_cooldown'=> 3,
            'basis'         => 'monthly',
            'description'   => 'Éves előfizetés – 10 hirdetés/hó',
        ],
        'platinum' => [
            'label'         => 'Platinum',
            'color'         => '#e2c6ff',
            'bg'            => 'rgba(226,198,255,.15)',
            'icon'          => '💎',
            'monthly_limit' => 20,      // felülírja va_plan_listing_limit
            'boost_cooldown'=> 3,       // felülírja va_plan_boost_cooldown
            'basis'         => 'monthly',
            'description'   => 'Egyedi feltételek – admin határozza meg',
        ],
    ];

    /* ── Boot ───────────────────────────────────────────────── */
    public static function init(): void {
        // Admin AJAX: admin állítja a tervet
        add_action( 'wp_ajax_va_admin_set_user_plan',  [ __CLASS__, 'ajax_admin_set_plan'      ] );
        // Admin AJAX: plan beállítások mentése
        add_action( 'wp_ajax_va_admin_save_plan_cfg',  [ __CLASS__, 'ajax_save_plan_settings'  ] );

        // Frontend AJAX: felhasználó boostol egy hirdetést
        add_action( 'wp_ajax_va_boost_listing', [ __CLASS__, 'ajax_boost_listing' ] );

        // Boost sorrendezés a va_listing archívum/taxonómia oldalain
        add_filter( 'posts_clauses', [ __CLASS__, 'filter_posts_clauses' ], 10, 2 );
    }

    /* ══ Plan config – options overlay ════════════════════════ */

    /**
     * Teljes plans config DB-ből (wp_options), merged a PLANS const alapértékeivel.
     * @return array<string,array>
     */
    public static function get_all_plan_configs(): array {
        if ( self::$_cfg_cache !== null ) return self::$_cfg_cache;

        $saved = get_option( 'va_plans_config', [] );
        if ( ! is_array( $saved ) ) $saved = [];

        $merged = [];
        foreach ( self::PLANS as $slug => $defaults ) {
            $override        = isset( $saved[ $slug ] ) && is_array( $saved[ $slug ] ) ? $saved[ $slug ] : [];
            $merged[ $slug ] = array_merge( $defaults, $override );
            // Típus kényszer
            $merged[ $slug ]['monthly_limit']  = (int)  $merged[ $slug ]['monthly_limit'];
            $merged[ $slug ]['boost_cooldown'] = (int)  $merged[ $slug ]['boost_cooldown'];
            $merged[ $slug ]['basis']          = in_array( $merged[ $slug ]['basis'], [ 'active', 'monthly' ], true )
                ? $merged[ $slug ]['basis'] : $defaults['basis'];
        }

        // Globális boost beállítások
        $global_defaults = [
            'boost_badge_window' => 14,
            'boost_badge_text'   => '⚡ Előre téve',
            'boost_enabled'      => true,
        ];
        $global_saved    = isset( $saved['_global'] ) && is_array( $saved['_global'] ) ? $saved['_global'] : [];
        $merged['_global'] = array_merge( $global_defaults, $global_saved );

        self::$_cfg_cache = $merged;
        return $merged;
    }

    /** Plan config invalidálása (mentés után hívandó) */
    public static function flush_plan_cache(): void {
        self::$_cfg_cache = null;
    }

    public static function get_user_plan( int $user_id ): string {
        $plan = (string) get_user_meta( $user_id, 'va_plan', true );
        $all  = self::get_all_plan_configs();
        // _global key nem plan slug
        return ( isset( $all[ $plan ] ) && $plan !== '_global' ) ? $plan : 'basic';
    }

    /**
     * Plan konfiguráció – DB override-dal, platinum esetén user-specifikus értékekkel.
     * @return array{label:string,color:string,bg:string,icon:string,monthly_limit:int,boost_cooldown:int,basis:string,description:string}
     */
    public static function get_plan_config( string $plan, int $user_id = 0 ): array {
        $all = self::get_all_plan_configs();
        $cfg = ( isset( $all[ $plan ] ) && $plan !== '_global' ) ? $all[ $plan ] : $all['basic'];

        if ( $plan === 'platinum' && $user_id > 0 ) {
            $custom_limit = (int) get_user_meta( $user_id, 'va_plan_listing_limit', true );
            $custom_cd    = (int) get_user_meta( $user_id, 'va_plan_boost_cooldown', true );
            if ( $custom_limit > 0 ) $cfg['monthly_limit']  = $custom_limit;
            if ( $custom_cd    > 0 ) $cfg['boost_cooldown'] = $custom_cd;
        }
        return $cfg;
    }

    /* ══ Hirdetésszám-ellenőrzők ════════════════════════════════ */

    /** Aktuális hónapban feladott aktív/pending/draft hirdetések száma */
    public static function get_monthly_listing_count( int $user_id ): int {
        global $wpdb;
        $start = gmdate( 'Y-m-01 00:00:00' );
        $end   = gmdate( 'Y-m-t 23:59:59' );
        return (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->posts}
             WHERE post_type   = %s
               AND post_author = %d
               AND post_status IN ('publish','pending','draft','future','private')
               AND post_date  >= %s
               AND post_date  <= %s",
            'va_listing', $user_id, $start, $end
        ) );
    }

    /** Összes aktív hirdetés (minden státuszban, minden hónapban) – basic limit */
    public static function get_active_listing_count( int $user_id ): int {
        global $wpdb;
        return (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->posts}
             WHERE post_type   = %s
               AND post_author = %d
               AND post_status IN ('publish','pending','draft','future','private')",
            'va_listing', $user_id
        ) );
    }

    /**
     * Feladhat-e új hirdetést a felhasználó?
     * @return array{can:bool, reason:string, used:int, limit:int}
     */
    public static function can_post_listing( int $user_id ): array {
        $plan = self::get_user_plan( $user_id );
        $cfg  = self::get_plan_config( $plan, $user_id );

        // Korlátlan (0 vagy -1)
        if ( $cfg['monthly_limit'] <= 0 ) {
            return [ 'can' => true, 'reason' => '', 'used' => 0, 'limit' => 0 ];
        }

        $limit = $cfg['monthly_limit'];
        $used  = ( $cfg['basis'] === 'active' )
            ? self::get_active_listing_count( $user_id )
            : self::get_monthly_listing_count( $user_id );

        if ( $used < $limit ) {
            return [ 'can' => true, 'reason' => '', 'used' => $used, 'limit' => $limit ];
        }

        $label = $cfg['label'];
        if ( $cfg['basis'] === 'active' ) {
            $reason = "{$label} csomaggal egyszerre legfeljebb {$limit} aktív hirdetésed lehet. Töröl egy meglévőt, vagy frissítsd csomagodat!";
        } else {
            $reason = "{$label} csomaggal havonta legfeljebb {$limit} hirdetést adhatsz fel. A hónap végén újra indul a keret.";
        }

        return [ 'can' => false, 'reason' => $reason, 'used' => $used, 'limit' => $limit ];
    }

    /* ══ Boost logika ═══════════════════════════════════════════ */

    /** Hány másodperc van még a következő boostig? 0 = azonnal boostolhat. */
    public static function boost_seconds_remaining( int $user_id, int $post_id ): int {
        $plan    = self::get_user_plan( $user_id );
        $cfg     = self::get_plan_config( $plan, $user_id );
        $cd_secs = $cfg['boost_cooldown'] * DAY_IN_SECONDS;

        $last_boost = (int) get_post_meta( $post_id, 'va_boost_user_' . $user_id . '_last', true );
        if ( $last_boost === 0 ) return 0;

        $elapsed = time() - $last_boost;
        return ( $elapsed >= $cd_secs ) ? 0 : ( $cd_secs - $elapsed );
    }

    /** @return array{can:bool, seconds_remaining:int, cooldown_days:int} */
    public static function can_boost( int $user_id, int $post_id ): array {
        $plan = self::get_user_plan( $user_id );
        $cfg  = self::get_plan_config( $plan, $user_id );
        $rem  = self::boost_seconds_remaining( $user_id, $post_id );
        return [
            'can'               => $rem === 0,
            'seconds_remaining' => $rem,
            'cooldown_days'     => $cfg['boost_cooldown'],
        ];
    }

    /**
     * Boost elvégzése. True ha sikeres.
     */
    public static function do_boost( int $user_id, int $post_id ): bool {
        $check = self::can_boost( $user_id, $post_id );
        if ( ! $check['can'] ) return false;

        $now = time();
        update_post_meta( $post_id, 'va_boost_time', $now );
        update_post_meta( $post_id, 'va_boost_user_' . $user_id . '_last', $now );
        return true;
    }

    /**
     * Hirdetés boostednak számít-e (kategória badge megjelenítéshez)?
     * Az ablak mérete a globális konfigból jön (alapba 14 nap).
     */
    public static function is_boosted( int $post_id, int $window_days = 0 ): bool {
        if ( $window_days <= 0 ) {
            $cfg = self::get_all_plan_configs();
            $window_days = (int) ( $cfg['_global']['boost_badge_window'] ?? 14 );
        }
        $bt = (int) get_post_meta( $post_id, 'va_boost_time', true );
        if ( $bt <= 0 ) return false;
        return ( time() - $bt ) < ( $window_days * DAY_IN_SECONDS );
    }

    /* ══ Query szűrő: boost sorrendezés ════════════════════════ */

    public static function filter_posts_clauses( array $clauses, \WP_Query $query ): array {
        global $wpdb;

        if ( is_admin() ) return $clauses;
        if ( ! $query->is_main_query() ) return $clauses;

        // Csak va_listing típusnál
        $pt = $query->get( 'post_type' );
        $pt_arr = is_array( $pt ) ? $pt : [ $pt ];
        if ( ! in_array( 'va_listing', $pt_arr, true ) ) {
            return $clauses;
        }

        // Ne írjuk felül ha valaki explicit nem-dátum szerinti sorrendezést kért
        $orderby = $query->get( 'orderby' );
        if ( $orderby && ! in_array( $orderby, [ '', 'date', 'post_date', 'none' ], true ) ) {
            return $clauses;
        }

        $alias = 'va_bst_pm';

        // Csak egyszer adjuk hozzá a JOIN-t
        if ( strpos( $clauses['join'], $alias ) !== false ) {
            return $clauses;
        }

        $clauses['join'] .= " LEFT JOIN {$wpdb->postmeta} AS {$alias}
            ON ( {$alias}.post_id = {$wpdb->posts}.ID
                 AND {$alias}.meta_key = 'va_boost_time' )";

        // Boosted hirdetések először (frissebb boost → magasabb), aztán feladás dátuma
        $clauses['orderby'] = "COALESCE( CAST( {$alias}.meta_value AS UNSIGNED ), 0 ) DESC, {$wpdb->posts}.post_date DESC";

        return $clauses;
    }

    /* ══ AJAX: Plan beállítások mentése (admin) ═══════════════ */

    public static function ajax_save_plan_settings(): void {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => 'Nincs jogosultság.' ] );
        }
        check_ajax_referer( 'va_admin_plan_cfg', 'nonce' );

        $raw   = isset( $_POST['plans'] ) ? wp_unslash( (string) $_POST['plans'] ) : '{}';
        $input = json_decode( $raw, true );
        if ( ! is_array( $input ) ) {
            wp_send_json_error( [ 'message' => 'Érvénytelen adatformátum.' ] );
        }

        $current       = get_option( 'va_plans_config', [] );
        if ( ! is_array( $current ) ) $current = [];

        $allowed_slugs = array_keys( self::PLANS );

        foreach ( $allowed_slugs as $slug ) {
            if ( ! isset( $input[ $slug ] ) || ! is_array( $input[ $slug ] ) ) continue;
            $d = $input[ $slug ];

            $current[ $slug ] = [
                'label'          => sanitize_text_field( $d['label']         ?? '' ),
                'icon'           => sanitize_text_field( $d['icon']          ?? '' ),
                'color'          => sanitize_hex_color( $d['color']          ?? '' ) ?? self::PLANS[ $slug ]['color'],
                'bg'             => sanitize_text_field( $d['bg']            ?? '' ),
                'monthly_limit'  => max( 0, (int) ( $d['monthly_limit']     ?? 0 ) ),
                'boost_cooldown' => max( 1, (int) ( $d['boost_cooldown']    ?? 1 ) ),
                'basis'          => in_array( $d['basis'] ?? '', [ 'active', 'monthly' ], true ) ? $d['basis'] : self::PLANS[ $slug ]['basis'],
                'description'    => sanitize_textarea_field( $d['description']   ?? '' ),
                'price_monthly'  => sanitize_text_field( $d['price_monthly']     ?? '' ),
                'price_yearly'   => sanitize_text_field( $d['price_yearly']      ?? '' ),
                'badge_text'     => sanitize_text_field( $d['badge_text']        ?? '' ),
            ];
        }

        if ( isset( $input['_global'] ) && is_array( $input['_global'] ) ) {
            $g = $input['_global'];
            $current['_global'] = [
                'boost_badge_window' => max( 1, (int) ( $g['boost_badge_window'] ?? 14 ) ),
                'boost_badge_text'   => sanitize_text_field( $g['boost_badge_text']  ?? '⚡ Előre téve' ),
                'boost_enabled'      => ! empty( $g['boost_enabled'] ),
            ];
        }

        update_option( 'va_plans_config', $current );
        self::flush_plan_cache();

        wp_send_json_success( [ 'message' => 'Csomag beállítások sikeresen mentve!' ] );
    }

    /* ══ AJAX: Admin állítja a tervet ══════════════════════════ */

    public static function ajax_admin_set_plan(): void {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => 'Nincs jogosultság.' ] );
        }

        check_ajax_referer( 'va_admin_user_plan', 'nonce' );

        $target_uid  = absint( $_POST['user_id'] ?? 0 );
        $plan        = sanitize_key( $_POST['plan'] ?? 'basic' );
        $custom_lim  = absint( $_POST['custom_limit'] ?? 0 );
        $custom_cd   = absint( $_POST['custom_boost_cooldown'] ?? 0 );
        $plan_note   = sanitize_textarea_field( wp_unslash( (string) ( $_POST['plan_note'] ?? '' ) ) );

        if ( ! $target_uid || ! isset( self::get_all_plan_configs()[ $plan ] ) || $plan === '_global' ) {
            wp_send_json_error( [ 'message' => 'Érvénytelen adat.' ] );
        }

        // Ne engedjen admin fiókot módosítani
        $target = get_userdata( $target_uid );
        if ( ! $target ) {
            wp_send_json_error( [ 'message' => 'Felhasználó nem található.' ] );
        }
        if ( in_array( 'administrator', (array) $target->roles, true ) ) {
            wp_send_json_error( [ 'message' => 'Adminisztrátor jogköre nem módosítható.' ] );
        }

        update_user_meta( $target_uid, 'va_plan', $plan );

        if ( $plan === 'platinum' ) {
            if ( $custom_lim > 0 ) {
                update_user_meta( $target_uid, 'va_plan_listing_limit', $custom_lim );
            }
            if ( $custom_cd > 0 ) {
                update_user_meta( $target_uid, 'va_plan_boost_cooldown', $custom_cd );
            }
            if ( $plan_note !== '' ) {
                update_user_meta( $target_uid, 'va_plan_note', $plan_note );
            }
        }

        $cfg = self::get_plan_config( $plan, $target_uid );
        wp_send_json_success( [
            'message' => 'Terv sikeresen frissítve!',
            'plan'    => $plan,
            'label'   => $cfg['label'],
            'icon'    => $cfg['icon'],
            'color'   => $cfg['color'],
        ] );
    }

    /* ══ AJAX: Felhasználó boostol ══════════════════════════════ */

    public static function ajax_boost_listing(): void {
        check_ajax_referer( 'va_user_nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( [ 'message' => 'Nincs jogosultság.' ] );
        }

        $user_id = get_current_user_id();
        $post_id = absint( $_POST['post_id'] ?? 0 );

        if ( ! $post_id ) {
            wp_send_json_error( [ 'message' => 'Érvénytelen hirdetés azonosító.' ] );
        }

        $post = get_post( $post_id );
        if ( ! $post || $post->post_type !== 'va_listing' ) {
            wp_send_json_error( [ 'message' => 'Hirdetés nem található.' ] );
        }

        // Csak a saját hirdetést boostolhatja
        if ( (int) $post->post_author !== $user_id ) {
            wp_send_json_error( [ 'message' => 'Csak saját hirdetést emelhetsz ki.' ] );
        }

        $check = self::can_boost( $user_id, $post_id );

        if ( ! $check['can'] ) {
            $hours = (int) ceil( $check['seconds_remaining'] / 3600 );
            $days  = round( $check['seconds_remaining'] / DAY_IN_SECONDS, 1 );
            $msg   = $hours >= 24
                ? "Még {$days} nap múlva emelheted ki ezt a hirdetést."
                : "Még {$hours} óra múlva emelheted ki ezt a hirdetést.";
            wp_send_json_error( [
                'message'           => $msg,
                'seconds_remaining' => $check['seconds_remaining'],
            ] );
        }

        self::do_boost( $user_id, $post_id );

        wp_send_json_success( [
            'message' => '✅ Hirdetés kiemelve! Az adott kategóriában az élre kerültél.',
        ] );
    }
}
