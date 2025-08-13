<div class="container py-4">
    <h1 class="mb-4">Blade Views Dependency Tree</h1>

    <ul class="list-group">
        @foreach ($viewMap['views'] as $view => $deps)
            <li class="list-group-item">
                <h5 class="mb-1">{{ $view }}</h5>
                <small class="text-muted">
                    File: {{ $deps['file'] }} |
                    Size: {{ number_format($deps['size']) }} bytes |
                    Modified: {{ $deps['last_modified'] }}
                </small>

                @if($deps['extends'])
                    <div><strong>Extends:</strong> <em>{{ $deps['extends'] }}</em></div>
                @endif

                @if(!empty($deps['includes']))
                    <div><strong>Includes:</strong>
                        <ul class="mb-1">
                            @foreach($deps['includes'] as $include)
                                <li>{{ $include }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if(!empty($deps['components']))
                    <div><strong>Components:</strong>
                        <ul class="mb-1">
                            @foreach($deps['components'] as $component)
                                <li>{{ $component }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if(!empty($deps['sections']))
                    <div><strong>Sections:</strong>
                        {{ implode(', ', $deps['sections']) }}
                    </div>
                @endif

                @if(!empty($deps['yields']))
                    <div><strong>Yields:</strong>
                        {{ implode(', ', $deps['yields']) }}
                    </div>
                @endif

                @if(!empty($deps['stacks']))
                    <div><strong>Stacks:</strong>
                        {{ implode(', ', $deps['stacks']) }}
                    </div>
                @endif

                @if(!empty($deps['variables']))
                    <div><strong>Variables:</strong>
                        {{ implode(', ', $deps['variables']) }}
                    </div>
                @endif

                @if(!empty($deps['translations']))
                    <div><strong>Translations:</strong>
                        {{ implode(', ', $deps['translations']) }}
                    </div>
                @endif

                @if(!empty($deps['assets']))
                    <div><strong>Assets:</strong>
                        {{ implode(', ', $deps['assets']) }}
                    </div>
                @endif

                @if(!empty($deps['directives']))
                    <div><strong>Directives:</strong>
                        {{ implode(', ', $deps['directives']) }}
                    </div>
                @endif
            </li>
        @endforeach
    </ul>
</div>
