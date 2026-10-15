<?php

declare(strict_types=1);

namespace RunApi\HeygenVideo;

use RunApi\Core\BaseClient;
use RunApi\Core\ClientOptions;
use RunApi\HeygenVideo\Resources\ImageToVideo;
use RunApi\HeygenVideo\Resources\TextToVideo;

/**
 * HeyGen Video RunAPI PHP client.
 *
 * The client exposes typed model resources plus the universal `files` and
 * `account` resources.
 */
final class HeygenVideoClient extends BaseClient
{
    /** Text to video operations for HeyGen Video. */
    public readonly TextToVideo $textToVideo;
    /** Image to video operations for HeyGen Video. */
    public readonly ImageToVideo $imageToVideo;

    /** Create a HeyGen Video client with optional API key, base URL, and transport overrides. */
    public function __construct(ClientOptions $options = new ClientOptions())
    {
        parent::__construct($options);
        $this->textToVideo = TextToVideo::fromHttp($this->http);
        $this->imageToVideo = ImageToVideo::fromHttp($this->http);
    }
}
