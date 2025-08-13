<?php

namespace Ezmu\DocsGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Route;
use ReflectionClass;
use ReflectionMethod;
use Exception;
use Illuminate\Support\Facades\File;
use Ezmu\DocsGenerator\Services\BladeViewTree;

class GenerateDocumentation extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'docs:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates comprehensive documentation for the Laravel application.';

    /**
     * Execute the console command.
     */
    public function handle() {
        $this->info('Generating Laravel Documentation...');

        $docsPath = storage_path('app/docs');
        if (!File::exists($docsPath)) {
            File::makeDirectory($docsPath, 0755, true);
        }

//         --- 1. Generate Installation Guide Data ---
        $this->info('Generating Installation Guide data...');
        $installationGuideData = $this->generateInstallationGuideData();
        File::put("{$docsPath}/installation.json", json_encode($installationGuideData, JSON_PRETTY_PRINT));
        $this->info('Installation Guide data saved to installation.json');

        // --- 2. Generate ERD Data ---
        $this->info('Generating ERD data...');
        $modelsDir = app_path('Models');
        $erdData = $this->generateERDData($modelsDir);
        File::put("{$docsPath}/erd.json", json_encode($erdData, JSON_PRETTY_PRINT));
        $this->info('ERD data saved to erd.json');

        // --- 3. Generate Routes Documentation Data ---
        $this->info('Generating Routes documentation data...');
        $routesData = $this->generateRoutesDocumentationData();
        File::put("{$docsPath}/routes.json", json_encode($routesData, JSON_PRETTY_PRINT));
        $this->info('Routes documentation data saved to routes.json');

        // --- 4. Generate Controller/Model Code Documentation Data ---
        $this->info('Generating Controller/Model Code documentation data...');
        // Adjusted path to controllers
        $controllersDir = app_path('Http/Controllers');
        $modelsDirForCode = app_path('Models');

        $codeDocumentation = [
            'controllers' => $this->getFunctionsFromFiles($controllersDir),
            'models' => $this->getFunctionsFromFiles($modelsDirForCode),
        ];
        File::put("{$docsPath}/code.json", json_encode($codeDocumentation, JSON_PRETTY_PRINT));
        $this->info('Controller/Model Code documentation data saved to code.json');

        $this->info('Generating Views tree data...');
        $viewsTree = $this->generateViewsTreeData();
        File::put("{$docsPath}/views-tree.json", json_encode($viewsTree, JSON_PRETTY_PRINT));
        $this->info('Views tree data saved to views-tree.json');
        $this->info('Generating Views tree data...');
        $assetsReport = $this->generateAssetsReport();
        File::put("{$docsPath}/assets-report.json", json_encode($assetsReport, JSON_PRETTY_PRINT));
        $this->info('Assets Report data saved to assets-report.json');
        $this->info('Generating CSS data...');
        $cssData = $this->generateCssDocumentation();
        File::put("{$docsPath}/css-report.json", json_encode($cssData, JSON_PRETTY_PRINT));
        $this->info('CSS Report data saved to css-report.json');

        $this->info('Laravel Documentation generation complete!');
    }

    // --- Installation Guide Functions ---
    private function generateInstallationGuideData() {
        $this->newLine();

        // Detect environment info
        $phpVersion = $this->getPhpVersion();
        $composerVersion = $this->getComposerVersion() ?: 'Not installed';
        $webServer = $this->detectWebServer();
        $database = $this->detectDatabase();
        $requiredExtensions = ['openssl', 'pdo', 'mbstring', 'tokenizer', 'xml', 'ctype', 'json', 'bcmath', 'curl', 'zip'];
        $missingExtensions = $this->getMissingExtensions($requiredExtensions);

        $systemRequirements = [
            'PHP' => $phpVersion,
            'Composer' => $composerVersion,
            'Web Server' => $webServer,
            'Database' => $database,
            'Required Extensions' => $requiredExtensions,
            'Missing Extensions' => $missingExtensions,
        ];

        // Detect PHP version short for packages (major.minor)
        $phpVersionFull = phpversion();
        $parts = explode('.', $phpVersionFull);
        $phpVersionShort = $parts[0] . '.' . ($parts[1] ?? '1');

        // Prepare OS steps - show **all** regardless of current OS
        $steps = [];

        // Ubuntu / Debian Linux
        $phpPackages = "php{$phpVersionShort} php{$phpVersionShort}-{cli,xml,mbstring,zip,curl}";
        $steps['Ubuntu / Debian'] = <<<EOD
sudo apt update
sudo apt install {$phpPackages} composer
sudo apt install mysql-server nginx
EOD;

        // Windows instructions
        $steps['Windows'] = [
            ['name' => 'PHP', 'link' => 'https://windows.php.net/'],
            ['name' => 'Composer', 'link' => 'https://getcomposer.org/'],
            ['name' => 'XAMPP/WAMP', 'description' => 'For Apache and MySQL services.'],
        ];

        // macOS (Homebrew)
        $steps['macOS (Homebrew)'] = <<<EOD
brew update
brew install php composer mysql nginx
EOD;

        // Manual fallback instructions
        $steps['Manual Setup'] = "Please install PHP, Composer, MySQL/PostgreSQL, and a web server manually according to your OS.";

        // Add missing PHP extension install commands per OS (all three)
        // Note: generateExtensionInstallCommands expects OS family like 'Linux', 'Windows', 'Darwin'
        if (!empty($missingExtensions)) {
            $steps['Install Missing PHP Extensions (Linux)'] = $this->generateExtensionInstallCommands($missingExtensions, 'Linux');
            $steps['Install Missing PHP Extensions (Windows)'] = $this->generateExtensionInstallCommands($missingExtensions, 'Windows');
            $steps['Install Missing PHP Extensions (macOS)'] = $this->generateExtensionInstallCommands($missingExtensions, 'Darwin');
        }

        // Common Laravel app setup steps
        $steps['Clone Application'] = "git clone https://github.com/your-repo/your-laravel-project.git\ncd your-laravel-project";

        if ($composerVersion !== 'Not installed') {
            $steps['Composer Install'] = "composer install";
        }

        $steps['Environment Setup'] = <<<EOD
cp .env.example .env

Configure your database settings in .env:

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_db
DB_USERNAME=root
DB_PASSWORD=your_password
EOD;

        $steps['Migrate & Seed'] = "php artisan migrate --seed";
        $steps['Folder Permissions'] = "sudo chmod -R 775 storage bootstrap/cache";

        if (strtolower($database) === 'mysql') {
            $steps['Manage MySQL Service'] = "sudo systemctl start mysql";
        } elseif (strtolower($database) === 'postgresql') {
            $steps['Manage PostgreSQL Service'] = "sudo systemctl start postgresql";
        }

        if (stripos($webServer, 'nginx') !== false) {
            $steps['Manage Nginx Service'] = "sudo systemctl start nginx";
        } elseif (stripos($webServer, 'apache') !== false) {
            $steps['Manage Apache Service'] = "sudo systemctl start apache2";
        }

        $steps['Serve Application'] = "php artisan serve";

        // Calculate total steps count for progress bar
        $stepsCount = count($steps) + count($missingExtensions);

        // Load composer dependencies
        $composerFile = base_path('composer.json');
        $dependencies = [];
        if (!File::exists($composerFile)) {
            $this->error("composer.json not found!");
            return [];
        }
        $composerData = json_decode(File::get($composerFile), true);

        foreach (['require', 'require-dev'] as $section) {
            if (isset($composerData[$section])) {
                $stepsCount += count($composerData[$section]); // add to total steps count
            }
        }

        $progressBar = $this->output->createProgressBar($stepsCount);
        $progressBar->setFormat(" %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s% - <info>%package%</info>");
        $progressBar->start();

        // Report missing extensions progress
        foreach ($missingExtensions as $ext) {
            $progressBar->setMessage("Missing PHP extension: {$ext}", 'package');
            $progressBar->advance();
        }

        // Process composer dependencies with progress
        foreach (['require', 'require-dev'] as $section) {
            if (isset($composerData[$section])) {
                foreach ($composerData[$section] as $package => $version) {
                    $dependencies[] = [
                        'package' => $package,
                        'version' => $version,
                        'description' => $this->fetchPackageDescription($package),
                    ];
                    $progressBar->setMessage("Processing {$package}...", 'package');
                    $progressBar->advance();
                }
            }
        }

        $progressBar->setMessage("Completed", 'package');
        $progressBar->finish();

        $this->newLine(2);
        $this->info('Documentation generation complete!');
        $this->comment('You can view it in your browser at /docs');

        return [
            'system_requirements' => $systemRequirements,
            'composer_dependencies' => $dependencies,
            'steps' => $steps,
        ];
    }

 
    private function generateExtensionInstallCommands(array $extensions, string $os = null): string {


        if ($os === 'Linux') {
            $phpVersion = trim(shell_exec('php -r "echo PHP_MAJOR_VERSION.\'.\'.PHP_MINOR_VERSION;"'));

            $ubuntuMapping = [];

            foreach ($extensions as $ext) {
                $package = "php{$phpVersion}-{$ext}";

                $exists = trim(shell_exec("apt-cache show {$package} 2>/dev/null"));
                if ($exists) {
                    $ubuntuMapping[$ext] = $package;
                }
            }
            $pkgs = [];
            foreach ($extensions as $ext) {
                if (isset($ubuntuMapping[$ext])) {
                    $pkgs[] = $ubuntuMapping[$ext];
                }
            }
            if (empty($pkgs)) {
                return "No known package names for missing extensions on Ubuntu/Linux.";
            }
            return "sudo apt update\nsudo apt install " . implode(' ', $pkgs);
        } elseif ($os === 'Windows') {

            $list = implode(", ", $extensions);
            return "Please enable these PHP extensions in your php.ini: {$list}\nTypically, uncomment extension lines and restart your web server.";
        } elseif ($os === 'Darwin') {
            return "brew install php\nExtensions usually come bundled with Homebrew PHP.\nCheck your php.ini to enable missing extensions.";
        }

        return "Please install the following PHP extensions manually: " . implode(', ', $extensions);
    }

    private function getPhpVersion() {
        return phpversion();
    }

    private function getComposerVersion() {
        exec('composer --version 2>&1', $output, $return);
        if ($return === 0 && !empty($output)) {
            return $output[0];
        }
        return null;
    }

    private function detectWebServer() {
        exec('ps aux', $processes);
        foreach ($processes as $process) {
            if (stripos($process, 'nginx') !== false)
                return 'nginx';
            if (stripos($process, 'apache') !== false || stripos($process, 'httpd') !== false)
                return 'apache';
        }
        return 'unknown';
    }

    private function detectDatabase() {
        exec('ps aux', $processes);
        foreach ($processes as $process) {
            if (stripos($process, 'mysql') !== false)
                return 'MySQL';
            if (stripos($process, 'postgres') !== false)
                return 'PostgreSQL';
        }
        return 'unknown';
    }

    private function getMissingExtensions(array $required) {
        $missing = [];
        foreach ($required as $ext) {
            if (!extension_loaded($ext)) {
                $missing[] = $ext;
            }
        }
        return $missing;
    }

    private function fetchPackageDescription($package) {
        $url = "https://repo.packagist.org/p2/{$package}.json";
        $options = ['http' => ['ignore_errors' => true]]; // Ignore HTTP errors
        $context = stream_context_create($options);
        $response = @file_get_contents($url, false, $context);

        if ($response === false) {
            return 'Failed to fetch description (network issue or package not found)';
        }

        $data = json_decode($response, true);
        return $data['packages'][$package][0]['description'] ?? 'No description found on Packagist.';
    }

    // --- ERD Functions ---

    private function generateERDData($modelsDir) {
        $erd = ['tables' => [], 'relationships' => []];

        $models = $this->getModels($modelsDir);
        if (count($models) == 0) {
            $models = $this->getAllModels();
        }

        $totalSteps = count($models);
        $progressBar = $this->output->createProgressBar($totalSteps);
        $progressBar->setFormat(" %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s% - <info>%modelClass%</info>");
        $progressBar->start();

        foreach ($models as $modelClass) {
            try {
                $ref = new ReflectionClass($modelClass);
                if ($ref->isAbstract()) {
                    $progressBar->advance();
                    continue;
                }

                $modelInstance = app($modelClass);
                $table = $modelInstance->getTable();

                if (empty($table) || !\Schema::hasTable($table)) {
                    $this->warn("Skipping {$modelClass}: table '{$table}' does not exist.");
                    $progressBar->advance();
                    continue;
                }

                $columns = $this->getTableColumns($table);
                $columnDetails = [];
                foreach ($columns as $column) {
                    $detail = $this->getColumnDetails($table, $column);
                    $columnDetails[] = array_merge(['name' => $column], $detail);
                }

                $foreignKeys = $this->getForeignKeysFromDatabase($table);

                $erd['tables'][$table] = [
                    'columns' => $columnDetails,
                    'foreign_keys' => $foreignKeys,
                ];

                $modelRelationships = $this->getModelRelationships($modelClass);
                if (!empty($modelRelationships)) {
                    $erd['relationships'][$table] = $modelRelationships;
                }

                $progressBar->setMessage("  Processing {$modelClass}...", "modelClass");
                $progressBar->advance();
            } catch (Exception $e) {
                $this->warn("Could not process model {$modelClass}: " . $e->getMessage());
                $progressBar->advance();
            }
        }

        $progressBar->setMessage("Completed", "modelClass");
        $progressBar->finish();
        $this->newLine(2);

        $this->info('Documentation generation complete!');
        $this->comment('You can view it in your browser at /docs');

        return $erd;
    }

    private function getModels($modelsDir) {
        $models = [];
        $modelsPath = base_path($modelsDir);

        if (!File::isDirectory($modelsPath)) {
            return [];
        }

        foreach (File::allFiles($modelsPath) as $file) {
            if ($file->getExtension() === 'php') {
                $relativePath = str_replace([base_path() . DIRECTORY_SEPARATOR, '.php'], '', $file->getPathname());

                $className = str_replace(DIRECTORY_SEPARATOR, '\\', $relativePath);
                $className = ucfirst($className);

                if (class_exists($className)) {
                    $models[] = $className;
                } else {
                    $this->warn("Class does not exist: {$className}");
                }
            }
        }

        $this->info("Found " . count($models) . " models.");
        return $models;
    }

    private function getTableColumns($table) {
        return Schema::getColumnListing($table);
    }

    private function getColumnDetails($table, $column) {
        try {
            $type = Schema::getColumnType($table, $column);
            $doctrineColumn = Schema::getConnection()->getDoctrineColumn($table, $column);
            $nullable = $doctrineColumn->getNotnull() ? false : true;
            $default = $doctrineColumn->getDefault();

            return [
                'type' => $type,
                'nullable' => $nullable,
                'default' => $default === null ? 'None' : (is_bool($default) ? ($default ? 'true' : 'false') : $default),
            ];
        } catch (Exception $e) {
            $this->warn("Could not get details for column '{$column}' in table '{$table}': " . $e->getMessage());
            return [
                'type' => 'unknown',
                'nullable' => true,
                'default' => 'N/A',
            ];
        }
    }

    private function getForeignKeysFromDatabase($table) {
        $foreignKeys = [];
        $databaseName = config('database.connections.' . config('database.default') . '.database');

        // Query information_schema for foreign keys
        // This is MySQL specific. For PostgreSQL, SQLite, etc., you'd need different queries.
        $results = Schema::getConnection()->select(
                "SELECT
                COLUMN_NAME,
                REFERENCED_TABLE_NAME,
                REFERENCED_COLUMN_NAME
             FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = ?
               AND TABLE_NAME = ?
               AND REFERENCED_TABLE_NAME IS NOT NULL",
                [$databaseName, $table]
        );

        foreach ($results as $row) {
            $foreignKeys[] = [
                'column' => $row->COLUMN_NAME,
                'referenced_table' => $row->REFERENCED_TABLE_NAME,
                'referenced_column' => $row->REFERENCED_COLUMN_NAME,
            ];
        }

        return $foreignKeys;
    }

    private function getClassFullNameFromFile(string $filePath): ?string {
        $content = file_get_contents($filePath);
        $namespace = null;
        $class = null;

        if (preg_match('/^namespace\s+(.+?);/sm', $content, $matches)) {
            $namespace = $matches[1];
        }

        if (preg_match('/^class\s+(\w+)/sm', $content, $matches)) {
            $class = $matches[1];
        }

        if ($namespace && $class) {
            return $namespace . '\\' . $class;
        }

        return null;
    }

    private function getAllModels(): array {
        $models = [];

        foreach (File::allFiles(app_path()) as $file) {
            if ($file->getExtension() !== 'php')
                continue;

            $relativePath = $file->getRealPath();
            $className = $this->getClassFullNameFromFile($relativePath);

            if ($className && is_subclass_of($className, \Illuminate\Database\Eloquent\Model::class) && !(new \ReflectionClass($className))->isAbstract()) {
                $models[] = $className;
            }
        }

        return $models;
    }

    private function getModelRelationships($modelClass) {
        $relationships = [];

        try {
            $reflection = new ReflectionClass($modelClass);
            $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);

            $instance = app($modelClass);

            foreach ($methods as $method) {
                if ($method->class !== $modelClass || $method->getNumberOfParameters() > 0 || $method->isStatic()) {
                    continue;
                }

                try {
                    $result = $method->invoke($instance);

                    if ($result instanceof \Illuminate\Database\Eloquent\Relations\Relation) {
                        $relatedModel = $result->getRelated();

                        $relationships[] = [
                            'name' => $method->getName(),
                            'type' => class_basename($result),
                            'related_model' => class_basename($relatedModel),
                            'related_table' => $relatedModel->getTable(),
                        ];
                    }
                } catch (\Throwable $e) {
                    $this->warn("Failed invoking {$modelClass}::{$method->getName()} — " . $e->getMessage());
                }
            }
        } catch (\Throwable $e) {
            $this->warn("Could not reflect model {$modelClass}: " . $e->getMessage());
        }

        return $relationships;
    }

    private function generateRoutesDocumentationData() {
        $routesData = [
            'web' => [],
            'api' => [],
        ];
        $totalSteps = 100;
        $progressBar = $this->output->createProgressBar($totalSteps);

        $progressBar->setFormat(" %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s% - <info>%route%</info>");

        $progressBar->start();
        foreach (Route::getRoutes()->getIterator() as $route) {
            $uri = $route->uri();
            $methods = $route->methods();
            $action = $route->getAction();
            $middleware = $route->middleware();

            $controllerMethod = 'N/A';
            $description = 'No description available';

            if (isset($action['uses'])) {
                if (is_string($action['uses'])) {
                    $controllerMethod = $action['uses'];
                    // Try to get docblock from controller method
                    try {
                        [$controllerClass, $methodName] = explode('@', $action['uses']);
                        if (class_exists($controllerClass) && method_exists($controllerClass, $methodName)) {
                            $reflectionMethod = new ReflectionMethod($controllerClass, $methodName);
                            $docComment = $reflectionMethod->getDocComment();
                            if ($docComment) {
                                $description = $this->parseDocComment($docComment);
                            }
                        }
                    } catch (Exception $e) {
                        // Ignore errors if reflection fails
                    }
                } elseif (is_array($action['uses']) && count($action['uses']) === 2) { // For Laravel 9+ array syntax [Controller::class, 'method']
                    $controllerClass = $action['uses'][0];
                    $methodName = $action['uses'][1];
                    $controllerMethod = class_basename($controllerClass) . '@' . $methodName;
                    try {
                        if (class_exists($controllerClass) && method_exists($controllerClass, $methodName)) {
                            $reflectionMethod = new ReflectionMethod($controllerClass, $methodName);
                            $docComment = $reflectionMethod->getDocComment();
                            if ($docComment) {
                                $description = $this->parseDocComment($docComment);
                            }
                        }
                    } catch (Exception $e) {
                        
                    }
                } else {
                    $controllerMethod = 'Closure or Invokable';
                }
            }

            $routeInfo = [
                'methods' => $methods,
                'uri' => $uri,
                'controller_method' => $controllerMethod,
                'middleware' => implode(', ', $middleware),
                'description' => $description,
            ];

            // Categorize routes
            if (str_starts_with($uri, 'api/')) {
                $routesData['api'][] = $routeInfo;
            } else {
                $routesData['web'][] = $routeInfo;
            }

            $progressBar->setMessage("  Processing {$uri}...", "route");
            $progressBar->advance();
        }
        $progressBar->setMessage("Completed", "route");
        $progressBar->finish();
        $this->newLine(2);

        $this->info('Documentation generation complete!');
        $this->comment('You can view it in your browser at /docs');
        return $routesData;
    }

    private function parseDocComment($docComment) {
        // Simple parsing to get the main description
        $description = preg_replace('/^\s*\*+\s*(.*)$/m', '$1', $docComment); // Remove stars and leading space
        $description = trim(str_replace(['/**', '*/', '@param', '@return', '@throws', '@var', "\n\n"], ['', '', '', '', '', '', "\n"], $description)); // Remove docblock markers and common tags
        $lines = explode("\n", $description);
        $cleanDescription = [];
        foreach ($lines as $line) {
            if (empty(trim($line)) && !empty($cleanDescription)) { // Stop at first empty line after content
                break;
            }
            if (!empty(trim($line))) {
                $cleanDescription[] = trim($line);
            }
        }
        return implode(' ', $cleanDescription);
    }

    private function getFunctionsFromFiles($directory) {
        $results = [];
        if (!File::isDirectory($directory)) {
            $this->error("Directory not found: {$directory}");
            return [];
        }
        $totalSteps = 100;
        $progressBar = $this->output->createProgressBar($totalSteps);

        $progressBar->setFormat(" %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s% - <info>%file%</info>");

        $progressBar->start();
        foreach (File::allFiles($directory) as $file) {

            if ($file->getExtension() === 'php') {
                try {
                    $classData = $this->getClassDataFromFile($file->getPathname());
                    if (!empty($classData)) {
                        $results[$file->getFilename()] = $classData;
                    }
                } catch (Exception $e) {
                    $this->warn("Error processing file {$file->getFilename()}: " . $e->getMessage());
                }
            }
            $progressBar->setMessage("  Processing {$file}...", "file");
            $progressBar->advance();
        }
        $progressBar->setMessage("Completed", "file");
        $progressBar->finish();
        $this->newLine(2);

        $this->info('Documentation generation complete!');
        $this->comment('You can view it in your browser at /docs');
        return $results;
    }

    private function getClassDataFromFile($filePath) {
        $fileContent = File::get($filePath);
        $tokens = token_get_all($fileContent);

        $classes = [];
        $currentNamespace = '';
        $currentClassName = null;
        $lastDocComment = null;
        $usedClasses = [];

        for ($i = 0; $i < count($tokens); $i++) {
            $token = $tokens[$i];

            if (is_array($token)) {
                switch ($token[0]) {
                    case T_NAMESPACE:
                        $currentNamespace = $this->getNamespaceFromTokens($tokens, $i);
                        break;
                    case T_USE:
                        $usedClasses[] = $this->getUseStatementFromTokens($tokens, $i);
                        break;
                    case T_CLASS:
                    case T_TRAIT:
                    case T_INTERFACE:

                        $j = $i + 1;
                        while (isset($tokens[$j]) && ($tokens[$j][0] === T_WHITESPACE || $tokens[$j][0] === T_EXTENDS || $tokens[$j][0] === T_IMPLEMENTS)) {
                            $j++;
                        }
                        if (isset($tokens[$j]) && is_array($tokens[$j]) && $tokens[$j][0] === T_STRING) {
                            $currentClassName = $tokens[$j][1];
                            $fullClassName = $currentNamespace ? $currentNamespace . '\\' . $currentClassName : $currentClassName;

                            $classes[$fullClassName] = [
                                'description' => $lastDocComment ? $this->parseDocComment($lastDocComment) : 'No class description',
                                'functions' => [],
                                'used_classes' => array_filter(array_unique($usedClasses)), // Clean up duplicates
                            ];
                            $lastDocComment = null; // Reset for next element
                        }
                        break;
                    case T_FUNCTION:
                        if ($currentClassName !== null) {
                            $functionNameTokenIndex = $i + 2;
                            while (isset($tokens[$functionNameTokenIndex]) && $tokens[$functionNameTokenIndex][0] === T_WHITESPACE) {
                                $functionNameTokenIndex++;
                            }

                            if (isset($tokens[$functionNameTokenIndex]) && is_array($tokens[$functionNameTokenIndex]) && $tokens[$functionNameTokenIndex][0] === T_STRING) {
                                $functionName = $tokens[$functionNameTokenIndex][1];
                                $functionCode = $this->extractFunctionCode($tokens, $i);

                                $signature = $this->extractFunctionSignature($tokens, $i);

                                $classes[$fullClassName]['functions'][] = [
                                    'name' => $functionName,
                                    'signature' => $signature,
                                    'description' => $lastDocComment ? $this->parseDocComment($lastDocComment) : 'No function description',
                                    'code' => $functionCode,
                                ];
                                $lastDocComment = null;
                            }
                        }
                        break;
                    case T_DOC_COMMENT:
                        $lastDocComment = $token[1];
                        break;
                }
            }
        }
        return $classes;
    }

    private function getNamespaceFromTokens($tokens, &$i) {
        $namespace = '';
        $j = $i + 1;
        while (isset($tokens[$j]) && is_array($tokens[$j]) && ($tokens[$j][0] === T_STRING || $tokens[$j][0] === T_NS_SEPARATOR || $tokens[$j][0] === T_WHITESPACE)) {
            if ($tokens[$j][0] !== T_WHITESPACE) {
                $namespace .= $tokens[$j][1];
            }
            $j++;
        }
        $i = $j - 1;
        return $namespace;
    }

    private function getUseStatementFromTokens($tokens, &$i) {
        $useStatement = '';
        $j = $i + 1;
        while (isset($tokens[$j]) && $tokens[$j] !== ';') {
            if (is_array($tokens[$j])) {
                $useStatement .= $tokens[$j][1];
            } else {
                $useStatement .= $tokens[$j];
            }
            $j++;
        }
        $i = $j;
        return trim($useStatement);
    }

    private function extractFunctionCode($tokens, $startIndex) {
        $code = '';
        $bracketCount = 0;
        $inFunctionBody = false;

        for ($i = $startIndex; $i < count($tokens); $i++) {
            $token = $tokens[$i];

            if (is_array($token)) {
                $code .= $token[1];
            } else {
                $code .= $token;
            }

            if ($token === '{') {
                $bracketCount++;
                $inFunctionBody = true;
            } elseif ($token === '}') {
                $bracketCount--;
            }

            if ($inFunctionBody && $bracketCount === 0) {
                break;
            }
        }
        return $code;
    }

    private function extractFunctionSignature($tokens, $startIndex) {
        $signature = 'function ';
        $parenCount = 0;
        $inParens = false;
        $reachedBody = false;

        for ($i = $startIndex + 1; $i < count($tokens); $i++) { // Start after T_FUNCTION
            $token = $tokens[$i];

            if (is_array($token)) {
                if ($token[0] === T_WHITESPACE) {
                    if (!empty($signature) && substr($signature, -1) !== ' ') {
                        $signature .= ' ';
                    }
                    continue;
                }
                $signature .= $token[1];
            } else {
                $signature .= $token;
            }

            if ($token === '(') {
                $parenCount++;
                $inParens = true;
            } elseif ($token === ')') {
                $parenCount--;
            }

            if ($inParens && $parenCount === 0) {
                $j = $i + 1;
                while (isset($tokens[$j]) && (is_array($tokens[$j]) && $tokens[$j][0] === T_WHITESPACE || $tokens[$j] === ':')) {
                    if ($tokens[$j] === ':') {
                        $signature .= $tokens[$j];
                    } else if (is_array($tokens[$j]) && $tokens[$j][0] === T_WHITESPACE && substr($signature, -1) !== ' ') {
                        $signature .= ' ';
                    }
                    $j++;
                }

                // Handling the return type (and potentially reference operator '&' or nullable '?')
                // Check for array tokens (T_STRING, T_NS_SEPARATOR) or literal characters ('?', '&')
                if (isset($tokens[$j]) && (
                        (is_array($tokens[$j]) && ($tokens[$j][0] === T_STRING || $tokens[$j][0] === T_NS_SEPARATOR)) || // Standard type names
                        $tokens[$j] === '?' || // Nullable type literal '?'
                        $tokens[$j] === '&' // Reference operator literal '&' or intersection type literal '&'
                        )) {
                    while (isset($tokens[$j]) && (
                    (is_array($tokens[$j]) && ($tokens[$j][0] === T_STRING || $tokens[$j][0] === T_NS_SEPARATOR)) ||
                    $tokens[$j] === '?' ||
                    $tokens[$j] === '&'
                    )) {
                        $signature .= is_array($tokens[$j]) ? $tokens[$j][1] : $tokens[$j];
                        $j++;
                    }
                }
                $i = $j - 1;
                $reachedBody = true;
            }

            if ($token === '{' && $reachedBody) {
                break;
            }
        }
        return trim($signature);
    }

    private function generateViewsTreeData(): array {
        $viewsPath = resource_path('views');
        $totalSteps = 100;
        $progressBar = $this->output->createProgressBar($totalSteps);

        $progressBar->setFormat(" %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s% - <info>%Generate Views Tree%</info>");

        $progressBar->start();
        $bladeTree = new BladeViewTree($viewsPath, '');
        $progressBar->setMessage("Completed", "Views Tree");
        $progressBar->finish();
        $this->newLine(2);
        return $bladeTree->buildTree();
    }

    private function extractAssetsFromBlade(string $viewsPath): array {
        $css = [];
        $js = [];

        $files = array_filter(\File::allFiles($viewsPath), fn($f) => str_ends_with($f->getFilename(), '.blade.php'));

        foreach ($files as $file) {
            $content = file_get_contents($file->getRealPath());

            // Match <link> CSS
            preg_match_all('/<link\s+[^>]*href=["\']([^"\']+\.css)["\'][^>]*>/i', $content, $matches);
            foreach ($matches[1] ?? [] as $href) {
                $css[] = ltrim($href, '/');
            }

            // Match <script> JS
            preg_match_all('/<script\s+[^>]*src=["\']([^"\']+\.js)["\'][^>]*><\/script>/i', $content, $matches);
            foreach ($matches[1] ?? [] as $src) {
                $js[] = ltrim($src, '/');
            }

            // Match @vite (single or array)
            preg_match_all('/@vite\(\s*(\[.*?\]|["\'][^"\']+["\'])\s*\)/', $content, $matches);
            foreach ($matches[1] ?? [] as $match) {
                // If array, extract individual strings
                preg_match_all('/["\']([^"\']+)["\']/', $match, $paths);
                foreach ($paths[1] as $path) {
                    $path = ltrim($path, '/');
                    if (str_ends_with($path, '.css'))
                        $css[] = $path;
                    if (str_ends_with($path, '.js'))
                        $js[] = $path;
                }
            }

            // Match @mix directive
            preg_match_all('/@mix\(["\']([^"\']+)["\']\)/', $content, $matches);
            foreach ($matches[1] ?? [] as $path) {
                $path = ltrim($path, '/');
                if (str_ends_with($path, '.css'))
                    $css[] = $path;
                if (str_ends_with($path, '.js'))
                    $js[] = $path;
            }
        }

        return [
            'css' => array_unique($css),
            'js' => array_unique($js),
        ];
    }

    private function readCssFileDescription(string $cssFilePath): ?string {
        if (!file_exists($cssFilePath)) {
            return null;
        }

        $content = file_get_contents($cssFilePath);

        // Look for CSS comment block at the top
        if (preg_match('/\/\*\s*(.*?)\s*\*\//s', $content, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    private function generateAssetsReport() {

        $viewsPath = resource_path('views');
        $assets = $this->extractAssetsFromBlade($viewsPath);

        $totalItems = count($assets['css']) + count($assets['js']);
        $progressBar = $this->output->createProgressBar($totalItems);
        $progressBar->setFormat(" %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s% - Processing assets");
        $progressBar->start();

        $report = [
            'css' => [],
            'js' => [],
        ];

        foreach ($assets['css'] as $cssPath) {
            if (str_starts_with($cssPath, 'http')) {
                // External CDN
                $report['css'][] = [
                    'type' => 'cdn',
                    'path' => $cssPath,
                    'description' => null,
                ];
            } else {
                // Local file: resolve absolute path
                $absPath = base_path(trim($cssPath, '/'));
                $desc = $this->readCssFileDescription($absPath);
                $report['css'][] = [
                    'type' => 'local',
                    'path' => $cssPath,
                    'description' => $desc,
                ];
            }
            $progressBar->advance();
        }

        foreach ($assets['js'] as $jsPath) {
            $report['js'][] = [
                'path' => $jsPath,
                'type' => str_starts_with($jsPath, 'http') ? 'cdn' : 'local',
            ];
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        return $report;
    }

    protected function parseCssContent(string $cssContent): array {
        $lines = preg_split('/\r\n|\r|\n/', $cssContent);

        $parsed = [];
        $currentCommentLines = [];
        $collectingComment = false;

        foreach ($lines as $line) {
            $line = trim($line);

            // Start of multi-line comment
            if (preg_match('/^\/\*/', $line)) {
                $collectingComment = true;
                $currentCommentLines = [];
                // Remove /* from line and capture if any text
                $line = preg_replace('/^\/\*\s?/', '', $line);
                if (preg_match('/^(.*?)\*\//', $line, $m)) {
                    // single line comment
                    $currentCommentLines[] = trim($m[1]);
                    $collectingComment = false;
                } elseif ($line !== '') {
                    $currentCommentLines[] = $line;
                }
                continue;
            }

            // Collect multi-line comment content
            if ($collectingComment) {
                if (preg_match('/\*\//', $line)) {
                    // comment end line, remove trailing */
                    $line = preg_replace('/\*\//', '', $line);
                    if ($line !== '') {
                        $currentCommentLines[] = $line;
                    }
                    $collectingComment = false;
                } else {
                    $currentCommentLines[] = $line;
                }
                continue;
            }

            // If line is empty, skip
            if ($line === '') {
                continue;
            }

            // Match selector lines with declaration block start
            if (preg_match('/^([^{]+)\s*\{/', $line, $matches)) {
                $selector = trim($matches[1]);
                $description = implode(' ', $currentCommentLines);
                $parsed[] = [
                    'selector' => $selector,
                    'description' => $description,
                ];
                $currentCommentLines = [];
            }
        }

        return $parsed;
    }

    public function generateCssDocumentation() {
        $cssFiles = config('docs-generator.css_files', []);
        $allCssData = [];

        $totalFiles = count($cssFiles);
        $progressBar = $this->output->createProgressBar($totalFiles);
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% - Processing: %message%');
        $progressBar->start();

        foreach ($cssFiles as $fileInfo) {
            $filename = $fileInfo['type'] === 'file' ? basename($fileInfo['path']) : $fileInfo['url'];
            $progressBar->setMessage($filename);

            if ($fileInfo['type'] === 'file' && file_exists($fileInfo['path'])) {
                $cssContent = file_get_contents($fileInfo['path']);
                if ($cssContent !== false) {
                    $allCssData = array_merge($allCssData, $this->parseCssContent($cssContent));
                }
            } elseif ($fileInfo['type'] === 'url') {
                // Fetch remote CSS content
                try {
                    $cssContent = file_get_contents($fileInfo['url']);
                    if ($cssContent !== false) {
                        $allCssData = array_merge($allCssData, $this->parseCssContent($cssContent));
                    } else {
                        $this->warn("Failed to fetch CSS from URL: {$fileInfo['url']}");
                    }
                } catch (\Exception $e) {
                    $this->warn("Error fetching CSS from URL: {$fileInfo['url']} - " . $e->getMessage());
                }
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        return $allCssData;
    }

}
