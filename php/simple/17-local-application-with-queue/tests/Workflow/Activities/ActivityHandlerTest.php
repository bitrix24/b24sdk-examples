<?php

declare(strict_types=1);

namespace Tests\Workflow\Activities;

use App\Workflow\Activities\ActivityHandler;
use App\Workflow\Activities\ActivityHandlerInterface;
use App\Workflow\Activities\ActivityHandlerMetadata;
use App\Workflow\Activities\ActivityHandlerNotFoundException;
use App\Workflow\Activities\ActivityInstallMetadata;
use App\Workflow\Activities\ActivityRequest;
use App\Workflow\Activities\ActivityResponse;
use Bitrix24\SDK\Services\Workflows\Common\Auth;
use Bitrix24\SDK\Services\Workflows\Common\WorkflowDocumentId;
use Bitrix24\SDK\Services\Workflows\Common\WorkflowDocumentType;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

final class ActivityHandlerTest extends TestCase
{
    public function testHandleReturnsResponseFromMatchingHandler(): void
    {
        $expectedResponse = new ActivityResponse(
            'event-token',
            ['result' => 'ok'],
            'done'
        );

        $handler = new ActivityHandler(
            [
                new FakeActivityHandler('other.activity', new ActivityResponse('other', [], null)),
                new FakeActivityHandler('weather_activity', $expectedResponse),
            ],
            new NullLogger()
        );

        $response = $handler->handle($this->createActivityRequest('weather_activity'));

        self::assertSame($expectedResponse, $response);
    }

    public function testHandleThrowsWhenNoMatchingHandlerExists(): void
    {
        $handler = new ActivityHandler(
            [
                new FakeActivityHandler('other.activity', new ActivityResponse('other', [], null)),
            ],
            new NullLogger()
        );

        $this->expectException(ActivityHandlerNotFoundException::class);
        $this->expectExceptionMessage('missing.activity');

        $handler->handle($this->createActivityRequest('missing.activity'));
    }

    private function createActivityRequest(string $code): ActivityRequest
    {
        return new ActivityRequest(
            'workflow-1',
            $code,
            new WorkflowDocumentId('crm', 'CCrmDocumentDeal', 'DEAL_42'),
            new WorkflowDocumentType('crm', 'CCrmDocumentDeal', 'DEAL'),
            'event-token',
            ['city' => 'Bishkek'],
            false,
            30,
            1710000000,
            Auth::initFromArray([
                'access_token' => 'access-token',
                'refresh_token' => 'refresh-token',
                'expires' => 1710003600,
                'client_endpoint' => 'example.bitrix24.ru',
                'server_endpoint' => 'https://oauth.bitrix.info/oauth/token/',
                'scope' => 'bizproc,crm',
                'status' => 'L',
                'application_token' => 'application-token',
                'expires_in' => 3600,
                'domain' => 'example.bitrix24.ru',
                'member_id' => 'member-1',
                'user_id' => 7,
            ])
        );
    }
}

final readonly class FakeActivityHandler implements ActivityHandlerInterface
{
    public function __construct(
        private string $code,
        private ActivityResponse $response,
    ) {
    }

    public function handle(ActivityRequest $activityActivityRequest): ActivityResponse
    {
        return $this->response;
    }

    public function getHandlerMetadata(): ActivityHandlerMetadata
    {
        return new ActivityHandlerMetadata($this->code);
    }

    public function getInstallMetadata(?string $handlerUrl, ?int $b24UserId): ActivityInstallMetadata
    {
        throw new \BadMethodCallException('Not needed in this test.');
    }
}
