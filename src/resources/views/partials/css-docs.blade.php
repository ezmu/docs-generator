<table class="table table-bordered table-hover">
    <thead>
        <tr>
            <th>Selector</th>
            <th>Description</th>
            <th>Example</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($cssData as $item)
            <tr>
                <td><code>{{ $item['selector'] }}</code></td>
                <td>{{ $item['description'] ?: 'No description' }}</td>
                <td>
                    @if (str_starts_with($item['selector'], '.'))
                        <div class="{{ ltrim($item['selector'], '.') }}" style="width:40px; height:20px; border:1px solid #ccc;"></div>
                    @elseif(str_starts_with($item['selector'], '#'))
                        <div id="{{ ltrim($item['selector'], '#') }}" style="width:40px; height:20px; border:1px solid #ccc;"></div>
                    @else
                        N/A
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
