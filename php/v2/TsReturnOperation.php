<?php

namespace NW\WebService\References\Operations\Notification;

use Exception;
use NW\WebService\References\Operations\Notification\Contractors\Client;

class TsReturnOperation extends ReferencesOperation
{
    public const TYPE_NEW = 1;
    public const TYPE_CHANGE = 2;
    private array $result;

    public function __construct()
    {
        $this->result = [
            'notificationEmployeeByEmail' => false,
            'notificationClientByEmail'   => false,
            'notificationClientBySms'     => [
                'isSent'  => false,
                'message' => '',
            ],
        ];
    }

    /**
     * @throws Exception
     */
    public function doOperation(): array
    {
        $operationRequest = new OperationRequest($this->getRequest('data'));

        if ($operationRequest->getResellerId() === false) {
            $this->result['notificationClientBySms']['message'] = 'Empty resellerId';

            return $this->result;
        }

        $this->sendMessage($operationRequest, true);

        // Шлём клиентское уведомление, только если произошла смена статуса
        if ($operationRequest->isChangeNotificationType() && $operationRequest->issetToDifferences()) {
            $this->sendMessage($operationRequest, false);
        }

        return $this->result;
    }

    public function sendMessage(OperationRequest $operationRequest, bool $forEmployees): void
    {
        $clientId = null;
        $statusTo = null;
        $resellerId = $operationRequest->getResellerId();
        $toDifferences = $operationRequest->getToDifferences();
        $client = Client::getByIdOrFail($operationRequest->getClientId());
        $templateData = $operationRequest->getTemplateData($client);
        $emailFrom = getResellerEmailFrom($resellerId);

        $createEmail = static function (string $emailFrom, array $emailTo, bool $isEmployee) use ($templateData, $resellerId) {
            $emails = [];
            foreach ($emailTo as $email) {
                $emails[] = [
                    [ // MessageTypes::EMAIL
                        'emailFrom' => $emailFrom,
                        'emailTo'   => $email,
                        'subject'   => __($isEmployee ? 'complaintEmployeeEmailSubject' : 'complaintClientEmailSubject', $templateData, $resellerId),
                        'message'   => __($isEmployee ? 'complaintEmployeeEmailBody' : 'complaintClientEmailBody', $templateData, $resellerId),
                    ]
                ];
            }
            return $emails;
        };

        if ($emailFrom) {
            if ($forEmployees) {
                $employeeEmails = getEmailsByPermit($resellerId, 'tsGoodsReturn');
                $emails = $createEmail($emailFrom, $employeeEmails, true);
            } elseif ($client->email) {
                $clientId = $client->id;
                $statusTo = $toDifferences;
                $emails = $createEmail($emailFrom, [$client->email], false);
            }

            MessagesClient::sendMessage($emails, $resellerId, $clientId, NotificationEvents::CHANGE_RETURN_STATUS, $statusTo);

            if ($forEmployees) {
                $this->result['notificationEmployeeByEmail'] = true;
            } else {
                $this->result['notificationClientByEmail'] = true;
            }
        }

        if (!$forEmployees && $client->mobile) {
            $error = '';
            $res = NotificationManager::send($resellerId, $client->id, NotificationEvents::CHANGE_RETURN_STATUS, $toDifferences, $templateData, $error);
            if ($res) {
                $this->result['notificationClientBySms']['isSent'] = true;
            }
            if ($error) {
                $this->result['notificationClientBySms']['message'] = $error;
            }
        }
    }
}
