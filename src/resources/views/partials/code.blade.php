<div class="section" id="code">
    <h1 class="h2 mb-4">Code Base Overview</h1>
    <p class="lead">This section provides insights into your Controllers and Models, showing their functions, descriptions, and code snippets.</p>

    @if (!empty($codeData))
        {{-- Controllers Section --}}
        <h2 class="h4" id="controllers">Controllers</h2>
        @if (!empty($codeData['controllers']))
            <div class="accordion mb-5" id="controllersAccordion">
                @foreach ($codeData['controllers'] as $controllerFileName => $controllerClasses)
                    @foreach($controllerClasses as $fullClassName => $classData)
                        <div class="accordion-item section" id="{{ Str::slug(class_basename($fullClassName)) }}">
                            <h2 class="accordion-header" id="heading{{ Str::slug(class_basename($fullClassName)) }}">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ Str::slug(class_basename($fullClassName)) }}" aria-expanded="false" aria-controls="collapse{{ Str::slug(class_basename($fullClassName)) }}">
                                    Controller: <code>{{ class_basename($fullClassName) }}</code>
                                </button>
                            </h2>
                            <div id="collapse{{ Str::slug(class_basename($fullClassName)) }}" class="accordion-collapse collapse" aria-labelledby="heading{{ Str::slug(class_basename($fullClassName)) }}" data-bs-parent="#controllersAccordion">
                                <div class="accordion-body">
                                    <p><strong>Description:</strong> {{ $classData['description'] }}</p>

                                    @if (!empty($classData['used_classes']))
                                        <h6>Used Classes:</h6>
                                        <ul class="list-group list-group-flush mb-3">
                                            @foreach ($classData['used_classes'] as $usedClass)
                                                <li class="list-group-item"><code>{{ $usedClass }}</code></li>
                                            @endforeach
                                        </ul>
                                    @endif

                                    <h5 class="mt-4">Functions:</h5>
                                    <div class="list-group">
                                        @forelse ($classData['functions'] as $function)
                                            <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
                                                data-bs-toggle="modal" data-bs-target="#functionModal"
                                                data-function-name="{{ $function['name'] }}"
                                                data-function-signature="{{ $function['signature'] }}"
                                                data-function-description="{{ $function['description'] }}"
                                                data-function-code="{{ htmlspecialchars($function['code']) }}">
                                                <code>{{ $function['signature'] }}</code>
                                                <small class="text-muted">{{ Str::limit($function['description'], 80) }}</small>
                                            </a>
                                        @empty
                                            <div class="list-group-item">No functions found in this controller.</div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endforeach
            </div>
        @else
            <div class="alert alert-info">No controllers found or data could not be loaded.</div>
        @endif

        {{-- Models Section --}}
        <h2 class="h4 mt-5" id="models">Models</h2>
        @if (!empty($codeData['models']))
            <div class="accordion mb-5" id="modelsAccordion">
                @foreach ($codeData['models'] as $modelFileName => $modelClasses)
                    @foreach($modelClasses as $fullClassName => $classData)
                        <div class="accordion-item section" id="{{ Str::slug(class_basename($fullClassName)) }}">
                            <h2 class="accordion-header" id="heading{{ Str::slug(class_basename($fullClassName)) }}">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ Str::slug(class_basename($fullClassName)) }}" aria-expanded="false" aria-controls="collapse{{ Str::slug(class_basename($fullClassName)) }}">
                                    Model: <code>{{ class_basename($fullClassName) }}</code>
                                </button>
                            </h2>
                            <div id="collapse{{ Str::slug(class_basename($fullClassName)) }}" class="accordion-collapse collapse" aria-labelledby="heading{{ Str::slug(class_basename($fullClassName)) }}" data-bs-parent="#modelsAccordion">
                                <div class="accordion-body">
                                    <p><strong>Description:</strong> {{ $classData['description'] }}</p>

                                    @if (!empty($classData['used_classes']))
                                        <h6>Used Classes:</h6>
                                        <ul class="list-group list-group-flush mb-3">
                                            @foreach ($classData['used_classes'] as $usedClass)
                                                <li class="list-group-item"><code>{{ $usedClass }}</code></li>
                                            @endforeach
                                        </ul>
                                    @endif

                                    <h5 class="mt-4">Functions:</h5>
                                    <div class="list-group">
                                        @forelse ($classData['functions'] as $function)
                                            <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
                                                data-bs-toggle="modal" data-bs-target="#functionModal"
                                                data-function-name="{{ $function['name'] }}"
                                                data-function-signature="{{ $function['signature'] }}"
                                                data-function-description="{{ $function['description'] }}"
                                                data-function-code="{{ htmlspecialchars($function['code']) }}">
                                                <code>{{ $function['signature'] }}</code>
                                                <small class="text-muted">{{ Str::limit($function['description'], 80) }}</small>
                                            </a>
                                        @empty
                                            <div class="list-group-item">No functions found in this model.</div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endforeach
            </div>
        @else
            <div class="alert alert-info">No models found or data could not be loaded.</div>
        @endif

        <div class="modal fade" id="functionModal" tabindex="-1" aria-labelledby="functionModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="functionModalLabel">Function Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <h6 id="modalFunctionName" class="text-primary"></h6>
                        <p id="modalFunctionDescription"></p>
                        <pre><code class="language-php" id="modalFunctionCode"></code></pre>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const functionModal = document.getElementById('functionModal');
                functionModal.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget; // Button that triggered the modal
                    const functionName = button.getAttribute('data-function-name');
                    const functionSignature = button.getAttribute('data-function-signature');
                    const functionDescription = button.getAttribute('data-function-description');
                    const functionCode = button.getAttribute('data-function-code');

                    const modalTitle = functionModal.querySelector('.modal-title');
                    const modalFunctionName = functionModal.querySelector('#modalFunctionName');
                    const modalFunctionDescription = functionModal.querySelector('#modalFunctionDescription');
                    const modalFunctionCode = functionModal.querySelector('#modalFunctionCode');

                    modalTitle.textContent = `Function: ${functionName}`;
                    modalFunctionName.textContent = functionSignature;
                    modalFunctionDescription.textContent = functionDescription;
                    modalFunctionCode.textContent = functionCode;

                    // Re-highlight the code in the modal
                    hljs.highlightElement(modalFunctionCode);
                });
            });
        </script>

    @else
        <div class="alert alert-warning" role="alert">
            Code data could not be loaded. Please run `php artisan docs:generate`.
        </div>
    @endif
</div>