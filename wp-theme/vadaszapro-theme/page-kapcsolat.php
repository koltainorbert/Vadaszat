<?php
/**
 * page-kapcsolat.php – Kapcsolat oldal
 */
get_header();

$status = sanitize_text_field( wp_unslash( $_GET['contact_status'] ?? '' ) );
$contact_video = get_option( 'va_contact_hero_video_url', content_url( 'uploads/2026/04/0_Offroad_4x4_1920x1080.mp4' ) );
?>

<section class="va-contact-page">
    <div class="va-contact-page__hero">
        <video class="va-contact-page__video" autoplay muted loop playsinline preload="auto" aria-hidden="true">
            <source src="<?php echo esc_url( $contact_video ); ?>" type="video/mp4">
        </video>
        <div class="va-contact-page__video-overlay"></div>
        <div class="va-contact-page__hero-glow va-contact-page__hero-glow--1"></div>
        <div class="va-contact-page__hero-glow va-contact-page__hero-glow--2"></div>

        <div class="va-contact-page__hero-inner">
            <div class="va-contact-page__eyebrow"><span class="vcp-hero__badge-dot"></span>Kapcsolat</div>
            <h1 class="va-contact-page__title">Írj nekünk e-mailt</h1>
            <p class="va-contact-page__lead">
                A kapcsolatfelvétel kizárólag e-mailben történik. Az itt elküldött üzenetek WordPress oldalon keresztül,
                a WP Mail SMTP bővítményen át jutnak el hozzánk.
            </p>
        </div>
    </div>

    <div class="va-contact-page__wrap">
        <div class="va-contact-page__info">
            <div class="va-contact-card va-contact-card--accent">
                <div class="va-contact-card__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M4 6h16v12H4z"/>
                        <path d="m4 7 8 6 8-6"/>
                    </svg>
                </div>
                <h2 class="va-contact-card__title">Csak e-mailes megkeresés</h2>
                <p class="va-contact-card__text">Telefonszámot és nyilvános közvetlen címet nem jelenítünk meg. Minden megkeresést ezen az űrlapon keresztül fogadunk.</p>
            </div>

            <div class="va-contact-card">
                <h3 class="va-contact-card__mini">Mit írj meg?</h3>
                <ul class="va-contact-list">
                    <li>melyik témában keresel minket</li>
                    <li>mi a kérdésed vagy problémád röviden</li>
                    <li>milyen e-mail címre válaszoljunk</li>
                </ul>
            </div>

            <div class="va-contact-card">
                <h3 class="va-contact-card__mini">Technikai háttér</h3>
                <p class="va-contact-card__text">Az üzenetküldés a WordPress `wp_mail()` rendszerén keresztül történik, így a küldést a WP Mail SMTP plugin kezeli.</p>
            </div>
        </div>

        <div class="va-contact-page__form-col">
            <div class="va-contact-formbox">
                <h2 class="va-contact-formbox__title">Üzenet küldése</h2>

                <?php if ( $status === 'ok' ) : ?>
                    <div class="va-contact-alert va-contact-alert--success">Az üzenetedet elküldtük. Hamarosan e-mailben válaszolunk.</div>
                <?php elseif ( $status === 'invalid' ) : ?>
                    <div class="va-contact-alert va-contact-alert--error">Kérlek tölts ki minden kötelező mezőt érvényes adatokkal.</div>
                <?php elseif ( $status === 'error' ) : ?>
                    <div class="va-contact-alert va-contact-alert--error">Az üzenet küldése nem sikerült. Ellenőrizd a SMTP beállítást, majd próbáld újra.</div>
                <?php elseif ( $status === 'nonce' ) : ?>
                    <div class="va-contact-alert va-contact-alert--error">A kérés érvénytelen volt. Kérlek küldd el újra az űrlapot.</div>
                <?php endif; ?>

                <form class="va-contact-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
                    <input type="hidden" name="action" value="va_contact_form">
                    <?php wp_nonce_field( 'va_contact_form', 'va_contact_nonce' ); ?>

                    <div class="va-contact-form__hp" aria-hidden="true">
                        <label for="va-company">Cég</label>
                        <input type="text" id="va-company" name="va_company" tabindex="-1" autocomplete="off">
                    </div>

                    <div class="va-contact-form__row">
                        <div class="va-contact-field">
                            <label for="va-name">Név</label>
                                                        <input id="va-name" name="va_name" type="text" required data-typed-placeholder="Teljes neved" placeholder="">
                        </div>
                        <div class="va-contact-field">
                            <label for="va-email">E-mail</label>
                                                        <input id="va-email" name="va_email" type="email" required data-typed-placeholder="pelda@email.hu" placeholder="">
                        </div>
                    </div>

                    <div class="va-contact-field">
                        <label for="va-phone">Telefonszám</label>
                                                <input id="va-phone" name="va_phone" type="tel" inputmode="tel" required data-typed-placeholder="+36 30 123 4567" placeholder="">
                    </div>

                    <div class="va-contact-field">
                        <label for="va-subject">Tárgy</label>
                                                <input id="va-subject" name="va_subject" type="text" required data-typed-placeholder="Miben tudunk segíteni?" placeholder="">
                    </div>

                    <div class="va-contact-field">
                        <label for="va-message">Üzenet</label>
                                                <textarea id="va-message" name="va_message" rows="8" required data-typed-placeholder="Írd le röviden a kérdésedet vagy megkeresésed részleteit..." placeholder=""></textarea>
                    </div>

                    <button class="va-contact-form__submit" type="submit">Üzenet elküldése</button>
                </form>
            </div>
        </div>
    </div>
</section>

<script>
(function(){
    var fields=document.querySelectorAll('[data-typed-placeholder]');
    if(!fields.length)return;

    function animateField(field,delay){
        var full=field.getAttribute('data-typed-placeholder')||'';
        var index=0;
        var timer=null;

        function clearTyping(){
            if(timer){window.clearTimeout(timer);timer=null;}
        }

        function step(){
            if(document.activeElement===field||field.value!=='')return;
            field.setAttribute('placeholder',full.slice(0,index));
            index++;
            if(index<=full.length){
                timer=window.setTimeout(step,index<4?42:28);
            }
        }

        field.addEventListener('focus',function(){
            clearTyping();
            field.setAttribute('placeholder','');
        });

        field.addEventListener('blur',function(){
            if(field.value!=='')return;
            index=0;
            field.setAttribute('placeholder','');
            timer=window.setTimeout(step,160);
        });

        timer=window.setTimeout(step,delay);
    }

    fields.forEach(function(field,i){
        animateField(field,220+(i*260));
    });
})();
</script>

<?php get_footer(); ?>