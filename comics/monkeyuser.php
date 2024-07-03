<?php

/**
 * Parse MonkeyUser feed
 *
 * <item>
 *   <title>Example</title>
 *   <link>https://www.monkeyuser.com/2024/example/</link>
 *   <pubDate>Mon, 19 Feb 2024 00:00:00 +0000</pubDate>
 *   <guid>https://www.monkeyuser.com/2024/example/</guid>
 *   <description/>
 * </item>
 *
 * @param FreshRSS_Entry $entry
 * @return FreshRSS_Entry
 * @throws DOMException
 */
function parseMonkeyUser($entry)
{
    /** Get missing content from link or return the entry */
    if (($content = @file_get_contents($entry->link(), false, stream_context_create(['http' => ['timeout' => 1]]))) === false) {
        return $entry;
    }

    /** Sanitize downloaded content */
    $content = strip_tags($content, '<body><img>'); // Keep only allowed tags

    /** Parse content */
    $dom = new DOMDocument;
    $dom->loadHTML($content);
    libxml_use_internal_errors(false);

    /** If image is found, replace content */
    if (is_null($image = $dom->getElementsByTagName('img')[1] ?? null) !== true) {
        $base = parse_url($entry->link(), PHP_URL_SCHEME) . '://' . parse_url($entry->link(), PHP_URL_HOST); // Get base URL

        $img = $dom->createElement('img'); // Create new image element
        $img->setAttribute('src', $base . $image->getAttribute('src')); // Set image source
        $img->setAttribute('alt', $image->getAttribute('alt')); // Set image alt

        $link = $dom->createElement('a'); // Create new link element
        $link->setAttribute('href', $entry->link()); // Set link href
        $link->appendChild($img); // Append image to link

        $body = $dom->getElementsByTagName('body')->item(0); // Get body element
        while ($body->hasChildNodes()) {
            $body->removeChild($body->firstChild); // Remove all children
        }
        $body->appendChild($link); // Append image to body

        $entry->_content($dom->saveHTML()); // Update entry content
    }

    return $entry;
}
