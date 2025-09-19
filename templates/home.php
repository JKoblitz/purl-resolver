<h1>DSMZ PURL Service</h1>
<p>This service provides persistent URLs (PURLs) for various DSMZ resources and ontologies.</p>
<h2>Usage</h2>
<p>Append paths like <code>/bacdive/strain/1234</code> or <code>/schema/TermName</code> to the base URL.</p>
<h2>Examples</h2>
<ul>
    <li><a href="<?= htmlspecialchars($config['base_url'] . '/bacdive/strain/1234') ?>"><?= htmlspecialchars($config['base_url'] . '/bacdive/strain/1234') ?></a></li>
    <li><a href="<?= htmlspecialchars($config['base_url'] . '/mediadive/medium/M123') ?>"><?= htmlspecialchars($config['base_url'] . '/mediadive/medium/M123') ?></a></li>
    <li><a href="<?= htmlspecialchars($config['base_url'] . '/brenda/enzyme/1') ?>"><?= htmlspecialchars($config['base_url'] . '/brenda/enzyme/1') ?></a></li>
</ul>

<p>You can also browse the ontology using the schema endpoint:</p>
<ul>
    <li><a href="<?= htmlspecialchars($config['base_url'] . '/schema/Strain') ?>"><?= htmlspecialchars($config['base_url'] . '/schema/Strain') ?></a></li>
</ul>