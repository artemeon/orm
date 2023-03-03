<?php

namespace Artemeon\Orm\Tests\FieldMapper;

use Artemeon\Database\Schema\DataType;
use Artemeon\Orm\Attribute\TableColumn;
use Artemeon\Orm\Attribute\TableName;
use Artemeon\Orm\Attribute\TablePrimary;
use Artemeon\Orm\EntityInterface;

#[TableName('agp_system')]
class TestParent implements EntityInterface
{
    #[TablePrimary('system_id')]
    private string $systemId;

    #[TableColumn('owner', DataType::STR_TYPE_CHAR20)]
    private ?string $owner = null;

    public function getSystemId(): string
    {
        return $this->systemId;
    }

    public function setSystemId(string $systemId): void
    {
        $this->systemId = $systemId;
    }

    public function getOwner(): ?string
    {
        return $this->owner;
    }

    public function setOwner(?string $owner): void
    {
        $this->owner = $owner;
    }
}

