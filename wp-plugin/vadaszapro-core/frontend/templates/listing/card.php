<?php
/**
 * Template: Hirdetés kártya (lista elem)
 * Változók: $post (WP_Post)
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$post_id   = $post->ID;
$price     = get_post_meta( $post_id, 'va_price', true );
$price_type= get_post_meta( $post_id, 'va_price_type', true ) ?: 'fixed';
$location  = get_post_meta( $post_id, 'va_location', true );
$views_base = 30 + ( $post_id % 70 );
$views      = intval( get_post_meta( $post_id, 'va_views', true ) ) + $views_base;
$featured  = get_post_meta( $post_id, 'va_featured', true ) === '1';
$is_auction= $post->post_type === 'va_auction';
$categories= get_the_terms( $post_id, 'va_category' );
$county    = get_the_terms( $post_id, 'va_county' );
$watching  = va_user_watches( $post_id );
?>
<div class="va-card va-animate" data-post-id="<?php echo esc_attr( $post_id ); ?>">

    <?php if ( $featured ): ?>
        <span class="va-card__badge va-card__badge--featured">⭐ Kiemelt</span>
    <?php elseif ( $is_auction ): ?>
        <span class="va-card__badge">🔨 Aukció</span>
    <?php endif; ?>

    <?php if ( is_user_logged_in() ): ?>
    <button class="va-card__watchlist<?php echo $watching ? ' active' : ''; ?>"
            data-post-id="<?php echo esc_attr( $post_id ); ?>"
            title="<?php echo $watching ? 'Eltávolítás kedvencekből' : 'Hozzáadás kedvencekhez'; ?>">
        ♥
    </button>
    <?php endif; ?>

    <a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>">
        <?php if ( has_post_thumbnail( $post_id ) ): ?>
            <?php echo get_the_post_thumbnail( $post_id, 'medium', [ 'class' => 'va-card__thumb' ] ); ?>
        <?php else: ?>
            <div class="va-card__thumb-placeholder">🎯</div>
        <?php endif; ?>
    </a>

    <div class="va-card__body">
        <h3 class="va-card__title">
            <a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>"><?php the_title(); ?></a>
        </h3>

        <?php if ( $is_auction ): ?>
            <?php $cur_bid = get_post_meta( $post_id, 'va_current_bid', true ); ?>
            <?php $start   = get_post_meta( $post_id, 'va_start_price', true ); ?>
            <div class="va-card__price">
                <?php echo esc_html( $cur_bid
                    ? number_format( (float) $cur_bid, 0, ',', ' ' ) . ' Ft'
                    : number_format( (float) $start, 0, ',', ' ' ) . ' Ft (kikiáltási)' ); ?>
            </div>
            <div class="va-card__meta">
                <span class="va-card__meta-item">⏱ <?php echo va_auction_countdown( $post_id ); ?></span>
                <span class="va-card__meta-item">🔨 <?php echo esc_html( get_post_meta( $post_id, 'va_bid_count', true ) ?: 0 ); ?> licit</span>
            </div>
        <?php else: ?>
            <div class="va-card__price"><?php echo esc_html( va_format_price( $price, $price_type ) ); ?></div>
        <?php endif; ?>

        <div class="va-card__meta">
            <?php if ( $categories && ! is_wp_error( $categories ) ): ?>
                <span class="va-card__meta-item">🏷 <?php echo esc_html( $categories[0]->name ); ?></span>
            <?php endif; ?>
            <?php if ( $county && ! is_wp_error( $county ) ): ?>
                <span class="va-card__meta-item">📍 <?php echo esc_html( $county[0]->name ); ?></span>
            <?php endif; ?>
            <?php if ( $location ): ?>
                <span class="va-card__meta-item"><?php echo esc_html( $location ); ?></span>
            <?php endif; ?>
            <span class="va-card__meta-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13" style="vertical-align:-1px;margin-right:2px;"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg><?php echo esc_html( $views ); ?></span>
            <span class="va-card__meta-item">🗓 <?php echo esc_html( get_the_date( 'Y.m.d', $post_id ) ); ?></span>
        </div>
    </div>
</div>
