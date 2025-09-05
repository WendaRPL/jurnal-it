// Live Search untuk accordion
document.getElementById('searchInput').addEventListener('keyup', function() {
  let filter = this.value.toLowerCase();
  let items = document.querySelectorAll(".accordion-item");

  items.forEach(item => {
    let text = item.innerText.toLowerCase();
    item.style.display = text.includes(filter) ? "" : "none";
  });
});

// Modal Create
const createBtn = document.querySelector(".btn.btn-outline-info");
const createModal = document.getElementById("createUserModal");
const closeModal = document.getElementById("closeCreateModal");

// Buka modal create
createBtn.addEventListener("click", () => {
  createModal.classList.remove("hidden");
});

// Tutup modal create
closeModal.addEventListener("click", () => {
  createModal.classList.add("hidden");
});

// Tutup modal create kalau klik di luar
window.addEventListener("click", (e) => {
  if (e.target === createModal) {
    createModal.classList.add("hidden");
  }
});

// Accordion
document.querySelectorAll('.accordion-header').forEach(header => {
  header.addEventListener('click', () => {
    const item = header.parentElement;
    const content = item.querySelector('.accordion-content');
    const icon = header.querySelector('.icon');

    // Tutup accordion lain
    document.querySelectorAll('.accordion-item.active').forEach(openItem => {
      if (openItem !== item) {
        openItem.classList.remove('active');
        openItem.querySelector('.accordion-content').style.maxHeight = 0;
        openItem.querySelector('.icon').textContent = '+';
      }
    });

    // Toggle accordion
    item.classList.toggle('active');
    if (item.classList.contains('active')) {
      content.style.maxHeight = content.scrollHeight + "px";
      icon.textContent = '-';
    } else {
      content.style.maxHeight = 0;
      icon.textContent = '+';
    }
  });
});

document.addEventListener("DOMContentLoaded", () => {
    const editUserModal = document.getElementById("editUserModal");
    const closeEditModal = document.getElementById("closeEditModal");

    // 🔹 Buka Modal Edit
    document.querySelectorAll(".edit-user-btn").forEach(btn => {
        btn.addEventListener("click", () => {
            document.getElementById("editUserId").value = btn.dataset.id;
            document.getElementById("editUsername").value = btn.dataset.username;
            document.getElementById("editUserName").value = btn.dataset.name;
            document.getElementById("editInitial").value = btn.dataset.initial;
            document.getElementById("editUserRole").value = btn.dataset.role;
            document.getElementById("editPassword").value = "";
            editUserModal.classList.remove("hidden");
        });
    });

    // 🔹 Tutup Modal
    closeEditModal.addEventListener("click", () => editUserModal.classList.add("hidden"));
    editUserModal.addEventListener("click", e => {
        if (e.target === editUserModal) editUserModal.classList.add("hidden");
    });
});

// 🔹 Notif Function (untuk notifikasi dari URL parameter)
function checkNotification() {
    const urlParams = new URLSearchParams(window.location.search);
    const msg = urlParams.get('msg');
    
    if (msg === 'user_updated') {
        showNotif('updated', 'User berhasil diperbarui!');
    } else if (msg === 'update_failed') {
        showNotif('error', 'Gagal memperbarui user');
    }
    
    // Hapus parameter dari URL tanpa reload
    window.history.replaceState({}, document.title, window.location.pathname);
}

// Jalankan saat page load
checkNotification();