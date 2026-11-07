<?php

/*
|--------------------------------------------------------------------------
| Editable public-site content
|--------------------------------------------------------------------------
|
| Every key the admin can edit in place on the public site (Hemam/), with
| its type and max length. PUT /admin/content rejects any key not listed
| here. The default text itself lives in the frontend component, so a key
| with no saved row simply shows that default.
|
| type "text": plain text, line breaks allowed, any HTML is stripped.
|
*/

return [
    'keys' => [
        'home.hero.title'    => ['type' => 'text', 'max' => 150],
        'home.hero.subtitle' => ['type' => 'text', 'max' => 500],
    ],
];
