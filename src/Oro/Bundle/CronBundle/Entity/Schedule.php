<?php

namespace Oro\Bundle\CronBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;

/**
 * @ORM\Table(name="oro_cron_schedule")
 * @ORM\Entity(repositoryClass="Oro\Bundle\CronBundle\Entity\Repository\ScheduleRepository")
 * @Config(
 *      defaultValues={
 *          "entity"={
 *              "icon"="fa-tasks"
 *          },
 *          "security"={
 *              "type"="ACL",
 *              "group_name"="",
 *              "category"="account_management"
 *          }
 *      }
 * )
 */
class Schedule
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="command", type="string", length=255)
     */
    protected $command;

    /**
     * @var array
     *
     * @ORM\Column(name="args", type = "json")
     */
    protected $arguments;

    /**
     * @var string
     *
     * @ORM\Column(name="args_hash", type="string", length=32)
     */
    protected $argumentsHash;

    /**
     * @var string
     *
     * @ORM\Column(name="definition", type="string", length=100, nullable=true)
     */
    protected $definition;

    /**
     * @var string
     *
     * @ORM\Column(name="overwrite_definition", type="string", length=100, nullable=true)
     */
    protected $overwriteDefinition;

    /**
     * @var boolean
     *
     * @ORM\Column(name="enabled", type="boolean", nullable=false)
     */
    protected $enabled = true;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", nullable=true, length=32)
     */
    protected $status;

    public function __construct()
    {
        $this->setArguments([]);
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
     * Get command name
     *
     * @return string
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * Set command name
     *
     * @param  string  $command
     * @return Schedule
     */
    public function setCommand($command)
    {
        $this->command = $command;
        $this->argumentsHash = $this->calculateHash($command, $this->arguments);

        return $this;
    }

    /**
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * @param array $arguments
     * @return $this
     */
    public function setArguments(array $arguments)
    {
        asort($arguments);

        $this->arguments = $arguments;
        $this->argumentsHash = $this->calculateHash($this->command, $arguments);

        return $this;
    }

    public static function calculateHash($command, array $arguments): string
    {
        asort($arguments);

        return md5(json_encode([$command, $arguments]));
    }

    /**
     * Returns cron definition string
     *
     * @return string
     */
    public function getDefinition()
    {
        return $this->definition;
    }

    /**
     * Set cron definition string
     *
     * General format:
     * *    *    *    *    *
     * ┬    ┬    ┬    ┬    ┬
     * │    │    │    │    │
     * │    │    │    │    │
     * │    │    │    │    └───── day of week (0 - 6) (0 to 6 are Sunday to Saturday, or use names)
     * │    │    │    └────────── month (1 - 12)
     * │    │    └─────────────── day of month (1 - 31)
     * │    └──────────────────── hour (0 - 23)
     * └───────────────────────── min (0 - 59)
     *
     * @param  string  $definition New cron definition
     * @return Schedule
     */
    public function setDefinition($definition)
    {
        $this->definition = $definition;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getId();
    }

    /**
     * @return string
     */
    public function getArgumentsHash()
    {
        return $this->argumentsHash;
    }

    /**
     * @return string
     */
    public function getOverwriteDefinition(): ?string
    {
        return $this->overwriteDefinition;
    }

    public function setOverwriteDefinition(string $overwriteDefinition = null)
    {
        $this->overwriteDefinition = $overwriteDefinition;
        return $this;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled)
    {
        $this->enabled = $enabled;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return Schedule
     */
    public function setStatus(?string $status)
    {
        $this->status = $status;
        return $this;
    }
}
