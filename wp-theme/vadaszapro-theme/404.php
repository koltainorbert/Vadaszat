<?php
/**
 * 404 template
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>

<main class="va-404" role="main" aria-labelledby="va404-title">
    <div class="va-404__grain" aria-hidden="true"></div>

    <span class="va-404__hud va-404__hud--top-right">ERR.404 // SIGNAL LOST</span>
    <span class="va-404__hud va-404__hud--left">RECALIBRATING // ROUTE LOST</span>
    <span class="va-404__hud va-404__hud--bottom-left">N47 00 E17 54 - 10:41:14</span>

    <div class="va-404__corner va-404__corner--tl" aria-hidden="true"></div>
    <div class="va-404__corner va-404__corner--tr" aria-hidden="true"></div>
    <div class="va-404__corner va-404__corner--bl" aria-hidden="true"></div>
    <div class="va-404__corner va-404__corner--br" aria-hidden="true"></div>

    <section class="va-404__center">
        <h1 class="va-404__digits" aria-label="404">404</h1>
        <div class="va-404__line" aria-hidden="true"></div>
        <h2 id="va404-title" class="va-404__title">AZ OLDAL ELVESZETT A DIGITALIS TERBEN</h2>
        <p class="va-404__lead">
            Ez az oldal nem letezik, vagy mar nem erheto el.<br>
            De a cel meg mindig elerheto - gyere vissza!
        </p>

        <a class="va-404__btn" href="<?php echo esc_url( home_url( '/' ) ); ?>">
            VISSZA A FOOLDALRA <span aria-hidden="true">&rarr;</span>
        </a>
    </section>
</main>

<style>
.va-404 {
    min-height: calc(100vh - var(--nav, 66px));
    display: grid;
    place-items: center;
    position: relative;
    overflow: hidden;
    padding: 20px;
    background:
        radial-gradient(circle at 50% 30%, rgba(255, 0, 0, .09), transparent 48%),
        radial-gradient(circle at 20% 75%, rgba(255, 0, 0, .05), transparent 35%),
        radial-gradient(circle at 80% 20%, rgba(255, 0, 0, .045), transparent 30%),
        repeating-linear-gradient(0deg, rgba(255,255,255,.02) 0 1px, transparent 1px 26px),
        repeating-linear-gradient(90deg, rgba(255,255,255,.016) 0 1px, transparent 1px 26px),
        rgb(6,6,6);
}
.va-404__grain {
    position: absolute;
    inset: 0;
    pointer-events: none;
    opacity: .09;
    background-image: radial-gradient(rgba(255,255,255,.32) .7px, transparent .7px);
    background-size: 3px 3px;
    mix-blend-mode: soft-light;
}
.va-404__center {
    width: min(980px, 100%);
    text-align: center;
    color: #fff !important;
    z-index: 2;
}
.va-404__digits {
    margin: 0;
    font-size: clamp(120px, 22vw, 300px);
    line-height: .82;
    font-weight: 900;
    letter-spacing: .01em;
    text-transform: uppercase;
    background: linear-gradient(
        to bottom,
        #f2f2f2 0 40%,
        #65e6ea 40% 50%,
        #f2f2f2 50% 68%,
        #ff2030 68% 80%,
        #f2f2f2 80% 100%
    );
    -webkit-background-clip: text;
    background-clip: text;
    color: #f2f2f2 !important;
    -webkit-text-fill-color: #f2f2f2;
    filter: drop-shadow(0 8px 20px rgba(0,0,0,.58));
    animation: va404-glitch 10s linear infinite;
}
@supports ((-webkit-background-clip: text) or (background-clip: text)) {
    .va-404__digits {
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent !important;
        -webkit-text-fill-color: transparent;
    }
}
.va-404__line {
    width: min(420px, 80%);
    height: 1px;
    margin: 22px auto 26px;
    background: linear-gradient(90deg, transparent, rgba(255,0,0,.95), transparent);
}
.va-404__title {
    margin: 0;
    font-size: clamp(25px, 3.2vw, 44px);
    letter-spacing: .12em;
    line-height: 1.2;
    font-weight: 800;
    text-transform: uppercase;
    color: #f6f6f6 !important;
    text-shadow: 0 2px 10px rgba(0,0,0,.55);
}
.va-404__lead {
    margin: 22px auto 0;
    max-width: 700px;
    color: rgba(255,255,255,.88) !important;
    text-shadow: 0 1px 8px rgba(0,0,0,.45);
    font-size: clamp(16px, 1.5vw, 30px);
    line-height: 1.65;
    font-weight: 600;
}
.va-404__btn {
    margin-top: 34px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    min-height: 62px;
    padding: 0 34px;
    border-radius: 6px;
    border: 1px solid rgba(255,255,255,.22);
    color: #fff;
    text-decoration: none;
    font-size: 24px;
    font-weight: 800;
    letter-spacing: .08em;
    text-transform: uppercase;
    background: rgba(255,255,255,.02);
    box-shadow: inset 0 0 0 1px rgba(255,255,255,.04), 0 10px 30px rgba(0,0,0,.38);
    transition: transform .2s ease, border-color .2s ease, color .2s ease;
}
.va-404__btn:hover {
    transform: translateY(-1px);
    border-color: rgba(255,0,0,.65);
    color: #fff;
}
.va-404__hud {
    position: absolute;
    color: rgba(255,40,40,.42);
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .12em;
    text-transform: uppercase;
    z-index: 2;
    pointer-events: none;
}
.va-404__hud--top-right { top: 30px; right: 36px; }
.va-404__hud--left {
    top: 50%;
    left: 18px;
    transform: translateY(-50%) rotate(-90deg);
    transform-origin: left top;
    color: rgba(255,255,255,.28);
}
.va-404__hud--bottom-left { left: 30px; bottom: 24px; color: rgba(255,255,255,.26); }
.va-404__corner {
    position: absolute;
    width: 28px;
    height: 28px;
    border-color: rgba(255,0,0,.5);
    border-style: solid;
    z-index: 2;
}
.va-404__corner--tl { top: 18px; left: 18px; border-width: 2px 0 0 2px; }
.va-404__corner--tr { top: 18px; right: 18px; border-width: 2px 2px 0 0; }
.va-404__corner--bl { bottom: 18px; left: 18px; border-width: 0 0 2px 2px; }
.va-404__corner--br { bottom: 18px; right: 18px; border-width: 0 2px 2px 0; }
@keyframes va404-glitch {
    0%, 79%, 100% {
        transform: translate3d(0,0,0);
        filter: drop-shadow(0 8px 20px rgba(0,0,0,.58));
    }
    80% {
        transform: translate3d(-2px, 0, 0) skewX(-1.2deg);
        filter: drop-shadow(4px 0 0 rgba(0,255,255,.55)) drop-shadow(-4px 0 0 rgba(255,0,0,.55));
    }
    81% {
        transform: translate3d(2px, -1px, 0) skewX(1.1deg);
        filter: drop-shadow(-5px 0 0 rgba(0,255,255,.55)) drop-shadow(5px 0 0 rgba(255,0,0,.55));
    }
    82% {
        transform: translate3d(-3px, 1px, 0) skewX(-1.5deg);
    }
    83% {
        transform: translate3d(1px, 0, 0);
        filter: drop-shadow(0 8px 20px rgba(0,0,0,.58));
    }
}
@media (max-width: 900px) {
    .va-404 { padding: 16px; }
    .va-404__hud--left,
    .va-404__hud--top-right,
    .va-404__hud--bottom-left { display: none; }
    .va-404__title { letter-spacing: .07em; }
}
@media (max-width: 640px) {
    .va-404 {
        min-height: calc(100vh - var(--nav, 66px));
        padding: 12px;
    }
    .va-404__digits { font-size: clamp(92px, 34vw, 170px); }
    .va-404__line { margin: 14px auto 18px; }
    .va-404__title { font-size: clamp(18px, 5.5vw, 28px); }
    .va-404__lead { font-size: 18px; margin-top: 14px; }
    .va-404__btn { width: 100%; min-height: 52px; font-size: 18px; margin-top: 22px; }
}
</style>

<?php
get_footer();