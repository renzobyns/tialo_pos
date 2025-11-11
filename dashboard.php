<?php
include 'includes/auth_check.php';
checkRole('Admin');
include 'includes/db_connect.php';
include 'includes/sidebar.php';

$today = date('Y-m-d');

$sales_query = "SELECT SUM(total_amount) as daily_sales FROM transactions WHERE DATE(transaction_date) = '$today'";
$sales_result = $conn->query($sales_query);
$daily_sales = $sales_result->fetch_assoc()['daily_sales'] ?? 0;

$low_stock_query = "SELECT COUNT(*) as low_stock_count FROM products WHERE quantity < 5 AND status = 'Available'";
$low_stock_result = $conn->query($low_stock_query);
$low_stock_count = $low_stock_result->fetch_assoc()['low_stock_count'];

$total_products = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
$today_transactions = $conn->query("SELECT COUNT(*) as count FROM transactions WHERE DATE(transaction_date) = '$today'")->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Tialo Japan Surplus</title>
    <style>
      :root {
        --color-bg-app: #F5F3F4;
        --color-surface: #FFFFFF;
        --color-text-strong: #161A1D;
        --color-text-muted: #B1A7A6;
        --color-border: #D3D3D3;
        --color-primary: #BA181B;
        --color-primary-hover: #A4161A;
        --color-accent: #E5383B;
      }

      * { margin: 0; padding: 0; box-sizing: border-box; }
      body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
      
      .kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
      }

      .kpi-card {
        background: var(--color-surface);
        border: 1px solid var(--color-border);
        border-radius: 10px;
        padding: 20px;
        display: flex;
        gap: 14px;
      }

      .kpi-icon {
        width: 48px;
        height: 48px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        flex-shrink: 0;
      }

      .kpi-icon.sales { background: #FEE; color: var(--color-primary); }
      .kpi-icon.stock { background: #FEF3C7; color: #D97706; }
      .kpi-icon.items { background: #DBEAFE; color: #2563EB; }
      .kpi-icon.txn { background: #F3E8FF; color: #7C3AED; }

      .kpi-content h3 {
        font-size: 12px;
        font-weight: 600;
        color: var(--color-text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
      }

      .kpi-value {
        font-size: 24px;
        font-weight: 700;
        color: var(--color-text-strong);
      }

      .data-table {
        background: var(--color-surface);
        border: 1px solid var(--color-border);
        border-radius: 10px;
        overflow: hidden;
      }

      .table-header {
        background: linear-gradient(135deg, #0B090A 0%, #161A1D 100%);
        color: white;
        padding: 16px 20px;
        display: flex;
        align-items: center;
        gap: 8px;
      }

      .table-header h3 {
        font-size: 14px;
        font-weight: 600;
      }

      .table-body {
        padding: 0;
      }

      table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
      }

      thead tr {
        border-bottom: 1px solid var(--color-border);
        background: #FAFAF9;
      }

      th {
        padding: 10px 16px;
        text-align: left;
        font-weight: 600;
        color: var(--color-text-strong);
      }

      tbody tr {
        border-bottom: 1px solid #F3F2F1;
      }

      tbody tr:hover {
        background: #F9F8F8;
      }

      td {
        padding: 10px 16px;
        color: var(--color-text-strong);
      }

      .status-badge {
        display: inline-block;
        padding: 3px 8px;
        background: #FEF2F2;
        color: #991B1B;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
      }

      .section-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
        gap: 20px;
        margin-bottom: 24px;
      }

      @media (max-width: 768px) {
        .section-grid {
          grid-template-columns: 1fr;
        }
      }

      .empty-state {
        text-align: center;
        padding: 32px 20px;
        color: var(--color-text-muted);
      }
    </style>
</head>
<body style="background: var(--color-bg-app);">
    <!-- Replaced header with sidebar include -->
    
    <div class="app-topbar">
      <div class="app-topbar-title">
        <h1>Dashboard</h1>
      </div>
    </div>
    
    <div class="app-content">
      <!-- KPI Cards -->
      <div class="kpi-grid">
        <div class="kpi-card">
          <div class="kpi-icon sales">₱</div>
          <div class="kpi-content">
            <h3>Daily Sales</h3>
            <div class="kpi-value"><?php echo number_format($daily_sales, 0); ?></div>
          </div>
        </div>

        <div class="kpi-card">
          <div class="kpi-icon stock">⚠</div>
          <div class="kpi-content">
            <h3>Low Stock Items</h3>
            <div class="kpi-value"><?php echo $low_stock_count; ?></div>
          </div>
        </div>

        <div class="kpi-card">
          <div class="kpi-icon items">📦</div>
          <div class="kpi-content">
            <h3>Total Products</h3>
            <div class="kpi-value"><?php echo $total_products; ?></div>
          </div>
        </div>

        <div class="kpi-card">
          <div class="kpi-icon txn">📄</div>
          <div class="kpi-content">
            <h3>Transactions Today</h3>
            <div class="kpi-value"><?php echo $today_transactions; ?></div>
          </div>
        </div>
      </div>

      <!-- Tables Section -->
      <div class="section-grid">
        <!-- Top Selling Products -->
        <div class="data-table">
          <div class="table-header">
            <span style="font-size: 18px;">🔥</span>
            <h3>Top Selling Products</h3>
          </div>
          <div class="table-body">
            <table>
              <thead>
                <tr>
                  <th>Product</th>
                  <th style="text-align: center;">Qty</th>
                  <th style="text-align: right;">Revenue</th>
                </tr>
              </thead>
              <tbody>
                <?php 
                $top_query = "SELECT p.name, SUM(ti.quantity) as qty_sold, SUM(ti.subtotal) as revenue 
                             FROM transaction_items ti 
                             JOIN products p ON ti.product_id = p.product_id 
                             JOIN transactions t ON ti.transaction_id = t.transaction_id 
                             WHERE DATE(t.transaction_date) = '$today'
                             GROUP BY p.product_id 
                             ORDER BY qty_sold DESC LIMIT 5";
                $top_result = $conn->query($top_query);
                if ($top_result->num_rows > 0) {
                    while ($row = $top_result->fetch_assoc()): 
                ?>
                    <tr>
                      <td><?php echo htmlspecialchars($row['name']); ?></td>
                      <td style="text-align: center; font-weight: 500;"><?php echo $row['qty_sold']; ?></td>
                      <td style="text-align: right; color: var(--color-primary); font-weight: 600;">₱<?php echo number_format($row['revenue'], 2); ?></td>
                    </tr>
                <?php 
                    endwhile;
                } else {
                    echo '<tr><td colspan="3" class="empty-state">No sales today</td></tr>';
                }
                ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Low Stock Alert -->
        <div class="data-table">
          <div class="table-header">
            <span style="font-size: 18px; color: #FCD34D;">🔔</span>
            <h3>Low Stock Alert</h3>
          </div>
          <div class="table-body">
            <table>
              <thead>
                <tr>
                  <th>Product</th>
                  <th style="text-align: center;">Stock</th>
                  <th style="text-align: right;">Status</th>
                </tr>
              </thead>
              <tbody>
                <?php 
                $low_query = "SELECT product_id, name, quantity FROM products WHERE quantity < 5 AND status = 'Available' ORDER BY quantity ASC LIMIT 5";
                $low_result = $conn->query($low_query);
                if ($low_result->num_rows > 0) {
                    while ($row = $low_result->fetch_assoc()): 
                ?>
                    <tr>
                      <td><?php echo htmlspecialchars($row['name']); ?></td>
                      <td style="text-align: center; font-weight: 500;"><?php echo $row['quantity']; ?></td>
                      <td style="text-align: right;">
                        <span class="status-badge">Low Stock</span>
                      </td>
                    </tr>
                <?php 
                    endwhile;
                } else {
                    echo '<tr><td colspan="3" class="empty-state">All stock levels healthy ✓</td></tr>';
                }
                ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    </div> <!-- Close app-container from sidebar.php -->
</body>
</html>
