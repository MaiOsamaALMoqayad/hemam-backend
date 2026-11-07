<?php

/*
|--------------------------------------------------------------------------
| Editable public-site content
|--------------------------------------------------------------------------
|
| Every key the admin can edit in place on the public site (Hemam/). The
| API rejects any key not listed here. The default content itself lives in
| the frontend component, so a key with no saved row shows that default.
|
| Types:
|   text   plain text, line breaks allowed, any HTML stripped. 'max' = length.
|   link   {label, url}. 'max' = label length; 'label' => false for
|          icon-only links (social icons), where only the URL is kept.
|   image  uploaded through POST /admin/content/image only. 'width' = the
|          largest width kept (bigger uploads are scaled down).
|
*/

$text = fn (int $max) => ['type' => 'text', 'max' => $max];
$link = fn (int $max = 60) => ['type' => 'link', 'max' => $max];
$icon = ['type' => 'link', 'label' => false];
$image = fn (int $width) => ['type' => 'image', 'width' => $width];

return [
    'keys' => [
        // --- Site-wide ---
        'site.logo'     => $image(400),
        'site.phone'    => $text(30),
        'site.email'    => $text(100),
        'site.address'  => $text(150),
        'site.whatsapp' => $icon,

        // --- Navbar ---
        'nav.home'     => $link(30),
        'nav.programs' => $link(30),
        'nav.projects' => $link(30),
        'nav.khawater' => $link(30),
        'nav.reviews'  => $link(30),
        'nav.join'     => $link(30),
        'nav.donate'   => $link(30),
        'nav.expert'   => $link(30),
        'nav.contact'  => $link(30),

        // --- Homepage ---
        'home.hero.title'         => $text(150),
        'home.hero.subtitle'      => $text(500),
        'home.hero.background'    => $image(1920),
        'home.hero.cta_primary'   => $link(),
        'home.hero.cta_secondary' => $link(),

        'home.video.title'    => $text(100),
        'home.video.subtitle' => $text(300),

        'home.news.title'         => $text(100),
        'home.goals.title'        => $text(100),
        'home.achievements.title' => $text(100),
        'home.trainers.title'     => $text(100),
        'home.partners.title'     => $text(100),

        'home.contact.title'       => $text(100),
        'home.contact.subtitle'    => $text(300),
        'home.contact.phone_label' => $text(50),
        'home.contact.email_label' => $text(50),

        'home.donation.title' => $text(100),
        'home.donation.text'  => $text(400),
        'home.donation.cta'   => $link(),

        // --- Footer ---
        'footer.logo'             => $image(600),
        'footer.about'            => $text(600),
        'footer.links_title'      => $text(50),
        'footer.link_1'           => $link(),
        'footer.link_2'           => $link(),
        'footer.link_3'           => $link(),
        'footer.contact_title'    => $text(50),
        'footer.copyright'        => $text(150),
        'footer.social.facebook'  => $icon,
        'footer.social.instagram' => $icon,
        'footer.social.youtube'   => $icon,
    ],
];
