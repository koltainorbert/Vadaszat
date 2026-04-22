            </div><!-- .va-main-content -->

            <!-- Jobb oldalsáv -->
            <aside class="va-sidebar va-sidebar--right">
                <?php if ( class_exists('VA_Ad_Zones') ) VA_Ad_Zones::render('sidebar_right'); ?>
            </aside>

        </div><!-- .va-content-layout -->
    </main><!-- .va-container -->

    <!-- ═══ Footer reklám ════════════════════════════════ -->
    <?php if ( class_exists('VA_Ad_Zones') ) VA_Ad_Zones::render('footer_top'); ?>

    <?php
    $f_brand_title    = trim( (string) get_option( 'va_hf_footer_brand_title', 'VadászApró' ) );
    $f_cat_title      = trim( (string) get_option( 'va_hf_footer_col_categories_title', 'Kategóriák' ) );
    $f_account_title  = trim( (string) get_option( 'va_hf_footer_col_account_title', 'Fiók' ) );
    $f_legal_title    = trim( (string) get_option( 'va_hf_footer_col_legal_title', 'Jogi információk' ) );
    $f_link_aszf      = trim( (string) get_option( 'va_hf_footer_link_aszf', 'ÁSZF' ) );
    $f_link_privacy   = trim( (string) get_option( 'va_hf_footer_link_privacy', 'Adatvédelmi nyilatkozat' ) );
    $f_link_contact   = trim( (string) get_option( 'va_hf_footer_link_contact', 'Kapcsolat' ) );
    $f_link_help      = trim( (string) get_option( 'va_hf_footer_link_help', 'Súgó' ) );
    $f_copy_text      = trim( (string) get_option( 'va_hf_footer_copy_text', 'VadászApró – Minden jog fenntartva.' ) );
    $f_privacy_bottom = trim( (string) get_option( 'va_hf_footer_privacy_text', 'Adatvédelem' ) );
    $f_logo_url       = trim( (string) get_option( 'va_hf_footer_logo_url', '' ) );
    $f_logo_height    = max( 20, min( 180, absint( get_option( 'va_hf_footer_logo_height', 48 ) ) ) );

    if ( $f_brand_title === '' )    $f_brand_title = 'VadászApró';
    if ( $f_cat_title === '' )      $f_cat_title = 'Kategóriák';
    if ( $f_account_title === '' )  $f_account_title = 'Fiók';
    if ( $f_legal_title === '' )    $f_legal_title = 'Jogi információk';
    if ( $f_link_aszf === '' )      $f_link_aszf = 'ÁSZF';
    if ( $f_link_privacy === '' )   $f_link_privacy = 'Adatvédelmi nyilatkozat';
    if ( $f_link_contact === '' )   $f_link_contact = 'Kapcsolat';
    if ( $f_link_help === '' )      $f_link_help = 'Súgó';
    if ( $f_copy_text === '' )      $f_copy_text = 'VadászApró – Minden jog fenntartva.';
    if ( $f_privacy_bottom === '' ) $f_privacy_bottom = 'Adatvédelem';
    ?>

    <!-- ═══ Footer ═══════════════════════════════════════ -->
    <footer class="va-footer">
        <div class="va-footer__grid">
            <div>
                <div class="va-footer__col-title"><?php echo esc_html( $f_brand_title ); ?></div>
                <?php if ( $f_logo_url !== '' ): ?>
                    <img src="<?php echo esc_url( $f_logo_url ); ?>" class="va-footer__brand-logo" style="height:<?php echo esc_attr( $f_logo_height ); ?>px;" alt="<?php echo esc_attr( $f_brand_title ); ?>" loading="lazy" decoding="async">
                <?php endif; ?>
                <p style="font-size:12px;color:rgba(255,255,255,0.4);line-height:1.6;"><?php echo esc_html(get_option('va_site_description', 'Magyarország vadászati apróhirdetési oldala')); ?></p>
            </div>
            <div>
                <div class="va-footer__col-title"><?php echo esc_html( $f_cat_title ); ?></div>
                <?php $cats = get_terms(['taxonomy' => 'va_category', 'parent' => 0, 'hide_empty' => false, 'number' => 6]);
                foreach ($cats as $cat): ?>
                    <a href="<?php echo esc_url(get_term_link($cat)); ?>" class="va-footer__link"><?php echo esc_html($cat->name); ?></a>
                <?php endforeach; ?>
            </div>
            <div>
                <div class="va-footer__col-title"><?php echo esc_html( $f_account_title ); ?></div>
                <?php
                $login_enabled = get_option( 'va_enable_login', '1' ) === '1';
                $register_enabled = get_option( 'va_enable_register', '1' ) === '1';
                $fp = [
                    'va-bejelentkezes' => 'Bejelentkezés',
                    'va-regisztracio'  => 'Regisztráció',
                    'va-fiok'          => 'Fiókom',
                    'va-hirdetes-feladas' => 'Hirdetés feladása',
                ];
                foreach ($fp as $slug => $label) {
                    if ( $slug === 'va-bejelentkezes' && ! $login_enabled ) {
                        continue;
                    }
                    if ( $slug === 'va-regisztracio' && ! $register_enabled ) {
                        continue;
                    }
                    $p = get_page_by_path($slug);
                    if ($p) echo '<a href="' . esc_url(get_permalink($p)) . '" class="va-footer__link">' . esc_html($label) . '</a>';
                }
                ?>
            </div>
            <div>
                <div class="va-footer__col-title"><?php echo esc_html( $f_legal_title ); ?></div>
                <a href="<?php echo esc_url(home_url('/aszf')); ?>"          class="va-footer__link"><?php echo esc_html( $f_link_aszf ); ?></a>
                <a href="<?php echo esc_url(home_url('/adatvedelmi-nyilatkozat')); ?>" class="va-footer__link"><?php echo esc_html( $f_link_privacy ); ?></a>
                <a href="<?php echo esc_url(home_url('/kapcsolat')); ?>"     class="va-footer__link"><?php echo esc_html( $f_link_contact ); ?></a>
                <a href="<?php echo esc_url(home_url('/sugo')); ?>"          class="va-footer__link"><?php echo esc_html( $f_link_help ); ?></a>
            </div>
        </div>
        <div class="va-footer__bottom">
            <?php if ( get_option('va_social_footer_show','1') === '1' && function_exists('va_social_bar') ):
                $ftr_style = get_option('va_social_footer_style','icons');
                $ftr_size  = max(14, min(28, absint( get_option('va_social_icon_size', 20) )));
                echo va_social_bar( $ftr_style, $ftr_size );
            endif; ?>
            &copy; <?php echo date('Y'); ?> <?php echo esc_html( $f_copy_text ); ?> |
            <a href="<?php echo esc_url(home_url('/adatvedelmi-nyilatkozat')); ?>"><?php echo esc_html( $f_privacy_bottom ); ?></a>
        </div>
    </footer>

    <button class="va-scrolltop" id="va-scrolltop" type="button" aria-label="Ugrás az oldal tetejére">
        <svg class="va-scrolltop__track" viewBox="0 0 62 62" aria-hidden="true">
            <circle cx="31" cy="31" r="27"></circle>
        </svg>
        <svg class="va-scrolltop__ring" viewBox="0 0 62 62" aria-hidden="true">
            <circle id="va-scrolltop-ring" cx="31" cy="31" r="27"></circle>
        </svg>
        <span class="va-scrolltop__core" aria-hidden="true">
            <span class="va-scrolltop__arrow"></span>
        </span>
    </button>

</div><!-- .va-site-wrap -->

<script>
(function(){
    // Hamburger + scroll-aware header
    var hdr  = document.querySelector('.va-header');
    var hbtn = document.getElementById('va-hamburger');
    var nav  = document.getElementById('va-main-nav');
    var scrollTopBtn = document.getElementById('va-scrolltop');
    var scrollTopRing = document.getElementById('va-scrolltop-ring');
    var ringLength = 169.65;

    // Scroll: header glass-effect bekapcsol
    function onScroll(){
        if( window.scrollY > 40 ) hdr.classList.add('scrolled');
        else hdr.classList.remove('scrolled');

        if (scrollTopBtn && scrollTopRing) {
            var top = window.pageYOffset || document.documentElement.scrollTop || 0;
            var height = document.documentElement.scrollHeight - window.innerHeight;
            var pct = height > 0 ? (top / height) : 0;
            if (pct < 0) pct = 0;
            if (pct > 1) pct = 1;

            scrollTopBtn.classList.toggle('is-visible', top > 220);
            scrollTopRing.style.strokeDashoffset = String(ringLength * (1 - pct));
        }
    }
    window.addEventListener('scroll', onScroll, {passive:true});
    window.addEventListener('resize', onScroll);
    onScroll();

    if (scrollTopBtn) {
        scrollTopBtn.addEventListener('click', function(){
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // Hamburger toggle
    if(hbtn && nav){
        hbtn.addEventListener('click', function(){
            var open = nav.classList.toggle('open');
            hbtn.classList.toggle('open', open);
            document.body.style.overflow = open ? 'hidden' : '';
        });
        // Kattint\u00e1s nav-on k\u00edv\u00fcl z\u00e1rja
        document.addEventListener('click', function(e){
            if(nav.classList.contains('open') && !nav.contains(e.target) && e.target !== hbtn && !hbtn.contains(e.target)){
                nav.classList.remove('open');
                hbtn.classList.remove('open');
                document.body.style.overflow = '';
            }
        });
    }

    // Aktiv nav item
    var cur = location.pathname;
    document.querySelectorAll('.va-nav__item').forEach(function(a){
        if(a.getAttribute('href') && cur.indexOf(a.getAttribute('href')) === 0 && a.getAttribute('href') !== '/'){
            a.classList.add('active');
        }
    });
})();
</script>

<?php wp_footer(); ?>

<?php
/* ── Back-to-top gomb ──────────────────────────────────────── */
if ( get_option( 'va_btt_enabled', '1' ) === '1' ) :
    $btt_style    = sanitize_key( (string) get_option( 'va_btt_style',        'circle' ) );
    $btt_icon_cls = strip_tags( trim( (string) get_option( 'va_btt_icon',     'fa-solid fa-chevron-up' ) ) );
    $btt_color    = va_design_css_color( (string) get_option( 'va_btt_color',         '#ff0000' ), '#ff0000' );
    $btt_border   = va_design_css_color( (string) get_option( 'va_btt_border_color',  '#ff0000' ), '#ff0000' );
    $btt_txtcolor = va_design_css_color( (string) get_option( 'va_btt_text_color',    '#ffffff' ), '#ffffff' );
    $btt_size     = max( 32, min( 80, absint( get_option( 'va_btt_size',       48 ) ) ) );
    $btt_pos      = get_option( 'va_btt_position', 'right' ) === 'left' ? 'left' : 'right';
    $btt_ox       = max( 0, min( 120, absint( get_option( 'va_btt_offset_x',  28 ) ) ) );
    $btt_oy       = max( 0, min( 120, absint( get_option( 'va_btt_offset_y',  28 ) ) ) );
    $btt_after    = max( 0, absint( get_option( 'va_btt_show_after', 300 ) ) );

    // FA ikon osztály whitelist: csak betű, szám, - és szóköz engedélyezett
    $btt_icon_cls = preg_replace( '/[^a-zA-Z0-9\- ]/', '', $btt_icon_cls );

    // Stílus-specifikus CSS
    $btt_extra_css = [
        'circle'   => 'border-radius:50%;border:2px solid ' . $btt_border . ';',
        'rounded'  => 'border-radius:14px;border:2px solid ' . $btt_border . ';',
        'square'   => 'border-radius:4px;border:2px solid ' . $btt_border . ';',
        'pill'     => 'border-radius:999px;width:auto;padding:0 20px;border:2px solid ' . $btt_border . ';',
        'ghost'    => 'border-radius:50%;background:transparent !important;border:2px solid ' . $btt_border . ';color:' . $btt_txtcolor . ' !important;',
        'glass'    => 'border-radius:50%;background:rgba(255,255,255,.12) !important;backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);border:1px solid ' . $btt_border . ';',
        'neon'     => 'border-radius:50%;border:2px solid ' . $btt_border . ';box-shadow:0 0 18px ' . $btt_color . ',0 0 36px ' . $btt_color . '88;',
        'minimal'  => 'border-radius:50%;background:transparent !important;border:none;box-shadow:none !important;color:' . $btt_txtcolor . ' !important;',
        'floating' => 'border-radius:50%;border:2px solid ' . $btt_border . ';box-shadow:0 8px 32px rgba(0,0,0,.45),0 0 16px ' . $btt_color . '55;',
        'arrow'    => 'border-radius:0 0 50% 50% / 0 0 20px 20px;border:none;border-top:3px solid ' . $btt_border . ';',
    ];
    $extra = $btt_extra_css[ $btt_style ] ?? $btt_extra_css['circle'];
    ?>
    <button id="va-btt" aria-label="Vissza a tetejére"
        style="position:fixed;<?php echo $btt_pos; ?>:<?php echo $btt_ox; ?>px;bottom:<?php echo $btt_oy; ?>px;
               width:<?php echo $btt_size; ?>px;height:<?php echo $btt_size; ?>px;
               background:<?php echo $btt_color; ?>;color:<?php echo $btt_txtcolor; ?>;
               cursor:pointer;display:none;align-items:center;justify-content:center;
               z-index:9999;transition:opacity .25s,transform .25s;opacity:0;
               font-size:<?php echo round($btt_size*0.4); ?>px;line-height:1;
               <?php echo $extra; ?>">
        <i class="<?php echo esc_attr( $btt_icon_cls ); ?>" aria-hidden="true"></i>
    </button>
    <script>
    (function(){
        var btn = document.getElementById('va-btt');
        if(!btn) return;
        var after = <?php echo $btt_after; ?>;
        window.addEventListener('scroll', function(){
            if(window.scrollY > after){
                btn.style.display='flex';
                setTimeout(function(){ btn.style.opacity='1'; btn.style.transform='translateY(0)'; },10);
            } else {
                btn.style.opacity='0';
                btn.style.transform='translateY(10px)';
                setTimeout(function(){ if(window.scrollY<=after) btn.style.display='none'; },260);
            }
        }, {passive:true});
        btn.addEventListener('click', function(){
            window.scrollTo({top:0, behavior:'smooth'});
        });
        btn.addEventListener('mouseenter', function(){ btn.style.transform='scale(1.1)'; });
        btn.addEventListener('mouseleave', function(){ btn.style.transform='scale(1)'; });
    })();
    </script>
<?php endif; ?>
</body>
</html>
