<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>J3RS Landing Page</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
body {
  margin: 0;
  font-family: 'Poppins', sans-serif;
  background: #fdf2f6;
}

/* Header */
header {
  position: sticky;
  top: 0;
  background: rgba(255,255,255,0.9);
  backdrop-filter: blur(10px);
  border-bottom: 1px solid #eee;
  padding: 15px 40px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.logo {
  display: flex;
  align-items: center;
  gap: 10px;
}
.logo-img {
  width: 45px;   /* 👈 adjust size here */
  height: auto;
}

.logo-box {
  width: 40px;
  height: 40px;
  background: #610C27;
  border-radius: 10px;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
}

header strong {
  color: #000; /* black */
}

nav {
  display: flex;
  gap: 25px;
}

nav a {
  text-decoration: none;
  color: #000; /* black */
  font-size: 14px;
  font-weight: 500;
}

nav a:hover {
  color: #a61b4a;
}

.buttons button {
  margin-left: 10px;
  padding: 8px 15px;
  border: none;
  cursor: pointer;
  border-radius: 6px;
  font-weight: 500;
}

/* Buttons */
.login {
  background: transparent;
  color: #000;
}

.signup {
  background: #a61b4a;
  color: white;
}

.signup:hover {
  background: #610C27;
}

/* Hero */
.hero {
  position: relative;
  height: 80vh;
  display: flex;
  align-items: center;
  justify-content: center;
  text-align: center;
  padding: 20px;
}

.hero img {
  position: absolute;
  width: 100%;
  height: 100%;
  object-fit: cover;
  opacity: 0.4;
}

.hero-content {
  position: relative;
  max-width: 700px;
}

.hero h1 {
  font-size: 50px;
  color: #000; /* black */
  margin-bottom: 20px;
}

.hero span {
  color: #a61b4a;
}

.hero p {
  color: #000; /* black */
  margin-bottom: 30px;
}

/* CTA Button */
.hero button {
  padding: 15px 30px;
  background: #a61b4a;
  color: white;
  border: none;
  cursor: pointer;
  border-radius: 6px;
  font-size: 16px;
}

.hero button:hover {
  background: #610C27;
}

/* Features */
.features {
  padding: 80px 40px;
  background: white;
}

.feature-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px,1fr));
  gap: 30px;
  text-align: center;
}

.feature-box {
  padding: 20px;
}

.icon {
  width: 60px;
  height: 60px;
  background: #fdf2f6;
  border-radius: 15px;
  margin: auto;
  margin-bottom: 20px;
}

h3 {
  color: #000; /* black */
}

p {
  color: #000; /* black */
}
</style>

</head>
<body>

<!-- Header -->
<header>
  <div class="logo">
    <img src="JERS-LOGO.png" class="logo-img">
  </div>

  <nav>
    <a href="#">Products</a>
    <a href="#">Categories</a>
    <a href="#">About Us</a>
    <a href="#">Contact</a>
  </nav>

  <div class="buttons">
    <button class="login">Log in</button>
    <button class="signup">Sign up</button>
  </div>
</header>

<!-- Hero Section -->
<section class="hero">
  <img src="labg.jpg">
  <div class="hero-content">
    <h1>
      Curated essentials for <br>
      <span>modern living.</span>
    </h1>
    <p>
      Discover our handpicked selection of premium products designed to
      elevate your everyday experience. Quality meets aesthetics.
    </p>
    <button>Shop Collection →</button>
  </div>
</section>

<!-- Features -->
<section class="features">
  <div class="feature-grid">

    <div class="feature-box">
      <div class="icon"></div>
      <h3>Free Shipping</h3>
      <p>On all orders over ₱2,000. Fast and reliable delivery.</p>
    </div>

    <div class="feature-box">
      <div class="icon"></div>
      <h3>Secure Payments</h3>
      <p>Your transactions are protected with strong encryption.</p>
    </div>

    <div class="feature-box">
      <div class="icon"></div>
      <h3>Easy Returns</h3>
      <p>Return items within 30 days for a full refund.</p>
    </div>

  </div>
</section>

</body>
</html>