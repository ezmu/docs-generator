<div class="section" id="routes">
    <h1 class="h2 mb-4">Application Routes</h1>
    <?php

use Illuminate\Support\Facades\File; // import at the top
if (!function_exists('getApiLogsForUri')) {

        
function getApiLogsForUri(string $uri, int $limit = 3): array {
    $docsPath = storage_path('app/docs/api-log');
    if (!File::exists($docsPath)) {
        return []; // no logs folder yet
    }

    $files = File::files($docsPath); // get all files as SplFileInfo objects
    $logs = [];

    $safeUri = trim($uri, '/');

    foreach ($files as $file) {
        $content = File::get($file->getRealPath());
        $json = json_decode($content, true);

        if ($json && isset($json['uri']) && trim($json['uri'], '/') === $safeUri) {
            $logs[] = $json;
        }
    }

    // Sort descending by timestamp
    usort($logs, fn($a, $b) => strtotime($b['timestamp']) <=> strtotime($a['timestamp']));

    return array_slice($logs, 0, $limit);
}
    }
    ?>
    @if (!empty($routesData))
    @if (!empty($routesData['web']))
    <h2 class="h4">Web Routes</h2>
    <div class="table-responsive mb-4">
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Method(s)</th>
                    <th>URI</th>
                    <th>Action</th>
                    <th>Middleware</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($routesData['web'] as $route)
                <tr>
                    <td>
                        @foreach($route['methods'] as $method)
                        <span class="badge {{
                                            $method === 'GET' ? 'bg-success' :
                                            ($method === 'POST' ? 'bg-primary' :
                                            ($method === 'PUT' ? 'bg-warning text-dark' :
                                            ($method === 'PATCH' ? 'bg-info text-dark' :
                                            ($method === 'DELETE' ? 'bg-danger' : 'bg-secondary'))))
                              }}">{{ $method }}</span>
                        @endforeach
                    </td>
                    <td><code>{{ $route['uri'] }}</code></td>
                    <td><code>{{ $route['controller_method'] }}</code></td>
                    <td>{{ $route['middleware'] ?: 'None' }}</td>
                    <td>{{ $route['description'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="alert alert-info">No web routes found.</div>
    @endif

    @if (!empty($routesData['api']))
    <h2 class="h4 mt-5">API Routes</h2>
    <div class="table-responsive">
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Method(s)</th>
                    <th>URI</th>
                    <th>Action</th>
                    <th>Middleware</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
             @foreach ($routesData['api'] as $route)
    <tr>
        <td>
            @foreach($route['methods'] as $method)
                <span class="badge {{
                    $method === 'GET' ? 'bg-success' :
                    ($method === 'POST' ? 'bg-primary' :
                    ($method === 'PUT' ? 'bg-warning text-dark' :
                    ($method === 'PATCH' ? 'bg-info text-dark' :
                    ($method === 'DELETE' ? 'bg-danger' : 'bg-secondary'))))
                }}">{{ $method }}</span>
            @endforeach
        </td>
        <td><code>{{ $route['uri'] }}</code></td>
        <td><code>{{ $route['controller_method'] }}</code></td>
        <td>{{ $route['middleware'] ?: 'None' }}</td>
        <td>{{ $route['description'] }}</td>
        <td>
            <button class="btn btn-sm btn-outline-secondary" 
                    type="button" 
                    data-bs-toggle="collapse" 
                    data-bs-target="#logs-{{ md5($route['uri']) }}" 
                    aria-expanded="false" 
                    aria-controls="logs-{{ md5($route['uri']) }}">
                Logs
            </button>
        </td>
        <td>
    <button class="btn btn-sm btn-outline-primary api-test-btn"
            data-method="{{ $route['methods'][0] }}"   {{-- take first method --}}
            data-uri="{{ url($route['uri']) }}">         {{-- full URL --}}
        Test
    </button>
</td>
    </tr>

    <tr class="collapse" id="logs-{{ md5($route['uri']) }}">
        <td colspan="6" class="bg-light">
            @php
                $logs = getApiLogsForUri($route['uri']);
            @endphp

            @if (count($logs) === 0)
                <div class="text-muted">No logs found for this route.</div>
            @else
                <div class="api-logs">
                    @foreach ($logs as $log)
                        <div class="border rounded p-2 mb-2">
                            <div><strong>Timestamp:</strong> {{ $log['timestamp'] }}</div>
                            <div><strong>Request:</strong> <code>{{ $log['method'] }} {{ $log['uri'] }}</code></div>
                            <div><strong>Request Body:</strong> <pre>{{ json_encode($log['request_body'], JSON_PRETTY_PRINT) }}</pre></div>
                            <div><strong>Response Status:</strong> {{ $log['response_status'] }}</div>
                            <div><strong>Response Body:</strong> <pre>{{ $log['response_body'] }}</pre></div>
                        </div>
                    @endforeach
                </div>
            @endif
        </td>
    </tr>
@endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="alert alert-info">No API routes found.</div>
    @endif
    @else
    <div class="alert alert-warning" role="alert">
        Routes data could not be loaded. Please run `php artisan docs:generate`.
    </div>
    @endif
</div>
<div class="modal fade" id="apiTesterModal" tabindex="-1" aria-labelledby="apiTesterModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="apiTesterModalLabel">API Tester</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="apiTesterForm">
          <div class="mb-3">
            <label for="apiMethod" class="form-label">HTTP Method</label>
            <select id="apiMethod" class="form-select">
              <option>GET</option>
              <option>POST</option>
              <option>PUT</option>
              <option>PATCH</option>
              <option>DELETE</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="apiUrl" class="form-label">Request URL</label>
            <input type="text" id="apiUrl" class="form-control" />
          </div>
          <div class="mb-3">
            <label for="apiBody" class="form-label">Request Body (JSON)</label>
            <textarea id="apiBody" class="form-control" rows="6" placeholder='{"key":"value"}'></textarea>
          </div>
          <button type="submit" class="btn btn-primary">Send Request</button>
        </form>
        <hr />
        <pre id="apiResponse" style="color:#222 ;max-height:400px; overflow:auto; background:#f5f5f5; padding:10px;"></pre>
      </div>
    </div>
  </div>
</div>
<script>

document.querySelectorAll('.api-test-btn').forEach(button => {
  button.addEventListener('click', () => {
    const method = button.dataset.method || 'GET';
    const url = button.dataset.uri || '';

    // Fill modal inputs
    document.getElementById('apiMethod').value = method;
    document.getElementById('apiUrl').value = url;
    document.getElementById('apiBody').value = '';

    // Clear previous response
    document.getElementById('apiResponse').textContent = '';

    // Show Bootstrap modal
    const apiTesterModal = new bootstrap.Modal(document.getElementById('apiTesterModal'));
    apiTesterModal.show();
  });
});

document.getElementById('apiTesterForm').addEventListener('submit', async (e) => {
  e.preventDefault();

  const method = document.getElementById('apiMethod').value;
  const url = document.getElementById('apiUrl').value;
  let body = document.getElementById('apiBody').value.trim();

  let options = {
    method,
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      // Add auth token here if needed
    },
  };

  if (method !== 'GET' && body) {
    try {
      options.body = JSON.stringify(JSON.parse(body));
    } catch {
      alert('Invalid JSON in request body');
      return;
    }
  }

  try {
    const response = await fetch(url, options);
    const contentType = response.headers.get('content-type');
    let responseBody;

    if (contentType && contentType.includes('application/json')) {
      const json = await response.json();
      responseBody = JSON.stringify(json, null, 2);
    } else {
      responseBody = await response.text();
    }

    document.getElementById('apiResponse').textContent = responseBody;
  } catch (err) {
    document.getElementById('apiResponse').textContent = `Error: ${err.message}`;
  }
});
</script>