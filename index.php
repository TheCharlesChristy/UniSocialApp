<?php
// index.php
// This PHP file generates the HTML structure for the Spark welcome page.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spark - Welcome</title>
    <!-- Link to external CSS for styling -->
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- HERO SECTION -->
    <section class="hero">
        <div class="hero-content">
            <!-- Engaging tagline and call-to-action -->
            <h1>Welcome to Spark</h1>
            <p>Ignite your social experience – connect, share, and shine!</p>
            <a href="#signup" class="cta-button">Join Now</a>
        </div>
    </section>

    <!-- FEATURES SECTION -->
    <section class="features">
        <!-- Each feature block includes an icon/image, title, and description -->
        <div class="feature">
            <img src="icon1.png" alt="Connect Icon">
            <h2>Connect</h2>
            <p>Engage with friends and discover new communities.</p>
        </div>
        <div class="feature">
            <img src="icon2.png" alt="Share Icon">
            <h2>Share</h2>
            <p>Express yourself with photos, stories, and updates.</p>
        </div>
        <div class="feature">
            <img src="icon3.png" alt="Inspire Icon">
            <h2>Inspire</h2>
            <p>Find motivation and share your passion with the world.</p>
        </div>
    </section>

    <!-- FOOTER SECTION -->
    <footer>
        <!-- Navigation links in the footer -->
        <nav class="footer-nav">
            <a href="#about">About</a>
            <a href="#contact">Contact</a>
            <a href="#privacy">Privacy Policy</a>
        </nav>
        <!-- Social media icons/links -->
        <div class="social-media">
            <a href="#"><img src="facebook.png" alt="Facebook"></a>
            <a href="#"><img src="twitter.png" alt="Twitter"></a>
            <a href="#"><img src="instagram.png" alt="Instagram"></a>
        </div>
    </footer>

    <!-- Link to external JavaScript for interactive elements -->
    <script src="script.js"></script>
</body>
</html>
