<!doctype html>
<html lang="en">
<meta charset="utf-8">
<title><?= htmlspecialchars($label) ?> – DSMZ Schema</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="alternate" type="text/turtle" href="?format=ttl">
<link rel="alternate" type="application/ld+json" href="?format=jsonld">
<link rel="alternate" type="application/rdf+xml" href="?format=rdf">

<!-- Phosphoricons and individual D3 icons -->
<link href="<?= $config['base_url'] ?>/css/phosphoricons/regular/style.css?v=2" rel="stylesheet" />
<!-- <link href="<?= $config['base_url'] ?>/css/phosphoricons/fill/style.css?v=2" rel="stylesheet" /> -->
<link href="<?= $config['base_url'] ?>/css/d3icons/style.css?v=2" rel="stylesheet" />
<link href="<?= $config['base_url'] ?>/css/digidive.css?v=4" rel="stylesheet" />

<style>
    /* we have no bottom navbar */
    .content-wrapper>.content-container {
        min-height: calc(100vh - 16rem);
    }
</style>

<body>
    <div id="loader">
        <span></span>
    </div>

    <!-- Page wrapper start -->
    <div class="page-wrapper" data-sidebar-hidden="hidden">
        <!--  to hide sidebar on start -->

        <!-- Sticky alerts (toasts), empty container -->
        <div class="sticky-alerts"></div>

        <!-- Sidebar overlay -->
        <div class="sidebar-overlay" onclick="digidive.toggleSidebar()"></div>

        <!-- Navbar start -->
        <div class="navbar navbar-top">
            <div class="container w-600">
                <a href="//hub.dsmz.de" class="navbar-brand ml-0">
                    <!-- DSMZ Logo is mandatory -->
                    <img src="<?= $config['base_url'] ?>/img/digital-diversity.png" alt="DSMZ Digital Diversity" style="height:6rem;">
                </a>

            </div>
        </div>

        <!-- Sidebar start -->
        <div class="sidebar">
        </div>
        <!-- Sidebar end -->

        <!-- imprint modal -->
        <div class="modal" id="imprint" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <a href="#close-modal" class="close" role="button" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </a>
                    <div itemprop="provider" itemscope itemtype="https://schema.org/Organization" itemid="#dsmz">
                        <h2 class="title">Imprint</h2>

                        <b itemprop="name">Leibniz-Institut DSMZ-Deutsche Sammlung von
                            Mikroorganismen und Zellkulturen GmbH</b><br>
                        <br>
                        <div itemprop="address" itemscope itemtype="https://schema.org/PostalAddress">
                            <span itemprop="streetAddress">Inhoffenstraße 7 B</span><br>
                            <span itemprop="postalCode">38124</span>
                            <span itemprop="addressLocality">Braunschweig, Germany</span>
                        </div>
                        <br>
                        Phone: <span itemprop="telephone">+49 (0) 531-2616-0</span><br>
                        Fax: <span itemprop="telephone">+49 (0) 531-2616-418</span><br>

                        E-mail: <a href="javascript:linkTo_UnCryptMailto('nbjmup;dpoubduAcbdejwf/ef');" class="mail" itemprop="email">contact [at] bacdive [dot] de</a><br>
                        Web: <a href="https://dsmz.de" target="_blank" itemprop="url">dsmz.de</a>
                        <br>
                        Registered at: Amtsgericht Braunschweig, HRB 2570<br>
                        <br>
                        Scientific Interim Director: Prof. Dr. Yvonne Mast<br>
                        Administrative Managing Director: Bettina Fischer
                        <br>
                        EU VAT Registration Number: <span itemprop="vatID">DE 114815269</span><br>
                        <br>
                        Editor responsible for the content of the web site: Bettina Fischer<br>
                        <br>
                        Conception and development: Julia Koblitz<br />
                    </div>

                    <h2 class="title mt-20" id="privacy">Privacy Policy</h2>
                    <div class="text-justify">
                        The protection of your personal data and thus your privacy in the use of our network
                        presence is important to us - in accordance with the General Data Protection Regulation
                        ("GDPR"). We only obtain data automatically sent to us by your browser,
                        in particular the name of your internet service provider, the page on the internet from which
                        you are visiting us, the pages you are visiting and when you do so. We need this information in order
                        to maintain the function of our website.
                    </div>

                    <h2 class="title mt-20" id="termsofuse">Terms of use</h2>
                    <div class="text-justify">
                        <p>
                            All information contained on the web
                            site is carefully checked for accuracy and is continuously up-dated.
                            However, we cannot guarantee the completeness, the correctness and the
                            topicality. Liability claims regarding damage caused by the use of any
                            information provided, including any kind of information which is
                            incomplete or incorrect, will therefore be rejected.
                        </p>
                        <p>
                            According to the verdict passed by the District Court in Hamburg the
                            owner of a website is responsible for the contents which appear in the
                            links. This can only be prevented when the owner explicitly dissociates
                            itself to these contents. We have placed links to other homepages in
                            the internet on our website and we would like to emphasize that we have
                            no influence on the contents and design of these pages. Therefore,
                            herewith we explicitly dissociate ourselves from all the contents
                            linked to our homepage and that we take no responsibility for these
                            contents. This statement applies to all the links used on our homepage.
                        </p>

                        <div class="text-right mt-20">
                            <a href="#close-modal" class="btn mr-5" role="button">Close</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content wrapper start -->
        <div class="content-wrapper">
            <!-- OPTIONAL: title-bar -->
            <div class="content-container">
                <div class="container w-600">