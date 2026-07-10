<div class="toolbar">
    <div>
        <span class="eyebrow">Preview dokumen</span>
        <h2><?= esc(strtoupper($documentType)) ?> - <?= esc($event['title']) ?></h2>
    </div>
    <div class="button-row">
        <a class="ghost-button" href="<?= site_url('generate-documents/' . $documentType) ?>">Kembali</a>
        <?php if (! empty($document['generated_path']) || ! empty($document['content'])): ?>
            <a class="primary-button" href="<?= site_url('generate-documents/' . $event['id'] . '/' . $documentType . '/download') ?>">Download Word</a>
        <?php endif; ?>
    </div>
</div>

<section class="detail-layout">
    <article class="panel document-preview-panel">
        <div class="document-preview-content">
            <?= nl2br(esc($document['content'] ?? 'Dokumen belum memiliki isi.')) ?>
        </div>
    </article>

    <aside class="panel">
        <span class="eyebrow">Metadata</span>
        <div class="doc-preview-meta">
            <div>
                <small>Status</small>
                <strong><?= esc($document['status'] ?? '-') ?></strong>
            </div>
            <div>
                <small>Template</small>
                <strong><?= esc($document['template_name'] ?? 'Default sistem') ?></strong>
            </div>
            <div>
                <small>Model AI</small>
                <strong><?= esc($document['ai_model'] ?? '-') ?></strong>
            </div>
            <div>
                <small>Dibuat</small>
                <strong><?= esc($document['generated_at'] ?? '-') ?></strong>
            </div>
        </div>
        <?php if (! empty($document['ai_notes'])): ?>
            <div class="info-box">
                <?= esc($document['ai_notes']) ?>
            </div>
        <?php endif; ?>
    </aside>
</section>
