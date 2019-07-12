<?php
/*
 User: Baldur.Siegel
 Date: 2/20/2019
 Time: 7:54 PM

 TODO:  -translate query across languages (EN - DE)
        -parse out incorrect/extra symbols (i.e. '::')
        -correct incorrectly spelled words
        -add inclusion parameters ("apple and potato" searches for 'apple and potato')
        -optimise query column selection (only use needed columns)
 */

function searchFunction($userQuery, $orderData, $parameters){
    $resultData[0]['Signature'] = 47;

    //Parameter Declaration
    $forceStrict = $parameters[0];
    $forceExact = $parameters[1];
    $localOrder = $parameters[2];

    $validColumns = array('RecipeID', 'RecipeTitle', 'Course', 'URL', 'Source',
        'MainIngredient', 'Region', 'Category', 'DateCreated', 'LastOnMenu', 'OnMenu',
        'DateModified', 'Ingedients', 'IngredientsContinued', 'Instructions', 'Notes',
        'Servings', 'Special', 'Rating', 'PrepTime', 'CookTime', 'Wine', 'Language', 'NPictures');
    if($localOrder === 'true'){
        if(preg_match('#(;)#i', $userQuery)){
            $explodeResults = preg_split('#(;)#i', $userQuery);
            $userQuery = $explodeResults[0];
            if(preg_match('#(:)#i', $explodeResults[1])){
                $explodeRemnants = preg_split('#(:)#i', $explodeResults[1]);
                $explodeRemnants[0] = trim($explodeRemnants[0]);
                $explodeRemnants[1] = trim($explodeRemnants[1]);
                if(in_array($explodeRemnants[0], $validColumns)){
                    if($explodeRemnants[1] === 'DESC' || $explodeRemnants[1] === 'ASC') {
                        $orderData = str_replace(' ', '', $explodeResults[1]);
                        $resultData[0]['orderError'] = 'none';
                    } else {
                        $resultData[0]['orderError'] = 'Requested Sort Direction Invalid --> ' . $explodeRemnants[1];
                        $orderData = 'RecipeTitle:ASC';
                    }
                } else {
                    $resultData[0]['orderError'] = 'Requested Sort Column Invalid --> ' . $explodeRemnants[0];
                    $orderData = 'RecipeTitle:ASC';
                }
            } else {
                $resultData[0]['orderError'] = 'Micro Order Separator Not Found --> ' . $explodeResults[1];
                $orderData = 'RecipeTitle:ASC';
            }
        } else {
            $resultData[0]['orderError'] = 'Macro Order Separator Not Found';
            $orderData = 'RecipeTitle:ASC';
        }
    }

    $parseData = parseQueryStrict($userQuery, $orderData);
    $statement = $parseData[0];
    if($forceExact === 'true'){
        $resultData[0]['Trim Exact'] = true;
        $statement = str_replace('%', '', $statement);
    }
    $columns = $parseData[1];
    $keywords = $parseData[2];
    $colError = $parseData[3];

    $resultData[0]['Initial Query Parse'] = $statement;
    $resultData[0]['Initial Columns'] = $columns;
    $resultData[0]['Initial Keywords'] = $keywords;
    $resultData[0]['colErr'] = $colError;
    $resultData[0]['Parameters'] = $parameters;
    $resultData[0]['Input'] = $userQuery;
    $resultData[0]['orderData'] = $orderData;
    $resultData[0]['errorMsg'] = 'none';
    $resultData[0]['errorDetails'] = '';

    //Dynamic Query Selection/Execution
    $tableData = executeQuery($statement, $keywords);
    $tableData[0]['content'] = 'original';
    if($forceStrict !== 'true' && strpos($userQuery, ' ') !== false){
        $softData = parseQuerySoft($userQuery, $orderData);
        $querySet = $softData[0];
        $contentSet = $softData[1];
        $resultData[0]['contentSet'] = $softData[1];
        $queryCount = count($querySet);
        for($i = 0; $i < $queryCount; $i++){
            $resultData[0]['softQueries'][$i] = $querySet[$i];
            $softTableData = executeQuery($querySet[$i], array($contentSet[$i]));
            $softTableData[0]['content'] = str_replace('%', '', $contentSet[$i]);
            $e = $i + 2;
            $resultData[1] = $tableData;
            $resultData[$e] = $softTableData;
        }
        $resultData[0]['softExecution'] = true;
    } else {
        $resultData[0]['softExecution'] = false;
        $resultData[1] = $tableData;
    }

    if ($tableData[0]['errorMsg'] === 'SQLerr') {
        $resultData[0]['errorMsg'] = 'SQLerr';
        $resultData[0]['errorDetails'] .= 'Bad SQL Statement;';
        $resultData[1] = array(array('errorMsg' => '404', 'numResults' => '0'));
    }

    return $resultData;
}

function parseQueryStrict($stringInput, $orderData){
    function parseOr($stringInput){
        $queryHead = 'SELECT * FROM'.' RecipeTable WHERE ';
        $error = array(false);
        $columns = array();
        $keywords = array();
        $colError = array();
        if(preg_match('#\b(or)\b#i', $stringInput) === 1){
            $splitResult = preg_split('#\b(or)\b#i', $stringInput);
            $sectionCount = count($splitResult);
            $query = '';
            if($sectionCount >= 2){
                for($i = 0; $i < $sectionCount; $i++){
                    $sectionParse = parseAnd($splitResult[$i]);
                    $section = $sectionParse[0];
                    $columns = array_merge($columns, $sectionParse[1]);
                    $keywords = array_merge($keywords, $sectionParse[2]);
                    $colError = array_merge($colError, $sectionParse[3]);
                    if($i === 0){
                        $query .= $queryHead.'('.$section.')';
                    } else {
                        $query .= ' OR (' .$section. ')';
                    }
                }
                $returnArray = array($query, $columns, $keywords, $colError);
                return $returnArray;
            } else {
                $error[0] = true;
                $errorCount = count($error);
                $error[$errorCount] = 'Invalid Section Count';
                return $error;
            }
        } else {
            $sectionParse = parseAnd($stringInput);
            list($section, $columns, $keywords, $colError) = $sectionParse;
            $query = $queryHead.$section;
            $returnArray = array($query, $columns, $keywords, $colError);
            return $returnArray;
        }
    }

    function parseAnd($stringInput){
        $error = array(false);
        $columns = array();
        $keywords = array();
        $colError = array();
        if(preg_match('#\b(and)\b#i', $stringInput) === 1){
            $splitResult = preg_split('#\b(and)\b#i', $stringInput);
            $subsectionCount = count($splitResult);
            $section = '';
            if($subsectionCount >= 2){
                for($i = 0; $i < $subsectionCount; $i++){
                    $subsectionParse = parseColon($splitResult[$i]);
                    $subsection = $subsectionParse[0];
                    $columns = array_merge($columns, $subsectionParse[1]);
                    $keywords = array_merge($keywords, $subsectionParse[2]);
                    $colError = array_merge($colError, $subsectionParse[3]);
                    if($i === 0) {
                        $section .= '('.$subsection;
                    } else if($i + 1 >= $subsectionCount){
                        $section .= ' AND '.$subsection.')';
                    } else {
                        $section .= ' AND '.$subsection;
                    }
                }
                $returnArray = array($section, $columns, $keywords, $colError);
                return $returnArray;
            } else {
                $error[0] = true;
                $errorCount = count($error);
                $error[$errorCount] = 'Invalid Subsection Count';
                return $error;
            }
        } else {
            $subsectionParse = parseColon($stringInput);
            list($section, $columns, $keywords, $colError) = $subsectionParse;
            $returnArray = array($section, $columns, $keywords, $colError);
            return $returnArray;
        }
    }

    function parseColon($stringInput){
        $error = array(false);
        $columns = array();
        $keywords = array();
        $colError = array();
        $subsection = '';
        $notPresent = false;
        $validColumns = array('RecipeID', 'RecipeTitle', 'Course', 'URL', 'Source',
            'MainIngredient', 'Region', 'Category', 'DateCreated', 'LastOnMenu', 'OnMenu',
            'DateModified', 'Ingedients', 'IngredientsContinued', 'Instructions', 'Notes',
            'Servings', 'Special', 'Rating', 'PrepTime', 'CookTime', 'Wine', 'Language', 'NPictures');
        if(preg_match('#\b(not)\b#i', $stringInput) === 1){
            $stringInput = preg_replace('#\b(not)\b#i', '', $stringInput);
            $notPresent = true;
        }
        $stringInput = str_replace(' ', '', $stringInput);
        if(strpos($stringInput, ':') !== false){
            $explodeResults = explode(':', $stringInput);
            $colResult = str_replace(' ', '', $explodeResults[0]);
            $queryRaw = str_replace(' ', '', $explodeResults[1]);
            if(in_array($colResult, $validColumns)){
                $colInput = $colResult;
                $colInputRaw = $colInput;
                $columns[0] = $colInput;
            } else {
                $colInput = 'RecipeTitle';
                $colInputRaw = $colInput;
                $columns[0] = $colInput;
                $colError[0] = $colResult;
            }
            $queryParse = parseComma($queryRaw);
        } else {
            $colInput = 'RecipeTitle';
            $colInputRaw = $colInput;
            $columns[0] = $colInput;
            $queryParse = parseComma($stringInput);
        }
        if($notPresent){
            $colInput = "NOT ".$colInput;
        }
        if(is_array($queryParse)){
            $queryCount = count($queryParse);
            if($queryCount >= 2){
                $keywordCount = 0;
                $columnCount = 0;
                for($i = 0; $i < $queryCount; $i++){
                    $keywords[$keywordCount] = '%'.$queryParse[$i].'%';
                    $keywordCount++;
                    $columns[$columnCount] = $colInputRaw;
                    $columnCount++;
                    if($i === 0){
                        $subsection .= "(".$colInput." LIKE ?";
                    } else if($i + 1 >= $queryCount){
                        $subsection .= " AND ".$colInput." LIKE ?)";
                    } else {
                        $subsection .= " AND ".$colInput." LIKE ?";
                    }
                }
            } else {
                $error[0] = true;
                $errorCount = count($error);
                $error[$errorCount] = 'Invalid Query Count, While Array Found';
                return $error;
            }
        } else {
            $keywords[0] = '%'.$queryParse.'%';
            $subsection = $colInput." LIKE ?";
        }
        $returnArray = array($subsection, $columns, $keywords, $colError);
        return $returnArray;
    }

    function parseComma($stringInput){
        if (strpos($stringInput, ',') !== false){
            $explodeResults = explode(',', $stringInput);
            $queryCount = count($explodeResults);
            $querySubsection = array();
            for ($i = 0; $i < $queryCount; $i++){
                $querySubsection[$i] = str_replace(' ', '', $explodeResults[$i]);
            }
            return $querySubsection;
        } else {
            $query = $stringInput;
            $query = str_replace(' ', '', $query);
            return $query;

        }
    }

    //parse orderData
    $explodeResults = explode(':', $orderData);
    $dataOrder = ' ORDER BY '.$explodeResults[0].' '.$explodeResults[1];

    $query = parseOr($stringInput);
    $query[0] = $query[0].$dataOrder;
    return $query;
}

function parseQuerySoft($stringInput, $orderData){
    $validColumns = array('RecipeID', 'RecipeTitle', 'Course', 'URL', 'Source',
        'MainIngredient', 'Region', 'Category', 'DateCreated', 'LastOnMenu',
        'OnMenu', 'DateModified', 'Ingedients', 'IngredientsContinued', 'Instructions',
        'Notes', 'Servings', 'Special', 'Rating', 'PrepTime', 'CookTime', 'Wine', 'Language',);
    $queryHead = 'SELECT *'.' FROM RecipeTable WHERE ';
    $columns = array();
    $queries = array();
    $querySet = array();

    //parse orderData
    $explodeResults = explode(':', $orderData);
    $dataOrder = ' ORDER BY '.$explodeResults[0].' '.$explodeResults[1];

    if(preg_match('#(,|:|and|or)#i', $stringInput) === 1) {
        if(preg_match('#\b(and)\b#i', $stringInput)){
            $stringInput = preg_replace('#(and)#i', ' ', $stringInput);
        }
        if(preg_match('#\b(or)\b#i', $stringInput)){
            $stringInput = preg_replace('#(or)#i', ' ', $stringInput);
        }
        if(strpos($stringInput, ',')) {
            $stringInput = str_replace(',', ' ', $stringInput);
        }
        if(strpos($stringInput, ':')) {
            $stringInput = str_replace(':', ' ', $stringInput);
        }
    }

    $inputSeparated = explode(' ', $stringInput);
    $count = count($inputSeparated);
    $queries[0] = $stringInput;
    for($i = 0; $i < $count; $i++){
        if(in_array($inputSeparated[$i], $validColumns)){
            $pointer = count($columns);
            $columns[$pointer] = $inputSeparated[$i];
        } else {
            if($inputSeparated[$i] !== '') {
                $pointer = count($queries);
                $queries[$pointer] = '%'.$inputSeparated[$i].'%';
            }
        }
    }
    $queryCount = count($queries);
    for($i = 0; $i < $queryCount; $i++){
        $querySet[$i] = $queryHead." RecipeTitle LIKE ?".$dataOrder;
    }
    $returnArray = array($querySet, $queries);
    return $returnArray;
}

function executeQuery($statement, $keywords){
    //Connect to recipe database
    $database = '';
    $username = '';
    $password = '';
    $tableData = array();
    include("dbinfo.inc.php");
    $db = new PDO("mysql:host=localhost; dbname=".$database, $username, $password);
    //fix umlaut encoding problems
    $querySetup = "SET NAMES 'utf8'";
    $res = $db->query($querySetup);
    $res->closeCursor();

    //function below writes all the requested information from the query into the multidimensional array $tableData
    $res = $db->prepare($statement, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    if($res !== false){
        $res->execute($keywords);
        $success = true;
    } else {
        $tableData[0]['errorData'] = 'Statement: ' .$statement. ' || Keywords: ' .$keywords;
        $tableData[0]['errorMsg'] = 'SQLerr';
        $success = false;
    }
    if($success === true){
        $i = 1;
        $tableData[0]['numResults'] = $res->rowCount();
        $data = $res->fetchAll(PDO::FETCH_ASSOC);
        foreach($data as $Row){
            $Category = $Row["Category"];
            $RecipeID = $Row["RecipeID"];
            $RecipeTitle = $Row["RecipeTitle"];
            $Course = $Row["Course"];
            $Source = $Row["Source"];
            $MainIngredient = $Row["MainIngredient"];
            $Region = $Row["Region"];
            $DateCreated = $Row["DateCreated"];
            if ($DateCreated == '0000-00-00' || $DateCreated == null) $DateCreated = '';
            $OnMenu = $Row["OnMenu"];
            $URL = $Row["URL"];
            $LastOnMenu = $Row["LastOnMenu"];
            if ($LastOnMenu == '0000-00-00' || $LastOnMenu == null) $LastOnMenu = '';
            $tableData[$i]['RecipeID'] = $RecipeID;
            $tableData[$i]['RecipeTitle'] = $RecipeTitle;
            $tableData[$i]['Course'] = $Course;
            if (substr($URL, 0, 7) == 'http://') {
                $tableData[$i]['URL'] = $URL;
                $tableData[$i]['Source'] = $Source;
            } else {
                $tableData[$i]['URL'] = 'false';
                $tableData[$i]['Source'] = $Source;
            }
            $tableData[$i]['MainIngredient'] = $MainIngredient;
            $tableData[$i]['Region'] = $Region;
            $tableData[$i]['Category'] = $Category;
            $tableData[$i]['DateCreated'] = $DateCreated;
            $tableData[$i]['LastOnMenu'] = $LastOnMenu;
            $tableData[$i]['OnMenu'] = $OnMenu;
            $i = $i + 1;
        }
        $tableData[0]['errorMsg'] = 'none';
        $res->closeCursor();
        $db = null;
        return($tableData);
    } else {
        $tableData[0]['errorMsg'] = '404';
        $tableData[0]['numResults'] = '0';
        return($tableData);
    }
}

/*Credit to StackExchange users Victor and Christian Alexandru*/
$aResult = array();

if( !isset($_POST['function']) ) { $aResult['error'] = 'No function name!'; }

if( !isset($_POST['arguments']) ) { $aResult['error'] = 'No function arguments!'; }

if( !isset($aResult['error']) ) {

    switch($_POST['function']) {
        case 'searchFunction':
            if( !is_array($_POST['arguments']) || (count($_POST['arguments']) < 1) ) {
                $aResult['error'] = 'Error in arguments!';
            }
            else {
                $aResult['result'] = searchFunction($_POST['arguments'][0], $_POST['arguments'][1], $_POST['arguments'][2]);
            }
            break;

        default:
            $aResult['error'] = 'Not found function '.$_POST['function'].'!';
            break;
    }

}

echo json_encode($aResult);