<?php
return [
    // base URL
    'base_url' => '/purl',
  // Simple redirect patterns (Regex => Target with $1, $2…)
  'redirects' => [
    // BACDIVE
    '#^/bacdive/strain/(\d+)$#' => 'https://bacdive.dsmz.de/strain/$1',
    '#^/bacdive/(.+)$#' => 'https://bacdive.dsmz.de/purl/$1', # catch-all for bacdive
    // MEDIADIVE
    '#^/mediadive/medium/(.+)$#' => 'https://mediadive.dsmz.de/medium/$1',
    '#^/mediadive/strain/(.+)$#' => 'https://mediadive.dsmz.de/strains/view/$1',
    '#^/mediadive/ingredient/(.+)$#' => 'https://mediadive.dsmz.de/ingredients/$1',
    '#^/mediadive/solution/(.+)$#' => 'https://mediadive.dsmz.de/solutions/S$1',
    '#^/mediadive/(.+)$#' => 'https://mediadive.dsmz.de/purl/$1', # catch-all for mediadive
    // BRENDA
    // "#^/brenda/activator/(\d+)$#" => 'https://brenda-enzymes.org/$1',
    // "#^/brenda/tissue/(\d+)$#" => 'https://brenda-enzymes.org/$1',
    // "#^/brenda/organism/(\d+)$#" => 'https://brenda-enzymes.org/taxonomy.php?f[id][value]=$1', // currently only tax-ID
    // "#^/brenda/compound/(\d+)$#" => 'https://brenda-enzymes.org/ligand.php?brenda_ligand_id=$1', // does not seem to work, neither group nor ligand ID
    // "#^/brenda/cofactor/(\d+)$#" => 'https://brenda-enzymes.org/$1',
    "#^/brenda/ec/(\d+\.\d+\.\d+\.\d+)$#" => 'https://brenda-enzymes.org/enzyme.php?ecno=$1',
    // "#^/brenda/enzyme/(\d+)$#" => "https://brenda-enzymes.org/enzyme.php?ecno=$1",
    // "#^/brenda/inhibitor/(\d+)$#" => 'https://brenda-enzymes.org/$1',
    // "#^/brenda/localization/(\d+)$#" => 'https://brenda-enzymes.org/$1',
    // "#^/brenda/structure/(\d+)$#" => 'https://brenda-enzymes.org/$1',
    // "#^/brenda/pathway/(\d+)$#" => 'https://brenda-enzymes.org/pathway_index.php?pathway_id=$1', // currently only pathway name
    // "#^/brenda/product/(\d+)$#" => 'https://brenda-enzymes.org/$1',
    // "#^/brenda/reaction/I/(\d+)$#" => 'https://brenda-enzymes.org/$1',
    // "#^/brenda/reaction/S/(\d+)$#" => 'https://brenda-enzymes.org/$1',
    "#^/brenda/reference/(\d+)$#" => 'https://brenda-enzymes.org/literature.php?r=$1',
    // "#^/brenda/substrate/(\d+)$#" => 'https://brenda-enzymes.org/$1',

    '#^/brenda/enzyme/(.+)$#' => 'https://www.brenda-enzymes.org/enzyme.php?ecno=$1',
    '#^/brenda/(.+)$#' => 'https://www.brenda-enzymes.org/purl/$1', # catch-all for brenda
  ],

  // Ontologies
  'ontology' => [
    'base_iri' => 'https://purl.dsmz.de/schema/',
    'file'     => __DIR__.'/schema/D3O.ttl',   // Path to TTL
    'format'   => 'turtle'
  ],

  // allowed MIME types in negotiation
  'rdf_mime_map' => [
    'text/turtle'              => 'turtle',
    'application/ld+json'      => 'jsonld',
    'application/rdf+xml'      => 'rdfxml',
    'application/n-triples'    => 'ntriples'
  ],

  // Caching
  'cache_ttl' => 3600
];