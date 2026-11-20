<?php
$selectedDocumentType = $selectedDocumentType ?? 'proposal';
$documentLabel = $selectedDocumentType === 'report' ? 'LPJ / Laporan' : 'Proposal';
$documentItems = $selectedDocumentType === 'report' ? ($reports ?? []) : ($proposals ?? []);
$documentTemplates = $documentTemplates ?? [];
$documentMap = [];
foreach ($documentItems as $item) {
    $documentMap[$item['event_id']] = $item;
}
?>

<div class="toolbar">
    <div>
        <span class="eyebrow">Generate Otomatis</span>
        <h2><?= esc($documentLabel) ?> dengan AI</h2>
    </div>
    <span class="status <?= $geminiConfigured ? 'status-aktif' : 'status-diajukan' ?>">
        Gemini <?= $geminiConfigured ? esc($geminiModel) : 'belum dikonfigurasi' ?>
    </span>
</div>

<nav class="document-subnav" aria-label="Sub menu dokumen">
    <a class="ghost-button" href="<?= site_url('documents') ?>">Dokumen</a>
    <a class="ghost-button <?= $selectedDocumentType === 'proposal' ? 'is-active' : '' ?>" href="<?= site_url('generate-documents/proposal') ?>">Generate Proposal</a>
    <a class="ghost-button <?= $selectedDocumentType === 'report' ? 'is-active' : '' ?>" href="<?= site_url('generate-documents/report') ?>">Generate LPJ / Laporan</a>
    <a class="ghost-button" href="<?= site_url('document-templates?type=' . urlencode($selectedDocumentType)) ?>">Template Dokumen (<?= count($documentTemplates) ?>)</a>
</nav>

<?php if (! $geminiConfigured): ?>
    <div class="alert error">
        Isi <strong>GEMINI_API_KEY</strong> di file .env agar fitur generate otomatis dapat memakai AI.
    </div>
<?php endif; ?>

<section class="document-list">
    <?php foreach ($events as $event): ?>
        <?php
        $document = $documentMap[$event['id']] ?? null;
        $documentStatus = (string) ($document['status'] ?? '');
        $hasGenerated = ! empty($document['generated_path'])
            || ! empty($document['generated_at'])
            || in_array($documentStatus, ['siap review ai', 'siap export'], true);
        ?>
        <details class="document-list-item document-drawer">
            <summary class="document-event-summary">
                <div>
                    <h3><?= esc($event['title']) ?></h3>
                    <small><?= esc($event['campus']) ?> - <?= esc($event['date_label'] ?? date('d M Y', strtotime($event['date']))) ?></small>
                </div>
                <span class="ghost-button compact-button">Generate <?= esc($documentLabel) ?></span>
            </summary>

            <div class="document-generate-panel">
                <div class="document-generate-meta">
                    <span class="status"><?= esc($document['status'] ?? 'belum dibuat') ?></span>
                    <?php if (! empty($document['generated_at'])): ?>
                        <small>Terakhir dibuat <?= esc(substr((string) $document['generated_at'], 0, 16)) ?></small>
                    <?php else: ?>
                        <small>Isi ringkasan kegiatan, lalu AI akan menyusun seluruh struktur <?= esc($documentLabel) ?>.</small>
                    <?php endif; ?>
                </div>

                <form class="ai-structure-form is-summary-only" action="<?= site_url('generate-documents/' . $event['id'] . '/' . $selectedDocumentType . '/generate') ?>" method="post">
                    <?= csrf_field() ?>
                    <?php
                    $evCatLower = strtolower(trim((string) ($event['category'] ?? '')));
                    $recommendedTplId = 0;
                    foreach ($documentTemplates as $tpl) {
                        if ($evCatLower !== '' && strtolower(trim((string) ($tpl['event_category'] ?? ''))) === $evCatLower) {
                            $recommendedTplId = (int) $tpl['id'];
                            break;
                        }
                    }
                    if ($recommendedTplId === 0 && ! empty($documentTemplates)) {
                        $recommendedTplId = (int) $documentTemplates[0]['id'];
                    }
                    ?>
                    <label>
                        Template Dokumen Acuan (Sesuai Upload Admin)
                        <select class="control" name="template_id">
                            <?php if (! empty($documentTemplates)): ?>
                                <?php foreach ($documentTemplates as $tpl): ?>
                                    <?php $tplId = (int) $tpl['id']; ?>
                                    <option value="<?= $tplId ?>" <?= $tplId === $recommendedTplId ? 'selected' : '' ?>>
                                        [<?= esc($tpl['event_category'] ?: 'Umum') ?>] <?= esc($tpl['name']) ?> (<?= esc($tpl['file_format'] ?: 'DOCX') ?>)
                                    </option>
                                <?php endforeach; ?>
                                <option value="0">Gunakan Struktur Standar Sistem</option>
                            <?php else: ?>
                                <option value="0">Struktur Standar Sistem (Belum ada template <?= esc($documentLabel) ?> terupload)</option>
                            <?php endif; ?>
                        </select>
                    </label>
                    <?php foreach (($documentStructureFields ?? []) as $field => $label): ?>
                        <label>
                            <?= esc($label) ?>
                            <textarea class="control" name="structure[<?= esc($field) ?>]" rows="7" placeholder="Contoh: seminar teknologi untuk siswa SMA/SMK, membahas AI dan karier digital, target 150 peserta, lokasi auditorium kampus, ada sesi materi, tanya jawab, dan dokumentasi." required><?= esc(old('structure.' . $field, '')) ?></textarea>
                        </label>
                    <?php endforeach; ?>

                    <div class="document-actions">
                        <button class="primary-button" type="submit" <?= $geminiConfigured ? '' : 'disabled' ?>>Generate Otomatis</button>
                        <?php if ($hasGenerated): ?>
                            <a class="ghost-button" href="<?= site_url('generate-documents/' . $event['id'] . '/' . $selectedDocumentType . '/preview') ?>">Preview</a>
                        <?php endif; ?>
                        <?php if ($hasGenerated): ?>
                            <a class="ghost-button" href="<?= site_url('generate-documents/' . $event['id'] . '/' . $selectedDocumentType . '/download') ?>">Download Word</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </details>
    <?php endforeach; ?>

    <?php if (($events ?? []) === []): ?>
        <div class="notification-item">
            <strong>Belum ada event aktif</strong>
            <p>Generate otomatis akan muncul setelah ada event aktif.</p>
        </div>
    <?php endif; ?>
</section>
