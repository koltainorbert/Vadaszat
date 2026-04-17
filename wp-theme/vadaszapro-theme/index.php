<?php
/**
 * index.php – Főoldal / fallback template
 */
get_header(); ?>

<?php if ( is_front_page() ): ?>
<div class="va-home-layout">

<!-- ═══ BAL SIDEBAR — csak holdnaptár ═════════════════════════════ -->
<aside class="va-home-sidebar">
<section class="va-moon" id="holdnaptar">
  <div class="va-moon__hd">
    <span class="va-moon__title">🌙 Holdnaptár</span>
    <span class="va-moon__time" id="mTime">Budapest…</span>
  </div>
  <div class="va-moon__bd">
    <div class="va-moon__canvas-wrap">
      <canvas id="mCanvas" width="208" height="208"></canvas>
      <div class="va-moon__illum" id="mIllum">–</div>
    </div>
  </div>
  <div class="va-moon__info">
    <div class="va-moon__phase" id="mPhase">–</div>
    <div class="va-moon__age"   id="mAge">–</div>
    <div class="va-moon__next"  id="mNext"></div>
  </div>
</section>
</aside>
  <div class="va-moon__info">
    <div class="va-moon__phase" id="mPhase">–</div>
    <div class="va-moon__age"   id="mAge">–</div>
    <div class="va-moon__next"  id="mNext"></div>
  </div>
</section>
</aside>
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
  /* ── Canvas holdrajz — élethű ── */
  function draw(cv,phase,frac){
    var W=cv.width,H=cv.height,cx=W/2,cy=H/2,R=W/2-7,
        ctx=cv.getContext('2d');
    ctx.clearRect(0,0,W,H);
    /* telihold ezüst ragyogás */
    if(frac>0.55){
      var gw=ctx.createRadialGradient(cx,cy,R*.65,cx,cy,R*2.2);
      var ga=(frac-.55)/.45*.22;
      gw.addColorStop(0,'rgba(210,215,190,'+ga+')');
      gw.addColorStop(.5,'rgba(180,190,160,'+(ga*.4)+')');
      gw.addColorStop(1,'rgba(0,0,0,0)');
      ctx.fillStyle=gw;ctx.fillRect(0,0,W,H);
    }
    /* sötét (árnyék) oldal: sötétszürke */
    ctx.beginPath();ctx.arc(cx,cy,R,0,2*PI);
    var dg=ctx.createRadialGradient(cx,cy,0,cx,cy,R);
    dg.addColorStop(0,'#1e1e2c');dg.addColorStop(1,'#08080f');
    ctx.fillStyle=dg;ctx.fill();
    /* megvilágított rész */
    if(frac>0.015&&frac<0.985){
      ctx.save();ctx.beginPath();ctx.arc(cx,cy,R,0,2*PI);ctx.clip();
      var tx=R*Math.abs(cos(phase*2*PI)),
          wax=phase<=0.5,gibb=phase>=0.25&&phase<=0.75,tcw=!gibb;
      ctx.beginPath();ctx.moveTo(cx,cy-R);
      ctx.arc(cx,cy,R,-PI/2,PI/2,!wax);
      ctx.ellipse(cx,cy,tx,R,0,PI/2,-PI/2,tcw);
      ctx.closePath();
      /* élethű szürke-fehér hold gradient (limb darkening) */
      var lg=ctx.createRadialGradient(cx-R*.12,cy-R*.18,R*.02,cx+R*.08,cy+R*.1,R*1.08);
      lg.addColorStop(0,'#f0f0e8');   /* fényes közép */
      lg.addColorStop(.22,'#dcdcd2');
      lg.addColorStop(.5,'#b8b8ae');
      lg.addColorStop(.78,'#888880');  /* limb darkening */
      lg.addColorStop(1,'#50504a');
      ctx.fillStyle=lg;ctx.fill();
      /* Mare — sötét holdtengerek (reális pozíció) */
      ctx.globalCompositeOperation='multiply';
      /* Mare Imbrium (bal felső nagy folt) */
      ctx.beginPath();ctx.ellipse(cx-R*.14,cy-R*.1,R*.3,R*.24,-.25,0,2*PI);
      ctx.fillStyle='rgba(100,100,92,.55)';ctx.fill();
      /* Mare Serenitatis */
      ctx.beginPath();ctx.ellipse(cx+R*.12,cy-R*.2,R*.18,R*.16,0,0,2*PI);
      ctx.fillStyle='rgba(110,110,100,.5)';ctx.fill();
      /* Mare Tranquillitatis */
      ctx.beginPath();ctx.ellipse(cx+R*.22,cy-.02*R,R*.2,R*.16,.15,0,2*PI);
      ctx.fillStyle='rgba(105,105,95,.48)';ctx.fill();
      /* Mare Crisium (kis kerek) */
      ctx.beginPath();ctx.ellipse(cx+R*.42,cy-R*.22,R*.1,R*.09,0,0,2*PI);
      ctx.fillStyle='rgba(100,100,90,.55)';ctx.fill();
      /* Mare Nubium / Nectaris (jobb alsó) */
      ctx.beginPath();ctx.ellipse(cx+R*.08,cy+R*.3,R*.16,R*.12,.1,0,2*PI);
      ctx.fillStyle='rgba(108,108,98,.45)';ctx.fill();
      ctx.globalCompositeOperation='source-over';
      /* kráterek — halvány karikák */
      ctx.strokeStyle='rgba(60,60,55,.35)';ctx.lineWidth=1.2;
      [[-.28,.14,.09],[.18,.32,.07],[-.08,.28,.055],[.3,-.12,.065],[-.15,-.32,.05]].forEach(function(c){
        ctx.beginPath();ctx.arc(cx+R*c[0],cy+R*c[1],R*c[2],0,2*PI);ctx.stroke();
      });
      /* terminátor élénkítő highlight */
      ctx.globalAlpha=.08;
      ctx.fillStyle='#ffffff';
      ctx.beginPath();ctx.moveTo(cx,cy-R);
      ctx.arc(cx,cy,R,-PI/2,PI/2,!wax);
      ctx.ellipse(cx,cy,tx*.92,R*.98,0,PI/2,-PI/2,tcw);
      ctx.closePath();ctx.fill();
      ctx.globalAlpha=1;ctx.restore();
    }
    /* telihold: egész kör élethű */
    if(frac>=0.985){
      ctx.save();ctx.beginPath();ctx.arc(cx,cy,R,0,2*PI);ctx.clip();
      var flg=ctx.createRadialGradient(cx-R*.1,cy-R*.15,0,cx,cy,R*1.05);
      flg.addColorStop(0,'#f0f0e8');flg.addColorStop(.4,'#d0d0c4');
      flg.addColorStop(.78,'#909088');flg.addColorStop(1,'#505048');
      ctx.fillStyle=flg;ctx.fill();
      ctx.globalCompositeOperation='multiply';
      ctx.beginPath();ctx.ellipse(cx-R*.14,cy-R*.1,R*.3,R*.24,-.25,0,2*PI);
      ctx.fillStyle='rgba(100,100,92,.55)';ctx.fill();
      ctx.beginPath();ctx.ellipse(cx+R*.12,cy-R*.2,R*.18,R*.16,0,0,2*PI);
      ctx.fillStyle='rgba(110,110,100,.5)';ctx.fill();
      ctx.beginPath();ctx.ellipse(cx+R*.22,cy,R*.2,R*.16,.15,0,2*PI);
      ctx.fillStyle='rgba(105,105,95,.48)';ctx.fill();
      ctx.beginPath();ctx.ellipse(cx+R*.42,cy-R*.22,R*.1,R*.09,0,0,2*PI);
      ctx.fillStyle='rgba(100,100,90,.55)';ctx.fill();
      ctx.globalCompositeOperation='source-over';
      ctx.strokeStyle='rgba(60,60,55,.3)';ctx.lineWidth=1.2;
      [[-.28,.14,.09],[.18,.32,.07],[-.08,.28,.055],[.3,-.12,.065]].forEach(function(c){
        ctx.beginPath();ctx.arc(cx+R*c[0],cy+R*c[1],R*c[2],0,2*PI);ctx.stroke();
      });
      ctx.restore();
    }
    /* újhold: nagyon sötét + halvány earthshine */
    if(frac<=0.015){
      ctx.save();ctx.beginPath();ctx.arc(cx,cy,R,0,2*PI);ctx.clip();
      var eg=ctx.createRadialGradient(cx,cy,0,cx,cy,R);
      eg.addColorStop(0,'#181820');eg.addColorStop(1,'#060609');
      ctx.fillStyle=eg;ctx.fill();
      ctx.globalAlpha=.06;ctx.fillStyle='#4060a0';
      ctx.beginPath();ctx.arc(cx,cy,R,0,2*PI);ctx.fill();
      ctx.globalAlpha=1;ctx.restore();
    }
    /* ezüst keret */
    ctx.beginPath();ctx.arc(cx,cy,R,0,2*PI);
    ctx.strokeStyle='rgba(160,165,145,'+(0.15+frac*.25)+')';
    ctx.lineWidth=1;ctx.stroke();
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

<!-- FŐ TARTALOM -->
<main class="va-home-main">

<!-- HERO -->
<div class="va-hero">
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

<!-- HIRDETÉSEK -->
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

  </main><!-- va-home-main -->
</div><!-- va-home-layout -->

<?php else: ?>
    <!-- Archívum / single / page tartalom -->
    <?php if ( have_posts() ): while ( have_posts() ): the_post(); ?>
        <div class="va-wrap">
            <?php the_content(); ?>
        </div>
    <?php endwhile; endif; ?>
<?php endif; ?>

<?php get_footer(); ?>
