<?php require_once 'includes/config.php'; ?>
<!DOCTYPE html>
<html>
<head><title>Upgrade Plan - <?php echo SITE_NAME; ?></title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"><style>body{background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);min-height:100vh;}.pricing-card{background:white;border-radius:20px;padding:30px;text-align:center;margin-bottom:30px;}.pricing-card.popular{border:2px solid #f59e0b;transform:scale(1.02);}.price{font-size:48px;font-weight:800;color:#6366f1;}.btn-purchase{background:linear-gradient(135deg,#6366f1,#8b5cf6);border:none;border-radius:12px;padding:12px;width:100%;color:white;font-weight:600;}</style></head>
<body>
<div class="container py-5"><div class="text-center mb-5"><h1 class="text-white">Choose Your Plan</h1><p class="text-white-50">Upgrade to premium and unlock advanced features</p></div>
<div class="row"><?php foreach($PLANS as $key=>$plan): if($key=='free') continue; ?>
<div class="col-md-4"><div class="pricing-card <?php echo $key=='pro'?'popular':''; ?>"><h3><?php echo $plan['name']; ?></h3><div class="price">₹<?php echo $plan['price']; ?><small>/mo</small></div>
<ul class="list-unstyled text-start mt-3"><?php foreach($plan['features'] as $f): ?><li><i class="fas fa-check-circle text-success"></i> <?php echo $f; ?></li><?php endforeach; ?></ul>
<button class="btn-purchase purchase-btn" data-plan="<?php echo $key; ?>">Get Started</button></div></div>
<?php endforeach; ?></div></div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
$('.purchase-btn').click(function(){ let plan=$(this).data('plan'); let prices=<?php echo json_encode(array_column($PLANS,'price')); ?>; let amount=prices[plan];
var options={ key:"<?php echo RAZORPAY_KEY; ?>", amount:amount*100, currency:"INR", name:"<?php echo SITE_NAME; ?>", handler:function(r){ $.post('checkout.php',{payment_id:r.razorpay_payment_id,plan:plan,amount:amount},function(d){ if(d.success) window.location.href='dashboard.php?upgraded=1'; }); } };
var rzp=new Razorpay(options); rzp.open(); });
</script>
</body></html>