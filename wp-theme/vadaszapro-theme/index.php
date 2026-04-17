<?php
/**
 * index.php – Főoldal / fallback template
 */
get_header(); ?>

<!-- HERO szekció (csak főoldalon) -->
<?php if ( is_front_page() ): ?>
<div class="va-hero" style="margin: -28px -20px 28px;">
    <div class="va-hero__title">Magyarország <span>vadászati</span> apróhirdetési oldala</div>
    <div class="va-hero__sub">Fegyverek, lőszerek, optikák, felszerelések – vásárolj és adj el!</div>

    <div class="va-hero__search">
        <input type="text" placeholder="Mit keresel? (pl. Beretta, 12/70, Blaser...)" id="va-hero-search">
        <button onclick="window.location='<?php echo esc_url(home_url('/va-hirdetes-kereses')); ?>?s='+document.getElementById('va-hero-search').value">Keresés</button>
    </div>

    <div class="va-hero__stats">
        <?php
        $stats = [
            [ 'num' => (int) wp_count_posts('va_listing')->publish, 'label' => 'Aktív hirdetés' ],
            [ 'num' => (int) wp_count_posts('va_auction')->publish,  'label' => 'Futó aukció' ],
            [ 'num' => (int) count_users()['total_users'],            'label' => 'Regisztrált felhasználó' ],
        ];
        foreach ($stats as $stat): ?>
        <div class="va-hero__stat">
            <span class="va-hero__stat-num"><?php echo number_format($stat['num'], 0, ',', ' '); ?></span>
            <span class="va-hero__stat-label"><?php echo esc_html($stat['label']); ?></span>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- ═══ HOLDNAPTÁR ══════════════════════════════════════════════════════ -->
<?php if ( is_front_page() ): ?>
<section class="va-moon" id="holdnaptar">
  <div class="va-moon__hd">
    <span class="va-moon__title">🌙 Holdnaptár</span>
    <span class="va-moon__time" id="mTime">Budapest…</span>
  </div>
  <div class="va-moon__bd">
    <div class="va-moon__canvas-wrap">
      <canvas id="mCanvas" width="140" height="140"></canvas>
      <div class="va-moon__illum" id="mIllum">–</div>
    </div>
    <div class="va-moon__info">
      <div class="va-moon__phase" id="mPhase">–</div>
      <div class="va-moon__age"   id="mAge">–</div>
      <div class="va-moon__next"  id="mNext"></div>
    </div>
  </div>
</section>
<script>
(function(){
  var PI=Math.PI,sin=Math.sin,cos=Math.cos,tan=Math.tan,
      asin=Math.asin,atan=Math.atan2,acos=Math.acos,rad=PI/180,
      dayMs=86400000,J1970=2440588,J2000=2451545,e=rad*23.4397;
  function toDays(d){return d.valueOf()/dayMs-.5+J1970-J2000;}
  function ra(l,b){return atan(sin(l)*cos(e)-tan(b)*sin(e),cos(l));}
  function dec(l,b){return asin(sin(b)*cos(e)+cos(b)*sin(e)*sin(l));}
  function sunC(d){
    var M=rad*(357.5291+0.98560028*d),
        C=rad*(1.9148*sin(M)+0.02*sin(2*M)+0.0003*sin(3*M)),
        L=M+C+rad*102.9372+PI;
    return{dec:dec(L,0),ra:ra(L,0)};
  }
  function moonC(d){
    var L=rad*(218.316+13.176396*d),M=rad*(134.963+13.064993*d),
        F=rad*(93.272+13.22935*d),
        l=L+rad*6.289*sin(M),b=rad*5.128*sin(F),dt=385001-20905*cos(M);
    return{ra:ra(l,b),dec:dec(l,b),dist:dt};
  }
  function moonIllum(date){
    var d=toDays(date),s=sunC(d),m=moonC(d),sd=149598000,
        phi=acos(Math.max(-1,Math.min(1,sin(s.dec)*sin(m.dec)+cos(s.dec)*cos(m.dec)*cos(s.ra-m.ra)))),
        inc=atan(sd*sin(phi),m.dist-sd*cos(phi)),
        ang=atan(cos(s.dec)*sin(s.ra-m.ra),sin(s.dec)*cos(m.dec)-cos(s.dec)*sin(m.dec)*cos(s.ra-m.ra));
    return{frac:(1+cos(inc))/2,phase:.5+.5*inc*(ang<0?-1:1)/PI};
  }
  var SYN=29.53058867;
  function nextPhase(now,target){
    var p=moonIllum(now).phase,days=((target-p+1)%1)*SYN;
    if(days<0.5)days+=SYN;
    return new Date(now.getTime()+days*dayMs);
  }
  /* ── Canvas holdrajz ── */
  function draw(cv,phase,frac){
    var W=cv.width,H=cv.height,cx=W/2,cy=H/2,R=W/2-8,
        ctx=cv.getContext('2d');
    ctx.clearRect(0,0,W,H);
    /* hold körüli ragyogás teliholdnál */
    if(frac>0.5){
      var gw=ctx.createRadialGradient(cx,cy,R*.6,cx,cy,R*2);
      gw.addColorStop(0,'rgba(255,190,60,'+(0.14*frac)+')');
      gw.addColorStop(1,'rgba(0,0,0,0)');
      ctx.fillStyle=gw;ctx.fillRect(0,0,W,H);
    }
    /* sötét alap */
    ctx.beginPath();ctx.arc(cx,cy,R,0,2*PI);
    var dg=ctx.createRadialGradient(cx-R*.3,cy-R*.3,0,cx,cy,R);
    dg.addColorStop(0,'#2d0808');dg.addColorStop(1,'#0c0000');
    ctx.fillStyle=dg;ctx.fill();
    /* megvilágított rész */
    if(frac>0.02&&frac<0.98){
      ctx.save();ctx.beginPath();ctx.arc(cx,cy,R,0,2*PI);ctx.clip();
      var tx=R*Math.abs(cos(phase*2*PI)),
          wax=phase<=0.5,gibb=phase>=0.25&&phase<=0.75,tcw=!gibb;
      ctx.beginPath();ctx.moveTo(cx,cy-R);
      ctx.arc(cx,cy,R,-PI/2,PI/2,!wax);
      ctx.ellipse(cx,cy,tx,R,0,PI/2,-PI/2,tcw);
      ctx.closePath();
      var lg=ctx.createRadialGradient(cx-R*.2,cy-R*.25,R*.05,cx+R*.18,cy+R*.18,R);
      lg.addColorStop(0,'#fffaf2');lg.addColorStop(.3,'#ffd880');
      lg.addColorStop(.72,'#c87010');lg.addColorStop(1,'#804000');
      ctx.fillStyle=lg;ctx.fill();
      /* kráterek */
      ctx.globalAlpha=.17;ctx.fillStyle='#6a2800';
      [[.21,.17,.085],[-.26,-.08,.065],[.09,-.29,.05],[-.11,.31,.06]].forEach(function(c){
        ctx.beginPath();ctx.arc(cx+R*c[0],cy+R*c[1],R*c[2],0,2*PI);ctx.fill();
      });
      ctx.globalAlpha=1;ctx.restore();
    }
    /* piros keret */
    ctx.beginPath();ctx.arc(cx,cy,R+1.5,0,2*PI);
    ctx.strokeStyle='rgba(255,40,0,'+(0.12+frac*.22)+')';
    ctx.lineWidth=1.5;ctx.stroke();
  }
  /* ── Fázisnevek magyarul ── */
  var PH=[[.0625,'🌑 Újhold'],[.1875,'🌒 Növekvő sarló'],[.3125,'🌓 Első negyed'],
          [.4375,'🌔 Növekvő domború'],[.5625,'🌕 Telihold'],[.6875,'🌖 Fogyó domború'],
          [.8125,'🌗 Utolsó negyed'],[.9375,'🌘 Fogyó sarló'],[1.01,'🌑 Újhold']];
  function phName(p){return(PH.find(function(x){return p<x[0]})||[0,'–'])[1];}
  function until(d,now){
    var h=(d-now)/3600000;
    if(h<1)return 'hamarosan';
    if(h<24)return Math.round(h)+'\u00a0óra múlva';
    return Math.round(h/24)+'\u00a0nap múlva';
  }
  /* ── Frissítés ── */
  function update(){
    var now=new Date(),il=moonIllum(now),ph=il.phase,fr=il.frac;
    var cv=document.getElementById('mCanvas');if(!cv)return;
    draw(cv,ph,fr);
    document.getElementById('mPhase').textContent=phName(ph);
    document.getElementById('mAge').textContent=(ph*SYN).toFixed(1)+' napos hold';
    document.getElementById('mIllum').textContent=Math.round(fr*100)+'%';
    document.getElementById('mTime').textContent=now.toLocaleString('hu-HU',{
      timeZone:'Europe/Budapest',year:'numeric',month:'2-digit',day:'2-digit',
      hour:'2-digit',minute:'2-digit'
    });
    var nx=[{i:'🌑',l:'Újhold',f:0},{i:'🌓',l:'Első negyed',f:.25},
            {i:'🌕',l:'Telihold',f:.5},{i:'🌗',l:'Utolsó negyed',f:.75}];
    document.getElementById('mNext').innerHTML=nx.map(function(n){
      return '<div class="va-moon__nr"><span>'+n.i+'</span><span>'+n.l+'</span>'
            +'<span class="va-moon__nrd">'+until(nextPhase(now,n.f),now)+'</span></div>';
    }).join('');
  }
  document.addEventListener('DOMContentLoaded',function(){update();setInterval(update,60000);});
})();
</script>
<?php endif; ?>

<!-- Legújabb hirdetések -->
<?php if ( is_front_page() ): ?>
<div style="margin-bottom:24px;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
        <h2 style="font-size:18px;font-weight:800;">🆕 Legújabb hirdetések</h2>
        <?php $search = get_page_by_path('va-hirdetes-kereses'); ?>
        <?php if ($search): ?>
            <a href="<?php echo esc_url(get_permalink($search)); ?>" style="color:#ff0000;font-size:13px;text-decoration:none;">Összes →</a>
        <?php endif; ?>
    </div>

    <?php $latest = new WP_Query(['post_type' => 'va_listing', 'post_status' => 'publish', 'posts_per_page' => 8, 'orderby' => 'date', 'order' => 'DESC']); ?>
    <?php if ($latest->have_posts()): ?>
        <div class="va-grid">
            <?php while ($latest->have_posts()): $latest->the_post();
                if (class_exists('VA_Shortcodes')) va_template('listing/card', ['post' => get_post()]);
            endwhile; wp_reset_postdata(); ?>
        </div>
    <?php endif; ?>
</div>

<!-- Kiemelt hirdetések -->
<?php $featured = new WP_Query(['post_type' => 'va_listing', 'post_status' => 'publish', 'posts_per_page' => 4, 'meta_key' => 'va_featured', 'meta_value' => '1']);
if ($featured->have_posts()): ?>
<div style="margin-bottom:24px;">
    <h2 style="font-size:18px;font-weight:800;margin-bottom:16px;">⭐ Kiemelt hirdetések</h2>
    <div class="va-grid">
        <?php while ($featured->have_posts()): $featured->the_post();
            va_template('listing/card', ['post' => get_post()]);
        endwhile; wp_reset_postdata(); ?>
    </div>
</div>
<?php endif; ?>

<!-- Futó aukciók -->
<?php $auctions = new WP_Query(['post_type' => 'va_auction', 'post_status' => 'publish', 'posts_per_page' => 4, 'orderby' => 'date', 'order' => 'DESC']);
if ($auctions->have_posts()): ?>
<div style="margin-bottom:24px;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
        <h2 style="font-size:18px;font-weight:800;">🔨 Futó aukciók</h2>
        <?php $ap = get_page_by_path('va-aukciok'); ?>
        <?php if ($ap): ?><a href="<?php echo esc_url(get_permalink($ap)); ?>" style="color:#ff0000;font-size:13px;text-decoration:none;">Összes →</a><?php endif; ?>
    </div>
    <div class="va-grid">
        <?php while ($auctions->have_posts()): $auctions->the_post();
            va_template('listing/card', ['post' => get_post()]);
        endwhile; wp_reset_postdata(); ?>
    </div>
</div>
<?php endif; ?>

<?php else: ?>
    <!-- Archívum / single / page tartalom -->
    <?php if ( have_posts() ): while ( have_posts() ): the_post(); ?>
        <div class="va-wrap">
            <?php the_content(); ?>
        </div>
    <?php endwhile; endif; ?>
<?php endif; ?>

<?php get_footer(); ?>
