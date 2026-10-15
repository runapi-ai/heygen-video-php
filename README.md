# HeyGen Video PHP SDK for RunAPI

[![Packagist](https://img.shields.io/packagist/v/runapi-ai/heygen-video)](https://packagist.org/packages/runapi-ai/heygen-video)
[![License](https://img.shields.io/github/license/runapi-ai/heygen-video-php)](https://github.com/runapi-ai/heygen-video-php/blob/main/LICENSE)

The HeyGen Video PHP SDK is the language-specific package for HeyGen Video
on RunAPI. Use this package when your application needs Composer installs,
associative-array request bodies, task status lookup, and consistent RunAPI
errors in PHP.

This README is the PHP package guide for the public `heygen-video-php` split
repository. For model details, use https://runapi.ai/models/heygen-video; for API
reference, use https://runapi.ai/docs/api/heygen-video/text-to-video; for SDK docs, use
https://runapi.ai/docs/resources/sdks.

## Install

```bash
composer require runapi-ai/heygen-video
```

## Quick start

```php
<?php

require __DIR__ . "/vendor/autoload.php";

use RunApi\HeygenVideo\HeygenVideoClient;

$client = new HeygenVideoClient(); // reads RUNAPI_API_KEY

$imageToVideoTask = $client->imageToVideo->create([
    'model' => 'heygen-video-1',
    'duration_seconds' => 5,
    'first_frame_image_url' => 'https://cdn.runapi.ai/public/samples/image.jpg',
    'output_resolution' => '768p',
    'prompt' => 'Make it golden hour',
]);

$task = $client->textToVideo->create([
    'model' => 'heygen-video-1',
    'aspect_ratio' => 'adaptive',
    'duration_seconds' => 5,
    'output_resolution' => '768p',
    'prompt' => 'A precise product render on white marble',
    'reference_audio_urls' => ['https://cdn.runapi.ai/public/samples/voice.mp3'],
    'reference_image_urls' => ['https://cdn.runapi.ai/public/samples/image.jpg'],
    'reference_video_urls' => ['https://cdn.runapi.ai/public/samples/video.mp4'],
]);

$status = $client->textToVideo->get($task->id);

$result = $client->textToVideo->run([
    'model' => 'heygen-video-1',
    'aspect_ratio' => 'adaptive',
    'duration_seconds' => 5,
    'output_resolution' => '768p',
    'prompt' => 'A serene mountain lake at dawn',
    'reference_audio_urls' => ['https://cdn.runapi.ai/public/samples/voice.mp3'],
    'reference_image_urls' => ['https://cdn.runapi.ai/public/samples/image.jpg'],
    'reference_video_urls' => ['https://cdn.runapi.ai/public/samples/video.mp4'],
]);

echo $result->videos[0]->url . PHP_EOL;
```

Use `create()` to submit a task and return quickly, `get()` to fetch the latest
task state, and `run()` when a script should create and poll until completion.
In web request handlers, prefer `create()` plus webhook or later `get()`
polling so a worker is not held open.


RunAPI-generated file URLs are temporary. Download and store generated files
in your own durable storage within the retention window; do not treat returned
URLs as long-term assets.

## Language notes

Pass request parameters as associative arrays with snake_case keys. The
available resources are `textToVideo`, `imageToVideo`. Keep `RUNAPI_API_KEY` in the environment
or your secret manager; never commit API keys or callback secrets.

## Links

- Model page: https://runapi.ai/models/heygen-video
- SDK docs: https://runapi.ai/docs/resources/sdks
- Product docs: https://runapi.ai/docs/api/heygen-video/text-to-video
- Pricing and rate limits: https://runapi.ai/models/heygen-video
- Full catalog: https://runapi.ai/models
- GitHub repository: https://github.com/runapi-ai/heygen-video-php
- Multi-language SDK repository: https://github.com/runapi-ai/heygen-video-sdk

## License

Licensed under the Apache License, Version 2.0.
