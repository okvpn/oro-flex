<?php

namespace Oro\Bundle\MessageQueueBundle\Test\Assert;

use Oro\Component\MessageQueue\Client\Message;
use Oro\Component\MessageQueue\Client\MessagePriority;

/**
 * Provides useful assertion methods for the message queue related tests.
 * It is expected that this trait will be used in classes that have "getMessageCollector" static method.
 * @see \Oro\Bundle\MessageQueueBundle\Test\Unit\MessageQueueAssertTrait
 * @see \Oro\Bundle\MessageQueueBundle\Test\Functional\MessageQueueAssertTrait
 */
trait AbstractMessageQueueAssertTrait
{
    /**
     * Asserts that a message was sent to a topic.
     * If the message is not provided this method checks that at least one message was sent to a topic.
     *
     * @param string                    $expectedTopic   The expected topic name
     * @param string|array|Message|null $expectedMessage The expected message or NULL if a message is not matter
     * @param bool                      $isSubJobMessage true if the message is sent for runDelayed running
     * @param bool                      $canonicalize    Arrays with integer keys and scalar values
     *                                                   are sorted before comparison when set to true
     */
    protected static function assertMessageSent(
        $expectedTopic,
        $expectedMessage = null,
        bool $isSubJobMessage = false,
        bool $canonicalize = false
    ) {
        $message = ['topic' => $expectedTopic];
        if (null !== $expectedMessage) {
            $message['message'] = $expectedMessage;
        }

        self::assertThat(self::getSentMessages(), new SentMessageConstraint($message, $isSubJobMessage, $canonicalize));
    }

    protected static function assertMessageSentWithPriority(string $topic, string $expectedPriority): void
    {
        $sentMessages = self::getSentMessagesByTopic($topic, false);
        self::assertEquals(
            $expectedPriority,
            array_pop($sentMessages)?->getPriority() ?? MessagePriority::NORMAL,
            'Failed asserting priority ' . $expectedPriority
        );
    }

    /**
     * Asserts that messages were sent to a topic.
     * The send order is not taken into account.
     *
     * @param string $expectedTopic    The expected topic name
     * @param array  $expectedMessages The expected messages
     *                                 Each message can be string, array or instance of Message class
     * @param bool   $isSubJobMessage  true if the message is sent for runDelayed running
     * @param bool   $canonicalize     Arrays with integer keys and scalar values
     *                                 are sorted before comparison when set to true
     */
    protected static function assertMessagesSent(
        $expectedTopic,
        array $expectedMessages,
        bool $isSubJobMessage = false,
        bool $canonicalize = false
    ) {
        foreach ($expectedMessages as $expectedMessage) {
            self::assertMessageSent($expectedTopic, $expectedMessage, $isSubJobMessage, $canonicalize);
        }

        $topicMessages = [];
        $sentMessages = self::getSentMessages();
        foreach ($sentMessages as $sentMessage) {
            if ($sentMessage['topic'] === $expectedTopic) {
                $topicMessages[] = $sentMessage['message'];
            }
        }
        self::assertCount(
            count($expectedMessages),
            $topicMessages,
            sprintf('Failed asserting that exactly given messages were sent to "%s" topic.', $expectedTopic)
        );
    }

    /**
     * Asserts the number of messages are sent to a topic.
     *
     * @param string $expectedTopic The expected topic name
     * @param int    $expectedCount The expected number of messages
     */
    protected static function assertMessagesCount($expectedTopic, $expectedCount)
    {
        $topicMessages = [];
        $sentMessages = self::getSentMessages();
        foreach ($sentMessages as $sentMessage) {
            if ($sentMessage['topic'] === $expectedTopic) {
                $topicMessages[] = $sentMessage['message'];
            }
        }
        self::assertCount(
            $expectedCount,
            $topicMessages,
            sprintf('Failed asserting that the given number of messages were sent to "%s" topic.', $expectedTopic)
        );
    }

    /**
     * Asserts the number of messages are sent to a topic.
     * This is an alias for assertMessagesCount method
     *
     * @param string $expectedTopic The expected topic name
     * @param int    $expectedCount The expected number of messages
     */
    protected static function assertCountMessages($expectedTopic, $expectedCount)
    {
        self::assertMessagesCount($expectedTopic, $expectedCount);
    }

    /**
     * Asserts that no any message was sent to a topic.
     *
     * @param string $expectedTopic The expected topic name
     */
    protected static function assertMessagesEmpty($expectedTopic)
    {
        self::assertMessagesSent($expectedTopic, []);
    }

    /**
     * Asserts that no any message was sent to a topic.
     * This is an alias for assertMessagesEmpty method
     *
     * @param string $expectedTopic The expected topic name
     */
    protected static function assertEmptyMessages($expectedTopic)
    {
        self::assertMessagesEmpty($expectedTopic);
    }

    /**
     * Asserts that exactly given messages were sent.
     * NOTE: use this assertion with caution because it is possible
     * that messages not related to a testing functionality were sent as well.
     *
     * @param array $expected [['topic' => topic name, 'message' => message], ...]
     */
    protected static function assertAllMessagesSent(array $expected)
    {
        $constraint = new SentMessagesConstraint($expected);

        self::assertThat(self::getSentMessages(), $constraint);
    }

    /**
     * Gets all sent messages.
     *
     * @return array [['topic' => topic name, 'message' => message (string|array|Message)], ...]
     */
    protected static function getSentMessages(bool $extractBodies = true): array
    {
        $sentMessages = self::getMessageCollector()->getSentMessages();
        if ($extractBodies) {
            $sentMessages = array_map(static function (array $sentMessage) {
                if ($sentMessage['message'] instanceof Message) {
                    $sentMessage['message'] = $sentMessage['message']->getBody();
                }

                return $sentMessage;
            }, $sentMessages);
        }

        return $sentMessages;
    }

    protected static function getTopicSentMessages(string $topic, bool $extractBodies = true): array
    {
        $sentMessages = [];
        foreach (self::getSentMessages($extractBodies) as $sentMessage) {
            if ($topic === $sentMessage['topic']) {
                $sentMessages[] = $sentMessage;
            }
        }

        return $sentMessages;
    }

    /**
     * Clears messages in queue.
     */
    protected static function clearMessageCollector()
    {
        return self::getMessageCollector()->clear();
    }

    /**
     * Gets a message sent to a specific topic.
     *
     * @param string $topic
     *
     * @return string|array|Message
     */
    protected static function getSentMessage(string $topic, bool $extractBody = true)
    {
        self::assertMessagesCount($topic, 1);
        $messages = self::getSentMessages(false);
        foreach ($messages as $message) {
            if ($message['topic'] === $topic) {
                return $extractBody && $message['message'] instanceof Message
                    ? $message['message']->getBody()
                    : $message['message'];
            }
        }
        self::fail(sprintf('A message was not sent to "%s" topic.', $topic));
    }

    /**
     * @return array|string[]|Message[]
     */
    protected static function getSentMessagesByTopic(string $expectedTopic, bool $extractBodies = true): array
    {
        $topicMessages = [];
        $sentMessages = self::getSentMessages(false);
        foreach ($sentMessages as $sentMessage) {
            if ($sentMessage['topic'] === $expectedTopic) {
                $message = $sentMessage['message'];
                $topicMessages[] = $extractBodies && $message instanceof Message ? $message->getBody() : $message;
            }
        }

        return $topicMessages;
    }
}
