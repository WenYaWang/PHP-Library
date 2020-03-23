<?php 

namespace framework\base;

class DataReader{
    private $_rawdata;
    
    public function __construct($rawdata){
        $this->_rawdata = $rawdata;
    }

    public function __get($name){
        switch($name){
            case "Length":
                return count($this->_rawdata);
                break;
        }
    }

    public function First($Index = 1){
        $rawdata = array();
        for($idx = 0; $idx < $Index; $idx++)
            array_push($rawdata, $this->_rawdata[$idx]);
        $dataReader = new DataReader($rawdata);
        return $dataReader;
    }

    public function Last($Index = 1){
        $rawdata = array();
        for($idx = count($this->_rawdata) - $Index; $idx <= count($this->_rawdata) - 1; $idx++)
            array_push($rawdata, $this->_rawdata[$idx]);
        $dataReader = new DataReader($rawdata);
        return $dataReader;
    }

    public function Range($beginIdx, $endIdx){
        $rawdata = array();
        for($index = $beginIdx - 1; $index <= $endIdx - 1; $index++)
            array_push($rawdata, $this->_rawdata[$index]);
        $dataReader = new DataReader($rawdata);
        return $dataReader;
    }

    public function LRange($beginIdx, $endIdx){
        $rawdata = array();
        for($index = count($this->_rawdata) - $endIdx; $index <= count($this->_rawdata) - $beginIdx; $index++){
            array_push($rawdata, $this->_rawdata[$index]);
        }
        $dataReader = new DataReader($rawdata);
        return $dataReader;
    }

    public function Row($Keyname = null){
        if($Keyname == null)
            $Keyname = array_key_first($this->_rawdata);
        $rawdata = $this->_rawdata[$Keyname];
        $dataRows = new DateRows($rawdata);
        return $dataRows;
    }

    public function Column($Keyname = null){
        if($Keyname == null)
            $Keyname = array_key_first($this->Row());
        $rawdata = array();
        foreach($this->_rawdata as $key => $rows)
            array_push($rawdata, $rows[$Keyname]);
        $dataColumns = new DataColumns($rawdata);
        return $dataColumns;
    }

    public function toValue($columnsName = null, $rowsIdx = 1){
        if(count($this->_rawdata) == 1){
            return (float)$this->_rawdata[$rowsIdx - 1];
        }else if(count($this->_rawdata) == 2){
            $colName = ($columnsName == null)?array_key_first($this->_rawdata[0]):$columnsName;
            return (float)$this->_rawdata[$rowsIdx - 1][$columnsName];
        }
    }

    public function toString($columnsName = null, $rowsIdx = 1){
        if(count($this->_rawdata) == 1){
            return (string)$this->_rawdata[$rowsIdx - 1];
        }else if(count($this->_rawdata) == 2){
            $colName = ($columnsName == null)?array_key_first($this->_rawdata[0]):$columnsName;
            return (string)$this->_rawdata[$rowsIdx - 1][$columnsName];
        }
    }

    public function toArray(){
        return $this->_rawdata;
    }

    public function AlgorithmOperation($formula){
        $stack = array();
        $value = "";
        for($txtIdx = strlen($formula) - 1; $txtIdx >= 0; $txtIdx--){
            if($formula[$txtIdx] != "("){
                switch($formula[$txtIdx]){
                    case "+":
                    case "-":
                    case "*":
                    case "/":
                    case ")":
                    case ",":
                        if($value != "")
                            array_unshift($stack, trim($value));
                        array_unshift($stack, trim($formula[$txtIdx]));
                        $value = "";
                        break;
                    default:
                        $value = $formula[$txtIdx].$value;
                        break;
                }
            }else{
                $func_name = "";
                $func_param = array();
                if($value != ""){
                    array_unshift($stack, $value);
                    $value = "";
                }

                // Get function name
                while($txtIdx - 1 >= 0 &&
                        $formula[$txtIdx - 1] != "+" && 
                        $formula[$txtIdx - 1] != "-" && 
                        $formula[$txtIdx - 1] != "*" &&
                        $formula[$txtIdx - 1] != "/" &&
                        $formula[$txtIdx - 1] != ")" &&
                        $formula[$txtIdx - 1] != "(" && 
                        $formula[$txtIdx - 1] != ","){
                    $func_name = $formula[$txtIdx].$func_name;
                    $txtIdx--;
                }
                $func_name = $formula[$txtIdx].$func_name;
                $func_name = str_replace("(", "", $func_name);
                $func_name = trim($func_name);

                // Get function parameter
                $operator = "";
                while(count($stack) > 0){   
                    if($stack[0] == "," || $stack[0] == ")"){
                        array_push($func_param, $operator);
                        $operator = "";
                        if($stack[0] == ")"){
                            break;
                        }
                    }else{
                        switch(gettype($stack[0])){
                            case "object":
                            case "array":
                                $operator = $stack[0];
                                break;
                            default:
                                $operator .= $stack[0];
                                break;
                        }
                    }
                    array_shift($stack);
                }               

                // Execute function
                switch(strtoupper($func_name)){
                    case "":
                        $stack[0] = self::Calculation($func_param[0]);
                        break;
                    case "FIRST":                   // Selection data collection
                        switch(count($func_param)){
                            case 0:
                                $stack[0] = $this->First();
                                break;
                            case 1:
                                $stack[0] = $this->First((int)$func_param[0]);
                                break;
                            case 2:
                                $stack[0] = $this->First((int)$func_param[1])->Column((string)$func_param[0]);
                                break;
                        }
                        break;
                    case "LAST":                    // Selection data collection
                        switch(count($func_param)){
                            case 0:
                                $stack[0] = $this->Last();
                                break;
                            case 1:
                                $stack[0] = $this->Last((int)$func_param[0]);
                                break;
                            case 2: 
                                $stack[0] = $this->Last((int)$func_param[1])->Column((string)$func_param[0]);
                                break;
                        }
                        break;
                    case "COLUMN":                  // Selection data collection
                        switch(count($func_param)){
                            case 0:
                                $stack[0] = $this->Column();
                                break;
                            case 1:
                                $stack[0] = $this->Column((string)$func_param[0]);
                                break;
                        }
                        break;
                    case "ROW":                     // Selection data collection
                        switch(count($func_param)){
                            case 0:
                                $stack[0] = $this->Row();
                                break;
                            case 1:
                                $stack[0] = $thos->Row((int)$func_param[0]);
                                break;
                        }
                        break;
                    case "RANGE":                   // Selection data collection
                        switch(count($func_param)){
                            case 2:
                                $stack[0] = $this->Range((int)$func_param[0], (int)$func_param[1]);
                                break;
                            case 3:
                                $stack[0] = $this->Range((int)$func_param[1], (int)$func_param[2])->Column((string)$func_param[0]);
                                break;
                        }
                        break;
                    case "SUM":
                        switch(count($func_param)){
                            case 0:
                                $stack[0] = null;
                                break;
                            case 1:
                                switch(gettype($func_param[0])){
                                    case "object":
                                        $className = get_class($func_param[0]);
                                        if(substr($className, strrpos($className, "\\") + 1) == "DataColumns")
                                            $stack[0] = $func_param[0]->Sum();
                                        break;
                                    case "array":
                                        $objCol = new DataColumns($func_param[0]);
                                        $stack[0] = $objCol->Sum();
                                        break;
                                }
                                break;
                        }
                        break;
                    case "AVG":
                        switch(count($func_param)){
                            case 0:
                                $stack[0] = null;
                                break;
                            case 1:
                                switch(gettype($func_param[0])){
                                    case "object":
                                        $className = get_class($func_param[0]);
                                        if(substr($className, strrpos($className, "\\") + 1) == "DataColumns")
                                            $stack[0] = $func_param[0]->Avg();
                                        break;
                                    case "array":
                                        $objCol = new DataColumns($func_param[0]);
                                        $stack[0] = $objCol->Avg();
                                        break;
                                }
                                break;
                        }
                        break;
                    case "MIN":
                        switch(count($func_param)){
                            case 0:
                                $stack[0] = null;
                                break;
                            case 1:
                                switch(gettype($func_param[0])){
                                    case "object":
                                        $className = get_class($func_param[0]);
                                        if(substr($className, strrpos($className, "\\") + 1) == "DataColumns")
                                            $stack[0] = $func_param[0]->Min();
                                        break;
                                    case "array":
                                        $objCol = new DataColumns($func_param[0]);
                                        $stack[0] = $objCol->Min();
                                        break;
                                }
                        }
                        break;
                    case "MAX":
                        switch(count($func_param)){
                            case 0:
                                $stack[0] = null;
                                break;
                            case 1:
                                switch(gettype($func_param[0])){
                                    case "object":
                                        $className = get_class($func_param[0]);
                                        if(substr($className, strrpos($className, "\\") + 1) == "DataColumns")
                                            $stack[0] = $func_param[0]->Max();
                                        break;
                                    case "array":
                                        $objCol = new DataColumns($func_param[0]);
                                        $stack[0] = $objCol->Max();
                                        break;
                                }
                                break;
                        }
                        break;
                    case "SLOPE":
                        $sumX = 0;
                        $sumX2 = 0;
                        $sumY = 0;
                        $sumY2 = 0;
                        $sumXY = 0;
                        $dataX = array();
                        $dataY = array();

                        if(count($func_param) == 2){
                            $cntParam1 = 0;
                            $cntParam2 = 0;
                            switch(gettype($func_param[0])){
                                case "object":
                                    $className = get_class($func_param[0]);
                                    if(substr($className, strrpos($className, "\\") + 1) == "DataColumns"){
                                        $cntParam1 = $func_param[0]->Length;
                                        $sumY = $func_param[0]->Sum();
                                        $sumY2 = $func_param[0]->Pow()->Sum();
                                        $dataY = $func_param[0]->toArray();
                                    }
                                    break;
                                case "array":
                                    $objCol = new DataColumns($func_param[0]);
                                    $cntParam1 = $objCol->Length;
                                    $sumY = $objCol->Sum();
                                    $sumY2 = $objCol->Pow()->Sum();
                                    $dataY = $objCol->toArray();
                                    break;
                            }

                            switch(gettype($func_param[1])){
                                case "object":
                                    $className = get_class($func_param[1]);
                                    if(substr($className, strrpos($className, "\\") + 1) == "DataColumns"){
                                        $cntParam2 = $func_param[1]->Length;
                                        $sumX = $func_param[1]->Sum();
                                        $sumX2 = $func_param[1]->Pow()->Sum();
                                        $dataX = $func_param[1]->toArray();
                                    }
                                    break;
                                case "array":
                                    $objCol = new DataColumns($func_param[1]);
                                    $cntParam2 = $objCol->Length;
                                    $sumX = $objCol->Sum();
                                    $sumX2 = $objCol->Pow()->Sum();
                                    $dataX = $objCol->toArray();
                                    break;
                            }
                        }

                        if($cntParam1 == $cntParam2){
                            $N = $cntParam1 = $cntParam2;
                            for($index = 0; $index < $N; $index++)
                                $sumXY += ($dataX[$index] * $dataY[$index]);
                            $stack[0] = ($N * $sumXY - $sumX * $sumY) / ($N * $sumX2 - $sumX * $sumX);
                        }else{
                            $stack[0] = "N/A";
                        }
                        break;
                    case "SIGMA":
                        switch(count($func_param)){
                            case 0:
                                $stack[0] = null;
                                break;
                            case 1:
                                switch(gettype($func_param[0])){
                                    case "object":
                                        $className = get_class($func_param[0]);
                                        if(substr($className, strrpos($className, "\\") + 1) == "DataColumns")
                                            $stack[0] = $func_param[0]->Sigma(); 
                                        break;
                                    case "array":
                                        $objCol = new DataColumns($func_param[0]);
                                        $stack[0] = $objCol->Sigma();
                                        break;
                                }
                                break;
                        }
                        break;
                    case "ROUND":
                        switch(count($func_param)){
                            case 0:
                                $stack[0] = null;
                                break;
                            case 1:
                                $stack[0] = round((float)$func_param[0]);
                                break;
                            case 2:
                                $stack[0] = round((float)$func_param[0], (int)$func_param[1]);
                                break;
                        }
                        break;
                    case "FLOOR":
                        switch(count($func_param)){
                            case 0:
                                $stack[0] = null;
                                break;
                            case 1:
                                $stack[0] = floor((float)$func_param[0]);
                                break;
                        }
                        break;
                    case "SIN":
                        if(count($func_param) == 1)
                            $stack[0] = sin((float)$func_param[0]);
                        break;
                    case "COS":
                        if(count($func_param) == 1)
                            $stack[0] = cos((float)$func_param[0]);
                        break;
                    case "TAN":
                        if(count($func_param) == 1)
                            $stack[0] = tan((float)$func_param[0]);
                        break;
                }
            }
        }

        $res = true;
        foreach($stack as $item){
            if(gettype($item) == "object" || gettype($item) == "array"){
                $res = false;
                break;  
            }
        }
        return ($res)?self::Calculation(implode("", $stack)):"N/A";
    }

    public function ComparisonOperator($formula){
        $formula = str_replace('&gt;', '>', $formula);
        $formula = str_replace('&lt;', '<', $formula);

        $leftFormula = '';
        $rightFormula = '';
        $charIdx = (strpos($formula, '=') !== false)?2:1;
        $operator = '';

        if(strpos($formula, '>') !== false)
            $leftFormula = substr($formula, 0, strpos($formula, '>'));
        else if(strpos($formula, '<') !== false)
            $leftFormula = substr($formula, 0, strpos($formula, '<'));
        else if(strpos($formula, '=') !== false)
            $leftFormula = substr($formula, 0, strpos($formula, '='));
        else if(strpos($formula, '!') !== false)
            $leftFormula = substr($formula, 0, strpos($formula, '!'));

        if(strpos($formula, '>') !== false){
            $operator = substr($formula, strpos($formula, '>'), $charIdx);
            $rightFormula = substr($formula, strpos($formula, '>') + $charIdx);
        }else if(strpos($formula, '<') !== false){
            $operator = substr($formula, strpos($formula, '<'), $charIdx);
            $rightFormula = substr($formula, strpos($formula, '<') + $charIdx);
        }else if(strpos($formula, '=') !== false){
            $operator = substr($formula, strpos($formula, '='), $charIdx);
            $rightFormula = substr($formula, strpos($formula, '=') + $charIdx);
        }else if(strpos($formula, '!') !== false){
            $operator = substr($formula, strpos($formula, '!'), $charIdx);
            $rightFormula = substr($formula, strpos($formula, '!') + $charIdx);
        }

        $result = false;
        $leftFormula = $this->AlgorithmOperation(trim($leftFormula));
        $rightFormula = $this->AlgorithmOperation(trim($rightFormula));

        switch($operator){
            case "<":
                $result = $leftFormula < $rightFormula;
                break;
            case "<=":
                $result = $leftFormula <= $rightFormula;
                break;
            case ">":
                $result = $leftFormula > $rightFormula;
                break;
            case ">=":
                $result = $leftFormula >= $rightFormula;
                break;
            case "==":
                $result = $leftFormula == $rightFormula;
                break;
            case "!=":
                $result = $leftFormula != $rightFormula;
                break;
        }
        
        return $result;
    }

    public function Calculation($formula){
        $stack = array();
        $value = "";
        $operator = "";
        for($txtIdx = 0; $txtIdx < strlen($formula); $txtIdx++){
            switch($formula[$txtIdx]){
                case "+":
                case "-":
                    if($operator == "*"){
                        $stack[count($stack) - 1] *= floatval($value);
                    }else if($operator == "/"){
                        $stack[count($stack) - 1] /= floatval($value);
                    }else{
                        array_push($stack, floatval($value));
                    }
                    array_push($stack, $formula[$txtIdx]);
                    $value = "";
                    $operator = "";
                    break;
                case "*":
                case "/":
                    if($operator == "*"){
                        $stack[count($stack) - 1] *= floatval($value);
                    }else if($operator == "/"){
                        $stack[count($stack) - 1] /= floatval($value);
                    }else{
                        array_push($stack, floatval($value));
                    }
                    $value = "";
                    $operator = $formula[$txtIdx];
                    break;
                default:
                    $value .= $formula[$txtIdx];
                    break;
            }
        }
        if($operator == "*"){
            $stack[count($stack) - 1] *= floatval($value);
        }else if($operator == "/"){
            $stack[count($stack) - 1] /= floatval($value);
        }else{
            array_push($stack, floatval($value));
        }

        $value = 0;
        $operator = "";
        while(count($stack) != 0){
            switch($stack[0]){
                case "+":
                case "-":
                    $operator = $stack[0];
                    break;
                default:
                    if($operator == "+"){
                        $value += $stack[0];
                    }else if($operator == "-"){
                        $value -= $stack[0];
                    }else{
                        $value = $stack[0];
                    }
                    break;
            }
            array_shift($stack);
        }
        return ($value != "")?$value:$formula;
    }
}

class DataRows{
    private $_fieldCount;
    private $_rawdata;

    public function __construct($rawdata){
        $this->_fieldCount = count($rawdata);
        $this->_rawdata = $rawdata;
    }

    public function __invoke($Keyname){
        return $this->_rawdata[$Keyname];
    }

    public function __get($name){
        switch($name){
            case "FieldCount":
                return $this->_fieldCount;
                break;
        }
    }

    public function Field($Keyname){
        return $this->_rawdata[$Keyname];
    }

    public function __toString(){
        return implode(",", $this->_rawdata);
    }
}

class DataColumns{
    private $_rowsCount;
    private $_rawdata;

    public function __construct($rawdata){
        $this->_rowsCount = count($rawdata);
        if(is_array($rawdata))
            $this->_rawdata = $rawdata;
        else
            $this->_rawdata = array($rawdata);
    }

    public function __invoke($index = null){
        $index = ($index == null)?array_key_first($this->_rawdata):$index;
        return $this->_rawdata[$index];
    }

    public function __get($name){
        switch($name){
            case "Length":
                return count($this->_rawdata);
                break;
        }
    }

    public function toArray(){
        if(is_array($this->_rawdata))
            return $this->_rawdata;
        return array();
    }

    public function getValue($index = null){
        $index = ($index == null)?array_key_first($this->_rawdata):$index;
        return $this->_rawdata[$index];
    }

    public function Sigma(){
        $avg_value = $this->Avg();
        $sigma = 0;
        foreach($this->_rawdata as $rows)
            $sigma += pow($rows - $avg_value, 2);
        $sigma /= $this->_rowsCount;
        return sqrt($sigma);
    }

    public function Sum(){
        return array_sum($this->_rawdata);
    }

    public function Avg(){
        return (array_sum($this->_rawdata) / $this->_rowsCount);
    }

    public function Max(){
        return max($this->_rawdata);
    }

    public function Min(){
        return min($this->_rawdata);
    }

    // 四捨五入
    public function Round($digit = 2){
        $data = array();
        foreach($this->_rawdata as $key => $value)
            array_push($data, round($value, $digit));
        $objColumn = new DataColumns($data);
        return $objColumn;
    }

    // 無條件進位
    public function Ceil(){
        $data = array();
        foreach($this->_rawdata as $key => $value)
            array_push($data, ceil($value));
        $objColumn = new DataColumns($data);
        return $objColumn;
    }

    // 無條件捨去
    public function Floor(){
        $data = array();
        foreach($this->_rawdata as $key => $value)
            array_push($data, floor($value));
        $objColumn = new DataColumns($data);
        return $objColumn;
    }

    // 平方
    public function Pow($index = 2){
        $data = array();
        foreach($this->_rawdata as $key => $value)
            array_push($data, pow($value, $index));
        $objColumn = new DataColumns($data);
        return $objColumn;
    }

    // 開根號
    public function Sqrt($index = 2){
        $data = array();
        foreach($this->_rawdata as $key => $value)
            array_push($data, pow($value, (1.0/$index)));
        $objColumn = new DataColumns($data);
        return $objColumn;
    }

    // 階層
    public function Factorial(){
        $data = array();
        foreach($this->_rawdata as $key => $value)
            array_push($data, gmp_fact($value));
        $objColumn = new DataColumns($data);
        return $objColumn;
    }
}

?>
