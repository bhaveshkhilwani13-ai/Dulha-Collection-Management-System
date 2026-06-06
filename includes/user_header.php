<?php
// includes/user_header.php - Simplified public storefront header
require_once __DIR__ . '/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dulha Collection - Premium Groom Wear Rental</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/style.css">
    <style>
        .user-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.2rem 5%;
            background: rgba(255, 255, 255, 0.97);
            backdrop-filter: blur(10px);
            position: sticky;
            top: 0;
            z-index: 1000;
            border-bottom: 1px solid var(--border-color);
            box-shadow: 0 2px 20px rgba(88,17,26,0.04);
        }
        .nav-links {
            display: flex;
            gap: 2rem;
            list-style: none;
            align-items: center;
        }
        .nav-links a {
            text-decoration: none;
            color: var(--text-primary);
            font-weight: 600;
            font-size: 0.92rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: color 0.3s ease;
        }
        .nav-links a:hover { color: var(--gold-color); }
        .hero-section {
            height: 80vh;
            background: linear-gradient(rgba(0,0,0,0.38), rgba(0,0,0,0.38)),
                        url('<?php echo $base_url; ?>/assets/images/hero.png');
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: #fff;
        }
        .hero-content h1 { font-size: 3.5rem; margin-bottom: 1rem; text-shadow: 0 4px 10px rgba(0,0,0,0.3); }
        .hero-content p { font-size: 1.3rem; margin-bottom: 2rem; font-family: 'Playfair Display', serif; font-style: italic; }
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(270px, 1fr));
            gap: 2rem;
        }
        .product-card {
            background: #fff;
            border-radius: 18px;
            overflow: hidden;
            border: 1px solid var(--border-color);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(88,17,26,0.1);
        }
        .product-img {
            height: 280px;
            background: #fdf8f2;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--border-color);
            font-size: 5rem;
            position: relative;
        }
        .product-info { padding: 1.5rem; }
        .category-tag {
            font-size: 0.72rem;
            color: var(--gold-color);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 0.5rem;
            display: block;
        }
        .product-name { font-size: 1.1rem; font-weight: 700; color: var(--accent-color); margin-bottom: 0.8rem; }
        .product-price { font-size: 1.1rem; font-weight: 800; color: var(--text-primary); }
    </style>
</head>
<body>
    <header class="user-nav">
        <div class="logo-container" style="margin-bottom:0; border-bottom:none; padding-bottom:0;">
            <div class="logo-icon">D</div>
            <div class="logo-text">
                <span class="main-title">Dulha Collection</span>
                <span class="sub-title">Royal Groom Wear</span>
            </div>
        </div>
        <nav>
            <ul class="nav-links">
                <li><a href="<?php echo $base_url; ?>/index.php"><i class="fa-solid fa-house"></i> Home</a></li>
                <li><a href="<?php echo $base_url; ?>/catalog.php"><i class="fa-solid fa-shirt"></i> Catalog</a></li>
                <li><a href="<?php echo $base_url; ?>/admin/index.php" class="btn btn-primary" style="padding: 0.5rem 1.2rem; font-size: 0.82rem; border-radius: 8px;">
                    <i class="fa-solid fa-shield-halved"></i> Admin Portal
                </a></li>
            </ul>
        </nav>
    </header>
