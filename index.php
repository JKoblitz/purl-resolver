<?php

declare(strict_types=1);
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);

require __DIR__ . '/vendor/autoload.php';
$config = require __DIR__ . '/config.php';

use EasyRdf\Graph;
use EasyRdf\Serialiser\RdfPhp;
use EasyRdf\Format;
// ---------- kleine Helfer ----------

function bestAccept(array $supported): ?string
{
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '*/*';
    // sehr simple Gewichtung: prüfe in Konfig-Reihenfolge
    foreach (array_keys($supported) as $mime) {
        if ($accept === '*/*' || stripos($accept, $mime) !== false) return $mime;
    }
    return null;
}

function sendCachingHeaders(string $etag, int $maxAge): void
{
    header("ETag: \"$etag\"");
    header("Cache-Control: public, max-age={$maxAge}");
    if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && trim($_SERVER['HTTP_IF_NONE_MATCH'], '"') === $etag) {
        http_response_code(304);
        exit;
    }
}

function notFound(string $msg = 'Not Found'): void
{
    http_response_code(404);
    header('Content-Type: text/plain; charset=UTF-8');
    echo $msg;
    exit;
}

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
$baseUrl = $config['base_url'] ?? '';
if ($baseUrl !== '' && str_starts_with($path, $baseUrl)) {
    $path = substr($path, strlen($baseUrl));
}

// ---------- 1) Harte Redirects (PURL → Ziel) ----------
if ($path === '' || $path === '/') {
    // Root: einfache HTML-Seite
    include __DIR__ . '/templates/header.php';
    include __DIR__ . '/templates/home.php';
    include __DIR__ . '/templates/footer.php';
    exit;
}

// check if path starts with schema
if (str_starts_with($path, '/schema')) {
    // handled in next section
} else {
    // check redirects
    foreach ($config['redirects'] as $regex => $target) {
        if (preg_match($regex, $path, $m)) {
            array_shift($m);
            $url = preg_replace($regex, $target, $path);
            header('Vary: Accept');
            header('Location: ' . $url, true, 301);
            exit;
        }
    }
}


// if complete path is just /schema or /schema/, show the top level classes
if ($path === '/schema' || $path === '/schema/') {
    $baseIri = rtrim($config['ontology']['base_iri'], '/') . '/';
    $graph = new \EasyRdf\Graph();
    $ttl = @file_get_contents($config['ontology']['file']);
    if ($ttl === false) {
        notFound("Cannot read ontology file.");
    }
    $graph->parse($ttl, $config['ontology']['format'], $baseIri);
    $topClasses = [];
    foreach ($graph->allResourcesOfType('owl:Class') as $class) {
        $isTop = true;
        foreach ($graph->all($class, 'rdfs:subClassOf') as $super) {
            if ($super instanceof \EasyRdf\Resource && $super->isBNode()) {
                // ignore BNode superclasses
            } elseif ($super instanceof \EasyRdf\Resource && str_starts_with((string)$super, $baseIri)) {
                // has a superclass in the same ontology
                $isTop = false;
                break;
            }
        }
        if ($isTop) {
            $topClasses[] = [
                'iri' => (string)$class,
                'label' => $class->label() ?? preg_replace('~^.+[#/](.+)$~', '$1', (string)$class),
                'internal' => str_starts_with((string)$class, $baseIri),
                'local' => str_starts_with((string)$class, $baseIri) ? substr((string)$class, strlen($baseIri)) : null
            ];
        }
    }
    // sort by label
    usort($topClasses, fn($a, $b) => strcmp($a['label'], $b['label']));
    include __DIR__ . '/templates/header.php';  
    echo "<h1>DSMZ Ontology - Top Level Classes</h1>\n";
    foreach ($topClasses as $tc) {
        echo '<p><a href="' . htmlspecialchars($config['base_url'] . '/schema/' . $tc['local']) . '" data-iri="' . htmlspecialchars($tc['iri']) . '" class="enrich">' . htmlspecialchars($tc['label']) . '</a></p>';
    }
    include __DIR__ . '/templates/footer.php';
    exit;
}

// ---------- 2) /schema/<Term> mit Content-Negotiation ----------
if (preg_match('#^/schema/([A-Za-z0-9_\-:.]+)$#', $path, $m)) {
    $termLocal = $m[1];
    $baseIri   = rtrim($config['ontology']['base_iri'], '/') . '/';
    $termIri   = $baseIri . $termLocal;

    // Ontologie laden
    $graph = new \EasyRdf\Graph();
    $ttl = @file_get_contents($config['ontology']['file']);
    if ($ttl === false) {
        notFound("Cannot read ontology file.");
    }
    $graph->parse($ttl, $config['ontology']['format'], $baseIri);

    // Resource holen
    $res = $graph->resource($termIri);

    // *** BESSERE Existenzprüfung ***
    // Prüfe konkret auf bekannte Prädikate, statt count(properties())
    $hasAny = ($res->label() !== null)
        || (count($res->types()) > 0)
        || (count($res->propertyUris()) > 0);

    if (!$hasAny) {
        // Debug-Hilfe: logge, welche Ressourcen im Graph sind
        error_log("[PURL] No triples for $termIri; available examples:");
        $i = 0;
        foreach ($graph->resources() as $r) {
            if ($i++ > 10) break;
            error_log("  - " . $r);
        }
        notFound("Schema term not found: $termLocal");
    }

    // Negotiation
    $supported = array_merge(['text/html' => 'html'], $config['rdf_mime_map']);
    $wantMime  = bestAccept($supported) ?? 'text/html';

    // ?format=ttl|jsonld|rdf optional zulassen
    if (isset($_GET['format'])) {
        $fmt = strtolower(trim((string)$_GET['format']));
        $fmtMap = array_flip($config['rdf_mime_map']); // 'turtle' => 'text/turtle'
        if (isset($fmtMap[$fmt])) $wantMime = $fmtMap[$fmt];
    }

    // ETag / Header
    $etag = sha1($termIri . '|' . (string)@filemtime($config['ontology']['file']));
    sendCachingHeaders($etag, $config['cache_ttl']);
    header('Vary: Accept');
    header('Link: <' . $termIri . '>; rel="about"', false);
    header('Link: <' . $baseIri . '>; rel="up"', false);

    // Gemeinsame Metadaten
    $label      = $res->label() ?? $termLocal;
    $types      = array_map('strval', $res->types());
    $comment    = $res->getLiteral('rdfs:comment')?->getValue();
    $definition = $res->getLiteral('skos:definition')?->getValue()
        ?? $res->getLiteral('dcterms:description')?->getValue()
        ?? $comment;

    if ($wantMime === 'text/html') {
        header('Content-Type: text/html; charset=UTF-8');
        $label = $res->label() ?? $termLocal;
        $types = array_map(fn($t) => (string)$t, $res->types());
        $comment = $res->getLiteral('rdfs:comment')?->getValue();
        $definition = $res->getLiteral('dcterms:description')?->getValue()
            ?? $res->getLiteral('skos:definition')?->getValue()
            ?? $comment ?? null;
        // Hilfsfunktion: Label finden oder hübschen Fallback bauen
        $pretty = function (string $iri) use ($graph) {
            $r = $graph->resource($iri);
            $lbl = $r ? $r->label() : null;
            if ($lbl) return (string)$lbl;
            // letzter Segment-Teil als Fallback
            $frag = preg_replace('~^.+[#/](.+)$~', '$1', $iri);
            return $frag ?: $iri;
        };

        // subClassOf sammeln
        $subClasses = [];
        foreach ($graph->all($termIri, 'rdfs:subClassOf') as $obj) {
            $iri = (string)$obj;
            $subClasses[] = [
                'iri'   => $iri,
                'label' => $pretty($iri),
                'internal' => str_starts_with($iri, $baseIri),
                'local' => $iri && str_starts_with($iri, $baseIri) ? substr($iri, strlen($baseIri)) : null
            ];
        }

        // rdfs:seeAlso sammeln
        $seeAlso = [];
        foreach ($graph->all($termIri, 'rdfs:seeAlso') as $obj) {
            $iri = (string)$obj;
            $seeAlso[] = [
                'iri'   => $iri,
                'label' => $pretty($iri),
                'internal' => str_starts_with($iri, $baseIri),
                'local' => $iri && str_starts_with($iri, $baseIri) ? substr($iri, strlen($baseIri)) : null
            ];
        }
        $children = [];
        $termRes = $graph->resource($termIri);

        if ($termRes) {
            foreach ($graph->resourcesMatching('rdfs:subClassOf', $termRes) as $child) {
                $iri = (string)$child;
                $children[] = [
                    'iri'     => $iri,
                    'label'   => $pretty($iri),
                    'internal' => str_starts_with($iri, $baseIri),
                    'local'   => str_starts_with($iri, $baseIri) ? substr($iri, strlen($baseIri)) : null
                ];
            }
        }
        include __DIR__ . '/templates/header.php';
        include __DIR__ . '/templates/term.php';
        include __DIR__ . '/templates/footer.php';
        exit;
    } else {

        // ---- Serialisierungsvorbereitung ----
        $fmtName = $supported[$wantMime]; // z.B. 'turtle' | 'rdfxml' | 'jsonld'
        $sub = new \EasyRdf\Graph();
        $termRes = $graph->resource($termIri);
        $tripleCount = 0;

        // Helper: sicheres Hinzufügen (Literal/Resource/BNode) + Zählung
        $addTriple = function ($s, $p, $o) use ($sub, &$tripleCount) {
            if ($o instanceof \EasyRdf\Literal) {
                $sub->addLiteral(
                    (string)$s,
                    (string)$p,
                    $o->getValue(),
                    $o->getLang(),
                    $o->getDatatypeUri()
                );
            } else {
                // Resource oder BNode
                $sub->addResource((string)$s, (string)$p, (string)$o);
            }
            $tripleCount++;
        };

        // 1) Outgoing Triples (Term als Subjekt): ALLES rüberkopieren
        foreach ($termRes->propertyUris() as $prop) {
            foreach ($termRes->all($prop) as $val) {
                $addTriple($termIri, $prop, $val);

                // Wenn Objekt ein BNode ist: eine Ebene BNode-Eigenschaften mitnehmen
                if ($val instanceof \EasyRdf\Resource && $val->isBNode()) {
                    $bn = $graph->resource((string)$val);
                    foreach ($bn->propertyUris() as $bp) {
                        foreach ($bn->all($bp) as $bv) {
                            $addTriple((string)$val, $bp, $bv);
                        }
                    }
                }
            }
        }

        // 2) OPTIONAL: eingehende Tripel (x ?p term) hinzufügen (macht JSON-LD/Turtle informativer)
        foreach ($graph->resources() as $s) {
            if (!($s instanceof \EasyRdf\Resource)) continue;
            foreach ($s->propertyUris() as $p) {
                foreach ($s->all($p) as $o) {
                    if ((string)$o === $termIri) {
                        $addTriple((string)$s, $p, $termIri);
                        // Bonus: Label des Subjekts für Lesbarkeit
                        $lbl = $graph->resource((string)$s)->label();
                        if ($lbl) {
                            $sub->addLiteral((string)$s, 'rdfs:label', (string)$lbl);
                            $tripleCount++;
                        }
                    }
                }
            }
        }

        // 3) DEBUG: Tripelanzahl in Header ausgeben
        header('X-Triples: ' . $tripleCount);

        // 4) Wenn hier 0 rauskommt, wissen wir: Subgraph ist leer → sofort sichtbar via curl -i
        if ($tripleCount === 0) {
            // Hilfestellung ins Log
            var_dump('[PURL] Subgraph empty for ' . $termIri . '; props on term: ' . count($termRes->propertyUris()));
        }
        // 5) Serialisieren
        $out = null;
        if ($fmtName === 'jsonld') {
            $out = $sub->serialise('jsonld', ['indent' => true]);
        } elseif ($fmtName === 'turtle') {
            $out = $sub->serialise('turtle');
        } elseif ($fmtName === 'rdfxml') {
            $out = $sub->serialise('rdfxml');
        } elseif ($fmtName === 'ntriples') {
            $out = $sub->serialise('ntriples');
        } else {
            // Fallback: RdfPhp + var_export
            $ser = new RdfPhp();
            $data = $ser->serialise($sub, 'php');
            $out = "<?php\nreturn " . var_export($data, true) . ";\n";
        }

        if ($out === null) {
            notFound('Unable to serialise to ' . $fmtName . ' (empty or unsupported).');
        }
        header('Content-Type: ' . $wantMime . '; charset=UTF-8');
        echo $out;
        exit;
    }
}

// ---------- 3) Fallback ----------
notFound();
