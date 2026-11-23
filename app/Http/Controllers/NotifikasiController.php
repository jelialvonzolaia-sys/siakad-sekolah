<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotifikasiController extends Controller
{
    /**
     * Tandai satu notifikasi sebagai sudah dibaca lalu arahkan ke URL tautannya.
     */
    public function baca(Notifikasi $notifikasi): RedirectResponse
    {
        if ($notifikasi->user_id !== auth()->id()) {
            abort(403, 'Akses Ditolak.');
        }

        if (!$notifikasi->is_read) {
            $notifikasi->update(['is_read' => true]);
        }

        if ($notifikasi->tautan) {
            return redirect($notifikasi->tautan);
        }

        return redirect()->route('dashboard');
    }

    /**
     * Tandai seluruh notifikasi milik pengguna yang sedang login sebagai sudah dibaca.
     */
    public function bacaSemua(Request $request): JsonResponse|RedirectResponse
    {
        Notifikasi::where('user_id', auth()->id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'unread_count' => 0,
                'message' => 'Semua notifikasi telah ditandai sudah dibaca.',
            ]);
        }

        return redirect()->back()->with('success', 'Semua notifikasi telah ditandai sudah dibaca.');
    }
}
