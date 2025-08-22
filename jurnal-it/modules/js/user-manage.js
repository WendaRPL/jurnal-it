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

// Buka modal
createBtn.addEventListener("click", () => {
  createModal.classList.remove("hidden");
});

// Tutup modal klik X
closeModal.addEventListener("click", () => {
  createModal.classList.add("hidden");
});

// Tutup modal klik di luar modal-content
window.addEventListener("click", (e) => {
  if(e.target === createModal) {
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

    // Toggle accordion ini
    item.classList.toggle('active');
    if (item.classList.contains('active')) {
      content.style.maxHeight = content.scrollHeight + "px"; // dynamic height
      icon.textContent = '-';
    } else {
      content.style.maxHeight = 0;
      icon.textContent = '+';
    }
  });
});

