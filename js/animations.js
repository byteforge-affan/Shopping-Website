/* ============================================================
   Arts Store — Clean Professional Animations
   Sirf 3 cheezein:
   1. Scroll reveal (halka fade up)
   2. Number count-up (about page stats)
   3. Navbar scroll shadow
   ============================================================ */

(function () {
  'use strict';

  var css = document.createElement('style');
  css.textContent = [
    /* Scroll reveal */
    '[data-as]{opacity:0;transform:translateY(28px);transition:opacity .6s ease,transform .6s ease}',
    '[data-as].as-done{opacity:1;transform:none}',

    /* Button hover — sirf lift */
    '.btn-shop:hover,.btn-submit:hover,.btn-save:hover{transform:translateY(-2px);transition:transform .25s ease,box-shadow .25s ease}',
    '.btn-shop,.btn-submit,.btn-save{transition:transform .25s ease,box-shadow .25s ease}',

    /* Product card hover — sirf lift */
    '.product-card{transition:transform .3s ease,box-shadow .3s ease}',
    '.product-card:hover{transform:translateY(-5px);box-shadow:0 14px 35px rgba(0,0,0,.10)}',
    '.product-thumb img{transition:transform .5s ease}',
    '.product-card:hover .product-thumb img{transform:scale(1.05)}',

    /* Nav icon hover */
    '.nav-icon{transition:transform .25s ease,color .25s ease}',
    '.nav-icon:hover{transform:translateY(-2px);color:#7B5EA7}',

    /* Acc stat hover */
    '.acc-stat{transition:transform .3s ease,box-shadow .3s ease}',
    '.acc-stat:hover{transform:translateY(-3px);box-shadow:0 8px 24px rgba(123,94,167,.12)}',

    /* Sidebar link indent */
    '.acc-nav a,.sidebar a{transition:padding-left .2s ease,color .2s ease,background .2s ease}',
    '.acc-nav a:hover,.sidebar a:hover{padding-left:26px}',

    /* Footer links */
    '.site-footer a{transition:color .2s ease}',
    '.site-footer a:hover{color:#7B5EA7}',

    /* Input focus */
    '.form-control:focus,.fg input:focus,.pf-field input:focus,.input-wrap input:focus{transition:border-color .2s ease,box-shadow .2s ease;box-shadow:0 0 0 3px rgba(123,94,167,.12)}',

    /* Alert */
    '.alert-success,.acc-alert.ok{animation:fadeDown .4s ease both}',
    '@keyframes fadeDown{from{opacity:0;transform:translateY(-10px)}to{opacity:1;transform:none}}',

    /* Reduced motion */
    '@media(prefers-reduced-motion:reduce){*{animation-duration:.01ms!important;transition-duration:.01ms!important}}',
  ].join('');
  document.head.appendChild(css);

  document.addEventListener('DOMContentLoaded', function () {

    /* ── 1. SCROLL REVEAL ──────────────────────── */
    function tag(sel, delay) {
      document.querySelectorAll(sel).forEach(function (el, i) {
        if (el.hasAttribute('data-as')) return;
        el.setAttribute('data-as', 'up');
        el.style.transitionDelay = ((delay || 0) + i * 0.06) + 's';
      });
    }

    tag('.product-card',         0);
    tag('.fc-card',              0.04);
    tag('.cat-row-card',         0);
    tag('.acc-stat',             0.05);
    tag('.stat-card',            0.05);
    tag('.ord-card,.order-card', 0.04);
    tag('.feat-item',            0.05);
    tag('.section-header',       0);
    tag('.acc-panel,.panel',     0);

    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) {
        if (e.isIntersecting) {
          e.target.classList.add('as-done');
          io.unobserve(e.target);
        }
      });
    }, { threshold: 0.10, rootMargin: '0px 0px -20px 0px' });

    document.querySelectorAll('[data-as]').forEach(function (el) {
      io.observe(el);
    });

    /* ── 2. NUMBER COUNT-UP (about page stats) ─ */
    function countUp(el) {
      var text  = el.textContent.trim();
      var num   = parseInt(text.replace(/\D/g, ''), 10);
      var suffix = text.replace(/[\d,]/g, ''); // "+" ya "K" wagera
      if (!num || num < 2) return;

      var duration = 1800;
      var step     = 16;
      var steps    = duration / step;
      var inc      = num / steps;
      var cur      = 0;

      el.textContent = '0' + suffix;

      var iv = setInterval(function () {
        cur += inc;
        if (cur >= num) {
          el.textContent = num.toLocaleString() + suffix;
          clearInterval(iv);
        } else {
          el.textContent = Math.floor(cur).toLocaleString() + suffix;
        }
      }, step);
    }

    /* About page stats — jab visible hon tab chalao */
    var statEls = document.querySelectorAll(
      '.about-stat-num, .stat-number, [class*="stat"] h2, [class*="stat"] h3, ' +
      '.count-num, .number-stat, .big-num'
    );

    /* Generic selector — bade numbers dhundo about section mein */
    if (!statEls.length) {
      document.querySelectorAll('section h2, section h3').forEach(function (el) {
        if (/^\d[\d,+K%]*$/.test(el.textContent.trim())) {
          statEls = document.querySelectorAll('section h2, section h3');
        }
      });
    }

    var countIO = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) {
        if (e.isIntersecting) {
          countUp(e.target);
          countIO.unobserve(e.target);
        }
      });
    }, { threshold: 0.5 });

    statEls.forEach(function (el) {
      /* sirf woh elements jisme sirf number + suffix ho */
      if (/^\d[\d,+K%]*$/.test(el.textContent.trim())) {
        countIO.observe(el);
      }
    });

    /* My Account stat numbers bhi */
    document.querySelectorAll('.acc-stat-num').forEach(function (el) {
      countIO.observe(el);
    });

    /* ── 3. NAVBAR SCROLL SHADOW ───────────────── */
    var nav = document.querySelector('nav.navbar');
    if (nav) {
      window.addEventListener('scroll', function () {
        nav.style.boxShadow = window.scrollY > 40
          ? '0 3px 18px rgba(0,0,0,.10)'
          : '0 2px 10px rgba(0,0,0,.05)';
      }, { passive: true });
    }

    /* ── 4. SCROLL TO TOP VISIBILITY ──────────── */
    var stBtn = document.querySelector('.scroll-top');
    if (stBtn) {
      window.addEventListener('scroll', function () {
        stBtn.style.opacity      = window.scrollY > 300 ? '1' : '0';
        stBtn.style.pointerEvents = window.scrollY > 300 ? 'auto' : 'none';
        stBtn.style.transition   = 'opacity .3s ease';
      }, { passive: true });
    }

  }); // DOMContentLoaded

})();