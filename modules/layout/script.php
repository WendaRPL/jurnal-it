<script>
function toggleUserMenu() {
  document.getElementById("userMenu").classList.toggle("show-menu");
}
document.addEventListener("click", function(e) {
  if (!e.target.closest(".user-dropdown")) {
    document.getElementById("userMenu").classList.remove("show-menu");
  }
});

document.addEventListener("DOMContentLoaded", () => {
  const inboxModal = document.getElementById("inboxModal");
  const openInbox = document.getElementById("openInbox");
  const closeInbox = document.getElementById("closeInbox");
  const notifList = document.getElementById("notifList");
  const notifCount = document.getElementById("notifCount");

  function fetchNotifications() {
    fetch("modules/layout/get_notification.php")
      .then(res => res.json())
      .then(data => {
        notifList.innerHTML = "";
        let unreadCount = 0;

        data.forEach(item => {
          if (item.is_read == 0) unreadCount++;
          const li = document.createElement("li");
          li.className = `notif-item ${item.type}`;
          li.innerHTML = `
            <strong>${item.title || "Notifikasi"}</strong>
            <p>${item.keterangan}</p>
            <small>${item.input_datetime}</small>
          `;
          notifList.appendChild(li);
        });

        notifCount.textContent = unreadCount > 0 ? unreadCount : "";
      });
  }

  // buka modal inbox
  openInbox.addEventListener("click", () => {
    inboxModal.classList.remove("hidden");
    inboxModal.classList.add("show");
    fetchNotifications();
  });

  // tutup modal inbox
  closeInbox.addEventListener("click", () => {
    inboxModal.classList.add("hidden");
    inboxModal.classList.remove("show");
  });

  // refresh notif setiap 30 detik
  setInterval(fetchNotifications, 30000);
  fetchNotifications();
});

// Optional: Adjust truncation based on available space
function adjustBreadcrumb() {
  const breadcrumb = document.querySelector('.breadcrumb');
  const navIcons = document.querySelector('.nav-icons-wrapper');
  
  if (breadcrumb && navIcons) {
    const availableWidth = breadcrumb.parentElement.offsetWidth - navIcons.offsetWidth - 40;
    document.querySelector('.breadcrumb-item').style.maxWidth = availableWidth + 'px';
  }
}

// Jalankan saat load dan resize
window.addEventListener('load', adjustBreadcrumb);
window.addEventListener('resize', adjustBreadcrumb);
</script> 