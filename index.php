<?php require_once 'includes/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Professional Telegram Bot Hosting</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #6366f1; --secondary: #8b5cf6; --dark: #0f172a; --light: #f8fafc; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: var(--dark); overflow-x: hidden; }
        
        /* Navbar */
        .navbar { background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(10px); padding: 15px 0; position: fixed; width: 100%; top: 0; z-index: 1000; }
        .navbar-brand { font-weight: 800; font-size: 1.8rem; background: linear-gradient(135deg, var(--primary), var(--secondary)); -webkit-background-clip: text; background-clip: text; color: transparent; }
        
        /* Hero Section */
        .hero { min-height: 100vh; display: flex; align-items: center; padding: 120px 0 80px; background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%); position: relative; overflow: hidden; }
        .hero::before { content: ''; position: absolute; width: 200%; height: 200%; background: radial-gradient(circle, rgba(99,102,241,0.1) 1%, transparent 1%); background-size: 50px 50px; animation: moveBackground 20s linear infinite; }
        @keyframes moveBackground { 0% { transform: translate(0, 0); } 100% { transform: translate(50px, 50px); } }
        .hero h1 { font-size: 4rem; font-weight: 800; margin-bottom: 20px; background: linear-gradient(135deg, #fff, #a855f7); -webkit-background-clip: text; background-clip: text; color: transparent; }
        .hero p { font-size: 1.2rem; color: rgba(255,255,255,0.8); margin-bottom: 30px; }
        
        /* Stats Cards */
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 30px; margin-top: -80px; position: relative; z-index: 2; }
        .stat-card { background: #1e293b; border-radius: 20px; padding: 25px; text-align: center; border: 1px solid rgba(255,255,255,0.1); transition: all 0.3s; }
        .stat-card:hover { transform: translateY(-10px); border-color: var(--primary); }
        .stat-card i { font-size: 3rem; color: var(--primary); margin-bottom: 15px; }
        .stat-card h3 { font-size: 2rem; font-weight: 800; color: white; margin-bottom: 5px; }
        
        /* Pricing Cards */
        .pricing-card { background: #1e293b; border-radius: 24px; padding: 30px; text-align: center; transition: all 0.3s; border: 1px solid rgba(255,255,255,0.1); }
        .pricing-card:hover { transform: translateY(-10px); border-color: var(--primary); }
        .pricing-card.popular { border: 2px solid var(--primary); transform: scale(1.02); }
        .pricing-card.popular::before { content: '🔥 POPULAR'; position: absolute; top: -12px; right: 20px; background: var(--primary); color: white; padding: 5px 15px; border-radius: 20px; font-size: 12px; }
        .price { font-size: 3rem; font-weight: 800; color: var(--primary); margin: 20px 0; }
        .feature-list { list-style: none; padding: 0; margin: 20px 0; text-align: left; }
        .feature-list li { padding: 10px 0; border-bottom: 1px solid #334155; color: #cbd5e1; }
        .feature-list li i { color: #10b981; margin-right: 10px; }
        
        /* Features Grid */
        .feature-card { background: #1e293b; border-radius: 20px; padding: 30px; text-align: center; transition: all 0.3s; border: 1px solid rgba(255,255,255,0.1); }
        .feature-card:hover { transform: translateY(-5px); border-color: var(--primary); }
        .feature-card i { font-size: 3rem; background: linear-gradient(135deg, var(--primary), var(--secondary)); -webkit-background-clip: text; background-clip: text; color: transparent; margin-bottom: 20px; }
        
        /* Language Icons */
        .lang-icons { display: flex; justify-content: center; gap: 30px; margin: 40px 0; flex-wrap: wrap; }
        .lang-icon { text-align: center; padding: 20px; background: #1e293b; border-radius: 20px; min-width: 100px; border: 1px solid rgba(255,255,255,0.1); transition: all 0.3s; }
        .lang-icon:hover { transform: translateY(-5px); border-color: var(--primary); }
        .lang-icon span { font-size: 3rem; display: block; margin-bottom: 10px; }
        
        /* Footer */
        .footer { background: #0f172a; border-top: 1px solid #1e293b; padding: 60px 0 30px; }
        .footer a { color: #94a3b8; text-decoration: none; transition: all 0.3s; }
        .footer a:hover { color: white; }
        
        /* Buttons */
        .btn-primary { background: linear-gradient(135deg, var(--primary), var(--secondary)); border: none; border-radius: 12px; padding: 12px 30px; font-weight: 600; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(99,102,241,0.3); }
        .btn-outline-primary { border: 2px solid var(--primary); color: var(--primary); border-radius: 12px; padding: 10px 28px; font-weight: 600; }
        .btn-outline-primary:hover { background: var(--primary); color: white; }
        
        /* Server Status Bar */
        .server-status { background: #0f172a; border-bottom: 1px solid #1e293b; padding: 8px 0; font-size: 12px; color: #94a3b8; position: fixed; top: 0; width: 100%; z-index: 1001; }
        .status-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; margin-right: 5px; }
        .status-online { background: #10b981; box-shadow: 0 0 5px #10b981; }
        
        @media (max-width: 768px) {
            .hero h1 { font-size: 2.5rem; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .pricing-card.popular { transform: scale(1); }
        }
    </style>
</head>
<body>

<!-- Server Status Bar -->
<div class="server-status">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <span class="status-dot status-online"></span> System Online
                <span class="ms-3"><i class="fas fa-microchip"></i> CPU: <span id="cpu">0</span>%</span>
                <span class="ms-3"><i class="fas fa-memory"></i> RAM: <span id="ram">0</span> MB</span>
                <span class="ms-3"><i class="fas fa-hdd"></i> DISK: <span id="disk">0</span>%</span>
            </div>
            <div class="col-md-6 text-end">
                <span><i class="fas fa-tachometer-alt"></i> Ping: <span id="ping">0</span>ms</span>
                <span class="ms-3"><i class="fas fa-users"></i> Users: <span id="totalUsers">0</span></span>
                <span class="ms-3"><i class="fas fa-robot"></i> Bots: <span id="totalBots">0</span></span>
                <span class="ms-3"><i class="fas fa-play-circle"></i> Running: <span id="runningBots">0</span></span>
            </div>
        </div>
    </div>
</div>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php"><i class="fas fa-robot me-2"></i><?php echo SITE_NAME; ?></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
                <li class="nav-item"><a class="nav-link" href="#pricing">Pricing</a></li>
                <li class="nav-item"><a class="nav-link" href="#languages">Languages</a></li>
                <li class="nav-item"><a class="nav-link" href="docs.php">Docs</a></li>
                <li class="nav-item"><a class="nav-link" href="support.php">Support</a></li>
                <?php if(isLoggedIn()): ?>
                    <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <?php if(isAdmin()): ?>
                        <li class="nav-item"><a class="nav-link btn btn-primary text-white px-3" href="admin.php"><i class="fas fa-crown"></i> Admin</a></li>
                    <?php endif; ?>
                    <li class="nav-item"><a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                    <li class="nav-item"><a class="nav-link btn btn-primary text-white px-3" href="register.php">Sign Up Free</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Hero Section -->
<section class="hero">
    <div class="container hero-content">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <h1>Host Telegram Bots Like Never Before</h1>
                <p>Deploy, run and scale your Telegram bots with our powerful hosting platform. Support for 7+ programming languages, real-time monitoring, web terminal, and 99.9% uptime guarantee.</p>
                <div class="d-flex gap-3 flex-wrap">
                    <a href="register.php" class="btn btn-primary btn-lg">Get Started Free</a>
                    <a href="#pricing" class="btn btn-outline-primary btn-lg">View Plans</a>
                    <a href="terminal.php" class="btn btn-outline-light btn-lg"><i class="fas fa-terminal"></i> Web Terminal</a>
                </div>
                <div class="mt-4">
                    <span class="text-white-50"><i class="fas fa-check-circle text-success"></i> No credit card required</span>
                    <span class="text-white-50 ms-3"><i class="fas fa-check-circle text-success"></i> Free forever plan</span>
                    <span class="text-white-50 ms-3"><i class="fas fa-check-circle text-success"></i> 24/7 support</span>
                </div>
            </div>
            <div class="col-lg-5 text-center">
                <img src="https://img.icons8.com/fluency/400/bot.png" alt="Bot" class="img-fluid">
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<div class="container" style="margin-top: -60px;">
    <div class="stats-grid">
        <div class="stat-card"><i class="fas fa-robot"></i><h3 id="statBots">0</h3><p class="text-white-50">Bots Hosted</p></div>
        <div class="stat-card"><i class="fas fa-users"></i><h3 id="statUsers">0</h3><p class="text-white-50">Happy Users</p></div>
        <div class="stat-card"><i class="fas fa-code"></i><h3>7+</h3><p class="text-white-50">Languages</p></div>
        <div class="stat-card"><i class="fas fa-heart"></i><h3>99.9%</h3><p class="text-white-50">Uptime</p></div>
    </div>
</div>

<!-- Features Section -->
<section id="features" class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="text-white">Powerful Features for Developers</h2>
            <p class="text-white-50">Everything you need to deploy and manage your Telegram bots</p>
        </div>
        <div class="row g-4">
            <div class="col-md-4"><div class="feature-card"><i class="fas fa-rocket"></i><h4 class="text-white">Instant Deployment</h4><p class="text-white-50">Deploy your bot in seconds with one-click deployment</p></div></div>
            <div class="col-md-4"><div class="feature-card"><i class="fas fa-chart-line"></i><h4 class="text-white">Real-time Monitoring</h4><p class="text-white-50">Track CPU, RAM, requests, and errors in real-time</p></div></div>
            <div class="col-md-4"><div class="feature-card"><i class="fas fa-terminal"></i><h4 class="text-white">Web Terminal</h4><p class="text-white-50">Full terminal access like Termux - run commands, install packages</p></div></div>
            <div class="col-md-4"><div class="feature-card"><i class="fas fa-shield-alt"></i><h4 class="text-white">DDoS Protection</h4><p class="text-white-50">Enterprise-grade security with automatic DDoS protection</p></div></div>
            <div class="col-md-4"><div class="feature-card"><i class="fas fa-database"></i><h4 class="text-white">Auto Backups</h4><p class="text-white-50">Automatic daily backups with one-click restore</p></div></div>
            <div class="col-md-4"><div class="feature-card"><i class="fas fa-globe"></i><h4 class="text-white">Custom Domains</h4><p class="text-white-50">Use your own domain for bot webhooks</p></div></div>
            <div class="col-md-4"><div class="feature-card"><i class="fas fa-package"></i><h4 class="text-white">Auto Package Install</h4><p class="text-white-50">Auto-detect and install required packages</p></div></div>
            <div class="col-md-4"><div class="feature-card"><i class="fas fa-code-branch"></i><h4 class="text-white">Multiple Languages</h4><p class="text-white-50">Python, Node.js, PHP, Go, Ruby, Java, BJS support</p></div></div>
            <div class="col-md-4"><div class="feature-card"><i class="fas fa-headset"></i><h4 class="text-white">24/7 Support</h4><p class="text-white-50">Get help whenever you need from our expert team</p></div></div>
        </div>
    </div>
</section>

<!-- Languages Section -->
<section id="languages" class="py-5" style="background: rgba(30, 41, 59, 0.5);">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="text-white">Supporting All Major Languages</h2>
            <p class="text-white-50">Write your bot in your favorite programming language</p>
        </div>
        <div class="lang-icons">
            <?php foreach($SUPPORTED_LANGUAGES as $key => $lang): ?>
            <div class="lang-icon"><span><?php echo $lang['icon']; ?></span><h6 class="text-white mt-2"><?php echo $lang['name']; ?></h6><small class="text-white-50">v<?php echo $lang['version']; ?></small></div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Pricing Section -->
<section id="pricing" class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="text-white">Simple, Transparent Pricing</h2>
            <p class="text-white-50">Choose the plan that's right for you</p>
            <div class="btn-group mt-3">
                <button class="btn btn-primary" id="monthlyBtn">Monthly</button>
                <button class="btn btn-outline-primary" id="yearlyBtn">Yearly (Save 20%)</button>
            </div>
        </div>
        <div class="row g-4" id="pricingContainer">
            <?php foreach($PLANS as $key => $plan): if($key == 'free') continue; ?>
            <div class="col-md-4">
                <div class="pricing-card <?php echo $key == 'pro' ? 'popular' : ''; ?> position-relative">
                    <h3 class="text-white"><?php echo $plan['name']; ?></h3>
                    <div class="price monthly-price">₹<?php echo $plan['price']; ?><small class="text-white-50">/mo</small></div>
                    <div class="price yearly-price" style="display:none;">₹<?php echo $plan['price_yearly']; ?><small class="text-white-50">/year</small></div>
                    <ul class="feature-list">
                        <li><i class="fas fa-check-circle"></i> <?php echo $plan['bots'] == -1 ? 'Unlimited' : $plan['bots']; ?> Bots</li>
                        <li><i class="fas fa-check-circle"></i> <?php echo $plan['ram']; ?> MB RAM</li>
                        <li><i class="fas fa-check-circle"></i> <?php echo $plan['cpu']; ?>% CPU</li>
                        <li><i class="fas fa-check-circle"></i> <?php echo $plan['storage']; ?> MB Storage</li>
                        <?php foreach($plan['features'] as $feature): ?>
                        <li><i class="fas fa-check-circle"></i> <?php echo $feature; ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <button class="btn btn-primary w-100 purchase-btn" data-plan="<?php echo $key; ?>">Get Started</button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5" style="background: linear-gradient(135deg, #6366f1, #8b5cf6);">
    <div class="container text-center">
        <h2 class="text-white mb-3">Ready to Deploy Your Bot?</h2>
        <p class="text-white-50 mb-4">Join thousands of developers hosting their Telegram bots with us</p>
        <a href="register.php" class="btn btn-dark btn-lg">Get Started Now</a>
        <a href="terminal.php" class="btn btn-outline-light btn-lg ms-3"><i class="fas fa-terminal"></i> Try Web Terminal</a>
    </div>
</section>

<!-- Footer -->
<footer class="footer">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-4">
                <h5 class="text-white mb-3"><?php echo SITE_NAME; ?></h5>
                <p class="text-white-50">The most reliable Telegram bot hosting platform for developers worldwide.</p>
                <div>
                    <a href="<?php echo TELEGRAM_CHANNEL; ?>" class="text-white-50 me-3"><i class="fab fa-telegram fa-2x"></i></a>
                    <a href="#" class="text-white-50 me-3"><i class="fab fa-github fa-2x"></i></a>
                    <a href="#" class="text-white-50"><i class="fab fa-twitter fa-2x"></i></a>
                </div>
            </div>
            <div class="col-md-2 mb-4"><h6 class="text-white">Product</h6><ul class="list-unstyled"><li><a href="#features" class="text-white-50">Features</a></li><li><a href="#pricing" class="text-white-50">Pricing</a></li><li><a href="docs.php" class="text-white-50">Docs</a></li></ul></div>
            <div class="col-md-2 mb-4"><h6 class="text-white">Company</h6><ul class="list-unstyled"><li><a href="#" class="text-white-50">About</a></li><li><a href="#" class="text-white-50">Blog</a></li><li><a href="support.php" class="text-white-50">Contact</a></li></ul></div>
            <div class="col-md-4 mb-4"><h6 class="text-white">Subscribe to Updates</h6><div class="input-group"><input type="email" class="form-control bg-dark text-white border-secondary" placeholder="Your email"><button class="btn btn-primary">Subscribe</button></div><p class="mt-3 text-white-50 small">© 2024 <?php echo SITE_NAME; ?>. All rights reserved.</p></div>
        </div>
    </div>
</footer>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
let isYearly = false;

$('#monthlyBtn').click(function() { isYearly = false; $('.monthly-price').show(); $('.yearly-price').hide(); $(this).addClass('btn-primary').removeClass('btn-outline-primary'); $('#yearlyBtn').addClass('btn-outline-primary').removeClass('btn-primary'); });
$('#yearlyBtn').click(function() { isYearly = true; $('.monthly-price').hide(); $('.yearly-price').show(); $(this).addClass('btn-primary').removeClass('btn-outline-primary'); $('#monthlyBtn').addClass('btn-outline-primary').removeClass('btn-primary'); });

$('.purchase-btn').click(function() {
    let plan = $(this).data('plan');
    let prices = <?php echo json_encode(array_column($PLANS, 'price')); ?>;
    let yearlyPrices = <?php echo json_encode(array_column($PLANS, 'price_yearly')); ?>;
    let amount = isYearly ? yearlyPrices[plan] : prices[plan];
    var options = { key: "<?php echo RAZORPAY_KEY; ?>", amount: amount * 100, currency: "INR", name: "<?php echo SITE_NAME; ?>", description: plan.toUpperCase() + " Plan Subscription", handler: function(response) { $.post('checkout.php', {payment_id: response.razorpay_payment_id, plan: plan, amount: amount}, function(data) { if(data.success) window.location.href = 'dashboard.php?upgraded=1'; }); } };
    var rzp = new Razorpay(options); rzp.open();
});

function loadStats() {
    $.get('api/system-info.php', function(data) {
        $('#cpu').text(data.cpu); $('#ram').text(data.ram); $('#disk').text(data.disk); $('#ping').text(data.ping);
        $('#totalUsers').text(data.total_users); $('#totalBots').text(data.total_bots); $('#runningBots').text(data.running_bots);
        $('#statBots').text(data.total_bots); $('#statUsers').text(data.total_users);
    });
}
loadStats(); setInterval(loadStats, 10000);
</script>
</body>
</html>