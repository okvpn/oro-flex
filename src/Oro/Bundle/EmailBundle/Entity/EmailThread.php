<?php

namespace Oro\Bundle\EmailBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;

/**
 * Entity that represents a chain(thread) of emails
 *
 * @ORM\Table(
 *      name="oro_email_thread"
 * )
 * @ORM\Entity(repositoryClass="Oro\Bundle\EmailBundle\Entity\Repository\EmailThreadRepository")
 * @ORM\HasLifecycleCallbacks
 */
class EmailThread
{
    const ENTITY_CLASS = 'Oro\Bundle\EmailBundle\Entity\EmailThread';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var ArrayCollection|Email[] $emails
     *
     * @ORM\OneToMany(targetEntity="Email", mappedBy="thread", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $emails;

    /**
     * @var Email
     *
     * @ORM\ManyToOne(targetEntity="Email")
     * @ORM\JoinColumn(name="last_unseen_email_id", referencedColumnName="id", nullable=true)
     */
    protected $lastUnseenEmail;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime")
     * @ConfigField(
     *      defaultValues={
     *          "entity"={
     *              "label"="oro.ui.created_at"
     *          }
     *      }
     * )
     */
    protected $created;

    public function __construct()
    {
        $this->emails = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get entity created date/time
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param Email $email
     *
     * @return bool
     */
    public function hasEmail(Email $email)
    {
        return $this->emails->contains($email);
    }

    /**
     * @param Email $email
     *
     * @return EmailThread
     */
    public function removeEmail(Email $email)
    {
        if ($this->emails->contains($email)) {
            $this->emails->removeElement($email);
        }

        return $this;
    }

    /**
     * Get email
     *
     * @return ArrayCollection|Email[]
     */
    public function getEmails()
    {
        return $this->emails;
    }

    /**
     * Set email
     *
     * @param Email $email
     *
     * @return EmailThread
     */
    public function addEmail(Email $email)
    {
        if (!$this->emails->contains($email)) {
            $this->emails->add($email);
        }

        return $this;
    }

    /**
     * Pre persist event listener
     *
     * @ORM\PrePersist
     */
    public function beforeSave()
    {
        $this->created = new \DateTime('now', new \DateTimeZone('UTC'));
    }

    /**
     * Get last unseen email
     *
     * @return EmailThread
     */
    public function getLastUnseenEmail()
    {
        return $this->lastUnseenEmail;
    }

    /**
     * Set last unseen email
     *
     * @param Email $lastUnseenEmail
     *
     * @return EmailThread
     */
    public function setLastUnseenEmail($lastUnseenEmail)
    {
        $this->lastUnseenEmail = $lastUnseenEmail;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getId();
    }
}
