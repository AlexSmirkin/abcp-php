<?php

namespace NW\WebService\References\Operations\Notification\Contractors;

use Exception;
use NW\WebService\References\Operations\Notification\ResponseCodes;

class Expert extends Contractor
{
    protected string $templateName = 'EXPERT_NAME';

    /**
     * @throws Exception
     */
    public static function getByIdOrFail($resellerId): Contractor
    {
        return new self($resellerId) ?? throw new Exception('Expert not found!', ResponseCodes::HTTP_NOT_FOUND);
    }
}
