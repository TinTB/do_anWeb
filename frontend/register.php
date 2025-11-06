<?php
$pageTitle = "Đăng ký - Soudemy";
include 'includes/header.php';
?>

<section class="auth-section">
    <div class="container">
        <div class="auth-form">
            <h2>Đăng ký tài khoản</h2>
            <form action="process_register.php" method="POST" id="registerForm">
                <div class="form-group">
                    <input type="text" name="full_name" placeholder="Họ và tên" required>
                </div>
                <div class="form-group">
                    <input type="email" name="email" placeholder="Email" required>
                </div>
                <div class="form-group">
                    <input type="tel" name="phone" placeholder="Số điện thoại" required>
                </div>
                <div class="form-group">
                    <input type="password" name="password" placeholder="Mật khẩu" required minlength="6">
                </div>
                <div class="form-group">
                    <input type="password" name="confirm_password" placeholder="Xác nhận mật khẩu" required>
                </div>
                <button type="submit" class="btn btn-primary">Đăng ký</button>
            </form>
            <p>Đã có tài khoản? <a href="login.php">Đăng nhập ngay</a></p>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>