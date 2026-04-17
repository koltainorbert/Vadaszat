            </div><!-- .va-main-content -->

            <!-- Jobb oldalsáv -->
            <aside class="va-sidebar va-sidebar--right">
                <?php if ( class_exists('VA_Ad_Zones') ) VA_Ad_Zones::render('sidebar_right'); ?>

                <!-- Gyors linkek widget -->
                <div class="va-sidebar__widget">
                    <div class="va-sidebar__widget-title">Gyors elérés</div>
                    <?php
                    $quick_links = [
                        'va-hirdetes-feladas' => '📤 Hirdetés feladása',
                        'va-aukciok'          => '🔨 Aukciók',
                        'va-fiok'             => '👤 Fiókom',
                    ];
                    foreach ($quick_links as $slug => $label) {
                        $page = get_page_by_path($slug);
                        if ($page) {
                            echo '<a href="' . esc_url(get_permalink($page)) . '" style="display:block;padding:8px 0;color:rgba(255,255,255,0.7);text-decoration:none;font-size:13px;border-bottom:1px solid rgba(255,255,255,0.05);">' . esc_html($label) . '</a>';
                        }
                    }
                    ?>
                </div>
            </aside>

        </div><!-- .va-content-layout -->
    </main><!-- .va-container -->

    <!-- ═══ Footer reklám ════════════════════════════════ -->
    <?php if ( class_exists('VA_Ad_Zones') ) VA_Ad_Zones::render('footer_top'); ?>

    <!-- ═══ Footer ═══════════════════════════════════════ -->
    <footer class="va-footer">
        <div class="va-footer__grid">
            <div>
                <div class="va-footer__col-title">VadászApró</div>
                <p style="font-size:12px;color:rgba(255,255,255,0.4);line-height:1.6;"><?php echo esc_html(get_option('va_site_description', 'Magyarország vadászati apróhirdetési oldala')); ?></p>
            </div>
            <div>
                <div class="va-footer__col-title">Kategóriák</div>
                <?php $cats = get_terms(['taxonomy' => 'va_category', 'parent' => 0, 'hide_empty' => false, 'number' => 6]);
                foreach ($cats as $cat): ?>
                    <a href="<?php echo esc_url(get_term_link($cat)); ?>" class="va-footer__link"><?php echo esc_html($cat->name); ?></a>
                <?php endforeach; ?>
            </div>
            <div>
                <div class="va-footer__col-title">Fiók</div>
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
                <div class="va-footer__col-title">Jogi információk</div>
                <a href="<?php echo esc_url(home_url('/aszf')); ?>"          class="va-footer__link">ÁSZF</a>
                <a href="<?php echo esc_url(home_url('/adatvedelmi-nyilatkozat')); ?>" class="va-footer__link">Adatvédelmi nyilatkozat</a>
                <a href="<?php echo esc_url(home_url('/kapcsolat')); ?>"     class="va-footer__link">Kapcsolat</a>
                <a href="<?php echo esc_url(home_url('/sugo')); ?>"          class="va-footer__link">Súgó</a>
            </div>
        </div>
        <div class="va-footer__bottom">
            &copy; <?php echo date('Y'); ?> VadászApró – Minden jog fenntartva. |
            <a href="<?php echo esc_url(home_url('/adatvedelmi-nyilatkozat')); ?>">Adatvédelem</a>
        </div>
    </footer>

</div><!-- .va-site-wrap -->

<script>
// Hamburger menü
document.getElementById('va-hamburger').addEventListener('click', function(){
    document.getElementById('va-main-nav').classList.toggle('open');
});
</script>

<?php wp_footer(); ?>
</body>
</html>
