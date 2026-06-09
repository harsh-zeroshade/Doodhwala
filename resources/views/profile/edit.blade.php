@extends('layouts.doodhwala')

@section('content')
<div class="page active px-4 pb-7">

    @if(session('status') === 'avatar-updated')
        <div class="mt-4 bg-green-50 border-[1.5px] border-green-200 rounded-xl p-3.5 text-sm font-semibold text-green-700">
            ✓ {{ __('Profile photo updated.') }}
        </div>
    @endif
    @if(session('status') === 'avatar-removed')
        <div class="mt-4 bg-slate-50 border-[1.5px] border-slate-200 rounded-xl p-3.5 text-sm font-semibold text-slate-600">
            {{ __('Profile photo removed.') }}
        </div>
    @endif
    @if(session('status') === 'profile-updated')
        <div class="mt-4 bg-green-50 border-[1.5px] border-green-200 rounded-xl p-3.5 text-sm font-semibold text-green-700">
            ✓ {{ __('Profile updated successfully.') }}
        </div>
    @endif
    @if(session('status') === 'password-updated')
        <div class="mt-4 bg-green-50 border-[1.5px] border-green-200 rounded-xl p-3.5 text-sm font-semibold text-green-700">
            ✓ {{ __('Password updated successfully.') }}
        </div>
    @endif

    {{-- Profile Picture --}}
    @php $profileAvatarUrl = $user->avatarUrl(); @endphp
    <div class="card p-4 mt-4 mb-4">
        <p class="m-0 mb-4 text-[15px] font-extrabold text-slate-900">{{ __('Profile Picture') }}</p>
        <div class="flex items-center gap-4">

            @if($profileAvatarUrl)
                {{-- Avatar with hover/tap overlay --}}
                <div id="avatarContainer"
                     style="position:relative;width:72px;height:72px;flex-shrink:0;cursor:pointer;">
                    <img id="avatarPreview" src="{{ $profileAvatarUrl }}"
                         style="display:block;width:72px;height:72px;border-radius:20px;object-fit:cover;user-select:none;"
                         alt="">
                    {{-- Overlay (hidden by default, shown on hover/tap) --}}
                    <div id="avatarOverlay"
                         style="display:none;position:absolute;inset:0;border-radius:20px;
                                background:rgba(0,0,0,0.62);align-items:center;justify-content:center;gap:12px;">
                        <label for="avatarInput"
                               style="display:flex;flex-direction:column;align-items:center;gap:2px;
                                      color:#fff;font-size:10px;font-weight:700;cursor:pointer;
                                      line-height:1.2;text-align:center;user-select:none;">
                            <span style="font-size:18px;">📷</span>
                            {{ __('Change') }}
                        </label>
                        <button type="button" id="removeAvatarBtn"
                                style="display:flex;flex-direction:column;align-items:center;gap:2px;
                                       color:#fca5a5;font-size:10px;font-weight:700;
                                       border:none;background:none;cursor:pointer;padding:0;
                                       line-height:1.2;text-align:center;">
                            <span style="font-size:18px;">🗑️</span>
                            {{ __('Remove') }}
                        </button>
                    </div>
                    {{-- Hidden forms --}}
                    <form method="POST" action="{{ route('profile.avatar') }}"
                          enctype="multipart/form-data" id="avatarForm" class="hidden">
                        @csrf
                        <input type="file" name="avatar" id="avatarInput"
                               accept="image/jpeg,image/png,image/webp">
                    </form>
                    <form method="POST" action="{{ route('profile.avatar.remove') }}"
                          id="removeAvatarForm" class="hidden">
                        @csrf
                        @method('DELETE')
                    </form>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="m-0 text-[15px] font-bold text-slate-900 truncate">{{ $user->name }}</p>
                    <p class="m-0 text-xs text-slate-400 mt-0.5">{{ __('Tap photo to change or remove') }}</p>
                </div>

            @else
                {{-- No avatar: initials circle + upload button --}}
                <div id="avatarPreviewWrap"
                     class="w-[72px] h-[72px] rounded-[20px] shrink-0 flex items-center justify-center
                            {{ $user->isAdmin() ? 'bg-emerald-600 shadow-[0_4px_8px_rgba(5,150,105,.2)]' : 'bg-blue-500' }}">
                    <span id="avatarInitials" class="text-2xl font-extrabold text-white">{{ $user->initials() }}</span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="m-0 text-[15px] font-bold text-slate-900 truncate">{{ $user->name }}</p>
                    <p class="m-0 text-xs text-slate-400 mt-0.5">{{ __('JPG, PNG or WebP · Max 2 MB') }}</p>
                    <form method="POST" action="{{ route('profile.avatar') }}"
                          enctype="multipart/form-data" id="avatarForm">
                        @csrf
                        <input type="file" name="avatar" id="avatarInput"
                               accept="image/jpeg,image/png,image/webp" class="hidden">
                        <label for="avatarInput"
                               class="mt-2.5 inline-flex items-center gap-1.5 text-[13px] font-bold text-emerald-600
                                      bg-emerald-50 border-[1.5px] border-emerald-200 rounded-xl py-2 px-3.5
                                      cursor-pointer active:bg-emerald-100 select-none">
                            📷 {{ __('Change Photo') }}
                        </label>
                    </form>
                </div>
            @endif

        </div>
    </div>

    {{-- Profile Information --}}
    <div class="card p-4 mt-4 mb-4">
        <p class="m-0 mb-4 text-[15px] font-extrabold text-slate-900">{{ __('Profile Information') }}</p>
        <form method="POST" action="{{ route('profile.update') }}">
            @csrf
            @method('PATCH')

            <p class="lbl">{{ __('Name') }}</p>
            <input class="input-field mb-3 {{ $errors->has('name') ? 'border-red-400' : '' }}"
                   type="text" name="name"
                   value="{{ old('name', $user->name) }}" required autocomplete="name">
            @error('name')
                <p class="text-[12px] text-red-500 -mt-2 mb-3">{{ $message }}</p>
            @enderror

            <p class="lbl">{{ __('Phone Number') }}</p>
            <input class="input-field mb-3 {{ $errors->has('phone_number') ? 'border-red-400' : '' }}"
                   type="tel" name="phone_number"
                   value="{{ old('phone_number', $user->phone_number) }}" required autocomplete="tel">
            @error('phone_number')
                <p class="text-[12px] text-red-500 -mt-2 mb-3">{{ $message }}</p>
            @enderror

            <button type="submit" class="btn-primary">{{ __('Save Changes') }}</button>
        </form>
    </div>

    {{-- Change Password --}}
    <div class="card p-4 mb-4">
        <p class="m-0 mb-4 text-[15px] font-extrabold text-slate-900">{{ __('Change Password') }}</p>
        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            @method('PUT')

            <p class="lbl">{{ __('Current Password') }}</p>
            <input class="input-field mb-3 {{ $errors->updatePassword->has('current_password') ? 'border-red-400' : '' }}"
                   type="password" name="current_password" autocomplete="current-password">
            @error('current_password', 'updatePassword')
                <p class="text-[12px] text-red-500 -mt-2 mb-3">{{ $message }}</p>
            @enderror

            <p class="lbl">{{ __('New Password') }}</p>
            <input class="input-field mb-3 {{ $errors->updatePassword->has('password') ? 'border-red-400' : '' }}"
                   type="password" name="password" autocomplete="new-password">
            @error('password', 'updatePassword')
                <p class="text-[12px] text-red-500 -mt-2 mb-3">{{ $message }}</p>
            @enderror

            <p class="lbl">{{ __('Confirm New Password') }}</p>
            <input class="input-field mb-4"
                   type="password" name="password_confirmation" autocomplete="new-password">

            <button type="submit" class="btn-primary">{{ __('Update Password') }}</button>
        </form>
    </div>

    {{-- Logout --}}
    <form method="POST" action="{{ route('logout') }}" class="mb-4">
        @csrf
        <button type="submit" class="btn-primary" style="background:#ef4444;box-shadow:none;">
            🚪 {{ __('Logout') }}
        </button>
    </form>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const container  = document.getElementById('avatarContainer');
    const overlay    = document.getElementById('avatarOverlay');
    const removeBtn  = document.getElementById('removeAvatarBtn');
    const avatarForm = document.getElementById('avatarForm');
    const removeForm = document.getElementById('removeAvatarForm');
    const input      = document.getElementById('avatarInput');

    // ── Overlay show / hide ───────────────────────────────────
    const show = () => { if (overlay) overlay.style.display = 'flex'; };
    const hide = () => { if (overlay) overlay.style.display = 'none'; };

    if (container && overlay) {
        // Desktop: show on hover
        container.addEventListener('mouseenter', show);
        container.addEventListener('mouseleave', hide);

        // Mobile: tap the image to reveal, tap outside to dismiss
        document.getElementById('avatarPreview')?.addEventListener('click', (e) => {
            e.stopPropagation();
            overlay.classList.contains('hidden') ? show() : hide();
        });
        document.addEventListener('click', hide);

        // Keep overlay open when interacting with its children
        overlay.addEventListener('click', (e) => e.stopPropagation());
    }

    // ── Remove photo ──────────────────────────────────────────
    removeBtn?.addEventListener('click', () => removeForm?.submit());

    // ── Upload: preview then auto-submit ─────────────────────
    input?.addEventListener('change', () => {
        const file = input.files?.[0];
        if (!file) return;

        // Instant local preview
        const reader = new FileReader();
        reader.onload = (ev) => {
            const existing = document.getElementById('avatarPreview')
                          || document.getElementById('avatarPreviewWrap');
            if (existing) {
                const img = document.createElement('img');
                img.id  = 'avatarPreview';
                img.src = ev.target.result;
                img.style.cssText = 'display:block;width:72px;height:72px;border-radius:20px;object-fit:cover;user-select:none;';
                existing.replaceWith(img);
            }
        };
        reader.readAsDataURL(file);

        avatarForm?.submit();
    });
});
</script>
@endpush
