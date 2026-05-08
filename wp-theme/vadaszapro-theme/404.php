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
        <p class="va-404__kicker">Page not found</p>
        <h1 class="va-404__digits" aria-label="404">
            <span>4</span>
            <span>0</span>
            <span>4</span>
        </h1>
        <h2 class="va-404__title">Rossz savba kanyarodtal</h2>
        <p class="va-404__lead">
            A keresett oldal nem erheto el. Ugralj vissza a forgalomba,
            vagy hasznald a keresot es talald meg gyorsan a megfelelo hirdetest.
        </p>

        <form class="va-404__search" action="<?php echo esc_url( home_url( '/' ) ); ?>" method="get">
            <label class="screen-reader-text" for="va-404-s">Kereses</label>
            <input id="va-404-s" type="search" name="s" placeholder="Mit keresel? marka, modell, kulcsszo" value="<?php echo esc_attr( get_search_query() ); ?>">
            <button type="submit">Kereses</button>
        </form>

        <div class="va-404__actions">
            <a class="va-404__btn va-404__btn--primary" href="<?php echo esc_url( home_url( '/va-hirdetes-kereses/' ) ); ?>">Hirdetes keresese</a>
            <a class="va-404__btn va-404__btn--ghost" href="<?php echo esc_url( home_url( '/' ) ); ?>">Vissza a fooldalra</a>
            <a class="va-404__btn va-404__btn--ghost" href="<?php echo esc_url( home_url( '/kapcsolat/' ) ); ?>">Kapcsolat</a>
        </div>

        <div class="va-404__quicklinks" aria-label="Gyors linkek">
            <a href="<?php echo esc_url( home_url( '/va-hirdetes-kereses/' ) ); ?>">Uj hirdetesek</a>
            <a href="<?php echo esc_url( home_url( '/aukcio/' ) ); ?>">Aukciok</a>
            <a href="<?php echo esc_url( home_url( '/fiok/' ) ); ?>">Fiokom</a>
        </div>
    </section>
</main>

<style>
.va-404 {
    min-height: calc(100vh - var(--nav, 66px));
    display: grid;
    place-items: center;
    padding: 40px 14px;
    position: relative;
    overflow: hidden;
    background:
        radial-gradient(circle at 12% 12%, rgba(255,0,0,.18), transparent 40%),
        radial-gradient(circle at 88% 88%, rgba(255,0,0,.12), transparent 36%),
        linear-gradient(180deg, rgba(255,0,0,.04), transparent 34%),
        repeating-linear-gradient(0deg, rgba(255,255,255,.035) 0 1px, transparent 1px 22px),
        repeating-linear-gradient(90deg, rgba(255,255,255,.028) 0 1px, transparent 1px 22px),
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
    top: -120px;
    left: -120px;
    background: rgba(255,0,0,.18);
}
.va-404::after {
    bottom: -140px;
    right: -130px;
    background: rgba(255,0,0,.14);
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
    width: min(980px, 100%);
    border: 1px solid rgba(255,255,255,.16);
    border-radius: 24px;
    background:
        linear-gradient(155deg, rgba(20,20,20,.94), rgba(8,8,8,.98));
    box-shadow: 0 24px 100px rgba(0,0,0,.65), 0 0 0 1px rgba(255,0,0,.18) inset;
    color: #fff;
    padding: clamp(22px, 4vw, 44px);
    text-align: center;
    position: relative;
    overflow: hidden;
    z-index: 1;
}
.va-404__shell::before {
    content: "";
    position: absolute;
    inset: -38% auto auto -24%;
    width: 420px;
    height: 420px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(255,0,0,.24), transparent 65%);
    filter: blur(10px);
    pointer-events: none;
}
.va-404__kicker {
    margin: 0;
    color: rgba(255,255,255,.7);
    font-size: 12px;
    font-weight: 700;
    letter-spacing: .24em;
    text-transform: uppercase;
}
.va-404__digits {
    margin: 8px 0 6px;
    display: flex;
    justify-content: center;
    gap: clamp(8px, 2vw, 16px);
    font-size: clamp(74px, 16vw, 168px);
    line-height: .9;
    font-weight: 900;
    letter-spacing: .02em;
}
.va-404__digits span {
    color: #fff;
    text-shadow: 0 0 30px rgba(255,0,0,.35);
}
.va-404__digits span:nth-child(2) {
    color: #ff2a2a;
    text-shadow: 0 0 42px rgba(255,0,0,.55);
}
.va-404__title {
    margin: 4px 0 12px;
    font-size: clamp(28px, 4.2vw, 50px);
    line-height: 1.05;
    letter-spacing: .01em;
    text-transform: uppercase;
}
.va-404__lead {
    margin: 0 auto;
    max-width: 740px;
    color: rgba(255,255,255,.72);
    font-size: clamp(14px, 2.1vw, 18px);
    line-height: 1.65;
}
.va-404__search {
    margin: 24px auto 0;
    max-width: 700px;
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
    background: linear-gradient(135deg, rgba(255,0,0,.3), rgba(150,0,0,.35));
    color: #fff;
    font-weight: 800;
    letter-spacing: .03em;
    padding: 0 20px;
    cursor: pointer;
    transition: .2s ease;
}
.va-404__search button:hover {
    transform: translateY(-1px);
    background: linear-gradient(135deg, rgba(255,0,0,.42), rgba(170,0,0,.45));
}
.va-404__actions {
    margin-top: 14px;
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
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
.va-404__quicklinks {
    margin-top: 22px;
    display: inline-flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 8px;
}
.va-404__quicklinks a {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 34px;
    padding: 0 12px;
    border-radius: 999px;
    border: 1px solid rgba(255,255,255,.18);
    background: rgba(255,255,255,.03);
    color: rgba(255,255,255,.86);
    text-decoration: none;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: .02em;
    transition: .2s ease;
}
.va-404__quicklinks a:hover {
    color: #fff;
    border-color: rgba(255,0,0,.7);
    background: rgba(255,0,0,.16);
}
@media (max-width: 640px) {
    .va-404 {
        padding: 18px 10px;
    }
    .va-404__shell {
        border-radius: 16px;
        padding: 20px 14px;
    }
    .va-404__digits {
        font-size: clamp(70px, 24vw, 120px);
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
    .va-404__search button {
        width: 100%;
    }
}
</style>

<?php
get_footer();
