// ============================================================
// LENA BAKERY — admin.js
// SSE realtime notifications + status update + table filter
// ============================================================

// ---- Realtime notifications (Short Polling - Non-blocking) ----
(function initPolling() {
  const statusEl = document.getElementById('realtimeStatus');
  if (!statusEl) return;

  let since = typeof SINCE_TS !== 'undefined' ? SINCE_TS : Math.floor(Date.now()/1000);
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
          statusEl.textContent = '⚡ Realtime — đang theo dõi';

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
          }

          // Hiển thị tin nhắn mới
          if (data.messages && data.messages.length > 0) {
            data.messages.forEach(msg => {
              showToast('new_message', msg);
            });
          }
        }
      } else {
        statusEl.textContent = '⚡ Realtime — chờ kết nối';
      }
    } catch (err) {
      statusEl.textContent = '⚡ Realtime — chờ kết nối';
    } finally {
      isPolling = false;
    }
  }

  // Chạy ngay lần đầu và lặp lại mỗi 4 giây (không giữ connection liên tục)
  checkUpdates();
  setInterval(checkUpdates, 4000);
})();

// ---- Toast notifications ----
function showToast(type, data) {
  const container = document.getElementById('toastContainer');
  if (!container) return;

  const toast = document.createElement('div');
  toast.className = `toast toast-${type}`;

  if (type === 'new_order') {
    const total = new Intl.NumberFormat('vi-VN').format(data.total);
    const method = data.shipping_method === 'pickup' ? '🏪' : '🛵';
    toast.innerHTML = `
      <div class="toast-icon">📦</div>
      <div class="toast-body">
        <div class="toast-title">Đơn mới! ${method}</div>
        <div class="toast-msg">${escHtml(data.customer_name)} — ${total}đ</div>
        <div class="toast-code">#${escHtml(data.order_code)}</div>
      </div>
      <button class="toast-close" onclick="this.parentElement.remove()">×</button>
    `;
    // Play a soft notification sound
    playBeep();
  } else if (type === 'new_message') {
    toast.innerHTML = `
      <div class="toast-icon">💬</div>
      <div class="toast-body">
        <div class="toast-title">Tin nhắn mới</div>
        <div class="toast-msg">${escHtml(data.sender_name)}: ${escHtml(data.preview)}...</div>
      </div>
      <button class="toast-close" onclick="this.parentElement.remove()">×</button>
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
  try {
    const ctx = new AudioContext();
    const osc = ctx.createOscillator();
    const gain = ctx.createGain();
    osc.connect(gain); gain.connect(ctx.destination);
    osc.frequency.value = 880;
    gain.gain.setValueAtTime(0.15, ctx.currentTime);
    gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.4);
    osc.start(); osc.stop(ctx.currentTime + 0.4);
  } catch(e) {}
}

function updateBadge(id, count) {
  const el = document.getElementById(id);
  if (el) el.textContent = count;
}

function incrementBadge(id) {
  const el = document.getElementById(id);
  if (el) el.textContent = +el.textContent + 1;
}

function incrementStat(id) {
  const el = document.getElementById(id);
  if (el) el.textContent = +el.textContent + 1;
}

function prependOrderRow(order) {
  const tbody = document.getElementById('ordersBody');
  if (!tbody) return;
  const tr = document.createElement('tr');
  tr.className = 'order-row order-row-new';
  tr.dataset.status = 'new';
  tr.dataset.id = order.id;
  const total = new Intl.NumberFormat('vi-VN').format(order.total);
  const isCod = (order.payment_method || 'transfer') === 'cod';
  const payBadge = isCod 
    ? '<span class="pay-pill pay-cod">💵 COD</span>'
    : '<span class="pay-pill pay-qr">⚡ QR Chuyển khoản</span>';

  tr.innerHTML = `
    <td class="td-code"><a href="order-detail.php?id=${order.id}">${escHtml(order.order_code)}</a></td>
    <td class="td-date">vừa xong</td>
    <td class="td-customer">
      <div class="cust-name">${escHtml(order.customer_name)}</div>
      <div class="cust-phone">${escHtml(order.customer_phone)}</div>
    </td>
    <td>${order.shipping_method === 'pickup' ? '🏪 Pickup' : '🛵 Giao'}</td>
    <td>${payBadge}</td>
    <td class="td-total"><strong>${total}đ</strong></td>
    <td><span class="status-pill" style="background:#f59e0b22;color:#f59e0b">🆕 Mới</span></td>
    <td><select class="status-select" data-id="${order.id}" onchange="updateStatus(this)">
      <option value="new" selected>🆕 Mới</option>
      <option value="confirmed">✅ Đã xác nhận</option>
      <option value="making">👨‍🍳 Đang làm</option>
      <option value="done">🎉 Hoàn thành</option>
      <option value="cancelled">❌ Đã huỷ</option>
    </select></td>
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
        const COLORS = {'new':'#f59e0b','confirmed':'#3b82f6','making':'#8b5cf6','done':'#10b981','cancelled':'#ef4444'};
        const LABELS = {'new':'🆕 Mới','confirmed':'✅ Đã xác nhận','making':'👨‍🍳 Đang làm','done':'🎉 Hoàn thành','cancelled':'❌ Đã huỷ'};
        if (pill) {
          pill.style.background = COLORS[status] + '22';
          pill.style.color = COLORS[status];
          pill.textContent = LABELS[status];
        }
        applyFilter();
      }
    } else { alert('Lỗi: ' + (json.error || 'unknown')); }
  } catch(e) { alert('Lỗi kết nối'); }
}

// ---- Filter + Search ----
let currentFilter = '';

function applyFilter() {
  const search = (document.getElementById('searchInput')?.value || '').toLowerCase();
  document.querySelectorAll('.order-row').forEach(row => {
    const matchStatus = !currentFilter || row.dataset.status === currentFilter;
    const text = row.textContent.toLowerCase();
    const matchSearch = !search || text.includes(search);
    row.style.display = matchStatus && matchSearch ? '' : 'none';
  });
}

document.addEventListener('DOMContentLoaded', () => {
  // Filter tabs
  document.querySelectorAll('.ftab').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.ftab').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      currentFilter = btn.dataset.status;
      applyFilter();
    });
  });

  // Search
  document.getElementById('searchInput')?.addEventListener('input', applyFilter);
});

// ---- Utilities ----
function escHtml(s) {
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}
