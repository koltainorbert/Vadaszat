# Patch: single-va_listing.php jobb oldali szekció cseréje
$files = @(
    "d:\Vadaszat2026\wp-theme\vadaszapro-theme\single-va_listing.php",
    "d:\Vadaszat2026\single-va_listing.php"
)

$newSection = @'
        <!-- JOBB: fejlec + adatok + kontakt -->
        <div class="sl__right">

            <!-- Fejlec: kategoria + cim + ar -->
            <div class="sl__card sl__head">
                <?php if ( $categories && !is_wp_error($categories) ): ?>
                    <a href="<?php echo esc_url(get_term_link($categories[0])); ?>" class="sl__cat-pill">
                        <?php echo esc_html($categories[0]->name); ?>
                    </a>
                <?php endif; ?>
                <h1 class="sl__title"><?php the_title(); ?></h1>
                <div class="sl__price"><?php echo esc_html( va_format_price($price, $price_type) ); ?></div>

                <?php if ( $demand_count >= 2 ): ?>
                <div class="sl__demand">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" aria-hidden="true"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
                    <?php echo esc_html( sprintf( 'A(z) %d érdeklődő az elmúlt 24 órában figyelőlistájára vette', $demand_count ) ); ?>
                </div>
                <?php endif; ?>

                <div class="sl__meta-row">
                    <?php if ( $county && !is_wp_error($county) ): ?>
                        <span>&#128205; <?php echo esc_html($county[0]->name); ?></span>
                    <?php endif; ?>
                    <?php if ( $location ): ?>
                        <span><?php echo esc_html($location); ?></span>
                    <?php endif; ?>
                    <?php if ( $condition && !is_wp_error($condition) ): ?>
                        <span>&#193;llapot: <?php echo esc_html($condition[0]->name); ?></span>
                    <?php endif; ?>
                    <span class="sl__views">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="15" height="15" style="vertical-align:-2px;margin-right:4px;"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg><?php echo esc_html( number_format($views, 0, ',', ' ') ); ?> megtekint&#233;s
                    </span>
                    <span>Feladva: <?php echo esc_html(get_the_date('Y. m. d.')); ?></span>
                    <?php if ( $expires && strtotime($expires) > time() ): ?>
                        <span>Lej&#225;r: <?php echo esc_html($expires); ?></span>
                    <?php elseif ( $expires && strtotime($expires) <= time() ): ?>
                        <span class="sl__expired">Lej&#225;rt: <?php echo esc_html($expires); ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Adatok / Specifications -->
            <?php
            $specs = [];
            if ( $site_type === 'jarmu' ) {
                if ( $brand )          $specs[] = [ 'Gy&#225;rt&#243;',               $brand,       true ];
                if ( $model )          $specs[] = [ 'Modell',               $model,       true ];
                if ( $year )           $specs[] = [ '&#201;vj&#225;rat',              $year,        false ];
                if ( $first_reg )      $specs[] = [ 'Els&#337; forgalomba hely.',$first_reg,   false ];
                if ( $mileage )        $specs[] = [ 'Kilom&#233;ter&#243;ra',         number_format((int)$mileage,0,',',' ').' km', false ];
                if ( $fuel_type )      $specs[] = [ '&#220;zemanyag',            $fuel_labels[$fuel_type] ?? $fuel_type, false ];
                if ( $performance_kw ) $specs[] = [ 'Teljes&#237;tm&#233;ny',         $performance_kw.' kW / '.round($performance_kw*1.36).' LE', false ];
                if ( $engine_size )    $specs[] = [ 'Henger&#369;rtartalom',     number_format((int)$engine_size,0,',',' ').' cm&#179;', false ];
                if ( $transmission )   $specs[] = [ 'Sebess&#233;gv&#225;lt&#243;',        $trans_labels[$transmission] ?? $transmission, false ];
                if ( $body_type )      $specs[] = [ 'Fel&#233;p&#237;tm&#233;ny',          $body_labels[$body_type] ?? $body_type, false ];
                if ( $color_val )      $specs[] = [ 'Sz&#237;n',                 $color_val,   false ];
                if ( $doors )          $specs[] = [ 'Ajt&#243;k sz&#225;ma',          $doors,       false ];
                if ( $owners )         $specs[] = [ 'Tulajdonosok sz.',     $owners,      false ];
                if ( $keys_count )     $specs[] = [ 'Kulcsok sz&#225;ma',        $keys_count,  false ];
                if ( $tech_inspect )   $specs[] = [ 'M&#369;szaki lej&#225;r',        $tech_inspect,false ];
            } elseif ( $site_type === 'ingatlan' ) {
                if ( $area_m2 )        $specs[] = [ 'Alapter&#252;let',          $area_m2.' m&#178;',       false ];
                if ( $rooms )          $specs[] = [ 'Szob&#225;k',               $rooms,               false ];
                if ( $floor !== '' )   $specs[] = [ 'Emelet',               $floor.'. emelet',    false ];
                if ( $lot_size )       $specs[] = [ 'Telekter&#252;let',         $lot_size.' m&#178;',      false ];
                if ( $building_year )  $specs[] = [ '&#201;p&#237;t&#233;si &#233;v',           $building_year,       false ];
                if ( $parking )        $specs[] = [ 'Parkol&#243;',              $park_labels[$parking] ?? $parking, false ];
                if ( $furnished )      $specs[] = [ 'B&#250;torozott',           $furn_labels[$furnished] ?? $furnished, false ];
                if ( $heating )        $specs[] = [ 'F&#369;t&#233;s',                $heat_labels[$heating] ?? $heating, false ];
            } else {
                if ( $brand )          $specs[] = [ 'M&#225;rka / Gy&#225;rt&#243;',       $brand,  true ];
                if ( $model )          $specs[] = [ 'Modell / T&#237;pus',       $model,  true ];
                if ( $caliber )        $specs[] = [ 'Kaliber',              $caliber,false ];
                if ( $year )           $specs[] = [ 'Gy&#225;rt&#225;si &#233;v',          $year,   false ];
            }

            $badges = [];
            if ( $site_type === 'jarmu' ) {
                $badges[] = $prev_damage === '1'
                    ? ['damage-yes','&#9888; Kor&#225;bbi k&#225;r / baleset']
                    : ['damage-no', '&#10003; Nincs kor&#225;bbi k&#225;r'];
                if ( $service_book === '1' ) $badges[] = ['service-yes','&#10003; Szervizk&#246;nyv megvan'];
            }
            if ( $license_req === '1' )  $badges[] = ['license',     '&#9888; Fegyverenged&#233;ly sz&#252;ks&#233;ges'];
            if ( $balcony === '1' )       $badges[] = ['service-yes', '&#10003; Erk&#233;ly / terasz'];

            if ( ! empty($specs) || ! empty($badges) ):
            ?>
            <div class="sl__card sl__params">
                <div class="sl__card-title">R&#233;szletek</div>
                <?php if ( ! empty($badges) ): ?>
                <div class="sl__badge-row">
                    <?php foreach ( $badges as $b ): ?>
                        <span class="sl__badge sl__badge--<?php echo esc_attr($b[0]); ?>"><?php echo esc_html($b[1]); ?></span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                <?php if ( ! empty($specs) ): ?>
                <div class="sl__specs-table">
                    <?php foreach ( $specs as $spec ):
                        $full_cls = ! empty($spec[2]) ? ' sl__spec-row--full' : '';
                    ?>
                    <div class="sl__spec-row<?php echo esc_attr($full_cls); ?>">
                        <span class="sl__spec-label"><?php echo esc_html($spec[0]); ?></span>
                        <span class="sl__spec-val"><?php echo esc_html($spec[1]); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Felado + kapcsolat -->
            <div class="sl__card sl__contact">
                <div class="sl__card-title">Felad&#243;</div>
                <?php
                $author_avatar_id  = $author ? (int) get_user_meta( $author->ID, 'va_profile_avatar_id', true ) : 0;
                $author_avatar_url = $author_avatar_id ? wp_get_attachment_image_url( $author_avatar_id, 'thumbnail' ) : '';
                ?>
                <div class="sl__seller">
                    <div class="sl__seller-av">
                        <?php if ( $author_avatar_url ): ?>
                            <img src="<?php echo esc_url( $author_avatar_url ); ?>" alt="Felad&#243; profijk&#233;pe">
                        <?php else: ?>
                            <?php echo strtoupper( substr( $author ? $author->display_name : 'X', 0, 1 ) ); ?>
                        <?php endif; ?>
                    </div>
                    <div>
                        <div class="sl__seller-name"><?php echo esc_html($author ? $author->display_name : 'Ismeretlen'); ?></div>
                        <?php if ($author): ?>
                        <div class="sl__seller-since">Tag <?php echo esc_html(date('Y', strtotime($author->user_registered))); ?> &#243;ta</div>
                        <?php
                        if ( get_option( 'va_single_show_plan_badge', '1' ) === '1' && class_exists( 'VA_User_Roles' ) ):
                            $author_plan  = VA_User_Roles::get_user_plan( $author->ID );
                            $all_plan_cfg = VA_User_Roles::get_all_plan_configs();
                            $plan_labels  = [ 'basic'=>'Alap','silver'=>'Ez&#252;st','gold'=>'Arany','platinum'=>'Platina' ];
                            if ( $author_plan === 'platinum' ) {
                                $user_seller_label = get_user_meta( $author->ID, 'va_seller_label', true );
                                $plan_label = ! empty($user_seller_label)
                                    ? sanitize_text_field($user_seller_label)
                                    : ( ! empty($all_plan_cfg['platinum']['seller_label'])
                                        ? sanitize_text_field($all_plan_cfg['platinum']['seller_label'])
                                        : $plan_labels['platinum'] );
                            } else {
                                $plan_label = $plan_labels[$author_plan] ?? ucfirst($author_plan);
                            }
                            $plan_icons = ['basic'=>'','silver'=>'&#10086;','gold'=>'&#9733;','platinum'=>'&#9670;'];
                            $plan_icon  = $plan_icons[$author_plan] ?? '';
                        ?>
                        <div class="sl__plan-badge sl__plan-badge--<?php echo esc_attr($author_plan); ?>">
                            <?php if ($plan_icon) echo esc_html($plan_icon).' '; ?><?php echo esc_html($plan_label); ?> tag
                        </div>
                        <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <?php
                $author_phone = $author ? get_user_meta($author->ID,'va_phone',true) : '';
                $show_phone   = $phone ?: $author_phone;
                if ( $show_phone ): ?>
                    <button class="sl__btn sl__btn--phone" data-phone="<?php echo esc_attr($show_phone); ?>">
                        &#128222; Telefonsz&#225;m megjelen&#237;t&#233;se
                    </button>
                    <a href="tel:<?php echo esc_attr(preg_replace('/[^+0-9]/','',$show_phone)); ?>"
                       class="sl__phone-reveal" id="sl-phone" style="display:none;">
                        <?php echo esc_html($show_phone); ?>
                    </a>
                <?php endif; ?>

                <?php if ( $email_show === '1' && $author ): ?>
                    <a href="mailto:<?php echo esc_attr($author->user_email); ?>" class="sl__btn sl__btn--email">
                        &#9993; E-mail &#252;zenet k&#252;ld&#233;se
                    </a>
                <?php endif; ?>

                <?php if ( is_user_logged_in() ):
                    $watching = va_user_watches($post_id); ?>
                    <button class="sl__btn sl__btn--watch<?php echo $watching?' active':''; ?>"
                            data-post-id="<?php echo esc_attr($post_id); ?>">
                        <?php echo $watching ? '&#9733; Kedvencekb&#337;l elt&#225;vol&#237;t&#225;s' : '&#9734; Ment&#233;s kedvencekbe'; ?>
                    </button>
                <?php endif; ?>

                <!-- Megosztás -->
                <?php
                $share_url   = rawurlencode( get_permalink($post_id) );
                $share_title = rawurlencode( get_the_title($post_id) );
                ?>
                <div class="sl__share">
                    <span class="sl__share-label">Megosztás:</span>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $share_url; ?>" target="_blank" rel="noopener noreferrer" class="sl__share-btn sl__share-btn--fb" aria-label="Facebook">
                        <?php echo function_exists('va_social_svg') ? va_social_svg('facebook',18) : ''; ?>
                    </a>
                    <a href="https://wa.me/?text=<?php echo $share_title; ?>%20<?php echo $share_url; ?>" target="_blank" rel="noopener noreferrer" class="sl__share-btn sl__share-btn--wa" aria-label="WhatsApp">
                        <?php echo function_exists('va_social_svg') ? va_social_svg('whatsapp',18) : ''; ?>
                    </a>
                    <a href="https://t.me/share/url?url=<?php echo $share_url; ?>&text=<?php echo $share_title; ?>" target="_blank" rel="noopener noreferrer" class="sl__share-btn sl__share-btn--tg" aria-label="Telegram">
                        <?php echo function_exists('va_social_svg') ? va_social_svg('telegram',18) : ''; ?>
                    </a>
                    <a href="https://twitter.com/intent/tweet?url=<?php echo $share_url; ?>&text=<?php echo $share_title; ?>" target="_blank" rel="noopener noreferrer" class="sl__share-btn sl__share-btn--tw" aria-label="X/Twitter">
                        <?php echo function_exists('va_social_svg') ? va_social_svg('twitter',18) : ''; ?>
                    </a>
                    <button class="sl__share-btn sl__share-btn--copy" id="sl-copy-link" aria-label="Link m&#225;sol&#225;sa" data-url="<?php echo esc_attr(get_permalink($post_id)); ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>
                    </button>
                </div>
            </div>

            <!-- Feladó további hirdetései -->
            <?php if ( $author ):
                $other = new WP_Query([
                    'post_type'      => 'va_listing',
                    'post_status'    => 'publish',
                    'author'         => $author->ID,
                    'posts_per_page' => 3,
                    'post__not_in'   => [$post_id],
                    'no_found_rows'  => true,
                ]);
                if ( $other->have_posts() ):
            ?>
            <div class="sl__card sl__more">
                <div class="sl__card-title">Felad&#243; tov&#225;bbi hirdet&#233;sei</div>
                <?php while ( $other->have_posts() ): $other->the_post();
                    $p_id    = get_the_ID();
                    $p_price = get_post_meta($p_id,'va_price',true);
                    $p_type  = get_post_meta($p_id,'va_price_type',true);
                ?>
                    <a href="<?php the_permalink(); ?>" class="sl__more-item">
                        <?php if ( has_post_thumbnail() ):
                            echo get_the_post_thumbnail(null,[54,54],['class'=>'sl__more-img']);
                        else: ?>
                            <div class="sl__more-img sl__more-img--empty">&#128247;</div>
                        <?php endif; ?>
                        <div class="sl__more-info">
                            <div class="sl__more-title"><?php the_title(); ?></div>
                            <div class="sl__more-price"><?php echo esc_html(va_format_price($p_price,$p_type)); ?></div>
                        </div>
                    </a>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>
            <?php endif; endif; ?>

        </div><!-- .sl__right -->
    </div><!-- .sl__layout -->
</div><!-- .sl -->

<!-- Hasonló hirdetések -->
<?php
$related = new WP_Query([
    'post_type'      => 'va_listing',
    'post_status'    => 'publish',
    'posts_per_page' => 6,
    'post__not_in'   => [$post_id],
    'no_found_rows'  => true,
    'tax_query'      => $categories && !is_wp_error($categories) ? [[
        'taxonomy' => 'va_category',
        'field'    => 'term_id',
        'terms'    => wp_list_pluck($categories,'term_id'),
    ]] : [],
]);
if ( $related->have_posts() ):
?>
<div style="max-width:<?php echo esc_attr((string)$sl_content_max); ?>px;margin:28px auto 0;padding:0 16px;">
    <div class="sl__card" style="margin-bottom:0;">
        <div class="sl__card-title" style="margin-bottom:14px;">Hasonl&#243; hirdet&#233;sek</div>
        <div class="sl__related-grid">
            <?php while ( $related->have_posts() ): $related->the_post();
                $rp_id    = get_the_ID();
                $rp_price = get_post_meta($rp_id,'va_price',true);
                $rp_ptype = get_post_meta($rp_id,'va_price_type',true) ?: 'fixed';
                $rp_loc   = get_post_meta($rp_id,'va_location',true);
                $rp_km    = get_post_meta($rp_id,'va_mileage',true);
                $rp_yr    = get_post_meta($rp_id,'va_year',true);
            ?>
            <a href="<?php the_permalink(); ?>" class="sl__rel-item">
                <?php if ( has_post_thumbnail() ): ?>
                    <?php echo get_the_post_thumbnail(null,[400,300],['class'=>'sl__rel-img','loading'=>'lazy']); ?>
                <?php else: ?>
                    <div class="sl__rel-img--empty">&#128247;</div>
                <?php endif; ?>
                <div class="sl__rel-info">
                    <div class="sl__rel-title"><?php the_title(); ?></div>
                    <div class="sl__rel-price"><?php echo esc_html(va_format_price($rp_price,$rp_ptype)); ?></div>
                    <div class="sl__rel-meta">
                        <?php if ($rp_yr)  echo esc_html($rp_yr); ?>
                        <?php if ($rp_km)  echo ' &middot; '.esc_html(number_format((int)$rp_km,0,',',' ')).' km'; ?>
                        <?php if ($rp_loc) echo ' &middot; '.esc_html($rp_loc); ?>
                    </div>
                </div>
            </a>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Sticky bottom bar -->
<?php
$show_phone_sticky = $phone ?: ($author ? get_user_meta($author->ID,'va_phone',true) : '');
$watching_sticky   = is_user_logged_in() ? va_user_watches($post_id) : false;
?>
<div class="sl__sticky-bar" id="sl-sticky-bar">
    <div class="sl__sticky-title"><?php the_title(); ?></div>
    <div class="sl__sticky-price"><?php echo esc_html(va_format_price($price,$price_type)); ?></div>
    <?php if ($show_phone_sticky): ?>
    <button class="sl__sticky-btn sl__sticky-btn--phone" id="sl-sticky-phone" data-phone="<?php echo esc_attr($show_phone_sticky); ?>">
        &#128222; Telefonsz&#225;m
    </button>
    <?php endif; ?>
    <?php if (is_user_logged_in()): ?>
    <button class="sl__sticky-btn sl__sticky-btn--watch<?php echo $watching_sticky?' active':''; ?>"
            id="sl-sticky-watch" data-post-id="<?php echo esc_attr($post_id); ?>">
        <?php echo $watching_sticky ? '&#9733; Elmentve' : '&#9734; Kedvencekbe'; ?>
    </button>
    <?php endif; ?>
</div>

'@

foreach ($filePath in $files) {
    if (-not (Test-Path $filePath)) { Write-Host "SKIP (not found): $filePath"; continue }
    
    $content = [System.IO.File]::ReadAllText($filePath, [System.Text.Encoding]::UTF8)
    
    $startMarker = "<!-- JOBB: fejlec + adatok + kontakt -->"
    $endMarker   = "</div><!-- .sl -->"
    
    $startIdx = $content.IndexOf($startMarker)
    $endIdx   = $content.IndexOf($endMarker)
    
    if ($startIdx -lt 0 -or $endIdx -lt 0) {
        Write-Host "SKIP (markers not found): $filePath"
        continue
    }
    
    # Start of that line (go back to after the previous newline)
    $lineStart = $content.LastIndexOf("`n", $startIdx) + 1
    # End: after the marker + \r\n
    $endFull = $endIdx + $endMarker.Length
    # Skip optional \r\n after end marker
    if ($endFull -lt $content.Length -and $content[$endFull] -eq "`r") { $endFull++ }
    if ($endFull -lt $content.Length -and $content[$endFull] -eq "`n") { $endFull++ }
    
    $before = $content.Substring(0, $lineStart)
    $after  = $content.Substring($endFull)
    
    # Convert newSection to CRLF
    $newSectionCRLF = $newSection -replace "(?<!\r)\n", "`r`n"
    
    $newContent = $before + $newSectionCRLF + $after
    
    [System.IO.File]::WriteAllText($filePath, $newContent, [System.Text.Encoding]::UTF8)
    Write-Host "PATCHED: $filePath"
}

Write-Host "Done."
