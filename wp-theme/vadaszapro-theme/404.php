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
    <section class="va-404__shell">
        <span class="va-404__code">404</span>
        <h1 class="va-404__title">Ez az oldal nem talalhato</h1>
        <p class="va-404__lead">
            A keresett URL nem letezik, vagy at lett helyezve. Menj tovabb a fo oldalon,
            vagy keress ra hirdetesre.
        </p>

        <div class="va-404__actions">
            <a class="va-404__btn va-404__btn--primary" href="<?php echo esc_url( home_url( '/' ) ); ?>">Vissza a fooldalra</a>
            <a class="va-404__btn va-404__btn--ghost" href="<?php echo esc_url( home_url( '/va-hirdetes-kereses/' ) ); ?>">Hirdetes keresese</a>
            <a class="va-404__btn va-404__btn--ghost" href="<?php echo esc_url( home_url( '/kapcsolat/' ) ); ?>">Kapcsolat</a>
        </div>

        <form class="va-404__search" action="<?php echo esc_url( home_url( '/' ) ); ?>" method="get">
            <label class="screen-reader-text" for="va-404-s">Kereses</label>
            <input id="va-404-s" type="search" name="s" placeholder="Kereses az oldalon..." value="<?php echo esc_attr( get_search_query() ); ?>">
            <button type="submit">Kereses</button>
        </form>
    </section>
</main>

<style>
.va-404 {
    min-height: calc(100vh - var(--nav, 66px));
    display: grid;
    place-items: center;
    padding: 36px 14px;
    background:
        radial-gradient(circle at 15% 15%, rgba(255,0,0,.12), transparent 38%),
        radial-gradient(circle at 85% 85%, rgba(255,0,0,.08), transparent 35%),
        repeating-linear-gradient(0deg, rgba(255,255,255,.04) 0 1px, transparent 1px 24px),
        repeating-linear-gradient(90deg, rgba(255,255,255,.035) 0 1px, transparent 1px 24px),
        rgb(6,6,6);
}
.va-404__shell {
    width: min(920px, 100%);
    border: 1px solid rgba(255,255,255,.14);
    border-radius: 22px;
    background:
        linear-gradient(145deg, rgba(15,15,15,.96), rgba(8,8,8,.98));
    box-shadow: 0 20px 80px rgba(0,0,0,.62), 0 0 0 1px rgba(255,0,0,.14) inset;
    color: #fff;
    padding: clamp(22px, 4vw, 42px);
    text-align: center;
    position: relative;
    overflow: hidden;
}
.va-404__shell::before {
    content: "";
    position: absolute;
    inset: -30% auto auto -20%;
    width: 360px;
    height: 360px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(255,0,0,.2), transparent 65%);
    filter: blur(8px);
    pointer-events: none;
}
.va-404__code {
    display: inline-flex;
    padding: 7px 14px;
    border-radius: 999px;
    border: 1px solid rgba(255,0,0,.6);
    background: rgba(255,0,0,.12);
    color: #ff4d4d;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
}
.va-404__title {
    margin: 16px 0 10px;
    font-size: clamp(30px, 4vw, 54px);
    line-height: 1.08;
    letter-spacing: .01em;
}
.va-404__lead {
    margin: 0 auto;
    max-width: 700px;
    color: rgba(255,255,255,.72);
    font-size: clamp(15px, 2.2vw, 18px);
    line-height: 1.6;
}
.va-404__actions {
    margin-top: 26px;
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 10px;
}
.va-404__btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 42px;
    padding: 0 16px;
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
    box-shadow: 0 8px 24px rgba(255,0,0,.26);
}
.va-404__btn--primary:hover {
    filter: brightness(1.08);
    transform: translateY(-1px);
}
.va-404__btn--ghost {
    background: rgba(255,255,255,.04);
    color: #fff;
}
.va-404__btn--ghost:hover {
    border-color: rgba(255,0,0,.7);
    color: #ff4d4d;
}
.va-404__search {
    margin: 26px auto 0;
    max-width: 620px;
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 10px;
}
.va-404__search input {
    height: 46px;
    border-radius: 12px;
    border: 1px solid rgba(255,255,255,.14);
    background: rgba(0,0,0,.35);
    color: #fff;
    padding: 0 14px;
    font-size: 14px;
}
.va-404__search input:focus {
    outline: none;
    border-color: rgba(255,0,0,.8);
    box-shadow: 0 0 0 3px rgba(255,0,0,.16);
}
.va-404__search button {
    height: 46px;
    border-radius: 12px;
    border: 1px solid rgba(255,0,0,.75);
    background: rgba(255,0,0,.18);
    color: #fff;
    font-weight: 700;
    padding: 0 18px;
    cursor: pointer;
    transition: .2s ease;
}
.va-404__search button:hover {
    background: rgba(255,0,0,.28);
}
@media (max-width: 640px) {
    .va-404 {
        padding: 18px 10px;
    }
    .va-404__shell {
        border-radius: 16px;
        padding: 20px 14px;
    }
    .va-404__actions {
        flex-direction: column;
    }
    .va-404__btn {
        width: 100%;
    }
    .va-404__search {
        grid-template-columns: 1fr;
    }
}
</style>

<?php
get_footer();
