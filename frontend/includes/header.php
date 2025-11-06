<?php
// ============================================
// SESSION & DATABASE SETUP
// ============================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$databaseFile = __DIR__ . '/../config/database.php';
if (file_exists($databaseFile)) {
    require_once $databaseFile;
} else {
    if (!class_exists('Database')) {
        class Database {
            private $host = "localhost";
            private $db_name = "Soudemy_Demo";
            private $username = "root";
            private $password = "";
            public $conn;

            public function getConnection() {
                $this->conn = null;
                try {
                    $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
                    $this->conn->exec("set names utf8");
                    $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                } catch(PDOException $exception) {
                    error_log("Database connection error: " . $exception->getMessage());
                }
                return $this->conn;
            }
        }
    }
}

// ============================================
// CHECK LOGIN & LOAD USER INFO
// ============================================
$isLoggedIn = isset($_SESSION['user_id']);
$user = null;

if ($isLoggedIn) {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        if ($db) {
            $query = "SELECT * FROM users WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    } catch (Exception $e) {
        error_log("User query error: " . $e->getMessage());
    }
}

// ============================================
// FLASH MESSAGES
// ============================================
$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError = $_SESSION['flash_error'] ?? null;
if ($flashSuccess) {
    unset($_SESSION['flash_success']);
}
if ($flashError) {
    unset($_SESSION['flash_error']);
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'Soudemy - Modern Furniture Store'; ?></title>
    
    <!-- CSS External -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/Do_an/frontend/css/styles.css">
    <link rel="stylesheet" href="/Do_an/frontend/css/responsive.css">
    
    <!-- Header Styles -->
    <style>
        /* ============ HEADER CONTAINER ============ */
        .header {
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            padding: 16px 0;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-wrapper {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }

        .header-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            gap: 20px;
        }

        /* Logo - Nằm trái cùng */
        .logo {
            font-size: 28px;
            font-weight: 700;
            color: #333;
            text-decoration: none;
            white-space: nowrap;
            letter-spacing: -1px;
            transition: all 0.3s ease;
            flex-shrink: 0;
            min-width: 120px;
        }

        .logo:hover {
            color: #007bff;
        }

        /* Main Navigation - Nằm ở giữa */
        .main-nav {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 20px;
        }

        .main-nav ul {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
            gap: 8px;
            align-items: center;
            justify-content: center;
        }

        .main-nav li {
            position: relative;
        }

        .main-nav a {
            display: block;
            padding: 12px 18px;
            color: #333;
            text-decoration: none;
            font-weight: 500;
            font-size: 15px;
            transition: all 0.3s ease;
            border-radius: 4px;
        }

        .main-nav a:hover {
            color: #007bff;
            background: #f8f9fa;
        }

        /* Dropdown Menu (cho Shop) */
        .submenu {
            position: absolute;
            top: 100%;
            left: 0;
            background: white;
            list-style: none;
            padding: 8px 0;
            margin: 8px 0 0 0;
            border-radius: 6px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            min-width: 180px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            z-index: 200;
        }

        .main-nav li:hover .submenu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .submenu li {
            margin: 0;
        }

        .submenu a {
            padding: 12px 20px;
            font-size: 14px;
            color: #555;
            display: block;
            border-radius: 0;
            transition: all 0.3s ease;
        }

        .submenu a:hover {
            background: #f0f0f0;
            color: #007bff;
        }

        /* Header Actions - Nằm phải cùng */
        .header-actions {
            display: flex;
            align-items: center;
            gap: 16px;
            flex-shrink: 0;
            order: 3;
        }

        .search-container {
            position: relative;
            display: inline-block;
        }

        .search-toggle {
            background: none;
            border: none;
            font-size: 18px;
            cursor: pointer;
            padding: 8px;
            color: #333;
            transition: all 0.3s ease;
        }

        .search-toggle:hover {
            color: #007bff;
        }

        .search-box {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            display: none;
            min-width: 280px;
            z-index: 1000;
            margin-top: 8px;
        }

        .search-box.active {
            display: block;
        }

        .search-box form {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .search-box input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .search-box button {
            background: #333;
            color: white;
            border: none;
            padding: 10px 14px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .search-box button:hover {
            background: #007bff;
        }

        /* ============ USER ACCOUNT AREA - Nằm phải cùng ============ */
        .nav-user {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-shrink: 0;
            order: 4;
        }

        .user-name {
            font-size: 14px;
            color: #333;
            font-weight: 500;
            max-width: 150px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .btn {
            padding: 8px 14px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            border: none;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .btn-outline {
            border: 1px solid #ddd;
            color: #333;
            background: transparent;
        }

        .btn-outline:hover {
            border-color: #007bff;
            color: #007bff;
        }

        .btn-primary {
            background: #dc3545;
            color: white;
        }

        .btn-primary:hover {
            background: #c82333;
        }

        .login-toggle {
            padding: 8px 16px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            border: 1px solid #007bff;
            color: #007bff;
            background: transparent;
            transition: all 0.3s ease;
            flex-shrink: 0;
            order: 4;
        }

        .login-toggle:hover {
            background: #007bff;
            color: white;
        }

        .user-account {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logout-link {
            padding: 8px 16px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            border: none;
            background: none;
            transition: all 0.3s ease;
            color: #dc3545;
        }

        .logout-link:hover {
            color: #c82333;
        }

        /* Cart Link */
        .cart-link {
            display: flex;
            align-items: center;
            gap: 6px;
            color: #333;
            text-decoration: none;
            font-size: 16px;
            position: relative;
            padding: 8px;
            transition: all 0.3s ease;
        }

        .cart-link:hover {
            color: #007bff;
        }

        .cart-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #dc3545;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
        }

        /* ============ MODAL LOGIN ============ */
        .modal {
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.4);
            display: none;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal-content {
            background-color: white;
            margin: 10% auto;
            padding: 20px;
            border-radius: 8px;
            width: 90%;
            max-width: 400px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .modal-content h3 {
            margin-top: 0;
            margin-bottom: 20px;
            color: #333;
            font-size: 20px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            background: none;
            border: none;
            padding: 0;
        }

        .close:hover,
        .close:focus {
            color: #000;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            box-sizing: border-box;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 8px rgba(0, 123, 255, 0.25);
        }

        .form-group button {
            padding: 12px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .form-group button:hover {
            background: #0056b3;
        }

        /* ============ BUTTON STYLES ============ */
        .btn {
            padding: 8px 14px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            border: none;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .btn-outline {
            border: 1px solid #ddd;
            color: #333;
            background: transparent;
        }

        .btn-outline:hover {
            border-color: #007bff;
            color: #007bff;
        }

        .btn-primary {
            background: #dc3545;
            color: white;
        }

        .btn-primary:hover {
            background: #c82333;
        }

        /* ============ USER ACCOUNT AREA ============ */
        .nav-user {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0 12px;
            border-right: 1px solid #eee;
        }

        .user-name {
            font-size: 14px;
            color: #333;
            font-weight: 500;
            max-width: 150px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .login-toggle {
            padding: 8px 16px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            border: 1px solid #007bff;
            color: #007bff;
            background: transparent;
            transition: all 0.3s ease;
        }

        .login-toggle:hover {
            background: #007bff;
            color: white;
        }

        /* User Account */
        .user-account {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logout-link {
            padding: 8px 16px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            border: none;
            background: none;
            transition: all 0.3s ease;
            color: #dc3545;
        }

        .logout-link:hover {
            color: #c82333;
        }

        /* ============ FLASH MESSAGES ============ */
        .flash-message {
            position: fixed;
            top: 80px;
            left: 50%;
            transform: translateX(-50%);
            padding: 16px 24px;
            border-radius: 6px;
            z-index: 999;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            animation: slideDown 0.3s ease;
        }

        .flash-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .flash-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Cart Link */
        .cart-link {
            display: flex;
            align-items: center;
            gap: 6px;
            color: #333;
            text-decoration: none;
            font-size: 16px;
            position: relative;
            padding: 8px;
            transition: all 0.3s ease;
        }

        .cart-link:hover {
            color: #007bff;
        }

        .cart-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #dc3545;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
        }

        /* Menu Toggle (Mobile) */
        .menu-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: #333;
        }

        /* ============ RESPONSIVE ============ */
        @media (max-width: 1024px) {
            .header-inner {
                gap: 12px;
            }

            .main-nav ul {
                gap: 4px;
            }

            .main-nav a {
                padding: 10px 14px;
                font-size: 14px;
            }
        }

        @media (max-width: 768px) {
            .header-inner {
                gap: 8px;
                flex-wrap: nowrap;
            }

            .logo {
                font-size: 24px;
                min-width: auto;
                flex-shrink: 0;
            }

            .main-nav {
                display: none;
            }

            .menu-toggle {
                display: block;
            }

            .nav-user {
                gap: 8px;
                padding-right: 8px;
                border-right: none;
            }

            .btn {
                padding: 6px 10px;
                font-size: 12px;
            }

            .user-name {
                max-width: 100px;
            }

            .header-actions {
                gap: 12px;
            }

            .search-box {
                min-width: 240px;
            }

            .login-toggle {
                padding: 6px 12px;
                font-size: 13px;
            }
        }

        @media (max-width: 480px) {
            .header {
                padding: 12px 0;
            }

            .header-inner {
                gap: 6px;
            }

            .logo {
                font-size: 20px;
            }

            .header-actions {
                gap: 10px;
            }

            .search-box {
                min-width: 200px;
                right: -30px;
            }

            .nav-user {
                display: none;
            }

            .login-toggle {
                display: block;
                padding: 6px 10px;
                font-size: 12px;
            }

            .modal-content {
                width: 95%;
                margin: 20% auto;
            }

            .search-toggle {
                font-size: 16px;
                padding: 6px;
            }

            .cart-link {
                font-size: 14px;
                padding: 6px;
            }
        }
    </style>
</head>
<body>
<?php
// Flash message output: show as top-fixed slide-down bar
$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError = $_SESSION['flash_error'] ?? null;
if ($flashSuccess) {
    echo '<div id="topFlash" class="flash-message flash-success" role="alert">' . htmlspecialchars($flashSuccess) . '</div>';
    unset($_SESSION['flash_success']);
}
if ($flashError) {
    echo '<div id="topFlash" class="flash-message flash-error" role="alert">' . htmlspecialchars($flashError) . '</div>';
    unset($_SESSION['flash_error']);
}
?>

<!-- Header -->
<header class="header">
    <div class="container header-inner">
        <!-- Logo -->
        <a href="/Do_an/frontend/index.php" class="logo">soudemy</a>

        <!-- Main Navigation với Dropdown -->
        <nav class="main-nav">
            <ul>
                <li><a href="/Do_an/frontend/index.php">Home</a></li>
                <li>
                    <a href="/Do_an/frontend/shop.php">Shop</a>
                    <ul class="submenu">
                        <li><a href="/Do_an/frontend/shop.php?category=sofa">Sofa</a></li>
                        <li><a href="/Do_an/frontend/shop.php?category=table">Table</a></li>
                        <li><a href="/Do_an/frontend/shop.php?category=lamp">Lamp</a></li>
                        <li><a href="/Do_an/frontend/shop.php?category=bed">Bed</a></li>
                        <li><a href="/Do_an/frontend/shop.php?category=bookshelf">Bookshelf</a></li>
                    </ul>
                </li>
                <li><a href="/Do_an/frontend/about.php">About us</a></li>
                <?php if ($isLoggedIn && ($user['role'] ?? '') === 'admin'): ?>
                    <li><a href="/Do_an/backend/admin.php">Admin Panel</a></li>
                <?php endif; ?>
            </ul>
        </nav>

        <!-- USER ACCOUNT AREA -->
        <?php if ($isLoggedIn): ?>
            <div class="nav-user">
                <span class="user-name" title="<?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>">
                    <?php echo htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username'] ?? $_SESSION['email'] ?? 'User'); ?>
                </span>
                <a href="/Do_an/frontend/profile.php" class="btn btn-outline">Hồ sơ</a>
                <a href="/Do_an/frontend/logout.php" class="btn btn-primary">Đăng xuất</a>
            </div>
        <?php else: ?>
            <a href="#" class="login-toggle">Đăng nhập</a>
        <?php endif; ?>

        <!-- Header Actions -->
        <div class="header-actions">
            <!-- Search -->
            <div class="search-container">
                <button class="search-toggle"><i class="fa fa-search"></i></button>
                <div class="search-box" id="searchBox">
                    <form id="searchForm" method="GET" action="/Do_an/frontend/shop.php">
                        <input type="text" name="search" id="searchInput" placeholder="Tìm kiếm sản phẩm..." required>
                        <button type="submit"><i class="fa fa-search"></i></button>
                    </form>
                </div>
            </div>

            <!-- Cart -->
            <a href="/Do_an/frontend/cart.php" class="cart-link" title="Cart">
                <i class="fa fa-shopping-cart"></i>
                <?php if (isset($_SESSION['cart_count']) && $_SESSION['cart_count'] > 0): ?>
                    <span class="cart-count"><?php echo $_SESSION['cart_count']; ?></span>
                <?php endif; ?>
            </a>

            <!-- Menu Toggle (Mobile) -->
            <button class="menu-toggle"><i class="fa fa-bars"></i></button>
        </div>
    </div>
</header>

<!-- Login Modal -->
<div id="loginModal" class="modal">
    <div class="modal-content">
        <button class="close" title="Đóng">&times;</button>
        <h3>Đăng nhập</h3>
        <div id="loginError" style="color:#dc3545; display:none; margin-bottom:15px; padding:10px; background:#f8d7da; border-radius:4px; border:1px solid #f5c6cb;"></div>

        <form id="loginForm">
            <div class="form-group">
                <input type="email" name="email" id="loginEmail" placeholder="Email" required>
            </div>
            <div class="form-group">
                <input type="password" name="password" id="loginPassword" placeholder="Mật khẩu" required>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary" style="width:100%;">Đăng nhập</button>
            </div>
            <div style="text-align:center; font-size:13px; color:#666; margin-top:10px;">
                Chưa có tài khoản? <a href="/Do_an/frontend/register.php" style="color:#007bff; text-decoration:none;">Đăng ký ngay</a>
            </div>
        </form>
    </div>
</div>

<script>
// ============ FLASH MESSAGE AUTO-HIDE ============
document.addEventListener('DOMContentLoaded', function(){
    const flash = document.getElementById('topFlash');
    if (!flash) return;
    flash.classList.add('show');
    setTimeout(() => {
        flash.classList.remove('show');
        flash.classList.add('hide');
        setTimeout(() => { flash.remove(); }, 400);
    }, 4500);
});

// ============ SEARCH TOGGLE ============
document.querySelector('.search-toggle')?.addEventListener('click', function(e) {
    e.preventDefault();
    e.stopPropagation();
    document.getElementById('searchBox').classList.toggle('active');
    if (document.getElementById('searchBox').classList.contains('active')) {
        document.getElementById('searchInput').focus();
    }
});

document.addEventListener('click', function(e) {
    if (!e.target.closest('.search-container')) {
        document.getElementById('searchBox').classList.remove('active');
    }
});

// ============ LOGIN MODAL ============
const loginToggle = document.querySelector('.login-toggle');
const loginModal = document.getElementById('loginModal');
const closeModal = document.querySelector('.close');

if (loginToggle) {
    loginToggle.addEventListener('click', function(e) {
        e.preventDefault();
        loginModal.style.display = 'block';
    });
}

if (closeModal) {
    closeModal.addEventListener('click', () => {
        loginModal.style.display = 'none';
    });
}

window.addEventListener('click', (e) => {
    if (e.target === loginModal) {
        loginModal.style.display = 'none';
    }
});

// ============ AJAX LOGIN ============
document.addEventListener('DOMContentLoaded', function () {
    const loginForm = document.getElementById('loginForm');
    const loginError = document.getElementById('loginError');

    loginForm?.addEventListener('submit', function (e) {
        e.preventDefault();
        loginError.style.display = 'none';
        const email = document.getElementById('loginEmail').value.trim();
        const password = document.getElementById('loginPassword').value;

        fetch('/Do_an/frontend/includes/process_login_ajax.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email: email, password: password })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                loginError.textContent = data.message || 'Đăng nhập thất bại';
                loginError.style.display = 'block';
            }
        })
        .catch(err => {
            console.error(err);
            loginError.textContent = 'Lỗi kết nối';
            loginError.style.display = 'block';
        });
    });
});
</script>

</body>
</html>