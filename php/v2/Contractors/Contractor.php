<?php

namespace NW\WebService\References\Operations\Notification\Contractors;

use Exception;
use NW\WebService\References\Operations\Notification\ResponseCodes;

class Contractor
{
    protected string $templateName = '';
    const TYPE_CUSTOMER = 0;
    public $id;
    public $type;
    public $name;

    public static function getById(int $resellerId): self
    {
        return new self($resellerId); // fakes the getById method
    }

    public function getFullName(): string
    {
        return $this->name . ' ' . $this->id;
    }

    /**
     * @throws Exception
     */
    public function checkFullName(): void
    {
        if (trim($this->getFullName()) === '') {
            throw new Exception("Template Data ($this->templateName) is empty!", ResponseCodes::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
