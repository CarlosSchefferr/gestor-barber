<div class="flex items-center justify-between mb-4">
    <div class="flex items-center space-x-3">
        @if(isset($icon))
            <span class="text-barber-700">{!! $icon !!}</span>
        @endif
        <div>
            <h1 class="text-2xl font-bold text-barber-900">{{ $title }}</h1>
            @if(isset($subtitle))
                <div class="text-sm text-gray-500">{{ $subtitle }}</div>
            @endif
        </div>
    </div>

    <div>
        {{ $actions ?? '' }}
    </div>
</div>
