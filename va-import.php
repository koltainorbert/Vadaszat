<?php
/**
 * VadászApró – Egyszer használatos beállítás importáló
 * Használat: másold a WP gyökerébe, nyisd meg böngészőben, majd TÖRÖLD!
 */

define( 'ABSPATH_CHECK', true );
require_once __DIR__ . '/wp-load.php';

if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( 'Nincs jogosultságod. Először jelentkezz be adminként: <a href="' . admin_url() . '">Admin</a>' );
}

$json_file = __DIR__ . '/va-settings-import.json';
if ( ! file_exists( $json_file ) ) {
    wp_die( 'Hiányzó fájl: <code>va-settings-import.json</code> – másold a WP gyökerébe!' );
}

$raw = file_get_contents( $json_file );
// UTF-8 BOM eltávolítás
$raw = ltrim( $raw, "\xEF\xBB\xBF" );
$data = json_decode( $raw, true );
if ( ! $data ) {
    wp_die( 'Hibás JSON fájl. json_last_error: ' . json_last_error_msg() );
}

$log = [];

/* ── 1. Opciók importálása ──────────────────────────── */
$skip_keys = [ 'siteurl', 'home', 'admin_email', 'blogname', 'blogdescription' ];
$count_opts = 0;
foreach ( $data['options'] as $key => $value ) {
    if ( in_array( $key, $skip_keys, true ) ) continue;
    $val = is_array( $value ) ? wp_json_encode( $value ) : $value;
    update_option( $key, $val, false );
    $count_opts++;
}
$log[] = "✅ Opciók: $count_opts db importálva";

/* ── 2. Taxonómiák importálása ──────────────────────── */
foreach ( $data['taxonomies'] ?? [] as $taxonomy => $terms ) {
    $count_tax = 0;
    $slug_to_id = [];

    // Először a szülő nélküli termek
    foreach ( $terms as $term ) {
        if ( ! empty( $term['parent_slug'] ) ) continue;
        $existing = get_term_by( 'slug', $term['slug'], $taxonomy );
        if ( $existing ) {
            $slug_to_id[ $term['slug'] ] = $existing->term_id;
        } else {
            $result = wp_insert_term( html_entity_decode( $term['name'], ENT_QUOTES | ENT_HTML5, 'UTF-8' ), $taxonomy, [
                'slug'        => $term['slug'],
                'description' => $term['description'] ?? '',
            ] );
            if ( ! is_wp_error( $result ) ) {
                $slug_to_id[ $term['slug'] ] = $result['term_id'];
                $count_tax++;
            }
        }
    }

    // Majd a gyerek termek
    foreach ( $terms as $term ) {
        if ( empty( $term['parent_slug'] ) ) continue;
        $existing = get_term_by( 'slug', $term['slug'], $taxonomy );
        $parent_id = $slug_to_id[ $term['parent_slug'] ] ?? 0;
        if ( $existing ) {
            $slug_to_id[ $term['slug'] ] = $existing->term_id;
            if ( $parent_id && $existing->parent != $parent_id ) {
                wp_update_term( $existing->term_id, $taxonomy, [ 'parent' => $parent_id ] );
            }
        } else {
            $result = wp_insert_term( html_entity_decode( $term['name'], ENT_QUOTES | ENT_HTML5, 'UTF-8' ), $taxonomy, [
                'slug'        => $term['slug'],
                'description' => $term['description'] ?? '',
                'parent'      => $parent_id,
            ] );
            if ( ! is_wp_error( $result ) ) {
                $slug_to_id[ $term['slug'] ] = $result['term_id'];
                $count_tax++;
            }
        }
    }
    $log[] = "✅ Taxonómia <code>$taxonomy</code>: $count_tax új term";
}

/* ── 3. Oldalak importálása ─────────────────────────── */
$count_pages = 0;
foreach ( $data['pages'] ?? [] as $page ) {
    $existing = get_page_by_path( $page['slug'], OBJECT, 'page' );
    if ( $existing ) {
        $log[] = "⏭️ Oldal már létezik: <code>{$page['slug']}</code>";
        continue;
    }
    $result = wp_insert_post( [
        'post_title'   => $page['title'],
        'post_name'    => $page['slug'],
        'post_content' => $page['content'] ?? '',
        'post_excerpt' => $page['excerpt'] ?? '',
        'post_status'  => $page['status'] ?? 'publish',
        'post_type'    => 'page',
    ] );
    if ( $result && ! is_wp_error( $result ) ) {
        $count_pages++;
        $log[] = "✅ Oldal létrehozva: <code>{$page['slug']}</code>";
    }
}

/* ── 4. Rewrite flush ───────────────────────────────── */
flush_rewrite_rules( true );
$log[] = "✅ Rewrite szabályok frissítve";

?><!DOCTYPE html>
<html lang="hu">
<head><meta charset="UTF-8"><title>VA Import</title>
<style>
body { font-family: monospace; background: #111; color: #eee; padding: 40px; }
h1 { color: #ff4444; }
.ok { color: #4caf50; }
.skip { color: #888; }
</style>
</head>
<body>
<h1>VadászApró – Import kész</h1>
<?php foreach ( $log as $line ) echo "<p>$line</p>"; ?>
<hr>
<p style="color:#ff4444;"><strong>⚠️ FONTOS: Töröld a <code>va-import.php</code> és <code>va-settings-import.json</code> fájlokat a WP gyökérből!</strong></p>
<p><a href="<?php echo admin_url('themes.php'); ?>" style="color:#ff4444">→ Témák aktiválása (Admin)</a></p>
<p><a href="<?php echo admin_url('plugins.php'); ?>" style="color:#ff4444">→ Bővítmények (Admin)</a></p>
</body>
</html>
