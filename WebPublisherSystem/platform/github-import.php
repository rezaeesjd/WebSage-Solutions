<?php
defined('WPS_ASSET_BASE')   || define('WPS_ASSET_BASE',   '.');
defined('WPS_SETTINGS_URL') || define('WPS_SETTINGS_URL', 'settings.php');

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/github-import-engine.php';

// ─── Output helpers ───────────────────────────────────────────────────────────

function ghimp_page_h(?string $v): string
{
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
}

// ─── Action handling ──────────────────────────────────────────────────────────

$flash       = ['type' => '', 'message' => ''];
$syncResults = null;
$testResult  = null;
$testConnId  = '';

$connections = ghimp_connections_load();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = trim((string) ($_POST['action'] ?? ''));

    // ── Add ──────────────────────────────────────────────────────────────────
    if ($action === 'add') {
        $url       = trim((string) ($_POST['github_url']  ?? ''));
        $branch    = trim((string) ($_POST['branch']      ?? ''));
        $path      = trim((string) ($_POST['path']        ?? ''));
        $token     = trim((string) ($_POST['token']       ?? ''));
        $localPath = trim((string) ($_POST['local_path']  ?? ''));

        if ($url === '') {
            $flash = ['type' => 'error', 'message' => 'GitHub URL is required.'];
        } else {
            $result = ghimp_connection_add($url, $branch, $path, $token, $localPath);
            $flash  = $result['ok']
                ? ['type' => 'success', 'message' => 'Connection added: ' . $result['connection']['owner'] . '/' . $result['connection']['repo']]
                : ['type' => 'error',   'message' => $result['error']];
        }
        $connections = ghimp_connections_load();
    }

    // ── Remove ───────────────────────────────────────────────────────────────
    if ($action === 'remove') {
        $id = (string) ($_POST['id'] ?? '');
        ghimp_connection_remove($id);
        $flash       = ['type' => 'success', 'message' => 'Connection removed.'];
        $connections = ghimp_connections_load();
    }

    // ── Toggle ───────────────────────────────────────────────────────────────
    if ($action === 'toggle') {
        $id = (string) ($_POST['id'] ?? '');
        ghimp_connection_toggle($id);
        $connections = ghimp_connections_load();
        $toggled     = null;
        foreach ($connections as $c) {
            if ($c['id'] === $id) { $toggled = $c; break; }
        }
        $flash = ['type' => 'success', 'message' => $toggled
            ? ($toggled['enabled'] ? 'Connection enabled.' : 'Connection disabled.')
            : 'Done.'];
    }

    // ── Test ─────────────────────────────────────────────────────────────────
    if ($action === 'test') {
        $id   = (string) ($_POST['id'] ?? '');
        $conn = null;
        foreach (ghimp_connections_load() as $c) {
            if ($c['id'] === $id) { $conn = $c; break; }
        }
        if ($conn !== null) {
            $testResult = ghimp_test_connection($conn);
            $testConnId = $id;
        } else {
            $flash = ['type' => 'error', 'message' => 'Connection not found.'];
        }
    }

    // ── Sync one ─────────────────────────────────────────────────────────────
    if ($action === 'sync') {
        $id   = (string) ($_POST['id'] ?? '');
        $conn = null;
        foreach (ghimp_connections_load() as $c) {
            if ($c['id'] === $id) { $conn = $c; break; }
        }
        if ($conn !== null) {
            $out         = ghimp_sync_connection($conn);
            $syncResults = [$id => $out];
            $flash       = [
                'type'    => $out['status'] === 'ok' ? 'success' : 'error',
                'message' => 'Sync completed — status: ' . $out['status'] . '.',
            ];
        } else {
            $flash = ['type' => 'error', 'message' => 'Connection not found.'];
        }
        $connections = ghimp_connections_load();
    }

    // ── Sync all ─────────────────────────────────────────────────────────────
    if ($action === 'sync_all') {
        $syncResults  = ghimp_sync_all_enabled();
        $totalErrors  = count(array_filter($syncResults, fn($r) => $r['status'] !== 'ok'));
        $flash        = [
            'type'    => $totalErrors === 0 ? 'success' : 'error',
            'message' => $totalErrors === 0
                ? 'All enabled connections synced successfully.'
                : $totalErrors . ' connection(s) had errors during sync.',
        ];
        $connections = ghimp_connections_load();
    }
}

// ─── View helpers ─────────────────────────────────────────────────────────────

$statusLabels = [
    'ok'       => 'OK',
    'partial'  => 'Partial',
    'error'    => 'Error',
    'neutral'  => 'Never synced',
    'disabled' => 'Disabled',
];

wps_render_header('GitHub Import');
?>

<!-- ═══ Page Header ═══════════════════════════════════════════════════════════ -->
<section class="panel">
    <h1>GitHub Import</h1>
    <p class="muted">Sync files from any GitHub repository or folder directly to your local file system. Each connection can be managed independently and synced on demand.</p>

    <?php if ($flash['message'] !== ''): ?>
        <div class="alert alert-<?php echo $flash['type'] === 'error' ? 'error' : 'success'; ?>">
            <?php echo ghimp_page_h($flash['message']); ?>
        </div>
    <?php endif; ?>

    <div class="actions" style="margin-top: 16px;">
        <?php if (count($connections) > 0): ?>
        <form method="post" style="display:inline;">
            <input type="hidden" name="action" value="sync_all">
            <button type="submit">Sync All Enabled</button>
        </form>
        <?php endif; ?>
        <a class="button-secondary" href="settings.php">Back to Settings</a>
    </div>
</section>

<!-- ═══ Connections ══════════════════════════════════════════════════════════ -->
<?php if (count($connections) > 0): ?>
<section class="panel">
    <h2>Connections</h2>
    <div class="ghimp-connections">
        <?php foreach ($connections as $conn):
            $enabled    = (bool) ($conn['enabled'] ?? true);
            $rawStatus  = $enabled ? ($conn['last_status'] ?? 'neutral') : 'disabled';
            $badgeClass = 'ghimp-badge ghimp-badge-' . $rawStatus;
            $label      = $statusLabels[$rawStatus] ?? $rawStatus;
            $isTestCard = ($testResult !== null && $testConnId === $conn['id']);
        ?>
        <div class="ghimp-card<?php echo $enabled ? '' : ' ghimp-card-disabled'; ?>">
            <div class="ghimp-card-header">
                <div class="ghimp-card-title">
                    <strong><?php echo ghimp_page_h($conn['owner'] . '/' . $conn['repo']); ?></strong>
                    <?php if (($conn['path'] ?? '') !== ''): ?>
                        <span class="ghimp-subpath">/ <?php echo ghimp_page_h($conn['path']); ?></span>
                    <?php endif; ?>
                </div>
                <span class="<?php echo $badgeClass; ?>"><?php echo ghimp_page_h($label); ?></span>
            </div>

            <div class="ghimp-card-meta">
                <span>Branch: <code><?php echo ghimp_page_h($conn['branch']); ?></code></span>
                <?php if (($conn['local_path'] ?? '') !== ''): ?>
                    <span>Local: <code><?php echo ghimp_page_h($conn['local_path']); ?></code></span>
                <?php endif; ?>
                <?php if (!empty($conn['last_sync'])): ?>
                    <span>Last sync: <?php echo ghimp_page_h($conn['last_sync']); ?></span>
                <?php endif; ?>
                <?php if (!empty($conn['token'])): ?>
                    <span>Token: <code>••••<?php echo ghimp_page_h(substr($conn['token'], -4)); ?></code></span>
                <?php endif; ?>
            </div>

            <div class="ghimp-card-actions">
                <form method="post" style="display:inline;">
                    <input type="hidden" name="action" value="sync">
                    <input type="hidden" name="id" value="<?php echo ghimp_page_h($conn['id']); ?>">
                    <button type="submit"<?php echo $enabled ? '' : ' disabled'; ?>>Sync Now</button>
                </form>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="action" value="test">
                    <input type="hidden" name="id" value="<?php echo ghimp_page_h($conn['id']); ?>">
                    <button type="submit" class="button-secondary">Test</button>
                </form>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="action" value="toggle">
                    <input type="hidden" name="id" value="<?php echo ghimp_page_h($conn['id']); ?>">
                    <button type="submit" class="button-secondary"><?php echo $enabled ? 'Disable' : 'Enable'; ?></button>
                </form>
                <form method="post" style="display:inline;"
                      onsubmit="return confirm('Remove this connection? This cannot be undone.');">
                    <input type="hidden" name="action" value="remove">
                    <input type="hidden" name="id" value="<?php echo ghimp_page_h($conn['id']); ?>">
                    <button type="submit" class="button-secondary ghimp-btn-danger">Remove</button>
                </form>
            </div>

            <?php if ($isTestCard): ?>
            <div class="alert <?php echo $testResult['ok'] ? 'alert-success' : 'alert-error'; ?>" style="margin-top: 12px; margin-bottom: 0;">
                <?php echo ghimp_page_h($testResult['message']); ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- ═══ Sync Results ══════════════════════════════════════════════════════════ -->
<?php if ($syncResults !== null): ?>
<section class="panel">
    <h2>Sync Results</h2>
    <?php foreach ($syncResults as $connId => $res):
        $connTitle = $connId;
        foreach ($connections as $c) {
            if ($c['id'] === $connId) { $connTitle = $c['owner'] . '/' . $c['repo']; break; }
        }
        $counts = ['created' => 0, 'updated' => 0, 'unchanged' => 0, 'deleted' => 0, 'skipped' => 0, 'error' => 0];
        foreach ($res['results'] as $r) {
            $s = $r['status'];
            $counts[$s] = ($counts[$s] ?? 0) + 1;
        }
    ?>
    <h3><?php echo ghimp_page_h($connTitle); ?></h3>
    <div class="ghimp-counts">
        <span class="ghimp-count-created"><?php echo $counts['created']; ?> created</span>
        <span class="ghimp-count-updated"><?php echo $counts['updated']; ?> updated</span>
        <span class="ghimp-count-unchanged"><?php echo $counts['unchanged']; ?> unchanged</span>
        <?php if ($counts['deleted'] > 0): ?>
        <span class="ghimp-count-skipped"><?php echo $counts['deleted']; ?> deleted</span>
        <?php endif; ?>
        <?php if ($counts['skipped'] > 0): ?>
        <span class="ghimp-count-skipped"><?php echo $counts['skipped']; ?> skipped</span>
        <?php endif; ?>
        <?php if ($counts['error'] > 0): ?>
        <span class="ghimp-count-error"><?php echo $counts['error']; ?> error(s)</span>
        <?php endif; ?>
    </div>

    <?php if (count($res['results']) > 0): ?>
    <div class="result-box ghimp-result-box">
        <ul>
            <?php foreach ($res['results'] as $item): ?>
            <li class="ghimp-result-<?php echo ghimp_page_h($item['status']); ?>">
                <strong><?php echo ghimp_page_h(strtoupper($item['status'])); ?></strong>
                — <?php echo ghimp_page_h($item['path']); ?>
                <br><small><?php echo ghimp_page_h($item['message']); ?></small>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>
    <?php endforeach; ?>
</section>
<?php endif; ?>

<!-- ═══ Add Connection ════════════════════════════════════════════════════════ -->
<section class="panel">
    <h2>Add Connection</h2>
    <p class="muted">Paste any GitHub URL. Branch, subfolder, local path, and token are optional.</p>

    <form method="post" class="form grid-form">
        <input type="hidden" name="action" value="add">

        <label class="full">
            GitHub URL <small>(required)</small>
            <input type="text" name="github_url"
                   placeholder="https://github.com/owner/repo  or  owner/repo  or  owner/repo@branch"
                   required>
        </label>

        <label>
            Branch <small>(overrides the URL default)</small>
            <input type="text" name="branch" placeholder="main">
        </label>

        <label>
            Subfolder in repo <small>(e.g. <code>docs</code> or <code>src/lib</code>)</small>
            <input type="text" name="path" placeholder="">
        </label>

        <label>
            Local target path <small>(relative to WebPublisherSystem root, e.g. <code>content-system/tours</code>)</small>
            <input type="text" name="local_path" placeholder="">
        </label>

        <label>
            Token <small>(optional — private repos or higher rate limits)</small>
            <input type="text" name="token" placeholder="github_pat_…" autocomplete="off">
        </label>

        <div class="full actions">
            <button type="submit">Add Connection</button>
        </div>
    </form>
</section>

<!-- ═══ About ════════════════════════════════════════════════════════════════ -->
<section class="panel muted-panel">
    <h2>About this Addon</h2>
    <p>The <strong>GitHub Import Addon</strong> syncs files from any public or private GitHub repository to your local file system. It is designed to be reusable and can be dropped standalone into any PHP 7.4+ project.</p>
    <ul>
        <li>Paste any GitHub URL — full URLs, <code>owner/repo</code>, <code>owner/repo@branch</code>, or <code>/tree/branch/path</code> formats are all supported.</li>
        <li>ZIP-based sync (one request, fast) with automatic GitHub API fallback when ZipArchive is unavailable.</li>
        <li>Optional Bearer token for private repositories and higher API rate limits.</li>
        <li>Directory-traversal guard on every write — protected paths such as <code>platform/data/</code> are never overwritten.</li>
        <li>Per-connection enable/disable, test, and on-demand sync.</li>
        <li>Standalone use: copy <code>github-import-engine.php</code> into any PHP project and define <code>GHIMP_LOCAL_ROOT</code> and <code>GHIMP_PROTECTED_PATHS</code> before including it.</li>
    </ul>
    <p><small>Connections are stored in <code>platform/data/github-import-connections.json</code>.</small></p>
</section>

<?php wps_render_footer(); ?>
