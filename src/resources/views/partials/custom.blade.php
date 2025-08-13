<div class="custom-section">
    <h2>{{ $section['title'] ?? 'Untitled Section' }}</h2>

    @if($section['type'] === 'sections' && !empty($section['sections']))
    @foreach($section['sections'] as $subsection)
    <h3>{{ $subsection['title'] ?? 'Untitled' }}</h3>
    <p>{{ $subsection['description'] ?? '' }}</p>
    @endforeach
    @elseif($section['type'] === 'paragraph')
    <p>{{ $section['description'] ?? '' }}</p>
    @elseif($section['type'] === 'blade' && !empty($section['blade_path']))
    @include($section['blade_path'])
    @else
    <p>No content available for this section.</p>
    @endif
</div>
