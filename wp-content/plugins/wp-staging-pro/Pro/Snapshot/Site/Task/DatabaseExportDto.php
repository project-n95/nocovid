<?php


namespace WPStaging\Pro\Snapshot\Site\Task;


use WPStaging\Component\Dto\AbstractRequestDto;

class DatabaseExportDto extends AbstractRequestDto
{
    /** @var string */
    private $fileName;

    /** @var int */
    private $tableRowsExported;

    /** @var int */
    private $tableRowsOffset;


    /**
     * @return int
     */
    public function getTableRowsOffset()
    {
        return $this->tableRowsOffset;
    }

    /**
     * @param int $tableRowsOffset
     */
    public function setTableRowsOffset($tableRowsOffset)
    {
        $this->tableRowsOffset = $tableRowsOffset;
    }

    /**
     * @return int
     */
    public function getTableRowsExported()
    {
        return $this->tableRowsExported;
    }

    /**
     * @param int $tableRowsCompleted
     */
    public function setTableRowsExported($tableRowsExported)
    {
        $this->tableRowsExported = $tableRowsExported;
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * @param string $fileName
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
    }
}