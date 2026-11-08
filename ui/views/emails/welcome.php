<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #007bff; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; }
        .footer { background: #f8f9fa; padding: 10px; text-align: center; font-size: 12px; }
        a { color: #007bff; text-decoration: none; }
        .button { background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Welcome to MyApp! 🎉</h1>
        </div>
        
        <div class="content">
            <p>Hi <?php echo htmlspecialchars($user->name ?? 'there'); ?>,</p>
            
            <p>Thanks for signing up! We're thrilled to have you join our community.</p>
            
            <p>Here's what you can do next:</p>
            <ul>
                <li>Complete your profile</li>
                <li>Explore our features</li>
                <li>Connect with other users</li>
            </ul>
            
            <p>
                <a href="<?php echo url('/dashboard'); ?>" class="button">Go to Dashboard</a>
            </p>
            
            <p>If you have any questions, feel free to reply to this email or contact us.</p>
            
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
