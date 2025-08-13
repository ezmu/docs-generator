<?php

namespace Ezmu\DocsGenerator\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Exception;

class DocumentationController extends Controller {

    public function index() {
        $docsPath = storage_path('app/docs');
        $installationData = $this->loadJsonData("{$docsPath}/installation.json");
        $erdData = $this->loadJsonData("{$docsPath}/erd.json");
        $routesData = $this->loadJsonData("{$docsPath}/routes.json");
        $codeData = $this->loadJsonData("{$docsPath}/code.json");
        $viewMap = $this->loadJsonData("{$docsPath}/views-tree.json");
        $assetsReport = $this->loadJsonData("{$docsPath}/assets-report.json");
        
        $customSections = config('docs-generator.custom_sections', []);
        $cssData = $this->loadJsonData("{$docsPath}/css-report.json");
        $logo = config('docs-generator.logo');
        $customCss = config('docs-generator.custom_css');
        return view('docs-generator::index', [
            'installationData' => $installationData,
            'erdData' => $erdData,
            'routesData' => $routesData,
            'codeData' => $codeData,
            'viewMap' => $viewMap,
            'assetsReport' => $assetsReport,
            'customSections' => $customSections,
            'cssData' => $cssData,
            'logo' => $logo,
            'customCss' => $customCss,
        ]);
    }

    private function loadJsonData(string $filePath): array {
        if (File::exists($filePath)) {
            try {
                return json_decode(File::get($filePath), true) ?? [];
            } catch (Exception $e) {
                report($e);
                return [];
            }
        }
        return [];
    }

}
