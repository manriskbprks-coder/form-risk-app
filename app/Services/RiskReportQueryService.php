<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\RiskReport;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class RiskReportQueryService
{
    /**
     * Terapkan role-based scoping ke query RiskReport.
     *
     * @param Builder $query
     * @param User $user
     * @return Builder
     */
    public function applyRoleScope(Builder $query, User $user): Builder
    {
        $roleCategory = $user->roleCategory();

        if ($roleCategory === 'checker') {
            $query->where('branch_id', $user->branch_id);
        } elseif ($roleCategory === 'viewer') {
            $branchIds = Branch::where('korwil_id', $user->id)
                ->whereRaw('is_active = true')
                ->pluck('id');
            $query->whereIn('branch_id', $branchIds);
        } elseif ($roleCategory === 'maker') {
            $query->where('user_id', $user->id);
        }
        // Admin: no filter (lihat semua)

        return $query;
    }

    /**
     * Dapatkan branch IDs yang bisa dilihat user berdasarkan role.
     *
     * @param User $user
     * @return \Illuminate\Support\Collection
     */
    public function getAccessibleBranchIds(User $user): \Illuminate\Support\Collection
    {
        $roleCategory = $user->roleCategory();

        if ($roleCategory === 'checker') {
            return collect([$user->branch_id]);
        } elseif ($roleCategory === 'viewer') {
            return Branch::where('korwil_id', $user->id)
                ->whereRaw('is_active = true')
                ->pluck('id');
        } elseif ($roleCategory === 'admin') {
            return Branch::whereRaw('is_active = true')->pluck('id');
        }

        return collect(); // maker: no branch scope
    }

    /**
     * Terapkan search 7 fields ke query RiskReport.
     * Mencari di: kode_laporan, other_item_description, other_cause_description,
     * kronologis_kejadian, user.name, item.nama_risiko, cause.penyebab
     *
     * @param Builder $query
     * @param string $search
     * @return Builder
     */
    public function applySearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('kode_laporan', 'like', "%{$search}%")
              ->orWhere('other_item_description', 'like', "%{$search}%")
              ->orWhere('other_cause_description', 'like', "%{$search}%")
              ->orWhere('kronologis_kejadian', 'like', "%{$search}%")
              ->orWhereHas('user', function ($uq) use ($search) {
                  $uq->where('name', 'like', "%{$search}%");
              })
              ->orWhereHas('item', function ($iq) use ($search) {
                  $iq->where('nama_risiko', 'like', "%{$search}%");
              })
              ->orWhereHas('cause', function ($cq) use ($search) {
                  $cq->where('penyebab', 'like', "%{$search}%");
              });
        });
    }

    /**
     * Terapkan filter dari request ke query RiskReport.
     * Filters: branch_id, kategori, jabatan, date_from/date_to, status
     *
     * @param Builder $query
     * @param Request $request
     * @param User $user
     * @return Builder
     */
    public function applyFilters(Builder $query, Request $request, User $user): Builder
    {
        $roleCategory = $user->roleCategory();

        if ($request->filled('search')) {
            $query = $this->applySearch($query, $request->search);
        }

        if ($request->filled('branch_id') && in_array($roleCategory, ['admin', 'viewer'])) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        if ($request->filled('jabatan')) {
            $query->whereHas('item', function ($q) use ($request) {
                $q->where('role_target', $request->jabatan);
            });
        }

        // Terima date_from/date_to (dari form) ATAU start_date/end_date (backward compatible)
        $dateFrom = $request->date_from ?? $request->start_date;
        $dateTo = $request->date_to ?? $request->end_date;

        if ($request->filled('date_from') || $request->filled('start_date')) {
            $query->where('tanggal_kejadian', '>=', $dateFrom);
        }

        if ($request->filled('date_to') || $request->filled('end_date')) {
            $query->where('tanggal_kejadian', '<=', $dateTo);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return $query;
    }

    /**
     * Buat query dasar RiskReport dengan eager loading standar.
     *
     * @return Builder
     */
    public function baseQuery(): Builder
    {
        return RiskReport::with(['user', 'item', 'cause.mitigations', 'branch']);
    }

    /**
     * Dapatkan daftar cabang yang relevan untuk user di halaman index.
     *
     * @param User $user
     * @return \Illuminate\Support\Collection
     */
    public function getBranchesForUser(User $user): \Illuminate\Support\Collection
    {
        $roleCategory = $user->roleCategory();

        if ($roleCategory === 'viewer') {
            $branchIds = Branch::where('korwil_id', $user->id)->pluck('id');
            return Branch::whereIn('id', $branchIds)->get();
        } elseif ($roleCategory === 'admin') {
            return Branch::all();
        }

        return collect();
    }
}
