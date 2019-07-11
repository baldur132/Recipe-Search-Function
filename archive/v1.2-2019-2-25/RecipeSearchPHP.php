<?php
/*
 User: Baldur.Siegel
 Date: 2/20/2019
 Time: 7:54 PM

 TODO: -make multiple searches for same term
       -translate query across languages (EN - DE)
       -parse out incorrect/extra symbols (i.e. '::')
       -correct incorrectly spelled words

 V1 Working: 2019-21-2:
    basic functionality achieved, can receive a search term and preform
    a basic fuzzy matching query against server, and return it as a JSON object
 */

function searchFunction($queryInputRaw){
    //Connect to recipe database (SGS original code)
    $database = '';
    $username = '';
    $password = '';
    include("dbinfo.inc.php");
    $db = new PDO("mysql:host=localhost; dbname=".$database, $username, $password);
    //fix umlaut encoding problems (SGS original code)
    $query = "SET NAMES 'utf8'";
    $result = $db->query($query);
    $result->closeCursor();

    $errorMsg = array();
    $tableData = array();
    $columnInput = array('RecipeTitle');
    $queryParam = array();
    $queryiesUnparsed = array();
    $queryInput = array();
    $matches = array(false, false, false, false);

    function parseByAnd($stringInput){
        $explodeResults = explode('and', $stringInput);
        $queryCount = count($explodeResults);
        $queryCount = $queryCount - 1;
        for ($i = 0; $i < $queryCount; $i++) {
            $queriesUnparsed[$i] = str_replace(' ', '', $explodeResults[$i]);
        }
        $headerLen = 2;
        $voneNum = $queryCount;
        $reply = array($headerLen -1, $voneNum);
        for ($i = $headerLen; $i == $headerLen + $voneNum; $i++) {
            $e = $i - $headerLen;
            $reply[$i] = $queriesUnparsed[$e];
        }
    }

    function parseByColon($stringInput){
        $validColumns = array('RecipeID', 'RecipeTitle', 'Course', 'URL', 'Source',
            'MainIngredient', 'Region', 'Category', 'DateCreated', 'LastOnMenu',
            'OnMenu', 'DateModified', 'Ingedients', 'IngredientsContinued', 'Instructions',
            'Notes', 'Servings', 'Special', 'Rating', 'PrepTime', 'CookTime', 'Wine', 'Language',);
        $queryCount = count($stringInput);
        $queryCount = $queryCount - 1;
        for ($i = 0; $i < $queryCount; $i++){
            if (strpos($stringInput[$i], ':')){
                $explodeResults = explode(':', $stringInput);
                if (in_array($explodeResults[0], $validColumns)) {
                    $columnInput[$i] = str_replace(' ', '', $explodeResults[0]);
                } else {
                    $tableData[0]['errBadCol'.$i] = 'true';
                }
                $queryInput[$i] = str_replace(' ', '', $explodeResults[1]);
            } else {
                $columnInput[$i] = 'RecipeTitle';
            }
        }
    }

    function parseBySemiColon ($stringInput){
        $queryCount = count($stringInput);
        $queryCount = $queryCount - 1;
        for ($i = 0; $i < $queryCount; $i++) {
            if (strpos($stringInput[$i], ';')) {
                $explodeResults = explode(';', $stringInput[$i]);
                $paramCount = count($explodeResults);
                $paramCount = $paramCount -1;
                for ($e = 0; $e < $paramCount; $e++) {
                    $queryParam[$i][$e] = str_replace(' ', '', $explodeResults[$i]);
                }
                $queryInput = str_replace(' ', '', $explodeResults[$paramCount]);
            } else {
                $queryParam[$i] = 'noParams';
            }
        }
    }

    function parseByComma($stringInput){
        $queryCount = count($stringInput);
        $queryCount = $queryCount -1;
        for($i = 0; $i < $queryCount; $i++){
            if (strpos($stringInput[$i], ',')){
                $explodeResults = explode(',', $stringInput[$i]);
                $subQueryCount = count($explodeResults);
                $subQueryCount = $subQueryCount -1;
                for ($e = 0; $e < $subQueryCount; $e++){
                    $queryInput[$i][$e] = str_replace(' ', '', $explodeResults[$e]);
                }
            } else {
                $queryInput[$i] = $stringInput;
            }
        }
    }

    if (preg_match('/(and|:|;|,)/i', $queryInputRaw) === 1) {
        if (preg_match('/(and)/i', $queryInputRaw === 1)){
            parseByAnd($queryInputRaw);
        }
        if (strpos($queryInputRaw, ':')){
            parseByColon($queryInputRaw);
        }
        if (strpos($queryInputRaw, ';')){
            if(empty($queryInput)) {
                parseBySemiColon($queryInputRaw);
            } else {
                parseBySemiColon($queryInput);
            }
        }

    } else {
        $queryInput[0] = $queryInputRaw;
    }

    //assembly of multiple queries into a single string


    //complete query
    $query = "SELECT * FROM RecipeTable WHERE ".$columnInput." LIKE '".$queryInput."'";
    $tableData[0]['queryComplete'] = $query;

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
            }else{
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
    }else{
        $tableData[0]['errorMsg'] = '404';
        return($tableData);
    }

};

/*Credit to Metehanarslan from php.net for code*/
function multiexplode ($delimiters,$string) {
    $ready = str_replace($delimiters, $delimiters[0], $string);
    $launch = explode($delimiters[0], $ready);
    return  $launch;
}

/*Credit to StackExchange users Victor and Christian Alexandru*/
$aResult = array();

if( !isset($_POST['functionname']) ) { $aResult['error'] = 'No function name!'; }

if( !isset($_POST['arguments']) ) { $aResult['error'] = 'No function arguments!'; }

if( !isset($aResult['error']) ) {

    switch($_POST['functionname']) {
        case 'searchFunction':
            if( !is_array($_POST['arguments']) || (count($_POST['arguments']) < 1) ) {
                $aResult['error'] = 'Error in arguments!';
            }
            else {
                $aResult['result'] = searchFunction($_POST['arguments'][0]);
            }
            break;

        default:
            $aResult['error'] = 'Not found function '.$_POST['functionname'].'!';
            break;
    }

}

echo json_encode($aResult);
