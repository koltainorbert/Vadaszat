<?php
/**
 * 404 template
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>

<main class="va-404" role="main">
    <div class="va-404__noise" aria-hidden="true"></div>
    <section class="va-404__shell">
        <div class="va-404__left">
            <p class="va-404__kicker">Error page</p>
            <h1 class="va-404__digits" aria-label="404" data-text="404">404</h1>
            <h2 class="va-404__title">Eltunt az oldal</h2>
            <p class="va-404__lead">
                Amit kerestel, mar nem itt van. Menj vissza a forgalomba,
                vagy nyiss ra uj hirdetesekre egy kattintassal.
            </p>

            <div class="va-404__actions">
                <a class="va-404__btn va-404__btn--primary" href="<?php echo esc_url( home_url( '/va-hirdetes-kereses/' ) ); ?>">Hirdetes keresese</a>
                <a class="va-404__btn va-404__btn--ghost" href="<?php echo esc_url( home_url( '/' ) ); ?>">Fooldal</a>
                <a class="va-404__btn va-404__btn--ghost" href="<?php echo esc_url( home_url( '/kapcsolat/' ) ); ?>">Kapcsolat</a>
            </div>

            <form class="va-404__search" action="<?php echo esc_url( home_url( '/' ) ); ?>" method="get">
                <label class="screen-reader-text" for="va-404-s">Kereses</label>
                <input id="va-404-s" type="search" name="s" placeholder="marka, modell, kulcsszo" value="<?php echo esc_attr( get_search_query() ); ?>">
                <button type="submit">Kereses</button>
            </form>
        </div>

        <aside class="va-404__right" aria-hidden="true">
            <div class="va-404__stat">
                <span>Uj hirdetesek</span>
                <strong>24/7</strong>
            </div>
            <div class="va-404__stat">
                <span>Aukcios piac</span>
                <strong>LIVE</strong>
            </div>
            <div class="va-404__stat">
                <span>Gyors visszaut</span>
                <strong>1 KATT</strong>
            </div>
        </aside>
    </section>
</main>

<style>
.va-404 {
    min-height: calc(100vh - var(--nav, 66px));
    display: grid;
    place-items: center;
    padding: 24px 14px;
    position: relative;
    overflow: hidden;
    background:
        radial-gradient(circle at 8% 12%, rgba(255,0,60,.22), transparent 40%),
        radial-gradient(circle at 88% 90%, rgba(0,180,255,.12), transparent 42%),
        linear-gradient(120deg, rgba(255,0,0,.05), rgba(0,0,0,0) 40%),
        repeating-linear-gradient(0deg, rgba(255,255,255,.03) 0 1px, transparent 1px 20px),
        repeating-linear-gradient(90deg, rgba(255,255,255,.022) 0 1px, transparent 1px 20px),
        rgb(6,6,6);
}
.va-404::before,
.va-404::after {
    content: "";
    position: absolute;
    width: 360px;
    height: 360px;
    border-radius: 50%;
    filter: blur(30px);
    pointer-events: none;
}
.va-404::before {
    top: -110px;
    left: -140px;
    background: rgba(255,0,70,.2);
}
.va-404::after {
    bottom: -130px;
    right: -130px;
    background: rgba(0,170,255,.16);
}
.va-404__noise {
    position: absolute;
    inset: 0;
    background-image: radial-gradient(rgba(255,255,255,.05) 1px, transparent 1px);
    background-size: 3px 3px;
    opacity: .08;
    mix-blend-mode: screen;
    pointer-events: none;
}
.va-404__shell {
    width: min(1200px, 100%);
    min-height: min(760px, calc(100vh - var(--nav, 66px) - 32px));
    border: 1px solid rgba(255,255,255,.16);
    border-radius: 26px;
    background: linear-gradient(145deg, rgba(16,16,16,.96), rgba(7,7,7,.98));
    box-shadow: 0 30px 120px rgba(0,0,0,.66), 0 0 0 1px rgba(255,0,0,.18) inset;
    color: #fff;
    padding: clamp(20px, 4vw, 44px);
    text-align: left;
    position: relative;
    overflow: hidden;
    z-index: 1;
    display: grid;
    grid-template-columns: minmax(0, 1.45fr) minmax(260px, .55fr);
    gap: 22px;
    align-items: stretch;
}
.va-404__shell::before {
    content: "";
    position: absolute;
    inset: -36% auto auto -24%;
    width: 420px;
    height: 420px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(255,0,0,.24), transparent 65%);
    filter: blur(10px);
    pointer-events: none;
}
.va-404__left {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: flex-start;
    min-width: 0;
}
.va-404__kicker {
    margin: 0;
    color: rgba(255,255,255,.78);
    font-size: 12px;
    font-weight: 800;
    letter-spacing: .22em;
    text-transform: uppercase;
}
.va-404__digits {
    margin: 4px 0 8px;
    display: inline-block;
    font-size: clamp(84px, 19vw, 220px);
    line-height: .82;
    font-weight: 900;
    letter-spacing: .01em;
    position: relative;
    color: #fff;
    text-shadow: 0 10px 36px rgba(255,0,0,.28);
    animation: va404-shift 10s linear infinite;
}
.va-404__digits::before,
.va-404__digits::after {
    content: attr(data-text);
    position: absolute;
    top: 0;
    left: 0;
    pointer-events: none;
    opacity: .85;
}
.va-404__digits::before {
    color: rgba(0,255,255,.9);
    transform: translate(2px, 0);
    mix-blend-mode: screen;
    animation: va404-glitch-cyan 10s linear infinite;
}
.va-404__digits::after {
    color: rgba(255,0,80,.95);
    transform: translate(-2px, 0);
    mix-blend-mode: screen;
    animation: va404-glitch-red 10s linear infinite;
}
.va-404__title {
    margin: 0 0 12px;
    font-size: clamp(26px, 4vw, 52px);
    line-height: 1.05;
    letter-spacing: .01em;
    text-transform: uppercase;
    color: #fff;
}
.va-404__lead {
    margin: 0;
    max-width: 700px;
    color: rgba(255,255,255,.74);
    font-size: clamp(14px, 2.1vw, 18px);
    line-height: 1.68;
}
.va-404__actions {
    margin-top: 22px;
    display: flex;
    flex-wrap: wrap;
    justify-content: flex-start;
    gap: 10px;
}
.va-404__btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 44px;
    padding: 0 18px;
    border-radius: 999px;
    border: 1px solid rgba(255,255,255,.18);
    text-decoration: none;
    font-weight: 700;
    font-size: 13px;
    transition: .2s ease;
}
.va-404__btn--primary {
    background: linear-gradient(135deg, #ff0000, #b00000);
    border-color: rgba(255,0,0,.85);
    color: #fff;
    box-shadow: 0 10px 28px rgba(255,0,0,.28);
}
.va-404__btn--primary:hover {
    filter: brightness(1.08);
    transform: translateY(-1px);
}
.va-404__btn--ghost {
    background: rgba(255,255,255,.05);
    color: #fff;
}
.va-404__btn--ghost:hover {
    border-color: rgba(255,0,0,.7);
    color: #ff4d4d;
}
.va-404__search {
    margin-top: 16px;
    max-width: 700px;
    width: 100%;
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 10px;
}
.va-404__search input {
    height: 50px;
    border-radius: 14px;
    border: 1px solid rgba(255,255,255,.16);
    background: rgba(0,0,0,.42);
    color: #fff;
    padding: 0 16px;
    font-size: 14px;
}
.va-404__search input:focus {
    outline: none;
    border-color: rgba(255,0,0,.8);
    box-shadow: 0 0 0 3px rgba(255,0,0,.15);
}
.va-404__search button {
    height: 50px;
    border-radius: 14px;
    border: 1px solid rgba(255,0,0,.8);
    background: linear-gradient(135deg, rgba(255,0,0,.32), rgba(150,0,0,.38));
    color: #fff;
    font-weight: 800;
    letter-spacing: .03em;
    padding: 0 20px;
    cursor: pointer;
    transition: .2s ease;
}
.va-404__search button:hover {
    transform: translateY(-1px);
    background: linear-gradient(135deg, rgba(255,0,0,.46), rgba(170,0,0,.48));
}
.va-404__right {
    border: 1px solid rgba(255,255,255,.1);
    border-radius: 20px;
    background: linear-gradient(170deg, rgba(20,20,20,.7), rgba(6,6,6,.88));
    padding: 16px;
    display: grid;
    align-content: center;
    gap: 10px;
    position: relative;
    overflow: hidden;
}
.va-404__right::before {
    content: "";
    position: absolute;
    inset: -20% -20% auto auto;
    width: 220px;
    height: 220px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(255,0,0,.2), transparent 65%);
    filter: blur(10px);
}
.va-404__stat {
    border: 1px solid rgba(255,255,255,.1);
    border-radius: 14px;
    padding: 12px;
    background: rgba(255,255,255,.02);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    position: relative;
    z-index: 1;
}
.va-404__stat span {
    color: rgba(255,255,255,.68);
    font-size: 12px;
    letter-spacing: .04em;
    text-transform: uppercase;
    font-weight: 700;
}
.va-404__stat strong {
    color: #fff;
    font-size: 14px;
    letter-spacing: .03em;
}
@keyframes va404-shift {
    0%, 79%, 100% { transform: translate3d(0,0,0); filter: none; }
    80% { transform: translate3d(-2px, 0, 0) skewX(-1deg); }
    81% { transform: translate3d(2px, -1px, 0) skewX(1deg); }
    82% { transform: translate3d(-3px, 1px, 0) skewX(-1.6deg); }
    83% { transform: translate3d(1px, 0, 0); }
}
@keyframes va404-glitch-cyan {
    0%, 79%, 100% { transform: translate(2px,0); clip-path: inset(0 0 0 0); }
    80% { transform: translate(8px, -2px); clip-path: inset(4% 0 58% 0); }
    81% { transform: translate(-6px, 2px); clip-path: inset(58% 0 8% 0); }
    82% { transform: translate(4px, -1px); clip-path: inset(30% 0 34% 0); }
    83% { transform: translate(2px,0); clip-path: inset(0 0 0 0); }
}
@keyframes va404-glitch-red {
    0%, 79%, 100% { transform: translate(-2px,0); clip-path: inset(0 0 0 0); }
    80% { transform: translate(-8px, 1px); clip-path: inset(50% 0 6% 0); }
    81% { transform: translate(6px, -2px); clip-path: inset(8% 0 56% 0); }
    82% { transform: translate(-5px, 1px); clip-path: inset(28% 0 30% 0); }
    83% { transform: translate(-2px,0); clip-path: inset(0 0 0 0); }
}
@media (max-width: 640px) {
    .va-404 {
        padding: 18px 10px;
    }
    .va-404__shell {
        border-radius: 16px;
        padding: 18px 12px;
        min-height: calc(100vh - var(--nav, 66px) - 16px);
        grid-template-columns: 1fr;
        gap: 14px;
    }
    .va-404__left {
        align-items: center;
        text-align: center;
    }
    .va-404__btn {
        width: 100%;
    }
    .va-404__actions {
        flex-direction: column;
        justify-content: center;
    }
    .va-404__search {
        grid-template-columns: 1fr;
    }
    .va-404__right {
        grid-template-columns: 1fr;
    }
}
@media (max-width: 980px) {
    .va-404__shell {
        grid-template-columns: 1fr;
        min-height: calc(100vh - var(--nav, 66px) - 20px);
    }
    .va-404__left {
        align-items: center;
        text-align: center;
    }
    .va-404__actions {
        justify-content: center;
    }
}
</style>

<?php
get_footer();
