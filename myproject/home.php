<?php
session_start();
require "db.php";

// 1. Check Login
if (!isset($_SESSION['name'])) {
    header("Location: login.php");
    exit();
}

// 2. Fetch Menu
$stmt = $conn->prepare("SELECT * FROM menu");
$stmt->execute();
$menu = array_map(function($m){
    $m['id'] = (int)$m['id'];
    $m['price'] = (float)$m['price'];
    return $m;
}, $stmt->fetchAll(PDO::FETCH_ASSOC));

// 3. (NEW) Fetch User's Pending Cart from Database
$current_cart_db = [];

// Ensure we have a valid user identifier (adjust 'email' to 'name' if that's what you use)
$user_id = $_SESSION['email'] ?? $_SESSION['name']; 

// Find the ID of the pending order for this user
// Note: We check for 'status = pending' so we don't load old completed orders
$stmt_order = $conn->prepare("SELECT id FROM orders WHERE user_email = ? AND status = 'pending' LIMIT 1");
$stmt_order->execute([$user_id]);
$pending_order = $stmt_order->fetch(PDO::FETCH_ASSOC);

if ($pending_order) {
    // If a pending order exists, fetch the items inside it
    $stmt_items = $conn->prepare("
        SELECT 
            t1.item_id AS id, 
            t1.item_name AS name, 
            t1.price, 
            t1.qty
        FROM order_items t1
        WHERE t1.order_id = ?
    ");
    $stmt_items->execute([$pending_order['id']]);
    $items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

    // Format the array so JavaScript can read it easily
    foreach ($items as $item) {
        $item['id'] = (int)$item['id'];
        $item['price'] = (float)$item['price'];
        $item['qty'] = (int)$item['qty'];
        // Use the item ID as the key
        $current_cart_db[$item['id']] = $item;
    }
}

// Unique list of categories
$categories = array_values(array_unique(array_column($menu, 'category')));

function e($s){ return htmlspecialchars($s, ENT_QUOTES); }
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>TAR UMT Cafeteria — Home</title>
<style>
    :root{
        --accent:#6c5ce7;
        --muted:#6b7280;
        --card-bg: rgba(255,255,255,0.08);
    }
    * {
        box-sizing: border-box;
    }
    body{
        margin:0;
        font-family:Inter,system-ui,Arial;
        background: linear-gradient(180deg,#eef2ff 0%, #f8fafc 100%);
        color:#111827;
        min-height:100vh;
    }
    header{
        display:flex;
        align-items:center;
        justify-content:space-between;
        padding:18px 28px;
        background:white;
        box-shadow:0 2px 8px rgba(2,6,23,0.06);
        position:sticky; top:0; z-index:10;
    }
    .brand{display:flex; align-items:center; gap:12px}
    .brand .logo {
        width:44px; height:44px;
        display:flex; align-items:center; justify-content:center;
        border-radius:8px; font-weight:700;
    }
    .logo-img {
    width: 55px;
    height: 55px;
    object-fit: cover;
    border-radius: 8px;
    }
    .controls{display:flex; gap:12px; align-items:center}
    .search { padding:8px 12px; border-radius:8px; border:1px solid #e6e8ee; min-width:260px }
    .btn { background:var(--accent); color:#fff; border:none; padding:9px 14px; border-radius:8px; cursor:pointer }
    .container{max-width:1100px; margin:28px auto; padding:0 18px}
    .hero{
        display:flex; gap:24px; align-items:center; padding:28px; border-radius:14px;
        background:linear-gradient(135deg, rgba(108,92,231,0.12), rgba(99,102,241,0.06));
        margin-bottom:20px;
    }
    .hero .left h1{margin:0 0 8px; font-size:24px}
    .hero .left p{margin:0; color:var(--muted)}
    .layout{display:grid; grid-template-columns: 260px 1fr 340px; gap:18px;}
    @media(max-width:980px){ .layout{grid-template-columns:1fr; } .hero{flex-direction:column} .controls{flex-wrap:wrap}}
    /* sidebar */
    .sidebar{background:white; padding:16px; border-radius:10px; box-shadow:0 6px 18px rgba(2,6,23,0.06)}
    .category { display:flex; flex-direction:column; gap:8px }
    .category button { text-align:left; padding:8px 10px; border-radius:8px; border:1px solid #f1f5f9; background:white; cursor:pointer }
    .category button.active { background:var(--accent); color:white; border:none }
    /* menu grid */
    .menu-grid{display:grid; grid-template-columns:repeat(2,1fr); gap:14px}
    @media(max-width:740px){ .menu-grid{grid-template-columns:1fr} }
    .card{background: white; padding:14px; border-radius:12px; display:flex; gap:12px; align-items:center; box-shadow:0 8px 24px rgba(2,6,23,0.04)}
    .thumb{width:90px; height:70px; border-radius:8px; background:#f3f4f6; display:flex; align-items:center; justify-content:center; font-weight:600; color:#374151}
    .meta{flex:1}
    .meta h3{margin:0 0 6px; font-size:16px}
    .meta p{margin:0; color:var(--muted); font-size:14px}
    .price{font-weight:700; margin-top:6px}
    .actions{display:flex; flex-direction:column; gap:8px; align-items:flex-end}
    .small{padding:6px 10px; border-radius:8px; border:1px solid #e6e8ee; background:white; cursor:pointer}
    .cart{background:white; padding:14px; border-radius:10px; box-shadow:0 6px 18px rgba(2,6,23,0.06)}
    .cart h4{margin:0 0 10px}
    .cart-list{max-height:320px; overflow:auto; display:flex; flex-direction:column; gap:8px}
    .cart-item{display:flex; justify-content:space-between; gap:8px; align-items:center; padding:8px; border-radius:8px; background:var(--card-bg)}
    .cart-footer{display:flex; justify-content:space-between; align-items:center; margin-top:12px}
    .muted{color:var(--muted); font-size:13px}
    .empty{color:var(--muted); padding:20px; text-align:center}
    form.place-order{margin-top:12px}
    input.qty{width:60px; padding:6px 8px; border-radius:6px; border:1px solid #e6e8ee}
    @media (max-width: 1300px) {
        .layout {
            grid-template-columns: 1fr;
        }

        .cart {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            width: 100%;
            max-height: 55vh;
            overflow-y: auto;
            border-radius: 16px 16px 0 0;
            box-shadow: 0 -10px 30px rgba(0,0,0,0.15);
            z-index: 20;
            padding-bottom: 20px;
        }

        .container {
            padding-bottom: 60vh;
        }

        .cart-list {
            max-height: 200px;
        }
    }   
    @media (max-width: 580px) {
        .brand > div:not(.logo) {
            display: none;
        }

        .brand .logo {
            width: 70px;
            height: 70px;
        }

        .logo-img {
            width: 70px;
            height: 70px;
        }

        .brand {
            margin: 0px 10px;
        }
    }
</style>
</head>
<body>

<header>
    <div class="brand">
        <div class="logo"><img src="img/logo.jpg" class="logo-img"></div>
        <div>
            <div style="font-weight:700">TAR UMT Cafeteria</div>
            <div class="muted" style="font-size:13px">Cloud POC — Online Ordering</div>
        </div>
    </div>

    <div class="controls">
        <input id="search" class="search" placeholder="Search food or drink..." />
        <button class="btn" onclick="location.href='login.php'">Logout</button>
    </div>
</header>

<div class="container">
    <div class="hero">
        <div class="left">
            <h1>Welcome, <?php echo e($_SESSION['name']); ?></h1>
            <p>Order ahead and skip the queue.</p>
        </div>
    </div>

    <div class="layout">
        <aside class="sidebar">
            <h4 style="margin:0 0 10px">Categories</h4>
            <div class="category" id="categoryList">
                <button class="active" data-cat="all" onclick="filterCat('all')">All</button>
                <?php foreach($categories as $cat): ?>
                    <button data-cat="<?php echo e($cat); ?>" onclick="filterCat('<?php echo e($cat); ?>')"><?php echo e($cat); ?></button>
                <?php endforeach; ?>
            </div>
            <hr style="margin:12px 0">
            <div class="muted">Tip: Use the search box to quickly find items by name.</div>
        </aside>

        <main>
            <div style="margin-bottom:12px; display:flex; justify-content:space-between; align-items:center">
                <h3 style="margin:0">Menu</h3>
                <div class="muted" id="resultCount"></div>
            </div>

            <div class="menu-grid" id="menuGrid">
                <?php foreach($menu as $item): ?>
                    <div class="card" data-name="<?php echo e(strtolower($item['name'])); ?>" data-cat="<?php echo e($item['category']); ?>" data-id="<?php echo e($item['id']); ?>">
                    <div class="thumb">
                        <?php if (!empty($item['image'])): ?>
                            <img src="uploads/<?php echo e($item['image']); ?>" style="width:100%; height:100%; object-fit:cover; border-radius:8px;">
                        <?php else: ?>
                            <?php echo e(substr($item['name'],0,1)); ?>
                        <?php endif; ?>
                    </div>
                        <div class="meta">
                            <h3><?php echo e($item['name']); ?></h3>
                            <p class="muted"><?php echo e($item['category']); ?></p>
                            <div class="price">RM <?php echo number_format($item['price'],2); ?></div>
                        </div>
                        <div class="actions">
                            <input type="number" class="qty" value="1" min="1" />
                            <button class="small" onclick="addToCart(<?php echo e($item['id']); ?>)">Add</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>

        <aside class="cart">
            <h4 style="margin-bottom:8px">🛒 Your Order</h4>

            <div id="cartList" class="cart-list">
                <div class="empty">Cart is empty</div>
            </div>

            <div class="cart-footer">
                <div class="muted">Subtotal</div>
                <div id="subtotal">RM 0.00</div>
            </div>

            <form class="place-order" method="POST" action="order.php" onsubmit="return prepareOrder(event)">
                <input type="hidden" name="order_data" id="order_data">
                <button class="btn" type="submit" style="width:100%; margin-top:12px">Place Order</button>
            </form>
        </aside>
    </div>
</div>

<script>

const menu = <?php echo json_encode(array_values($menu)); ?>;
let cart = <?php echo json_encode($current_cart_db); ?>;

function money(n){ return 'RM ' + Number(n).toFixed(2); }

function updateResultCount(){
    const visible = document.querySelectorAll('#menuGrid .card:not([style*="display:none"])').length;
    document.getElementById('resultCount').textContent = visible + ' items';
}
updateResultCount();

document.getElementById('search').addEventListener('input', function(e){
    const q = e.target.value.trim().toLowerCase();
    document.querySelectorAll('#menuGrid .card').forEach(card=>{
        const name = card.dataset.name || '';
        card.style.display = name.includes(q) ? '' : 'none';
    });
    updateResultCount();
});

function filterCat(cat){
    document.querySelectorAll('#categoryList button').forEach(b=>b.classList.remove('active'));
    document.querySelector('#categoryList button[data-cat="'+(cat||'all')+'"]').classList.add('active');
    document.querySelectorAll('#menuGrid .card').forEach(card=>{
        if(cat === 'all' || card.dataset.cat === cat) card.style.display='';
        else card.style.display='none';
    });
    updateResultCount();
}

function addToCart(id){
    const card = document.querySelector('#menuGrid .card[data-id="'+id+'"]');
    const qtyInput = card.querySelector('input.qty');
    let qty = parseInt(qtyInput.value) || 1;
    const item = menu.find(m=>m.id==id);
    if(!item) return;
    if(cart[id]) cart[id].qty += qty;
    else cart[id] = { ...item, qty: qty };
    renderCart();
}

function renderCart(){
    const el = document.getElementById('cartList');
    el.innerHTML = '';
    const keys = Object.keys(cart);
    if(keys.length === 0){
        el.innerHTML = '<div class="empty">Cart is empty</div>';
        document.getElementById('subtotal').textContent = money(0);
        return;
    }
    let subtotal = 0;
    keys.forEach(k=>{
        const it = cart[k];
        subtotal += it.price * it.qty;
        const node = document.createElement('div');
        node.className = 'cart-item';
        node.innerHTML = `
            <div style="flex:1">
                <div style="font-weight:600">${it.name}</div>
                <div class="muted">RM ${it.price.toFixed(2)} x ${it.qty}</div>
            </div>
            <div style="display:flex; gap:6px; align-items:center">
                <button onclick="dec(${it.id})" class="small">-</button>
                <button onclick="inc(${it.id})" class="small">+</button>
                <button onclick="removeItem(${it.id})" class="small">Remove</button>
            </div>
        `;
        el.appendChild(node);
    });
    document.getElementById('subtotal').textContent = money(subtotal);
}

function inc(id){ cart[id].qty++; renderCart(); }
function dec(id){ cart[id].qty = Math.max(1, cart[id].qty-1); renderCart(); }
function removeItem(id){ delete cart[id]; renderCart(); }

function prepareOrder(e){
    if(Object.keys(cart).length === 0){
        alert('Your cart is empty');
        e.preventDefault();
        return false;
    }
    document.getElementById('order_data').value = JSON.stringify(cart);
    return true;
}

renderCart();

</script>
</body>
</html>
