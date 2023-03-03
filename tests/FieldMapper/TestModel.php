<?php

namespace Artemeon\Orm\Tests\FieldMapper;

use Artemeon\Database\Schema\DataType;
use Artemeon\Orm\Attribute\TableColumn;
use Artemeon\Orm\Attribute\TableName;
use Artemeon\Orm\Attribute\TablePrimary;
use Artemeon\Orm\EntityInterface;

#[TableName('agp_contracts_con')]
class TestModel implements EntityInterface
{
    #[TablePrimary('test_id')]
    private string $id;

    #[TableColumn('servicerid', DataType::STR_TYPE_CHAR20)]
    private $strServicerId;

    #[TableColumn('inhouseservice', DataType::STR_TYPE_INT)]
    private $intInhouseService;

    #[TableColumn('outsourcing_i', DataType::STR_TYPE_CHAR20)]
    private ?string $outsourcingInstitution = null;

    #[TableColumn('purchasing_relevance', DataType::STR_TYPE_INT)]
    private ?int $purchasingRelevance = 0;

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getStrServicerId()
    {
        return $this->strServicerId;
    }

    public function setStrServicerId($strServicerId)
    {
        $this->strServicerId = $strServicerId;
    }

    public function getIntInhouseService()
    {
        return $this->intInhouseService;
    }

    public function setIntInhouseService($intInhouseService)
    {
        $this->intInhouseService = $intInhouseService;
    }

    public function getOutsourcingInstitution(): ?string
    {
        return $this->outsourcingInstitution;
    }

    public function setOutsourcingInstitution(?string $outsourcingInstitution): void
    {
        $this->outsourcingInstitution = $outsourcingInstitution;
    }

    public function getPurchasingRelevance(): ?int
    {
        return $this->purchasingRelevance;
    }

    public function setPurchasingRelevance(?int $purchasingRelevance): void
    {
        $this->purchasingRelevance = $purchasingRelevance;
    }
}

