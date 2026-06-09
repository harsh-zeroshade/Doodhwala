@extends('layouts.doodhwala')

@section('content')
<div id="pageStatus" class="page active px-4">
    <p class="text-[13px] text-slate-500 py-4 m-0">{{ __('Broadcast real-time updates to all customers') }}</p>

    <p class="lbl mt-3">{{ __('Quick Select') }}</p>
    <div class="flex flex-wrap gap-2.5 mb-5">
        @foreach([
            ['chip-amber', __('Running 30 min Late ⏰')],
            ['chip-red', __('Not Coming Today ❌')],
            ['chip-green', __('On the Way 🛵')],
            ['chip-green', __('All Deliveries Done ✅')],
            ['chip-amber', __('Coming 1 Hour Late ⏱')],
            ['chip-blue', __('Started Route 🌅')],
        ] as [$chip, $text])
            <button type="button" class="chip {{ $chip }} status-chip" data-text="{{ $text }}">{{ $text }}</button>
        @endforeach
    </div>

    <p class="lbl">{{ __('Custom Message') }}</p>
    <textarea class="input-field resize-none h-[88px] leading-relaxed mb-3" id="statusMsg" rows="3" placeholder="{{ __('Type your custom update...') }}">{{ $defaultMessage }}</textarea>

    <div class="bg-slate-50 rounded-xl p-3.5 border border-slate-200 mb-4">
        <p class="text-xs text-slate-500 m-0 mb-1.5 font-bold">📋 {{ __('WhatsApp Preview') }}</p>
        <p id="previewMsg" class="text-[13px] text-slate-900 m-0 leading-relaxed break-words"></p>
    </div>

    <div class="flex flex-col gap-2.5">
        <button type="button" class="btn-primary bg-[#25d366]" id="shareWhatsApp">
            {{ __('Share on WhatsApp') }}
        </button>
        <button type="button" class="btn-secondary" id="copyStatus">{{ __('Copy to Clipboard') }}</button>
        <button type="button" class="btn-secondary" id="saveBroadcast">{{ __('Status') }}</button>
    </div>

    <p class="lbl mt-5">{{ __('Last Broadcast') }}</p>
    <div class="card p-4" id="lastBroadcastCard">
        @if($lastBroadcast)
            <div class="flex items-center gap-2.5">
                <div class="w-2.5 h-2.5 rounded-full bg-emerald-600 shrink-0"></div>
                <div>
                    <p class="m-0 text-sm font-bold text-slate-900">{{ $lastBroadcast->message }}</p>
                    <p class="mt-0.5 text-xs text-slate-400">{{ $lastBroadcast->created_at->diffForHumans() }} · {{ __('Sent to :count customers', ['count' => $customerCount]) }}</p>
                </div>
            </div>
        @else
            <p class="m-0 text-sm text-slate-400">{{ __('No delivery data yet.') }}</p>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const { apiPost, toast } = window.doodhwala;
    const milkmanName    = @json(auth()->user()->name);
    const todayLabel     = @json(now()->translatedFormat('j F Y'));
    const broadcastUrl   = @json(route('admin.status.broadcast'));
    const justNowLabel   = @json(__('Just now'));
    const sentToLabel    = @json(__('Sent to :count customers', ['count' => $customerCount]));
    const doodhwalaLabel = @json(__('Doodhwala Update'));
    const statusLabel    = @json(__('Status'));
    const dateLabel      = @json(__('Date'));
    const appName        = @json(__('Doodhwala'));

    const buildMsg = (t) =>
        `*${doodhwalaLabel}* 🥛\n${statusLabel}: ${t}\n${dateLabel}: ${todayLabel}\n— ${milkmanName} (${appName})`;

    const statusMsg  = document.getElementById('statusMsg');
    const previewMsg = document.getElementById('previewMsg');

    const updatePreview = (t) => {
        previewMsg.innerHTML = buildMsg(t).replace(/\n/g, '<br>');
    };

    updatePreview(statusMsg.value);
    statusMsg.addEventListener('input', () => updatePreview(statusMsg.value));

    document.querySelectorAll('.status-chip').forEach(chip => {
        chip.addEventListener('click', () => {
            statusMsg.value = chip.dataset.text;
            updatePreview(chip.dataset.text);
        });
    });

    document.getElementById('shareWhatsApp').addEventListener('click', () => {
        window.open('https://wa.me/?text=' + encodeURIComponent(buildMsg(statusMsg.value)), '_blank');
    });

    document.getElementById('copyStatus').addEventListener('click', () => {
        navigator.clipboard.writeText(buildMsg(statusMsg.value))
            .then(() => toast(@json(__('Copied!'))));
    });

    document.getElementById('saveBroadcast').addEventListener('click', async () => {
        const msg = statusMsg.value.trim();
        if (!msg) return;
        await apiPost(broadcastUrl, { message: msg });

        // Update "Last Broadcast" card dynamically
        const card = document.getElementById('lastBroadcastCard');
        if (card) {
            card.innerHTML =
                `<div class="flex items-center gap-2.5">` +
                    `<div class="w-2.5 h-2.5 rounded-full bg-emerald-600 shrink-0"></div>` +
                    `<div>` +
                        `<p class="m-0 text-sm font-bold text-slate-900">${msg}</p>` +
                        `<p class="mt-0.5 text-xs text-slate-400">${justNowLabel} · ${sentToLabel}</p>` +
                    `</div>` +
                `</div>`;
        }

        toast(@json(__('Done!')));
    });
});
</script>
@endpush
