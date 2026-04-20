<?php
require_once 'auth.php';
require_roles([2, 4]);
require_once 'admin/db.connect.php';

$customerId = (int) $_SESSION['user_id'];
$productId = (int) ($_GET['product_id'] ?? 0);
$message = '';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];

function valid_post_csrf(): bool
{
    $token = $_POST['csrf_token'] ?? '';
    return is_string($token) && isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!valid_post_csrf()) {
        $message = "Invalid form token. Please try again.";
    } elseif (isset($_POST['variant_id'])) {
        $variantId = (int) $_POST['variant_id'];
        if ($variantId > 0) {
            $conn->begin_transaction();
            try {
                $cartStmt = $conn->prepare("SELECT cart_id FROM cart WHERE user_id = ? LIMIT 1");
                $cartStmt->bind_param("i", $customerId);
                $cartStmt->execute();
                $cartRes = $cartStmt->get_result();
                if ($cartRes->num_rows > 0) {
                    $cartId = (int) $cartRes->fetch_assoc()['cart_id'];
                } else {
                    $createStmt = $conn->prepare("INSERT INTO cart (user_id) VALUES (?)");
                    $createStmt->bind_param("i", $customerId);
                    $createStmt->execute();
                    $cartId = (int) $conn->insert_id;
                }

                $existingStmt = $conn->prepare("SELECT cart_item_id, quantity FROM cart_item WHERE cart_id = ? AND variant_id = ? LIMIT 1");
                $existingStmt->bind_param("ii", $cartId, $variantId);
                $existingStmt->execute();
                $existingRes = $existingStmt->get_result();
                if ($existingRes->num_rows > 0) {
                    $item = $existingRes->fetch_assoc();
                    $newQty = (int) $item['quantity'] + 1;
                    $updateStmt = $conn->prepare("UPDATE cart_item SET quantity = ?, updated_at = NOW() WHERE cart_item_id = ?");
                    $updateStmt->bind_param("ii", $newQty, $item['cart_item_id']);
                    $updateStmt->execute();
                } else {
                    $insertStmt = $conn->prepare("INSERT INTO cart_item (cart_id, variant_id, quantity) VALUES (?, ?, 1)");
                    $insertStmt->bind_param("ii", $cartId, $variantId);
                    $insertStmt->execute();
                }

                $conn->commit();
                $message = "Variant added to cart.";
            } catch (Throwable $t) {
                $conn->rollback();
                $message = "Failed to add variant to cart.";
            }
        }
    } elseif (isset($_POST['wishlist_product_id'])) {
        $wishlistProductId = (int) $_POST['wishlist_product_id'];
        if ($wishlistProductId > 0) {
            $conn->begin_transaction();
            try {
                $wishlistStmt = $conn->prepare("SELECT wishlist_id FROM wishlist WHERE user_id = ? LIMIT 1");
                $wishlistStmt->bind_param("i", $customerId);
                $wishlistStmt->execute();
                $wishlistRes = $wishlistStmt->get_result();
                if ($wishlistRes->num_rows > 0) {
                    $wishlistId = (int) $wishlistRes->fetch_assoc()['wishlist_id'];
                } else {
                    $createWishlistStmt = $conn->prepare("INSERT INTO wishlist (user_id) VALUES (?)");
                    $createWishlistStmt->bind_param("i", $customerId);
                    $createWishlistStmt->execute();
                    $wishlistId = (int) $conn->insert_id;
                }

                $wishlistItemStmt = $conn->prepare("INSERT IGNORE INTO wishlist_item (wishlist_id, product_id) VALUES (?, ?)");
                $wishlistItemStmt->bind_param("ii", $wishlistId, $wishlistProductId);
                $wishlistItemStmt->execute();

                $conn->commit();
                $message = "Product saved to wishlist.";
            } catch (Throwable $t) {
                $conn->rollback();
                $message = "Failed to save product to wishlist.";
            }
        }
    } elseif (isset($_POST['start_chat_seller_id'])) {
        $sellerId = (int) $_POST['start_chat_seller_id'];
        if ($sellerId > 0) {
            $conversationStmt = $conn->prepare("SELECT conversation_id FROM conversation WHERE customer_id = ? AND seller_id = ? LIMIT 1");
            $conversationStmt->bind_param("ii", $customerId, $sellerId);
            $conversationStmt->execute();
            $conversationRes = $conversationStmt->get_result();
            if ($conversationRes->num_rows > 0) {
                $conversationId = (int) $conversationRes->fetch_assoc()['conversation_id'];
            } else {
                $createConversationStmt = $conn->prepare("INSERT INTO conversation (customer_id, seller_id) VALUES (?, ?)");
                $createConversationStmt->bind_param("ii", $customerId, $sellerId);
                $createConversationStmt->execute();
                $conversationId = (int) $conn->insert_id;
            }

            header("Location: customer_chat.php?conversation_id=" . $conversationId);
            exit;
        }
    }
}

$productStmt = $conn->prepare("
    SELECT p.product_id, p.name, p.description, p.category_gender, p.seller_id, s.shop_name
    FROM product p
    INNER JOIN seller s ON s.seller_id = p.seller_id
    WHERE p.product_id = ? AND p.status = 'active'
    LIMIT 1
");
$productStmt->bind_param("i", $productId);
$productStmt->execute();
$product = $productStmt->get_result()->fetch_assoc();

if (!$product) {
    header("Location: customer_home.php");
    exit;
}

$variantStmt = $conn->prepare("
    SELECT variant_id, size, color, price, stock_qty
    FROM product_variant
    WHERE product_id = ?
    ORDER BY size ASC, color ASC
");
$variantStmt->bind_param("i", $productId);
$variantStmt->execute();
$variants = $variantStmt->get_result()->fetch_all(MYSQLI_ASSOC);

$reviewStmt = $conn->prepare("
    SELECT r.rating, r.review_text, r.created_at, u.username
    FROM review r
    INNER JOIN user u ON u.user_id = r.customer_id
    WHERE r.product_id = ? AND r.review_status = 'active'
    ORDER BY r.created_at DESC
    LIMIT 10
");
$reviewStmt->bind_param("i", $productId);
$reviewStmt->execute();
$reviews = $reviewStmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Product Details</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="sidebar.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<style>
  body { background: #f5f1ee; }
  .main-content { margin-left: 240px; transition: 0.3s; padding: 70px 30px 30px 30px; }
  .sidebar.collapsed ~ .main-content { margin-left: 70px; }
  .details-card { border: none; border-radius: 20px; }
  @media (max-width: 768px) {
    .main-content { margin-left: 0; padding: 80px 20px 20px 20px; }
    .sidebar.collapsed ~ .main-content { margin-left: 0; }
  }
</style>
</head>
<body>
<div class="sidebar" id="sidebar">
  <div class="sidebar-header">
    <div class="toggle-btn" onclick="toggleSidebar()">☰</div>
    <div class="logo-text">Customer</div>
  </div>
  <a href="customer_profile.php"><i class="bi bi-person-circle"></i><span class="text">Profile</span></a>
  <a href="customer_home.php"><i class="bi bi-house"></i><span class="text">Home</span></a>
  <a href="customer_orders.php"><i class="bi bi-bag"></i><span class="text">Orders</span></a>
  <a href="customer_cart.php"><i class="bi bi-cart-check"></i><span class="text">Cart</span></a>
  <a href="customer_wishlist.php"><i class="bi bi-bookmark-heart"></i><span class="text">Wishlist</span></a>
  <a href="customer_chat.php"><i class="bi bi-chat-dots"></i><span class="text">Chat & Support</span></a>
  <a href="logout.php" class="logout"><i class="bi bi-box-arrow-right"></i><span class="text">Logout</span></a>
</div>

<div class="main-content">
  <div class="container-fluid px-0">
    <a href="customer_home.php" class="btn btn-link text-decoration-none mb-3"><i class="bi bi-arrow-left"></i> Back to products</a>
    <?php if ($message !== ''): ?>
      <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <div class="card details-card p-4">
      <h2 class="mb-2"><?php echo htmlspecialchars($product['name']); ?></h2>
      <p class="text-muted mb-1">Category: <?php echo htmlspecialchars($product['category_gender']); ?></p>
      <p class="text-muted mb-3">Sold by: <?php echo htmlspecialchars($product['shop_name']); ?></p>
      <p><?php echo nl2br(htmlspecialchars($product['description'] ?? '')); ?></p>

      <div class="d-flex gap-2 flex-wrap mt-3">
        <form method="POST" class="mb-0">
          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
          <input type="hidden" name="wishlist_product_id" value="<?php echo (int) $product['product_id']; ?>">
          <button type="submit" class="btn btn-outline-dark btn-sm">
            Add to Wishlist
          </button>
        </form>
        <form method="POST" class="mb-0">
          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
          <input type="hidden" name="start_chat_seller_id" value="<?php echo (int) $product['seller_id']; ?>">
          <button type="submit" class="btn btn-dark btn-sm">
            Chat Seller
          </button>
        </form>
      </div>

      <h5 class="mt-4">Select Size / Color</h5>
      <div class="table-responsive">
        <table class="table align-middle">
          <thead>
            <tr>
              <th>Size</th>
              <th>Color</th>
              <th>Price</th>
              <th>Stock</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($variants as $variant): ?>
            <tr>
              <td><?php echo htmlspecialchars($variant['size']); ?></td>
              <td><?php echo htmlspecialchars($variant['color']); ?></td>
              <td>P<?php echo number_format((float) $variant['price'], 2); ?></td>
              <td><?php echo (int) $variant['stock_qty']; ?></td>
              <td>
                <form method="POST">
                  <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                  <input type="hidden" name="variant_id" value="<?php echo (int) $variant['variant_id']; ?>">
                  <button type="submit" class="btn btn-sm btn-dark" <?php echo ((int) $variant['stock_qty'] <= 0) ? 'disabled' : ''; ?>>
                    Add to Cart
                  </button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <h5 class="mt-4">Reviews</h5>
      <?php if (empty($reviews)): ?>
        <p class="text-muted mb-0">No reviews yet for this product.</p>
      <?php else: ?>
        <div class="list-group list-group-flush">
          <?php foreach ($reviews as $review): ?>
            <div class="list-group-item px-0">
              <div class="d-flex justify-content-between">
                <strong><?php echo htmlspecialchars($review['username']); ?></strong>
                <small class="text-muted"><?php echo htmlspecialchars(date("M d, Y", strtotime((string) $review['created_at']))); ?></small>
              </div>
              <div class="mb-1">Rating: <?php echo (int) $review['rating']; ?>/5</div>
              <div><?php echo nl2br(htmlspecialchars((string) ($review['review_text'] ?? ''))); ?></div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
function toggleSidebar() {
  document.getElementById("sidebar").classList.toggle("collapsed");
}
</script>
</body>
</html>
