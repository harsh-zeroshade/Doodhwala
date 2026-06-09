<nav class="bottom-nav">
    <a href="{{ route('admin.route') }}" class="nav-btn {{ ($activeNav ?? '') === 'route' ? 'active' : '' }} no-underline">
        @if(($activeNav ?? '') === 'route')
            <i class="ph-fill ph-map-trifold"></i>
        @else
            <i class="ph ph-map-trifold"></i>
        @endif
        {{ __('Route') }}
    </a>
    <a href="{{ route('admin.status') }}" class="nav-btn {{ ($activeNav ?? '') === 'status' ? 'active' : '' }} no-underline">
        @if(($activeNav ?? '') === 'status')
            <i class="ph-fill ph-megaphone-simple"></i>
        @else
            <i class="ph ph-megaphone-simple"></i>
        @endif
        {{ __('Broadcast') }}
    </a>
    <a href="{{ route('admin.ledger') }}" class="nav-btn {{ ($activeNav ?? '') === 'ledger' ? 'active' : '' }} no-underline">
        @if(($activeNav ?? '') === 'ledger')
            <i class="ph-fill ph-notebook"></i>
        @else
            <i class="ph ph-notebook"></i>
        @endif
        {{ __('Ledger') }}
    </a>
</nav>
