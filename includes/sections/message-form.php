<!-- ===== IN-APP MESSAGING (IB) ===== -->
        <section class="ib-section" id="nhan-tin">
            <div class="ib-inner">
                <div class="ib-copy reveal">
                    <div class="section-label">Nhắn tin</div>
                    <h2 class="section-title">Hỏi thêm <em>trực tiếp</em></h2>
                    <p class="section-sub" style="margin-top:14px;">
                        Để lại tin nhắn cho Lena — mình sẽ phản hồi sớm nhất qua Zalo hoặc Messenger.
                    </p>
                    <div class="ib-social-links">
                        <a href="https://zalo.me/0906819341" target="_blank" class="ib-social-btn zalo">
                            <img src="assets/images/zalo-icon.svg" alt="Zalo" style="height:16px;width:auto" />
                            Zalo 0906.819.341
                        </a>
                        <a href="https://m.me/caryln.fer" target="_blank" class="ib-social-btn messenger">
                            <img src="assets/images/messenger icon.png" alt="Messenger" style="width:16px;height:16px;object-fit:contain" />
                            Facebook Messenger
                        </a>
                    </div>
                </div>

                <div class="ib-form-wrap reveal">
                    <form class="ib-form" id="ibForm" novalidate>
                        <div class="ib-form-title" style="display:flex;align-items:center;gap:8px;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                            <span>Để lại lời nhắn</span>
                        </div>

                        <div class="ib-row">
                            <input type="text" id="ibName" name="sender_name" placeholder="Tên của bạn *" required autocomplete="name" />
                        </div>
                        <div class="ib-row">
                            <input type="tel" id="ibPhone" name="sender_phone" placeholder="Số điện thoại (để mình gọi lại)" autocomplete="tel" />
                        </div>
                        <div class="ib-row">
                            <textarea id="ibContent" name="content" rows="4" placeholder="Nội dung tin nhắn... VD: Tôi muốn hỏi về cách bảo quản bánh, có thể đặt số lượng lớn không?" required></textarea>
                        </div>

                        <button type="submit" class="ib-submit-btn" id="ibSubmitBtn">
                            <span id="ibBtnText">Gửi tin nhắn →</span>
                            <span id="ibBtnSpinner" style="display:none">Đang gửi...</span>
                        </button>

                        <div class="ib-success" id="ibSuccess" style="display:none">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-2px;margin-right:4px;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                            Tin nhắn đã được gửi! Lena sẽ phản hồi sớm nhất có thể.
                        </div>
                        <div class="ib-error" id="ibError" style="display:none"></div>
                    </form>
                </div>
            </div>
        </section>
