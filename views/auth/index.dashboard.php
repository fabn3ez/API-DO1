<?php
// views/dashboard/index.php
// Sophisticated Dashboard for Farm Management System
// This file both renders the dashboard and serves JSON data at ?action=data
// Replace sample data arrays with real DB queries where needed.

// ---------------------- Sample Data (replace with DB queries) ----------------------
$sampleFieldsCount = 12;
$sampleCropsCount = 34;
$sampleAnimalsCount = 58;
$sampleInventoryValue = 24560.75; // currency
$sampleSalesMonth = 8230.40;

$productionTrend = [
    // last 12 months sample production (units)
    'labels' => ['Nov','Dec','Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct'],
    'values' => [120,140,150,135,160,170,180,210,200,230,240,250]
];

$livestockDistribution = [
    'labels' => ['Cattle','Goats','Sheep','Poultry','Other'],
    'values' => [22, 15, 8, 10, 3]
];

$recentActivities = [
    ["time" => "2025-10-15 09:52", "desc" => "New user registered (farmer@example.com)"],
    ["time" => "2025-10-14 16:23", "desc" => "Crop 'Maize - Field A' updated (harvested)"],
    ["time" => "2025-10-13 11:05", "desc" => "Inventory adjustment: Fertilizer +50kg"],
    ["time" => "2025-10-12 08:40", "desc" => "Sale recorded: Order #FMS-302"],
];

$latestOrders = [
    ["order_id"=>"FMS-302","customer"=>"Local Market","amount"=>420.00,"status"=>"Completed","date"=>"2025-10-14"],
    ["order_id"=>"FMS-301","customer"=>"AgroShop Ltd","amount"=>1390.00,"status"=>"Pending","date"=>"2025-10-13"],
    ["order_id"=>"FMS-300","customer"=>"John Doe","amount"=>78.50,"status"=>"Delivered","date"=>"2025-10-10"],
    ["order_id"=>"FMS-299","customer"=>"County Co-op","amount"=>2500.00,"status"=>"Completed","date"=>"2025-10-08"],
];

// ---------------------- JSON API endpoint ----------------------
if (isset($_GET['action']) && $_GET['action'] === 'data') {
    header('Content-Type: application/json');
    echo json_encode([
        'kpis' => [
            'fields' => $sampleFieldsCount,
            'crops' => $sampleCropsCount,
            'animals' => $sampleAnimalsCount,
            'inventory_value' => $sampleInventoryValue,
            'sales_month' => $sampleSalesMonth
        ],
        'productionTrend' => $productionTrend,
        'livestockDistribution' => $livestockDistribution,
        'recentActivities' => $recentActivities,
        'latestOrders' => $latestOrders
    ]);
    exit;
}

// ---------------------- Helper functions ----------------------
function currency($amount) {
    return number_format($amount, 2);
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Farm Management — Dashboard</title>

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Feather icons -->
  <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>

  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

  <style>
    :root{
      --accent: #2a9d8f;
      --muted: #6c757d;
      --card-bg: #ffffff;
    }
    body { background: #f4f7fb; font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial; }
    .sidebar { min-width: 260px; max-width: 260px; height: 100vh; position: fixed; background: linear-gradient(180deg,#133a3a,#0b6b61); color: #fff; padding: 20px; }
    .sidebar a { color: rgba(255,255,255,0.9); text-decoration: none; }
    .sidebar .logo { font-weight:700; font-size: 1.25rem; letter-spacing: .5px; }
    .content { margin-left: 280px; padding: 28px; }
    .kpi-card { border-radius: 14px; box-shadow: 0 6px 18px rgba(15,23,42,0.06); }
    .small-muted { color: var(--muted); font-size: .85rem; }
    .table-wrap { max-height: 320px; overflow: auto; }
    .activity-item { border-left: 3px solid rgba(42,157,143,0.1); padding-left: 12px; margin-bottom: 12px; }
    @media (max-width: 992px) {
        .sidebar{ display:none; position:static; height:auto; }
        .content{ margin-left: 0; padding: 16px; }
    }
  </style>
</head>
<body>

  <!-- SIDEBAR -->
  <aside class="sidebar d-flex flex-column">
    <div class="mb-4">
      <div class="logo mb-2">Farm<span style="color:#ffd166">Manager</span></div>
      <div class="small-muted">Smart farm operations dashboard</div>
    </div>

    <nav class="mt-4">
      <ul class="nav flex-column">
        <li class="nav-item mb-2"><a class="nav-link" href="#"><i data-feather="grid"></i> Dashboard</a></li>
        <li class="nav-item mb-2"><a class="nav-link" href="#"><i data-feather="layers"></i> Fields & Crops</a></li>
        <li class="nav-item mb-2"><a class="nav-link" href="#"><i data-feather="activity"></i> Livestock</a></li>
        <li class="nav-item mb-2"><a class="nav-link" href="#"><i data-feather="box"></i> Inventory</a></li>
        <li class="nav-item mb-2"><a class="nav-link" href="#"><i data-feather="shopping-cart"></i> Sales</a></li>
        <li class="nav-item mb-2"><a class="nav-link" href="#"><i data-feather="bar-chart-2"></i> Reports</a></li>
        <li class="nav-item mt-4"><a class="nav-link" href="#"><i data-feather="settings"></i> Settings</a></li>
      </ul>
    </nav>

    <div class="mt-auto pt-4">
      <div class="small-muted">Signed in as</div>
      <div><strong>Admin</strong></div>
      <button class="btn btn-outline-light btn-sm mt-3" id="quick-new">+ New record</button>
    </div>
  </aside>

  <!-- MAIN CONTENT -->
  <main class="content">
    <!-- Topbar -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h3 class="mb-0">Dashboard</h3>
        <div class="small-muted">Overview of farm operations & financials</div>
      </div>
      <div class="d-flex gap-2 align-items-center">
        <div class="input-group">
          <input id="globalSearch" class="form-control form-control-sm" placeholder="Search..." />
          <button class="btn btn-sm btn-outline-secondary" id="btnSearch"><i data-feather="search"></i></button>
        </div>
        <button class="btn btn-sm btn-primary" id="refreshBtn"><i data-feather="rotate-cw"></i> Refresh</button>
        <div class="dropdown">
          <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown"><i data-feather="user"></i></button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="#">Profile</a></li>
            <li><a class="dropdown-item" href="#">Preferences</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="#">Log out</a></li>
          </ul>
        </div>
      </div>
    </div>

    <!-- KPI CARDS -->
    <div class="row g-3 mb-3">
      <div class="col-lg-3 col-md-6">
        <div class="p-3 kpi-card bg-white">
          <div class="d-flex justify-content-between">
            <div>
              <div class="small-muted">TOTAL FIELDS</div>
              <h4 id="kpiFields"><?php echo $sampleFieldsCount; ?></h4>
            </div>
            <div class="text-end">
              <i data-feather="map" class="me-1"></i>
              <div class="small-muted">active</div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-3 col-md-6">
        <div class="p-3 kpi-card bg-white">
          <div class="d-flex justify-content-between">
            <div>
              <div class="small-muted">CROPS</div>
              <h4 id="kpiCrops"><?php echo $sampleCropsCount; ?></h4>
            </div>
            <div class="text-end">
              <i data-feather="leaf"></i>
              <div class="small-muted">varieties</div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-3 col-md-6">
        <div class="p-3 kpi-card bg-white">
          <div class="d-flex justify-content-between">
            <div>
              <div class="small-muted">LIVESTOCK</div>
              <h4 id="kpiAnimals"><?php echo $sampleAnimalsCount; ?></h4>
            </div>
            <div class="text-end">
              <i data-feather="truck"></i>
              <div class="small-muted">heads</div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-3 col-md-6">
        <div class="p-3 kpi-card bg-white">
          <div class="d-flex justify-content-between">
            <div>
              <div class="small-muted">INVENTORY VALUE</div>
              <h4 id="kpiInventory">Ksh <?php echo currency($sampleInventoryValue); ?></h4>
            </div>
            <div class="text-end">
              <i data-feather="credit-card"></i>
              <div class="small-muted">estimated</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- CONTENT ROW: CHARTS + ACTIVITIES -->
    <div class="row g-3">
      <div class="col-lg-8">
        <div class="card p-3 kpi-card">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <div>
              <h5 class="mb-0">Production Trend</h5>
              <div class="small-muted">Last 12 months</div>
            </div>
            <div>
              <select id="productionMetric" class="form-select form-select-sm">
                <option value="yield">Yield (kg)</option>
                <option value="area">Area planted (ha)</option>
                <option value="revenue">Revenue (Ksh)</option>
              </select>
            </div>
          </div>
          <canvas id="productionChart" height="120"></canvas>
          <div class="d-flex justify-content-between mt-3 small-muted">
            <div>Projected next month: <strong id="projected">+6.3%</strong></div>
            <div>Export: <button class="btn btn-sm btn-outline-secondary" id="exportTrend">CSV</button></div>
          </div>
        </div>

        <div class="card p-3 mt-3 kpi-card">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <h5 class="mb-0">Latest Orders</h5>
            <div class="small-muted">Most recent sales</div>
          </div>
          <div class="table-wrap">
            <table class="table table-hover">
              <thead class="table-light">
                <tr><th>Order</th><th>Customer</th><th>Amount (Ksh)</th><th>Status</th><th>Date</th></tr>
              </thead>
              <tbody id="ordersTable">
                <?php foreach ($latestOrders as $o): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($o['order_id']); ?></td>
                    <td><?php echo htmlspecialchars($o['customer']); ?></td>
                    <td><?php echo currency($o['amount']); ?></td>
                    <td><span class="badge <?php echo $o['status']=='Completed'?'bg-success':'bg-warning'; ?>"><?php echo $o['status']; ?></span></td>
                    <td><?php echo $o['date']; ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="col-lg-4">
        <div class="card p-3 kpi-card mb-3">
          <h6 class="mb-3">Livestock Distribution</h6>
          <canvas id="livestockChart" height="220"></canvas>
          <div class="small-muted mt-3">Distribution by type</div>
        </div>

        <div class="card p-3 kpi-card mb-3">
          <div class="d-flex justify-content-between">
            <h6 class="mb-0">Recent Activity</h6>
            <small class="small-muted">Real-time</small>
          </div>
          <div id="activityFeed" class="mt-3">
            <?php foreach ($recentActivities as $act): ?>
              <div class="activity-item">
                <div class="small-muted"><?php echo $act['time']; ?></div>
                <div><?php echo htmlspecialchars($act['desc']); ?></div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="card p-3 kpi-card">
          <h6 class="mb-0">Quick Actions</h6>
          <div class="d-grid gap-2 mt-3">
            <button class="btn btn-outline-primary" id="addCropBtn"><i data-feather="plus"></i> Add Crop</button>
            <button class="btn btn-outline-primary" id="addAnimalBtn"><i data-feather="plus-circle"></i> Add Animal</button>
            <button class="btn btn-outline-secondary" id="inventoryAdjBtn"><i data-feather="edit-2"></i> Inventory Adj.</button>
          </div>
        </div>
      </div>
    </div>

    <!-- FOOTER -->
    <footer class="mt-4 small-muted">© <?php echo date("Y"); ?> FarmManager • Built for producers • Data is sample/demo only</footer>
  </main>

  <!-- Modal (example) -->
  <div class="modal fade" id="actionModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Quick Action</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form id="quickActionForm">
            <div class="mb-3">
              <label class="form-label">Name</label>
              <input class="form-control" name="name" required />
            </div>
            <div class="mb-3">
              <label class="form-label">Type</label>
              <select class="form-control" name="type">
                <option>Crop</option><option>Livestock</option><option>Inventory</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Notes</label>
              <textarea class="form-control" name="notes"></textarea>
            </div>
            <div class="text-end">
              <button type="submit" class="btn btn-primary">Create</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap + utilities -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    feather.replace();

    // Fetch data from the same PHP file's JSON endpoint
    async function fetchDashboardData(){
      try {
        const res = await fetch('?action=data');
        if(!res.ok) throw new Error('Network response not ok');
        return await res.json();
      } catch (err) {
        console.error('Failed to fetch dashboard data', err);
        return null;
      }
    }

    // Initialize charts with sample data, and update on refresh
    let productionChart = null, livestockChart = null;

    function renderProductionChart(labels, values){
      const ctx = document.getElementById('productionChart').getContext('2d');
      if(productionChart) productionChart.destroy();
      productionChart = new Chart(ctx, {
        type: 'line',
        data: {
          labels: labels,
          datasets: [{
            label: 'Production (units)',
            data: values,
            tension: 0.35,
            fill: true,
            borderWidth: 2,
            backgroundColor: 'rgba(42,157,143,0.08)',
            borderColor: 'rgba(42,157,143,1)',
            pointRadius: 3
          }]
        },
        options: {
          plugins: { legend: { display: false } },
          scales: {
            y: { beginAtZero: true, grid: { drawBorder: false } },
            x: { grid: { display: false } }
          }
        }
      });
    }

    function renderLivestockChart(labels, values){
      const ctx = document.getElementById('livestockChart').getContext('2d');
      if(livestockChart) livestockChart.destroy();
      livestockChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
          labels: labels,
          datasets: [{
            data: values,
            backgroundColor: [
              'rgba(42,157,143,0.95)',
              'rgba(38,70,83,0.9)',
              'rgba(233,196,106,0.9)',
              'rgba(244,162,97,0.9)',
              'rgba(231,111,81,0.9)'
            ]
          }]
        },
        options: {
          plugins: { legend: { position: 'bottom' } }
        }
      });
    }

    // Export trend as CSV
    function exportTrendCSV(labels, values){
      let csv = 'month,value\n';
      for(let i=0;i<labels.length;i++){
        csv += `${labels[i]},${values[i]}\n`;
      }
      const blob = new Blob([csv], {type: 'text/csv'});
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url; a.download = 'production-trend.csv';
      document.body.appendChild(a); a.click();
      a.remove(); URL.revokeObjectURL(url);
    }

    // Wire up UI
    document.addEventListener('DOMContentLoaded', async () => {
      // initial render using embedded PHP data
      renderProductionChart(<?php echo json_encode($productionTrend['labels']); ?>, <?php echo json_encode($productionTrend['values']); ?>);
      renderLivestockChart(<?php echo json_encode($livestockDistribution['labels']); ?>, <?php echo json_encode($livestockDistribution['values']); ?>);

      document.getElementById('exportTrend').addEventListener('click', () => {
        exportTrendCSV(<?php echo json_encode($productionTrend['labels']); ?>, <?php echo json_encode($productionTrend['values']); ?>);
      });

      document.getElementById('refreshBtn').addEventListener('click', async () => {
        await refreshData();
      });

      document.getElementById('quick-new').addEventListener('click', () => {
        const modal = new bootstrap.Modal(document.getElementById('actionModal'));
        modal.show();
      });

      document.getElementById('quickActionForm').addEventListener('submit', (e) => {
        e.preventDefault();
        // For prototype: just show toast and close modal
        const form = e.target;
        const name = form.name.value;
        const type = form.type.value;
        // In real app, send to server via fetch POST
        alert(`Quick create: ${type} — ${name} (demo)`);
        bootstrap.Modal.getInstance(document.getElementById('actionModal')).hide();
        form.reset();
      });

      // Auto refresh data every 5 minutes (optional)
      // setInterval(() => refreshData(), 1000 * 60 * 5);
    });

    async function refreshData(){
      const data = await fetchDashboardData();
      if(!data) return;
      // update KPIs
      document.getElementById('kpiFields').textContent = data.kpis.fields;
      document.getElementById('kpiCrops').textContent = data.kpis.crops;
      document.getElementById('kpiAnimals').textContent = data.kpis.animals;
      document.getElementById('kpiInventory').textContent = 'Ksh ' + Number(data.kpis.inventory_value).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2});
      // update production chart
      renderProductionChart(data.productionTrend.labels, data.productionTrend.values);
      renderLivestockChart(data.livestockDistribution.labels, data.livestockDistribution.values);
      // update recent activity feed
      const feed = document.getElementById('activityFeed');
      feed.innerHTML = '';
      data.recentActivities.forEach(a => {
        const div = document.createElement('div');
        div.className = 'activity-item';
        div.innerHTML = `<div class="small-muted">${a.time}</div><div>${a.desc}</div>`;
        feed.appendChild(div);
      });
      // update latest orders table
      const ordersTable = document.getElementById('ordersTable');
      ordersTable.innerHTML = '';
      data.latestOrders.forEach(o => {
        const tr = document.createElement('tr');
        tr.innerHTML = `<td>${o.order_id}</td><td>${o.customer}</td><td>${Number(o.amount).toFixed(2)}</td><td><span class="badge ${o.status==='Completed'?'bg-success':'bg-warning'}">${o.status}</span></td><td>${o.date}</td>`;
        ordersTable.appendChild(tr);
      });
    }
  </script>
</body>
</html>
