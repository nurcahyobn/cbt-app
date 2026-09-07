<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Informasi Profil') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Perbarui informasi profil akun, email, dan foto profil Anda.') }}
        </p>
    </header>

    @if (session('success'))
        <div class="mt-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Foto Saat Ini & Tombol Hapus --}}
    <div class="mt-6 flex items-center gap-6">
        <div class="relative">
            @if ($user->foto)
                <img src="{{ Storage::url($user->foto) }}"
                     alt="Foto {{ $user->name }}"
                     class="w-24 h-24 rounded-full object-cover border-2 border-gray-200 shadow-sm"
                     id="current-avatar">
            @else
                <div class="w-24 h-24 rounded-full bg-teal-100 text-teal-700 flex items-center justify-center font-bold text-2xl border-2 border-gray-200 shadow-sm" id="current-avatar-placeholder">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
            @endif
        </div>

        @if ($user->foto)
            <form method="POST" action="{{ route('profile.foto.destroy') }}">
                @csrf
                @method('DELETE')
                <button type="submit"
                        onclick="return confirm('Apakah Anda yakin ingin menghapus foto profil?')"
                        class="text-sm text-red-600 hover:text-red-800 hover:underline font-medium focus:outline-none">
                    {{ __('Hapus Foto') }}
                </button>
            </form>
        @endif
    </div>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post"
          action="{{ route('profile.update') }}"
          enctype="multipart/form-data"
          class="mt-6 space-y-6"
          x-data="{
              photoPreview: null,
              previewImage(event) {
                  const file = event.target.files[0];
                  if (file) {
                      const reader = new FileReader();
                      reader.onload = (e) => { this.photoPreview = e.target.result; };
                      reader.readAsDataURL(file);
                  } else {
                      this.photoPreview = null;
                  }
              }
          }">
        @csrf
        @method('patch')

        {{-- Upload Foto Profil Baru --}}
        <div>
            <x-input-label for="foto" :value="__('Ganti Foto Profil')" />
            <input id="foto"
                   name="foto"
                   type="file"
                   accept="image/jpeg,image/png,image/jpg,image/webp"
                   @change="previewImage"
                   class="mt-1 block w-full text-sm text-gray-500 file:me-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100 cursor-pointer border border-gray-300 rounded-md p-1" />
            <p class="text-xs text-gray-500 mt-1">Format: JPG, JPEG, PNG, WebP. Ukuran maksimal 2 MB.</p>
            <x-input-error class="mt-2" :messages="$errors->get('foto')" />

            {{-- Live Preview Saat File Dipilih --}}
            <div x-show="photoPreview" class="mt-3 flex items-center gap-3" style="display: none;">
                <span class="text-xs text-gray-500">Preview:</span>
                <img :src="photoPreview" class="w-16 h-16 rounded-full object-cover border border-teal-500 shadow-sm" alt="Preview foto baru">
            </div>
        </div>

        <div>
            <x-input-label for="name" :value="__('Nama Lengkap')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Simpan Perubahan') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Tersimpan.') }}</p>
            @endif
        </div>
    </form>
</section>
