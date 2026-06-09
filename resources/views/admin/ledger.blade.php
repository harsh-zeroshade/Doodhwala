@extends('layouts.doodhwala')

@section('content')
<div id="pageLedger" class="page active px-4">

    {{-- Month navigator --}}
    <div class="flex items-center gap-2 pt-4 pb-3">
        <a href="{{ route('admin.ledger', ['month' => $month->copy()->subMonth()->format('Y-m')]) }}"
           class="w-9 h-9 flex items-center justify-center rounded-[12px] shrink-0 no-underline"
           style="background:#F1F5F9;border:1.5px solid #E2E8F0;">
            <i class="ph ph-caret-left" style="font-size:16px;color:#475569;"></i>
        </a>

        <div class="flex-1 text-center">
            <p class="m-0 font-extrabold text-slate-900" style="font-size:17px;letter-spacing:-.02em;">
                {{ $month->translatedFormat('F Y') }}
            </p>
        </div>

        {{-- Disable "next" if already at current month --}}
        @if($month->lt(now()->startOfMonth()))
            <a href="{{ route('admin.ledger', ['month' => $month->copy()->addMonth()->format('Y-m')]) }}"
               class="w-9 h-9 flex items-center justify-center rounded-[12px] shrink-0 no-underline"
               style="background:#F1F5F9;border:1.5px solid #E2E8F0;">
                <i class="ph ph-caret-right" style="font-size:16px;color:#475569;"></i>
            </a>
        @else
            <div class="w-9 h-9 flex items-center justify-center rounded-[12px] shrink-0"
                 style="background:#F8FAFC;border:1.5px solid #F1F5F9;opacity:.4;">
                <i class="ph ph-caret-right" style="font-size:16px;color:#94A3B8;"></i>
            </div>
        @endif
    </div>

    {{-- Jump to any month --}}
    <form method="GET" action="{{ route('admin.ledger') }}" class="mb-4">
        <input type="month" name="month"
               value="{{ $month->format('Y-m') }}"
               max="{{ now()->format('Y-m') }}"
               class="input-field"
               onchange="this.form.submit()">
    </form>

    {{-- Summary cards --}}
    <div class="grid grid-cols-2 gap-3 mb-4">
        <div class="metric-card" style="background:linear-gradient(135deg,#ECFDF5,#D1FAE5);">
            <p class="lbl" style="color:#065F46;">{{ __('Collected') }}</p>
            <p class="m-0 font-extrabold" style="font-size:24px;color:#15803D;letter-spacing:-.02em;">₹{{ number_format($ledger['total_collected']) }}</p>
        </div>
        <div class="metric-card" style="background:linear-gradient(135deg,#FEF2F2,#FEE2E2);">
            <p class="lbl" style="color:#991B1B;">{{ __('Outstanding') }}</p>
            <p class="m-0 font-extrabold" style="font-size:24px;color:#DC2626;letter-spacing:-.02em;">₹{{ number_format($ledger['total_outstanding']) }}</p>
        </div>
    </div>

    {{-- Customer list --}}
    <div class="card mb-4">
        <div class="px-4 pt-4 pb-2 flex items-center justify-between">
            <p class="lbl m-0">{{ __('All Customers') }}</p>
            <span class="badge badge-amber">⚠ {{ __('Total Due') }}: ₹{{ number_format($ledger['total_outstanding']) }}</span>
        </div>
        <div class="px-4">
            @foreach($ledger['rows'] as $row)
                @php
                    $house   = $row['house'];
                    $userAvatar = $house->user?->avatarUrl();
                @endphp
                <div class="ledger-row">
                    <div class="flex items-center gap-3">
                        {{-- Profile picture or initials --}}
                        <div class="shrink-0" style="width:44px;height:44px;border-radius:14px;overflow:hidden;
                                    {{ $userAvatar ? '' : 'display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#DBEAFE,#BFDBFE);' }}">
                            @if($userAvatar)
                                <img src="{{ $userAvatar }}"
                                     style="width:44px;height:44px;object-fit:cover;display:block;" alt="">
                            @else
                                <span style="font-size:15px;font-weight:800;color:#1E40AF;">{{ $house->initials() }}</span>
                            @endif
                        </div>
                        <div>
                            <p class="m-0 font-bold text-slate-900" style="font-size:15px;">{{ $house->customer_name }}</p>
                            <p class="m-0 text-xs text-slate-400 mt-0.5">
                                <i class="ph ph-check-circle" style="font-size:11px;color:#16A34A;vertical-align:middle;"></i>
                                {{ $row['deliveries'] }} {{ __('days') }} · {{ number_format($row['liters'], 1) }}L
                            </p>
                        </div>
                    </div>
                    <div class="text-right">
                        @if($row['due'] > 0)
                            <p class="m-0 font-extrabold" style="font-size:18px;color:#DC2626;letter-spacing:-.01em;">₹{{ number_format($row['due']) }}</p>
                            <button type="button"
                                    class="pay-btn mt-1 font-bold cursor-pointer"
                                    style="font-size:11px;background:#DCFCE7;color:#166534;border:1px solid #86EFAC;
                                           border-radius:8px;padding:3px 10px;"
                                    data-house-id="{{ $house->id }}"
                                    data-name="{{ $house->customer_name }}"
                                    data-due="{{ $row['due'] }}">
                                <i class="ph ph-plus" style="font-size:10px;"></i> {{ __('Pay') }}
                            </button>
                        @else
                            <p class="m-0 font-extrabold" style="font-size:18px;color:#16A34A;letter-spacing:-.01em;">₹0</p>
                            <span class="badge badge-green mt-1 inline-flex">
                                <i class="ph ph-check" style="font-size:10px;"></i> {{ __('Paid') }}
                            </span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

@push('drawers')
    @include('partials.drawers.payment')
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const { apiPost, toast } = window.doodhwala;
    const paymentUrl = @json(route('admin.payment.record', ['house' => '__HOUSE__']));
    let currentHouseId = null;
    let paymentMode    = 'cash';

    document.querySelectorAll('.pay-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            currentHouseId = btn.dataset.houseId;
            document.getElementById('payName').textContent = btn.dataset.name;
            document.getElementById('payDue').textContent  = '₹' + parseInt(btn.dataset.due).toLocaleString();
            document.getElementById('payAmt').value        = '';
            document.getElementById('dPay').classList.add('open');
            document.getElementById('ov2').classList.add('open');
        });
    });

    document.getElementById('m1')?.addEventListener('click', () => { paymentMode = 'cash'; });
    document.getElementById('m2')?.addEventListener('click', () => { paymentMode = 'upi';  });

    document.getElementById('recordPayBtn')?.addEventListener('click', async () => {
        const amt = document.getElementById('payAmt').value;
        if (!amt || parseFloat(amt) <= 0) {
            document.getElementById('payAmt').style.borderColor = '#ef4444';
            return;
        }
        const url = paymentUrl.replace('__HOUSE__', currentHouseId);
        await apiPost(url, { amount: parseFloat(amt), payment_mode: paymentMode });
        document.getElementById('dPay').classList.remove('open');
        document.getElementById('ov2').classList.remove('open');
        toast('₹' + parseInt(amt).toLocaleString() + ' ' + @json(__('Done!')));
        location.reload();
    });
});
</script>
@endpush
