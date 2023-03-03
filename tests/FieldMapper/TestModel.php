<?php

namespace Artemeon\Orm\Tests\FieldMapper;

use Artemeon\Database\Schema\DataType;
use Artemeon\Orm\Attribute\TableColumn;
use Artemeon\Orm\Attribute\TableName;
use Artemeon\Orm\Attribute\TablePrimary;

#[TableName('agp_contracts_con')]
class TestModel extends TestParent
{
    #[TablePrimary('contract_id')]
    private string $contractId;

    #[TableColumn('servicerid', DataType::STR_TYPE_CHAR20)]
    private $strServicerId;

    #[TableColumn('inhouseservice', DataType::STR_TYPE_INT)]
    private $intInhouseService;

    #[TableColumn('outsourcing_i', DataType::STR_TYPE_CHAR20)]
    private ?string $outsourcingInstitution = null;

    #[TableColumn('purchasing_relevance', DataType::STR_TYPE_INT)]
    private ?int $purchasingRelevance = 0;

    public function getContractId(): string
    {
        return $this->contractId;
    }

    public function setContractId(string $contractId): void
    {
        $this->contractId = $contractId;
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

