<?php

declare(strict_types=1);

namespace Artemeon\Orm\Tests\FieldMapper;

use Artemeon\Orm\Attribute\TableName;
use Artemeon\Orm\Attribute\TablePrimary;

#[TableName('sub_user')]
class TestSubTestUser extends TestUserModel
{
    #[TablePrimary('sub_user_id')]
    private string $subUserId;

    public function getSubUserId(): string
    {
        return $this->subUserId;
    }

    public function setSubUserId(string $subUserId): void
    {
        $this->subUserId = $subUserId;
    }
}
