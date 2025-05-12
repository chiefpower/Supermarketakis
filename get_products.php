<?php
require_once 'db.php';

$category = $_GET['category'] ?? '';

$sql = "
  SELECT 
    p.product_id, 
    p.name, 
    p.price, 
    p.photo_source, 
    SUM(COALESCE(si.quantity, 0)) AS total_quantity
  FROM products p
  LEFT JOIN store_inventory si ON p.product_id = si.product_id
  WHERE p.type = ?
  GROUP BY p.product_id, p.name, p.price, p.photo_source
";

$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $category);
$stmt->execute();
$result = $stmt->get_result();

ob_start();
?>
<div class="product-grid row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-5">
<?php while($row = $result->fetch_assoc()): ?>
  <?php $qty = $row['total_quantity'] ?? 0; ?>
  <div class="col">
    <div class="product-item">
      <a href="#" class="btn-wishlist">
        <svg width="24" height="24"><use xlink:href="#heart"></use></svg>
      </a>
      <figure>
        <a href="home.php" title="<?= htmlspecialchars($row['name']) ?>">
          <img src="<?= htmlspecialchars($row['photo_source']) ?>" class="tab-image" alt="<?= htmlspecialchars($row['name']) ?>">
        </a>
      </figure>
      <h3><?= htmlspecialchars($row['name']) ?></h3>
      <span class="qty">1 Unit</span>
      <span class="rating">
        <svg width="24" height="24" class="text-primary"><use xlink:href="#star-solid"></use></svg> 4.5
      </span>
      <span class="price">€<?= number_format($row['price'], 2) ?>
        <span style="margin-left: 10px; font-size: 0.65em; <?= $qty == 0 ? 'color:red;' : 'color:green;' ?>">
          <?= $qty == 0 ? 'Out of stock' : 'In stock' ?>
        </span>
      </span>
      <div class="d-flex align-items-center justify-content-between">
        <div class="input-group product-qty">
          <span class="input-group-btn">
            <button type="button" class="quantity-left-minus1 btn btn-danger btn-number" data-type="minus">
              <svg width="16" height="16"><use xlink:href="#minus"></use></svg>
            </button>
          </span>
          <input type="text" name="quantity" class="form-control input-number" value="1" min="1">
          <span class="input-group-btn">
            <button type="button" class="quantity-right-plus1 btn btn-success btn-number" data-type="plus">
              <svg width="16" height="16"><use xlink:href="#plus"></use></svg>
            </button>
          </span>
        </div>
        <button 
          class="nav-link add-to-cart1"
          data-id="<?= $row['product_id'] ?>"
          data-name="<?= htmlspecialchars($row['name']) ?>"
          data-price="<?= $row['price'] ?>" 
          <?= $qty == 0 ? 'disabled style="opacity:0.5; cursor: not-allowed;"' : '' ?>
        >
          Add to Cart <iconify-icon icon="uil:shopping-cart"></iconify-icon>
        </button>
      </div>
    </div>
  </div>
<?php endwhile; ?>
</div>
<?php
$html = ob_get_clean();
echo $html;
?>