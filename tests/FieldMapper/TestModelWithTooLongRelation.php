<?php

declare(strict_types=1);

namespace Artemeon\Orm\Tests\FieldMapper;

use Artemeon\Orm\Attribute\OneToMany;
use Artemeon\Orm\Attribute\TableName;
use Artemeon\Orm\Attribute\TablePrimary;
use Artemeon\Orm\EntityInterface;
use Doctrine\Common\Collections\Collection;

#[TableName('my_model')]
class TestModelWithTooLongRelation implements EntityInterface
{
    #[TablePrimary('my_id')]
    private string $myId;

    /**
     * @var Collection<int, TestParent>|null
     */
    #[OneToMany('some_really_long__relation_name', 'contract_id', 'system_id', [TestParent::class])]
    private ?Collection $relations = null;

    public function getMyId(): string
    {
        return $this->myId;
    }

    public function setMyId(string $myId): void
    {
        $this->myId = $myId;
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
