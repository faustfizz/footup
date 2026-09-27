<?php

declare(strict_types=1);

return [
    'login' => [
        'title' => 'User Login',
        'email' => 'Email Address',
        'password' => 'Password',
        'remember' => 'Remember Me',
        'submit' => 'Login',
        'success' => 'Welcome back :name, you are now logged in',
        'failed' => 'Invalid credentials for {email}',
    ],
    'register' => [
        'title' => 'Create Account',
        'username' => 'Username',
        'email' => 'Email Address',
        'password' => 'Password',
        'confirm' => 'Confirm Password',
        'submit' => 'Register',
        'success' => 'Account created for :name, welcome!',
        'exists' => 'The email {email} is already registered',
    ],
    'password' => [
        'reset' => 'Reset your password',
        'new' => 'New Password',
        'confirm' => 'Confirm Password',
        'submit' => 'Reset Password',
        'sent' => 'Password reset link sent to :email',
        'changed' => 'Your password has been changed successfully',
        'mismatch' => 'Passwords do not match',
        'invalid_token' => 'Invalid or expired password reset link',
    ],
    'verification' => [
        'title' => 'Email Verification',
        'message' => 'Please verify your email address to complete registration',
        'sent' => 'Verification link sent to {email}',
        'verified' => 'Email verified successfully',
        'not_verified' => 'Your email is not verified',
    ],
    'error' => [
        'unauthorized' => 'You are not authorized to perform this action',
        'unauthenticated' => 'Please log in to continue',
        'account_locked' => 'Account locked after :attempts failed login attempts',
        'too_many_attempts' => 'Too many login attempts. Try again in {minutes} minutes',
        'expired_session' => 'Your session has expired. Please login again',
    ],
];
