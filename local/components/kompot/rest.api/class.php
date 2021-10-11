<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
use Bitrix\Main\Loader;
use Bitrix\Iblock\Component\Tools as IblockTools;

class CRestHelper extends CBitrixComponent
{
    /** @var int ID инфоблока основной информации */
    const IBLOCK_SETTINGS_ID = 1;
    /** @var int ID инфоблока баннеров */
    const IBLOCK_BANNER_ID = 2;
    /** @var int ID инфоблока вакансий */
    const IBLOCK_VACANCY_ID = 3;
    /** @var int ID инфоблока заявок на вакансию */
    const IBLOCK_VACANCY_REQUEST_ID = 4;
    /** @var int ID инфоблока геопозиций */
    const IBLOCK_LOCATION_ID = 5;


    /**
     * Поиск метода по параметрам $entity и $method
     * @param string $requestMethod
     * @param $entity
     * @param $method
     * @return bool|string
     */
    public function findMethod ($requestMethod = 'GET', $entity, $method)
    {
        $functionName = '';
        if ($requestMethod == 'GET') {
            $functionName = 'get' . ucfirst($entity) . ucfirst($method);
        } else if ($requestMethod == 'POST') {
            $functionName = 'post' . ucfirst($entity) . ucfirst($method);
        }

        if (method_exists($this, $functionName)) {
            return $functionName;
        } else {
            return false;
        }
    }

    /**
     * Запуск метода
     * @param $entity
     * @param $method
     * @throws \Bitrix\Main\LoaderException
     */
    public function runMethod ($entity, $method)
    {
        $entity = htmlspecialchars(strip_tags($entity));
        $method = htmlspecialchars(strip_tags($method));

        $functionName = $this->findMethod($_SERVER['REQUEST_METHOD'], $entity, $method);
        if (!empty($functionName)) {
            $this->$functionName();
        } else {
            $this->show404("Method '$entity/$method/' not found!");
        }
    }

    /**
     * Вывод 404
     * @param string $pageString
     * @throws \Bitrix\Main\LoaderException
     */
    public function show404 ($pageString = '')
    {
        $pageString = $pageString ? $pageString : '404 not found';
        if (Loader::IncludeModule('iblock')) {
            IblockTools::process404(
                $pageString
                , true
                , true
                , true
            );
        }
    }

    /**
     * Успешный ответ
     * @param $data
     */
    public function successAnswer ($data)
    {
        $this->response(true, null, $data);
    }

    /**
     * Ответ с ошибкой
     * @param $error
     */
    public function errorAnswer ($error)
    {
        $this->response(false, $error, null);
    }

    /**
     * Ответ в json формате
     * @param bool $success
     * @param null $error
     * @param null $data
     */
    private function response ($success = true, $error = null, $data = null)
    {
        global $APPLICATION;
        $result = [
            "success" => $success,
            "data" => $data,
            "error" => $error
        ];
        $APPLICATION->RestartBuffer();
        header('Content-Type: application/json; charset=utf-8');
        die(json_encode($result));
    }

    /**
     * Получение основной информации
     */
    public function getSettingsList ()
    {
        $arResult = [
            "logo_file" => '',
            "logo_text" => '',
            "phone" => '',
            "personal_data_policy_text" => '',
        ];
        $rsElement = CIBlockElement::GetList([], [
            'IBLOCK_ID' => self::IBLOCK_SETTINGS_ID,
            'CODE' => 'osnovnaya-informatsiya'
        ], false, false, [
            "ID",
            "PROPERTY_LOGO_FILE",
            "PROPERTY_LOGO_TEXT",
            "PROPERTY_PHONE",
            "PROPERTY_PERSONAL_DATA_POLICY"
        ]);
        if ($arElement = $rsElement->Fetch()) {
            $arResult['logo_file'] = CFile::GetPath($arElement['PROPERTY_LOGO_FILE_VALUE']);
            $arResult['logo_text'] = $arElement['PROPERTY_LOGO_TEXT_VALUE'];
            $arResult['phone'] = $arElement['PROPERTY_PHONE_VALUE'];
            $arResult['personal_data_policy_text'] = $arElement['PROPERTY_PERSONAL_DATA_POLICY_VALUE']['TEXT'];
        }
        $this->successAnswer($arResult);
    }

    /**
     * Список баннеров
     */
    public function getBannerList ()
    {
        $arResult = [];
        $rsElement = CIBlockElement::GetList(['SORT' => "ASC"], [
            "IBLOCK_ID" => self::IBLOCK_BANNER_ID,
            "ACTIVE" => "Y",
            "GLOBAL_ACTIVE" => "Y"
        ], false, false, [
            "ID",
            "PREVIEW_TEXT",
            "PROPERTY_NAME",
            "PROPERTY_POST",
            "PROPERTY_PICTURE",
            "PROPERTY_PICTURE_680",
            "PROPERTY_PICTURE_1024",
        ]);
        $i = 0;
        while ($arElement = $rsElement->Fetch()) {
            $i++;
            $location_style = $i % 2 == 0 ? ' rigth_dir' : ' left_dir';
            $arResult[] = [
                "title" => $arElement['PREVIEW_TEXT'],
                "pic_larg" => CFile::GetPath($arElement['PROPERTY_PICTURE_VALUE']),
                "pic_mid" => CFile::GetPath($arElement['PROPERTY_PICTURE_1024_VALUE']),
                "pic_small" => CFile::GetPath($arElement['PROPERTY_PICTURE_680_VALUE']),
                "location_style" => $location_style,
                "name" => $arElement['PROPERTY_NAME_VALUE'],
                "job" => $arElement['PROPERTY_POST_VALUE'],
                "id" => $arElement['ID']
            ];
        }
        $this->successAnswer($arResult);
    }

    /**
     * Список вакансий
     */
    public function getVacancyList ()
    {
        $arResult = [];
        $rsElement = CIBlockElement::GetList(['SORT' => "ASC"], [
            "IBLOCK_ID" => self::IBLOCK_VACANCY_ID,
            "ACTIVE" => "Y",
            "GLOBAL_ACTIVE" => "Y"
        ], false, false, [
            "ID",
            "NAME",
            "PREVIEW_PICTURE",
            "PREVIEW_TEXT",
        ]);
        while ($arElement = $rsElement->Fetch()) {
            $arResult[] = [
                "jobTitle" => $arElement['NAME'],
                "src" => CFile::GetPath($arElement['PREVIEW_PICTURE']),
                "desc" => $arElement['PREVIEW_TEXT'],
                "id" => $arElement['ID']
            ];
        }
        $this->successAnswer($arResult);
    }

    /**
     * Добавлении заявки на вакансию
     */
    public function postVacancyAdd ()
    {
        $vacancy_id = intval($_POST['vacancy_id']);
        $fio = htmlspecialchars(strip_tags($_POST['fio']));
        $birthday = htmlspecialchars(strip_tags($_POST['birthday']));
        $gender = htmlspecialchars(strip_tags($_POST['gender']));
        $phone = htmlspecialchars(strip_tags($_POST['phone']));
        $email = htmlspecialchars(strip_tags($_POST['email']));
        $resume_text = htmlspecialchars(strip_tags($_POST['resume_text']));
        $resume_file = CFile::SaveFile(array_merge($_FILES['resume_file'], ['MODULE_ID' => "iblock"]), "iblock");

        $arFields = [
            "ACTIVE" => "Y",
            "NAME" => "Заявка от " . date('d.m.Y H:i:s'),
            "IBLOCK_ID" => self::IBLOCK_VACANCY_REQUEST_ID,
            "PROPERTY_VALUES" => [
                "VACANCY" => $vacancy_id,
                "FIO" => $fio,
                "BIRTHDAY" => $birthday,
                "GENDER" => $gender,
                "PHONE" => $phone,
                "EMAIL" => $email,
                "RESUME" => $resume_text,
                "RESUME_FILE" => $resume_file,
            ]
        ];

        $obElement = new \CIBlockElement();
        $id = $obElement->Add($arFields);
        if ($id > 0) {
            $this->successAnswer(['id' => $id]);
        } else {
            \CFile::Delete($resume_file);
            $this->errorAnswer("Ошибка при добавлении заявки: " . $obElement->LAST_ERROR);
        }
    }

    /**
     * Список геолокаций
     */
    public function getLocationList ()
    {
        $arResult = [];
        $rsElement = CIBlockElement::GetList(['SORT' => "ASC"], [
            "IBLOCK_ID" => self::IBLOCK_LOCATION_ID,
            "ACTIVE" => "Y",
            "GLOBAL_ACTIVE" => "Y"
        ], false, false, [
            "ID",
            "NAME",
            "PROPERTY_GEO",
            "PROPERTY_TYPE",
        ]);
        while ($arElement = $rsElement->Fetch()) {
            $geo = explode(",", $arElement['PROPERTY_GEO_VALUE']);
            $fiz = $arElement['PROPERTY_GEO_VALUE_ID'] == 49;
            $arResult[] = [
                "pointName" => $arElement['NAME'],
                "lat" => $geo[0],
                "lng" => $geo[1],
                "yur" => !$fiz,
                "fiz" => $fiz,
                "id" => $arElement['ID'],
            ];
        }
        $this->successAnswer($arResult);
    }
}