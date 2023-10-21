<?php

namespace NW\WebService\References\Operations\Notification\Contractors;

use Exception;
use NW\WebService\References\Operations\Notification\ResponseCodes;

class Creator extends Contractor
{
    protected string $templateName = 'CREATOR_NAME';

    /**
     * @throws Exception
     */
    public static function getByIdOrFail($resellerId): Contractor
    {
         return new self($resellerId) ?? throw new Exception('Creator not found!', ResponseCodes::HTTP_NOT_FOUND);
    }
}
