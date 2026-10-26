<?php

return [
    // Credentials, store products and enablement are deliberately deferred until release preparation.
    'enabled' => (bool) env('RELIGIOUS_ASSISTANT_ENABLED', false),
    'api_key' => env('RELIGIOUS_ASSISTANT_API_KEY', ''),
    'model' => 'gpt-6-luna',
    'input_limit' => 3000,
    'output_limit' => 600,
    'daily_limit' => 3,
    'monthly_limit' => 25,
    // Integer micro-USD. Rates must be revalidated against the provider before enablement.
    'budget_micro_usd' => 20000000,
    'safety_micro_usd' => 2000000,
    'input_micro_usd_per_million' => 100000,
    'output_micro_usd_per_million' => 500000,
    'terms_url' => env('RELIGIOUS_BILLING_TERMS_URL', ''),
    'privacy_url' => env('RELIGIOUS_BILLING_PRIVACY_URL', ''),
    'android' => [
        'enabled' => (bool) env('RELIGIOUS_BILLING_ANDROID_ENABLED', false),
        'product' => env('RELIGIOUS_BILLING_ANDROID_PRODUCT', ''),
        'package' => 'com.haqfoundation.haq_app',
        'credentials_path' => env('RELIGIOUS_BILLING_GOOGLE_CREDENTIALS', ''),
        'notification_audience' => env('RELIGIOUS_BILLING_GOOGLE_NOTIFICATION_AUDIENCE', ''),
        'notification_email' => env('RELIGIOUS_BILLING_GOOGLE_NOTIFICATION_EMAIL', ''),
        'allow_sandbox' => (bool) env('RELIGIOUS_BILLING_ALLOW_SANDBOX', false),
    ],
    'ios' => [
        'enabled' => (bool) env('RELIGIOUS_BILLING_IOS_ENABLED', false),
        'product' => env('RELIGIOUS_BILLING_IOS_PRODUCT', ''),
        'bundle' => 'com.haqfoundation.haqApp',
        'issuer' => env('RELIGIOUS_BILLING_APPLE_ISSUER', ''),
        'key_id' => env('RELIGIOUS_BILLING_APPLE_KEY_ID', ''),
        'key_path' => env('RELIGIOUS_BILLING_APPLE_KEY_PATH', ''),
        'app_id' => env('RELIGIOUS_BILLING_APPLE_APP_ID', ''),
        'environment' => env('RELIGIOUS_BILLING_APPLE_ENVIRONMENT', 'Production'),
        'root_path' => resource_path('certificates/AppleRootCA-G3.pem'),
    ],
];
