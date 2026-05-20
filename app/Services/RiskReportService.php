<?php

namespace App\Services;

use App\Domain\Enums\ApprovalStatus;
use App\Domain\Enums\ResolutionStatus;
use App\Domain\Enums\RoleCategory;
use App\Domain\Rules\ApprovalRule;
use App\Models\RiskReport;
use App\Models\RiskReportLog;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class RiskReportService
{
    public function __construct(
        protected KodeLaporanService $kodeLaporanService,
        protected NotificationService $notificationService,
        protected ApprovalRule $approvalRule,
    ) {}

    /**
     * Buat laporan risiko baru.
     *
     * @param array $data Data dari StoreRiskReportRequest (sudah tervalidasi)
     * @param User $user User yang membuat laporan
     * @return RiskReport
     *
     * @throws \Exception
     */
    public function create(array $data, User $user): RiskReport
    {
        $roleCategory = RoleCategory::tryFrom($user->roleCategory() ?? '');
        $targetApproval = $this->approvalRule->determineInitialStatus($roleCategory ?? RoleCategory::Maker);

        // XSS Sanitization: strip_tags() dilakukan di sini (setelah validasi lolos)
        // sehingga validasi min_words:20 tetap berjalan di data asli
        $sanitized = [
            'kronologis_kejadian' => strip_tags($data['kronologis_kejadian']),
            'mitigasi_tambahan' => isset($data['mitigasi_tambahan']) ? strip_tags($data['mitigasi_tambahan']) : null,
            'other_item_description' => isset($data['other_item_description']) ? strip_tags($data['other_item_description']) : null,
            'other_cause_description' => isset($data['other_cause_description']) ? strip_tags($data['other_cause_description']) : null,
            'dampak_non_finansial' => isset($data['dampak_non_finansial']) ? strip_tags($data['dampak_non_finansial']) : null,
        ];

        $report = RiskReport::create([
            'kode_laporan' => $this->kodeLaporanService->generate($user),
            'user_id' => $user->id,
            'branch_id' => $user->branch_id,
            'kategori' => $data['kategori'],
            'tanggal_kejadian' => $data['tanggal_kejadian'],
            'tanggal_diketahui' => $data['tanggal_diketahui'],
            'risk_item_id' => $data['risk_item_id'],
            'other_item_description' => $sanitized['other_item_description'],
            'risk_cause_id' => $data['risk_cause_id'],
            'other_cause_description' => $sanitized['other_cause_description'],
            'kronologis_kejadian' => $sanitized['kronologis_kejadian'],
            'mitigasi_tambahan' => $sanitized['mitigasi_tambahan'],
            'durasi_penyelesaian' => $data['durasi_penyelesaian'] ?? null,
            'durasi_satuan' => $data['durasi_satuan'] ?? null,
            'dampak_finansial' => $data['dampak_finansial'] ?? 0,
            'dampak_non_finansial' => $sanitized['dampak_non_finansial'],
            'skala_dampak' => $data['skala_dampak'] ?? null,
            'sumber_risiko' => $data['sumber_risiko'] ?? null,
            'approval_status' => $targetApproval,
            'resolution_status' => $data['status_awal'],
        ]);



        // Log pertama: laporan dibuat
        $report->logs()->create([
            'user_id' => $user->id,
            'note' => 'Laporan dibuat',
            'status_after_note' => $targetApproval,
            'old_data' => null,
        ]);

        // Log penanganan awal jika ada (XSS sanitized)
        if (!empty($data['tindakan_awal'])) {
            $report->logs()->create([
                'user_id' => $user->id,
                'note' => 'Penanganan Awal: ' . strip_tags($data['tindakan_awal']),
                'status_after_note' => $data['status_awal'],
            ]);
        }


        // Notifikasi ke Kacab jika perlu approval
        if ($targetApproval === ApprovalStatus::PendingKacab->value) {
            $this->notifyKacabBranch($report, $user, 'new_report',
                "Laporan baru dari {$user->name}: {$report->kode_laporan}"
            );
        }


        return $report;
    }

    /**
     * Setujui laporan (Kacab).
     */
    public function approve(RiskReport $report, User $user): void
    {
        $this->approvalRule->validateTransition(
            ApprovalStatus::tryFrom($report->approval_status) ?? ApprovalStatus::PendingKacab,
            ApprovalStatus::Approved
        );

        $report->update([
            'approval_status' => ApprovalStatus::Approved->value,
            'revision_note' => null,
        ]);

        RiskReportLog::create([
            'risk_report_id' => $report->id,
            'user_id' => $user->id,
            'note' => 'Laporan disetujui oleh Kacab',
            'status_after_note' => ApprovalStatus::Approved->value,
            'old_data' => null,
        ]);

        $this->notificationService->notifyUser($report->user, 'approved',
            "Laporan {$report->kode_laporan} telah disetujui oleh {$user->name}.",
            $report->id
        );
    }

    /**
     * Minta revisi laporan (Kacab reject → need_revision).
     */
    public function requestRevisionFromKacab(RiskReport $report, User $user, string $alasan): void
    {
        $this->approvalRule->validateTransition(
            ApprovalStatus::tryFrom($report->approval_status) ?? ApprovalStatus::PendingKacab,
            ApprovalStatus::NeedRevision
        );

        $report->update([
            'approval_status' => ApprovalStatus::NeedRevision->value,
            'revision_note' => $alasan,
        ]);

        $report->logs()->create([
            'user_id' => $user->id,
            'note' => 'Revisi diminta oleh Kacab: ' . $alasan,
            'status_after_note' => ApprovalStatus::NeedRevision->value,
            'old_data' => null,
        ]);

        $this->notificationService->notifyUser($report->user, 'rejected',
            "Laporan {$report->kode_laporan} perlu direvisi. Alasan: {$alasan}",
            $report->id
        );
    }

    /**
     * Minta revisi laporan (ManRisk request revision).
     */
    public function requestRevisionFromManRisk(RiskReport $report, User $user, string $revisionNote): void
    {
        $this->approvalRule->validateTransition(
            ApprovalStatus::tryFrom($report->approval_status) ?? ApprovalStatus::Approved,
            ApprovalStatus::NeedRevision
        );

        $report->update([
            'approval_status' => ApprovalStatus::NeedRevision->value,
            'revision_note' => $revisionNote,
        ]);

        $report->logs()->create([
            'user_id' => $user->id,
            'note' => 'Revisi diminta oleh ManRisk: ' . $revisionNote,
            'status_after_note' => ApprovalStatus::NeedRevision->value,
            'old_data' => null,
        ]);

        $this->notificationService->notifyUser($report->user, 'revision_requested',
            "Laporan {$report->kode_laporan} perlu direvisi. Catatan: {$revisionNote}",
            $report->id
        );
    }

    /**
     * Kirim revisi laporan (Maker/Kacab).
     */
    public function submitRevision(RiskReport $report, User $user, array $data, array $oldData): void
    {
        // Tentukan status baru berdasarkan siapa yang minta revisi
        $lastLog = $report->logs()->latest()->first();
        $newStatus = $this->approvalRule->determineRevisionTarget($lastLog?->note);

        $this->approvalRule->validateTransition(
            ApprovalStatus::NeedRevision,
            $newStatus
        );

        $report->update([
            'kronologis_kejadian' => $data['kronologis_kejadian'],
            'dampak_finansial' => $data['dampak_finansial'] ?? 0,
            'skala_dampak' => $data['skala_dampak'] ?? null,
            'dampak_non_finansial' => $data['dampak_non_finansial'] ?? null,
            'mitigasi_tambahan' => $data['mitigasi_tambahan'] ?? null,
            'durasi_penyelesaian' => $data['durasi_penyelesaian'] ?? null,
            'durasi_satuan' => $data['durasi_satuan'] ?? null,
            'sumber_risiko' => $data['sumber_risiko'] ?? $report->sumber_risiko,
            'approval_status' => $newStatus->value,
            'revision_note' => null,
        ]);


        // Simpan snapshot old_data ke log
        $report->logs()->create([
            'user_id' => $user->id,
            'note' => 'Revisi laporan telah dikirim',
            'status_after_note' => $newStatus->value,
            'old_data' => json_encode($oldData),
        ]);

        // Notif ke reviewer
        if ($newStatus === ApprovalStatus::PendingKacab) {
            $this->notifyKacabBranch($report, $user, 'revision_submitted',
                "Revisi laporan {$report->kode_laporan} telah dikirim oleh {$user->name}."
            );
        } else {
            $this->notifyManRisk($report, $user, 'revision_submitted',
                "Revisi laporan {$report->kode_laporan} telah dikirim oleh {$user->name}."
            );
        }
    }

    /**
     * Setujui revisi (ManRisk).
     */
    public function approveRevision(RiskReport $report, User $user): void
    {
        $this->approvalRule->validateTransition(
            ApprovalStatus::tryFrom($report->approval_status) ?? ApprovalStatus::PendingRevision,
            ApprovalStatus::Approved
        );

        $report->update([
            'approval_status' => ApprovalStatus::Approved->value,
            'revision_note' => null,
        ]);

        $report->logs()->create([
            'user_id' => $user->id,
            'note' => 'Revisi disetujui oleh ManRisk',
            'status_after_note' => ApprovalStatus::Approved->value,
            'old_data' => null,
        ]);

        $this->notificationService->notifyMaker($report, 'approved',
            "Revisi laporan {$report->kode_laporan} telah disetujui oleh ManRisk."
        );

    }

    /**
     * Update resolution status (tindak lanjut).
     */
    public function updateResolution(RiskReport $report, User $user, string $newStatus): void
    {
        $oldStatus = $report->resolution_status;

        // Validasi transisi resolution status
        $fromStatus = ResolutionStatus::tryFrom($oldStatus) ?? ResolutionStatus::Open;
        $toStatus = ResolutionStatus::tryFrom($newStatus) ?? ResolutionStatus::Open;

        if (!$fromStatus->canTransitionTo($toStatus)) {
            throw new \DomainException(
                "Transisi resolution status tidak valid: dari '{$oldStatus}' ke '{$newStatus}'."
            );
        }

        $report->update(['resolution_status' => $newStatus]);

        Log::channel('daily')->info('[AUDIT] Resolution status updated', [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'report_id' => $report->id,
            'kode_laporan' => $report->kode_laporan,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
        ]);
    }

    /**
     * Tambah progress (note + status) ke laporan.
     */
    public function addProgress(RiskReport $report, User $user, string $note, string $newStatus): void
    {
        $report->logs()->create([
            'user_id' => $user->id,
            'note' => $note,
            'status_after_note' => $newStatus,
        ]);

        $report->update(['resolution_status' => $newStatus]);

        // Notifikasi ke Maker jika laporan di-closed
        if ($newStatus === ResolutionStatus::Closed->value) {
            $this->notificationService->notifyMaker($report, 'closed',
                "Laporan {$report->kode_laporan} telah ditutup oleh {$user->name}."
            );
        }

    }


    // ========================================================================
    // PRIVATE HELPERS
    // ========================================================================

    /**
     * Kirim notifikasi ke semua user Kacab di cabang yang sama.
     */
    private function notifyKacabBranch(RiskReport $report, User $user, string $type, string $message): void
    {
        $this->notificationService->notifyKacabBranch(
            $report->branch_id,
            $type,
            $message,
            $report->id
        );
    }

    /**
     * Kirim notifikasi ke semua user ManRisk.
     */
    private function notifyManRisk(RiskReport $report, User $user, string $type, string $message): void
    {
        $this->notificationService->notifyManRisk(
            $type,
            $message,
            $report->id
        );
    }

}
