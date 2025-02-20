<?php

return [
    'org_name' => env('DS_ORG_NAME', 'Organization Name'),
    'org_logo' => env('DS_ORG_LOGO', ''),
    
    'enable_weather' => env('DS_ENABLE_WEATHER', True),
    'weather_api_key' => env('DS_WEATHER_API_KEY', ''),

    'enable_alertgen' => env('DS_ENABLE_ALERTGEN', False),
    'alertgen_url' => env('DS_ALERTGEN_URL', 'http://alertgen:5000')
];