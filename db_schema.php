<?php
/**
 * File    : db_schema.php
 * Purpose : Live Database & SQL Schema Inspector for Staff & Faculty Demonstrations
 * Module  : Database Overview / SQL Data Inspection
 */

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_login();

$pdo = get_db();

$tables = ['member', 'attendance', 'plan', 'trainer', 'payment', 'login'];
$views  = ['v_member_summary'];

$selected_table = trim($_GET['table'] ?? 'member');
if (!in_array($selected_table, array_merge($tables, $views), true)) {
    $selected_table = 'member';
}

// Fetch table schema
$schema_stmt = $pdo->prepare("DESCRIBE `" . $selected_table . "`");
$schema_stmt->execute();
$columns = $schema_stmt->fetchAll();

// Fetch sample data (up to 20 rows)
$data_stmt = $pdo->prepare("SELECT * FROM `" . $selected_table . "` LIMIT 20");
$data_stmt->execute();
$rows = $data_stmt->fetchAll();

// Fetch row counts for all tables
$counts = [];
foreach ($tables as $t) {
    $stmt = $pdo->query("SELECT COUNT(*) AS c FROM `$t`");
    $counts[$t] = $stmt->fetchColumn();
}
foreach ($views as $v) {
    $stmt = $pdo->query("SELECT COUNT(*) AS c FROM `$v`");
    $counts[$v] = $stmt->fetchColumn();
}

$page_title = "Database Inspector — FitTrack SQL Data";
include __DIR__ . '/includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 fw-bold mb-1"><i class="bi bi-database-fill-gear text-primary me-2"></i>MySQL Database Inspector</h2>
            <p class="text-muted mb-0">Live SQL schema, tables, views, and data records in <code>fittrack_db</code></p>
        </div>
        <div>
            <a href="http://localhost/phpmyadmin" target="_blank" class="btn btn-outline-primary">
                <i class="bi bi-box-arrow-up-right me-1"></i> Open phpMyAdmin GUI
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <?php foreach ($counts as $tbl_name => $count): ?>
            <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                <a href="?table=<?= $tbl_name ?>" class="card border-0 shadow-sm text-decoration-none text-dark h-100 <?= $selected_table === $tbl_name ? 'border-primary border-2 bg-light' : '' ?>">
                    <div class="card-body text-center p-3">
                        <span class="badge bg-secondary mb-2"><?= strpos($tbl_name, 'v_') === 0 ? 'VIEW' : 'TABLE' ?></span>
                        <div class="h4 fw-bold mb-0 text-primary"><?= number_format($count) ?></div>
                        <small class="text-muted text-uppercase fw-semibold" style="font-size:0.75rem;"><?= htmlspecialchars($tbl_name) ?></small>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Table Details -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 fw-bold">
                <i class="bi bi-table text-primary me-2"></i>Table Structure: <code><?= htmlspecialchars($selected_table) ?></code>
            </h5>
            <span class="badge bg-info text-dark">Showing top 20 rows</span>
        </div>
        <div class="card-body p-0">
            <!-- Schema / Columns Table -->
            <div class="p-3 bg-light border-bottom">
                <h6 class="fw-bold mb-2 text-secondary">Column Definitions & Data Types</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered bg-white mb-0">
                        <thead class="table-secondary">
                            <tr>
                                <th>Field</th>
                                <th>Type</th>
                                <th>Null</th>
                                <th>Key</th>
                                <th>Default</th>
                                <th>Extra</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($columns as $col): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($col['Field']) ?></strong></td>
                                    <td><code><?= htmlspecialchars($col['Type']) ?></code></td>
                                    <td><?= htmlspecialchars($col['Null']) ?></td>
                                    <td><span class="badge bg-<?= $col['Key'] === 'PRI' ? 'danger' : ($col['Key'] === 'UNI' ? 'warning' : 'light text-dark') ?>"><?= htmlspecialchars($col['Key']) ?></span></td>
                                    <td><?= htmlspecialchars($col['Default'] ?? 'NULL') ?></td>
                                    <td><?= htmlspecialchars($col['Extra']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Data Rows Table -->
            <div class="p-3">
                <h6 class="fw-bold mb-2 text-secondary">Sample Data Records (<code>SELECT * FROM `<?= htmlspecialchars($selected_table) ?>` LIMIT 20</code>)</h6>
                <?php if (empty($rows)): ?>
                    <p class="text-muted italic mb-0">No records found in this table.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped table-bordered align-middle mb-0" style="font-size:0.875rem;">
                            <thead class="table-dark">
                                <tr>
                                    <?php foreach (array_keys($rows[0]) as $h): ?>
                                        <th><?= htmlspecialchars($h) ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rows as $r): ?>
                                    <tr>
                                        <?php foreach ($r as $val): ?>
                                            <td><?= htmlspecialchars((string)($val ?? 'NULL')) ?></td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
