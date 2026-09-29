<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\System;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function create(System $system)
    {
        abort_if(auth()->user()->cannot('kelola sistem'), 403);

        return view('systems.plans.create', compact('system'));
    }

    public function store(Request $request, System $system)
    {
        abort_if(auth()->user()->cannot('kelola sistem'), 403);

        $validated = $this->validated($request);

        $system->plans()->create($validated);

        return redirect()
            ->route('systems.edit', $system->id)
            ->with('success', 'Plan berhasil ditambahkan.');
    }

    public function edit(System $system, Plan $plan)
    {
        abort_if(auth()->user()->cannot('kelola sistem'), 403);

        $this->ensurePlanBelongsToSystem($system, $plan);

        return view('systems.plans.edit', compact('system', 'plan'));
    }

    public function update(Request $request, System $system, Plan $plan)
    {
        abort_if(auth()->user()->cannot('kelola sistem'), 403);

        $this->ensurePlanBelongsToSystem($system, $plan);

        $validated = $this->validated($request);

        // Sengaja TIDAK mengubah duration_days/price pada subscription yang sudah berjalan --
        // perubahan cuma berlaku untuk perpanjangan berikutnya. Kalau ada subscription aktif
        // yang pakai plan ini, kasih tahu dulu supaya admin sadar dampaknya.
        $activeCount = $plan->subscriptions()
            ->whereIn('status', ['active', 'segera_berakhir'])
            ->count();

        $plan->update($validated);

        $message = 'Plan berhasil diperbarui.';
        if ($activeCount > 0) {
            $message .= " Catatan: {$activeCount} langganan aktif memakai plan ini, perubahan harga/durasi baru berlaku saat mereka perpanjang.";
        }

        return redirect()->route('systems.edit', $system->id)->with('success', $message);
    }

    public function destroy(System $system, Plan $plan)
    {
        abort_if(auth()->user()->cannot('kelola sistem'), 403);

        $this->ensurePlanBelongsToSystem($system, $plan);

        if ($plan->subscriptions()->exists()) {
            return response()->json([
                'status'  => 'failed',
                'message' => 'Plan ini masih dipakai/pernah dipakai oleh subscription, tidak bisa dihapus. Nonaktifkan saja.',
            ], 422);
        }

        $plan->delete();

        return response()->json(['status' => 'success', 'message' => 'Plan berhasil dihapus.']);
    }

    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'price'         => ['required', 'numeric', 'min:0'],
            'duration_days' => ['required', 'integer', 'min:1'],
            'is_active'     => ['sometimes', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        return $validated;
    }

    /** Cegah akses plan lewat system_id yang salah di URL (mis. /systems/1/plans/2/edit padahal plan 2 milik system 5). */
    private function ensurePlanBelongsToSystem(System $system, Plan $plan): void
    {
        abort_if($plan->system_id !== $system->id, 404);
    }
}