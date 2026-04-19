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

        // Kiemelt kép
        $thumbnail_id = (int)( $_POST['va_thumbnail_id'] ?? 0 );
        if ( $thumbnail_id > 0 ) {
            set_post_thumbnail( $post_id, $thumbnail_id );
        } elseif ( isset( $_POST['va_remove_thumbnail'] ) ) {
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
        wp_trash_post( $id );
        wp_safe_redirect( admin_url( 'admin.php?page=vadaszapro-listings&va_trashed=1' ) );
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

        $post_id = (int)( $_GET['id'] ?? 0 );
        $post    = $post_id ? get_post( $post_id ) : null;
        $is_new  = ! $post;

        if ( $post && $post->post_type !== 'va_listing' ) {
            echo '<div class="notice notice-error"><p>Érvénytelen hirdetés azonosítója.</p></div>';
            return;
        }

        $get_meta = static fn($k) => $post ? (string)get_post_meta( $post_id, $k, true ) : '';

        // Thumbnail
        $thumb_id  = $post ? (int)get_post_thumbnail_id( $post_id ) : 0;
        $thumb_url = $thumb_id ? wp_get_attachment_image_url( $thumb_id, 'medium' ) : '';

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
                            <textarea name="va_description" class="va-le-desc" rows="10"
                                      placeholder="Részletes leírás a hirdetett termékről…"><?php echo esc_textarea($post ? $post->post_content : ''); ?></textarea>
                        </div>

                        <!-- Kiemelt kép -->
                        <div class="va-le-card">
                            <div class="va-le-card-hdr">📷 Kiemelt kép</div>
                            <div class="va-le-img-area" id="va-img-area" style="cursor:pointer" title="Kép kiválasztása">
                                <?php if ($thumb_url): ?>
                                <img src="<?php echo esc_url($thumb_url); ?>" id="va-img-preview" class="va-le-img-preview">
                                <?php else: ?>
                                <div class="va-le-img-ph" id="va-img-ph"><span>📷</span><p>Kattints a kép kiválasztásához</p></div>
                                <?php endif; ?>
                            </div>
                            <input type="hidden" name="va_thumbnail_id" id="va_thumbnail_id" value="<?php echo (int)$thumb_id; ?>">
                            <div class="va-le-img-btns">
                                <button type="button" id="va-img-btn" class="va-btn va-btn--sm">📂 Kép kiválasztása</button>
                                <button type="button" id="va-img-rm" class="va-btn va-btn--sm va-btn--danger"<?php echo $thumb_id ? '' : ' style="display:none"'; ?>>✕ Eltávolítás</button>
                            </div>
                        </div>

                        <!-- Árazás -->
                        <div class="va-le-card">
                            <div class="va-le-card-hdr">💰 Árazás</div>
                            <div class="va-le-field-grid">
                                <div class="va-le-field">
                                    <label class="va-le-lbl">Ár (Ft)</label>
                                    <input type="number" name="va_price" min="0" value="<?php echo esc_attr($get_meta('va_price')); ?>"
                                           placeholder="0" class="va-le-input">
                                </div>
                                <div class="va-le-field">
                                    <label class="va-le-lbl">Árazás típusa</label>
                                    <select name="va_price_type" class="va-le-select">
                                        <?php foreach ($price_types as $v => $l): ?>
                                        <option value="<?php echo esc_attr($v); ?>" <?php selected($get_meta('va_price_type'), $v); ?>><?php echo esc_html($l); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Részletek -->
                        <div class="va-le-card">
                            <div class="va-le-card-hdr">🔍 Termék részletei</div>
                            <div class="va-le-field-grid">
                                <div class="va-le-field">
                                    <label class="va-le-lbl">Márka / Gyártó</label>
                                    <input type="text" name="va_brand" value="<?php echo esc_attr($get_meta('va_brand')); ?>" class="va-le-input" placeholder="pl. Browning">
                                </div>
                                <div class="va-le-field">
                                    <label class="va-le-lbl">Modell / Típus</label>
                                    <input type="text" name="va_model" value="<?php echo esc_attr($get_meta('va_model')); ?>" class="va-le-input" placeholder="pl. X-Bolt">
                                </div>
                                <div class="va-le-field">
                                    <label class="va-le-lbl">Kaliber</label>
                                    <input type="text" name="va_caliber" value="<?php echo esc_attr($get_meta('va_caliber')); ?>" class="va-le-input" placeholder="pl. .308 Win">
                                </div>
                                <div class="va-le-field">
                                    <label class="va-le-lbl">Gyártási év</label>
                                    <input type="number" name="va_year" min="1800" max="2026" value="<?php echo esc_attr($get_meta('va_year')); ?>" class="va-le-input" placeholder="pl. 2020">
                                </div>
                            </div>
                        </div>

                        <!-- Kapcsolat -->
                        <div class="va-le-card">
                            <div class="va-le-card-hdr">📞 Kapcsolat</div>
                            <div class="va-le-field-grid">
                                <div class="va-le-field">
                                    <label class="va-le-lbl">Telefonszám</label>
                                    <input type="tel" name="va_phone" value="<?php echo esc_attr($get_meta('va_phone')); ?>" class="va-le-input" placeholder="+36 20 …">
                                </div>
                                <div class="va-le-field">
                                    <label class="va-le-lbl">Helység</label>
                                    <input type="text" name="va_location" value="<?php echo esc_attr($get_meta('va_location')); ?>" class="va-le-input" placeholder="pl. Budapest">
                                </div>
                            </div>
                            <label class="va-le-check-lbl">
                                <input type="checkbox" name="va_email_show" value="1" <?php checked($get_meta('va_email_show'), '1'); ?>>
                                <span>Email cím megjelenítése a hirdetésen</span>
                            </label>
                        </div>

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
                                <button type="button" class="va-btn va-btn--ghost va-btn--sm" onclick="document.getElementById('va_post_status').value='draft';document.getElementById('va-listing-form').submit()">Mentés vázlatként</button>
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
                        <div class="va-le-card">
                            <div class="va-le-card-hdr">🏷️ Jelölések</div>
                            <label class="va-le-check-lbl">
                                <input type="checkbox" name="va_featured" value="1" <?php checked($get_meta('va_featured'), '1'); ?>>
                                <span>⭐ Kiemelt hirdetés</span>
                            </label>
                            <label class="va-le-check-lbl">
                                <input type="checkbox" name="va_verified" value="1" <?php checked($get_meta('va_verified'), '1'); ?>>
                                <span>✅ Ellenőrzött hirdető</span>
                            </label>
                            <label class="va-le-check-lbl">
                                <input type="checkbox" name="va_license_req" value="1" <?php checked($get_meta('va_license_req'), '1'); ?>>
                                <span>🔒 Fegyverengedély szükséges</span>
                            </label>
                            <div class="va-le-field" style="margin-top:12px">
                                <label class="va-le-lbl">⏰ Lejárat dátuma</label>
                                <input type="date" name="va_expires" value="<?php echo esc_attr($get_meta('va_expires')); ?>" class="va-le-input">
                            </div>
                        </div>

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
        (function () {
            var frame, imgArea = document.getElementById('va-img-area'),
                thumbInput = document.getElementById('va_thumbnail_id'),
                imgBtn = document.getElementById('va-img-btn'),
                rmBtn  = document.getElementById('va-img-rm');

            function openMedia(e) {
                if (e) e.preventDefault();
                if (!window.wp || !wp.media) return;
                if (frame) { frame.open(); return; }
                frame = wp.media({ title: 'Kép kiválasztása', multiple: false, library: { type: 'image' }, button: { text: 'Kiválaszt' } });
                frame.on('select', function () {
                    var att = frame.state().get('selection').first().toJSON();
                    thumbInput.value = att.id;
                    imgArea.innerHTML = '<img src="' + (att.sizes && att.sizes.medium ? att.sizes.medium.url : att.url) + '" id="va-img-preview" class="va-le-img-preview">';
                    if (rmBtn) rmBtn.style.display = '';
                });
                frame.open();
            }

            if (imgBtn)  imgBtn.addEventListener('click', openMedia);
            if (imgArea) imgArea.addEventListener('click', function(e){ if (!e.target.closest('#va-img-rm')) openMedia(e); });
            if (rmBtn)   rmBtn.addEventListener('click', function(e){
                e.preventDefault(); e.stopPropagation();
                thumbInput.value = '0';
                imgArea.innerHTML = '<div class="va-le-img-ph" id="va-img-ph"><span>📷</span><p>Kattints a kép kiválasztásához</p></div>';
                rmBtn.style.display = 'none';
            });
        })();
        </script>
        <?php
    }
}
