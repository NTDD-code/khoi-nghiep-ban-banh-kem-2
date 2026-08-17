// ============================================================
// LENA BAKERY — admin.js
// Realtime Polling + Status Update + Live Filter + Total Calculation + Settings & Privacy Mask
// ============================================================

// ---- Settings Management (localStorage) ----
const SETTINGS_KEYS = {
  RULE: 'lena_revenue_rule',     // 'non_cancelled' | 'done_only' | 'all'
  MASK: 'lena_revenue_masked',   // 'true' | 'false'
  SOUND: 'lena_sound_enabled',   // 'true' | 'false'
};

function getRevenueRule() {
  return localStorage.getItem(SETTINGS_KEYS.RULE) || 'non_cancelled';
}

function saveRevenueRule(rule) {
  localStorage.setItem(SETTINGS_KEYS.RULE, rule);
  updateRevenueCardDisplay();
  recalculateVisibleTotal();
}

function isRevenueMasked() {
  return localStorage.getItem(SETTINGS_KEYS.MASK) === 'true';
}

function setRevenueMask(masked) {
  localStorage.setItem(SETTINGS_KEYS.MASK, masked ? 'true' : 'false');
  updateMaskUI();
}

function toggleRevenueMask() {
  setRevenueMask(!isRevenueMasked());
}

function isSoundEnabled() {
  return localStorage.getItem(SETTINGS_KEYS.SOUND) !== 'false';
}

function setSoundSetting(enabled) {
  localStorage.setItem(SETTINGS_KEYS.SOUND, enabled ? 'true' : 'false');
}

// Cập nhật giao diện ẩn/hiện số tiền
function updateMaskUI() {
  const masked = isRevenueMasked();
  
  // Nút mắt trên card
  const eyeOpen = document.querySelector('.icon-eye-open');
  const eyeClosed = document.querySelector('.icon-eye-closed');
  if (eyeOpen && eyeClosed) {
    eyeOpen.style.display = masked ? 'none' : 'block';
    eyeClosed.style.display = masked ? 'block' : 'none';
  }

  // Toggle trong modal cài đặt
  const maskToggle = document.getElementById('settingMaskToggle');
  if (maskToggle) maskToggle.checked = masked;

  // Cập nhật lại các số tiền đang hiển thị
  updateRevenueCardDisplay();
  recalculateVisibleTotal();
}

// Cập nhật card tổng doanh thu ở đầu trang theo quy tắc đã chọn
function updateRevenueCardDisplay() {
  const cardEl = document.getElementById('statRevenue');
  const descEl = document.getElementById('revenueRuleDesc');
  if (!cardEl) return;

  const rule = getRevenueRule();
  let val = 0;

  if (rule === 'done_only') {
    val = parseFloat(cardEl.dataset.revenueDone) || 0;
    if (descEl) descEl.textContent = 'Chỉ tính đơn Hoàn thành';
  } else if (rule === 'all') {
    val = parseFloat(cardEl.dataset.revenueAll) || 0;
    if (descEl) descEl.textContent = 'Toàn bộ đơn hàng';
  } else {
    // non_cancelled (mặc định)
    val = parseFloat(cardEl.dataset.revenueValid) || 0;
    if (descEl) descEl.textContent = 'Đơn hợp lệ (trừ đơn huỷ)';
  }

  if (isRevenueMasked()) {
    cardEl.innerHTML = '<span class="masked-value">••••••••</span>';
  } else {
    cardEl.textContent = new Intl.NumberFormat('vi-VN').format(val) + 'đ';
  }
}

// ---- Modal Cài đặt ----
function openSettingsModal() {
  const modal = document.getElementById('settingsModal');
  if (!modal) return;

  // Sync state vào modal controls
  const rule = getRevenueRule();
  const radio = document.querySelector(`input[name="revenueRule"][value="${rule}"]`);
  if (radio) radio.checked = true;

  const maskToggle = document.getElementById('settingMaskToggle');
  if (maskToggle) maskToggle.checked = isRevenueMasked();

  const soundToggle = document.getElementById('settingSoundToggle');
  if (soundToggle) soundToggle.checked = isSoundEnabled();

  modal.style.display = 'flex';
}

function closeSettingsModal() {
  const modal = document.getElementById('settingsModal');
  if (modal) modal.style.display = 'none';
}

// ---- Realtime notifications (Short Polling) ----
(function initPolling() {
  const statusEl = document.getElementById('realtimeStatus');
  if (!statusEl) return;

  let since = typeof SINCE_TS !== 'undefined' ? SINCE_TS : Math.floor(Date.now() / 1000);
  let isPolling = false;

  async function checkUpdates() {
    if (isPolling) return;
    isPolling = true;

    try {
      const res = await fetch(`../api/check-new.php?since=${since}`, { cache: 'no-store' });
      if (res.ok) {
        const data = await res.json();
        if (data.ok) {
          since = data.ts;
          statusEl.innerHTML = '<span class="realtime-dot"></span> Realtime — Đang kết nối';

          // Cập nhật số lượng badge
          if (data.counts) {
            updateBadge('pendingBadge', data.counts.pending);
            updateBadge('statPending', data.counts.pending);
            updateBadge('msgBadge', data.counts.unread);
            updateBadge('statMsg', data.counts.unread);
          }

          // Hiển thị đơn hàng mới
          if (data.orders && data.orders.length > 0) {
            data.orders.forEach(order => {
              showToast('new_order', order);
              prependOrderRow(order);
            });
            recalculateVisibleTotal();
          }

          // Hiển thị tin nhắn mới
          if (data.messages && data.messages.length > 0) {
            data.messages.forEach(msg => {
              showToast('new_message', msg);
            });
          }
        }
      } else {
        statusEl.innerHTML = '<span class="realtime-dot" style="background:#f59e0b"></span> Realtime — Chờ kết nối';
      }
    } catch (err) {
      statusEl.innerHTML = '<span class="realtime-dot" style="background:#f59e0b"></span> Realtime — Chờ kết nối';
    } finally {
      isPolling = false;
    }
  }

  checkUpdates();
  setInterval(checkUpdates, 4000);
})();

// ---- Toast notifications (Clean SVG, No Emoji) ----
function showToast(type, data) {
  const container = document.getElementById('toastContainer');
  if (!container) return;

  const toast = document.createElement('div');
  toast.className = `toast toast-${type}`;

  if (type === 'new_order') {
    const total = new Intl.NumberFormat('vi-VN').format(data.total);
    const shipText = data.shipping_method === 'pickup' ? 'Nhận tại quán' : 'Giao tận nơi';
    
    toast.innerHTML = `
      <div class="toast-icon">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
      </div>
      <div class="toast-body">
        <div class="toast-title">Đơn hàng mới (${shipText})</div>
        <div class="toast-msg">${escHtml(data.customer_name)} — ${total}đ</div>
        <div class="toast-code">#${escHtml(data.order_code)}</div>
      </div>
      <button class="toast-close" onclick="this.parentElement.remove()" aria-label="Đóng">&times;</button>
    `;
    playBeep();
  } else if (type === 'new_message') {
    toast.innerHTML = `
      <div class="toast-icon">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
      </div>
      <div class="toast-body">
        <div class="toast-title">Tin nhắn mới</div>
        <div class="toast-msg">${escHtml(data.sender_name)}: ${escHtml(data.preview)}...</div>
      </div>
      <button class="toast-close" onclick="this.parentElement.remove()" aria-label="Đóng">&times;</button>
    `;
  }

  container.appendChild(toast);
  requestAnimationFrame(() => toast.classList.add('show'));
  setTimeout(() => {
    toast.classList.remove('show');
    setTimeout(() => toast.remove(), 400);
  }, 8000);
}

function playBeep() {
  if (!isSoundEnabled()) return;
  try {
    const ctx = new (window.AudioContext || window.webkitAudioContext)();
    const osc = ctx.createOscillator();
    const gain = ctx.createGain();
    osc.connect(gain);
    gain.connect(ctx.destination);
    osc.frequency.value = 880;
    gain.gain.setValueAtTime(0.12, ctx.currentTime);
    gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.35);
    osc.start();
    osc.stop(ctx.currentTime + 0.35);
  } catch(e) {}
}

function updateBadge(id, count) {
  const el = document.getElementById(id);
  if (el) el.textContent = count;
}

// Chèn dòng đơn hàng mới vào bảng
function prependOrderRow(order) {
  const tbody = document.getElementById('ordersBody');
  if (!tbody) return;

  const tr = document.createElement('tr');
  tr.className = 'order-row order-row-new';
  tr.dataset.status = 'new';
  tr.dataset.id = order.id;
  tr.dataset.total = order.total;

  const total = new Intl.NumberFormat('vi-VN').format(order.total);
  const isCod = (order.payment_method || 'transfer') === 'cod';
  
  const shipBadge = order.shipping_method === 'pickup'
    ? '<span class="ship-pill"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg> Pickup</span>'
    : '<span class="ship-pill"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg> Giao hàng</span>';

  const payBadge = isCod 
    ? '<span class="pay-pill pay-cod"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="6" width="20" height="12" rx="2"></rect><circle cx="12" cy="12" r="2"></circle></svg> COD</span>'
    : '<span class="pay-pill pay-qr"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><rect x="7" y="7" width="3" height="3"></rect><rect x="14" y="7" width="3" height="3"></rect><rect x="7" y="14" width="3" height="3"></rect><rect x="14" y="14" width="3" height="3"></rect></svg> VietQR</span>';

  const itemsHtml = order.items_summary 
    ? `<div class="order-items-list"><span class="item-pill"><span class="item-name">${escHtml(order.items_summary)}</span></span></div>`
    : '<span style="color:var(--text-dim);font-size:12px;">Đang tải chi tiết...</span>';

  tr.innerHTML = `
    <td class="td-code">
      <a href="order-detail.php?id=${order.id}">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
        <span>${escHtml(order.order_code)}</span>
      </a>
    </td>
    <td class="td-date">Vừa xong</td>
    <td class="td-customer">
      <div class="cust-name">${escHtml(order.customer_name)}</div>
      <div class="cust-phone">${escHtml(order.customer_phone)}</div>
    </td>
    <td class="td-items">${itemsHtml}</td>
    <td>${shipBadge}</td>
    <td>${payBadge}</td>
    <td class="td-total"><strong>${total}đ</strong></td>
    <td>
      <span class="status-pill" style="background:#f59e0b1c;color:#f59e0b">
        <span class="status-dot"></span> Mới
      </span>
    </td>
    <td>
      <select class="status-select" data-id="${order.id}" onchange="updateStatus(this)">
        <option value="new" selected>Mới</option>
        <option value="confirmed">Đã xác nhận</option>
        <option value="making">Đang làm</option>
        <option value="done">Hoàn thành</option>
        <option value="cancelled">Đã huỷ</option>
      </select>
    </td>
  `;

  tbody.prepend(tr);
  setTimeout(() => tr.classList.remove('order-row-new'), 3000);
}

// ---- Status update ----
async function updateStatus(sel) {
  const id     = +sel.dataset.id;
  const status = sel.value;

  try {
    const res  = await fetch('api/update-status.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({ id, status }),
    });
    const json = await res.json();
    if (json.ok) {
      const row = sel.closest('tr');
      if (row) {
        row.dataset.status = status;
        const pill = row.querySelector('.status-pill');
        const COLORS = {
          'new': '#f59e0b',
          'confirmed': '#3b82f6',
          'making': '#8b5cf6',
          'done': '#10b981',
          'cancelled': '#ef4444'
        };
        const LABELS = {
          'new': 'Mới',
          'confirmed': 'Đã xác nhận',
          'making': 'Đang làm',
          'done': 'Hoàn thành',
          'cancelled': 'Đã huỷ'
        };
        if (pill) {
          pill.style.background = COLORS[status] + '1c';
          pill.style.color = COLORS[status];
          pill.innerHTML = `<span class="status-dot"></span> ${LABELS[status]}`;
        }
        applyFilter();
      }
    } else {
      alert('Lỗi cập nhật: ' + (json.error || 'unknown'));
    }
  } catch(e) {
    alert('Lỗi kết nối máy chủ');
  }
}

// ---- Filter + Search + Live Total Calculation (Supports Rules & Masking) ----
let currentFilter = '';

function recalculateVisibleTotal() {
  const rule = getRevenueRule();
  let sum = 0;
  let count = 0;

  document.querySelectorAll('.order-row').forEach(row => {
    if (row.style.display !== 'none') {
      const total = parseFloat(row.dataset.total) || 0;
      const status = row.dataset.status;

      // Áp dụng quy tắc tính doanh thu
      let countThisRow = true;
      if (rule === 'done_only') {
        countThisRow = (status === 'done');
      } else if (rule === 'non_cancelled') {
        countThisRow = (status !== 'cancelled');
      } else if (rule === 'all') {
        countThisRow = true;
      }

      if (countThisRow) {
        sum += total;
      }
      count++;
    }
  });

  const totalEl = document.getElementById('visibleTotalDisplay');
  if (totalEl) {
    if (isRevenueMasked()) {
      totalEl.innerHTML = '<span class="masked-value">••••••••</span>';
    } else {
      totalEl.textContent = new Intl.NumberFormat('vi-VN').format(sum) + 'đ';
    }
  }

  const countEl = document.getElementById('visibleCountDisplay');
  if (countEl) {
    countEl.textContent = count;
  }
}

function applyFilter() {
  const search = (document.getElementById('searchInput')?.value || '').toLowerCase().trim();
  
  document.querySelectorAll('.order-row').forEach(row => {
    const matchStatus = !currentFilter || row.dataset.status === currentFilter;
    const text = row.textContent.toLowerCase();
    const matchSearch = !search || text.includes(search);
    
    row.style.display = matchStatus && matchSearch ? '' : 'none';
  });

  recalculateVisibleTotal();
}

document.addEventListener('DOMContentLoaded', () => {
  // Đồng bộ UI theo cài đặt lưu trong localStorage
  updateMaskUI();

  // Filter tabs
  document.querySelectorAll('.ftab').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.ftab').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      currentFilter = btn.dataset.status;
      applyFilter();
    });
  });

  // Search input
  document.getElementById('searchInput')?.addEventListener('input', applyFilter);

  // Phím ESC đóng modal
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeSettingsModal();
  });
});

// Xuất file Excel với bộ lọc hiện tại và quy tắc tính doanh thu
function exportOrdersExcel() {
  const urlParams = new URLSearchParams(window.location.search);
  const date = urlParams.get('date') || 'today';
  const status = currentFilter || '';
  const search = document.getElementById('searchInput')?.value || '';
  const rule = getRevenueRule();

  const exportUrl = `export-excel.php?date=${encodeURIComponent(date)}&status=${encodeURIComponent(status)}&search=${encodeURIComponent(search)}&rule=${encodeURIComponent(rule)}`;
  window.location.href = exportUrl;
}

// Helper escape HTML
function escHtml(s) {
  return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}
