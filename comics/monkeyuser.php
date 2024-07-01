<?php

/**
 * Parse MonkeyUser feed
 * @param $entry
 * @return mixed
 * @throws DOMException
 */
function parseMonkeyUser($entry)
{
    if (($content = @file_get_contents($entry->link(), false, stream_context_create(['http' => ['timeout' => 1]]))) === false) {
        return $entry;
    }

    $dom = new DOMDocument;
    $dom->loadHTML($content);
    libxml_use_internal_errors(false);

    if (is_null($image = $dom->getElementsByTagName('img')[1] ?? null) !== true) {
        $base = parse_url($entry->link(), PHP_URL_SCHEME) . '://' . parse_url($entry->link(), PHP_URL_HOST);

        $img = $dom->createElement('img');
        $img->setAttribute('src', $base . $image->getAttribute('src'));
        $img->setAttribute('alt', $image->getAttribute('alt'));

        $body = $dom->getElementsByTagName('body')->item(0);
        while ($body->hasChildNodes()) {
            $body->removeChild($body->firstChild);
        }
        $body->appendChild($img);

        $entry->_content($dom->saveHTML());
    }

    return $entry;
}

