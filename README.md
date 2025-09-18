# DSMZ PURL Resolver

[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![PHP](https://img.shields.io/badge/PHP-%5E8.1-8892BF.svg?logo=php)](https://www.php.net/)
[![Composer](https://img.shields.io/badge/Composer-install-orange.svg?logo=composer)](https://getcomposer.org/)

A lightweight **PURL (Persistent URL) resolver** written in PHP, developed during the  
[BioHackathon Japan 2025](https://2025.biohackathon.org/).

This resolver powers the [DSMZ Digital Diversity](https://www.dsmz.de/) PURL service:  
👉 <https://purl.dsmz.de/>

It handles:

- **Redirects** from persistent identifiers to the actual resource pages (e.g. BacDive, MediaDive, BRENDA).
- **Schema term resolution**: IRIs such as  
  `https://purl.dsmz.de/schema/Strain` return either human-readable HTML docs or machine-readable RDF (Turtle, JSON-LD, RDF/XML) via HTTP content negotiation.
- **Catch-alls** for resources without explicit landing pages, ensuring every PURL resolves consistently.

---

## 🚀 Installation

Clone the repository and install dependencies via Composer:

```bash
git clone https://github.com/your-org/purl-resolver.git
cd purl-resolver
composer install
```

That’s it — the resolver is now ready to serve via PHP’s built-in webserver, Apache, or Nginx with PHP-FPM.


## ⚙️ Usage

### Local development (PHP built-in server)

```bash
php -S localhost:8080 index.php
```

Now open:
- http://localhost:8080/bacdive/strain/123 → redirects to BacDive.
- http://localhost:8080/schema/Strain → renders the ontology term Strain as HTML.
- Add Accept: text/turtle / application/ld+json / application/rdf+xml headers to get RDF serializations.

### Deployment
- Point your webserver (Apache or Nginx) at the public/ or repo root where index.php lives.
- Ensure PHP 8.1+ and Composer dependencies are available.
- Configure HTTPS + reverse proxy headers if deploying behind a gateway.

⸻

## 🧩 Configuration

All resolver logic is controlled by config.php:
- `base_url`: base path if not at root, do not forget to update .htaccess if changed.
- `redirects`: regex rules mapping PURLs to their landing pages.
- `ontology`: path to your ontology file (.ttl or .nt) and base IRI.
- `rdf_mime_map`: MIME types → EasyRdf serializers.

**Examples:**
```php
'redirects' => [
  '#^/bacdive/strain/(\d+)$#' => 'https://bacdive.dsmz.de/strain/$1',
  '#^/mediadive/ingredient/(\d+)$#' => 'https://mediadive.dsmz.de/ingredients/$1',
  '#^/brenda/ec/(\d+\.\d+\.\d+\.\d+)$#' => 'https://brenda-enzymes.org/enzyme.php?ecno=$1',
],
```


## 🧪 Testing

Use curl to check content negotiation:

```bash
curl -H "Accept: text/turtle"      http://localhost:8080/schema/Strain
curl -H "Accept: application/ld+json" http://localhost:8080/schema/Strain
curl -H "Accept: application/rdf+xml" http://localhost:8080/schema/Strain
```

## 📜 License

This project is released under the [MIT License](LICENSE).


## 🙏 Acknowledgements

This resolver was initiated and implemented at the
BioHackathon Japan 2025 by Julia Koblitz and collaborators.
Special thanks to the BioHackathon community for inspiration and feedback.
