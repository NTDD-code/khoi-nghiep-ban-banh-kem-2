// ============================================================
//  main.js  -  Lena Bakery  (script loaded at bottom of body)
//  DOM is already ready when this runs - no DOMContentLoaded needed
// ============================================================
(function () {

    // -- 1. REVEAL ANIMATION (Smooth Scroll-driven) -------------------
    var reveals = document.querySelectorAll('.reveal');

    if ('IntersectionObserver' in window) {
        var io = new IntersectionObserver(function (entries, observer) {
            entries.forEach(function (e) {
                if (e.isIntersecting) {
                    e.target.classList.add('in');
                    observer.unobserve(e.target);
                }
            });
        }, {
            threshold: 0.08,
            rootMargin: '0px 0px -30px 0px'
        });

        reveals.forEach(function (el) {
            io.observe(el);
        });
    } else {
        // Fallback for browsers without IntersectionObserver
        function fallbackRevealCheck() {
            var vh = window.innerHeight || document.documentElement.clientHeight;
            reveals.forEach(function (el) {
                var rect = el.getBoundingClientRect();
                if (rect.top <= vh - 40) {
                    el.classList.add('in');
                }
            });
        }
        fallbackRevealCheck();
        window.addEventListener('scroll', fallbackRevealCheck, { passive: true });
        window.addEventListener('resize', fallbackRevealCheck, { passive: true });
    }



    // -- 2. FAQ ACCORDION --------------------------------------------
    document.querySelectorAll('.faq-q').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var item = btn.closest('.faq-item');
            if (item) item.classList.toggle('open');
        });
    });


    // -- 3. ORDER MODAL ----------------------------------------------
    var modal      = document.getElementById('orderModal');
    var closeModal = document.getElementById('closeModal');

    if (modal && closeModal) {
        closeModal.addEventListener('click', function () { modal.classList.remove('active'); });
        modal.addEventListener('click', function (e) {
            if (e.target === modal) modal.classList.remove('active');
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') modal.classList.remove('active');
        });
    }


    // -- 4. COPY MESSAGE BUTTON --------------------------------------
    var copyBtn   = document.getElementById('copyMsgBtn');
    var msgToCopy = document.getElementById('msgToCopy');

    if (copyBtn && msgToCopy) {
        var originalBtnHtml = copyBtn.innerHTML;
        var checkSvg = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>';

        copyBtn.addEventListener('click', function () {
            var textToCopy = msgToCopy.innerText.trim();
            var showCopied = function () {
                copyBtn.innerHTML = checkSvg + '<span>Da copy!</span>';
                copyBtn.classList.add('copied');
                setTimeout(function () {
                    copyBtn.innerHTML = originalBtnHtml;
                    copyBtn.classList.remove('copied');
                }, 2000);
            };

            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(textToCopy).then(showCopied).catch(function () { fallbackCopy(textToCopy, showCopied); });
            } else {
                fallbackCopy(textToCopy, showCopied);
            }
        });

        function fallbackCopy(text, cb) {
            var ta = document.createElement('textarea');
            ta.value = text;
            ta.style.cssText = 'position:fixed;opacity:0;pointer-events:none';
            document.body.appendChild(ta);
            ta.select();
            try { document.execCommand('copy'); cb(); } catch (err) { console.warn('Copy failed', err); }
            document.body.removeChild(ta);
        }
    }


    // -- 5. HEADER SCROLL / MOBILE NAV ------------------------------
    var header = document.querySelector('header');
    var toggle = document.querySelector('.nav-menu-toggle');
    var links  = document.querySelectorAll('.nav-links a');

    if (header) {
        var updateHeader = function () {
            header.classList.toggle('scrolled', window.scrollY > 8);
        };
        updateHeader();
        window.addEventListener('scroll', updateHeader, { passive: true });

        if (toggle) {
            toggle.addEventListener('click', function () {
                var open = header.classList.toggle('menu-open');
                toggle.setAttribute('aria-expanded', String(open));
                toggle.setAttribute('aria-label', open ? 'Dong menu' : 'Mo menu');
            });
        }

        links.forEach(function (link) {
            link.addEventListener('click', function () {
                header.classList.remove('menu-open');
                if (toggle) {
                    toggle.setAttribute('aria-expanded', 'false');
                    toggle.setAttribute('aria-label', 'Mo menu');
                }
            });
        });
    }


    // -- 6. MENU FLAVOR FILTER ---------------------------------------
    document.querySelectorAll('.mff-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.mff-btn').forEach(function (b) { b.classList.remove('active'); });
            btn.classList.add('active');
            var flavor = btn.dataset.flavor;
            document.querySelectorAll('.menu-card').forEach(function (card) {
                var flavors = card.dataset.flavors || '';
                card.classList.toggle('menu-card-hidden', !(!flavor || flavors.includes(flavor)));
            });
        });
    });


    // -- 7. IN-APP MESSAGING FORM ------------------------------------
    var ibForm = document.getElementById('ibForm');
    if (ibForm) {
        ibForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            var btn     = document.getElementById('ibSubmitBtn');
            var btnText = document.getElementById('ibBtnText');
            var spinner = document.getElementById('ibBtnSpinner');
            var success = document.getElementById('ibSuccess');
            var error   = document.getElementById('ibError');

            var name    = document.getElementById('ibName').value.trim();
            var phone   = document.getElementById('ibPhone').value.trim();
            var content = document.getElementById('ibContent').value.trim();

            if (error)   error.style.display = 'none';
            if (success) success.style.display = 'none';

            if (!name)    { if (error) { error.style.display = ''; error.textContent = 'Vui long nhap ten'; } return; }
            if (!content) { if (error) { error.style.display = ''; error.textContent = 'Vui long nhap noi dung tin nhan'; } return; }

            if (btn)     btn.disabled = true;
            if (btnText) btnText.style.display = 'none';
            if (spinner) spinner.style.display = '';

            try {
                var res  = await fetch('api/send-message.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ sender_name: name, sender_phone: phone, content: content }),
                });
                var json = await res.json();
                if (json.ok) {
                    ibForm.reset();
                    if (success) success.style.display = '';
                } else {
                    if (error) { error.style.display = ''; error.textContent = json.error || 'Loi he thong, vui long thu lai'; }
                }
            } catch (err) {
                if (error) { error.style.display = ''; error.textContent = 'Loi ket noi mang, vui long thu lai'; }
            } finally {
                if (btn)     btn.disabled = false;
                if (btnText) btnText.style.display = '';
                if (spinner) spinner.style.display = 'none';
            }
        });
    }

})();
