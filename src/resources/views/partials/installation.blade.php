<div class="section" id="installation">
    <h1 class="h2 mb-4">Installation Guide</h1>

    @if (!empty($installationData))
        <h2 class="h4">System Requirements</h2>
        <ul class="list-group mb-4">
            @foreach ($installationData['system_requirements'] ?? [] as $req => $val)
                @if ($req === 'Missing Extensions')
                    {{-- Skip here, handled below --}}
                    @continue
                @elseif ($req === 'Extensions' || $req === 'Required Extensions')
                    <li class="list-group-item">
                        <strong>PHP Extensions:</strong>
                        @php
                            $allExtensions = $val;
                            $missingExtensions = $installationData['system_requirements']['Missing Extensions'] ?? [];
                        @endphp
                        @foreach ($allExtensions as $ext)
                            @if (in_array($ext, $missingExtensions))
                                <span class="text-danger">{{ $ext }}</span>
                            @else
                                <span>{{ $ext }}</span>
                            @endif
                            @if (!$loop->last), @endif
                        @endforeach
                    </li>
                @else
                    <li class="list-group-item">
                        <strong>{{ $req }}:</strong>
                        @if (is_array($val))
                            {{ implode(', ', $val) }}
                        @else
                            {{ $val }}
                        @endif
                    </li>
                @endif
            @endforeach

            {{-- Optional summary of missing extensions --}}
            @if (!empty($installationData['system_requirements']['Missing Extensions']))
                <li class="list-group-item">
                    <strong>Missing Extensions Summary:</strong>
                    <span class="text-danger">{{ implode(', ', $installationData['system_requirements']['Missing Extensions']) }}</span>
                </li>
            @endif
        </ul>

        <h2 class="h4">Composer Dependencies</h2>
        <table class="table table-striped table-bordered mb-4">
            <thead>
                <tr>
                    <th>Package</th>
                    <th>Version</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($installationData['composer_dependencies'] ?? [] as $dependency)
                    <tr>
                        <td><code>{{ $dependency['package'] }}</code></td>
                        <td>{{ $dependency['version'] }}</td>
                        <td>{{ $dependency['description'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3">No Composer dependencies found or failed to load.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <h2 class="h4">Installation Steps</h2>
        @foreach ($installationData['steps'] ?? [] as $stepName => $stepContent)
            <div class="card mb-3 @if($stepName === 'Install Missing PHP Extensions') border-danger @endif">
                <div class="card-header h5">
                    {{ $stepName }}
                    @if($stepName === 'Install Missing PHP Extensions')
                        <span class="badge bg-danger ms-2">Important</span>
                    @endif
                </div>
                <div class="card-body">
                    @if (is_array($stepContent))
                        <ul class="list-group list-group-flush">
                            @foreach ($stepContent as $item)
                                <li class="list-group-item">
                                    @if (isset($item['link']))
                                        <a href="{{ $item['link'] }}" target="_blank">{{ $item['name'] }}</a>
                                    @else
                                        {{ $item['name'] ?? '' }}
                                    @endif
                                    @if (isset($item['description']))
                                        - {{ $item['description'] }}
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <pre><code class="language-bash">{{ $stepContent }}</code></pre>
                    @endif
                </div>
            </div>
        @endforeach
    @else
        <div class="alert alert-warning" role="alert">
            Installation data could not be loaded. Please run <code>php artisan docs:generate</code>.
        </div>
    @endif
</div>
