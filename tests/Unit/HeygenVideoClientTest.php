<?php

declare(strict_types=1);

namespace RunApi\HeygenVideo\Tests\Unit;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use RunApi\Core\ClientOptions;
use RunApi\Core\Errors\ValidationException;
use RunApi\Core\Resources\Pricing;
use RunApi\Core\Tests\Fixtures\QueueHttpClient;
use RunApi\HeygenVideo\HeygenVideoClient;
use RunApi\HeygenVideo\Models\CompletedVideoTaskResponse;
use RunApi\HeygenVideo\Resources\ImageToVideo;
use RunApi\HeygenVideo\Resources\TextToVideo;

final class HeygenVideoClientTest extends TestCase
{
    public function testExposesTypedResources(): void
    {
        $client = new HeygenVideoClient(new ClientOptions(apiKey: 'k', httpClient: new QueueHttpClient([]), maxRetries: 0));

        self::assertInstanceOf(TextToVideo::class, $client->textToVideo);
        self::assertInstanceOf(ImageToVideo::class, $client->imageToVideo);
        self::assertInstanceOf(Pricing::class, $client->pricing);
    }

    public function testCreatePostsCompactedBodyToCorrectPath(): void
    {
        $transport = new QueueHttpClient([
            new Response(200, [], '{"id":"task_1"}')]);
        $client = new HeygenVideoClient(new ClientOptions(apiKey: 'k', httpClient: $transport, maxRetries: 0));

        $task = $client->textToVideo->create([
            'model' => 'heygen-video-1',
            'prompt' => 'A product render',
            'duration_seconds' => 5,
            'output_resolution' => '768p',
            'aspect_ratio' => 'adaptive',
            'reference_image_urls' => ['https://cdn.runapi.ai/public/samples/image.jpg'],
            'reference_video_urls' => ['https://cdn.runapi.ai/public/samples/video.mp4'],
            'reference_audio_urls' => ['https://cdn.runapi.ai/public/samples/voice.mp3'],
            'callback_url' => '',
            'seed' => 42]);

        $body = json_decode((string) $transport->requests[0]->getBody(), true, flags: JSON_THROW_ON_ERROR);

        self::assertSame('task_1', $task->id);
        self::assertSame('/api/v1/heygen_video/text_to_video', $transport->requests[0]->getUri()->getPath());
        self::assertSame('heygen-video-1', $body['model']);
        self::assertArrayNotHasKey('callback_url', $body);
        self::assertSame(42, $body['seed']);
    }

    public function testRunReturnsTypedCompletedResponseAndPreservesUnknownFields(): void
    {
        $transport = new QueueHttpClient([
            new Response(200, [], '{"id":"task_1"}'),
            new Response(200, [], '{"id":"task_1","status":"completed","videos":[{"url":"https://file.runapi.ai/result"}],"generation_stage":"all_audios_ready","extra_field":"kept","usage":{"cost":0.05}}')]);
        $client = new HeygenVideoClient(new ClientOptions(apiKey: 'k', httpClient: $transport, maxRetries: 0));

        $result = $client->textToVideo->run([
            'model' => 'heygen-video-1',
            'prompt' => 'A product render',
            'duration_seconds' => 5,
            'output_resolution' => '768p',
            'aspect_ratio' => 'adaptive',
            'reference_image_urls' => ['https://cdn.runapi.ai/public/samples/image.jpg'],
            'reference_video_urls' => ['https://cdn.runapi.ai/public/samples/video.mp4'],
            'reference_audio_urls' => ['https://cdn.runapi.ai/public/samples/voice.mp3']]);

        self::assertInstanceOf(CompletedVideoTaskResponse::class, $result);
        self::assertSame('https://file.runapi.ai/result', $result->videos[0]->url);
        self::assertSame('kept', $result->toArray()['extra_field']);
        self::assertSame('/api/v1/heygen_video/text_to_video/task_1', $transport->requests[1]->getUri()->getPath());
    }

    public function testCompletedResponseRequiresResultFiles(): void
    {
        $transport = new QueueHttpClient([
            new Response(200, [], '{"id":"task_1"}'),
            new Response(200, [], '{"id":"task_1","status":"completed","usage":{"cost":0.05}}')]);
        $client = new HeygenVideoClient(new ClientOptions(apiKey: 'k', httpClient: $transport, maxRetries: 0));

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('videos is required');

        $client->textToVideo->run([
            'model' => 'heygen-video-1',
            'prompt' => 'A product render',
            'duration_seconds' => 5,
            'output_resolution' => '768p',
            'aspect_ratio' => 'adaptive',
            'reference_image_urls' => ['https://cdn.runapi.ai/public/samples/image.jpg'],
            'reference_video_urls' => ['https://cdn.runapi.ai/public/samples/video.mp4'],
            'reference_audio_urls' => ['https://cdn.runapi.ai/public/samples/voice.mp3']]);
    }




    public function testSecondaryResourceUsesItsOwnPath(): void
    {
        $transport = new QueueHttpClient([
            new Response(200, [], '{"id":"task_2"}')]);
        $client = new HeygenVideoClient(new ClientOptions(apiKey: 'k', httpClient: $transport, maxRetries: 0));

        $client->imageToVideo->create([
            'model' => 'heygen-video-1',
            'prompt' => 'A product render',
            'first_frame_image_url' => 'https://cdn.runapi.ai/public/samples/image.jpg',
            'duration_seconds' => 5,
            'output_resolution' => '768p']);

        self::assertSame('/api/v1/heygen_video/image_to_video', $transport->requests[0]->getUri()->getPath());
    }
}
