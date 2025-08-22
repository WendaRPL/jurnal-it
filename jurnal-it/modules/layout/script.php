<script>
function toggleUserMenu() {
  document.getElementById("userMenu").classList.toggle("show-menu");
}
document.addEventListener("click", function(e) {
  if (!e.target.closest(".user-dropdown")) {
    document.getElementById("userMenu").classList.remove("show-menu");
  }
});
</script> 