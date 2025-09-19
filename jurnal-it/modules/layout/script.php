<script>
// ==================== TOAST NOTIFICATION ====================
function toastNotif(type, message) {
  let container = document.querySelector('.toast-container');
  if (!container) {
    container = document.createElement('div');
    container.className = 'toast-container';
    document.body.appendChild(container);
  }
  const notif = document.createElement('div');
  notif.className = `toast ${type}`;
  notif.textContent = message;
  container.appendChild(notif);
  setTimeout(() => { notif.remove(); if (!container.querySelector('.toast')) container.remove(); }, 4000);
}

// ==================== PROFILE MODAL ====================
function openProfileModal() {
  const modal = document.getElementById('profileModal');
  if (modal) {
    modal.classList.remove('hidden');
    modal.classList.add('show');
    document.getElementById('userMenu').classList.remove('show-menu');
  }
}
function closeProfileModal() {
  const modal = document.getElementById('profileModal');
  if (modal) {
    modal.classList.add('hidden');
    modal.classList.remove('show');
  }
}

// ==================== PASSWORD MODAL ====================
function openPasswordModal() {
  const modal = document.getElementById('passwordModal');
  if (modal) {
    modal.classList.remove('hidden');
    modal.classList.add('show');
    document.getElementById('userMenu').classList.remove('show-menu');
  }
}
function closePasswordModal() {
  const modal = document.getElementById('passwordModal');
  if (modal) {
    modal.classList.add('hidden');
    modal.classList.remove('show');
  }
}
function resetPasswordForm() {
  document.getElementById('changePasswordForm').reset();
}
function validatePassword() {
  const newPassword = document.getElementById('newPassword').value;
  const confirmPassword = document.getElementById('confirmPassword').value;

  if (newPassword !== confirmPassword) {
    toastNotif("error", "Password baru dan konfirmasi tidak cocok!");
    return false;
  }
  if (newPassword.length < 6) {
    toastNotif("error", "Password minimal 6 karakter!");
    return false;
  }
  return true;
}

async function handlePasswordChange(e) {
  e.preventDefault();
  if (!validatePassword()) return;

  const currentPassword = document.getElementById('currentPassword').value;
  const newPassword = document.getElementById('newPassword').value;

  try {
    const response = await fetch('direct/change_password.php', {
      method: 'POST',
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ current_password: currentPassword, new_password: newPassword })
    });

    const result = await response.json();
    if (result.success) {
      toastNotif("success", "✅ Password berhasil diubah!");
      resetPasswordForm();
      closePasswordModal();
    } else {
      toastNotif("error", "❌ " + (result.error || "Gagal mengubah password"));
    }
  } catch (err) {
    console.error(err);
    toastNotif("error", "❌ Error saat ubah password");
  }
}

// ==================== EDIT PROFILE FIELDS ====================
function enableEdit(button) {
  const fieldWrapper = button.closest(".profile-field");
  const span = fieldWrapper.querySelector(".field-value");
  const currentValue = span.textContent.trim();
  const fieldName = fieldWrapper.dataset.field;

  const input = document.createElement("input");
  input.type = "text";
  input.value = currentValue;
  input.dataset.originalValue = currentValue;

  span.replaceWith(input);

  button.style.display = "none";
  const saveBtn = document.createElement("button");
  saveBtn.className = "save-btn";
  saveBtn.textContent = "Save";
  saveBtn.onclick = () => saveField(fieldWrapper, fieldName, input.value);

  const cancelBtn = document.createElement("button");
  cancelBtn.className = "cancel-btn";
  cancelBtn.textContent = "Cancel";
  cancelBtn.onclick = () => cancelEdit(fieldWrapper, input);

  fieldWrapper.appendChild(saveBtn);
  fieldWrapper.appendChild(cancelBtn);
}

async function saveField(fieldWrapper, fieldName, newValue) {
  const saveBtn = fieldWrapper.querySelector(".save-btn");
  if (saveBtn) {
    saveBtn.disabled = true;
    saveBtn.classList.add("loading");
    saveBtn.textContent = "Saving...";
  }

  try {
    const response = await fetch("direct/update_profile.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ field: fieldName, value: newValue })
    });

    const result = await response.json();
    if (result.success) {
      const input = fieldWrapper.querySelector("input");
      if (input) {
        const span = document.createElement("span");
        span.className = "field-value";
        span.textContent = newValue;
        input.replaceWith(span);
      }
      cleanupButtons(fieldWrapper);

      if (fieldName === "name") {
        const userMenuName = document.querySelector("#userMenu p");
        if (userMenuName) userMenuName.textContent = "Halo, " + newValue;
      }
      if (fieldName === "initial") {
        const initialSpan = document.querySelector(".profile-field[data-field='initial'] .field-value");
        if (initialSpan) initialSpan.textContent = newValue;
      }

      toastNotif("success", `✅ ${fieldName} berhasil diperbarui`);
    } else {
      toastNotif("error", "❌ Gagal update: " + (result.error || "Unknown error"));
    }
  } catch (error) {
    console.error("❌ Error update field:", error);
    toastNotif("error", "❌ Terjadi error saat update profil");
  } finally {
    if (saveBtn) {
      saveBtn.disabled = false;
      saveBtn.classList.remove("loading");
      saveBtn.textContent = "Save";
    }
  }
}

function cancelEdit(fieldWrapper, input) {
  const span = document.createElement("span");
  span.className = "field-value";
  span.textContent = input.dataset.originalValue;
  input.replaceWith(span);
  cleanupButtons(fieldWrapper);
}

function cleanupButtons(fieldWrapper) {
  fieldWrapper.querySelectorAll(".save-btn, .cancel-btn").forEach(btn => btn.remove());
  const editBtn = fieldWrapper.querySelector(".edit-btn");
  if (editBtn) editBtn.style.display = "inline-block";
}

// ==================== CLOSE MODALS BY BACKDROP ====================
document.addEventListener("click", e => {
  const modals = [
    document.getElementById('profileModal'),
    document.getElementById('passwordModal'),
    document.getElementById('inboxModal')
  ];
  modals.forEach(m => {
    if (m && e.target === m) {
      m.classList.add('hidden');
      m.classList.remove('show');
    }
  });
});

// ==================== USER MENU ====================
function toggleUserMenu() {
  const userMenu = document.getElementById("userMenu");
  if (userMenu) userMenu.classList.toggle("show-menu");
}
document.addEventListener("click", e => {
  const userMenu = document.getElementById("userMenu");
  if (userMenu && !e.target.closest(".user-dropdown")) {
    userMenu.classList.remove("show-menu");
  }
});

// ==================== INBOX NOTIFICATION ====================
let notifCount, notifList, markReadBtn, inboxModal, openInbox, closeInbox;
const API_BASE = "/jurnal-it/modules/layout/";

function formatDateTime(dt) {
  try {
    const d = new Date(dt);
    if (isNaN(d)) return dt;
    const now = new Date();
    const diff = Math.floor((now - d) / (1000 * 60 * 60 * 24));
    if (diff === 0) return d.toLocaleTimeString("id-ID", { hour: "2-digit", minute: "2-digit" });
    if (diff === 1) return "Kemarin " + d.toLocaleTimeString("id-ID", { hour: "2-digit", minute: "2-digit" });
    if (diff < 7) return d.toLocaleDateString("id-ID", { weekday: "short", hour: "2-digit", minute: "2-digit" });
    return d.toLocaleDateString("id-ID", { day: "numeric", month: "short", year: "numeric" });
  } catch { return dt; }
}

async function fetchNotifications() {
  if (!notifList) return;
  try {
    const res = await fetch(`${API_BASE}get_notification.php?action=get&t=${Date.now()}`);
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const data = await res.json();
    const notifications = Array.isArray(data) ? data : data.data || [];
    renderNotifications(notifications);
    updateUnreadCount(notifications);
  } catch (err) {
    console.error("❌ Error fetching notifications:", err);
    notifList.innerHTML = `
      <div class="notif-error">
        <p>❌ Gagal memuat notifikasi</p>
        <small>${err.message}</small><br>
        <button onclick="fetchNotifications()">🔄 Coba Lagi</button>
      </div>`;
  }
}

function renderNotifications(data) {
  notifList.innerHTML = "";
  if (!data.length) {
    notifList.innerHTML = `<div class="notif-empty"><p>📭 Tidak ada notifikasi</p></div>`;
    return;
  }
  data.forEach(item => {
    const li = document.createElement("li");
    li.className = `notif-item ${item.type || "info"} ${item.is_read == 0 ? "unread" : ""}`;
    li.dataset.id = item.id;
    li.innerHTML = `
      <div class="notif-content">
        <div class="notif-title">${item.title || "Notifikasi"}</div>
        <div class="notif-desc">${item.keterangan || ""}</div>
        <div class="notif-time">${formatDateTime(item.input_datetime)}</div>
      </div>
      ${item.is_read == 0 ? `<div class="notif-actions"><button class="notif-read-btn" data-id="${item.id}">✓</button></div>` : ""}
    `;
    const btn = li.querySelector(".notif-read-btn");
    if (btn) btn.addEventListener("click", e => { e.stopPropagation(); markAsRead(item.id, btn); });
    li.addEventListener("click", e => { if (!e.target.classList.contains("notif-read-btn") && btn) markAsRead(item.id, btn); });
    notifList.appendChild(li);
  });
}

function updateUnreadCount(data) {
  if (!notifCount) return;
  const unread = Array.isArray(data) ? data.filter(i => i.is_read == 0).length : (data.unread_count || 0);
  notifCount.textContent = unread > 0 ? unread : "";
  notifCount.style.display = unread > 0 ? "flex" : "none";
}

async function markAsRead(id, el) {
  try {
    const res = await fetch(`${API_BASE}get_notification.php?action=mark-read`, {
      method: "POST", headers: { "Content-Type": "application/json" }, body: JSON.stringify({ notif_id: id })
    });
    const r = await res.json();
    if (r.success) {
      const item = el?.closest(".notif-item");
      if (item) { item.classList.remove("unread"); item.querySelector(".notif-actions")?.remove(); }
      updateUnreadCounter();
    }
  } catch (err) { console.error("❌ Error mark as read:", err); }
}

async function markAllAsRead() {
  try {
    const res = await fetch(`${API_BASE}get_notification.php?action=mark-all-read`, { method: "POST" });
    const r = await res.json();
    if (r.success) {
      document.querySelectorAll(".notif-item.unread").forEach(i => { i.classList.remove("unread"); i.querySelector(".notif-actions")?.remove(); });
      updateUnreadCounter();
    }
  } catch (err) { console.error("❌ Error mark all as read:", err); }
}

async function updateUnreadCounter() {
  try {
    const res = await fetch(`${API_BASE}get_notification.php?action=unread-count`);
    const r = await res.json();
    updateUnreadCount(r);
  } catch (err) { console.error("❌ Error update unread counter:", err); }
}

// ==================== INIT ====================
document.addEventListener("DOMContentLoaded", () => {
  notifCount = document.getElementById("notifCount");
  notifList = document.getElementById("notifList");
  markReadBtn = document.getElementById("markReadBtn");
  inboxModal = document.getElementById("inboxModal");
  openInbox = document.getElementById("openInbox");
  closeInbox = document.getElementById("closeInbox");

  if (openInbox && inboxModal) openInbox.addEventListener("click", () => { inboxModal.classList.remove("hidden"); inboxModal.classList.add("show"); fetchNotifications(); });
  if (closeInbox && inboxModal) closeInbox.addEventListener("click", () => { inboxModal.classList.add("hidden"); inboxModal.classList.remove("show"); });
  if (markReadBtn) markReadBtn.addEventListener("click", markAllAsRead);
  if (inboxModal) inboxModal.addEventListener("click", e => { if (e.target === inboxModal) { inboxModal.classList.add("hidden"); inboxModal.classList.remove("show"); } });

  if (notifList) fetchNotifications();
  setInterval(() => { if (inboxModal && !inboxModal.classList.contains("hidden")) fetchNotifications(); }, 30000);

  // test notif
  toastNotif("success", "✅ Notifikasi siap dipakai!");
});

// ==================== BREADCRUMB ====================
function adjustBreadcrumb() {
  const breadcrumb = document.querySelector(".breadcrumb");
  const navIcons = document.querySelector(".nav-icons-wrapper");
  if (breadcrumb && navIcons) {
    const w = breadcrumb.parentElement.offsetWidth - navIcons.offsetWidth - 40;
    breadcrumb.querySelector(".breadcrumb-item")?.style.setProperty("max-width", w + "px");
  }
}
window.addEventListener("load", adjustBreadcrumb);
window.addEventListener("resize", adjustBreadcrumb);
</script>