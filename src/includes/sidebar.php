<?php
if (!isset($_SESSION['user_id'])) {
    header("Location: /index.php?page=auth/login");
    exit();
}

// Determine current page for active state from the 'page' query parameter
$current_page = $_GET['page'] ?? 'home';

function is_active_route($route_name, $current_page) {
    if ($current_page === $route_name) {
        return true;
    }
    // Handle cases where the route is a parent of the current page
    // e.g., 'inventory' should be active for 'inventory/product_form'
    if (strpos($current_page, $route_name . '/') === 0) {
        return true;
    }
    return false;
}

$nav_items = [];

// Admin navigation
if ($_SESSION['role'] === 'Admin') {
    $nav_items = [
        ['label' => 'Dashboard', 'path' => '/index.php?page=dashboard', 'icon' => 'dashboard', 'active' => is_active_route('dashboard', $current_page)],
        ['label' => 'POS', 'path' => '/index.php?page=pos', 'icon' => 'pos', 'active' => is_active_route('pos', $current_page)],
        ['label' => 'Inventory', 'path' => '/index.php?page=inventory', 'icon' => 'inventory', 'active' => is_active_route('inventory', $current_page)],
        ['label' => 'Reports', 'path' => '/index.php?page=reports', 'icon' => 'reports', 'active' => is_active_route('reports', $current_page)],
        ['label' => 'Users', 'path' => '/index.php?page=users', 'icon' => 'users', 'active' => is_active_route('users', $current_page)],
    ];
} else {
    // Cashier navigation
    $nav_items = [
        ['label' => 'POS', 'path' => '/index.php?page=pos', 'icon' => 'pos', 'active' => is_active_route('pos', $current_page)],
    ];
}
?>

<style>
  :root {
    --color-bg-app: #F5F3F4;
    --color-surface: #FFFFFF;
    --color-text-strong: #161A1D;
    --color-text-muted: #B1A7A6;
    --color-border: #D3D3D3;
    --color-primary: #BA181B;
    --color-primary-hover: #A4161A;
    --color-primary-active: #660708;
    --color-accent: #E5383B;
    --color-neutral-dark: #0B090A;
    --color-neutral-light: #D3D3D3;
  }

  body {
    background-color: var(--color-bg-app);
    color: var(--color-text-strong);
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
  }

  .sidebar {
    background-color: var(--color-neutral-dark);
    color: white;
    width: 240px;
    position: fixed;
    height: 100vh;
    left: 0;
    top: 0;
    overflow-y: auto;
    border-right: 1px solid var(--color-border);
    z-index: 50;
    transition: width 0.2s ease;
  }

  .sidebar-collapse-btn {
    width: 100%;
    background: transparent;
    border: none;
    color: rgba(255, 255, 255, 0.7);
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 20px;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    cursor: pointer;
  }

  .sidebar-collapse-btn svg {
    width: 18px;
    height: 18px;
  }

  .sidebar-brand {
    padding: 24px 20px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  }

  .sidebar-brand-circle {
    width: 48px;
    height: 48px;
    background-color: var(--color-primary);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 20px;
    margin-bottom: 12px;
  }

  .sidebar-brand-text h3 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    line-height: 1.2;
  }

  .sidebar-brand-text p {
    margin: 4px 0 0 0;
    font-size: 12px;
    color: rgba(255, 255, 255, 0.6);
  }

  .sidebar-nav {
    padding: 16px 0;
  }

  .sidebar-nav-item {
    display: flex;
    align-items: center;
    padding: 12px 20px;
    color: rgba(255, 255, 255, 0.7);
    text-decoration: none;
    transition: all 0.2s;
    cursor: pointer;
    border-left: 4px solid transparent;
  }

  .sidebar-nav-item:hover {
    background-color: rgba(255, 255, 255, 0.1);
    color: white;
  }

  .sidebar-nav-item.active {
    background-color: var(--color-primary);
    color: white;
    border-left-color: var(--color-accent);
  }

  .sidebar-nav-icon {
    width: 20px;
    height: 20px;
    margin-right: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .sidebar-nav-label {
    font-size: 14px;
    font-weight: 500;
  }

  .sidebar-footer {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 16px 20px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    background-color: rgba(0, 0, 0, 0.2);
  }

  .sidebar-user {
    display: flex;
    align-items: center;
    margin-bottom: 12px;
    font-size: 12px;
  }

  .sidebar-user-avatar {
    width: 32px;
    height: 32px;
    background-color: var(--color-primary);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 8px;
    font-weight: 600;
    font-size: 14px;
  }

  .sidebar-user-info {
    flex: 1;
  }

  .sidebar-user-info p {
    margin: 0;
    line-height: 1.3;
  }

  .sidebar-user-name {
    color: white;
    font-weight: 500;
  }

  .sidebar-user-role {
    color: rgba(255, 255, 255, 0.6);
    font-size: 11px;
  }

  .sidebar-logout {
    display: flex;
    align-items: center;
    width: 100%;
    padding: 8px 0;
    color: rgba(255, 255, 255, 0.7);
    text-decoration: none;
    border: none;
    background: none;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    padding-top: 12px;
    cursor: pointer;
    transition: color 0.2s;
    font-size: 13px;
    font-weight: 500;
  }

  .sidebar-logout:hover {
    color: white;
  }

  .app-container {
    margin-left: 240px;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    width: calc(100% - 240px);
    transition: margin-left 0.2s ease, width 0.2s ease;
  }

  .app-content {
    flex: 1;
    padding: 24px 32px;
    width: 100%;
  }

  .page-header {
    padding: 16px 32px;
  }

  body.sidebar-collapsed .sidebar {
    width: 72px;
  }

  body.sidebar-collapsed .sidebar-brand {
    padding: 18px 12px;
  }

  body.sidebar-collapsed .sidebar-collapse-btn span,
  body.sidebar-collapsed .sidebar-brand-text,
  body.sidebar-collapsed .sidebar-nav-label,
  body.sidebar-collapsed .sidebar-user-info,
  body.sidebar-collapsed .sidebar-logout span {
    opacity: 0;
    pointer-events: none;
    position: absolute;
    left: -9999px;
  }

  body.sidebar-collapsed .sidebar-nav-item {
    justify-content: center;
  }

  body.sidebar-collapsed .sidebar-nav-icon {
    margin-right: 0;
  }

  body.sidebar-collapsed .sidebar-user {
    justify-content: center;
  }

  body.sidebar-collapsed .app-container {
    margin-left: 72px;
    width: calc(100% - 72px);
  }

  @media (max-width: 768px) {
    .sidebar {
      width: 200px;
    }

    .app-container {
      margin-left: 0;
      width: 100%;
    }

    .sidebar {
      transform: translateX(-100%);
      transition: transform 0.3s;
    }

    .sidebar.open {
      transform: translateX(0);
    }

    .app-topbar {
      padding: 12px 16px;
    }

    .app-content {
      padding: 16px;
    }
  }
</style>

<aside class="sidebar" id="mobileSidebar">
  <!-- Close button for mobile -->
  <button class="absolute top-2 right-2 md:hidden" id="closeSidebarButton">
    <i class="fas fa-times text-white text-2xl"></i>
  </button>
  <button class="sidebar-collapse-btn hidden md:flex" id="sidebarToggle" type="button">
    <span>Navigation</span>
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <path d="M4 12h16M4 6h16M4 18h16"/>
    </svg>
  </button>
  <!-- Brand -->
  <div class="sidebar-brand text-center">
    <img src="assets/img/logo_light.jpg" alt="Tialo POS Logo" class="h-10 mx-auto mb-2">
    <div class="sidebar-brand-text">
      <h3>Tialo Japan</h3>
      <p>Surplus POS</p>
    </div>
  </div>

  <!-- Navigation -->
  <nav class="sidebar-nav">
    <?php foreach ($nav_items as $item): ?>
      <a href="<?php echo htmlspecialchars($item['path']); ?>" 
         class="sidebar-nav-item <?php echo $item['active'] ? 'active' : ''; ?>">
        <span class="sidebar-nav-icon">
          <?php
          // Inline SVG icons (Heroicons style)
          switch ($item['icon']) {
            case 'dashboard':
              echo '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>';
              break;
            case 'pos':
              echo '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M2 17h20"/><path d="M6 21h12"/></svg>';
              break;
            case 'inventory':
              echo '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="2"/><path d="M2 6h20M6 2v20M18 2v20"/></svg>';
              break;
            case 'reports':
              echo '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"/><path d="M18 17V9M12 17v-5M6 17v-3"/></svg>';
              break;
            case 'users':
              echo '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>';
              break;
          }
          ?>
        </span>
        <span class="sidebar-nav-label"><?php echo htmlspecialchars($item['label']); ?></span>
      </a>
    <?php endforeach; ?>
  </nav>

  <!-- Footer -->
  <div class="sidebar-footer">
    <div class="sidebar-user">
      <div class="sidebar-user-avatar">
        <?php echo strtoupper(substr($_SESSION['name'], 0, 1)); ?>
      </div>
      <div class="sidebar-user-info">
        <p class="sidebar-user-name"><?php echo htmlspecialchars(substr($_SESSION['name'], 0, 12)); ?></p>
        <p class="sidebar-user-role"><?php echo htmlspecialchars($_SESSION['role']); ?></p>
      </div>
    </div>
    <a href="/index.php?page=auth/logout" 
       class="sidebar-logout">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px; margin-right: 8px;">
        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5m0 0l-5-5M21 12H9"/>
      </svg>
      <span>Logout</span>
    </a>
  </div>
</aside>
<!-- Sidebar Overlay -->
<div id="sidebarOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-49 hidden md:hidden"></div>
<script>
  (function () {
    // Desktop sidebar collapse
    const toggle = document.getElementById('sidebarToggle');
    const body = document.body;
    if (localStorage.getItem('sidebar-collapsed') === 'true') {
      body.classList.add('sidebar-collapsed');
    }
    if (toggle) {
      toggle.addEventListener('click', function () {
        body.classList.toggle('sidebar-collapsed');
        localStorage.setItem('sidebar-collapsed', body.classList.contains('sidebar-collapsed'));
      });
    }

    // Mobile sidebar toggle
    const mobileMenuButton = document.getElementById('mobileMenuButton');
    const closeSidebarButton = document.getElementById('closeSidebarButton');
    const sidebar = document.getElementById('mobileSidebar');
    const overlay = document.getElementById('sidebarOverlay');

    if (mobileMenuButton && sidebar && overlay) {
      mobileMenuButton.addEventListener('click', function() {
        sidebar.classList.add('open');
        overlay.classList.remove('hidden');
      });

      const closeAction = function() {
        sidebar.classList.remove('open');
        overlay.classList.add('hidden');
      };

      closeSidebarButton.addEventListener('click', closeAction);
      overlay.addEventListener('click', closeAction);
    }
  })();
</script>

<div class="app-container">
