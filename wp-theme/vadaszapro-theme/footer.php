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
    $f_contact_email  = trim( (string) get_option( 'va_contact_email', 'weingartnertrans@gmail.com' ) );
    $f_contact_phone  = trim( (string) get_option( 'va_billing_phone', '+36 20 943 8636' ) );
    $f_contact_addr   = trim( (string) get_option( 'va_billing_company_address', '8412 Veszprém, Alsó-Újsor utca 31.' ) );
    $f_contact_phone_href = preg_replace( '/[^0-9\+]/', '', $f_contact_phone );

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
    if ( $f_contact_email === '' )  $f_contact_email = 'weingartnertrans@gmail.com';
    if ( $f_contact_phone === '' )  $f_contact_phone = '+36 20 943 8636';
    if ( $f_contact_addr === '' )   $f_contact_addr = '8412 Veszprém, Alsó-Újsor utca 31.';
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
                <div class="va-footer__col-title" style="margin-top:12px;">Kapcsolat</div>
                <a href="mailto:<?php echo esc_attr( $f_contact_email ); ?>" class="va-footer__link"><?php echo esc_html( $f_contact_email ); ?></a>
                <a href="tel:<?php echo esc_attr( $f_contact_phone_href ); ?>" class="va-footer__link"><?php echo esc_html( $f_contact_phone ); ?></a>
                <div class="va-footer__link" style="line-height:1.55;"><?php echo esc_html( $f_contact_addr ); ?></div>
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


</div><!-- .va-site-wrap -->

<script>
(function(){
    // Hamburger + scroll-aware header
    var hdr  = document.querySelector('.va-header');
    var hbtn = document.getElementById('va-hamburger');
    var nav  = document.getElementById('va-main-nav');

    // Scroll: header glass-effect bekapcsol
    function onScroll(){
        if( window.scrollY > 40 ) hdr.classList.add('scrolled');
        else hdr.classList.remove('scrolled');
    }
    window.addEventListener('scroll', onScroll, {passive:true});
    window.addEventListener('resize', onScroll);
    onScroll();

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
$va_scroll_ring_video_url = trim( (string) get_option( 'va_scroll_ring_video_url', content_url( 'uploads/2026/04/0_Ride_Street_1920x1080.mp4' ) ) );
if ( $va_scroll_ring_video_url === '' ) {
    $va_scroll_ring_video_url = content_url( 'uploads/2026/04/0_Ride_Street_1920x1080.mp4' );
}
$va_scroll_ring_border_color = trim( (string) get_option( 'va_scroll_ring_border_color', '#00e676' ) );
if ( $va_scroll_ring_border_color === '' ) {
    $va_scroll_ring_border_color = '#00e676';
}
?>

<!-- ── Scroll-progress pill videó widget ──────────────────── -->
<div id="va-scroll-ring" role="button" aria-label="Vissza a tetejére" tabindex="0" style="--va-scroll-ring-color: <?php echo esc_attr( $va_scroll_ring_border_color ); ?>;">    <!-- progress border SVG (pill alak) – pathLength=100 → nincs kerület-hiba -->
    <svg id="va-ring-svg" viewBox="0 0 178 66" width="178" height="66" aria-hidden="true" style="position:absolute;top:0;left:0;pointer-events:none;z-index:3;">
        <rect x="2" y="2" width="174" height="62" rx="31" fill="none" stroke="rgba(255,255,255,0.13)" stroke-width="1.5" pathLength="100"/>
        <rect id="va-ring-el" x="2" y="2" width="174" height="62" rx="31" fill="none"
            stroke="var(--va-scroll-ring-color)" stroke-width="1.8" stroke-linecap="round"
            pathLength="100" stroke-dasharray="100" stroke-dashoffset="100"
            transform="rotate(180 89 33)"
            style="transition:stroke-dashoffset .12s linear;"/>
    </svg>
    <!-- videó + bal arrow réteg -->
    <div id="va-ring-inner">
        <video autoplay muted loop playsinline preload="auto" aria-hidden="true">
            <source src="<?php echo esc_url( $va_scroll_ring_video_url ); ?>" type="video/mp4">
        </video>
        <!-- bal oldali sötét átmenet + nyil -->
        <div id="va-ring-arrow">
            <div class="va-arr">
                <svg viewBox="0 0 32 20" fill="none" stroke="#fff" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" width="32" height="20"><polyline points="4 16 16 4 28 16"/></svg>
            </div>
        </div>
    </div>
</div>

<style>
#va-scroll-ring {
    position: fixed;
    right: 18px;
    bottom: 18px;
    width: 178px;
    height: 66px;
    z-index: 9999;
    cursor: pointer;
    opacity: 0;
    transform: translateY(14px);
    pointer-events: none;
    transition: opacity .3s, transform .3s;
    -webkit-tap-highlight-color: transparent;
    user-select: none;
}
#va-scroll-ring.va-ring--visible {
    opacity: 1;
    transform: translateY(0);
    pointer-events: auto;
}
#va-scroll-ring:hover #va-ring-el { stroke: var(--va-scroll-ring-color); }
#va-scroll-ring:hover #va-ring-inner { transform: scale(1.03); }

#va-ring-inner {
    position: absolute;
    top: 4px; left: 4px; right: 4px; bottom: 4px;
    border-radius: 28px;
    overflow: hidden;
    transition: transform .2s;
}
#va-ring-inner video {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
#va-ring-arrow {
    position: absolute;
    top: 0; left: 0;
    width: 62px;
    height: 100%;
    background: linear-gradient(to right, rgba(0,0,0,.78) 50%, transparent);
    display: flex;
    align-items: center;
    justify-content: center;
}
.va-arr {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0px;
    animation: va-arr-bounce 2.8s ease-in-out infinite;
}
.va-arr svg:first-child { animation: va-arr-fade 2.8s ease-in-out infinite; }
@keyframes va-arr-bounce {
    0%,100% { transform: translateY(2px); }
    50%      { transform: translateY(-4px); }
}
@keyframes va-arr-fade {
    0%,100% { opacity: 1; }
    50%      { opacity: .5; }
}
</style>
<script>
(function(){
    var ring  = document.getElementById('va-scroll-ring');
    var el    = document.getElementById('va-ring-el');
    var perim = 100;
    function update() {
        var doc     = document.documentElement;
        var scrollH = doc.scrollHeight - doc.clientHeight;
        var pct     = scrollH > 0 ? window.scrollY / scrollH : 0;
        el.style.strokeDashoffset = perim * (1 - pct);
        ring.classList.toggle('va-ring--visible', window.scrollY > 80);
    }
    window.addEventListener('scroll', update, {passive:true});
    ring.addEventListener('click', function(){ window.scrollTo({top:0, behavior:'smooth'}); });
    ring.addEventListener('keydown', function(e){
        if(e.key==='Enter'||e.key===' '){ window.scrollTo({top:0,behavior:'smooth'}); }
    });
    update();
})();
</script>
</body>
</html>
