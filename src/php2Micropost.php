<?php

/**
 * Library to make posting to Microposts on Wordpress from PHP easier.
 *
 * @author  Neil Thompson <hi@nei.lt>
 * @see     https://spokenlikeageek.com
 * @license GNU Lesser General Public License, version 3
 *
 */

namespace williamsdb\php2micropost;

use williamsdb\php2micropost\WordpressConsts;
use williamsdb\php2micropost\php2MicropostException;
use williamsdb\php2micropost\RegexPatterns;
use williamsdb\php2micropost\Version; // Not utilised, for documentation only

/**
 * Class for posting to Wordpress Microposts via the REST API. 
 * Handles text and media uploads, including resizing images that exceed Wordpress limits.
 */
class php2Micropost
{

    // base url for the Wordpress REST API endpoint, e.g. https://www.yourdomain.com/wp-json/wp/v2
    private string $base_url;
    private string $username;
    private string $password;
    private bool $parseUrls;
    private string $fileUploadDir;

    public function __construct(string $base_url, string $username, string $password, bool $parseUrls = true, $fileUploadDir = '/tmp')
    {
        $this->base_url = $base_url;
        $this->username = $username;
        $this->password = $password;
        $this->parseUrls = $parseUrls;
        $this->fileUploadDir = $fileUploadDir;
    }

    public function wordpress_connect()
    {
        return 'Authorization: Basic ' . base64_encode("{$this->username}:{$this->password}");
    }

    private function upload_media_to_wordpress($connection, $filename)
    {

        // helper: fetch remote file with cURL
        $fetchRemote = function ($url) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS      => 5,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER         => false,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_USERAGENT => "Mozilla/5.0",
            ]);
            $body = curl_exec($ch);
            $mime = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
            $err  = curl_error($ch);
            unset($ch);

            if ($body === false) {
                throw new php2MicropostException("Failed to fetch remote file: " . $err, 1005);
            }

            return [$body, $mime];
        };

        // have we been passed a file?
        if (empty($filename)) return;

        // get mime type and file body
        if (filter_var($filename, FILTER_VALIDATE_URL)) {
            [$body, $mime] = $fetchRemote($filename);
        } else {
            if (!file_exists($filename)) {
                throw new php2MicropostException("Local file does not exist: " . $filename, 1006);
            }
            $mime = mime_content_type($filename);
            $body = file_get_contents($filename);
        }

        // if we can't determine the mime type, use the fallback
        if (empty($mime) || !is_string($mime)) {
            throw new php2MicropostException("Could not determine mime type of file.", 1002);
        }

        // truncate mime type at semicolon if present
        if (($pos = strpos($mime, ';')) !== false) {
            $mime = substr($mime, 0, $pos);
        }

        // what file type have we got?
        if (!in_array($mime, WordpressConsts::FILE_TYPES)) {
            throw new php2MicropostException("File type not supported: " . $mime . " - $filename", 1003);
        }

        // get the size and basename of the file
        $basename = $this->getFileName($filename);
        $size     = strlen($body);

        // does the file size need reducing? (applies to local + remote)
        if ($mime != "image/gif") {
            if ($size > WordpressConsts::MAX_IMAGE_UPLOAD_SIZE) {
                $newImage = imagecreatefromstring($body);
                if ($newImage === false) {
                    throw new php2MicropostException("Could not create image resource for resizing.", 1007);
                }

                for ($i = 9; $i >= 1; $i--) {
                    $tempFile = $this->fileUploadDir . '/' . $basename;
                    imagejpeg($newImage, $tempFile, $i * 10);
                    $size = filesize($tempFile);

                    if ($size < WordpressConsts::MAX_IMAGE_UPLOAD_SIZE) {
                        $body = file_get_contents($tempFile);
                        unlink($tempFile);
                        break;
                    } else {
                        unlink($tempFile);
                    }
                }
            }
        }

        // upload the file to Wordpress
        $ch = curl_init("{$this->base_url}/media");
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            $connection,
            "Content-Disposition: attachment; filename=\"$basename\"",
            "Content-Type: $mime"
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);

        $media_response = json_decode(curl_exec($ch));
        $media_id = $media_response->id ?? null;
        unset($ch);

        if (!$media_id) {
            throw new php2MicropostException("Failed to upload image.", 1008);
        }
        return $media_id;
    }

    public function post_to_wordpress($connection, $text, $title = '', $media = '')
    {

        // if we have media, upload it first and get the media ID to attach to the post
        if (!empty($media)) {
            $media_id = $this->upload_media_to_wordpress($connection, $media);
        }

        // parse for URLs
        if ($this->parseUrls) {
            $urls = $this->mark_urls($text);
            if (!empty($urls)) {
                $offset = 0;
                foreach ($urls as $url) {
                    $start = $url['start'] + $offset;
                    $end = $url['end'] + $offset;
                    $link = "<a href=\"{$url['url']}\">";

                    $text = substr_replace($text, $link, $start, 0);
                    $offset += strlen($link);

                    $text = substr_replace($text, '</a>', $end + strlen($link), 0);
                    $offset += 4;
                }
            }
        }

        // create the post data, including the media ID if we have one
        $post_data = [
            'title'          => $title,
            'content'        => $text,
            'status'         => 'publish',
            ...!empty($media) ? ['featured_media' => $media_id] : [],
        ];

        $ch = curl_init("{$this->base_url}/micropost");
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            $connection,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));

        $post_response = curl_exec($ch);
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        unset($ch);

        if ($status_code === 201) {
            return true;
        } else {
            throw new php2MicropostException("Error creating post: " . $post_response, 1009);
        }
    }

    // get the filename from a URL or local path
    private function getFileName($path)
    {
        // If the path is a URL, use basename to get the filename
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return basename(parse_url($path, PHP_URL_PATH));
        } else {
            // If the path is a local path, use basename to get the filename
            return basename($path);
        }
    }

    // find URLs in text and return their positions and full URLs (including protocol)
    private function mark_urls($text)
    {
        // First find URLs with protocols
        preg_match_all(RegexPatterns::URL_REGEX, $text, $matches, PREG_OFFSET_CAPTURE);

        $urlData = array();

        foreach ($matches[0] as $match) {
            $url = $match[0];
            $start = $match[1];
            $end = $start + strlen($url);

            $urlData[] = array(
                'start' => $start,
                'end' => $end,
                'url' => $url,
            );
        }

        // Then find URLs without protocols (starting with www. or common TLDs)
        $noProtocolRegex = '/(?<!\S)(www\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*(?:\/[^\s]*)?|[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?\.(?:com|org|net|edu|gov|mil|int|co|uk|de|fr|jp|au|us|ru|ch|it|nl|se|no|es|mil)(?:\/[^\s]*)?)(?!\S)/i';

        preg_match_all($noProtocolRegex, $text, $noProtocolMatches, PREG_OFFSET_CAPTURE);

        foreach ($noProtocolMatches[0] as $match) {
            $url = $match[0];
            $start = $match[1];
            $end = $start + strlen($url);

            // Check if this URL overlaps with any already found protocol URLs
            $overlap = false;
            foreach ($urlData as $existingUrl) {
                if ($start < $existingUrl['end'] && $end > $existingUrl['start']) {
                    $overlap = true;
                    break;
                }
            }

            if (!$overlap) {
                // Prepend https:// for URLs without protocol
                $fullUrl = 'https://' . $url;

                $urlData[] = array(
                    'start' => $start,
                    'end' => $end,
                    'url' => $fullUrl,
                );
            }
        }

        // Sort by start position to maintain order
        usort($urlData, function ($a, $b) {
            return $a['start'] - $b['start'];
        });

        return $urlData;
    }
}
