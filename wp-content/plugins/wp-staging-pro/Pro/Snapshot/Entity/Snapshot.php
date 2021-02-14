<?php
//TODO PHP7.x; declare(strict_types=1);
//TODO PHP7.x; type-hints and return types

namespace WPStaging\Pro\Snapshot\Entity;

use DateTime;
use WPStaging\Framework\Entity\AbstractEntity;
use WPStaging\Framework\Entity\IdentifyableEntityInterface;
use WPStaging\Framework\Utils\Size;

class Snapshot extends AbstractEntity implements IdentifyableEntityInterface
{

    const TYPE_DATABASE = 'database';
    const TYPE_SITE = 'site';

    /** @var string */
    private $id;

    /** @var string */
    private $name;

    /** @var string */
    private $notes;

    /** @var string */
    private $type;

    /** @var array */
    private $directories;

    /** @var string */
    private $filePath;

    /** @var DateTime */
    private $createdAt;

    /** @var DateTime */
    private $updatedAt;

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string|null
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * @param string|null $notes
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;
    }

    /**
     * @return string
     */
    public function getType()
    {
        // Backward compatibility
        if (!$this->type) {
            $this->type = self::TYPE_DATABASE;
        }
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return array
     */
    public function getDirectories()
    {
        return $this->directories;
    }

    public function setDirectories(array $directories = null)
    {
        $this->directories = $directories;
    }

    /**
     * @return string
     */
    public function getFilePath()
    {
        return $this->filePath;
    }

    /**
     * @param string $filePath
     */
    public function setFilePath($filePath)
    {
        $this->filePath = $filePath;
    }

    /**
     * @return DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return DateTime|null
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param DateTime|null $updatedAt
     */
    public function setUpdatedAt(DateTime $updatedAt = null)
    {
        $this->updatedAt = $updatedAt;
    }

    public function getFileSize()
    {
        if (!$this->isValidFile()) {
            return '0B';
        }

        clearstatcache();
        return (new Size)->toUnit(filesize($this->filePath));
    }

    public function isValidFile()
    {
        return $this->filePath && is_file($this->filePath) && file_exists($this->filePath);
    }

    public function getUrlDownload()
    {
        if (!$this->isValidFile()) {
            return null;
        }

        return '/' . str_replace(ABSPATH, null, $this->filePath);
    }
}
