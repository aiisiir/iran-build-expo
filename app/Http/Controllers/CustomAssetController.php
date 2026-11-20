<?php

namespace App\Http\Controllers;

use App\Models\CustomSnippet;
use Illuminate\Http\Response;

/** /assets/custom.css and /assets/custom.js: the superadmin's snippets as ordinary files, so no inline code is ever needed. */
class CustomAssetController
{
    public function __invoke(string $ext): Response
    {
        $bundle = CustomSnippet::bundles()[$ext] ?? null;
        abort_if($bundle === null, 404);

        return response($bundle['code'], 200, [
            'Content-Type' => $ext === 'css' ? 'text/css; charset=utf-8' : 'application/javascript; charset=utf-8',
            'Cache-Control' => 'public, max-age=86400', // the page links ?v=<hash>, so a change is picked up at once
        ]);
    }
}
