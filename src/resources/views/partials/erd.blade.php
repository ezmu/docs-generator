<div class="section" id="erd">
    <h1 class="h2 mb-4">Entity-Relationship Diagram (ERD)</h1>

    @if (!empty($erdData) && (!empty($erdData['tables']) || !empty($erdData['relationships'])))
        <p class="lead">This section outlines your database schema, tables, columns, and detected relationships between your Eloquent models.</p>

        @if (!empty($erdData['tables']))
            <h2 class="h4">Tables and Columns</h2>
            <div class="accordion" id="tablesAccordion">
                @foreach ($erdData['tables'] as $tableName => $tableData)
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading{{ Str::slug($tableName) }}">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ Str::slug($tableName) }}" aria-expanded="false" aria-controls="collapse{{ Str::slug($tableName) }}">
                                Table: <code>{{ $tableName }}</code>
                            </button>
                        </h2>
                        <div id="collapse{{ Str::slug($tableName) }}" class="accordion-collapse collapse" aria-labelledby="heading{{ Str::slug($tableName) }}" data-bs-parent="#tablesAccordion">
                            <div class="accordion-body">
                                <h5 class="mt-0">Columns</h5>
                                <table class="table table-sm table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Column Name</th>
                                            <th>Type</th>
                                            <th>Nullable</th>
                                            <th>Default</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($tableData['columns'] ?? [] as $column)
                                            <tr>
                                                <td><code>{{ $column['name'] }}</code></td>
                                                <td>{{ $column['type'] }}</td>
                                                <td>{{ $column['nullable'] ? 'Yes' : 'No' }}</td>
                                                <td>{{ $column['default'] }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="4">No columns found for this table.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>

                                @if (!empty($tableData['foreign_keys']))
                                    <h5 class="mt-3">Foreign Keys (from database)</h5>
                                    <ul class="list-group list-group-flush">
                                        @foreach ($tableData['foreign_keys'] as $fk)
                                            <li class="list-group-item">
                                                <code>{{ $fk['column'] }}</code> references <code>{{ $fk['referenced_table'] }}.{{ $fk['referenced_column'] }}</code>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="alert alert-info mt-4">
                No tables data found. Ensure your database is migrated and connected, and `php artisan docs:generate` was run correctly.
            </div>
        @endif

        @if (!empty($erdData['relationships']))
            <h2 class="h4 mt-5">Model Relationships (detected from Eloquent)</h2>
            <div class="accordion" id="relationshipsAccordion">
                @foreach ($erdData['relationships'] as $modelTable => $relationships)
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingRel{{ Str::slug($modelTable) }}">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseRel{{ Str::slug($modelTable) }}" aria-expanded="false" aria-controls="collapseRel{{ Str::slug($modelTable) }}">
                                Model Table: <code>{{ $modelTable }}</code>
                            </button>
                        </h2>
                        <div id="collapseRel{{ Str::slug($modelTable) }}" class="accordion-collapse collapse" aria-labelledby="headingRel{{ Str::slug($modelTable) }}" data-bs-parent="#relationshipsAccordion">
                            <div class="accordion-body">
                                <ul class="list-group list-group-flush">
                                    @forelse ($relationships as $relationship)
                                        <li class="list-group-item">
                                            <strong>{{ $relationship['name'] }}()</strong>: {{ $relationship['type'] }} with <code>{{ $relationship['related_model'] }}</code> (<code>{{ $relationship['related_table'] }}</code> table)
                                        </li>
                                    @empty
                                        <li class="list-group-item">No relationships detected for this model.</li>
                                    @endforelse
                                </ul>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="alert alert-info mt-4">
                No model relationships data found. Ensure your models define relationships and `php artisan docs:generate` was run.
            </div>
        @endif

    @else
        <div class="alert alert-warning" role="alert">
            ERD data could not be loaded. Please run `php artisan docs:generate`.
        </div>
    @endif
</div>