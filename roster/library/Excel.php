<?php
class Library_Excel{
    public function __construct(){
        require_once(SERVER_ROOT . 'app/roster/library/PHPExcel/IOFactory.php');
    }

    public function read($filename, $encode='utf-8'){
        $objReader = PHPExcel_IOFactory::createReader('Excel5');
        $objReader->setReadDataOnly(true);
        $objPHPExcel = $objReader->load($filename);
        $objWorksheet = $objPHPExcel->getActiveSheet();
        $highestRow = $objWorksheet->getHighestRow();
        $highestColumn = $objWorksheet->getHighestColumn();
        $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
        $excelData = array();
        for($row = 1; $row <= $highestRow; $row++)
            for($col = 0; $col < $highestColumnIndex; $col++)
                $excelData[$row][] = (string)$objWorksheet->getCellByColumnAndRow($col,$row)->getValue();
        return  $excelData;
    }
}
?>