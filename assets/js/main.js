// Reveal animation
        const reveals = document.querySelectorAll('.reveal');
        const io = new IntersectionObserver((entries) => {
            entries.forEach(e => {
                if (e.isIntersecting) {
                    e.target.classList.add('in');
                    io.unobserve(e.target);
                }
            });
        }, { threshold: 0.15, rootMargin: '0px 0px -40px 0px' });
        reveals.forEach(el => io.observe(el));

        // FAQ: Mỗi câu hỏi hoạt động độc lập
        document.querySelectorAll('.faq-q').forEach(btn => {
            btn.addEventListener('click', () => {
                const item = btn.closest('.faq-item');
                item.classList.toggle('open');
            });
        });

        // Modal
        const modal = document.getElementById('orderModal');
        const closeModal = document.getElementById('closeModal');

        if (closeModal && modal) {
            closeModal.addEventListener('click', () => modal.classList.remove('active'));
            modal.addEventListener('click', (e) => {
                if (e.target === modal) modal.classList.remove('active');
            });
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') modal.classList.remove('active');
            });
        }

        // ====== Chức năng Copy mẫu tin nhắn ======
        const copyBtn = document.getElementById('copyMsgBtn');
        const msgToCopy = document.getElementById('msgToCopy');

        if (copyBtn && msgToCopy) {
            const originalBtnHtml = copyBtn.innerHTML;
            copyBtn.addEventListener('click', () => {
                const textToCopy = msgToCopy.innerText.trim();

                navigator.clipboard.writeText(textToCopy).then(() => {
                    copyBtn.innerHTML = `
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        <span>Đã copy!</span>
                    `;
                    copyBtn.classList.add('copied');

                    setTimeout(() => {
                        copyBtn.innerHTML = originalBtnHtml;
                        copyBtn.classList.remove('copied');
                    }, 2000);
                }).catch(err => {
                    console.error('Lỗi khi copy: ', err);
                    const textArea = document.createElement("textarea");
                    textArea.value = textToCopy;
                    document.body.appendChild(textArea);
                    textArea.select();
                    try {
                        document.execCommand('copy');
                        copyBtn.innerHTML = `
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            <span>Đã copy!</span>
                        `;
                        copyBtn.classList.add('copied');
                        setTimeout(() => {
                            copyBtn.innerHTML = originalBtnHtml;
                            copyBtn.classList.remove('copied');
                        }, 2000);
                    } catch (e) {
                        console.error('Fallback failed', e);
                    }
                    document.body.removeChild(textArea);
                });
            });
        }

(() => {
            const header = document.querySelector("header");
            const toggle = document.querySelector(".nav-menu-toggle");
            const links = document.querySelectorAll(".nav-links a");

            const updateHeader = () => {
                header.classList.toggle("scrolled", window.scrollY > 8);
            };

            updateHeader();
            window.addEventListener("scroll", updateHeader, { passive: true });

            toggle?.addEventListener("click", () => {
                const open = header.classList.toggle("menu-open");
                toggle.setAttribute("aria-expanded", String(open));
                toggle.setAttribute("aria-label", open ? "Đóng menu" : "Mở menu");
            });

            links.forEach((link) => {
                link.addEventListener("click", () => {
                    header.classList.remove("menu-open");
                    toggle?.setAttribute("aria-expanded", "false");
                    toggle?.setAttribute("aria-label", "Mở menu");
                });
            });
        })();

// ====== Menu Flavor Filter ======
document.querySelectorAll('.mff-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.mff-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        const flavor = btn.dataset.flavor;
        document.querySelectorAll('.menu-card').forEach(card => {
            const flavors = card.dataset.flavors || '';
            if (!flavor || flavors.includes(flavor)) {
                card.classList.remove('menu-card-hidden');
            } else {
                card.classList.add('menu-card-hidden');
            }
        });
    });
});

// ====== In-App Messaging Form ======
const ibForm = document.getElementById('ibForm');
if (ibForm) {
    ibForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn     = document.getElementById('ibSubmitBtn');
        const btnText = document.getElementById('ibBtnText');
        const spinner = document.getElementById('ibBtnSpinner');
        const success = document.getElementById('ibSuccess');
        const error   = document.getElementById('ibError');

        const name    = document.getElementById('ibName').value.trim();
        const phone   = document.getElementById('ibPhone').value.trim();
        const content = document.getElementById('ibContent').value.trim();

        error.style.display = 'none';
        success.style.display = 'none';

        if (!name) { error.style.display = ''; error.textContent = 'Vui lòng nhập tên'; return; }
        if (!content) { error.style.display = ''; error.textContent = 'Vui lòng nhập nội dung tin nhắn'; return; }

        btn.disabled = true;
        btnText.style.display = 'none';
        spinner.style.display = '';

        try {
            const res  = await fetch('api/send-message.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ sender_name: name, sender_phone: phone, content }),
            });
            const json = await res.json();
            if (json.ok) {
                ibForm.reset();
                success.style.display = '';
            } else {
                error.style.display = '';
                error.textContent = json.error || 'Lỗi hệ thống, vui lòng thử lại';
            }
        } catch (err) {
            error.style.display = '';
            error.textContent = 'Lỗi kết nối mạng, vui lòng thử lại';
        } finally {
            btn.disabled = false;
            btnText.style.display = '';
            spinner.style.display = 'none';
        }
    });
}