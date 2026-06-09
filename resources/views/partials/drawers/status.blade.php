<div class="drawer-overlay" id="ov1" data-drawer-close="dStatus" data-drawer-overlay="ov1"></div>
<div class="drawer" id="dStatus">
    <div class="drawer-handle"></div>
    <p class="text-[17px] font-extrabold text-slate-900 m-0 mb-1">{{ __('Quick Status Update') }}</p>
    <p class="text-[13px] text-slate-400 m-0 mb-4">{{ __('Tap to broadcast to all customers') }}</p>
    <div class="flex flex-wrap gap-2.5 mb-5">
        @foreach([
            ['chip-amber', __('30 Min Late'), __('Running 30 min Late ⏰')],
            ['chip-red', __('Not Coming'), __('Not Coming Today ❌')],
            ['chip-green', __('On the Way'), __('On the Way 🛵')],
            ['chip-green', __('All Done'), __('All Deliveries Done ✅')],
            ['chip-blue', __('Started Route'), __('Started Route 🌅')],
        ] as [$chip, $label, $value])
            <button type="button" class="chip {{ $chip }} quick-broadcast" data-value="{{ $value }}">{{ $label }}</button>
        @endforeach
    </div>
    <a href="{{ route('admin.status') }}" class="btn-secondary no-underline">{{ __('Open Full Broadcast Panel →') }}</a>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const { apiPost, toast } = window.doodhwala;
    document.querySelectorAll('.quick-broadcast').forEach(btn => {
        btn.addEventListener('click', async () => {
            await apiPost(@json(route('admin.status.broadcast')), { message: btn.dataset.value });
            document.getElementById('dStatus')?.classList.remove('open');
            document.getElementById('ov1')?.classList.remove('open');
            toast(@json(__('Done!')));
        });
    });
    document.getElementById('ov1')?.addEventListener('click', () => {
        document.getElementById('dStatus')?.classList.remove('open');
        document.getElementById('ov1')?.classList.remove('open');
    });
});
</script>
@endpush
