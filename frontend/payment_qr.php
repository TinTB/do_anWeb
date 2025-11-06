<?php
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra đơn hàng
if (!isset($_GET['order_id']) || !isset($_GET['method'])) {
    echo '<script>window.location.href = "cart.php";</script>';
    exit();
}

$order_id = $_GET['order_id'];
$payment_method = $_GET['method'];
$pageTitle = "Thanh toán - Soudemy";

include 'includes/header.php';

// QR codes mẫu (trong thực tế sẽ generate từ API)
$qr_codes = [
    'banking' => 'images/qr_banking.png',
    'momo' => 'images/qr_momo.png'
];

$qr_info = [
    'banking' => [
        'title' => 'Quét mã QR để chuyển khoản ngân hàng',
        'account' => '0123456789',
        'bank' => 'Vietcombank',
        'amount' => $_SESSION['last_order']['total'] ?? 0,
        'content' => 'SOUDEMY' . ($_SESSION['last_order']['order_number'] ?? '')
    ],
    'momo' => [
        'title' => 'Quét mã QR để thanh toán qua MoMo',
        'phone' => '0912345678',
        'amount' => $_SESSION['last_order']['total'] ?? 0,
        'content' => 'SOUDEMY' . ($_SESSION['last_order']['order_number'] ?? '')
    ]
];

$info = $qr_info[$payment_method] ?? [];
?>

<section class="payment-qr-section">
    <div class="container">
        <div class="qr-container">
            <h1>Thanh toán đơn hàng</h1>
            
            <div class="qr-card">
                <div class="qr-header">
                    <h2><?php echo $info['title'] ?? 'Quét mã QR để thanh toán'; ?></h2>
                    <div class="countdown" id="countdown">05:00</div>
                </div>
                
                <div class="qr-content">
                    <div class="qr-code">
                        <img src="<?php echo $qr_codes[$payment_method] ?? 'images/qr_default.png'; ?>" alt="QR Code">
                    </div>
                    
                    <div class="payment-info">
                        <?php if ($payment_method === 'banking'): ?>
                            <div class="info-item">
                                <strong>Số tài khoản:</strong> <?php echo $info['account'] ?? ''; ?>
                            </div>
                            <div class="info-item">
                                <strong>Ngân hàng:</strong> <?php echo $info['bank'] ?? ''; ?>
                            </div>
                        <?php else: ?>
                            <div class="info-item">
                                <strong>Số điện thoại:</strong> <?php echo $info['phone'] ?? ''; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="info-item">
                            <strong>Số tiền:</strong> $<?php echo number_format($info['amount'] ?? 0, 2); ?>
                        </div>
                        <div class="info-item">
                            <strong>Nội dung:</strong> <?php echo $info['content'] ?? ''; ?>
                        </div>
                    </div>
                </div>
                
                <div class="qr-actions">
                    <button class="btn btn-success" id="confirmPaymentBtn">
                        Tôi đã chuyển tiền
                    </button>
                    <a href="checkout.php" class="btn btn-outline">Quay lại</a>
                </div>
                
                <div class="payment-status" id="paymentStatus" style="display: none;">
                    <div class="status-success">
                        <i class="fa fa-check-circle"></i>
                        <p>Đã xác nhận thanh toán thành công!</p>
                        <p>Đang chuyển hướng...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.payment-qr-section {
    padding: 40px 0;
    min-height: 80vh;
    display: flex;
    align-items: center;
}

.qr-container {
    max-width: 500px;
    margin: 0 auto;
    text-align: center;
}

.qr-card {
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

.qr-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f8f9fa;
}

.qr-header h2 {
    margin: 0;
    color: #333;
    font-size: 24px;
}

.countdown {
    background: #dc3545;
    color: white;
    padding: 8px 16px;
    border-radius: 20px;
    font-weight: bold;
    font-size: 18px;
}

.qr-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
    align-items: start;
    margin-bottom: 30px;
}

.qr-code img {
    width: 100%;
    max-width: 200px;
    border: 1px solid #ddd;
    border-radius: 8px;
}

.payment-info {
    text-align: left;
}

.info-item {
    margin-bottom: 15px;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 6px;
}

.info-item strong {
    color: #333;
}

.qr-actions {
    display: flex;
    gap: 15px;
    justify-content: center;
}

.status-success {
    color: #28a745;
    text-align: center;
    padding: 20px;
}

.status-success i {
    font-size: 48px;
    margin-bottom: 15px;
}
</style>

<script>
let timeLeft = 300; // 5 phút

function updateCountdown() {
    const minutes = Math.floor(timeLeft / 60);
    const seconds = timeLeft % 60;
    document.getElementById('countdown').textContent = 
        `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    
    if (timeLeft > 0) {
        timeLeft--;
        setTimeout(updateCountdown, 1000);
    } else {
        // Hết thời gian, chuyển hướng về cart
        alert('Đã hết thời gian thanh toán!');
        window.location.href = 'cart.php';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Bắt đầu đếm ngược
    updateCountdown();
    
    // Xử lý xác nhận thanh toán
    document.getElementById('confirmPaymentBtn').addEventListener('click', function() {
        const btn = this;
        const statusDiv = document.getElementById('paymentStatus');
        
        btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Đang xác nhận...';
        btn.disabled = true;
        
        // Giả lập xác nhận thanh toán
        setTimeout(() => {
            statusDiv.style.display = 'block';
            btn.style.display = 'none';
            
            // Chuyển hướng sau 3 giây
            setTimeout(() => {
                window.location.href = 'order_success.php';
            }, 3000);
        }, 2000);
    });
});
</script>

<?php 
include 'includes/footer.php';
ob_end_flush();
?>