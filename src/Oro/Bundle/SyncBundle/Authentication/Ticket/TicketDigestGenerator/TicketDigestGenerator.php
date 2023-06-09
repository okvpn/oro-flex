<?php

namespace Oro\Bundle\SyncBundle\Authentication\Ticket\TicketDigestGenerator;

use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

/**
 * Generates unique and secure hash which is used in Sync authentication ticket.
 */
class TicketDigestGenerator implements TicketDigestGeneratorInterface
{
    /**
     * @var PasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * @var string
     */
    private $secret;

    public function __construct(PasswordEncoderInterface $passwordEncoder, string $secret)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->secret = $secret;
    }

    /**
     * {@inheritdoc}
     */
    public function generateDigest(string $nonce, string $created, string $password): string
    {
        return sha1(sprintf('%s|%s|%s', $nonce, $created, $password), $this->secret);
    }
}
