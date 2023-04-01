<?

$arParams = ['PATCH'=>'bitrix/templates', 'SORT_TYPE'=>'DESC', 'MODE'=>($_GET['FILE_VISOR'] === 'Y'|| $_GET['FV'] === 'Y') ? 'PAGE' : 'DIRECTORY', 'STF'=>$_GET['STF']];

function generate_table($fileName, $dataEdit, $searchText){
    $searchFlag = false;
    global $USER;

    if(!empty($searchText)){
        //prr(file_get_contents("$st_search"));
        $st_strpos = trim($searchText); //слово или фразу, которую нужно найти в файле
        $st_search = $fileName; //название файла, в котором нужно найти (если нужно, то еще пропишите путь к файлу)
        //echo "Результат поиска в файле $st_search: <br>";
        if (strpos(file_get_contents("$st_search"), "$st_strpos")){
            //echo "Есть такое слово"; 
            $searchFlag = true;
            $url = "/bitrix/admin/fileman_file_view.php?lang=ru&site=s2&path=".str_replace($_SERVER['DOCUMENT_ROOT'], '', $fileName);
            $fileOldCopy = $fileName.'.bkp';
            ?>
            <tr style="<?=$searchFlag ? 'background-color:#4ccb4c' : ''?>">
                <th><a href=<?=$url?> target="_blank" style="color:black; font-weight: bold;"><?=str_replace(dirname($_SERVER['DOCUMENT_ROOT']).'/', '', $fileName)?></a></th>
                <th><?if(file_exists($fileOldCopy) && $USER->isAdmin()){?><a style="color:black; font-weight: bold;" download href=<?=str_replace($_SERVER['DOCUMENT_ROOT'], '', $fileOldCopy)?> target="_blank">Скачать копию</a><?}?></th>
                <th><?=date ("F d Y H:i:s.", $dataEdit)?></th>
            </tr>
            <?
        } else {
            //echo "Нет такого слова";
        }
    }
    else{
        $url = "/bitrix/admin/fileman_file_view.php?lang=ru&site=s2&path=".str_replace($_SERVER['DOCUMENT_ROOT'], '', $fileName);
        $fileOldCopy = $fileName.'.bkp';
        ?>
        <tr style="<?=$searchFlag ? 'background-color:green' : ''?>">
            <th><a href=<?=$url?> target="_blank" style="color:black; font-weight: bold;"><?=str_replace(dirname($_SERVER['DOCUMENT_ROOT']).'/', '', $fileName)?></a></th>
            <th><?if(file_exists($fileOldCopy) && $USER->isAdmin()){?><a download href=<?=str_replace($_SERVER['DOCUMENT_ROOT'], '', $fileOldCopy)?> target="_blank" style="color:black; font-weight: bold;">Скачать копию</a><?}?></th>
            <th><?=date ("F d Y H:i:s.", $dataEdit)?></th>
        </tr>
        <?
    }
}



function getDirContents($dir, $arParams, &$results = array()) {
    switch($arParams['MODE']){
        case "PAGE":{
            $files = get_included_files();
            //prr($files);
            break;
        }
        case "DIRECTORY":{
            $files = scandir($dir);
            break;
        }
    }

    foreach ($files as $key => $value) {
        if($arParams['MODE']==='PAGE'){
            if(strpos($value, 'cache')!==false){
                continue;
            }
            $path = $value;
        }
        else{
            $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
        }
        
        if (!is_dir($path)) {
            $results[$path] = filemtime($path);
            arsort($results);
        } else if ($value != "." && $value != "..") {
            getDirContents($path, $arParams, $results);
            $results[] = $path;
        }
    }
    return $results;
}

?>
<?global $USER;?>
<?if($arParams['MODE'] === 'PAGE'){?>
<table id="FILE_VISOR" style="width:100%!important;">
  <colgroup>
    <col span="3" style="background:Khaki;"><!-- С помощью этой конструкции задаем цвет фона для первых двух столбцов таблицы-->
    <col style="background-color:LightCyan"><!-- Задаем цвет фона для следующего (одного) столбца таблицы-->
  </colgroup>
  <tr>
    <th>Файл</th>
    <th>Бэкап</th>
    <th>Изменен</th>
  </tr>
  <?
    $arFilesAfterSort = getDirContents($_SERVER['DOCUMENT_ROOT']."/".$arParams['PATCH'],$arParams);
    foreach($arFilesAfterSort as $key=>$file){
        generate_table($key, $file, $arParams['STF']);
    }
  ?>
</table>

<?}?>
