<?php

return [
    /*
      |--------------------------------------------------------------------------
      | Docs Generator Configuration
      |--------------------------------------------------------------------------
      |
      | Add your package-specific config options here.
      |
     */
//

//    'custom_sections' => [
//        [
//            'id' => 'custom1',
//            'title' => 'Custom Section 1',
//            'type' => "sections",
//            'view' => 'docs-generator::partials.custom',
//            'description' => 'test 1',
//            'sections' => [
//                [
//                    'title' => 'title 1',
//                    "description" => "description 1",
//                ],
//                [
//                    'title' => 'title 2',
//                    "description" => "description 1",
//                ],
//                [
//                    'title' => 'title 3',
//                    "description" => "description 1",
//                ],
//            ],
//            'sidebar_label' => 'Custom 1',
//            'sidebar_group' => 'main',
//        ],
//        [
//            'id' => 'custom2',
//            'title' => 'Custom Section 2',
//            'type' => "paragraph",
//            'description' => 'test 2',
//            'view' => 'docs-generator::partials.custom',
//            'sidebar_label' => 'Custom 2',
//            'sidebar_group' => 'main',
//        ],
//        [
//            'id' => 'custom3',
//            'title' => 'Custom Section 3',
//            'description' => 'test 2',
//            'type' => 'blade',
//            'view' => 'docs-generator::partials.custom',
//            'blade_path' => '',
//            'sidebar_label' => 'Custom 3',
//            'sidebar_group' => 'main',
//        ],
//    ],
//    'css_files' => [
//        [
//            "type" => "file",
//            "path" => resource_path('css/app.css'),
//        ],
//        [
//            "type" => "url",
//            "url" => 'https://<example>/assets/css/style.css',
//        ],
//    ],
    
    
    'logo' => [
        // Can be a URL or asset path (e.g., 'images/logo.png' relative to public)
        'path' => '/images/docs-logo.png', 
        'alt' => 'Documentation Logo',
        'width' => 150,  // Optional width in px
        'height' => 40,  // Optional height in px
    ],

    // Custom CSS - path to extra CSS file or inline styles (string)
    'custom_css' => [
        // 'file' => resource_path('css/docs-custom.css'),
        // or
        'inline' => '
            body { background-color: #f5f8fa; }
            .navbar { background-color: #123456; }
        ',
    ],

    // Docs route URL (prefix)
    'docs_route' => 'docs',

    // Custom middleware(s) for docs route group
    'middlewares' => [
        // 'auth',
        // 'admin',
    ],
       // Page title
    'page_title' => 'Laravel Documentation',

    // Logo URL (relative to public or absolute)
    'logo_url' => '/images/docs-logo.png', // e.g. 'images/docs-logo.png' in public/


    // Navbar styles overrides (optional)
    'navbar_class' => 'navbar navbar-expand-lg navbar-dark bg-primary fixed-top',
    // Fav Icon
    'fav_icon' => '/images/docs-fav.png',
];
