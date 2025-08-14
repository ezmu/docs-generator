# Docs Generator

Docs Generator is a Laravel package for automatically generating and displaying project documentation directly inside your application.  
It intelligently analyzes your Laravel project to extract information about routes, controllers, models, database schema, views, middleware, installed packages, and more, then organizes it into structured JSON files.  
The package provides built-in routes and views to present this documentation beautifully, without leaving your Laravel app.

Ideal for installation guides, database ERDs, developer notes, and system overviews — all generated automatically from your code.


## How It Works

The package scans your Laravel application and generates structured documentation by analyzing:

- Routes & HTTP methods
- Models & relationships
- Controllers & methods
- Middleware
- Database migrations & schema
- Installed Composer packages
- Views tree and assets (CSS, JS, CDNs)

The output is stored in JSON files and can be accessed via built-in routes and views in your Laravel project.

## Installation

Require the package via Composer:

```bash
composer require ezmu/docs-generator --dev
```

Publish the configuration and views:

```bash
php artisan vendor:publish --provider="Ezmu\DocsGenerator\DocsGeneratorServiceProvider"
```

Run the generator command to analyze your project and generate documentation:

```bash
php artisan docs:generate
```



## Configuration

After publishing the configuration file (`php artisan vendor:publish --provider="Ezmu\DocsGenerator\DocsGeneratorServiceProvider"`), you can customize Docs Generator via `config/docs-generator.php`.
```
config/docs-generator.php
```
Here you can configure:

- Storage path for generated JSON files
- Enabled features (routes, views, export options)
- Access control for the documentation pages
- Custom sections (paragraphs, Blade views, or structured sections)
- Additional CSS files or inline styles
- Logo, favicon, and page title
- Docs route URL and middlewares

Example configuration:

```php
return [
    'logo' => [
        'path' => '/images/docs-logo.png',
        'alt' => 'Documentation Logo',
        'width' => 150,
        'height' => 40,
    ],

    'custom_css' => [
        'inline' => '
            body { background-color: #f5f8fa; }
            .navbar { background-color: #123456; }
        ',
    ],

    'docs_route' => 'docs',

    'middlewares' => [
        // 'auth',
        // 'admin',
    ],

    'page_title' => 'Laravel Documentation',

    'logo_url' => '/images/docs-logo.png',

    'navbar_class' => 'navbar navbar-expand-lg navbar-dark bg-primary fixed-top',

    'fav_icon' => '/images/docs-fav.png',
];
```

You can also define **custom sections** with paragraphs, Blade views, or structured content for sidebar navigation:

```php
'custom_sections' => [
    [
        'id' => 'custom1',
        'title' => 'Custom Section 1',
        'type' => "sections",
        'view' => 'docs-generator::partials.custom',
        'description' => 'test 1',
        'sections' => [
            ['title' => 'title 1', 'description' => 'description 1'],
            ['title' => 'title 2', 'description' => 'description 2'],
        ],
        'sidebar_label' => 'Custom 1',
        'sidebar_group' => 'main',
    ],
],
```


## Usage

Once generated, you can access your documentation via the default route:

```
/docs
```

Or you can customize the route in the configuration file.



## Code Analysis

Docs Generator scans your Laravel application and extracts:

- Routes and HTTP methods
- Controllers, methods, and middleware
- Models and relationships
- Database migrations and schema
- Installed Composer packages
- Views structure and assets (CSS, JS, CDNs)
- Locale files

This allows you to visualize the system architecture, dependencies, and available endpoints.

## Contributing

Contributions, bug reports, and pull requests are welcome. Please follow standard Laravel package practices.

## License

This package is open-sourced software licensed under the MIT license.
    
Author

EzEldeen A. Y. Mushtaha
GitHub: https://github.com/ezmu
LinkedIn: https://www.linkedin.com/in/ezmush/