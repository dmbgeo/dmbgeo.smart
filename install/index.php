<?

use \Bitrix\Main\Application;
use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
class dmbgeo_SMART extends CModule
{
    public $MODULE_ID = 'dmbgeo.smart';
    public $COMPANY_ID = 'dmbgeo';
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;

    public function dmbgeo_smart()
    {
        $arModuleVersion = array();
        include __DIR__ . "/version.php";
        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        $this->MODULE_NAME = Loc::getMessage("DMBGEO_SMART_MODULE_NAME");
        $this->MODULE_DESCRIPTION = Loc::getMessage("DMBGEO_SMART_MODULE_DESC");
        $this->PARTNER_NAME = getMessage("DMBGEO_SMART_PARTNER_NAME");
        $this->PARTNER_URI = getMessage("DMBGEO_SMART_PARTNER_URI");
    }


    public function isVersionD7()
    {
        return CheckVersion(\Bitrix\Main\ModuleManager::getVersion('main'), '14.00.00');
    }

    public function GetPath($notDocumentRoot = false)
    {
        if ($notDocumentRoot) {
            return str_ireplace(Application::getDocumentRoot(), '', dirname(__DIR__));
        } else {
            return dirname(__DIR__);
        }
    }

    public function InstallFiles($arParams = array())
    {
        $path = $this->GetPath() . "/install/components/dmbgeo/";

        if (\Bitrix\Main\IO\Directory::isDirectoryExists($path)) {
            CopyDirFiles($path, $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/dmbgeo/", true, true);
        }

        $path = $this->GetPath() . "/install/tools/dmbgeo";

        if (\Bitrix\Main\IO\Directory::isDirectoryExists($path)) {
            CopyDirFiles($path, $_SERVER["DOCUMENT_ROOT"] . "/bitrix/tools/dmbgeo/", true, true);
        }

        if (\Bitrix\Main\IO\Directory::isDirectoryExists($path = $this->GetPath() . '/admin')) {
            CopyDirFiles($this->GetPath() . "/install/admin/", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin");
            if ($dir = opendir($path)) {
                while (false !== $item = readdir($dir)) {
                    if (in_array($item, $this->exclusionAdminFiles)) {
                        continue;
                    }

                    file_put_contents(
                        $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin/' . $item,
                        '<' . '? require($_SERVER["DOCUMENT_ROOT"]."' . $this->GetPath(true) . '/admin/' . $item . '");?' . '>'
                    );
                }
                closedir($dir);
            }
        }

        if (\Bitrix\Main\IO\Directory::isDirectoryExists($path = $this->GetPath() . '/install/files')) {
            $this->copyArbitraryFiles();
        }

        return true;
    }

    public function UnInstallFiles()
    {


        $deleteComponentDirectires=Array();
        $modulComponetnDirectory = new \Bitrix\Main\IO\Directory($this->GetPath() . "/install/components/". $this->COMPANY_ID . "/");
        if($modulComponetnDirectory->isExists() && $modulComponetnDirectory->isDirectory()){
            foreach ($modulComponetnDirectory->getChildren() as $directory) {
                if($directory->isExists() && $directory->isDirectory()){
                    $deleteComponentDirectires[]=$directory->getName();
                }
            }
        }
        var_dump($deleteComponentDirectires);
        $componetnDirectory = new \Bitrix\Main\IO\Directory($_SERVER["DOCUMENT_ROOT"] . '/bitrix/components/' . $this->COMPANY_ID . "/");
        if($componetnDirectory->isExists() && $componetnDirectory->isDirectory()){
            foreach ($componetnDirectory->getChildren() as $directory) {
                if($directory->isExists() && $directory->isDirectory()){
                    if(in_array($directory->getName(),$deleteComponentDirectires)){
                        $directory->delete();
                    }
                }
            }
        }
       

        $deleteToolsFiles=Array();
        $modulToolsDirectory = new \Bitrix\Main\IO\Directory($this->GetPath() . "/install/tools/". $this->COMPANY_ID . "/");
        if($modulToolsDirectory->isExists() && $modulToolsDirectory->isDirectory()){
            foreach ($modulToolsDirectory->getChildren() as $directory) {
                if($directory->isExists()){
                    $deleteToolsFiles[]=$directory->getName();
                }
            }
        }

        $toolsDirectory = new \Bitrix\Main\IO\Directory($_SERVER["DOCUMENT_ROOT"] . '/bitrix/tools/' . $this->COMPANY_ID . "/");
        if($toolsDirectory->isExists() && $toolsDirectory->isDirectory()){
            foreach ($toolsDirectory->getChildren() as $directory) {
                if($directory->isExists()){
                    if(in_array($directory->getName(),$deleteToolsFiles)){
                        $directory->delete();
                    }
                }
            }
        }


        $deleteAdminFiles=Array();
        $modulAdminDirectory = new \Bitrix\Main\IO\Directory($this->GetPath() . "/install/admin/");
        if($modulAdminDirectory->isExists() && $modulAdminDirectory->isDirectory()){
            foreach ($modulAdminDirectory->getChildren() as $directory) {
                if($directory->isExists()){
                    $deleteAdminFiles[]=$directory->getName();
                }
            }
        }

        $adminDirectory = new \Bitrix\Main\IO\Directory($_SERVER["DOCUMENT_ROOT"] . '/bitrix/admin/');
        if($adminDirectory->isExists() && $adminDirectory->isDirectory()){
            foreach ($adminDirectory->getChildren() as $directory) {
                if($directory->isExists()){
                    if(in_array($directory->getName(),$deleteAdminFiles)){
                        $directory->delete();
                    }
                }
            }
        }
        
        if (\Bitrix\Main\IO\Directory::isDirectoryExists($this->GetPath() . '/install/files')) {
            $this->deleteArbitraryFiles();
        }

        return true;
    }

    public function copyArbitraryFiles()
    {
        $rootPath = $_SERVER["DOCUMENT_ROOT"];
        $localPath = $this->GetPath() . '/install/files';

        $dirIterator = new RecursiveDirectoryIterator($localPath, RecursiveDirectoryIterator::SKIP_DOTS);
        $iterator = new RecursiveIteratorIterator($dirIterator, RecursiveIteratorIterator::SELF_FIRST);

        foreach ($iterator as $object) {
            $destPath = $rootPath . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
            ($object->isDir()) ? mkdir($destPath) : copy($object, $destPath);
        }
    }

    public function deleteArbitraryFiles()
    {
        $rootPath = $_SERVER["DOCUMENT_ROOT"];
        $localPath = $this->GetPath() . '/install/files';

        $dirIterator = new RecursiveDirectoryIterator($localPath, RecursiveDirectoryIterator::SKIP_DOTS);
        $iterator = new RecursiveIteratorIterator($dirIterator, RecursiveIteratorIterator::SELF_FIRST);

        foreach ($iterator as $object) {
            if (!$object->isDir()) {
                $file = str_replace($localPath, $rootPath, $object->getPathName());
                \Bitrix\Main\IO\File::deleteFile($file);
            }
        }
    }

    public function DoInstall()
    {

        global $APPLICATION;
        if ($this->isVersionD7()) {
            \Bitrix\Main\ModuleManager::registerModule($this->MODULE_ID);
            $this->InstallFiles();
        } else {
            $APPLICATION->ThrowException(Loc::getMessage("DMBGEO_SMART_INSTALL_ERROR_VERSION"));
        }

        $APPLICATION->IncludeAdminFile(Loc::getMessage("DMBGEO_SMART_INSTALL"), $this->GetPath() . "/install/step.php");
    }

    public function DoUninstall()
    {

        global $APPLICATION;

        $context = Application::getInstance()->getContext();
        $request = $context->getRequest();
        $this->UnInstallFiles();
        \Bitrix\Main\ModuleManager::unRegisterModule($this->MODULE_ID);

        $APPLICATION->IncludeAdminFile(Loc::getMessage("DMBGEO_SMART_UNINSTALL"), $this->GetPath() . "/install/unstep.php");
    }
}
