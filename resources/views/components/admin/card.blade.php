@props([
    'title' => null,
    'subtitle' => null,
    'class' => 'mb-4',
    'headerAction' => null
])

<div class="card {{ $class }}">
    @if($title)
        <div class="card-header {{ $headerAction ? 'd-flex justify-content-between align-items-center' : '' }}">
            <div>
                <h5 class="mb-0">{{ $title }}</h5>
                @if($subtitle)
                    <small class="text-white-50">{{ $subtitle }}</small>
                @endif
            </div>
            @if($headerAction)
                <div>{{ $headerAction }}</div>
            @endif
        </div>
    @endif
    <div class="card-body">
        {{ $slot }}
    </div>
</div>