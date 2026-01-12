<?php

declare(strict_types=1);

namespace Artemeon\Orm\Tests\FieldMapper;

use Artemeon\Database\Schema\DataType;
use Artemeon\Orm\Attribute\OneToMany;
use Artemeon\Orm\Attribute\TableColumn;
use Artemeon\Orm\Attribute\TableName;
use Artemeon\Orm\Attribute\TablePrimary;
use Doctrine\Common\Collections\Collection;

#[TableName('agp_contracts_con')]
class TestModel extends TestParent
{
    #[TablePrimary('contract_id')]
    private string $contractId;

    #[TableColumn('agp_contracts_con.servicerid', DataType::CHAR20)]
    private ?string $strServicerId = null;

    #[TableColumn('inhouseservice', DataType::INT)]
    private ?int $intInhouseService = null;

    #[TableColumn('outsourcing_i', DataType::CHAR20)]
    private ?string $outsourcingInstitution = null;

    #[TableColumn('purchasing_relevance', DataType::INT)]
    private ?int $purchasingRelevance = 0;

    /**
     * @var Collection<int, TestParent>|null
     */
    #[OneToMany('agp_contracts_con2foo', 'contract_id', 'system_id', [TestParent::class])]
    private ?Collection $relations = null;

    public function getContractId(): string
    {
        return $this->contractId;
    }

    public function setContractId(string $contractId): void
    {
        $this->contractId = $contractId;
    }

    public function getStrServicerId(): ?string
    {
        return $this->strServicerId;
    }

    public function setStrServicerId(?string $strServicerId): void
    {
        $this->strServicerId = $strServicerId;
    }

    public function getIntInhouseService(): ?int
    {
        return $this->intInhouseService;
    }

    public function setIntInhouseService(?int $intInhouseService): void
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

    /**
     * @return Collection<int, TestParent>|null
     */
    public function getRelations(): ?Collection
    {
        return $this->relations;
    }

    /**
     * @param Collection<int, TestParent>|null $relations
     */
    public function setRelations(?Collection $relations): void
    {
        $this->relations = $relations;
    }
}
