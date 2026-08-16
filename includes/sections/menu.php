<!-- ===== MENU ===== -->
        <section class="menu" id="menu">
            <div class="section-header reveal">
                <div>
                    <div class="section-label">Thực đơn</div>
                    <h2 class="section-title">Menu — <em>Lena Bakery</em></h2>
                    <p class="section-sub">3 size đa dạng phù hợp từ 1 người ăn vặt đến làm quà tặng sang trọng.</p>
                </div>
                <!-- Bộ lọc vị -->
                <div class="menu-flavor-filter">
                    <button class="mff-btn active" data-flavor="">Tất cả</button>
                    <button class="mff-btn" data-flavor="cacao">☕ Cacao</button>
                    <button class="mff-btn" data-flavor="matcha">🍵 Matcha</button>
                </div>
            </div>

            <div class="menu-grid" id="menuGrid">

                <!-- Mini Cup -->
                <article class="menu-card reveal" data-flavors="cacao,matcha">
                    <div class="img-wrap">
                        <img src="assets/images/cake-classic.jpg"
                            alt="Mini cup Tiramisu Lena Bakery" loading="lazy" />
                    </div>
                    <div class="menu-body">
                        <div class="menu-info">
                            <div>
                                <h3>Mini Cup</h3>
                                <div class="menu-flavor-chips">
                                    <span class="mfc cacao">☕ Cacao</span>
                                    <span class="mfc matcha">🍵 Matcha</span>
                                </div>
                            </div>
                            <span class="menu-price">20<small>k</small></span>
                        </div>
                        <div class="menu-desc">Nhỏ gọn cho 1 người. Kem Mascarpone ngậy tan, cốt bánh đượm Espresso.</div>
                        <a href="checkout.php?pid=mini" class="menu-order-btn">
                            Đặt ngay →
                        </a>
                    </div>
                </article>

                <!-- Hộp 350ml -->
                <article class="menu-card reveal" data-flavors="cacao,matcha">
                    <span class="menu-tag">Bán chạy</span>
                    <div class="img-wrap">
                        <img src="assets/images/cake-matcha.png"
                            alt="Hộp 350ml Tiramisu Matcha Lena Bakery" loading="lazy" />
                    </div>
                    <div class="menu-body">
                        <div class="menu-info">
                            <div>
                                <h3>Hộp 350ml <span class="badge">Bán chạy</span></h3>
                                <div class="menu-flavor-chips">
                                    <span class="mfc cacao">☕ Cacao</span>
                                    <span class="mfc matcha">🍵 Matcha</span>
                                </div>
                            </div>
                            <span class="menu-price">70<small>k</small></span>
                        </div>
                        <div class="menu-desc">Dành cho 2–3 người. Lớp kem dày béo ngậy chuẩn vị.</div>
                        <a href="checkout.php?pid=350ml" class="menu-order-btn">
                            Đặt ngay →
                        </a>
                    </div>
                </article>

                <!-- Hộp thiếc 750ml -->
                <article class="menu-card reveal" data-flavors="cacao,matcha">
                    <span class="menu-tag">Sang trọng</span>
                    <div class="img-wrap">
                        <img src="assets/images/cake-berry.jpg"
                            alt="Hộp thiếc 750ml Tiramisu sang trọng Lena Bakery" loading="lazy" />
                    </div>
                    <div class="menu-body">
                        <div class="menu-info">
                            <div>
                                <h3>Hộp thiếc 750ml <span class="badge">Sang trọng</span></h3>
                                <div class="menu-flavor-chips">
                                    <span class="mfc cacao">☕ Cacao</span>
                                    <span class="mfc matcha">🍵 Matcha</span>
                                </div>
                            </div>
                            <span class="menu-price">189<small>k</small></span>
                        </div>
                        <div class="menu-desc">Quà tặng giữ lạnh tối ưu, trang trí dâu/cherry tươi cao cấp.</div>
                        <a href="checkout.php?pid=tin750" class="menu-order-btn">
                            Đặt ngay →
                        </a>
                    </div>
                </article>

            </div>

            <div class="menu-note reveal">
                <strong>🍓 Trái cây tươi ăn kèm <span style="font-weight:400;font-size:13px;color:var(--gray);">(Gọi thêm khi đặt)</span></strong>
                <div class="fruit-list">
                    <span>Dâu tây tươi <span class="price">15k</span></span>
                    <span>Cherry nhập khẩu <span class="price">20k</span></span>
                    <span>Mix Dâu &amp; Cherry <span class="price">30k</span></span>
                </div>
            </div>
        </section>
