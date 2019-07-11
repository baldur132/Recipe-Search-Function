<?php
/*
 User: Baldur.Siegel
 Date: 3/29/2019
 Time: 5:24 PM
 */

function searchFunction($userQuery, $orderData){
    $resultData[0] = 10;
    /*$pspell_link = pspell_new("en");*/
    //$tableData = array();

    $parseData = parseQuery($userQuery, $orderData);
    $query1 = $parseData[0];
    $columns = $parseData[1];
    $keywords = $parseData[2];
    $colError = $parseData[3];

    $tableData[0]['Initial Query Parse'] = $query1;
    $tableData[0]['Initial Columns'] = $columns;
    $tableData[0]['Initial Keywords'] = $keywords;
    $tableData[0]['ColErr'] = $colError;

    //$query = "SELECT * FROM RecipeTable WHERE RecipeTitle LIKE 'a%' ORDER BY RecipeTitle ASC";
    //$searchResult = executeQuery($query);
    //$query = "SELECT * FROM RecipeTable WHERE RecipeTitle LIKE 'apple%' ORDER BY RecipeTitle ASC";
    //$searchResult1 = executeQuery($query);
    $resultData[1] = $userQuery;
    $resultData[2] = $tableData;
    //$resultData[3] = $searchResult1;

    $testResponse = $resultData;
    return $testResponse;
}

function parseQuery($stringInput, $orderData){
    if(preg_match('#\b(all)\b#i', $stringInput) === 1){
        $query = 'SELECT * FROM'.' RecipeTable';
        return $query;
    }

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
        $validColumns = array('RecipeID', 'RecipeTitle', 'Course', 'URL', 'Source',
            'MainIngredient', 'Region', 'Category', 'DateCreated', 'LastOnMenu',
            'OnMenu', 'DateModified', 'Ingedients', 'IngredientsContinued', 'Instructions',
            'Notes', 'Servings', 'Special', 'Rating', 'PrepTime', 'CookTime', 'Wine', 'Language',);
        $stringInput = str_replace(' ', '', $stringInput);
        if(strpos($stringInput, ':') !== false){
            $explodeResults = explode(':', $stringInput);
            $colResult = str_replace(' ', '', $explodeResults[0]);
            $queryRaw = str_replace(' ', '', $explodeResults[1]);
            if(in_array($colResult, $validColumns)){
                $colInput = $colResult;
                $columns[0] = $colInput;
            } else {
                $colInput = 'RecipeTitle';
                $columns[0] = $colInput;
                $colError[0] = $colResult;
            }
            $queryParse = parseComma($queryRaw);
        } else {
            $colInput = 'RecipeTitle';
            $columns[0] = $colInput;
            $queryParse = parseComma($stringInput);
        }
        if(is_array($queryParse)){
            $queryCount = count($queryParse);
            if($queryCount >= 2){
                $keywordCount = 0;
                $columnCount = 0;
                for($i = 0; $i < $queryCount; $i++){
                    $keywords[$keywordCount] = $queryParse[$i];
                    $keywordCount++;
                    $columns[$columnCount] = $colInput;
                    $columnCount++;
                    if($i === 0){
                        $subsection .= "(".$colInput." LIKE '".$queryParse[$i]."%'";
                    } else if($i + 1 >= $queryCount){
                        $subsection .= " AND ".$colInput." LIKE '".$queryParse[$i]."%')";
                    } else {
                        $subsection .= " AND ".$colInput." LIKE '".$queryParse[$i]."%'";
                    }
                }
            } else {
                $error[0] = true;
                $errorCount = count($error);
                $error[$errorCount] = 'Invalid Query Count, While Array Found';
                return $error;
            }
        } else {
            $keywords[0] = $queryParse;
            $subsection .= $colInput." LIKE '".$queryParse."%'";
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

    /*$queryWord = str_replace(' ', '', $stringInput);
            $wordCorrect = checkSpelling($queryWord);
            if ($wordCorrect === false){
                return $queryWord;
            } else {
                return $wordCorrect;
            }
    function checkSpelling($wordInput){
       global $pspell_link;
       if (!pspell_check($pspell_link, $wordInput)) {
           $suggestions = pspell_suggest($pspell_link, $wordInput);
           $suggestion = $suggestions[0];
           return $suggestion;
       } else {
           return false;
       }
    }*/

    //parse orderData
    $explodeResults = explode(':', $orderData);
    $dataOrder = ' ORDER BY '.$explodeResults[0].' '.$explodeResults[1];

    $query = parseOr($stringInput);
    $query[0] = $query[0].$dataOrder;
    return $query;
}

function executeQuery($query){
    //Connect to recipe database (SGS original code)
    $database = '';
    $username = '';
    $password = '';
    include("dbinfo.inc.php");
    $db = new PDO("mysql:host=localhost; dbname=".$database, $username, $password);
    //fix umlaut encoding problems (SGS original code)
    $querySetup = "SET NAMES 'utf8'";
    $result = $db->query($querySetup);
    $result->closeCursor();

    //get number of matching records (SGS original code)
    $RecordsQuery = str_replace('SELECT *', 'SELECT COUNT(*)',$query);
    $res = $db->query($RecordsQuery);
    $num = $res->fetchColumn();
    $res->closeCursor();

    $tableData[0]['numResults'] = $num;

    //function below writes all the requested information from the query into the multidimensional array $tableData
    if ($num > 0){
        $QueryResult = $db->query($query);
        //while ($i < $num) {
        $i=1;
        while($Row = $QueryResult->fetch(PDO::FETCH_ASSOC)){
            $Category = $Row["Category"];
            $RecipeID = $Row["RecipeID"];
            $RecipeTitle = $Row["RecipeTitle"];
            $Course = $Row["Course"];
            $Source = $Row["Source"];
            $MainIngredient=$Row["MainIngredient"];
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
            if (substr($URL, 0,7)=='http://'){
                $tableData[$i]['URL'] = $URL;
                $tableData[$i]['Source'] = $Source;
            } else {
                $tableData[$i]['Source'] = $Source;
            }
            $tableData[$i]['MainIngredient'] = $MainIngredient;
            $tableData[$i]['Region'] = $Region;
            $tableData[$i]['Category'] = $Category;
            $tableData[$i]['DateCreated'] = $DateCreated;
            $tableData[$i]['LastOnMenu'] = $LastOnMenu;
            $tableData[$i]['OnMenu'] = $OnMenu;

            ++$i;
        }
        $QueryResult->closeCursor();
        $db = null;
        return($tableData);
    } else {
        $tableData[0]['errorMsg'] = '404';
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
                $aResult['result'] = searchFunction($_POST['arguments'][0], $_POST['arguments'][1]);
            }
            break;

        default:
            $aResult['error'] = 'Not found function '.$_POST['function'].'!';
            break;
    }

}

echo json_encode($aResult);