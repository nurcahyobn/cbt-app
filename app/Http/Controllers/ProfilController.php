<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfilController extends Controller
{
    /**
     * Tampilkan form edit profil.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Proses update termasuk upload foto.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        if ($request->hasFile('foto')) {
            // Hapus foto lama jika ada
            if ($user->foto && Storage::disk('public')->exists($user->foto)) {
                Storage::disk('public')->delete($user->foto);
            }

            // Simpan foto baru ke disk public
            $path = $request->file('foto')->store('foto', 'public');
            $validated['foto'] = $path;
        }

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::back()
            ->with('status', 'profile-updated')
            ->with('success', 'Profil berhasil diperbarui.');
    }

    /**
     * Hapus foto saja, tanpa hapus akun.
     */
    public function destroyFoto(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->foto) {
            if (Storage::disk('public')->exists($user->foto)) {
                Storage::disk('public')->delete($user->foto);
            }
            $user->update(['foto' => null]);
        }

        return Redirect::back()->with('success', 'Foto profil dihapus.');
    }
}
