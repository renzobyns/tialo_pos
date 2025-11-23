<div id="toastContainer" class="fixed top-4 right-4 z-50 space-y-2"></div>

<script>
function showToast(message, type = "info") {
  const container = document.getElementById("toastContainer")
  if (!container) return
  const el = document.createElement("div")
  const colors =
    type === "success"
      ? "bg-emerald-600 text-white border border-emerald-700"
      : type === "error"
        ? "bg-red-600 text-white border border-red-700"
        : "bg-slate-800 text-white border border-slate-900"
  el.className = `px-4 py-3 rounded-xl shadow-lg text-sm font-semibold ${colors}`
  el.textContent = message
  container.appendChild(el)
  setTimeout(() => {
    el.style.opacity = "0"
    el.style.transition = "opacity 0.3s ease"
    setTimeout(() => el.remove(), 300)
  }, 3000)
}
</script>

<?php
if (isset($_SESSION['toast_message'])) {
    $toast = $_SESSION['toast_message'];
    // Ensure message is properly escaped for JavaScript
    $message = addslashes($toast['message']);
    $type = addslashes($toast['type']);
    echo "<script>document.addEventListener('DOMContentLoaded', function() { showToast('{$message}', '{$type}'); });</script>";
    unset($_SESSION['toast_message']);
}
?>

<?php echo $page_scripts ?? ''; ?>
</body>
</html>