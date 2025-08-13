<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />

        {{-- Get page title from config --}}
        <title>{{ config('docs-generator.page_title', 'Laravel Documentation') }}</title>

        {{-- Favicon from config if set --}}
        @if(config('docs-generator.fav_icon'))
        <link rel="icon" href="{{ asset(config('docs-generator.fav_icon')) }}" type="image/png" />
        @endif

        {{-- Bootstrap CSS --}}
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
        {{-- Highlight.js theme --}}
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/monokai.min.css" />
        <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
        <style>
            body {
                padding-top: 80px; 
            }
            .navbar {
                box-shadow: 0 2px 4px rgba(0,0,0,.05);
            }
            .sidebar {
                position: fixed;
                top: 80px; 
                bottom: 0;
                left: 0;
                width: 280px;
                z-index: 1000;
                padding: 20px;
                overflow-y: auto;
                background-color: #f8f9fa;
                border-right: 1px solid #eee;
            }
            .main-content {
                margin-left: 280px;
                padding: 20px;
            }
            .section {
                padding-top: 60px; 
                margin-top: -60px; 
            }
            pre {
                background-color: #272822;
                color: #f8f8f2;
                padding: 1rem;
                border-radius: 0.25rem;
                overflow-x: auto;
            }
            code {
                font-size: 0.875em;
            }
            .modal-content pre {
                max-height: 500px;
                overflow-y: auto;
            }
            .nav-link.active {
                font-weight: bold;
                color: #fff !important;
                background-color: #0d6efd !important;
                border-radius: 0.25rem;
            }
            /* Responsive adjustments */
            @media (max-width: 991.98px) {
                .sidebar {
                    position: static;
                    width: 100%;
                    height: auto;
                    border-right: none;
                    background-color: transparent;
                    padding: 0;
                }
                .main-content {
                    margin-left: 0;
                    padding: 15px;
                }
            }


        </style>
    </head>
    <body>

        <nav class="{{ config('docs-generator.navbar_class', 'navbar navbar-expand-lg navbar-dark bg-dark fixed-top') }}">
            <div class="container-fluid">
                <a class="navbar-brand d-flex align-items-center" href="{{ url(config('docs-generator.docs_route', 'docs')) }}">
                    @if(config('docs-generator.logo.path'))
                    <img 
                        src="{{ asset(config('docs-generator.logo.path')) }}" 
                        alt="{{ config('docs-generator.logo.alt', 'Docs Logo') }}"
                        style="height: {{ config('docs-generator.logo.height', 40) }}px; width: {{ config('docs-generator.logo.width', 150) }}px; object-fit: contain;"
                        >
                    @else
                    <svg width="60"  viewBox="0 0 150 150" xmlns="http://www.w3.org/2000/svg">
                            <rect x="5" y="5" width="140" height="140" rx="12" ry="12" fill="#000" stroke="#222" stroke-width="2"/>
                            <text x="15" y="35" font-size="20" fill="#66D9EF" font-family="monospace">&lt;?php</text>
                            <text x="30" y="90" font-size="48" fill="#FFFFFF" font-family="monospace" text-anchor="middle" dominant-baseline="middle">{</text>
                            <text x="75" y="90" font-size="16" fill="#FFD700" font-family="monospace" text-anchor="middle" dominant-baseline="middle">DocsGen</text>
                            <text x="120" y="90" font-size="48" fill="#FFFFFF" font-family="monospace" text-anchor="middle" dominant-baseline="middle">}</text>
                            <text x="75" y="70" font-size="14" fill="#FF2D20" font-family="monospace" text-anchor="middle" dominant-baseline="middle">Laravel</text>
                    </svg>
                    @endif
                </a>
                <a href="{{ asset('vendor/docs-generator/api-tester.html') }}" target="_blank" rel="noopener noreferrer" style="color: #fff;">API Tester</a>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                        aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0" id="topNavTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="tab-overview" data-bs-toggle="tab" href="#overview-section" role="tab" aria-selected="true">Overview</a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" id="tab-installation" data-bs-toggle="tab" href="#installation-section" role="tab" aria-selected="false">Installation</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab-views-tree" data-bs-toggle="tab" href="#views-tree-section" role="tab" aria-selected="false">Views Tree</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab-assets-report" data-bs-toggle="tab" href="#assets-report-section" role="tab" aria-selected="false">Assets Report</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab-erd" data-bs-toggle="tab" href="#erd-section" role="tab" aria-selected="false">ERD</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab-routes" data-bs-toggle="tab" href="#routes-section" role="tab" aria-selected="false">Routes</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab-code" data-bs-toggle="tab" href="#code-section" role="tab" aria-selected="false">Code</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <script>
function sidebarSearch() {
    return {
        query: '',
        filterSidebar() {
            const q = this.query.trim().toLowerCase();

            const mainTabs = document.querySelectorAll('#sidebarMainTabs .nav-link');
            const codeLinks = document.querySelectorAll('#sidebarCodeLinks .nav-link');

            function filterLinks(links) {
                links.forEach(link => {
                    const text = link.textContent.toLowerCase();
                    const li = link.closest('li');
                    if (!li)
                        return;

                    li.style.display = (q === '' || text.includes(q)) ? '' : 'none';
                });
            }

            filterLinks(mainTabs);
            filterLinks(codeLinks);
        }
    }
}
        </script>
        <div x-data="sidebarSearch()" class="sidebar" id="sidebarMenu">
            <!-- Sidebar Main Tabs -->


            <div class="p-2">
                <input
                    type="search"
                    x-model="query"
                    placeholder="Search docs & code..."
                    class="form-control form-control-sm"
                    autocomplete="off"
                    @input.debounce.300ms="filterSidebar"
                    />
            </div>

            <ul class="nav nav-pills flex-column" role="tablist" id="sidebarMainTabs">
                <li class="nav-item">
                    <a class="nav-link active" href="#overview-section" data-bs-toggle="pill" role="tab">Overview</a>
                </li>


                @if(!empty($customSections))
                @foreach($customSections as $section)
                @if($section['sidebar_group'] === 'main')
                <li class="nav-item">
                    <a 
                        class="nav-link" 
                        id="{{ $section['id'] }}-tab" 
                        data-bs-toggle="pill" 
                        href="#{{ $section['id'] }}-section" 
                        role="tab" 
                        aria-controls="{{ $section['id'] }}-section" 
                        aria-selected="false"
                        >
                        {{ $section['sidebar_label'] }}
                    </a>
                </li>
                @endif
                @endforeach
                @endif
                <li class="nav-item">
                    <a class="nav-link" href="#installation-section" data-bs-toggle="pill" role="tab">Installation Guide</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#views-tree-section" data-bs-toggle="pill" role="tab">Views Tree</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#assets-report-section" data-bs-toggle="pill" role="tab">Assets Report</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#css-docs-section" role="tab">CSS Documentation</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#erd-section" data-bs-toggle="pill" role="tab">ERD</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#routes-section" data-bs-toggle="pill" role="tab">Routes</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#code-section" data-bs-toggle="pill" role="tab">Code Base</a>
                </li>
            </ul>

            <hr />

            <!-- Sidebar Code Links -->
            <h6>Code Structure</h6>
            <ul class="nav flex-column" id="sidebarCodeLinks">
                @if (!empty($codeData['controllers']))
                @foreach ($codeData['controllers'] as $controllerFileName => $controllerClasses)
                @foreach($controllerClasses as $fullClassName => $classData)
                <li class="nav-item">
                    <a class="nav-link" href="#{{ \Illuminate\Support\Str::slug(class_basename($fullClassName)) }}">
                        {{ class_basename($fullClassName) }}
                    </a>
                </li>
                @endforeach
                @endforeach
                @endif
                @if (!empty($codeData['models']))
                @foreach ($codeData['models'] as $modelFileName => $modelClasses)
                @foreach($modelClasses as $fullClassName => $classData)
                <li class="nav-item">
                    <a class="nav-link" href="#{{ \Illuminate\Support\Str::slug(class_basename($fullClassName)) }}">
                        {{ class_basename($fullClassName) }}
                    </a>
                </li>
                @endforeach
                @endforeach
                @endif
            </ul>
        </div>

        <main class="main-content">
            <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade show active section" id="overview-section" role="tabpanel" aria-labelledby="tab-overview">
                    <h1>Welcome to Your Laravel Documentation!</h1>
                    <p>This interactive documentation provides an overview of your Laravel application, including installation steps, database structure (ERD), defined routes, and a deep dive into your controller and model code.</p>
                    <p>Use the navigation tabs or the sidebar on the left to explore.</p>
                    <h2>Generated On:</h2>
                    <p>{{ now()->format('F j, Y, H:i:s T') }}</p>
                </div>
                @foreach($customSections as $section)
                <div class="tab-pane fade" id="{{ $section['id'] }}-section">
                    @include($section['view'],$section)
                </div>
                @endforeach
                <div class="tab-pane fade section" id="installation-section" role="tabpanel" aria-labelledby="tab-installation">
                    @include('docs-generator::partials.installation', ['installationData' => $installationData])
                </div>

                <div class="tab-pane fade section" id="views-tree-section" role="tabpanel" aria-labelledby="tab-views-tree">
                    @include('docs-generator::partials.views-tree', ['viewMap' => $viewMap])
                </div>

                <div class="tab-pane fade section" id="assets-report-section" role="tabpanel" aria-labelledby="tab-assets-report">
                    @include('docs-generator::partials.assets-report', ['assetsReport' => $assetsReport])
                </div>
                <div class="tab-pane fade" id="css-docs-section" role="tabpanel">
                    @include('docs-generator::partials.css-docs', ['cssData' => $cssData])
                </div>
                <div class="tab-pane fade section" id="erd-section" role="tabpanel" aria-labelledby="tab-erd">
                    @include('docs-generator::partials.erd', ['erdData' => $erdData])
                </div>

                <div class="tab-pane fade section" id="routes-section" role="tabpanel" aria-labelledby="tab-routes">
                    @include('docs-generator::partials.routes', ['routesData' => $routesData])
                </div>

                <div class="tab-pane fade section" id="code-section" role="tabpanel" aria-labelledby="tab-code">
                    @include('docs-generator::partials.code', ['codeData' => $codeData])

                    {{-- Individual controller/model code sections for smooth scrolling --}}
                    @foreach ($codeData['controllers'] as $controllerFileName => $controllerClasses)
                    @foreach($controllerClasses as $fullClassName => $classData)
                    <div id="{{ \Illuminate\Support\Str::slug(class_basename($fullClassName)) }}" class="section">
                        <h3>{{ class_basename($fullClassName) }}</h3>
                        {{-- Display controller code/details here --}}
                    </div>
                    @endforeach
                    @endforeach

                    @foreach ($codeData['models'] as $modelFileName => $modelClasses)
                    @foreach($modelClasses as $fullClassName => $classData)
                    <div id="{{ \Illuminate\Support\Str::slug(class_basename($fullClassName)) }}" class="section">
                        <h3>{{ class_basename($fullClassName) }}</h3>
                        {{-- Display model code/details here --}}
                    </div>
                    @endforeach
                    @endforeach
                </div>
            </div>
        </main>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
        <script>
document.addEventListener('DOMContentLoaded', () => {
    hljs.highlightAll();

    const topNavTabs = document.querySelectorAll('#topNavTabs .nav-link');
    const sidebarMainTabs = document.querySelectorAll('#sidebarMainTabs .nav-link');
    const sidebarCodeLinks = document.querySelectorAll('#sidebarCodeLinks .nav-link');

    function clearActive(elements) {
        elements.forEach(el => el.classList.remove('active'));
    }

    function activateTab(tabElement) {
        const tab = new bootstrap.Tab(tabElement);
        tab.show();
    }

    function setActive(elements, selector) {
        elements.forEach(el => {
            el.classList.toggle('active', el.getAttribute('href') === selector);
        });
    }

    topNavTabs.forEach(tab => {
        tab.addEventListener('click', e => {
            e.preventDefault();
            const target = tab.getAttribute('href');

            activateTab(tab);
            setActive(sidebarMainTabs, target);
            clearActive(sidebarCodeLinks);

            history.replaceState(null, '', target);
            scrollToSection(target);
        });
    });

    sidebarMainTabs.forEach(tab => {
        tab.addEventListener('click', e => {
            e.preventDefault();
            const target = tab.getAttribute('href');

            activateTab(tab);
            setActive(topNavTabs, target);
            clearActive(sidebarCodeLinks);

            history.replaceState(null, '', target);
            scrollToSection(target);
        });
    });

    sidebarCodeLinks.forEach(link => {
        link.addEventListener('click', e => {
            e.preventDefault();
            clearActive(sidebarCodeLinks);
            link.classList.add('active');

            // Activate 'Code Base' tab on top nav and sidebar main tabs
            const codeBaseHash = '#code-section';
            activateTab(document.querySelector('#tab-code'));
            setActive(sidebarMainTabs, codeBaseHash);
            setActive(topNavTabs, codeBaseHash);

            const target = link.getAttribute('href');
            history.replaceState(null, '', target);
            scrollToSection(target);
        });
    });

    function scrollToSection(hash) {
        const el = document.querySelector(hash);
        if (el) {
            window.scrollTo({
                top: el.offsetTop - 56, // adjust for fixed navbar height
                behavior: 'smooth'
            });
        }
    }

    // On page load, activate tab and scroll based on URL hash
    function activateFromHash() {
        let hash = window.location.hash;
        if (!hash) {
            hash = '#overview-section';
        }

        // Check if hash matches a top nav tab
        const mainTab = document.querySelector(`#topNavTabs .nav-link[href="${hash}"]`);
        if (mainTab) {
            activateTab(mainTab);
            setActive(sidebarMainTabs, hash);
            clearActive(sidebarCodeLinks);
            scrollToSection(hash);
            return;
        }

        // Check if hash matches a sidebar code link
        const codeLink = document.querySelector(`#sidebarCodeLinks .nav-link[href="${hash}"]`);
        if (codeLink) {
            clearActive(sidebarCodeLinks);
            codeLink.classList.add('active');
            const codeBaseHash = '#code-section';
            activateTab(document.querySelector('#tab-code'));
            setActive(sidebarMainTabs, codeBaseHash);
            setActive(topNavTabs, codeBaseHash);
            scrollToSection(hash);
            return;
        }

        // Fallback to overview
        activateTab(document.querySelector('#tab-overview'));
        setActive(sidebarMainTabs, '#overview-section');
        clearActive(sidebarCodeLinks);
        scrollToSection('#overview-section');
    }

    activateFromHash();
});
        </script>

    </body>
</html>
