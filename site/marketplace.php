<?php
session_start();

define('API_BASE', 'https://justiceclient.org');

function apiCall($path, $method = 'GET', $body = null, $token = null) {
    $url = API_BASE . $path;
    $opts = [
        'http' => [
            'method'        => $method,
            'header'        => "Content-Type: application/json\r\n" .
                               ($token ? "Authorization: Bearer {$token}\r\n" : ''),
            'ignore_errors' => true,
            'timeout'       => 10,
        ],
    ];
    if ($body !== null) {
        $opts['http']['content'] = is_string($body) ? $body : json_encode($body);
    }
    $ctx = stream_context_create($opts);
    $raw = @file_get_contents($url, false, $ctx);
    if ($raw === false) return ['error' => 'Connection failed'];
    return json_decode($raw, true) ?: ['error' => 'Invalid response'];
}

function token() {
    return $_SESSION['jl_token'] ?? null;
}

function user() {
    return $_SESSION['jl_user'] ?? null;
}

if (isset($_GET['ajax'])) {
    header('Content-Type: application/json');
    $action = $_GET['ajax'];

    if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $res = apiCall('/api/login.php', 'POST', $data);
        if (isset($res['token']) && isset($res['user'])) {
            $_SESSION['jl_token'] = $res['token'];
            $_SESSION['jl_user'] = $res['user'];
            echo json_encode($res);
        } else {
            echo json_encode(['error' => $res['error'] ?? 'Login failed']);
        }
        exit;
    }

    if ($action === 'auto-login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $t = $data['token'] ?? '';
        if (!$t) { echo json_encode(['error' => 'No token']); exit; }
        $res = apiCall('/api/user.php?action=me', 'GET', null, $t);
        if (!empty($res['user'])) {
            $_SESSION['jl_token'] = $t;
            $_SESSION['jl_user'] = $res['user'];
            echo json_encode(['ok' => true, 'user' => $res['user']]);
        } else {
            echo json_encode(['error' => 'Invalid token']);
        }
        exit;
    }

    if ($action === 'logout') {
        session_destroy();
        echo json_encode(['ok' => true]);
        exit;
    }

    if ($action === 'me') {
        $t = token();
        if (!$t) {
            echo json_encode(['error' => 'Not logged in']);
            exit;
        }
        $res = apiCall('/api/user.php?action=me', 'GET', null, $t);
        if (!empty($res['user'])) {
            $_SESSION['jl_user'] = $res['user'];
        } else {
            unset($_SESSION['jl_token'], $_SESSION['jl_user']);
        }
        echo json_encode($res);
        exit;
    }

    if ($action === 'browse') {
        $type = $_GET['type'] ?? '';
        $sort = $_GET['sort'] ?? 'newest';
        $page = $_GET['page'] ?? 1;
        $path = '/api/marketplace.php?action=browse&type=' . urlencode($type) . '&sort=' . urlencode($sort) . '&page=' . intval($page);
        $res = apiCall($path, 'GET', null, token());
        echo json_encode($res);
        exit;
    }

    if ($action === 'my-listings') {
        $t = token();
        if (!$t) {
            echo json_encode(['error' => 'Not logged in']);
            exit;
        }
        $res = apiCall('/api/marketplace.php?action=my-listings', 'GET', null, $t);
        echo json_encode($res);
        exit;
    }

    if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $t = token();
        if (!$t) {
            echo json_encode(['error' => 'Not logged in']);
            exit;
        }

        $ch = curl_init(API_BASE . '/api/marketplace.php?action=create');
        $post = [
            'name'        => $_POST['name'] ?? '',
            'description' => $_POST['description'] ?? '',
            'type'        => $_POST['type'] ?? '',
            'price'       => $_POST['price'] ?? '',
        ];

        if (!empty($_FILES['texture']) && $_FILES['texture']['error'] === UPLOAD_ERR_OK) {
            $post['texture'] = new CURLFile(
                $_FILES['texture']['tmp_name'],
                $_FILES['texture']['type'],
                $_FILES['texture']['name']
            );
        }
        if (!empty($_FILES['preview']) && $_FILES['preview']['error'] === UPLOAD_ERR_OK) {
            $post['preview'] = new CURLFile(
                $_FILES['preview']['tmp_name'],
                $_FILES['preview']['type'],
                $_FILES['preview']['name']
            );
        }

        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $post,
            CURLOPT_HTTPHEADER     => ["Authorization: Bearer {$t}"],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
        ]);

        $res = curl_exec($ch);
        curl_close($ch);
        echo $res ?: json_encode(['error' => 'Upload failed']);
        exit;
    }

    if ($action === 'remove' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $t = token();
        if (!$t) {
            echo json_encode(['error' => 'Not logged in']);
            exit;
        }
        $data = json_decode(file_get_contents('php://input'), true);
        $res = apiCall('/api/marketplace.php?action=remove', 'POST', $data, $t);
        echo json_encode($res);
        exit;
    }

    if ($action === 'buy' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $t = token();
        if (!$t) {
            echo json_encode(['error' => 'Not logged in']);
            exit;
        }
        $data = json_decode(file_get_contents('php://input'), true);
        $res = apiCall('/api/marketplace.php?action=buy', 'POST', $data, $t);
        echo json_encode($res);
        exit;
    }

    echo json_encode(['error' => 'Unknown action']);
    exit;
}

$isLoggedIn = !!token();
$currentUser = user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marketplace - Justice</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #050309;
            --bg2: #080512;
            --bg3: #0c0918;
            --p: #7c3aed;
            --p2: #6d28d9;
            --pl: #a78bfa;
            --w: #f0eeff;
            --w2: rgba(240, 238, 255, 0.58);
            --w3: rgba(240, 238, 255, 0.28);
            --line: rgba(255, 255, 255, 0.05);
            --line2: rgba(139, 92, 246, 0.2);
            --green: #22c55e;
            --red: #f87171;
            --amber: #fbbf24;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Outfit', system-ui, sans-serif;
            background: var(--bg);
            color: var(--w);
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--line);
        }

        h1 {
            font-size: 32px;
            font-weight: 700;
            background: linear-gradient(135deg, var(--pl), var(--p));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .header-actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .user-info {
            padding: 8px 16px;
            background: var(--bg2);
            border: 1px solid var(--line);
            border-radius: 8px;
            font-size: 14px;
        }

        button {
            padding: 10px 20px;
            background: var(--p);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-family: 'Outfit', system-ui, sans-serif;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.2s;
        }

        button:hover {
            background: var(--p2);
            transform: translateY(-2px);
        }

        button:active {
            transform: translateY(0);
        }

        .back-link {
            display: inline-block;
            padding: 8px 16px;
            color: var(--pl);
            text-decoration: none;
            border: 1px solid var(--line2);
            border-radius: 6px;
            margin-bottom: 20px;
            transition: all 0.2s;
        }

        .back-link:hover {
            background: rgba(167, 139, 250, 0.1);
            border-color: var(--pl);
        }

        .login-container {
            max-width: 400px;
            margin: 60px auto;
            padding: 40px;
            background: var(--bg2);
            border: 1px solid var(--line);
            border-radius: 12px;
        }

        .login-container h2 {
            margin-bottom: 30px;
            font-size: 24px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 14px;
        }

        input[type="text"],
        input[type="password"],
        input[type="number"],
        input[type="file"],
        select,
        textarea {
            width: 100%;
            padding: 12px;
            background: var(--bg3);
            border: 1px solid var(--line);
            border-radius: 6px;
            color: var(--w);
            font-family: 'Outfit', system-ui, sans-serif;
            font-size: 14px;
            transition: border-color 0.2s;
        }

        input[type="text"]:focus,
        input[type="password"]:focus,
        input[type="number"]:focus,
        input[type="file"]:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: var(--p);
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        .login-btn {
            width: 100%;
            padding: 12px;
            margin-top: 10px;
        }

        .nav-tabs {
            display: flex;
            gap: 20px;
            margin-bottom: 40px;
            border-bottom: 1px solid var(--line);
            padding-bottom: 20px;
        }

        .nav-tab {
            padding: 10px 0;
            background: none;
            color: var(--w3);
            border: none;
            border-bottom: 2px solid transparent;
            cursor: pointer;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.2s;
        }

        .nav-tab.active {
            color: var(--p);
            border-bottom-color: var(--p);
        }

        .nav-tab:hover {
            color: var(--w2);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .filters {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .filter-group {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .filter-group label {
            margin: 0;
            font-size: 14px;
        }

        .filter-group select {
            flex: 1;
            min-width: 150px;
        }

        .listings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .listing-card {
            background: var(--bg2);
            border: 1px solid var(--line);
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s;
        }

        .listing-card:hover {
            border-color: var(--p);
            transform: translateY(-4px);
        }

        .listing-image {
            width: 100%;
            height: 200px;
            background: var(--bg3);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .listing-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .listing-content {
            padding: 16px;
        }

        .listing-header {
            margin-bottom: 12px;
        }

        .listing-name {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .listing-meta {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 12px;
        }

        .badge {
            display: inline-block;
            padding: 4px 10px;
            background: var(--bg3);
            border: 1px solid var(--line);
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge.cape {
            background: rgba(124, 58, 237, 0.2);
            border-color: var(--p);
            color: var(--pl);
        }

        .badge.wings {
            background: rgba(139, 92, 246, 0.2);
            border-color: var(--pl);
            color: var(--pl);
        }

        .badge.pending {
            background: rgba(251, 191, 36, 0.2);
            border-color: var(--amber);
            color: var(--amber);
        }

        .badge.approved {
            background: rgba(34, 197, 94, 0.2);
            border-color: var(--green);
            color: var(--green);
        }

        .badge.rejected {
            background: rgba(248, 113, 113, 0.2);
            border-color: var(--red);
            color: var(--red);
        }

        .listing-price {
            font-size: 20px;
            font-weight: 700;
            color: var(--pl);
            margin-bottom: 8px;
        }

        .listing-seller {
            font-size: 13px;
            color: var(--w3);
            margin-bottom: 12px;
        }

        .listing-actions {
            display: flex;
            gap: 8px;
        }

        .listing-actions button {
            flex: 1;
            padding: 8px;
            font-size: 13px;
        }

        .listing-actions button:disabled {
            background: var(--bg3);
            color: var(--w3);
            cursor: not-allowed;
        }

        .sell-form {
            max-width: 600px;
        }

        .form-section {
            margin-bottom: 30px;
        }

        .form-section h3 {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 16px;
            color: var(--pl);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-row.full {
            grid-template-columns: 1fr;
        }

        .listings-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .listings-table thead {
            background: var(--bg3);
            border-bottom: 2px solid var(--line);
        }

        .listings-table th {
            padding: 12px 16px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
        }

        .listings-table td {
            padding: 12px 16px;
            border-bottom: 1px solid var(--line);
        }

        .listings-table tr:hover {
            background: rgba(124, 58, 237, 0.1);
        }

        .toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 16px 24px;
            background: var(--bg2);
            border: 1px solid var(--line);
            border-radius: 8px;
            max-width: 400px;
            animation: slideIn 0.3s ease;
            z-index: 1000;
        }

        .toast.success {
            border-color: var(--green);
            background: rgba(34, 197, 94, 0.1);
        }

        .toast.error {
            border-color: var(--red);
            background: rgba(248, 113, 113, 0.1);
        }

        .toast.warning {
            border-color: var(--amber);
            background: rgba(251, 191, 36, 0.1);
        }

        @keyframes slideIn {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--w3);
        }

        .empty-state p {
            margin-bottom: 20px;
            font-size: 16px;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 40px;
        }

        .pagination button {
            padding: 8px 12px;
            min-width: 40px;
        }

        .pagination button.active {
            background: var(--p2);
        }

        .pagination button:disabled {
            background: var(--bg3);
            color: var(--w3);
            cursor: not-allowed;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            align-items: center;
            justify-content: center;
            z-index: 2000;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: var(--bg2);
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 30px;
            max-width: 400px;
            text-align: center;
        }

        .modal-content h3 {
            margin-bottom: 16px;
            font-size: 20px;
        }

        .modal-content p {
            margin-bottom: 24px;
            color: var(--w2);
        }

        .modal-actions {
            display: flex;
            gap: 10px;
        }

        .modal-actions button {
            flex: 1;
        }

        @media (max-width: 768px) {
            .listings-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .nav-tabs {
                gap: 10px;
            }

            .filters {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="back-link">← Back to Justice</a>

        <?php if (!$isLoggedIn): ?>
            <div class="login-container" id="loginBox">
                <h2>Login to Marketplace</h2>
                <div id="autoLoginMsg" style="display:none;text-align:center;padding:20px 0;color:var(--w3)">Signing you in...</div>
                <div id="loginFields">
                    <div class="form-group">
                        <label>Username or Email</label>
                        <input type="text" id="loginUsername" placeholder="Enter your username or email">
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" id="loginPassword" placeholder="Enter your password">
                    </div>
                    <button class="login-btn" onclick="handleLogin()">Login</button>
                </div>
            </div>
            <script>
            (function() {
                var t = localStorage.getItem('jl_token');
                if (!t) return;
                document.getElementById('loginFields').style.display = 'none';
                document.getElementById('autoLoginMsg').style.display = 'block';
                fetch('?ajax=auto-login', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ token: t })
                }).then(function(r) { return r.json(); }).then(function(d) {
                    if (d.ok) { location.reload(); }
                    else {
                        document.getElementById('loginFields').style.display = 'block';
                        document.getElementById('autoLoginMsg').style.display = 'none';
                    }
                }).catch(function() {
                    document.getElementById('loginFields').style.display = 'block';
                    document.getElementById('autoLoginMsg').style.display = 'none';
                });
            })();
            </script>
        <?php else: ?>
            <header>
                <h1>Marketplace</h1>
                <div class="header-actions">
                    <div class="user-info">
                        <strong><?php echo htmlspecialchars($currentUser['username'] ?? 'User'); ?></strong>
                        <br>
                        <span><?php echo htmlspecialchars($currentUser['points'] ?? 0); ?> points</span>
                    </div>
                    <button onclick="handleLogout()">Logout</button>
                </div>
            </header>

            <div class="nav-tabs">
                <button class="nav-tab active" data-tab="browse">Browse</button>
                <button class="nav-tab" data-tab="sell">Sell</button>
                <button class="nav-tab" data-tab="my-listings">My Listings</button>
            </div>

            <div id="browse" class="tab-content active">
                <div class="filters">
                    <div class="filter-group">
                        <label for="typeFilter">Type:</label>
                        <select id="typeFilter" onchange="loadBrowse()">
                            <option value="">All Items</option>
                            <option value="cape">Capes</option>
                            <option value="wings">Wings</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="sortFilter">Sort By:</label>
                        <select id="sortFilter" onchange="loadBrowse()">
                            <option value="newest">Newest</option>
                            <option value="price-low">Price: Low to High</option>
                            <option value="price-high">Price: High to Low</option>
                        </select>
                    </div>
                </div>

                <div id="browseListings" class="listings-grid"></div>
                <div id="browsePagination" class="pagination"></div>
            </div>

            <div id="sell" class="tab-content">
                <div class="sell-form">
                    <div class="form-section">
                        <h3>Create a New Listing</h3>
                        <div class="form-group">
                            <label for="sellName">Item Name</label>
                            <input type="text" id="sellName" placeholder="e.g., Midnight Cape">
                        </div>
                        <div class="form-group">
                            <label for="sellDescription">Description</label>
                            <textarea id="sellDescription" placeholder="Describe your item in detail..."></textarea>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="sellType">Type</label>
                                <select id="sellType">
                                    <option value="">Select type</option>
                                    <option value="cape">Cape</option>
                                    <option value="wings">Wings</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="sellPrice">Price (points)</label>
                                <input type="number" id="sellPrice" placeholder="100" min="1">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="sellTexture">Texture File (PNG)</label>
                                <input type="file" id="sellTexture" accept=".png">
                            </div>
                            <div class="form-group">
                                <label for="sellPreview">Preview Image (PNG/JPG)</label>
                                <input type="file" id="sellPreview" accept=".png,.jpg,.jpeg">
                            </div>
                        </div>
                        <button onclick="handleCreateListing()" style="width: 100%;">Create Listing</button>
                    </div>
                </div>
            </div>

            <div id="my-listings" class="tab-content">
                <div id="myListingsContainer"></div>
            </div>
        <?php endif; ?>
    </div>

    <div id="buyModal" class="modal">
        <div class="modal-content">
            <h3>Confirm Purchase</h3>
            <p>Are you sure you want to buy <strong id="buyItemName"></strong> for <strong id="buyItemPrice"></strong> points?</p>
            <div class="modal-actions">
                <button onclick="closeBuyModal()">Cancel</button>
                <button onclick="confirmBuy()">Confirm</button>
            </div>
        </div>
    </div>

    <script>
        let currentPage = 1;
        let totalPages = 1;
        let pendingBuyListingId = null;

        function showToast(message, type = 'info') {
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            toast.textContent = message;
            document.body.appendChild(toast);
            setTimeout(() => {
                toast.style.animation = 'slideIn 0.3s ease reverse';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        async function handleLogin() {
            const username = document.getElementById('loginUsername').value.trim();
            const password = document.getElementById('loginPassword').value.trim();

            if (!username || !password) {
                showToast('Please fill in all fields', 'warning');
                return;
            }

            try {
                const response = await fetch('?ajax=login', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ login: username, password })
                });

                const data = await response.json();

                if (data.error) {
                    showToast(data.error, 'error');
                } else {
                    showToast('Logged in successfully', 'success');
                    setTimeout(() => location.reload(), 1000);
                }
            } catch (err) {
                showToast('Login failed', 'error');
            }
        }

        async function handleLogout() {
            try {
                await fetch('?ajax=logout');
                showToast('Logged out', 'success');
                setTimeout(() => location.reload(), 1000);
            } catch (err) {
                showToast('Logout failed', 'error');
            }
        }

        document.querySelectorAll('.nav-tab').forEach(tab => {
            tab.addEventListener('click', () => {
                document.querySelectorAll('.nav-tab').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
                const tabName = tab.getAttribute('data-tab');
                document.getElementById(tabName).classList.add('active');

                if (tabName === 'browse') loadBrowse();
                if (tabName === 'my-listings') loadMyListings();
            });
        });

        async function loadBrowse(page = 1) {
            currentPage = page;
            const type = document.getElementById('typeFilter').value;
            const sort = document.getElementById('sortFilter').value;

            try {
                const response = await fetch(`?ajax=browse&type=${encodeURIComponent(type)}&sort=${encodeURIComponent(sort)}&page=${page}`);
                const data = await response.json();

                if (data.error) {
                    showToast(data.error, 'error');
                    return;
                }

                const listings = data.listings || [];
                const html = listings.length === 0
                    ? '<div class="empty-state" style="grid-column: 1/-1;"><p>No listings found</p></div>'
                    : listings.map(item => `
                        <div class="listing-card">
                            <div class="listing-image">
                                ${item.previewPath ? `<img src="${item.previewPath}" alt="${item.name}">` : (item.texturePath ? `<img src="${item.texturePath}" alt="${item.name}">` : '<div style="width:100%;height:100%;background:var(--bg3);"></div>')}
                            </div>
                            <div class="listing-content">
                                <div class="listing-header">
                                    <div class="listing-name">${item.name}</div>
                                    <div class="listing-meta">
                                        <span class="badge ${item.type}">${item.type}</span>
                                    </div>
                                </div>
                                <div class="listing-price">${item.price} pts</div>
                                <div class="listing-seller">By ${item.sellerName || 'Unknown'} · ${item.salesCount || 0} sale${item.salesCount === 1 ? '' : 's'}</div>
                                <div class="listing-actions">
                                    <button onclick="openBuyModal(${item.id}, '${item.name}', ${item.price})" ${item.isOwn ? 'disabled' : ''}>
                                        ${item.isOwn ? 'Your Item' : 'Buy'}
                                    </button>
                                </div>
                            </div>
                        </div>
                    `).join('');

                document.getElementById('browseListings').innerHTML = html;

                totalPages = data.pages || data.totalPages || 1;
                renderPagination('browsePagination', page, totalPages, loadBrowse);
            } catch (err) {
                showToast('Failed to load listings', 'error');
            }
        }

        function openBuyModal(listingId, name, price) {
            pendingBuyListingId = listingId;
            document.getElementById('buyItemName').textContent = name;
            document.getElementById('buyItemPrice').textContent = price;
            document.getElementById('buyModal').classList.add('active');
        }

        function closeBuyModal() {
            document.getElementById('buyModal').classList.remove('active');
            pendingBuyListingId = null;
        }

        async function confirmBuy() {
            if (!pendingBuyListingId) return;

            try {
                const response = await fetch('?ajax=buy', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ listingId: pendingBuyListingId })
                });

                const data = await response.json();

                if (data.error) {
                    showToast(data.error, 'error');
                } else {
                    showToast(data.message || 'Purchase successful!', 'success');
                    closeBuyModal();
                    if (data.pointsRemaining !== undefined) {
                        const pi = document.querySelector('.user-info span');
                        if (pi) pi.textContent = data.pointsRemaining + ' points';
                    }
                    loadBrowse(currentPage);
                }
            } catch (err) {
                showToast('Purchase failed', 'error');
            }
        }

        async function handleCreateListing() {
            const name = document.getElementById('sellName').value.trim();
            const description = document.getElementById('sellDescription').value.trim();
            const type = document.getElementById('sellType').value;
            const price = document.getElementById('sellPrice').value;
            const texture = document.getElementById('sellTexture').files[0];
            const preview = document.getElementById('sellPreview').files[0];

            if (!name || !description || !type || !price || !texture || !preview) {
                showToast('Please fill in all fields, texture, and preview image', 'warning');
                return;
            }

            const formData = new FormData();
            formData.append('name', name);
            formData.append('description', description);
            formData.append('type', type);
            formData.append('price', price);
            formData.append('texture', texture);
            formData.append('preview', preview);

            try {
                const response = await fetch('?ajax=create', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.error) {
                    showToast(data.error, 'error');
                } else {
                    showToast('Listing created successfully!', 'success');
                    document.getElementById('sellName').value = '';
                    document.getElementById('sellDescription').value = '';
                    document.getElementById('sellType').value = '';
                    document.getElementById('sellPrice').value = '';
                    document.getElementById('sellTexture').value = '';
                    document.getElementById('sellPreview').value = '';
                }
            } catch (err) {
                showToast('Failed to create listing', 'error');
            }
        }

        async function loadMyListings() {
            try {
                const response = await fetch('?ajax=my-listings');
                const data = await response.json();

                if (data.error) {
                    showToast(data.error, 'error');
                    return;
                }

                const listings = data.listings || [];

                if (listings.length === 0) {
                    document.getElementById('myListingsContainer').innerHTML = '<div class="empty-state"><p>You don\'t have any listings yet</p></div>';
                    return;
                }

                const html = `
                    <table class="listings-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${listings.map(item => `
                                <tr>
                                    <td>${item.name}</td>
                                    <td><span class="badge ${item.type}">${item.type}</span></td>
                                    <td>${item.price} pts</td>
                                    <td><span class="badge ${item.status}">${item.status}</span></td>
                                    <td>
                                        ${['pending', 'approved'].includes(item.status) ? `
                                            <button onclick="handleRemoveListing(${item.id})" style="padding: 6px 12px; font-size: 12px;">Remove</button>
                                        ` : '<span style="color: var(--w3);">-</span>'}
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                `;

                document.getElementById('myListingsContainer').innerHTML = html;
            } catch (err) {
                showToast('Failed to load your listings', 'error');
            }
        }

        async function handleRemoveListing(listingId) {
            if (!confirm('Are you sure you want to remove this listing?')) return;

            try {
                const response = await fetch('?ajax=remove', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ listingId })
                });

                const data = await response.json();

                if (data.error) {
                    showToast(data.error, 'error');
                } else {
                    showToast('Listing removed', 'success');
                    loadMyListings();
                }
            } catch (err) {
                showToast('Failed to remove listing', 'error');
            }
        }

        function renderPagination(containerId, currentPage, totalPages, callback) {
            const container = document.getElementById(containerId);
            let html = '';

            if (currentPage > 1) {
                html += `<button onclick="${callback.name}(${currentPage - 1})">Previous</button>`;
            }

            for (let i = Math.max(1, currentPage - 2); i <= Math.min(totalPages, currentPage + 2); i++) {
                html += `<button ${i === currentPage ? 'class="active"' : ''} onclick="${callback.name}(${i})">${i}</button>`;
            }

            if (currentPage < totalPages) {
                html += `<button onclick="${callback.name}(${currentPage + 1})">Next</button>`;
            }

            container.innerHTML = html;
        }

        loadBrowse();

        async function refreshBalance() {
            try {
                const r = await fetch('?ajax=me');
                const d = await r.json();
                if (d.user && d.user.points !== undefined) {
                    const el = document.querySelector('.user-info span');
                    if (el) el.textContent = d.user.points + ' points';
                }
            } catch {}
        }
        refreshBalance();
        setInterval(refreshBalance, 30000);
    </script>
</body>
</html>
