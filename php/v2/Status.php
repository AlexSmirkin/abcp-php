<?php

namespace NW\WebService\References\Operations\Notification;

use Exception;

class Status
{

    public $id, $name;

    private static array $statuses = [
        0 => 'Completed',
        1 => 'Pending',
        2 => 'Rejected',
    ];

    /**
     * @throws Exception
     */
    public static function getName(int $id): string
    {
        return self::$statuses[$id] ?? throw new Exception('Not valid status id', ResponseCodes::HTTP_INTERNAL_SERVER_ERROR);
    }

    public static function getKeys(): array
    {
        return array_keys(self::$statuses);
    }
}
