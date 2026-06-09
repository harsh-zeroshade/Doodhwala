@extends('layouts.doodhwala')

@section('content')
<div id="pageRoute" class="page active px-4">

    {{-- Header: date + stats + broadcast button --}}
    <div class="py-4 flex items-start justify-between">
        <div>
            <p class="text-[13px] text-slate-500 m-0 font-medium">{{ $date->translatedFormat('l, j F Y') }}</p>
            <div class="flex items-center gap-2 mt-1.5 flex-wrap">
                <span id="statDelivered" class="badge badge-green">✓ {{ $stats['delivered'] }} {{ __('Delivered') }}</span>
                <span id="statPending"   class="badge badge-amber">⏳ {{ $stats['pending'] }} {{ __('Pending') }}</span>
                <span class="badge badge-red">— {{ $stats['skipped'] }} {{ __('Skipped') }}</span>
            </div>
        </div>
        <button type="button"
                class="bg-emerald-50 border-[1.5px] border-green-200 rounded-xl py-2 px-3 text-xs font-bold text-emerald-600 shrink-0"
                data-drawer-open="dStatus" data-drawer-overlay="ov1">
            📢 {{ __('Status') }}
        </button>
    </div>

    {{-- Progress bar --}}
    <div class="bg-slate-200 rounded-[20px] h-[7px] mb-4 overflow-hidden">
        <div id="progressBar"
             class="h-full bg-gradient-to-r from-emerald-600 to-emerald-400 rounded-[20px] transition-all"
             style="width: {{ $stats['progress'] }}%"></div>
    </div>

    {{-- Delivery cards --}}
    <div class="flex flex-col gap-2.5">
        @foreach($houses as $house)
            @php
                $isDelivered     = $house->route_state === 'delivered';
                $isSkipped       = $house->route_state === 'skipped';
                $isHold          = $house->hold_badge;
                $pendingReqs     = $house->pending_requests;
            @endphp

            <div class="delivery-card {{ $isDelivered ? 'delivered' : '' }} {{ $isSkipped ? 'skipped' : '' }}"
                 id="dc-{{ $house->id }}"
                 data-house-id="{{ $house->id }}"
                 data-date="{{ $date->toDateString() }}"
                 data-cow="{{ $house->cow_milk }}"
                 data-buffalo="{{ $house->buffalo_milk }}">

                {{-- Customer avatar with route-order corner badge --}}
                @php $houseAvatarUrl = $house->user?->avatarUrl(); @endphp
                <div class="relative shrink-0" style="width:42px;height:42px;">
                    @if($houseAvatarUrl)
                        <img src="{{ $houseAvatarUrl }}"
                             style="width:42px;height:42px;border-radius:13px;object-fit:cover;display:block;
                                    {{ $isDelivered ? 'outline:2.5px solid #22C55E;outline-offset:1.5px;' : ($isSkipped ? 'outline:2.5px solid #FCA5A5;outline-offset:1.5px;' : '') }}"
                             alt="">
                    @else
                        <div style="width:42px;height:42px;border-radius:13px;display:flex;align-items:center;justify-content:center;
                                    font-weight:800;font-size:14px;
                                    {{ $isDelivered
                                        ? 'background:linear-gradient(135deg,#22C55E,#16A34A);color:white;'
                                        : ($isSkipped
                                            ? 'background:#FEE2E2;color:#EF4444;'
                                            : 'background:#F1F5F9;color:#64748B;') }}">
                            {{ $house->initials() }}
                        </div>
                    @endif
                    {{-- Route order corner badge --}}
                    <div style="position:absolute;bottom:-3px;right:-3px;background:#0F172A;color:#F8FAFC;
                                border-radius:6px;font-size:9px;font-weight:800;padding:1px 4px;
                                border:1.5px solid #F8FAFC;line-height:1.5;">
                        {{ $house->route_order }}
                    </div>
                </div>

                {{-- Content --}}
                <div class="flex-1 min-w-0">

                    {{-- Name row + edit pencil --}}
                    <div class="flex items-center gap-1 mb-0.5">
                        <p class="m-0 flex-1 text-[15px] font-bold text-slate-900 truncate">{{ $house->customer_name }}</p>
                        <button type="button"
                                class="edit-house-btn shrink-0 w-7 h-7 flex items-center justify-center rounded-lg bg-slate-100 border-0 cursor-pointer active:bg-slate-200"
                                data-house-id="{{ $house->id }}"
                                data-name="{{ $house->customer_name }}"
                                data-address="{{ $house->address }}"
                                data-default-cow="{{ $house->default_cow_milk }}"
                                data-default-buf="{{ $house->default_buffalo_milk }}">
                            <svg width="12" height="12" fill="none" stroke="#64748b" stroke-width="2.2"
                                 stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Address --}}
                    <p class="address-text my-0 text-xs {{ $house->address ? 'text-slate-500' : 'text-slate-300 italic' }}">
                        {{ $house->address ?: __('No address set') }}
                    </p>

                    @if($isHold)
                        {{-- Approved vacation hold --}}
                        <span class="badge badge-red mt-1.5 inline-flex">✈ {{ __('Vacation Hold') }}</span>

                    @else

                        {{-- Pending customer requests (all upcoming) --}}
                        @foreach($pendingReqs as $req)
                            <div class="flex items-center gap-1.5 mt-1.5 flex-wrap">
                                @if($req->type === 'hold')
                                    <span class="badge badge-amber">
                                        ✈ {{ $req->order_date->translatedFormat('M j') }} · {{ __('Hold') }}
                                    </span>
                                @else
                                    <span class="badge badge-blue">
                                        ➕ {{ $req->order_date->translatedFormat('M j') }}
                                        @if($req->extra_cow_milk > 0) · 🐄 +{{ number_format($req->extra_cow_milk, 1) }}L @endif
                                        @if($req->extra_buffalo_milk > 0) · 🐃 +{{ number_format($req->extra_buffalo_milk, 1) }}L @endif
                                    </span>
                                @endif
                                <button type="button"
                                        class="approve-order-btn text-[11px] font-bold text-emerald-600 bg-emerald-50 border border-emerald-200 rounded-lg py-0.5 px-2 cursor-pointer"
                                        data-order-id="{{ $req->id }}">
                                    {{ __('Approve') }}
                                </button>
                            </div>
                        @endforeach

                        {{-- Daily milk amounts (read-only, updated by JS on extra-milk approval) --}}
                        <div class="litre-display flex items-center gap-3 mt-2">
                            <span class="text-xs font-semibold text-slate-600">🐄 {{ number_format($house->cow_milk, 1) }}L</span>
                            <span class="text-slate-300 text-xs">·</span>
                            <span class="text-xs font-semibold text-slate-600">🐃 {{ number_format($house->buffalo_milk, 1) }}L</span>
                        </div>

                    @endif
                </div>

                {{-- Delivery toggle --}}
                @if($isHold)
                    <button type="button" class="toggle-btn shrink-0 bg-red-100 border-2 border-red-300 cursor-not-allowed" disabled>
                        <svg width="18" height="18" fill="none" stroke="#ef4444" stroke-width="2.5"
                             stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                        </svg>
                    </button>
                @else
                    <button type="button"
                            class="toggle-btn shrink-0 delivery-toggle {{ $isDelivered ? 'done' : 'undone' }}"
                            data-status="{{ $house->route_state }}">
                        <svg width="22" height="22" fill="none"
                             stroke="{{ $isDelivered ? '#fff' : '#cbd5e1' }}"
                             stroke-width="3" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path d="M20 6L9 17l-5-5"/>
                        </svg>
                    </button>
                @endif

            </div>
        @endforeach
    </div>
</div>
@endsection

@push('drawers')
    @include('partials.drawers.status')

    {{-- House edit drawer --}}
    <div class="drawer-overlay" id="ovHouseEdit"
         data-drawer-close="dHouseEdit" data-drawer-overlay="ovHouseEdit"></div>
    <div class="drawer" id="dHouseEdit">
        <div class="drawer-handle"></div>
        <p class="text-[17px] font-extrabold text-slate-900 m-0 mb-0.5" id="editHouseName"></p>
        <p class="text-[13px] text-slate-400 m-0 mb-4">{{ __('Set default daily milk & address') }}</p>

        <p class="lbl">{{ __('Address') }}</p>
        <input class="input-field mb-4" type="text" id="editAddress"
               placeholder="{{ __('e.g. A-12 Green Colony · 1st floor') }}">

        <div class="grid grid-cols-2 gap-3 mb-5">
            <div>
                <p class="lbl mb-2">🐄 {{ __('Cow Milk (L)') }}</p>
                <div class="qty-ctrl">
                    <button type="button" class="qty-btn" id="editCowMinus">−</button>
                    <input class="qty-val" id="editCowVal" value="0.0" readonly>
                    <button type="button" class="qty-btn" id="editCowPlus">+</button>
                </div>
            </div>
            <div>
                <p class="lbl mb-2">🐃 {{ __('Buffalo Milk (L)') }}</p>
                <div class="qty-ctrl">
                    <button type="button" class="qty-btn" id="editBufMinus">−</button>
                    <input class="qty-val" id="editBufVal" value="0.0" readonly>
                    <button type="button" class="qty-btn" id="editBufPlus">+</button>
                </div>
            </div>
        </div>

        <button type="button" class="btn-primary" id="saveHouseBtn">{{ __('Save Changes') }}</button>
    </div>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const { apiPost, toast } = window.doodhwala;
    const deliveryUrl    = @json(route('admin.delivery.update',      ['house'      => '__HOUSE__']));
    const updateHouseUrl = @json(route('admin.house.update',         ['house'      => '__HOUSE__']));
    const approveUrl     = @json(route('admin.extra-orders.approve', ['extraOrder' => '__ORDER__']));
    const deliveryMarked = @json(__('Delivery marked ✓'));
    const savedLabel     = @json(__('Saved ✓'));
    const noAddressLabel = @json(__('No address set'));
    const labelDelivered = @json(__('Delivered'));
    const labelPending   = @json(__('Pending'));

    // ── Live stats ─────────────────────────────────────────────
    const updateStats = () => {
        const cards = document.querySelectorAll('.delivery-card');
        const total  = cards.length;
        let delivered = 0, skipped = 0;
        cards.forEach(c => {
            if (c.classList.contains('delivered'))     delivered++;
            else if (c.classList.contains('skipped'))  skipped++;
        });
        const pending  = total - delivered - skipped;
        const progress = total > 0 ? Math.round(((delivered + skipped) / total) * 100) : 0;
        const elD   = document.getElementById('statDelivered');
        const elP   = document.getElementById('statPending');
        const elBar = document.getElementById('progressBar');
        if (elD)   elD.textContent   = `✓ ${delivered} ${labelDelivered}`;
        if (elP)   elP.textContent   = `⏳ ${pending} ${labelPending}`;
        if (elBar) elBar.style.width = `${progress}%`;
    };

    // ── Delivery toggle ────────────────────────────────────────
    document.querySelectorAll('.delivery-card').forEach(card => {
        const toggle = card.querySelector('.delivery-toggle');
        if (!toggle) return;

        const save = async (status) => {
            await apiPost(deliveryUrl.replace('__HOUSE__', card.dataset.houseId), {
                delivery_date: card.dataset.date,
                status,
                cow_milk:     parseFloat(card.dataset.cow     || 0),
                buffalo_milk: parseFloat(card.dataset.buffalo || 0),
            });
        };

        toggle.addEventListener('click', async () => {
            const isDone    = toggle.classList.contains('done');
            const newStatus = isDone ? 'pending' : 'delivered';
            try {
                await save(newStatus);
                toggle.classList.toggle('done',    !isDone);
                toggle.classList.toggle('undone',   isDone);
                card.classList.toggle('delivered', !isDone);
                toggle.querySelector('svg').setAttribute('stroke', isDone ? '#cbd5e1' : '#fff');
                if (!isDone) toast(deliveryMarked);
                updateStats();
            } catch { toast('Error'); }
        });
    });

    // ── Edit-house drawer ──────────────────────────────────────
    let activeHouseId = null;

    document.querySelectorAll('.edit-house-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            activeHouseId = btn.dataset.houseId;
            document.getElementById('editHouseName').textContent = btn.dataset.name;
            document.getElementById('editAddress').value  = btn.dataset.address  || '';
            document.getElementById('editCowVal').value   = parseFloat(btn.dataset.defaultCow || 0).toFixed(1);
            document.getElementById('editBufVal').value   = parseFloat(btn.dataset.defaultBuf || 0).toFixed(1);
            document.getElementById('dHouseEdit').classList.add('open');
            document.getElementById('ovHouseEdit').classList.add('open');
        });
    });

    const adjVal = (id, delta) => {
        const el = document.getElementById(id);
        el.value = Math.max(0, Math.round((parseFloat(el.value) + delta) * 10) / 10).toFixed(1);
    };
    document.getElementById('editCowMinus')?.addEventListener('click', () => adjVal('editCowVal', -0.5));
    document.getElementById('editCowPlus') ?.addEventListener('click', () => adjVal('editCowVal',  0.5));
    document.getElementById('editBufMinus')?.addEventListener('click', () => adjVal('editBufVal', -0.5));
    document.getElementById('editBufPlus') ?.addEventListener('click', () => adjVal('editBufVal',  0.5));

    document.getElementById('saveHouseBtn')?.addEventListener('click', async () => {
        const url = updateHouseUrl.replace('__HOUSE__', activeHouseId);
        const res = await apiPost(url, {
            address:              document.getElementById('editAddress').value,
            default_cow_milk:     parseFloat(document.getElementById('editCowVal').value),
            default_buffalo_milk: parseFloat(document.getElementById('editBufVal').value),
        });
        // Reflect changes in the card immediately
        const card = document.getElementById('dc-' + activeHouseId);
        if (card) {
            const addrEl = card.querySelector('.address-text');
            if (addrEl) addrEl.textContent = res.address || noAddressLabel;
            const editBtn = card.querySelector('.edit-house-btn');
            if (editBtn) {
                editBtn.dataset.address    = res.address ?? '';
                editBtn.dataset.defaultCow = res.default_cow_milk;
                editBtn.dataset.defaultBuf = res.default_buffalo_milk;
            }
            // Update today's qty inputs to match the new default (unless already delivered)
            const toggle = card.querySelector('.delivery-toggle');
            if (!toggle?.classList.contains('done')) {
                const cowInput = document.getElementById('cow-' + activeHouseId);
                const bufInput = document.getElementById('buf-' + activeHouseId);
                if (cowInput) cowInput.value = parseFloat(res.default_cow_milk).toFixed(1);
                if (bufInput) bufInput.value = parseFloat(res.default_buffalo_milk).toFixed(1);
            }
        }
        document.getElementById('dHouseEdit').classList.remove('open');
        document.getElementById('ovHouseEdit').classList.remove('open');
        toast(savedLabel);
    });

    // ── Approve pending request ────────────────────────
    const todayStr = @json(today()->toDateString());

    document.querySelectorAll('.approve-order-btn').forEach(btn => {
        btn.addEventListener('click', async (e) => {
            e.stopPropagation();
            try {
                const res = await apiPost(approveUrl.replace('__ORDER__', btn.dataset.orderId), {});

                // Remove the badge row from the card immediately
                btn.closest('.flex')?.remove();

                if (res.is_today) {
                    const card = document.querySelector(`[data-house-id="${res.house_id}"]`);

                    if (res.type === 'extra' && card) {
                        // Update the card's data attributes and displayed liters
                        card.dataset.cow     = res.updated_cow;
                        card.dataset.buffalo = res.updated_buffalo;

                        const litreRow = card.querySelector('.litre-display');
                        if (litreRow) {
                            litreRow.innerHTML =
                                `<span class="text-xs font-semibold text-slate-600">🐄 ${parseFloat(res.updated_cow).toFixed(1)}L</span>` +
                                `<span class="text-slate-300 text-xs">·</span>` +
                                `<span class="text-xs font-semibold text-slate-600">🐃 ${parseFloat(res.updated_buffalo).toFixed(1)}L</span>`;
                        }
                        toast(savedLabel);

                    } else if (res.type === 'hold') {
                        // Hold for today – reload so the toggle becomes disabled correctly
                        toast(savedLabel);
                        setTimeout(() => location.reload(), 900);
                    }
                } else {
                    // Future request – just remove the badge, no card update needed
                    toast(savedLabel);
                }
            } catch {
                toast(@json(__('Error')) + ' – ' + @json(__('Please try again')));
            }
        });
    });
});
</script>
@endpush
