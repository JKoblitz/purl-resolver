<h1><?= htmlspecialchars($label) ?></h1>
<p class="meta"><code><?= htmlspecialchars($termIri ?? '') ?></code></p>

<?php if (!empty($types)): ?>
    <p><strong>Type:</strong>
        <?php foreach ($types as $t): ?>
            <span class="badge"><?= htmlspecialchars($t) ?></span>
        <?php endforeach; ?>
    </p>
<?php endif; ?>

<?php if (!empty($subClasses)): ?>
    <p><strong>Subclass of:</strong>
        <?php foreach ($subClasses as $sc): ?>
            <?php if ($sc['internal'] && $sc['local']): ?>
                <a href="<?= $config['base_url'] ?>/schema/<?= htmlspecialchars($sc['local']) ?>"
                    data-iri="<?= htmlspecialchars($sc['iri']) ?>"
                    class=""><?= htmlspecialchars($sc['label']) ?></a>
            <?php else: ?>
                <a href="<?= htmlspecialchars($sc['iri']) ?>" target="_blank" rel="noopener"
                    data-iri="<?= htmlspecialchars($sc['iri']) ?>" class="">
                    <?= htmlspecialchars($sc['label']) ?>
                    <i class="ph ph-arrow-up-right"></i>
                </a>
            <?php endif; ?>
        <?php endforeach; ?>
    </p>

<?php endif; ?>

<?php if (!empty($definition)): ?>
    <p><?= nl2br(htmlspecialchars($definition)) ?></p>
<?php elseif (!empty($comment)): ?>
    <p><?= nl2br(htmlspecialchars($comment)) ?></p>
<?php endif; ?>

<?php if (!empty($seeAlso)): ?>
    <p><strong>See also:</strong>
        <?php foreach ($seeAlso as $sa): ?>
            <?php if ($sa['internal'] && $sa['local']): ?>
                <a href="<?= $config['base_url'] ?>/schema/<?= htmlspecialchars($sa['local']) ?>"
                    data-iri="<?= htmlspecialchars($sa['iri']) ?>"
                    class=" enrich"><?= htmlspecialchars($sa['label']) ?></a>
            <?php else: ?>
                <a href="<?= htmlspecialchars($sa['iri']) ?>" target="_blank" rel="noopener"
                    data-iri="<?= htmlspecialchars($sa['iri']) ?>" class=" enrich">
                    <?= htmlspecialchars($sa['label']) ?>
                    <i class="ph ph-arrow-up-right"></i>
                </a>
            <?php endif; ?>
    <div class="meta js-desc" data-for="<?= htmlspecialchars($sa['iri']) ?>"></div>
<?php endforeach; ?>
</p>
<?php endif; ?>

<!-- show children -->
<?php if (!empty($children)): ?>
    <b>Subclasses:</b>
    <ul class="list">
        <?php foreach ($children as $ch): ?>
            <li>
                <?php if ($ch['internal'] && $ch['local']): ?>
                    <a href="<?= $config['base_url'] ?>/schema/<?= htmlspecialchars($ch['local']) ?>"
                        data-iri="<?= htmlspecialchars($ch['iri']) ?>"
                        class="enrich"><?= htmlspecialchars($ch['label']) ?></a>
                <?php else: ?>
                    <a href="<?= htmlspecialchars($ch['iri']) ?>" target="_blank" rel="noopener"
                        data-iri="<?= htmlspecialchars($ch['iri']) ?>" class="enrich">
                        <?= htmlspecialchars($ch['label']) ?>
                        <i class="ph ph-arrow-up-right"></i>
                    </a>
                <?php endif; ?>
                <div class="meta js-desc" data-for="<?= htmlspecialchars($ch['iri']) ?>"></div>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<hr>
<p>Machine-readable:
    <a href="?format=turtle">Turtle</a> ·
    <a href="?format=jsonld">JSON-LD</a> ·
    <a href="?format=rdfxml">RDF/XML</a>
</p>

<script>
    // sehr leichtgewichtiges Enrichment externer IRIs via JSON-LD
    // Strategie: Für Links mit .enrich -> hol JSON-LD (falls CORS und Content vorhanden),
    // extrahiere rdfs:label / dct:title / skos:prefLabel und kurze description.

    const PRED_LABELS = [
        'http://www.w3.org/2000/01/rdf-schema#label',
        'http://purl.org/dc/terms/title',
        'http://www.w3.org/2004/02/skos/core#prefLabel',
    ];
    const PRED_DESCS = [
        'http://purl.org/dc/terms/description',
        'http://www.w3.org/2000/01/rdf-schema#comment',
        'http://www.w3.org/2004/02/skos/core#definition',
    ];

    function pick(obj, preds) {
        for (const p of preds) {
            if (obj[p]) {
                const v = obj[p];
                if (Array.isArray(v) && v.length) {
                    return v[0]['@value'] || v[0]['@id'] || null;
                }
                if (typeof v === 'object') {
                    return v['@value'] || v['@id'] || null;
                }
                if (typeof v === 'string') return v;
            }
        }
        return null;
    }

    async function enrichOne(a) {
        const iri = a.dataset.iri;
        console.log(iri);
        // interne IRIs brauchen wir nicht anzufragen
        if (!iri || iri.startsWith('<?= htmlspecialchars(rtrim($baseIri ?? '', '/')) ?>/')) return;

        try {
            const resp = await fetch(iri, {
                headers: {
                    'Accept': 'application/ld+json, application/json;q=0.9'
                }
            });
            if (!resp.ok) return;
            const data = await resp.json();
            console.log(data);
            // JSON-LD kann Objekt oder @graph sein
            const candidates = Array.isArray(data) ? data :
                (data['@graph'] ? data['@graph'] : [data]);

            // passendes Subjekt suchen (gleiches IRI oder erstes Objekt)
            let node = candidates.find(n => n['@id'] === iri) || candidates[0];
            if (!node) return;

            const lbl = pick(node, PRED_LABELS);
            if (lbl && a.textContent.trim() !== lbl) a.textContent = lbl;

            const desc = pick(node, PRED_DESCS);
            if (desc) {
                const box = document.querySelector(`.js-desc[data-for="${CSS.escape(iri)}"]`);
                if (box) box.textContent = desc;
            }
        } catch (e) {
            // still quietly
            console.warn(e);
        }
    }

    document.querySelectorAll('a.enrich').forEach(enrichOne);
</script>