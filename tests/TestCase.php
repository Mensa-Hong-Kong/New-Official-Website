<?php

namespace Tests;

use Illuminate\Broadcasting\Broadcasters\UsePusherChannelConventions;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\ExpectationFailedException;

abstract class TestCase extends BaseTestCase
{
    protected function assertBroadcastChannel(
        string $broadcastEvent,
        string|array $expectedNames,
        string $expectedChannelClass = PrivateChannel::class,
        array $expectedPayload = ['synced_to_stripe' => false]
    ): void {
        $expectedNames = (array) $expectedNames;
        Event::assertDispatched($broadcastEvent);
        $frameworkNormalizer = new class
        {
            use UsePusherChannelConventions;
        };
        $dispatchedEvents = Event::dispatched($broadcastEvent);
        $flattenedEvents = data_get($dispatchedEvents, '*.*');
        if (! is_array($flattenedEvents)) {
            $flattenedEvents = is_object($dispatchedEvents) ? [$dispatchedEvents] : [];
        }
        $matched = false;
        $lastException = null;
        foreach ($flattenedEvents as $event) {
            if (is_array($event)) {
                $event = $event['event'] ?? current($event);
            }
            if (! is_object($event) || ! method_exists($event, 'broadcastOn')) {
                continue;
            }
            try {
                $actualChannels = $event->broadcastOn();
                $actualChannels = is_array($actualChannels) ? $actualChannels : [$actualChannels];
                $validActualChannelNames = [];
                foreach ($actualChannels as $channel) {
                    if ($channel instanceof $expectedChannelClass) {
                        $validActualChannelNames[] = (string) $frameworkNormalizer->normalizeChannelName($channel);
                    }
                }
                sort($expectedNames);
                sort($validActualChannelNames);
                $this->assertEquals(
                    $expectedNames,
                    $validActualChannelNames,
                    'The broadcast channel names or types do not match the expectations.'
                );
                $payload = method_exists($event, 'broadcastWith')
                    ? $event->broadcastWith()
                    : get_object_vars($event);
                $actualPayloadSubset = array_intersect_key($payload, $expectedPayload);
                $this->assertEquals(
                    $expectedPayload,
                    $actualPayloadSubset,
                    'The broadcast payload does not match the expected subset.'
                );
                $matched = true;
                break;
            } catch (ExpectationFailedException $e) {
                $lastException = $e;
            }
        }
        if (! $matched) {
            if ($lastException) {
                throw $lastException;
            }
            $this->fail("No dispatched event [{$broadcastEvent}] matched the expected broadcast channels and payload.");
        }
    }
}
