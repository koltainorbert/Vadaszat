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
    $f_brand_title    = get_option( 'va_hf_footer_brand_title', 'VadászApró' );
    $f_cat_title      = get_option( 'va_hf_footer_col_categories_title', 'Kategóriák' );
    $f_account_title  = get_option( 'va_hf_footer_col_account_title', 'Fiók' );
    $f_legal_title    = get_option( 'va_hf_footer_col_legal_title', 'Jogi információk' );
    $f_link_aszf      = get_option( 'va_hf_footer_link_aszf', 'ÁSZF' );
    $f_link_privacy   = get_option( 'va_hf_footer_link_privacy', 'Adatvédelmi nyilatkozat' );
    $f_link_contact   = get_option( 'va_hf_footer_link_contact', 'Kapcsolat' );
    $f_link_help      = get_option( 'va_hf_footer_link_help', 'Súgó' );
    $f_copy_text      = get_option( 'va_hf_footer_copy_text', 'VadászApró – Minden jog fenntartva.' );
    $f_privacy_bottom = get_option( 'va_hf_footer_privacy_text', 'Adatvédelem' );
    ?>

    <!-- ═══ Footer ═══════════════════════════════════════ -->
    <footer class="va-footer">
        <div class="va-footer__grid">
            <div>
                <div class="va-footer__col-title"><?php echo esc_html( $f_brand_title ); ?></div>
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
                $fp = [
                    'va-bejelentkezes' => 'Bejelentkezés',
                    'va-regisztracio'  => 'Regisztráció',
                    'va-fiok'          => 'Fiókom',
                    'va-hirdetes-feladas' => 'Hirdetés feladása',
                ];
                foreach ($fp as $slug => $label) {
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
</body>
</html>
