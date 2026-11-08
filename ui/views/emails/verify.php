<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #28a745; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; }
        .footer { background: #f8f9fa; padding: 10px; text-align: center; font-size: 12px; }
        .button { background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Verify Your Email Address</h1>
        </div>
        
        <div class="content">
            <p>Hi <?php echo htmlspecialchars($user->name ?? 'there'); ?>,</p>
            
            <p>Please verify your email address by clicking the button below:</p>
            
            <p>
                <a href="<?php echo $link; ?>" class="button">Verify Email</a>
            </p>
            
            <p>If you didn't create this account, no action is needed.</p>
            
            <p>
                Cheers,<br>
                The MyApp Team
            </p>
        </div>
        
        <div class="footer">
            <p>&copy; <?php echo date('Y'); ?> MyApp. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
