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
                    <button class="mff-btn" data-flavor="cacao">Cacao</button>
                    <button class="mff-btn" data-flavor="matcha">Matcha</button>
                </div>
            </div>

            <div class="menu-grid" id="menuGrid">
                <?php foreach (PRODUCTS as $pid => $p): ?>
                <?php 
                    $isOut    = !empty($p['is_out_of_stock']);
                    $oosF     = $p['out_of_stock_flavors'] ?? [];
                    $flavors  = !empty($p['flavors']) ? implode(',', $p['flavors']) : 'cacao,matcha';
                    $allFlavs = $p['flavors'] ?? ['cacao','matcha'];
                    $priceInK = $p['price'] >= 1000 ? round($p['price'] / 1000) : $p['price'];
                    // Hết hàng khi tất cả vị đều hết hoặc is_out_of_stock = true
                    $fullyOut = $isOut || (!empty($allFlavs) && count(array_intersect($allFlavs, $oosF)) === count($allFlavs));
                ?>
                <article class="menu-card reveal <?= $fullyOut ? 'is-out-of-stock' : '' ?>" data-flavors="<?= htmlspecialchars($flavors) ?>">
                    <?php if ($fullyOut): ?>
                        <span class="menu-tag out-of-stock-tag" style="background:#ef4444;color:#fff;">Tạm hết hàng</span>
                    <?php elseif (!empty($p['badge'])): ?>
                        <span class="menu-tag"><?= htmlspecialchars($p['badge']) ?></span>
                    <?php endif; ?>
                    
                    <div class="img-wrap" style="<?= $fullyOut ? 'filter: grayscale(0.5) opacity(0.85);' : '' ?>">
                        <img src="<?= htmlspecialchars($p['img'] ?: 'assets/images/cake-classic.jpg') ?>"
                            alt="<?= htmlspecialchars($p['name']) ?> Lena Bakery" loading="lazy" />
                    </div>
                    <div class="menu-body">
                        <div class="menu-info">
                            <div>
                                <h3>
                                    <?= htmlspecialchars($p['name']) ?>
                                    <?php if ($fullyOut): ?>
                                        <span class="badge" style="background:#fee2e2;color:#dc2626;">Tạm hết</span>
                                    <?php elseif (!empty($p['badge'])): ?>
                                        <span class="badge"><?= htmlspecialchars($p['badge']) ?></span>
                                    <?php endif; ?>
                                </h3>
                                <div class="menu-flavor-chips">
                                    <?php foreach ($allFlavs as $f): 
                                        $fIsOut = in_array($f, $oosF);
                                    ?>
                                    <span class="mfc <?= htmlspecialchars($f) ?>"
                                          style="<?= $fIsOut ? 'text-decoration:line-through;opacity:0.5;' : '' ?>"
                                          title="<?= $fIsOut ? ucfirst(htmlspecialchars($f)) . ' tạm hết' : ucfirst(htmlspecialchars($f)) . ' còn hàng' ?>">
                                        <?= ucfirst(htmlspecialchars($f)) ?>
                                        <?php if ($fIsOut): ?>
                                            <small style="font-size:9px;display:block;color:#ef4444;text-decoration:none;">hết</small>
                                        <?php endif; ?>
                                    </span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <span class="menu-price"><?= $priceInK ?><small>k</small></span>
                        </div>
                        <div class="menu-desc"><?= htmlspecialchars($p['desc']) ?></div>
                        
                        <?php if ($fullyOut): ?>
                        <button type="button" class="menu-order-btn out-of-stock-btn" disabled style="background:#f3f4f6;color:#9ca3af;cursor:not-allowed;border-color:#e5e7eb;">
                            Tạm hết hàng
                        </button>
                        <?php else: ?>
                        <a href="checkout.php?pid=<?= urlencode($pid) ?>" class="menu-order-btn">
                            Đặt ngay →
                        </a>
                        <?php endif; ?>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>


            <div class="menu-note reveal">
                <strong>Trái cây tươi ăn kèm <span style="font-weight:400;font-size:13px;color:var(--gray);">(Gọi thêm khi đặt)</span></strong>
                <div class="fruit-list">
                    <span>Dâu tây tươi <span class="price">15k</span></span>
                    <span>Cherry nhập khẩu <span class="price">20k</span></span>
                    <span>Mix Dâu &amp; Cherry <span class="price">30k</span></span>
                </div>
            </div>
        </section>
