<?php
/**
 * Hirdetés admin: egyedi lista + szerkesztő oldal (Gutenberg-mentes)
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class VA_Listing_Edit {

    public static function init(): void {
        // Block editor kikapcsolása va_listing és va_auction esetén
        add_filter( 'use_block_editor_for_post_type', [ __CLASS__, 'disable_block_editor' ], 10, 2 );
        // Átirányítás WP natív szerkesztőkből → egyedi oldalunkra
        add_action( 'load-post-new.php', [ __CLASS__, 'redirect_new' ] );
        add_action( 'load-post.php',     [ __CLASS__, 'redirect_edit' ] );
        // Form mentés + quick actions
        add_action( 'admin_post_va_listing_save',    [ __CLASS__, 'handle_save'    ] );
        add_action( 'admin_post_va_listing_approve', [ __CLASS__, 'handle_approve' ] );
        add_action( 'admin_post_va_listing_delete',  [ __CLASS__, 'handle_delete'  ] );
        // Quill CDN betöltése az admin fejlécbe
        add_action( 'admin_head', [ __CLASS__, 'quill_assets' ] );
    }

    /* ── Quill CDN betöltése ── */
    public static function quill_assets(): void {
        $screen = get_current_screen();
        if ( ! $screen || strpos( $screen->id, 'vadaszapro-listing-edit' ) === false ) return;
        ?>
        <link rel="stylesheet" href="https://cdn.quilljs.com/1.3.7/quill.snow.css">
        <script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
        <style>
        #va-admin-quill-editor { background:#111; border-radius:0 0 6px 6px; }
        #va-admin-quill-editor .ql-editor { color:#e8e8e8; min-height:220px; font-size:15px; line-height:1.7; font-family:system-ui,sans-serif; }
        #va-admin-quill-editor .ql-editor.ql-blank::before { color:rgba(255,255,255,.3); font-style:normal; }
        #va-admin-quill-editor a { color:#ff4444; }
        #va-admin-quill-editor img { max-width:100%; border-radius:4px; }
        #va-admin-quill-editor blockquote { border-left:3px solid #ff4444; padding-left:12px; color:#aaa; }
        .ql-toolbar.ql-snow { background:#1e1e1e; border:1px solid rgba(255,255,255,.15)!important; border-bottom:none!important; border-radius:6px 6px 0 0; }
        .ql-container.ql-snow { border:1px solid rgba(255,255,255,.15)!important; border-radius:0 0 6px 6px; }
        .ql-snow .ql-stroke { stroke:#aaa!important; }
        .ql-snow .ql-fill,.ql-snow .ql-stroke.ql-fill { fill:#aaa!important; }
        .ql-snow .ql-picker { color:#bbb!important; }
        .ql-snow .ql-picker-label { border-color:rgba(255,255,255,.15)!important; }
        .ql-snow .ql-picker-options { background:#1e1e1e!important; border-color:rgba(255,255,255,.15)!important; }
        .ql-snow .ql-picker-item { color:#bbb!important; }
        .ql-snow .ql-picker-item:hover,.ql-snow .ql-picker-item.ql-selected { color:#fff!important; }
        .ql-snow.ql-toolbar button:hover .ql-stroke,.ql-snow .ql-toolbar button:hover .ql-stroke { stroke:#ff4444!important; }
        .ql-snow.ql-toolbar button.ql-active .ql-stroke { stroke:#ff4444!important; }
        .ql-snow.ql-toolbar button:hover .ql-fill,.ql-snow.ql-toolbar button.ql-active .ql-fill { fill:#ff4444!important; }
        .ql-snow .ql-tooltip { background:#1e1e1e!important; border-color:rgba(255,255,255,.15)!important; color:#e8e8e8!important; box-shadow:0 4px 20px rgba(0,0,0,.5)!important; }
        .ql-snow .ql-tooltip input[type=text] { background:#111!important; border-color:rgba(255,255,255,.2)!important; color:#e8e8e8!important; }
        .ql-snow .ql-tooltip a.ql-action,.ql-snow .ql-tooltip a.ql-remove { color:#ff4444!important; }
        </style>
        <?php
    }

    /* ── Block editor kikapcs. ───────────────────────────────── */
    public static function disable_block_editor( bool $use, string $post_type ): bool {
        return in_array( $post_type, [ 'va_listing', 'va_auction' ], true ) ? false : $use;
    }

    /* ── Átirányítások ───────────────────────────────────────── */
    public static function redirect_new(): void {
        if ( sanitize_key( $_GET['post_type'] ?? '' ) !== 'va_listing' ) return;
        wp_safe_redirect( admin_url( 'admin.php?page=vadaszapro-listing-edit' ) );
        exit;
    }

    public static function redirect_edit(): void {
        if ( sanitize_key( $_GET['action'] ?? '' ) !== 'edit' ) return;
        $post_id = (int)( $_GET['post'] ?? 0 );
        if ( ! $post_id ) return;
        $post = get_post( $post_id );
        if ( ! $post || $post->post_type !== 'va_listing' ) return;
        wp_safe_redirect( admin_url( 'admin.php?page=vadaszapro-listing-edit&id=' . $post_id ) );
        exit;
    }

    /* ── Mentés ──────────────────────────────────────────────── */
    public static function handle_save(): void {
        if ( ! current_user_can( 'edit_posts' ) ) wp_die( 'Nincs jogosultság.' );
        check_admin_referer( 'va_listing_save', 'va_listing_nonce' );

        $post_id = (int)( $_POST['va_post_id'] ?? 0 );
        $status  = sanitize_key( $_POST['va_post_status'] ?? 'draft' );
        if ( ! in_array( $status, [ 'publish', 'pending', 'draft' ], true ) ) $status = 'draft';

        $post_data = [
            'post_type'    => 'va_listing',
            'post_title'   => sanitize_text_field( wp_unslash( $_POST['va_title'] ?? '' ) ),
            'post_content' => wp_kses_post( wp_unslash( $_POST['va_description'] ?? '' ) ),
            'post_status'  => $status,
            'post_author'  => (int)( $_POST['va_author'] ?? get_current_user_id() ),
        ];

        if ( $post_id > 0 ) {
            $post_data['ID'] = $post_id;
            $result = wp_update_post( $post_data, true );
        } else {
            $result = wp_insert_post( $post_data, true );
        }

        if ( is_wp_error( $result ) ) {
            wp_safe_redirect( add_query_arg( 'va_error', '1', admin_url( 'admin.php?page=vadaszapro-listings' ) ) );
            exit;
        }

        $post_id = (int)$result;

        // Meta mezők mentése
        foreach ( VA_Meta_Fields::listing_fields() as $key => $f ) {
            if ( ! empty( $f['readonly'] ) ) continue;
            if ( $f['type'] === 'checkbox' ) {
                update_post_meta( $post_id, $key, isset( $_POST[ $key ] ) ? '1' : '0' );
            } elseif ( isset( $_POST[ $key ] ) ) {
                update_post_meta( $post_id, $key, sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) );
            }
        }

        // Galéria képek
        $raw_gids = sanitize_text_field( wp_unslash( $_POST['va_gallery_ids'] ?? '' ) );
        $gids = array_values( array_unique( array_filter( array_map( 'intval', $raw_gids !== '' ? explode( ',', $raw_gids ) : [] ) ) ) );
        update_post_meta( $post_id, 'va_gallery_ids', implode( ',', $gids ) );

        // Kiemelt kép (borítókép) – a galériából
        $thumbnail_id = (int)( $_POST['va_thumbnail_id'] ?? 0 );
        if ( $thumbnail_id > 0 ) {
            set_post_thumbnail( $post_id, $thumbnail_id );
        } else {
            delete_post_thumbnail( $post_id );
        }

        // Taxonómiák
        $cat_id    = (int)( $_POST['va_category'] ?? 0 );
        $county_id = (int)( $_POST['va_county']   ?? 0 );
        wp_set_object_terms( $post_id, $cat_id    ? [ $cat_id ]    : [], 'va_category' );
        wp_set_object_terms( $post_id, $county_id ? [ $county_id ] : [], 'va_county'   );

        // Listing meta szinkron
        if ( function_exists( 'va_sync_listing_meta' ) ) {
            va_sync_listing_meta( $post_id );
        }

        // Egyedi (custom_*) mezők mentése a Form Builder konfigból
        if ( class_exists( 'VA_Form_Builder' ) ) {
            $admin_fields = VA_Form_Builder::get_fields( 'va_admin_listing_edit' );
            foreach ( $admin_fields as $aff ) {
                $akey = (string)( $aff['key'] ?? '' );
                if ( ! str_starts_with( $akey, 'custom_' ) ) continue;
                if ( empty( $aff['enabled'] ) ) continue;
                $atype = (string)( $aff['type'] ?? 'text' );
                if ( $atype === 'checkbox' ) {
                    update_post_meta( $post_id, $akey, isset( $_POST[ $akey ] ) ? '1' : '0' );
                } elseif ( isset( $_POST[ $akey ] ) ) {
                    update_post_meta( $post_id, $akey, sanitize_text_field( wp_unslash( $_POST[ $akey ] ) ) );
                }
            }
        }

        wp_safe_redirect( admin_url( 'admin.php?page=vadaszapro-listing-edit&id=' . $post_id . '&va_saved=1' ) );
        exit;
    }

    /* ── Jóváhagyás ──────────────────────────────────────────── */
    public static function handle_approve(): void {
        $id = (int)( $_GET['id'] ?? 0 );
        check_admin_referer( 'va_listing_approve_' . $id );
        if ( ! current_user_can( 'edit_post', $id ) ) wp_die( 'Nincs jogosultság.' );
        wp_update_post( [ 'ID' => $id, 'post_status' => 'publish' ] );
        wp_safe_redirect( admin_url( 'admin.php?page=vadaszapro-listings&va_approved=1' ) );
        exit;
    }

    /* ── Törlés ──────────────────────────────────────────────── */
    public static function handle_delete(): void {
        $id = (int)( $_GET['id'] ?? 0 );
        check_admin_referer( 'va_listing_delete_' . $id );
        if ( ! current_user_can( 'delete_post', $id ) ) wp_die( 'Nincs jogosultság.' );
        wp_delete_post( $id, true ); // valódi törlés (képek is törlődnek a hookból)
        wp_safe_redirect( admin_url( 'admin.php?page=vadaszapro-listings&va_deleted=1' ) );
        exit;
    }

    /* ══════════════════════════════════════════════════════════
       LISTA OLDAL
    ══════════════════════════════════════════════════════════ */
    public static function render_list(): void {
        if ( ! current_user_can( 'edit_posts' ) ) return;

        $per_page   = 20;
        $paged      = max( 1, (int)( $_GET['paged'] ?? 1 ) );
        $search     = sanitize_text_field( wp_unslash( $_GET['s'] ?? '' ) );
        $status_tab = sanitize_key( $_GET['va_status'] ?? 'all' );
        $cat_filter = (int)( $_GET['va_cat'] ?? 0 );
        $orderby    = sanitize_key( $_GET['orderby'] ?? 'date' );
        $order      = strtoupper( sanitize_key( $_GET['order'] ?? 'DESC' ) ) === 'ASC' ? 'ASC' : 'DESC';

        $query_status = ( $status_tab === 'all' ) ? [ 'publish', 'pending', 'draft' ] : $status_tab;

        $args = [
            'post_type'      => 'va_listing',
            'post_status'    => $query_status,
            'posts_per_page' => $per_page,
            'paged'          => $paged,
            'orderby'        => $orderby,
            'order'          => $order,
        ];
        if ( $search )     $args['s']         = $search;
        if ( $cat_filter ) $args['tax_query']  = [[ 'taxonomy' => 'va_category', 'field' => 'term_id', 'terms' => $cat_filter ]];

        $query = new WP_Query( $args );
        $total = $query->found_posts;
        $pages = (int)ceil( $total / $per_page );

        $counts = wp_count_posts( 'va_listing' );
        $cats   = get_terms([ 'taxonomy' => 'va_category', 'hide_empty' => false, 'number' => 100 ]);
        if ( is_wp_error( $cats ) ) $cats = [];

        // Üzenetek
        $notices = [];
        if ( isset( $_GET['va_approved'] ) ) $notices[] = ['success','✅ Hirdetés közzétéve!'];
        if ( isset( $_GET['va_trashed']  ) ) $notices[] = ['success','🗑️ Hirdetés a kukába került.'];
        if ( isset( $_GET['va_error']    ) ) $notices[] = ['error','❌ Hiba történt a mentés során.'];

        $tabs = [
            'all'     => [ 'Összes',    (int)($counts->publish??0) + (int)($counts->pending??0) + (int)($counts->draft??0) ],
            'publish' => [ 'Aktív',     (int)($counts->publish??0) ],
            'pending' => [ 'Függő',     (int)($counts->pending??0) ],
            'draft'   => [ 'Vázlat',    (int)($counts->draft??0)   ],
            'trash'   => [ 'Kuka',      (int)($counts->trash??0)   ],
        ];

        ?>
        <div class="va-le-wrap">

            <?php foreach ($notices as [$type, $msg]): ?>
            <div class="va-le-notice va-le-notice--<?php echo esc_attr($type); ?>"><?php echo wp_kses_post($msg); ?></div>
            <?php endforeach; ?>

            <!-- Fejléc -->
            <div class="va-le-list-header">
                <h2>Hirdetések <span class="va-le-count"><?php echo (int)$total; ?></span></h2>
                <div class="va-le-header-actions">
                    <form method="get" class="va-le-search-form">
                        <input type="hidden" name="page" value="vadaszapro-listings">
                        <input type="hidden" name="va_status" value="<?php echo esc_attr($status_tab); ?>">
                        <?php if ($cat_filter): ?><input type="hidden" name="va_cat" value="<?php echo (int)$cat_filter; ?>"><?php endif; ?>
                        <input type="search" name="s" value="<?php echo esc_attr($search); ?>" placeholder="Keresés…" class="va-le-search">
                        <button type="submit" class="va-le-search-btn">🔍</button>
                    </form>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=vadaszapro-listing-edit')); ?>" class="va-btn va-btn--primary">+ Új hirdetés</a>
                </div>
            </div>

            <!-- Tabs + kategória szűrő -->
            <div class="va-le-tabs-bar">
                <div class="va-le-tabs">
                    <?php foreach ($tabs as $st => [$label, $cnt]):
                        $url = add_query_arg(['page'=>'vadaszapro-listings','va_status'=>$st,'paged'=>1,'s'=>$search], admin_url('admin.php'));
                    ?>
                    <a href="<?php echo esc_url($url); ?>" class="va-le-tab<?php echo ($status_tab===$st)?' active':''; ?>">
                        <?php echo esc_html($label); ?><span class="va-le-tab-cnt"><?php echo $cnt; ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php if ($cats): ?>
                <form method="get" class="va-le-cat-filter">
                    <input type="hidden" name="page" value="vadaszapro-listings">
                    <input type="hidden" name="va_status" value="<?php echo esc_attr($status_tab); ?>">
                    <input type="hidden" name="s" value="<?php echo esc_attr($search); ?>">
                    <select name="va_cat" class="va-le-cat-select" onchange="this.form.submit()">
                        <option value="">Minden kategória</option>
                        <?php foreach ($cats as $cat): ?>
                        <option value="<?php echo (int)$cat->term_id; ?>" <?php selected($cat_filter, $cat->term_id); ?>>
                            <?php echo esc_html($cat->name); ?> (<?php echo (int)$cat->count; ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </form>
                <?php endif; ?>
            </div>

            <!-- Táblázat -->
            <div class="va-le-table-wrap">
                <?php if ( $query->have_posts() ): ?>
                <table class="va-le-table">
                    <thead>
                    <tr>
                        <th class="va-le-col-img">Kép</th>
                        <th>Cím &amp; ID</th>
                        <th>Hirdető</th>
                        <th>Kategória</th>
                        <th>Ár</th>
                        <th>Státusz</th>
                        <th>👁</th>
                        <th>Feladva</th>
                        <th class="va-le-col-act">Műveletek</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php while ( $query->have_posts() ) : $query->the_post();
                        $pid    = get_the_ID();
                        $thumb  = get_the_post_thumbnail_url( $pid, [ 56, 56 ] );
                        $author = get_userdata( (int)get_post_field( 'post_author', $pid ) );
                        $pcats  = get_the_terms( $pid, 'va_category' );
                        $cat    = ( ! is_wp_error( $pcats ) && $pcats ) ? $pcats[0]->name : '—';
                        $price  = (float)get_post_meta( $pid, 'va_price', true );
                        $views  = (int)get_post_meta( $pid, 'va_views', true );
                        $st     = get_post_status();

                        $st_cfg = [
                            'publish' => [ 'Aktív',    '#4ade80', '#052e16' ],
                            'pending' => [ 'Függő',    '#fb923c', '#431407' ],
                            'draft'   => [ 'Vázlat',   '#94a3b8', '#1e293b' ],
                            'trash'   => [ 'Kukában',  '#f87171', '#1f0b0b' ],
                        ];
                        [$st_label, $st_color, $st_bg] = $st_cfg[$st] ?? ['—','#666','#111'];

                        $edit_url    = esc_url( admin_url( 'admin.php?page=vadaszapro-listing-edit&id=' . $pid ) );
                        $approve_url = esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=va_listing_approve&id=' . $pid ), 'va_listing_approve_' . $pid ) );
                        $delete_url  = esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=va_listing_delete&id='  . $pid ), 'va_listing_delete_'  . $pid ) );
                        $view_url    = esc_url( get_permalink( $pid ) );
                    ?>
                    <tr>
                        <td class="va-le-col-img">
                            <?php if ($thumb): ?>
                            <img src="<?php echo esc_url($thumb); ?>" class="va-le-thumb" loading="lazy">
                            <?php else: ?>
                            <div class="va-le-thumb-empty">📷</div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?php echo $edit_url; ?>" class="va-le-title-link"><?php the_title(); ?></a>
                            <div class="va-le-post-id">#<?php echo $pid; ?></div>
                        </td>
                        <td class="va-le-td-muted"><?php echo $author ? esc_html( $author->display_name ) : '—'; ?></td>
                        <td><span class="va-le-tag"><?php echo esc_html($cat); ?></span></td>
                        <td><?php echo $price > 0 ? '<strong class="va-le-price">' . number_format($price,0,',','&nbsp;') . ' Ft</strong>' : '<span class="va-le-muted">—</span>'; ?></td>
                        <td><span class="va-le-pill" style="color:<?php echo $st_color;?>;background:<?php echo $st_bg;?>"><?php echo esc_html($st_label); ?></span></td>
                        <td class="va-le-td-muted"><?php echo number_format($views,0,',','&nbsp;'); ?></td>
                        <td class="va-le-td-muted"><?php echo esc_html(get_the_date('m.d H:i')); ?></td>
                        <td>
                            <div class="va-le-actions">
                                <a href="<?php echo $edit_url; ?>" class="va-le-act" title="Szerkesztés">✏️</a>
                                <?php if ($st === 'pending'): ?>
                                <a href="<?php echo $approve_url; ?>" class="va-le-act va-le-act--ok" title="Jóváhagyás" onclick="return confirm('Közzéteszed?')">✅</a>
                                <?php endif; ?>
                                <a href="<?php echo $view_url; ?>" target="_blank" class="va-le-act" title="Megtekintés">🔗</a>
                                <a href="<?php echo $delete_url; ?>" class="va-le-act va-le-act--del" title="Törlés" onclick="return confirm('Biztosan törölni akarod ezt a hirdetést?')">🗑️</a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; wp_reset_postdata(); ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="va-le-empty">
                    <div class="va-le-empty-icon">📭</div>
                    <h3>Nincs találat</h3>
                    <p><?php echo $search ? 'A keresési feltételekre nincs találat.' : 'Még nincsenek hirdetések.'; ?></p>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=vadaszapro-listing-edit')); ?>" class="va-btn va-btn--primary">+ Új hirdetés</a>
                </div>
                <?php endif; ?>
            </div>

            <!-- Lapozó -->
            <?php if ($pages > 1): ?>
            <div class="va-le-pagination">
                <?php for ($p = 1; $p <= $pages; $p++):
                    $pu = add_query_arg(['page'=>'vadaszapro-listings','paged'=>$p,'va_status'=>$status_tab,'s'=>$search,'va_cat'=>$cat_filter], admin_url('admin.php'));
                ?>
                <a href="<?php echo esc_url($pu); ?>" class="va-le-page-btn<?php echo $p===$paged?' active':''; ?>"><?php echo $p; ?></a>
                <?php endfor; ?>
            </div>
            <?php endif; ?>

        </div><?php
    }

    /* ══════════════════════════════════════════════════════════
       SZERKESZTŐ / ÚJ OLDAL
    ══════════════════════════════════════════════════════════ */
    public static function render_edit(): void {
        if ( ! current_user_can( 'edit_posts' ) ) return;
        wp_enqueue_media();
        wp_enqueue_script( 'jquery-ui-sortable' );

        $post_id = (int)( $_GET['id'] ?? 0 );
        $post    = $post_id ? get_post( $post_id ) : null;
        $is_new  = ! $post;

        if ( $post && $post->post_type !== 'va_listing' ) {
            echo '<div class="notice notice-error"><p>Érvénytelen hirdetés azonosítója.</p></div>';
            return;
        }

        $get_meta = static fn($k) => $post ? (string)get_post_meta( $post_id, $k, true ) : '';

        // Galéria + borítókép
        $thumb_id    = $post ? (int)get_post_thumbnail_id( $post_id ) : 0;
        $gallery_raw = $post ? (string)get_post_meta( $post_id, 'va_gallery_ids', true ) : '';
        $gallery_ids = array_values( array_unique( array_filter( array_map( 'intval', $gallery_raw !== '' ? explode( ',', $gallery_raw ) : [] ) ) ) );
        if ( $thumb_id && ! in_array( $thumb_id, $gallery_ids, true ) ) {
            array_unshift( $gallery_ids, $thumb_id );
        }
        if ( ! $thumb_id && $gallery_ids ) {
            $thumb_id = $gallery_ids[0];
        }

        // Taxonómiák
        $categories   = get_terms([ 'taxonomy' => 'va_category', 'hide_empty' => false, 'number' => 200 ]);
        $counties     = get_terms([ 'taxonomy' => 'va_county',   'hide_empty' => false, 'number' => 100 ]);
        if ( is_wp_error($categories) ) $categories = [];
        if ( is_wp_error($counties) )   $counties   = [];

        $cur_cats   = $post ? wp_get_object_terms( $post_id, 'va_category', ['fields'=>'ids'] ) : [];
        $cur_county = $post ? wp_get_object_terms( $post_id, 'va_county',   ['fields'=>'ids'] ) : [];
        $cur_cat    = ( ! is_wp_error($cur_cats)   && $cur_cats   ) ? (int)$cur_cats[0]   : 0;
        $cur_cty    = ( ! is_wp_error($cur_county) && $cur_county ) ? (int)$cur_county[0] : 0;

        // Felhasználók (max 200)
        $users       = get_users([ 'number' => 200, 'orderby' => 'display_name', 'order' => 'ASC' ]);
        $post_author = $post ? (int)$post->post_author : get_current_user_id();
        $post_status = $post ? $post->post_status : 'draft';

        $price_types = [ 'fixed' => 'Fix ár', 'negotiable' => 'Alkudható', 'free' => 'Ingyenes', 'on_request' => 'Érdeklődjön' ];

        // Form Builder config – admin listing edit
        $fb_raw     = class_exists( 'VA_Form_Builder' ) ? VA_Form_Builder::get_fields( 'va_admin_listing_edit' ) : [];
        usort( $fb_raw, static fn( $a, $b ) => (int)( $a['order'] ?? 99 ) - (int)( $b['order'] ?? 99 ) );
        $fb = [];
        foreach ( $fb_raw as $ff ) { $fb[ $ff['key'] ] = $ff; }
        $fb_on  = static fn( $k )          => ! isset( $fb[ $k ] ) || ! empty( $fb[ $k ]['enabled'] );
        $fb_lbl = static fn( $k, $default ) => ( isset( $fb[ $k ]['label'] ) && $fb[ $k ]['label'] !== '' ) ? esc_html( $fb[ $k ]['label'] ) : $default;
        $fb_ph  = static fn( $k, $default ) => ( isset( $fb[ $k ]['placeholder'] ) && $fb[ $k ]['placeholder'] !== '' ) ? esc_attr( $fb[ $k ]['placeholder'] ) : $default;

        // Egyedi (custom_*) mezők gyűjtése
        $custom_fields = array_filter( $fb_raw, static fn( $ff ) => str_starts_with( (string)( $ff['key'] ?? '' ), 'custom_' ) && ! empty( $ff['enabled'] ) );
        ?>
        <div class="va-le-wrap">

            <?php if (isset($_GET['va_saved'])): ?>
            <div class="va-le-notice va-le-notice--success">✅ Hirdetés sikeresen mentve!</div>
            <?php elseif (isset($_GET['va_error'])): ?>
            <div class="va-le-notice va-le-notice--error">❌ Hiba történt a mentés során, próbáld újra.</div>
            <?php endif; ?>

            <!-- Szerkesztő fejléc -->
            <div class="va-le-edit-header">
                <div class="va-le-edit-back">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=vadaszapro-listings')); ?>" class="va-le-back-btn">← Hirdetések</a>
                    <h2><?php echo $is_new ? 'Új hirdetés' : 'Hirdetés szerkesztése <small>#' . $post_id . '</small>'; ?></h2>
                </div>
                <div class="va-le-edit-header-actions">
                    <?php if ($post): ?>
                    <a href="<?php echo esc_url(get_permalink($post_id)); ?>" target="_blank" class="va-btn va-btn--ghost">🔗 Megtekintés</a>
                    <?php endif; ?>
                    <button form="va-listing-form" type="submit" class="va-btn va-btn--primary">💾 Mentés</button>
                </div>
            </div>

            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="va-listing-form" class="va-le-edit-form">
                <input type="hidden" name="action" value="va_listing_save">
                <input type="hidden" name="va_post_id" value="<?php echo (int)$post_id; ?>">
                <?php wp_nonce_field('va_listing_save', 'va_listing_nonce'); ?>
                <input type="hidden" name="va_post_status" id="va_post_status" value="<?php echo esc_attr($post_status); ?>">

                <div class="va-le-edit-layout">

                    <!-- ━━ Fő tartalom ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ -->
                    <div class="va-le-edit-main">

                        <!-- Cím -->
                        <div class="va-le-card">
                            <input type="text" name="va_title" value="<?php echo esc_attr($post ? $post->post_title : ''); ?>"
                                   placeholder="Hirdetés címe…" class="va-le-title-input" required>
                        </div>

                        <!-- Leírás -->
                        <div class="va-le-card">
                            <div class="va-le-card-hdr">📝 Leírás</div>
                            <div id="va-admin-quill-editor"></div>
                            <textarea name="va_description" id="va-admin-desc-hidden" style="display:none"><?php echo esc_textarea( $post ? $post->post_content : '' ); ?></textarea>
                        </div>

                        <!-- Képek -->
                        <div class="va-le-card">
                            <div class="va-le-card-hdr" style="display:flex;align-items:center;justify-content:space-between;">
                                <span>
                                    <svg style="vertical-align:middle;margin-right:5px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                                    Képek
                                </span>
                                <span id="va-gal-count" style="font-size:11px;color:var(--va-muted);font-weight:400;"><?php echo $gallery_ids ? count($gallery_ids).' kép' : ''; ?></span>
                            </div>
                            <div class="va-gal-grid" id="va-gal-grid">
                                <?php foreach ( $gallery_ids as $gid ):
                                    $gurl = wp_get_attachment_image_url( $gid, 'thumbnail' );
                                    if ( ! $gurl ) continue;
                                    $is_cover = ( $gid === $thumb_id );
                                ?>
                                <div class="va-gal-item<?php echo $is_cover ? ' va-gal-item--cover' : ''; ?>" data-id="<?php echo (int)$gid; ?>">
                                    <img src="<?php echo esc_url($gurl); ?>" alt="">
                                    <div class="va-gal-over">
                                        <button type="button" class="va-gal-star" title="Borítókép">
                                            <svg viewBox="0 0 24 24" fill="currentColor" width="13" height="13"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                        </button>
                                        <button type="button" class="va-gal-del" title="Törlés">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" width="13" height="13"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                        </button>
                                    </div>
                                    <?php if ( $is_cover ): ?><div class="va-gal-badge">Borítókép</div><?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                                <button type="button" class="va-gal-add" id="va-gal-add">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" width="26" height="26"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                    <span>Képek hozzáadása</span>
                                </button>
                            </div>
                            <input type="hidden" name="va_thumbnail_id" id="va_thumbnail_id" value="<?php echo (int)$thumb_id; ?>">
                            <input type="hidden" name="va_gallery_ids"  id="va_gallery_ids"  value="<?php echo esc_attr( implode(',', $gallery_ids) ); ?>">
                            <p style="font-size:11px;color:rgba(255,255,255,.3);margin:4px 0 0;">Húzd a képeket az átrendezéshez &bull; ★ = borítókép beállítása</p>
                        </div>

                        <!-- Árazás -->
                        <?php if ( $fb_on('va_price') || $fb_on('va_price_type') ): ?>
                        <div class="va-le-card">
                            <div class="va-le-card-hdr">💰 Árazás</div>
                            <div class="va-le-field-grid">
                                <?php if ( $fb_on('va_price') ): ?>
                                <div class="va-le-field">
                                    <label class="va-le-lbl"><?php echo $fb_lbl('va_price', 'Ár (Ft)'); ?></label>
                                    <input type="number" name="va_price" min="0" value="<?php echo esc_attr($get_meta('va_price')); ?>"
                                           placeholder="<?php echo $fb_ph('va_price','0'); ?>" class="va-le-input">
                                </div>
                                <?php endif; ?>
                                <?php if ( $fb_on('va_price_type') ): ?>
                                <div class="va-le-field">
                                    <label class="va-le-lbl"><?php echo $fb_lbl('va_price_type', 'Árazás típusa'); ?></label>
                                    <select name="va_price_type" class="va-le-select">
                                        <?php foreach ($price_types as $v => $l): ?>
                                        <option value="<?php echo esc_attr($v); ?>" <?php selected($get_meta('va_price_type'), $v); ?>><?php echo esc_html($l); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Részletek -->
                        <?php if ( $fb_on('va_brand') || $fb_on('va_model') || $fb_on('va_caliber') || $fb_on('va_year') ): ?>
                        <div class="va-le-card">
                            <div class="va-le-card-hdr">🔍 Termék részletei</div>
                            <div class="va-le-field-grid">
                                <?php if ( $fb_on('va_brand') ): ?>
                                <div class="va-le-field">
                                    <label class="va-le-lbl"><?php echo $fb_lbl('va_brand', 'Márka / Gyártó'); ?></label>
                                    <input type="text" name="va_brand" value="<?php echo esc_attr($get_meta('va_brand')); ?>" class="va-le-input" placeholder="<?php echo $fb_ph('va_brand','pl. Browning'); ?>">
                                </div>
                                <?php endif; ?>
                                <?php if ( $fb_on('va_model') ): ?>
                                <div class="va-le-field">
                                    <label class="va-le-lbl"><?php echo $fb_lbl('va_model', 'Modell / Típus'); ?></label>
                                    <input type="text" name="va_model" value="<?php echo esc_attr($get_meta('va_model')); ?>" class="va-le-input" placeholder="<?php echo $fb_ph('va_model','pl. X-Bolt'); ?>">
                                </div>
                                <?php endif; ?>
                                <?php if ( $fb_on('va_caliber') ): ?>
                                <div class="va-le-field">
                                    <label class="va-le-lbl"><?php echo $fb_lbl('va_caliber', 'Kaliber'); ?></label>
                                    <input type="text" name="va_caliber" value="<?php echo esc_attr($get_meta('va_caliber')); ?>" class="va-le-input" placeholder="<?php echo $fb_ph('va_caliber','pl. .308 Win'); ?>">
                                </div>
                                <?php endif; ?>
                                <?php if ( $fb_on('va_year') ): ?>
                                <div class="va-le-field">
                                    <label class="va-le-lbl"><?php echo $fb_lbl('va_year', 'Gyártási év'); ?></label>
                                    <input type="number" name="va_year" min="1800" max="2099" value="<?php echo esc_attr($get_meta('va_year')); ?>" class="va-le-input" placeholder="<?php echo $fb_ph('va_year','pl. 2020'); ?>">
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Kapcsolat -->
                        <?php if ( $fb_on('va_phone') || $fb_on('va_location') || $fb_on('va_email_show') ): ?>
                        <div class="va-le-card">
                            <div class="va-le-card-hdr">📞 Kapcsolat</div>
                            <div class="va-le-field-grid">
                                <?php if ( $fb_on('va_phone') ): ?>
                                <div class="va-le-field">
                                    <label class="va-le-lbl"><?php echo $fb_lbl('va_phone', 'Telefonszám'); ?></label>
                                    <input type="tel" name="va_phone" value="<?php echo esc_attr($get_meta('va_phone')); ?>" class="va-le-input" placeholder="<?php echo $fb_ph('va_phone','+36 20 …'); ?>">
                                </div>
                                <?php endif; ?>
                                <?php if ( $fb_on('va_location') ): ?>
                                <div class="va-le-field">
                                    <label class="va-le-lbl"><?php echo $fb_lbl('va_location', 'Helység'); ?></label>
                                    <input type="text" name="va_location" value="<?php echo esc_attr($get_meta('va_location')); ?>" class="va-le-input" placeholder="<?php echo $fb_ph('va_location','pl. Budapest'); ?>">
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php if ( $fb_on('va_email_show') ): ?>
                            <label class="va-le-check-lbl">
                                <input type="checkbox" name="va_email_show" value="1" <?php checked($get_meta('va_email_show'), '1'); ?>>
                                <span><?php echo $fb_lbl('va_email_show', 'Email cím megjelenítése a hirdetésen'); ?></span>
                            </label>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <!-- Egyedi (custom_*) mezők -->
                        <?php if ( $custom_fields ): ?>
                        <div class="va-le-card">
                            <div class="va-le-card-hdr">➕ Egyéb mezők</div>
                            <div class="va-le-field-grid">
                            <?php foreach ( $custom_fields as $cff ):
                                $ck = esc_attr( (string)( $cff['key'] ?? '' ) );
                                $ct = (string)( $cff['type'] ?? 'text' );
                                $cl = esc_html( (string)( $cff['label'] ?? $ck ) );
                                $cp = esc_attr( (string)( $cff['placeholder'] ?? '' ) );
                            ?>
                            <div class="va-le-field">
                                <label class="va-le-lbl"><?php echo $cl; ?></label>
                                <?php if ( $ct === 'checkbox' ): ?>
                                <label class="va-le-check-lbl">
                                    <input type="checkbox" name="<?php echo $ck; ?>" value="1" <?php checked( (string)get_post_meta($post_id, $ck, true), '1' ); ?>>
                                    <span><?php echo $cl; ?></span>
                                </label>
                                <?php elseif ( $ct === 'textarea' ): ?>
                                <textarea name="<?php echo $ck; ?>" class="va-le-input" rows="3" placeholder="<?php echo $cp; ?>"><?php echo esc_textarea( (string)get_post_meta($post_id, $ck, true) ); ?></textarea>
                                <?php else: ?>
                                <input type="<?php echo esc_attr($ct); ?>" name="<?php echo $ck; ?>" value="<?php echo esc_attr( (string)get_post_meta($post_id, $ck, true) ); ?>" class="va-le-input" placeholder="<?php echo $cp; ?>">
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                    </div><!-- .va-le-edit-main -->

                    <!-- ━━ Oldalsáv ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ -->
                    <div class="va-le-edit-sidebar">

                        <!-- Közzététel -->
                        <div class="va-le-card">
                            <div class="va-le-card-hdr">🚀 Közzététel</div>
                            <div class="va-le-field">
                                <label class="va-le-lbl">Státusz</label>
                                <select class="va-le-select" onchange="document.getElementById('va_post_status').value=this.value">
                                    <option value="publish" <?php selected($post_status,'publish'); ?>>✅ Aktív (közzétett)</option>
                                    <option value="pending" <?php selected($post_status,'pending'); ?>>⏳ Jóváhagyásra vár</option>
                                    <option value="draft"   <?php selected($post_status,'draft');   ?>>📝 Vázlat</option>
                                </select>
                            </div>
                            <div class="va-le-field">
                                <label class="va-le-lbl">Hirdető felhasználó</label>
                                <select name="va_author" class="va-le-select">
                                    <?php foreach ($users as $u): ?>
                                    <option value="<?php echo (int)$u->ID; ?>" <?php selected($post_author, $u->ID); ?>><?php echo esc_html($u->display_name); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="va-le-publish-row">
                                <button type="button" class="va-btn va-btn--ghost va-btn--sm" onclick="document.getElementById('va_post_status').value='draft'; vaAdminDoSubmit(document.getElementById('va-listing-form'));">Mentés vázlatként</button>
                                <button type="submit" class="va-btn va-btn--primary va-btn--sm">💾 Mentés</button>
                            </div>
                        </div>

                        <!-- Kategória -->
                        <div class="va-le-card">
                            <div class="va-le-card-hdr">🗂️ Kategória</div>
                            <select name="va_category" class="va-le-select">
                                <option value="">— Nincs megadva —</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo (int)$cat->term_id; ?>" <?php selected($cur_cat, $cat->term_id); ?>><?php echo esc_html($cat->name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Megye -->
                        <div class="va-le-card">
                            <div class="va-le-card-hdr">📍 Megye</div>
                            <select name="va_county" class="va-le-select">
                                <option value="">— Nincs megadva —</option>
                                <?php foreach ($counties as $county): ?>
                                <option value="<?php echo (int)$county->term_id; ?>" <?php selected($cur_cty, $county->term_id); ?>><?php echo esc_html($county->name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Jelölések és beállítások -->
                        <?php if ( $fb_on('va_featured') || $fb_on('va_verified') || $fb_on('va_license_req') || $fb_on('va_expires') ): ?>
                        <div class="va-le-card">
                            <div class="va-le-card-hdr">🏷️ Jelölések</div>
                            <?php if ( $fb_on('va_featured') ): ?>
                            <label class="va-le-check-lbl">
                                <input type="checkbox" name="va_featured" value="1" <?php checked($get_meta('va_featured'), '1'); ?>>
                                <span><?php echo $fb_lbl('va_featured', '⭐ Kiemelt hirdetés'); ?></span>
                            </label>
                            <?php endif; ?>
                            <?php if ( $fb_on('va_verified') ): ?>
                            <label class="va-le-check-lbl">
                                <input type="checkbox" name="va_verified" value="1" <?php checked($get_meta('va_verified'), '1'); ?>>
                                <span><?php echo $fb_lbl('va_verified', '✅ Ellenőrzött hirdető'); ?></span>
                            </label>
                            <?php endif; ?>
                            <?php if ( $fb_on('va_license_req') ): ?>
                            <label class="va-le-check-lbl">
                                <input type="checkbox" name="va_license_req" value="1" <?php checked($get_meta('va_license_req'), '1'); ?>>
                                <span><?php echo $fb_lbl('va_license_req', '🔒 Fegyverengedély szükséges'); ?></span>
                            </label>
                            <?php endif; ?>
                            <?php if ( $fb_on('va_expires') ): ?>
                            <div class="va-le-field" style="margin-top:12px">
                                <label class="va-le-lbl"><?php echo $fb_lbl('va_expires', '⏰ Lejárat dátuma'); ?></label>
                                <input type="date" name="va_expires" value="<?php echo esc_attr($get_meta('va_expires')); ?>" class="va-le-input">
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <!-- Statisztika (meglévő hirdetésnél) -->
                        <?php if (!$is_new): ?>
                        <div class="va-le-card va-le-card--info">
                            <div class="va-le-card-hdr">📊 Statisztika</div>
                            <div class="va-le-stat-row"><span>Megtekintések</span><strong><?php echo number_format((int)$get_meta('va_views'),0,',','&nbsp;'); ?></strong></div>
                            <div class="va-le-stat-row"><span>Feladás dátuma</span><strong><?php echo esc_html(get_the_date('Y.m.d H:i', $post_id)); ?></strong></div>
                            <div class="va-le-stat-row"><span>Utolsó módosítás</span><strong><?php echo esc_html(get_the_modified_date('Y.m.d H:i', $post_id)); ?></strong></div>
                        </div>
                        <?php endif; ?>

                    </div><!-- .va-le-edit-sidebar -->
                </div><!-- .va-le-edit-layout -->
            </form>
        </div><!-- .va-le-wrap -->

        <script>
        /* ══ Quill admin init ══════════════════════════════ */
        (function(){
            var quillAdmin = window.quillAdmin = new Quill('#va-admin-quill-editor', {
                theme: 'snow',
                placeholder: 'Hirdetés leírása...',
                modules: {
                    toolbar: {
                        container: [
                            [{ header: [2, 3, false] }],
                            ['bold', 'italic', 'underline', 'strike'],
                            [{ list: 'ordered' }, { list: 'bullet' }],
                            ['blockquote'],
                            [{ align: [] }],
                            ['link', 'image'],
                            ['clean']
                        ],
                        handlers: {
                            image: function() {
                                if (quillAdmin.root.querySelectorAll('img').length >= 2) {
                                    alert('Maximum 2 kép engedélyezett a leírásban.');
                                    return;
                                }
                                var input = document.createElement('input');
                                input.setAttribute('type', 'file');
                                input.setAttribute('accept', 'image/jpeg,image/png,image/webp,image/gif');
                                input.style.cssText = 'position:fixed;top:-9999px;left:-9999px;opacity:0;';
                                document.body.appendChild(input);
                                input.addEventListener('change', function() {
                                    var file = input.files[0];
                                    document.body.removeChild(input);
                                    if (!file) return;
                                    var reader = new FileReader();
                                    reader.onload = function(e) {
                                        var range = quillAdmin.getSelection(true);
                                        quillAdmin.insertEmbed(range ? range.index : quillAdmin.getLength(), 'image', e.target.result);
                                        quillAdmin.setSelection((range ? range.index : 0) + 1);
                                    };
                                    reader.readAsDataURL(file);
                                });
                                input.click();
                            }
                        }
                    }
                }
            });
            var existing = document.getElementById('va-admin-desc-hidden');
            if (existing && existing.value.trim()) {
                quillAdmin.clipboard.dangerouslyPasteHTML(0, existing.value);
            }

            /* Kép resize */
            var activeImg = null, rHandle = document.createElement('div'), startX, startW;
            rHandle.style.cssText = 'position:absolute;width:12px;height:12px;background:#ff4444;border:2px solid #fff;border-radius:3px;cursor:se-resize;display:none;z-index:9999;box-shadow:0 0 4px rgba(0,0,0,.6);';
            document.body.appendChild(rHandle);
            function posH() { if (!activeImg) return; var r = activeImg.getBoundingClientRect(); rHandle.style.left=(r.right+window.scrollX-8)+'px'; rHandle.style.top=(r.bottom+window.scrollY-8)+'px'; }
            quillAdmin.root.addEventListener('click', function(e) {
                if (e.target.tagName==='IMG') { activeImg=e.target; if(!activeImg.style.width) activeImg.style.width=activeImg.offsetWidth+'px'; posH(); rHandle.style.display='block'; }
                else { rHandle.style.display='none'; activeImg=null; }
            });
            rHandle.addEventListener('mousedown', function(e) { e.preventDefault(); startX=e.clientX; startW=activeImg?activeImg.offsetWidth:100; document.addEventListener('mousemove',onM); document.addEventListener('mouseup',onU); });
            function onM(e) { if (!activeImg) return; var w=Math.max(40,startW+(e.clientX-startX)); activeImg.style.width=w+'px'; activeImg.style.height='auto'; posH(); }
            function onU() { document.removeEventListener('mousemove',onM); document.removeEventListener('mouseup',onU); }
            window.addEventListener('scroll',posH); window.addEventListener('resize',posH);
            document.addEventListener('click',function(e){ if(e.target!==activeImg&&e.target!==rHandle){rHandle.style.display='none';activeImg=null;} });

            /* Submit előtt: base64 képek feltöltése, majd form küldés */
            var _nonce  = '<?php echo esc_js( wp_create_nonce( 'va_upload_editor_image' ) ); ?>';
            var _ajax   = '<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>';
            var _postId = '<?php echo esc_js( (string) ( (int) ( $_GET['id'] ?? 0 ) ) ); ?>';

            window.vaAdminDoSubmit = function(form) {
                var imgs     = quillAdmin.root.querySelectorAll('img[src^="data:"]');
                var textarea = document.getElementById('va-admin-desc-hidden');
                if (!imgs.length) {
                    textarea.value = quillAdmin.root.innerHTML;
                    form.submit();
                    return;
                }
                var promises = Array.from(imgs).map(function(img) {
                    return fetch(_ajax, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({ action: 'va_upload_editor_image', nonce: _nonce, post_id: _postId, data_url: img.src })
                    })
                    .then(function(r) { return r.json(); })
                    .then(function(res) { if (res.success) img.src = res.data.url; })
                    .catch(function() { /* hálózati hiba: kép base64-ben marad */ });
                });
                Promise.all(promises)
                    .then(function() {
                        textarea.value = quillAdmin.root.innerHTML;
                        form.submit();
                    })
                    .catch(function() {
                        textarea.value = quillAdmin.root.innerHTML;
                        form.submit();
                    });
            };

            document.getElementById('va-listing-form').addEventListener('submit', function(e) {
                e.preventDefault();
                vaAdminDoSubmit(this);
            });
        })();

        jQuery(function($) {
            var mf,
                $grid      = $('#va-gal-grid'),
                $thumbIn   = $('#va_thumbnail_id'),
                $galIn     = $('#va_gallery_ids'),
                $count     = $('#va-gal-count');

            /* ── Segédek ─────────────── */
            function ids() {
                return $.map($grid.find('.va-gal-item'), function(el){ return parseInt($(el).data('id'), 10); });
            }
            function sync() {
                var list = ids();
                $galIn.val( list.join(',') );
                $count.text( list.length ? list.length + ' kép' : '' );
                // Ha nincs cover, az első lesz az
                if ( ! $grid.find('.va-gal-item--cover').length && $grid.find('.va-gal-item').length ) {
                    setCover( $grid.find('.va-gal-item').first() );
                }
            }
            function setCover($item) {
                $grid.find('.va-gal-item').removeClass('va-gal-item--cover');
                $grid.find('.va-gal-badge').remove();
                $item.addClass('va-gal-item--cover');
                $item.append('<div class="va-gal-badge">Borítókép</div>');
                $thumbIn.val( $item.data('id') );
            }
            function bindItem($item) {
                $item.find('.va-gal-star').on('click', function(e){
                    e.stopPropagation();
                    setCover($item);
                });
                $item.find('.va-gal-del').on('click', function(e){
                    e.stopPropagation();
                    var wasCover = $item.hasClass('va-gal-item--cover');
                    $item.remove();
                    if (wasCover) {
                        var $first = $grid.find('.va-gal-item').first();
                        if ($first.length) setCover($first);
                        else $thumbIn.val('0');
                    }
                    sync();
                });
            }

            /* ── Init: meglévő elemek ── */
            $grid.find('.va-gal-item').each(function(){ bindItem($(this)); });

            /* ── Sortable ────────────── */
            $grid.sortable({
                items: '.va-gal-item',
                tolerance: 'pointer',
                cursor: 'grabbing',
                placeholder: 'va-gal-ph',
                forcePlaceholderSize: true,
                stop: function(){ sync(); }
            });

            /* ── Médiatár ────────────── */
            $('#va-gal-add').on('click', function(){
                if (!window.wp || !wp.media) return;
                if (mf) { mf.open(); return; }
                mf = wp.media({
                    title: 'Képek kiválasztása',
                    multiple: 'add',
                    library: { type: 'image' },
                    button: { text: 'Hozzáadás a galériához' }
                });
                mf.on('select', function(){
                    var existing = ids();
                    mf.state().get('selection').each(function(att){
                        var d = att.toJSON();
                        if ( existing.indexOf(d.id) !== -1 ) return;
                        var url = (d.sizes && d.sizes.thumbnail) ? d.sizes.thumbnail.url : d.url;
                        var $el = $(
                            '<div class="va-gal-item" data-id="' + d.id + '">' +
                            '<img src="' + url + '" alt="">' +
                            '<div class="va-gal-over">' +
                                '<button type="button" class="va-gal-star" title="Borítókép"><svg viewBox="0 0 24 24" fill="currentColor" width="13" height="13"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg></button>' +
                                '<button type="button" class="va-gal-del" title="Törlés"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" width="13" height="13"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>' +
                            '</div>' +
                            '</div>'
                        );
                        $grid.find('#va-gal-add').before($el);
                        bindItem($el);
                    });
                    sync();
                });
                mf.open();
            });
        });
        </script>
        <?php
    }
}
