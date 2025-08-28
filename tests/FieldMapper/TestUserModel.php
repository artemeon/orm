<?php

declare(strict_types=1);

namespace Artemeon\Orm\Tests\FieldMapper;

use Artemeon\Orm\Attribute\TableName;
use Artemeon\Orm\Attribute\TablePrimary;
use Artemeon\Orm\EntityInterface;

#[TableName('user')]
class TestUserModel implements EntityInterface
{
    #[TablePrimary('user_id')]
    private string $userId;

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function setUserId(string $userId): void
    {
        $this->userId = $userId;
    }
}
