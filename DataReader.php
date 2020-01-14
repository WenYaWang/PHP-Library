<?php 

class DataReader{
    private $_rawdata;
    
    public function __construct($rawdata){
        $this->_rawdata = $rawdata;
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

    public function AlgorithmOperation($formula){
        $stack = array();
        for($txtIdx = strlen($formula) - 1; $txtIdx >= 0; $txtIdx--){
            if($formula[$txtIdx] != "("){
                array_unshift($stack, $formula[$txtIdx]);
            }else{
                switch($formula[$txtIdx - 1]){
                    case "+":
                    case "-":
                    case "*":
                    case "/":
                        $algorithm = "";
                        while($stack[0] != ")"){
                            $algorithm .= $stack[0];
                            array_shift($stack);
                        }
                        $stack[0] = $this->Calculation($algorithm);
                        break;
                    default:
                        $func_name = "";
                        $func_param = array();

                        $txtIdx--;
                        while($formula[$txtIdx] != "+" && 
                                $formula[$txtIdx] != "-" &&
                                $formula[$txtIdx] != "*" && 
                                $formula[$txtIdx] != "/" && 
                                $formula[$txtIdx] != "(" && $txtIdx >= 0){
                            $func_name = $formula[$txtIdx].$func_name;
                            $txtIdx--;
                        }
                        $txtIdx++;
                        
                        $param = null;
                        while($stack[0] != ")"){
                            if($stack[0] != ","){
                                switch(strtolower(gettype($stack[0]))){
                                    case "integer":
                                    case "string":
                                        $param = ($param != null && $param != "")?$param:"";
                                        $param = $param.$stack[0];
                                        break;
                                    default:
                                        $param = $stack[0];
                                        break;
                                }
                            }else{
                                array_push($func_param, $param);
                                $param = null;
                            }
                            array_shift($stack);
                        }
                        array_push($func_param, $param);
                        
                        switch(strtoupper($func_name)){
                            case "FIRST":
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
                            case "LAST":
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
                            case "RANGE":
                                switch(count($func_param)){
                                    case 2:
                                        $stack[0] = $this->Range((int)$func_param[0], (int)$func_param[1]);
                                        break;
                                    case 3:
                                        $stack[0] = $this->Range((int)$func_param[1], (int)$func_param[2])->Column((string)$func_param[0]);
                                        break;
                                }
                                break;
                            case "LRANGE":
                                switch(count($func_param)){
                                    case 2:
                                        $stack[0] = $this->LRange((int)$func_param[0], (int)$func_param[1]);
                                        break;
                                    case 3:
                                        $stack[0] = $this->LRange((int)$func_param[1], (int)$func_param[2])->Column((string)$func_param[0]);
                                        break;
                                }
                                break;
                            case "COLUMN":
                                switch(count($func_param)){
                                    case 1:
                                        switch(strtolower(gettype($func_param[0]))){
                                            case "string":
                                                $stack[0] = $this->Column((string)$func_param[0]);
                                                break;
                                            case "object":
                                                $stack[0] = $func_param[0]->Column();
                                                break;
                                        }
                                        break;   
                                    case 2:
                                        $stack[0] = $func_param[1]->Column((string)$func_param[0]);
                                        break;
                                }
                                break;
                            case "SUM":
                                switch(count($func_param)){
                                    case 0:
                                        $stack[0] = 0;
                                        break;
                                    case 1:
                                        switch(strtolower(gettype($func_param[0]))){
                                            case "object":
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
                                        $stack[0] = 0;
                                        break;
                                    case 1:
                                        switch(strtolower(gettype($func_param[0]))){
                                            case "object":
                                                $stack[0] = $func_param[0]->Avg();
                                                break;
                                            case "array":
                                                $objCol = new DataColumns($func_param[0]);
                                                $stack[0] = $objCol->Avg();
                                                break;
                                        }
                                }
                                break;
                            case "MAX":
                                switch(count($func_param)){
                                    case 0:
                                        $stack[0] = 0;
                                        break;
                                    case 1:
                                        switch(strtolower(gettype($func_param[0]))){
                                            case "object":
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
                            case "MIN":
                                switch(count($func_param)){
                                    case 0:
                                        $stack[0] = 0;
                                        break;
                                    case 1:
                                        switch(strtolower(gettype($func_param[0]))){
                                            case "object":
                                                $stack[0] = $func_param[0]->Min();
                                                break;
                                            case "array":
                                                $objCol = new DataColumns($func_param[0]);
                                                $stack[0] = $objCol->Min();
                                                break;
                                        }
                                        break;
                                }
                                break;
                            case "SLOPE":
                                switch(count($func_param)){
                                    case 0:
                                        $stack[0] = 0;
                                        break;
                                    case 1:
                                        switch(strtolower(gettype($func_param[0]))){
                                            case "object":
                                                $stack[0] = $func_param[0]->Slope();
                                                break;
                                            case "array":
                                                $objCol = new DataColumns($func_param[0]);
                                                $stack[0]->$objCol->Slope();
                                                break;
                                        }
                                        break;
                                }
                                break;
                            case "POW":
                                switch(count($func_param)){
                                    case 1:
                                        if(strtolower(gettype($func_param[0])) == "object" && strtolower(get_class($func_param[0])) == "datacolumns"){
                                            $stack[0] = $func_param[0]->Pow();
                                        }else if(is_numeric($func_param[0])){
                                            $stack[0] = pow($func_param[0], 2);
                                        }
                                        break;
                                    case 2:
                                        if(strtolower(gettype($func_param[0]) == "object") && strtolower(get_class($func_param[0])) == "datacolumns"){
                                            $stack[0] = $func_param[0]->Pow($func_param[1]);
                                        }else if(is_numeric($func_param[0])){
                                            $stack[0] = pow($func_param[0], $func_param[1]);
                                        }
                                        break;
                                }
                                break;
                            case "SQRT":
                                switch(count($func_param)){
                                    case 1:
                                        if(strtolower(gettype($func_param[0])) == "object" && strtolower(get_class($func_param[0])) == "datacolumns"){
                                            $stack[0] = $func_param[0]->Sqrt();
                                        }else if(is_numeric($func_param[0])){
                                            $stack[0] = pow($func_param[0], 0.5);
                                        }
                                        break;
                                    case 2:
                                        if(strtolower(gettype($func_param[0])) == "object" && strtolower(get_class($func_param[0])) == "datacolumns"){
                                            $stack[0] = $func_param[0]->Sqrt($func_param[1]);
                                        }else if(is_numeric($func_param[0])){
                                            $stack[0] = pow($func_param[0], (1.0 / $func_param[1]));
                                        }
                                        break;
                                }
                                break;
                            case "SIN":
                                if(count($func_param) == 1){
                                    $stack[0] = sin($func_param[0]);
                                }else{
                                    $stack[0] = 0;
                                }
                                break;
                            case "COS":
                                if(count($func_param) == 1){
                                    $stack[0] = cos($func_param[0]);
                                }else{
                                    $stack[0] = 0;
                                }
                                break;
                            case "TAN":
                                if(count($func_param) == 1){
                                    $stack[0] = tan($func_param[0]);
                                }else{
                                    $stack[0] = 0;
                                }
                                break;
                        }
                }
            }
            
        }

        return $this->Calculation(implode("", $stack));
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
        $leftFormula = $this->AlgorithmOperation($leftFormula);
        $rightFormula = $this->AlgorithmOperation($rightFormula);

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
        $bfVal = "";
        $aftVal = "";
        $operator = "";

        for($txtIdx = 0; $txtIdx < strlen($formula); $txtIdx++){
            if($formula[$txtIdx] == "*" || $formula[$txtIdx] == "/"){
                if($bfVal != "")
                    array_push($stack, $bfVal);
                $operator = $formula[$txtIdx];
                $txtIdx++;
                for($chrIdx = $txtIdx; $chrIdx < strlen($formula); $chrIdx++){
                    if($formula[$chrIdx] != "+" && $formula[$chrIdx] != "-" && $formula[$chrIdx] != "*" && $formula[$chrIdx] != "/"){
                        $aftVal .= $formula[$chrIdx];
                        $txtIdx++;
                    }else{
                        break;
                    }
                }

                switch($operator){
                    case "*":
                        $stack[count($stack) - 1] *= $aftVal;
                        break;
                    case "/":   
                        $stack[count($stack) - 1] /= $aftVal;
                        break;
                }

                $txtIdx -= 1;
                $bfVal = "";
                $aftVal = "";
            }else if($formula[$txtIdx] == "+" || $formula[$txtIdx] == "-"){
                if($bfVal != "")
                    array_push($stack, $bfVal);
                array_push($stack, $formula[$txtIdx]);
                $bfVal = "";
            }else{
                $bfVal .= $formula[$txtIdx];
            }
        }
        if($bfVal != "")
            array_push($stack, $bfVal);

        $bfVal = 0;
        $aftVal = 0;

        for($keyIdx = 0; $keyIdx < count($stack); $keyIdx++){
            if($bfVal == ""){
                $bfVal = $stack[$keyIdx];
            }else if($stack[$keyIdx] == "+" || $stack[$keyIdx] == "-"){
                $operator = $stack[$keyIdx];
                $aftVal = $stack[++$keyIdx];
                switch($operator){
                    case "+":
                        $bfVal = $bfVal + $aftVal;
                        break;
                    case "-":
                        $bfVal = $bfVal - $aftVal;
                        break;
                }
            }
        }

        return $bfVal;
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

    public function getValue($index = null){
        $index = ($index == null)?array_key_first($this->_rawdata):$index;
        return $this->_rawdata[$index];
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

    // 斜率
    public function Slope(){
        $y1 = $this->_rawdata[0];
        $y2 = $this->_rawdata[count($this->_rawdata) - 1];
        $x1 = 1;
        $x2 = count($this->_rawdata);
        $result = ($y2 - $y1) / ($x2 - $x1);
        return $result;
    }
}

?>
