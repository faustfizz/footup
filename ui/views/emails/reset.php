<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #dc3545; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; }
        .footer { background: #f8f9fa; padding: 10px; text-align: center; font-size: 12px; }
        .button { background: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; }
        .warning { background: #fff3cd; padding: 10px; border-radius: 5px; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Reset Your Password</h1>
        </div>
        
        <div class="content">
            <p>Hi <?php echo htmlspecialchars($user->name ?? 'there'); ?>,</p>
            
            <p>We received a request to reset the password for your account.</p>
            
            <p>
                <a href="<?php echo $link; ?>" class="button">Reset Password</a>
            </p>
            
            <div class="warning">
                <strong>⏰ This link expires in <?php echo $expires; ?></strong>
            </div>
            
            <p>If you didn't request this, you can safely ignore this email.</p>
            
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
