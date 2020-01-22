<?php
namespace framework\base;

class Table{
    private $_rows;
    private $_columns;
    private $_contents;
    private $_style;
    private $_rawdata;
    private $_replace;

    function __construct($rawdata = array(), $layout = array(), $style = ''){
        $this->_rawdata = $rawdata;
        $this->_rows = null;
        if(isset($layout['Rows']))
            $this->_rows     = $layout['Rows'];

        $this->_columns = null;
        if(isset($layout['Columns']))
            $this->_columns  = $layout['Columns'];

        $this->_contents = null;
        if(isset($layout['Contents']))
            $this->_contents = $layout['Contents'];

        $this->_style   = $style;
        $this->_replace = array();
    }

    public function getIndependentItems($indexID){
        $result = array();
        if(is_string($indexID)){
            foreach($this->_rawdata as $drows){
                $checkdata = false;
                $rowdata = array();
                $rowdata[$indexID] = $drows[$indexID];
                foreach($result as $row){
                    if($this->inArray($row, $rowdata)){
                        $checkdata = true;
                        break;
                    }
                }

                if($checkdata == false)
                    array_push($result, $rowdata);
            }
        }else if(is_array($indexID)){
            foreach($this->_rawdata as $drows){
                $checkdata = false;
                $rowdata = array();
                foreach($indexID as $items)
                    $rowdata[$items] = $drows[$items];

                foreach($result as $row){
                    if($this->inArray($row, $rowdata)){
                        $checkdata = true;
                        break;
                    }
                }

                if($checkdata == false)
                    array_push($result, $rowdata);
            }
        }

        return $result;
    }

    public function sortMultiArray(&$data, $method = "ASC"){
        if(!isset($data) || count($data) == 0) return;
        $arrayKey = array_keys($data[array_key_first($data)]);
        if(strtoupper($method) == "ASC"){
            for($keyIdx = count($arrayKey) - 1; $keyIdx >= 0; $keyIdx--){
                for($srcIdx = 0; $srcIdx < count($data); $srcIdx++){
                    for($schIdx = $srcIdx + 1; $schIdx < count($data); $schIdx++){
                        $colname = $arrayKey[$keyIdx];
                        $temp = array();
                        if(is_numeric($data[$srcIdx][$colname]) && is_numeric($data[$schIdx][$colname])){
                            if($data[$srcIdx][$colname] > $data[$schIdx][$colname]){
                                $temp = $data[$srcIdx];
                                $data[$srcIdx] = $data[$schIdx];
                                $data[$schIdx] = $temp;
                            }else if($data[$srcIdx][$colname] == $data[$schIdx][$colname]){
                                for($colIndex = $keyIdx + 1; $colIndex < count($arrayKey); $colIndex++){
                                    $colname = $arrayKey[$colIndex];
                                    if($data[$srcIdx][$colname] > $data[$schIdx][$colname]){
                                        $temp = $data[$srcIdx];
                                        $data[$srcIdx] = $data[$schIdx];
                                        $data[$schIdx] = $temp;
                                    }
                                }
                            }
                        }else if(is_string($data[$srcIdx][$colname]) || is_string($data[$schIdx][$colname])){
                            $srcText = $data[$srcIdx][$colname];
                            $schText = $data[$schIdx][$colname];
                            if($srcText != $schText){
                                for($txtIdx = 0; $txtIdx < min(strlen($data[$srcIdx][$colname]), strlen($data[$schIdx][$colname])); $txtIdx++){
                                    if(ord($srcText[$txtIdx]) > ord($schText[$txtIdx])){
                                        $temp = $data[$srcIdx];
                                        $data[$srcIdx] = $data[$schIdx];
                                        $data[$schIdx] = $temp;
                                        break;
                                    }else if(ord($srcText[$txtIdx]) < ord($schText[$txtIdx])){
                                        break;
                                    }
                                }
                            }else{
                                for($colIndex = $keyIdx + 1; $colIndex < count($arrayKey); $colIndex++){
                                    $colname = $arrayKey[$colIndex];
                                    for($txtIdx = 0; $txtIdx < min(strlen($data[$srcIdx][$colname]), strlen($data[$schIdx][$colname])); $txtIdx++){
                                        if(ord($srcText[$txtUdx]) > ord($schText[$txtIdx])){
                                            $temp = $data[$srcIdx];
                                            $data[$srcIdx] = $data[$schIdx];
                                            $data[$schIdx] = $temp;
                                            break;
                                        }else if(ord($srcText[$txtIdx]) < ord($schText[$txtIdx])){
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }else if(strtoupper($method) == "DESC"){
            for($keyIdx = count($arrayKey) - 1; $keyIdx >= 0; $keyIdx--){
                for($srcIdx = 0; $srcIdx < count($data); $srcIdx++){
                    for($schIdx = $srcIdx + 1; $schIdx < count($data); $schIdx++){
                        $colname = $arrayKey[$keyIdx];
                        $temp = array();
                        if(is_numeric($data[$srcIdx][$colname]) && is_numeric($data[$schIdx][$colname])){
                            if($data[$srcIdx][$colname] < $data[$schIdx][$colname]){
                                $temp = $data[$srcIdx];
                                $data[$srcIdx] = $data[$schIdx];
                                $data[$schIdx] = $temp;
                            }else if($data[$srcIdx][$colname] == $data[$schIdx][$colname]){
                                for($colIndex = $keyIdx + 1; $colIndex < count($arrayKey); $colIndex++){
                                    $colname = $arrayKey[$colIndex];
                                    if($data[$srcIdx][$colname] < $data[$schIdx][$colname]){
                                        $temp = $data[$srcIdx];
                                        $data[$srcIdx] = $data[$schIdx];
                                        $data[$schIdx] = $temp;
                                    }
                                }
                            }
                        }else if(is_string($data[$srcIdx][$colname]) || is_string($data[$schIdx][$colname])){
                            $srcText = $data[$srcIdx][$colname];
                            $schText = $data[$schIdx][$colname];
                            if($srcText != $schText){
                                for($txtIdx = 0; $txtIdx < min(strlen($data[$srcIdx][$colname]), strlen($data[$schIdx][$colname])); $txtIdx++){
                                    if(ord($srcText[$txtIdx]) < ord($schText[$txtIdx])){
                                        $temp = $data[$srcIdx];
                                        $data[$srcIdx] = $data[$schIdx];
                                        $data[$schIdx] = $temp;
                                        break;
                                    }else if(ord($srcText[$txtIdx]) > ord($schText[$txtIdx])){
                                        break;
                                    }
                                }
                            }else{
                                for($colIndex = $keyIdx + 1; $colIndex < count($arrayKey); $colIndex++){
                                    $colname = $arrayKey[$colIndex];
                                    for($txtIdx = 0; $txtIdx < min(strlen($data[$srcIdx][$colname]), strlen($data[$schIdx][$colname])); $txtIdx++){
                                        if(ord($srcText[$txtUdx]) > ord($schText[$txtIdx])){
                                            $temp = $data[$srcIdx];
                                            $data[$srcIdx] = $data[$schIdx];
                                            $data[$schIdx] = $temp;
                                            break;
                                        }else if(ord($srcText[$txtIdx]) < ord($schText[$txtIdx])){
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    // Check if the subarray in the parent array exists
    private function inArray($parent = array(), $child){
        $result = false;
        if(is_array($child)){
            foreach($child as $child_items){
                $result = false;
                foreach($parent as $parent_items){
                    if($parent_items == $child_items){
                        $result = true;
                        break;
                    }
                }

                if($result === false)
                    break;
            }
        }else if(is_string($child)){
            foreach($parent as $parent_items){
                if($parent_items == $child){
                    $result = true;
                    break;
                }
            }
        }
        return $result;
    }

    // Get layout rows(String or Array)
    public function getRows(){
        return $this->_rows;
    }

    // Set layout rows(String or Array)
    public function setRows($value){
        if(is_string($value))
            $this->_rows = array($value);
        else if(is_array($value))
            $this->_rows = $value;
    }

    // Get layout columns(String or Array)
    public function getColumns(){
        return $this->_columns;
    }

    // Set layout columns(String or Array)
    public function setColumns($value){
        if(is_string($value))
            $this->_columns = array($value);
        else if(is_array($value))
            $this->_columns = $value;
    }

    // Get layout content(String)
    public function getContents(){
        return $this->_contents;
    }

    // Set layout content(String)
    public function setContents($value){
        if(is_string($value))
            $this->_contents = $value;
    }

    // Get table theme style(CSS)
    public function getStyle(){
        return $this->style;
    }

    // Set table theme style(CSS)
    public function setStyle($value){
        if(is_string($value))
            $this->_style = $value;
    }

    // Get replace text array
    public function getReplace(){
        return $this->_replace;
    }

    // Set replace text array
    public function setReplace($value = array()){
        $this->_replace = $value;
    }

    // Create pivot table
    public function createPivotTable(){
        try{
            if(count($this->_rawdata) == 0) return;

            // Get index entries
            $data_rows    = $this->getIndependentItems($this->_rows);
            $data_columns = $this->getIndependentItems($this->_columns);

            $this->sortMultiArray($data_rows, "ASC");
            $this->sortMultiArray($data_columns, "ASC");

            // Rows layout
            $rows = array();
            foreach($data_rows as $drows){
                $rowname = "";
                foreach($drows as $drIdx => $drItems){
                    $rowname .= ($drIdx == array_key_first($drows))?$drItems:"|".$drItems;
                    if(!isset($rows[$rowname][$drIdx]))
                        $rows[$rowname][$drIdx] = 0;
                    $rows[$rowname][$drIdx] += 1;
                }
            }

            // Columns layout
            $columns = array();
            foreach($data_columns as $dcols){
                $colname = "";
                foreach($dcols as $dcIdx => $dcItems){
                    $colname .= ($dcIdx == array_key_first($dcols))?$dcItems:"|".$dcItems;
                    if(!isset($columns[$dcIdx][$colname]))
                        $columns[$dcIdx][$colname] = 0;
                    $columns[$dcIdx][$colname] += 1;
                }
            }

            // Fill data to content layout
            $content = array();
            foreach($data_rows as $drIndex => $drows){
                $rowname = implode("|", $drows);
                $content[$rowname] = array();
                foreach($data_columns as $dcIndex => $dcols){
                    $colname = implode("|", $dcols);
                    $content[$rowname][$colname] = "";
                    foreach($this->_rawdata as $index => $value){
                        if($this->inArray($value, $drows) && $this->inArray($value, $dcols))
                            $content[$rowname][$colname] = $value[$this->_contents];
                    }
                }
            }

            // Draw pivot table
            $rowspan = is_array($this->_rows)?count($this->_rows):1;
            $colspan = is_array($this->_columns)?count($this->_columns):1;

            echo "<table class='".$this->_style."'>";
            echo "  <thead>";
            foreach($columns as $columnIdx => $columnItems){
                echo "<tr id='$columnIdx'>";
                if($colspan > 0){
                    for($index = 0; $index < $rowspan; $index++)
                        echo "<th class='fixedField'></th>";
                }
                foreach($columnItems as $fieldIdx => $fieldItem){
                    $value = explode("|", $fieldIdx);
                    echo "<th id='$fieldIdx' colspan='$fieldItem'>".$value[count($value) - 1]."</th>";
                }
                echo "</tr>";
            }
            echo "  </thead>";
            echo "  <tbody>";

            $newRow = true;
            foreach($rows as $rowIdx => $rowItems){
                $value = explode("|", $rowIdx);
                if($newRow == true){
                    echo "<tr>";
                    $newRow = false;
                }
                
                echo "<th id='$rowIdx' rowspan='".$rowItems[array_key_first($rowItems)]."'>".$value[count($value) - 1]."</th>";
                
                if(array_key_first($rowItems) == $this->_rows[count($this->_rows) - 1]){
                    foreach($content[$rowIdx] as $fieldIdx => $fieldItem){
                        echo "<td id='$fieldIdx'>";

                        foreach($this->_replace as $replaceIdx => $replaceItem){
                            if($fieldItem == $replaceIdx)
                                $fieldItem = $replaceItem;
                        }

                        if(is_numeric($fieldItem))
                            echo round($fieldItem, 2);
                        else
                            echo $fieldItem;
                        
                        echo "</td>";
                    }
                    echo "</tr>";
                    $newRow = true;
                }
            }
            echo "  </tbody>";
            echo "<table>";
            
        }catch(Exception $e){

        }
    }
}
?>
