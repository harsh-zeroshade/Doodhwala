<nav class="bottom-nav">
    <a href="{{ route('customer.overview') }}" class="nav-btn {{ ($activeNav ?? '') === 'overview' ? 'active' : '' }} no-underline">
        @if(($activeNav ?? '') === 'overview')
            <i class="ph-fill ph-squares-four"></i>
        @else
            <i class="ph ph-squares-four"></i>
        @endif
        {{ __('Overview') }}
    </a>
    <a href="{{ route('customer.calendar') }}" class="nav-btn {{ ($activeNav ?? '') === 'calendar' ? 'active' : '' }} no-underline">
        @if(($activeNav ?? '') === 'calendar')
            <i class="ph-fill ph-calendar-blank"></i>
        @else
            <i class="ph ph-calendar-blank"></i>
        @endif
        {{ __('Calendar') }}
    </a>
    <a href="{{ route('customer.requests') }}" class="nav-btn {{ ($activeNav ?? '') === 'requests' ? 'active' : '' }} no-underline">
        @if(($activeNav ?? '') === 'requests')
            <i class="ph-fill ph-pencil-simple"></i>
        @else
            <i class="ph ph-pencil-simple"></i>
        @endif
        {{ __('Requests') }}
    </a>
</nav>
