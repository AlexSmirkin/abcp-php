<?php

namespace NW\WebService\References\Operations\Notification;

use Exception;
use NW\WebService\References\Operations\Notification\Contractors\Contractor;
use NW\WebService\References\Operations\Notification\Contractors\Creator;
use NW\WebService\References\Operations\Notification\Contractors\Expert;

final class OperationRequest
{
    private int|bool $resellerId;
    private int $notificationType;
    private int $clientId;
    private int $creatorId;
    private int $expertId;
    private int $complaintId;
    private int $consumptionId;
    private string $complaintNumber;
    private string $consumptionNumber;
    private string $agreementNumber;
    private string $date;
    private array $differences = [];

    /**
     * @throws Exception
     */
    public function __construct(array $request)
    {
        $required = ['complaintNumber', 'consumptionNumber', 'agreementNumber', 'date'];
        $intRequired = ['notificationType', 'clientId', 'creatorId', 'expertId', 'complaintId', 'consumptionId'];
        $allRequired = [...$required, ...$intRequired];

        foreach ($allRequired as $key) {
            if (!isset($request[$key]) && in_array($key, $allRequired)) {
                throw new Exception("Empty $key", ResponseCodes::HTTP_BAD_REQUEST);
            }
            if (!is_numeric($request[$key]) && in_array($key, $intRequired)) {
                throw new Exception("$key not a number", ResponseCodes::HTTP_BAD_REQUEST);
            }
        }

        $this->resellerId = isset($request['resellerId']) ? (int)$request['resellerId'] : false;
        $this->notificationType = (int)$request['notificationType'];
        $this->clientId = (int)$request['clientId'];
        $this->creatorId = (int)$request['creatorId'];
        $this->expertId = (int)$request['expertId'];
        $this->complaintId = (int)$request['complaintId'];
        $this->consumptionId = (int)$request['consumptionId'];
        $this->complaintNumber = (string)$request['complaintNumber'];
        $this->consumptionNumber = (string)$request['consumptionNumber'];
        $this->agreementNumber = (string)$request['agreementNumber'];
        $this->date = (string)$request['date'];

        if (is_numeric($request['differences']['from']) && in_array($request['differences']['from'], Status::getKeys())) {
            $this->differences['from'] = (int)$request['differences']['from'];
        }

        if (is_numeric($request['differences']['to']) && in_array($request['differences']['from'], Status::getKeys())) {
            $this->differences['to'] = (int)$request['differences']['to'];
        }
    }

    public function getResellerId(): int|bool
    {
        return $this->resellerId;
    }

    public function isNewNotificationType(): bool
    {
        return $this->notificationType === TsReturnOperation::TYPE_NEW;
    }

    public function isChangeNotificationType(): bool
    {
        return $this->notificationType === TsReturnOperation::TYPE_CHANGE;
    }

    public function getClientId(): int
    {
        return $this->clientId;
    }

    public function issetFromDifferences(): bool
    {
        return isset($this->differences['from']);
    }

    /**
     * @throws Exception
     */
    public function getFromDifferences(): int|Exception
    {
        if (isset($this->differences['from'])) {
            return $this->differences['from'];
        }

        throw new Exception('Differences from not valid', ResponseCodes::HTTP_BAD_REQUEST);
    }

    public function issetToDifferences(): bool
    {
        return isset($this->differences['to']);
    }

    /**
     * @throws Exception
     */
    public function getToDifferences(): int|Exception
    {
        if (isset($this->differences['to'])) {
            return $this->differences['to'];
        }

        throw new Exception('Differences to not valid', ResponseCodes::HTTP_BAD_REQUEST);
    }

    public function issetFromAndToDifferences(): bool
    {
        return $this->issetFromDifferences() && $this->issetToDifferences();
    }

    /**
     * @throws Exception
     */
    public function getTemplateData(Contractor $client): array|Exception
    {
        $creator = Creator::getByIdOrFail($this->creatorId);
        $expert = Expert::getByIdOrFail($this->expertId);

        $differences = match (true) {
            $this->isNewNotificationType() => __('NewPositionAdded', null, $this->getResellerId()),
            $this->isChangeNotificationType() && $this->issetFromAndToDifferences() => __('PositionStatusHasChanged', [
                'FROM' => Status::getName($this->getFromDifferences()),
                'TO'   => Status::getName($this->getToDifferences()),
            ], $this->getResellerId()),
            default => throw new Exception("Template Data (DIFFERENCES) is empty!", ResponseCodes::HTTP_INTERNAL_SERVER_ERROR),
        };

        foreach ([$creator, $expert, $client] as $contractor) {
            $contractor->checkFullName();
        }

        return [
            'COMPLAINT_ID'       => $this->complaintId,
            'COMPLAINT_NUMBER'   => $this->complaintNumber,
            'CREATOR_ID'         => $this->creatorId,
            'CREATOR_NAME'       => $creator->getFullName(),
            'EXPERT_ID'          => $this->expertId,
            'EXPERT_NAME'        => $expert->getFullName(),
            'CLIENT_ID'          => $this->clientId,
            'CLIENT_NAME'        => $client->getFullName(),
            'CONSUMPTION_ID'     => $this->consumptionId,
            'CONSUMPTION_NUMBER' => $this->consumptionNumber,
            'AGREEMENT_NUMBER'   => $this->agreementNumber,
            'DATE'               => $this->date,
            'DIFFERENCES'        => $differences,
        ];
    }
}
