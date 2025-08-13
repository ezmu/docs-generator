<?php

namespace Ezmu\DocsGenerator\Services;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveRegexIterator;
use RegexIterator;

class BladeViewTree
{
    protected string $viewsPath;
    protected string $namespacePrefix;

    // Store parsed data for each view
    protected array $viewMap = [];

    public function __construct(string $viewsPath, string $namespacePrefix = '')
    {
        $this->viewsPath = rtrim($viewsPath, DIRECTORY_SEPARATOR);
        $this->namespacePrefix = $namespacePrefix; // e.g. 'docs-generator::'
    }

    public function buildTree(): array
    {
        $this->viewMap = [];

        $files = $this->getBladeFiles();

        foreach ($files as $file) {
            $relative = str_replace($this->viewsPath . DIRECTORY_SEPARATOR, '', $file);
            $viewName = $this->bladeFileToViewName($relative);
            $content = file_get_contents($file);

            $this->viewMap[$viewName] = [
                'file'         => $relative,
                'path'         => $file,
                'last_modified'=> date('Y-m-d H:i:s', filemtime($file)),
                'size'         => filesize($file),
                'extends'      => $this->parseExtends($content),
                'includes'     => $this->parseIncludes($content),
                'components'   => $this->parseComponents($content),
                'sections'     => $this->parseSections($content),
                'yields'       => $this->parseYields($content),
                'stacks'       => $this->parseStacks($content),
                'variables'    => $this->parseVariables($content),
                'translations' => $this->parseTranslations($content),
                'assets'       => $this->parseAssets($content),
                'directives'   => $this->parseDirectives($content),
            ];
        }

        return [
            'tree'  => $this->buildDirectoryTree($this->viewsPath),
            'views' => $this->viewMap,
        ];
    }

    protected function getBladeFiles(): array
    {
        $directory = new RecursiveDirectoryIterator($this->viewsPath);
        $iterator = new RecursiveIteratorIterator($directory);
        $regex = new RegexIterator($iterator, '/\.blade\.php$/i', RecursiveRegexIterator::GET_MATCH);

        $files = [];
        foreach ($regex as $file => $match) {
            $files[] = $file;
        }
        return $files;
    }

    protected function buildDirectoryTree(string $path): array
    {
        $items = [];
        foreach (scandir($path) as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            $fullPath = $path . DIRECTORY_SEPARATOR . $entry;
            if (is_dir($fullPath)) {
                $items[$entry] = $this->buildDirectoryTree($fullPath);
            } elseif (preg_match('/\.blade\.php$/', $entry)) {
                $items[] = $entry;
            }
        }
        return $items;
    }

    protected function bladeFileToViewName(string $relativePath): string
    {
        $name = str_replace(DIRECTORY_SEPARATOR, '.', $relativePath);
        $name = preg_replace('/\.blade\.php$/', '', $name);

        if ($this->namespacePrefix) {
            $name = rtrim($this->namespacePrefix, '::') . '::' . $name;
        }
        return $name;
    }

    protected function parseExtends(string $content): ?string
    {
        return $this->firstMatch('/@extends\([\'"]([^\'"]+)[\'"]\)/', $content);
    }

    protected function parseIncludes(string $content): array
    {
        return $this->allMatches('/@include(?:If|When|First)?\([\'"]([^\'"]+)[\'"]/', $content);
    }

    protected function parseComponents(string $content): array
    {
        $bladeComponents = $this->allMatches('/<x-([\w\.\-:]+)/', $content);
        $componentDirectives = $this->allMatches('/@component\([\'"]([^\'"]+)[\'"]\)/', $content);
        return array_unique(array_merge($bladeComponents, $componentDirectives));
    }

    protected function parseSections(string $content): array
    {
        return $this->allMatches('/@section\([\'"]([^\'"]+)[\'"]/', $content);
    }

    protected function parseYields(string $content): array
    {
        return $this->allMatches('/@yield\([\'"]([^\'"]+)[\'"]/', $content);
    }

    protected function parseStacks(string $content): array
    {
        return array_unique(array_merge(
            $this->allMatches('/@push\([\'"]([^\'"]+)[\'"]/', $content),
            $this->allMatches('/@stack\([\'"]([^\'"]+)[\'"]/', $content)
        ));
    }

    protected function parseVariables(string $content): array
    {
        preg_match_all('/\$(\w+)/', $content, $matches);
        return array_unique($matches[0]);
    }

    protected function parseTranslations(string $content): array
    {
        return array_unique(array_merge(
            $this->allMatches('/__\([\'"]([^\'"]+)[\'"]/', $content),
            $this->allMatches('/@lang\([\'"]([^\'"]+)[\'"]/', $content)
        ));
    }

    protected function parseAssets(string $content): array
    {
        return array_unique(array_merge(
            $this->allMatches('/asset\([\'"]([^\'"]+)[\'"]/', $content),
            $this->allMatches('/mix\([\'"]([^\'"]+)[\'"]/', $content),
            $this->allMatches('/@vite\([\'"]([^\'"]+)[\'"]/', $content)
        ));
    }

    protected function parseDirectives(string $content): array
    {
        return $this->allMatches('/@(\w+)(?=\(|\s|$)/', $content);
    }

    protected function firstMatch(string $pattern, string $content): ?string
    {
        if (preg_match($pattern, $content, $matches)) {
            return $matches[1];
        }
        return null;
    }

    protected function allMatches(string $pattern, string $content): array
    {
        preg_match_all($pattern, $content, $matches);
        return $matches[1] ?? [];
    }
}
