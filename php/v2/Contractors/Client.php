<?php

namespace NW\WebService\References\Operations\Notification\Contractors;

use Exception;
use NW\WebService\References\Operations\Notification\ResponseCodes;

class Client extends Contractor
{
    protected string $templateName = 'CLIENT_NAME';

    /**
     * @throws Exception
     */
    public static function getByIdOrFail($resellerId): Contractor
    {
        $client = new self($resellerId);
        if ($client === null || $client->type !== self::TYPE_CUSTOMER || $client->Seller->id !== $resellerId) {
            throw new Exception('Client not found!', ResponseCodes::HTTP_NOT_FOUND);
        }

        return $client;
    }
}
