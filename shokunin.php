<?php
// ============================================================
// LENA BAKERY — TRIẾT LÝ SHOKUNIN
// ============================================================

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/head.php';
?>

<style>
/* Style riêng cho trang Shokunin để tạo phong cách Editorial/Luxury */
.shokunin-hero {
    padding: 160px 0 80px;
    background: var(--cream);
    text-align: center;
    border-bottom: 1px solid var(--line);
    position: relative;
    overflow: hidden;
}

.shokunin-hero::after {
    content: "職人";
    position: absolute;
    bottom: -30px;
    left: 50%;
    transform: translateX(-50%);
    font-family: var(--font-serif);
    font-size: 15vw;
    color: rgba(139, 58, 42, 0.03);
    font-weight: 900;
    pointer-events: none;
    white-space: nowrap;
}

.shokunin-meta {
    font-size: 11px;
    letter-spacing: 4px;
    text-transform: uppercase;
    color: var(--accent);
    font-weight: 600;
    margin-bottom: 20px;
    display: inline-block;
}

.shokunin-title {
    font-family: var(--font-serif);
    font-size: 3.5rem;
    font-weight: 500;
    line-height: 1.2;
    color: var(--black);
    max-width: 800px;
    margin: 0 auto 30px;
}

.shokunin-title em {
    font-family: var(--font-serif);
    font-style: italic;
    color: var(--accent);
}

.shokunin-subtitle {
    font-size: 1.15rem;
    color: var(--gray);
    max-width: 600px;
    margin: 0 auto;
    line-height: 1.8;
}

.shokunin-content {
    padding: 80px 0;
    background: var(--white);
}

.shokunin-intro-paragraph {
    font-size: 1.3rem;
    line-height: 1.8;
    color: var(--black);
    max-width: 800px;
    margin: 0 auto 60px;
    text-align: center;
    font-family: var(--font-serif);
}

.shokunin-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 40px;
    max-width: 1100px;
    margin: 0 auto 80px;
}

.shokunin-card {
    background: var(--cream);
    border: 1px solid var(--line);
    border-radius: var(--radius);
    padding: 40px 30px;
    transition: var(--transition);
    position: relative;
}

.shokunin-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-hover);
    border-color: var(--gold);
}

.shokunin-card-num {
    font-family: var(--font-serif);
    font-size: 2.5rem;
    color: var(--gold);
    opacity: 0.5;
    margin-bottom: 20px;
    font-style: italic;
}

.shokunin-card-title {
    font-family: var(--font-serif);
    font-size: 1.5rem;
    color: var(--black);
    margin-bottom: 15px;
    font-weight: 500;
}

.shokunin-card-jp {
    display: block;
    font-size: 0.9rem;
    color: var(--accent);
    font-weight: 500;
    margin-top: -10px;
    margin-bottom: 15px;
}

.shokunin-card-desc {
    font-size: 0.95rem;
    color: var(--gray);
    line-height: 1.7;
}

.shokunin-philosophy-quote {
    max-width: 800px;
    margin: 80px auto;
    padding: 40px;
    border-left: 2px solid var(--accent);
    background: var(--cream);
    border-radius: 0 var(--radius) var(--radius) 0;
}

.shokunin-philosophy-quote p {
    font-family: var(--font-serif);
    font-size: 1.4rem;
    line-height: 1.8;
    color: var(--black);
    font-style: italic;
    margin-bottom: 15px;
}

.shokunin-philosophy-quote cite {
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 2px;
    color: var(--accent);
    font-style: normal;
    font-weight: 600;
}

.shokunin-cta-section {
    text-align: center;
    max-width: 700px;
    margin: 0 auto;
    padding-top: 40px;
}

.shokunin-actions {
    display: flex;
    justify-content: center;
    gap: 20px;
    margin-top: 40px;
}

.shokunin-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 15px 35px;
    border-radius: 30px;
    font-weight: 500;
    font-size: 0.95rem;
    transition: var(--transition);
}

.shokunin-btn-primary {
    background: var(--accent);
    color: var(--white);
}

.shokunin-btn-primary:hover {
    background: var(--accent-light);
    transform: translateY(-2px);
}

.shokunin-btn-secondary {
    background: transparent;
    color: var(--black);
    border: 1px solid var(--line);
}

.shokunin-btn-secondary:hover {
    background: var(--cream);
    border-color: var(--black);
    transform: translateY(-2px);
}

/* Responsive */
@media (max-width: 991px) {
    .shokunin-grid {
        grid-template-columns: 1fr;
        gap: 30px;
    }
    .shokunin-title {
        font-size: 2.8rem;
    }
}

@media (max-width: 576px) {
    .shokunin-title {
        font-size: 2.2rem;
    }
    .shokunin-hero {
        padding: 120px 0 60px;
    }
    .shokunin-actions {
        flex-direction: column;
        gap: 15px;
    }
    .shokunin-btn {
        width: 100%;
    }
}
</style>

<!-- Simple elegant header for subpages -->
<header style="position: absolute; top: 0; left: 0; width: 100%; z-index: 10; border-bottom: 1px solid rgba(139, 58, 42, 0.08);">
    <nav aria-label="Điều hướng phụ" style="max-width: 1280px; margin: 0 auto; padding: 25px 30px; display: flex; justify-content: space-between; align-items: center;">
        <a href="index.php" style="font-family: var(--font-serif); font-size: 1.5rem; font-weight: 500; color: var(--black); letter-spacing: -0.5px;">
            Lena<em style="font-style: italic; color: var(--accent); font-weight: 500; font-family: var(--font-serif);">Bakery</em>
        </a>
        <a href="index.php" style="font-size: 0.9rem; font-weight: 500; color: var(--accent); display: flex; align-items: center; gap: 8px; transition: var(--transition);">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            Về trang chủ
        </a>
    </nav>
</header>

<main>
    <!-- HERO SECTION -->
    <section class="shokunin-hero">
        <div class="container">
            <span class="shokunin-meta">Tinh thần nghệ nhân Nhật Bản</span>
            <h1 class="shokunin-title">Triết lý <em>Shokunin</em><br>trong từng mẻ bánh</h1>
            <p class="shokunin-subtitle">
                Tại Lena Bakery, làm bánh không chỉ là sự kết hợp của bột, đường và sữa. 
                Đó là một hành trình rèn luyện, nâng niu từng chi tiết nhỏ nhất để chạm tới sự hoàn mỹ.
            </p>
        </div>
    </section>

    <!-- CONTENT SECTION -->
    <section class="shokunin-content">
        <div class="container">
            <p class="shokunin-intro-paragraph">
                "Shokunin" (職人) trong tiếng Nhật không chỉ đơn giản là người thợ thủ công. 
                Đó là người cống hiến cả cuộc đời, tâm trí và linh hồn cho nghề nghiệp của họ, 
                luôn khao khát làm tốt hơn mỗi ngày để mang lại những giá trị tốt đẹp nhất cho cộng đồng.
            </p>

            <div class="shokunin-grid">
                <!-- CARD 1 -->
                <div class="shokunin-card">
                    <div class="shokunin-card-num">01</div>
                    <h3 class="shokunin-card-title">Kodawari</h3>
                    <span class="shokunin-card-jp">こだわり · Sự tỉ mỉ tột cùng</span>
                    <p class="shokunin-desc">
                        Không chấp nhận sự thỏa hiệp trong việc lựa chọn nguyên liệu. Từ hạt cà phê rang mộc Tây Nguyên đậm vị cho đến phô mai Mascarpone nhập khẩu từ Ý, tất cả đều được tuyển chọn khắt khe để tạo nên cấu trúc bánh hoàn hảo nhất.
                    </p>
                </div>

                <!-- CARD 2 -->
                <div class="shokunin-card">
                    <div class="shokunin-card-num">02</div>
                    <h3 class="shokunin-card-title">Kaizen</h3>
                    <span class="shokunin-card-jp">改善 · Cải tiến không ngừng</span>
                    <p class="shokunin-desc">
                        Mỗi chiếc bánh ra lò là một bài học để hoàn thiện hơn. Chúng mình liên tục tối ưu hóa độ ngọt, độ ẩm của cốt bánh savoiardi, và độ mịn mượt của kem Mascarpone để đảm bảo mỗi hộp bánh trao đi luôn mang chất lượng tốt nhất.
                    </p>
                </div>

                <!-- CARD 3 -->
                <div class="shokunin-card">
                    <div class="shokunin-card-num">03</div>
                    <h3 class="shokunin-card-title">Tâm huyết trọn vẹn</h3>
                    <span class="shokunin-card-jp">一生懸命 · Nỗ lực cả cuộc đời</span>
                    <p class="shokunin-desc">
                        Làm bánh bằng tất cả lòng kiên nhẫn và kỷ luật học được từ giảng đường Luật. Chúng mình coi mỗi hộp Tiramisu là một lời ủi an dịu dàng, xoa dịu những mệt mỏi của bạn sau một ngày dài làm việc bận rộn.
                    </p>
                </div>
            </div>

            <!-- QUOTE -->
            <div class="shokunin-philosophy-quote">
                <p>
                    "Người Shokunin không bao giờ nói họ đã hoàn hảo. Họ luôn tin rằng mẻ bánh tiếp theo sẽ là mẻ bánh ngon nhất họ từng làm."
                </p>
                <cite>— Triết lý nghệ nhân</cite>
            </div>

            <!-- CTA SECTION -->
            <div class="shokunin-cta-section">
                <h2 style="font-family: var(--font-serif); font-size: 2.2rem; font-weight: 500; color: var(--black); margin-bottom: 20px;">
                    Thưởng thức chiếc bánh được làm bằng tất cả tấm lòng
                </h2>
                <p style="color: var(--gray); font-size: 1.05rem;">
                    Bánh tươi được chuẩn bị và làm trực tiếp sau khi nhận được yêu cầu của bạn để bảo đảm giữ trọn vẹn sự tươi mới tinh khiết.
                </p>
                <div class="shokunin-actions">
                    <a href="checkout.php" class="shokunin-btn shokunin-btn-primary">
                        Đặt bánh ngay
                    </a>
                    <a href="index.php" class="shokunin-btn shokunin-btn-secondary">
                        Quay lại trang chủ
                    </a>
                </div>
            </div>
        </div>
    </section>
</main>

<?php
require_once __DIR__ . '/includes/footer.php';
require_once __DIR__ . '/includes/scripts.php';
?>
