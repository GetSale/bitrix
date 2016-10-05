<?
IncludeModuleLangFile(__FILE__);

Class getsale_getsale extends CModule
{
    const MODULE_ID = 'getsale.getsale';
    var $MODULE_ID = 'getsale.getsale';
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $MODULE_CSS;
    var $strError = '';

    function __construct()
    {
        $arModuleVersion = array();
        include(dirname(__FILE__) . "/version.php");
        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        $this->MODULE_NAME = GetMessage("getsale.getsale_MODULE_NAME");
        $this->MODULE_DESCRIPTION = GetMessage("getsale.getsale_MODULE_DESC");

        $this->PARTNER_NAME = GetMessage("getsale.getsale_PARTNER_NAME");
        $this->PARTNER_URI = GetMessage("getsale.getsale_PARTNER_URI");
    }

    function InstallEvents()
    {
        return true;
    }

    function UnInstallEvents()
    {
        return true;
    }

    public function DoInstall()
    {
        RegisterModule($this->MODULE_ID);
        RegisterModuleDependences("main", "OnPageStart", $this->MODULE_ID, "CGetsaleGetsale", "ini");
        RegisterModuleDependences("sale", "OnBasketOrder", $this->MODULE_ID, "CGetsaleGetsale", "order", "100");
        RegisterModuleDependences("main", "OnAfterUserRegister", $this->MODULE_ID, "CGetsaleGetsale", "OnAfterUserRegisterHandler", "100");
        $this->InstallFiles();
        $this->InstallDB();
    }

    public function DoUninstall()
    {
        UnRegisterModuleDependences('main', 'OnPageStart', self::MODULE_ID, 'CGetsaleGetsale', 'ini');
        UnRegisterModuleDependences("sale", "OnBasketOrder", $this->MODULE_ID, "CGetsaleGetsale", "order");
        UnRegisterModuleDependences("main", "OnAfterUserRegister", $this->MODULE_ID, "CGetsaleGetsale", "OnAfterUserRegisterHandler");
        COption::RemoveOption(self::MODULE_ID, "getsale_id");
        COption::RemoveOption(self::MODULE_ID, "getsale_mail");
        COption::RemoveOption(self::MODULE_ID, "getsale_key");
        COption::RemoveOption(self::MODULE_ID, "getsale_code");

        $this->UnInstallFiles();
        UnRegisterModule($this->MODULE_ID);
    }

    function InstallFiles()
    {
        if (is_dir($p = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . self::MODULE_ID . '/install/images')) {
            if ($dir = opendir($p)) {
                while (false !== $item = readdir($dir)) {
                    if ($item == '..' || $item == '.')
                        continue;
                    CopyDirFiles($p . '/' . $item, $_SERVER['DOCUMENT_ROOT'] . '/bitrix/images/' . self::MODULE_ID . '/' . $item, $ReWrite = True, $Recursive = True);
                }
                closedir($dir);
            }
        }
        return true;
    }
}

?>

