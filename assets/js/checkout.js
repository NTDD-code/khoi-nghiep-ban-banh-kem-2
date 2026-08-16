// ============================================================
// LENA BAKERY — checkout.js
// Quản lý giỏ hàng, wizard steps, VietQR, submit đơn
// ============================================================

// ---- State ----
const cart   = {};   // { pid: { qty, flavor, topping } }
let tipAmount = 0;
let currentStep = 1;

// ---- Step navigation ----
function goStep(n, pushHistory = true) {
  if (n < 1 || n > 3) return;

  // Nếu muốn tiến sang bước 2 hoặc 3 từ bước 1 mà chưa có món nào
  if (n > 1 && currentStep === 1) {
    const count = Object.values(cart).reduce((s, v) => s + v.qty, 0);
    if (count === 0) {
      showError('Vui lòng chọn ít nhất 1 loại bánh để tiếp tục');
      return;
    }
  }

  document.querySelectorAll('.co-panel').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.co-step').forEach(s => {
    s.classList.remove('active', 'done');
    const sn = +s.dataset.step;
    if (sn < n) s.classList.add('done');
    if (sn === n) s.classList.add('active');
  });
  document.getElementById(`step${n}`).classList.add('active');
  currentStep = n;
  window.scrollTo({ top: 0, behavior: 'smooth' });

  if (pushHistory) {
    history.pushState({ step: n }, '', `#step${n}`);
  }

  if (n === 2) renderSummaryStep2();
  if (n === 3) renderStep3();
}

// ---- Cart logic ----
function updateCart(pid, delta) {
  if (!cart[pid]) cart[pid] = { qty: 0, flavor: 'cacao', topping: 'none' };
  cart[pid].qty = Math.max(0, cart[pid].qty + delta);

  // Sync flavor & topping from UI
  const flavorEl  = document.querySelector(`input[name="flavor_${pid}"]:checked`);
  const toppingEl = document.querySelector(`select[name="topping_${pid}"]`);
  if (flavorEl)  cart[pid].flavor  = flavorEl.value;
  if (toppingEl) cart[pid].topping = toppingEl.value;

  if (cart[pid].qty === 0) delete cart[pid];
  renderCartUI();
}

function renderCartUI() {
  const items   = Object.entries(cart);
  const count   = items.reduce((s, [, v]) => s + v.qty, 0);
  const subtotal = calcSubtotal();

  // Qty displays
  Object.keys(PRODUCTS_DATA).forEach(pid => {
    const qEl = document.getElementById(`qty_${pid}`);
    const sEl = document.getElementById(`sub_${pid}`);
    if (!qEl) return;
    const c = cart[pid];
    qEl.textContent = c ? c.qty : 0;
    if (c && c.qty > 0 && sEl) {
      const topPrice = TOPPINGS_DATA[c.topping]?.price ?? 0;
      sEl.textContent = fmt((PRODUCTS_DATA[pid].price + topPrice) * c.qty);
    } else if (sEl) {
      sEl.textContent = '';
    }

    // Card highlight
    document.querySelector(`.product-card[data-pid="${pid}"]`)?.classList.toggle('in-cart', !!(c && c.qty > 0));
  });

  // Cart sidebar
  document.getElementById('cartCount').textContent = count > 0 ? `${count} món` : '0 món';
  document.getElementById('cartEmpty').style.display  = count === 0 ? '' : 'none';
  document.getElementById('cartTotalRow').style.display = count === 0 ? 'none' : '';
  document.getElementById('cartNextBtn').disabled = count === 0;

  const list = document.getElementById('cartList');
  list.innerHTML = items.filter(([,v]) => v.qty > 0).map(([pid, v]) => {
    const p = PRODUCTS_DATA[pid];
    const top = TOPPINGS_DATA[v.topping];
    const rowPrice = (p.price + (top?.price ?? 0)) * v.qty;
    return `<li class="cart-item">
      <div class="ci-name">${p.name} ×${v.qty}</div>
      <div class="ci-meta">${v.flavor} • ${top?.name ?? ''}</div>
      <div class="ci-price">${fmt(rowPrice)}</div>
    </li>`;
  }).join('');

  document.getElementById('cartSubtotal').textContent = fmt(subtotal);
}

function calcSubtotal() {
  return Object.entries(cart).reduce((s, [pid, v]) => {
    const topPrice = TOPPINGS_DATA[v.topping]?.price ?? 0;
    return s + (PRODUCTS_DATA[pid].price + topPrice) * v.qty;
  }, 0);
}

// ---- Step 2: summary + validate ----
function renderSummaryStep2() {
  const items      = Object.entries(cart).filter(([,v]) => v.qty > 0);
  const list       = document.getElementById('summaryList2');
  const rows       = document.getElementById('summaryRows2');
  const sub        = calcSubtotal();
  const isDelivery = getShipMethod() === 'delivery';
  const total      = sub + tipAmount; // Tiền ship thanh toán riêng cho shipper

  list.innerHTML = items.map(([pid, v]) => {
    const p   = PRODUCTS_DATA[pid];
    const top = TOPPINGS_DATA[v.topping];
    return `<li>${p.name} ×${v.qty} (${v.flavor}, ${top?.name}) — ${fmt((p.price+(top?.price??0))*v.qty)}</li>`;
  }).join('');

  rows.innerHTML = `
    <div class="sum-row"><span>Tạm tính</span><span>${fmt(sub)}</span></div>
    <div class="sum-row">
      <span>Phí ship</span>
      <span>${isDelivery ? '<strong class="text-warn">Chưa tính (Ship tận giường trả shipper)</strong>' : '<span class="text-success">Miễn phí (Pickup)</span>'}</span>
    </div>
    <div class="sum-row"><span>Tip 💛</span><span>${fmt(tipAmount)}</span></div>
    <div class="sum-row total"><span>Tổng cộng</span><strong>${fmt(total)}</strong></div>
    ${isDelivery ? '<div class="sum-ship-tip">🛌 <em>Ship tận giường nhà bạn luôn — Tiền ship tính theo cước app và gửi riêng cho shipper khi nhận bánh nhé!</em></div>' : ''}
  `;
}

function validateStep2() {
  const name  = document.getElementById('customerName').value.trim();
  const phone = document.getElementById('customerPhone').value.trim();
  const method= getShipMethod();
  const addr  = document.getElementById('customerAddr').value.trim();

  if (!name)  { shake('customerName');  showError('Vui lòng nhập tên'); return; }
  if (!/^[0-9+\-\s]{8,15}$/.test(phone)) { shake('customerPhone'); showError('Số điện thoại chưa đúng'); return; }
  if (method === 'delivery' && !addr) { shake('customerAddr'); showError('Vui lòng nhập địa chỉ'); return; }

  goStep(3);
}

// ---- Step 3: VietQR / COD + submit ----
function getPaymentChoice() {
  return document.querySelector('input[name="payment_choice"]:checked')?.value || 'transfer';
}

function switchPaymentView(choice) {
  document.querySelectorAll('.pay-tab').forEach(t => t.classList.remove('active'));
  const activeTab = document.getElementById(choice === 'cod' ? 'tabCOD' : 'tabTransfer');
  activeTab?.classList.add('active');

  const qrBox        = document.getElementById('qrBox');
  const codBox       = document.getElementById('codBox');
  const btnTransfer  = document.getElementById('btnConfirmPaid');
  const btnCOD       = document.getElementById('btnSubmitCOD');

  if (choice === 'cod') {
    if (qrBox) qrBox.style.display = 'none';
    if (codBox) codBox.style.display = 'block';
    if (btnTransfer) btnTransfer.style.display = 'none';
    if (btnCOD) btnCOD.style.display = 'block';
  } else {
    if (qrBox) qrBox.style.display = 'block';
    if (codBox) codBox.style.display = 'none';
    if (btnTransfer) btnTransfer.style.display = 'block';
    if (btnCOD) btnCOD.style.display = 'none';
  }
}

function copyQRContent() {
  const codeEl = document.getElementById('qrContentDisplay');
  if (!codeEl) return;
  const text = codeEl.textContent.trim();
  if (!text) return;

  navigator.clipboard.writeText(text).then(() => {
    const btn = document.querySelector('.btn-copy-code');
    if (btn) {
      const oldText = btn.textContent;
      btn.textContent = '✅ Đã chép!';
      setTimeout(() => { btn.textContent = oldText; }, 2000);
    }
  }).catch(() => {
    // Fallback
    const input = document.createElement('textarea');
    input.value = text;
    document.body.appendChild(input);
    input.select();
    document.execCommand('copy');
    document.body.removeChild(input);
    alert('Đã sao chép nội dung chuyển khoản: ' + text);
  });
}

function renderStep3() {
  const sub        = calcSubtotal();
  const isDelivery = getShipMethod() === 'delivery';
  const total      = sub + tipAmount; // Tiền ship trả riêng cho shipper
  const name       = document.getElementById('customerName').value.trim();
  const addInfo    = (name ? `${name}_dat banh` : 'dat banh').substring(0, 50);
  const payChoice  = getPaymentChoice();

  // Cập nhật QR VietQR
  const qrUrl = `https://img.vietqr.io/image/${BANK_ID}-${BANK_ACC}-compact2.png?`
    + `amount=${total}`
    + `&addInfo=${encodeURIComponent(addInfo)}`
    + `&accountName=${encodeURIComponent(BANK_NAME)}`;

  const qrImg = document.getElementById('qrImage');
  const qrLoading = document.getElementById('qrLoading');
  if (qrImg && qrLoading) {
    qrImg.style.display = 'none';
    qrLoading.style.display = '';
    qrImg.onload = () => { qrImg.style.display = ''; qrLoading.style.display = 'none'; };
    qrImg.src = qrUrl;
  }

  document.getElementById('qrAmountDisplay').textContent = fmt(total);
  document.getElementById('qrContentDisplay').textContent = addInfo;

  // Cập nhật COD Box
  const codAmount = document.getElementById('codAmountDisplay');
  if (codAmount) codAmount.textContent = fmt(total);

  // Chuyển view tương ứng
  switchPaymentView(payChoice);

  // Final summary
  const items = Object.entries(cart).filter(([,v]) => v.qty > 0);
  document.getElementById('finalSummary').innerHTML = `
    <div class="fs-title">📋 Xác nhận đơn hàng</div>
    <div class="fs-row"><b>Khách:</b> ${esc(name)} — ${esc(document.getElementById('customerPhone').value.trim())}</div>
    <div class="fs-row"><b>Giao:</b> ${isDelivery ? '🛵 ' + esc(document.getElementById('customerAddr').value.trim()) + ' (Ship tận giường)' : '🏪 Pickup tại quán'}</div>
    <hr/>
    ${items.map(([pid,v]) => {
      const p = PRODUCTS_DATA[pid]; const top = TOPPINGS_DATA[v.topping];
      return `<div class="fs-row">${p.name} ×${v.qty} — ${fmt((p.price+(top?.price??0))*v.qty)}</div>`;
    }).join('')}
    <hr/>
    <div class="fs-row"><span>Sản phẩm</span><span>${fmt(sub)}</span></div>
    <div class="fs-row"><span>Phí ship</span><span>${isDelivery ? '<strong class="text-warn">Chưa tính (khách gửi shipper)</strong>' : '<span class="text-success">Miễn phí (Pickup)</span>'}</span></div>
    ${tipAmount ? `<div class="fs-row"><span>Tip 💛</span><span>${fmt(tipAmount)}</span></div>` : ''}
    <div class="fs-row fs-total"><span>Tổng thanh toán</span><strong>${fmt(total)}</strong></div>
    ${isDelivery ? `<div class="fs-ship-alert">🛌 <strong>Ship tận giường:</strong> Tiền thanh toán ở đây <u>chưa tính tiền ship</u>. Cước giao hàng tính theo app (Grab/Be/Xanh SM) và bạn gửi trực tiếp cho anh shipper khi nhận bánh nha!</div>` : ''}
  `;
}

async function submitOrder(payMethod) {
  const name       = document.getElementById('customerName').value.trim();
  const phone      = document.getElementById('customerPhone').value.trim();
  const method     = getShipMethod();
  const addr       = document.getElementById('customerAddr').value.trim();
  const note       = document.getElementById('customerNote').value.trim();
  const actualPay  = payMethod || getPaymentChoice();
  const items      = Object.entries(cart).filter(([,v]) => v.qty > 0).map(([pid, v]) => ({
    product_id: pid,
    flavor: v.flavor,
    topping: v.topping,
    quantity: v.qty,
  }));

  const btnTransfer = document.getElementById('btnConfirmPaid');
  const btnCOD      = document.getElementById('btnSubmitCOD');
  if (btnTransfer) btnTransfer.disabled = true;
  if (btnCOD) btnCOD.disabled = true;
  document.getElementById('paySpinner').style.display = '';

  try {
    const res = await fetch('api/submit-order.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        customer_name: name,
        customer_phone: phone,
        customer_addr: addr,
        shipping_method: method,
        payment_method: actualPay,
        note,
        tip: tipAmount,
        items
      }),
    });
    const json = await res.json();
    if (json.ok) {
      // Redirect sang trang cảm ơn
      window.location.href = `order-success.php?code=${encodeURIComponent(json.order_code)}&pay=${actualPay}`;
    } else {
      showError(json.error || 'Lỗi hệ thống');
      if (btnTransfer) btnTransfer.disabled = false;
      if (btnCOD) btnCOD.disabled = false;
    }
  } catch(e) {
    showError('Lỗi kết nối mạng, vui lòng thử lại');
    if (btnTransfer) btnTransfer.disabled = false;
    if (btnCOD) btnCOD.disabled = false;
  } finally {
    document.getElementById('paySpinner').style.display = 'none';
  }
}

// ---- Helpers ----
function getShipMethod() {
  return document.querySelector('input[name="shipping_method"]:checked')?.value || 'pickup';
}

function fmt(n) {
  return new Intl.NumberFormat('vi-VN').format(n) + 'đ';
}

function esc(s) {
  return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

function showError(msg) {
  const toast = document.createElement('div');
  toast.className = 'co-toast error';
  toast.textContent = msg;
  document.body.appendChild(toast);
  requestAnimationFrame(() => toast.classList.add('show'));
  setTimeout(() => { toast.classList.remove('show'); setTimeout(()=>toast.remove(),300); }, 3000);
}

function shake(id) {
  const el = document.getElementById(id);
  el?.classList.add('shake');
  setTimeout(() => el?.classList.remove('shake'), 500);
}

// ---- Event listeners ----
document.addEventListener('DOMContentLoaded', () => {

  // Qty buttons
  document.querySelectorAll('.qty-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const pid = btn.dataset.pid;
      const act = btn.dataset.action;
      updateCart(pid, act === 'plus' ? 1 : -1);
    });
  });

  // Flavor / topping change → update cart if already in cart
  document.querySelectorAll('[name^="flavor_"], [name^="topping_"]').forEach(el => {
    el.addEventListener('change', () => {
      const pid = el.name.replace(/^(flavor_|topping_)/, '');
      if (cart[pid] && cart[pid].qty > 0) {
        const flavorEl  = document.querySelector(`input[name="flavor_${pid}"]:checked`);
        const toppingEl = document.querySelector(`select[name="topping_${pid}"]`);
        if (flavorEl)  cart[pid].flavor  = flavorEl.value;
        if (toppingEl) cart[pid].topping = toppingEl.value;
        renderCartUI();
      }
    });
  });

  // Ship method cards
  document.querySelectorAll('input[name="shipping_method"]').forEach(radio => {
    radio.addEventListener('change', () => {
      document.querySelectorAll('.ship-card').forEach(c => c.classList.remove('active'));
      radio.closest('.ship-card')?.classList.add('active');
      const isDel = radio.value === 'delivery';
      document.getElementById('addrRow').style.display = isDel ? '' : 'none';
      const noticeBox = document.getElementById('deliveryNoticeBox');
      if (noticeBox) noticeBox.style.display = isDel ? 'flex' : 'none';
      if (currentStep === 2) renderSummaryStep2();
    });
  });

  // Tip chips
  document.querySelectorAll('.tip-chip').forEach(chip => {
    chip.addEventListener('click', () => {
      document.querySelectorAll('.tip-chip').forEach(c => c.classList.remove('active'));
      chip.classList.add('active');
      tipAmount = +chip.dataset.tip;
      document.getElementById('tipCustom').value = '';
      updateTipDisplay();
      if (currentStep === 2) renderSummaryStep2();
    });
  });

  document.getElementById('tipCustom')?.addEventListener('input', e => {
    const val = Math.max(0, +e.target.value || 0);
    tipAmount = val;
    document.querySelectorAll('.tip-chip').forEach(c => c.classList.remove('active'));
    updateTipDisplay();
    if (currentStep === 2) renderSummaryStep2();
  });

  function updateTipDisplay() {
    document.getElementById('tipAmount').textContent = fmt(tipAmount);
  }

  // Click vào các bước trên thanh header để back/chuyển bước
  document.querySelectorAll('.co-step').forEach(stepEl => {
    stepEl.addEventListener('click', () => {
      const targetStep = +stepEl.dataset.step;
      if (targetStep === currentStep) return;

      if (targetStep < currentStep) {
        // Cho phép back lại các bước trước bất cứ lúc nào
        goStep(targetStep);
      } else if (targetStep === 2) {
        const count = Object.values(cart).reduce((s, v) => s + v.qty, 0);
        if (count === 0) {
          showError('Vui lòng chọn ít nhất 1 loại bánh');
          return;
        }
        goStep(2);
      } else if (targetStep === 3) {
        if (currentStep === 1) {
          const count = Object.values(cart).reduce((s, v) => s + v.qty, 0);
          if (count === 0) {
            showError('Vui lòng chọn ít nhất 1 loại bánh');
            return;
          }
          goStep(2);
        } else if (currentStep === 2) {
          validateStep2();
        }
      }
    });
  });

  // Khởi tạo history state ban đầu
  history.replaceState({ step: 1 }, '', '#step1');

  // Lắng nghe nút Back của trình duyệt / điện thoại
  window.addEventListener('popstate', (e) => {
    if (e.state && e.state.step) {
      goStep(e.state.step, false);
    } else {
      goStep(1, false);
    }
  });

  // Chuyển đổi phương thức thanh toán VietQR / COD
  document.querySelectorAll('input[name="payment_choice"]').forEach(radio => {
    radio.addEventListener('change', () => {
      switchPaymentView(radio.value);
    });
  });

  // Submit order buttons
  document.getElementById('btnConfirmPaid')?.addEventListener('click', () => submitOrder('transfer'));
  document.getElementById('btnSubmitCOD')?.addEventListener('click', () => submitOrder('cod'));

  // Pre-select from URL params (nếu đến từ menu)
  const params = new URLSearchParams(window.location.search);
  const prePid = params.get('pid');
  if (prePid && PRODUCTS_DATA[prePid]) {
    updateCart(prePid, 1);
  }

  // Initial render
  renderCartUI();
  updateTipDisplay();
});
