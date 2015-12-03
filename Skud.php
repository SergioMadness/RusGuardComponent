<?php

namespace datalayerru\skud;


class Skud
{
    /**
     * URI of WSDL
     *
     * @var type string
     */
    public $url;

    /**
     * SoapClient object
     *
     * @var type SoapClient
     */
    private $SOAPClient;

    /**
     * Connection ID
     *
     * @var string
     */
    private $connectioId;

    /**
     * SOAP location
     *
     * @var string
     */
    public $location;

    /**
     * Login to skud
     *
     * @var string
     */
    public $login;

    /**
     * Password to skud
     *
     * @var string
     */
    public $password;

    /**
     * Adding of auth header
     */
    private function addHeader()
    {
        $auth = '<NS1:Security xmlns:NS1="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd"><NS2:Timestamp xmlns:NS2="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd"><Created xmlns="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">' . date('Y-m-d\TH:i:s.811P') . '</Created><Expires xmlns="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">' . date('Y-m-d\TH:i:s.811P',
                time() + (5 * 60)) . '</Expires></NS2:Timestamp><NS1:UsernameToken><Username xmlns="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">' . $this->login . '</Username><Password xmlns="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">' . $this->password . '</Password></NS1:UsernameToken></NS1:Security>';


        $authvalues = new \SoapVar($auth, XSD_ANYXML);
        $header     = new \SoapHeader("http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd",
            "Security", $authvalues, true);

        $this->getSoap()->__setSoapHeaders($header);
    }

    /**
     * Get SOAP object
     *
     * @return \SoapClient
     */
    protected function getSoap()
    {
        if ($this->SOAPClient === null) {
            $this->SOAPClient = new \SoapClient($this->url,
                [
                    'trace' => 1,
                    'cache_wsdl' => WSDL_CACHE_NONE
                ]);
            if ($this->location) {
                $this->SOAPClient->__setLocation($this->location);
            }
        }
        return $this->SOAPClient;
    }

    /**
     * 'Connect' method
     *
     * @return boolean
     */
    public function connect()
    {
        try {
            if (empty($this->connectioId)) {
                $this->addHeader();
                $connectionResult  = $this->getSoap()->Connect();
                $this->connectioId = $connectionResult->ConnectResult;
            }
            $result = true;
        } catch (\SoapFault $ex) {
            $result = false;
        }
        return $result;
    }

    /**
     * Long polling request
     *
     * getNotification response:
     * $result = stdClass Object
     * (
     *   [DateTime] => 2015-08-06T11:35:53
     *   [Details] => По ключу Номер ключа 20033968059153 (0x1235EFF02411)
     *   [DriverId] => 8a041882-7d14-4ef1-9df6-0ceba9f396a3
     *   [EmployeeId] => 6a3432c6-c93f-4cd1-8796-c0fg45e77143
     *   [IsKeyEvent] =>
     *   [LogMessageId] =>
     *   [Message] => Выход
     *   [MessageSubType] => AccessPointExitByKey
     *   [MessageType] => Information
     *   [OperatorId] =>
     *   [EmployeeFirstName] => Имя
     *   [EmployeeGroupFullPath] => Группа
     *   [EmployeeLastName] => Фамилия
     *   [EmployeePosition] => Должность
     *   [EmployeeSecondName] => Отчество
     * )
     *
     * @return mixed
     */
    public function getNotification()
    {
        $result = false;
        try {
            $this->addHeader();
            $connectionResult = $this->getSoap()->GetNotification([
                'connectionId' => $this->connectioId
            ]);
            $result           = $connectionResult->GetNotificationResult->EmployeePassageNotifications->EmployeePassageNotification;
        } catch (\SoapFault $ex) {
            $result = false;
        } catch (\Exception $ex) {

        }
        return $result;
    }

    /**
     * 'Disconnect' method
     *
     * @return boolean
     */
    public function disconnect()
    {
        $result = $this->getSoap()->Disconnect();
        return $result !== null;
    }

    /**
     * Add new Employee
     *
     * @param string $groupId
     * @param string $firstName
     * @param string $lastName
     * @return \datalayer\rusguard\Employee
     */
    public function addEmployee($groupId, $firstName = 'Гость',
                                $lastName = 'Гость', $secondName = '')
    {
        $result = null;
        $this->addHeader();
        try {
            $data                         = new \stdClass();
            $data->employeeGroupID        = $groupId;
            $data->data                   = new \stdClass();
            $data->data->FirstName        = $firstName;
            $data->data->LastName         = $lastName;
            $data->data->SecondName       = $secondName;
            $data->data->CreationDateTime = date('Y-m-d\TH:i:s.811P');
            $data->data->EmployeeGroupID  = $groupId;

            $result = new Employee((array)$this->getSoap()->AddAcsEmployee($data)->AddAcsEmployeeResult);
        } catch (\SoapFault $ex) {
            $this->logError();
        }
        return $result;
    }

    /**
     * Update employee's info
     *
     * @param integer $id
     * @param string $firstName
     * @param string $lastName
     * @param string $secondName
     * @return bool
     */
    public function updateEmployee($id, $firstName, $lastName, $secondName = '')
    {
        $result = false;
        $this->addHeader();
        try {
            $data                             = new \stdClass();
            $data->id                         = $id;
            $data->data                       = new \stdClass();
            $data->data->FirstName            = $firstName;
            $data->data->LastName             = $lastName;
            $data->data->SecondName           = $secondName;
            $data->data->ModificationDateTime = date('Y-m-d\TH:i:s.811P');

            $this->getSoap()->SaveAcsEmployee($data);
            $result = true;
        } catch (\SoapFault $ex) {
            $this->logError();
        }
        return $result;
    }

    /**
     * Assigning key to Employee
     *
     * @param string $employeeId
     * @param integer $cardNumber
     * @param boolean $force
     * @return object
     */
    public function assignKey($employeeId, $cardNumber, $force = false)
    {
        $cardNumber = $this->prepareKey($cardNumber);
        $result     = null;
        $this->addHeader();
        try {
            $data                       = new \stdClass();
            $data->employeeId           = $employeeId;
            $data->indexNumber          = 1;
            $data->keyData              = new \stdClass();
            $data->keyData->KeyNumber   = $cardNumber;
            $data->keyData->StartDate   = date('Y-m-d\TH:i:s.811P',
                time() - 10 * 60);
            $data->keyData->EndDate     = date('Y-m-d\TH:i:s.811P',
                time() + 60 * 60 * 24 * 365);
            $data->keyData->Description = 'Карта номер:' . $cardNumber;

            if (!$force) {
                $result = $this->getSoap()->AssignAcsKeyForEmployee($data);
            } else {
                $result = $this->getSoap()->ForceAssignAcsKeyForEmployee($data);
            }
        } catch (\SoapFault $ex) {
            $this->logError();
        }
        return $result;
    }

    /**
     * Sets key's status
     *
     * @param string $cardNumber
     * @param string $employeeId
     * @param integer $index
     * @return mixed
     */
    public function setKeyIsLost($cardNumber, $employeeId, $index = 1)
    {
        $result     = false;
        $cardNumber = $this->prepareKey($cardNumber);
        $this->addHeader();
        try {
            $result = $this->getSoap()->SetStatusOfAcsKeyAsLost([
                'keyNumber' => $cardNumber,
                'indexNumber' => $index,
                'employeeID' => $employeeId
            ]);
        } catch (\SoapFault $ex) {
            $this->logError();
        }
        return $result;
    }

    /**
     * Returnes employees's id by card number
     *
     * @param string $cardNumber
     * @return integer
     */
    public function getEmployeeIdByKey($cardNumber)
    {
        $cardNumber = $this->prepareKey($cardNumber);
        $result     = null;
        $this->addHeader();
        try {
            $soapResult = $this->getSoap()->GetAssignedAcsKeyByKeyNumber([
                'keyNumber' => $cardNumber
            ]);
            if (isset($soapResult->GetAssignedAcsKeyByKeyNumberResult->AcsEmployeeId)) {
                $result = $soapResult->GetAssignedAcsKeyByKeyNumberResult->AcsEmployeeId;
            }
        } catch (\SoapFault $ex) {
            $this->logError();
        }

        return $result;
    }

    /**
     * Employee removing
     *
     * @param string $id
     * @return object
     */
    public function removeEmployee($id)
    {
        $result = null;
        $this->addHeader();
        try {
            $result = $this->getSoap()->RemoveAcsEmployee([
                'id' => $id
            ]);
        } catch (\SoapFault $ex) {
            $this->logError();
        }
        return $result;
    }

    /**
     * Removing employee by card number
     *
     * @param string $cardNumber
     * @return object
     */
    public function removeEmployeeByCardNumber($cardNumber)
    {
        $cardNumber = $this->prepareKey($cardNumber);
        $result     = null;
        $this->addHeader();
        try {
            $empResult = $this->getSoap()->GetAssignedAcsKeyByKeyNumber([
                'keyNumber' => $cardNumber
            ]);
            if ($empResult->GetAssignedAcsKeyByKeyNumberResult !== null) {
                $result = $this->removeEmployee($empResult->GetAssignedAcsKeyByKeyNumberResult->AcsEmployeeId);
            }
        } catch (\SoapFault $ex) {
            $this->logError();
        }
        return $result;
    }

    /**
     * Get group list
     *
     * @return \datalayer\rusguard\EmployeeGroup[]
     */
    public function getGroups()
    {
        $result = [];

        $this->addHeader();
        try {
            $rawGroups = $this->getSoap()->GetAcsEmployeeGroups()->GetAcsEmployeeGroupsResult;
            if (isset($rawGroups->AcsEmployeeGroup) && is_array($rawGroups->AcsEmployeeGroup)) {
                $rawGroups = $rawGroups->AcsEmployeeGroup;
            }
            foreach ($rawGroups as $group) {
                $result[] = new EmployeeGroup((array)$group);
            }
        } catch (\SoapFault $ex) {
            $this->logError();
        }

        return $result;
    }

    /**
     * Get variable
     *
     * @param string $name
     * @return object
     */
    public function getVariable($name)
    {
        $result = null;
        $this->addHeader();
        try {
            $result = $this->getSoap()->GetVariable([
                'name' => $name
            ]);
        } catch (\SoapFault $ex) {
            $this->logError();
        }
        return $result;
    }

    /**
     * Addes photo to employee's profile
     *
     * @param string $employeeId
     * @param integer $index
     * @param string $base64Data
     * @return mixed
     */
    public function addEmployeePhoto($employeeId, $index, $base64Data)
    {
        $result = false;

        $indexes   = [1, 2];
        $freeIndex = 0;

        if ($index === null) {
            foreach ($indexes as $index) {
                if (($photoInfo = $this->getEmployeePhoto($employeeId, $index)) !== false
                    && (!isset($photoInfo->GetAcsEmployeePhotoResult) || $photoInfo->GetAcsEmployeePhotoResult
                        == null)
                ) {
                    $freeIndex = $index;
                    break;
                }
            }
        } else {
            $freeIndex = $index;
        }
        if ($freeIndex > 0) {
            $this->addHeader();
            try {
                $result = $this->getSoap()->SetAcsEmployeePhoto([
                    'employeeId' => $employeeId,
                    'photoNumber' => $freeIndex,
                    'data' => $base64Data
                ]);
            } catch (\SoapFault $ex) {
                $result = false;
                $this->logError();
            }
        }
        return $result;
    }

    /**
     * Get employee's photo by index
     *
     * @param string $employeeId
     * @param integer $index
     * @return mixed
     */
    public function getEmployeePhoto($employeeId, $index)
    {
        $this->addHeader();
        try {
            $result = $this->getSoap()->GetAcsEmployeePhoto([
                'employeeId' => $employeeId,
                'photoNumber' => $index
            ]);
        } catch (\SoapFault $ex) {
            $result = false;
            $this->logError();
        }
        return $result;
    }

    /**
     * Get employee
     *
     * @param string $employeeId
     * @return Employee
     */
    public function getEmployee($employeeId)
    {
        $result = null;
        $this->addHeader();
        try {
            $employee = $this->getSoap()->GetAcsEmployee([
                'id' => $employeeId
            ]);
            if (isset($employee->GetAcsEmployeeResult)) {
                $result = new Employee((array)$employee->GetAcsEmployeeResult);
            }
        } catch (\SoapFault $ex) {
            $result = false;
            $this->logError();
        }
        return $result;
    }

    /**
     * Example:
     * stdClass Object
     * (
     *     [GetEventsResult] => stdClass Object
     *         (
     *             [Count] => 168
     *             [Messages] => stdClass Object
     *                 (
     *                     [LogMessage] => Array
     *                         (
     *                             [0] => stdClass Object
     *                                 (
     *                                     [ContentData] =>
     *                                     [ContentType] =>
     *                                     [DateTime] => 2015-08-12T00:02:20
     *                                     [Details] => По кнопке турникета
     *                                     [DriverID] => 8a041882-7d14-4ef1-9df6-0ceba9f396a3
     *                                     [DriverName] => Турникет 5687
     *                                     [EmployeeFirstName] =>
     *                                     [EmployeeGroupFullName] =>
     *                                     [EmployeeGroupId] =>
     *                                     [EmployeeGroupName] =>
     *                                     [EmployeeID] =>
     *                                     [EmployeeLastName] =>
     *                                     [EmployeeSecondName] =>
     *                                     [Id] => 22700
     *                                     [LogMessageSubType] => AccessPointPassUnknown
     *                                     [LogMessageType] => Information
     *                                     [Message] => Проход
     *                                     [OperatorFullName] =>
     *                                     [OperatorID] =>
     *                                     [OperatorLogin] =>
     *                                     [ServerId] => 0a60e2df-51a2-4be8-ba3b-88afe8251076
     *                                     [ServerName] => NVR-1
     *                                 )
     *
     *                             [11] => stdClass Object
     *                                 (
     *                                     [ContentData] =>
     *                                     [ContentType] =>
     *                                     [DateTime] => 2015-08-12T07:31:52
     *                                     [Details] => По ключу Номер ключа 4489993822848 (0x041411C29900)
     *                                     [DriverID] => 8a041882-7d14-4ef1-9df6-0ceba9f396a3
     *                                     [DriverName] => Турникет 6547
     *                                     [EmployeeFirstName] => Иван
     *                                     [EmployeeGroupFullName] => Управляющая компания
     *                                     [EmployeeGroupId] => ee04428e-4519-4d48-8e65-e9e0d2313687
     *                                     [EmployeeGroupName] => Управляющая компания
     *                                     [EmployeeID] => 53549f4b-946a-47f8-b904-9fc8f5f88783
     *                                     [EmployeeLastName] => Иванов
     *                                     [EmployeeSecondName] => Иванович
     *                                     [Id] => 22711
     *                                     [LogMessageSubType] => AccessPointEntryByKey
     *                                     [LogMessageType] => Information
     *                                     [Message] => Вход
     *                                     [OperatorFullName] =>
     *                                     [OperatorID] =>
     *                                     [OperatorLogin] =>
     *                                     [ServerId] => 0a60e2df-52a2-4be8-ba3b-88afe9251076
     *                                     [ServerName] => SERV-1
     *                                 )
     *                         )
     *                 )
     *         )
     * )
     *
     * @param string $from
     * @param string $to
     * @param integer $page
     * @param integer $pageSize
     * @return mixed
     */
    public function getEvents($from = null, $to = null, $inout = null,
                              $page = 1, $pageSize = 20)
    {
        $msgSubTypes = [];
        switch ($inout) {
            case 0:
                $msgSubTypes[] = 'AccessPointEntryByKey';
                break;
            case 1:
                $msgSubTypes[] = 'AccessPointExitByKey';
                break;
        }
        if ($from === null) {
            $from = date('c', strtotime(date('d.m.Y 00:00:00')));
        }
        if ($to === null) {
            $to = date('c', strtotime(date('d.m.Y H:i:s')));
        }

        $this->addHeader();
        try {
            $result = $this->getSoap()->GetEvents([
                'fromDateTime' => $from,
                'toDateTime' => $to,
                'pageNumber' => $page,
                'pageSize' => $pageSize,
                'msgSubTypes' => $msgSubTypes
            ]);
            if (isset($result->GetEventsResult)) {
                $result = $result->GetEventsResult;
            }
        } catch (\SoapFault $ex) {
            $result = false;
            $this->logError();
        }
        return $result;
    }

    /**
     * Logging errors
     */
    protected function logError()
    {
        $errorMsg = "Request:\n" . $this->getSoap()->__getLastRequest() .
            "\n<br>" .
            $this->getSoap()->__getLastRequestHeaders() .
            "\n\n\n\n<br><br><br><br>"
            . "Response:\n<br>" .
            $this->getSoap()->__getLastResponse() .
            "\n<br>" .
            $this->getSoap()->__getLastResponseHeaders();
    }

    /**
     * Cuts key number
     *
     * @param string $cardNumber
     * @return float
     */
    protected function prepareKey($cardNumber)
    {
        return floatval(mb_substr($cardNumber, -14));
    }
}