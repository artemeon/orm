<?php

declare(strict_types=1);

namespace Artemeon\Orm\Exception;

use Exception;

/**
 * Most exceptions thrown by the orm system will use the OrmException type in order
 * to react with special catch-blocks.
 */
class OrmException extends Exception
{
}
