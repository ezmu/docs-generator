<div class="assets-report-section">
    <h1>Assets Report</h1>

    <h2>CSS Files</h2>
    @if(!empty($assetsReport['css']))
        <ul>
            @foreach ($assetsReport['css'] as $css)
                <li>
                    <strong>{{ $css['path'] }}</strong>
                    ({{ $css['type'] }})
                    @if(!empty($css['description']))
                        <br><em>Description:</em> {{ $css['description'] }}
                    @endif
                </li>
            @endforeach
        </ul>
    @else
        <p>No CSS files found.</p>
    @endif

    <h2>JS Files</h2>
    @if(!empty($assetsReport['js']))
        <ul>
            @foreach ($assetsReport['js'] as $js)
                <li>
                    <strong>{{ $js['path'] }}</strong>
                    ({{ $js['type'] }})
                </li>
            @endforeach
        </ul>
    @else
        <p>No JS files found.</p>
    @endif
</div>