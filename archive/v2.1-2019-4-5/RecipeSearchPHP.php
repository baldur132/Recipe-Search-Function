<?php
/*
 User: Baldur.Siegel
 Date: 2/20/2019
 Time: 7:54 PM

 TODO:  -make multiple searches for same term
        -report bad syntax back to user
        -translate query across languages (EN - DE)
        -parse out incorrect/extra symbols (i.e. '::')
        -correct incorrectly spelled words
        -add 'not' and 'exact' functions
        -add inclusion parameters ("apple and potato" searches for 'apple and potato')
        -optimise query column selection (only use needed columns)

 v1.4: 2019-3-18:
    code optimisation, prepared for export to SiegelServer for testing, to
    be moved to hosted server as initial replacement for old search function
    -added column error checking

 v1.3: 2019-3-2:
    code based on a hierarchical function structure correctly parses input
    strings into SQL queries, based on input delimiters ',',':','and','or'
    -parameters in search query removed

 v1.2: 2019-2-25:
    if structure based parsing system replaced by a first attempt at a
    function based parsing system, using containers as a storage for
    already parsed pieces of the input string, to be assembled together
    after everything has been parsed and stored

  v1.1: 2019-21-2:
    basic functionality achieved, can receive a search term and preform
    a basic fuzzy matching query against server, and return it as a JSON object
 */

function searchFunction($queryInputRaw, $orderData){
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

    /*$pspell_link = pspell_new("en");*/
    $tableData = array();

    //parse orderData
    $explodeResults = explode(':', $orderData);
    $dataOrder = ' ORDER BY '.$explodeResults[0].' '.$explodeResults[1];

    function parseAndAssembleQuery($stringInput) {
        if (preg_match('#(all)#i', $stringInput) === 1){
            $query = 'SELECT * '.'FROM RecipeTable';
            return $query;
        }
        $query = parseByAndOr($stringInput);
        return $query;
    }

    function parseByAndOr($stringInput){
        $queryHead = 'SELECT * FROM'.' RecipeTable WHERE ';
        $errorLog = array(false);
        if (preg_match('#\b(and|or)\b#i', $stringInput) === 1) {
            //checks for or, runs if present
            if (preg_match('#\b(or)\b#i', $stringInput) === 1) {
                str_replace(' ', '', $stringInput);
                $separationResults = preg_split('#\b(or)\b#i', $stringInput);
                $divisionCount = count($separationResults);
                $queryDivision = array();
                //runs for same amount of number of divisions, generates $queryDivision
                for ($i = 0; $i < $divisionCount; $i++){
                    //checks for presence of and, and runs accordingly
                    if (preg_match('#\b(and)\b#i', $separationResults[$i]) === 1) {
                        $splitResults = preg_split('#\b(and)\b#i', $separationResults[$i]);
                        $sectionCount = count($splitResults);
                        $sectionCount = $sectionCount -1;
                        $querySectionString = '(';
                        for ($e = 0; $e < $sectionCount; $e++){
                            $querySection = parseByColon($splitResults[$e]);
                            $querySectionString .= $querySection.' AND ';
                        }
                        $sectionLast = parseByColon($splitResults[$sectionCount]);
                        $queryDivision[$i] = $querySectionString.$sectionLast.')';
                    } else {
                        $querySection = parseByColon($separationResults[$i]);
                        $queryDivision[$i] = '('.$querySection.')';
                    }
                }
                $divisionCount1 = count($queryDivision);
                $divisionCount1 = $divisionCount1 -1;
                $queryDivisionJoined = $queryHead;
                for ($i = 0; $i < $divisionCount1; $i++){
                    $queryDivisionJoined .= $queryDivision[$i].' OR ';
                }
                $queryDivisionLast = $queryDivision[$divisionCount1];
                $query = $queryDivisionJoined.$queryDivisionLast;
                return $query;
            } else if (preg_match('#\b(and)\b#i', $stringInput) === 1) {
                str_replace(' ', '', $stringInput);
                $splitResults = preg_split('#\b(and)\b#i', $stringInput);
                $sectionCount = count($splitResults);
                $sectionCount = $sectionCount -1;
                $querySectionString = $queryHead;
                for ($i = 0; $i < $sectionCount; $i++){
                    $parseResult = parseByColon($splitResults[$i]);
                    list($querySection, $errorNew) = $parseResult;
                    $errorLog = array_merge($errorLog, $errorNew);
                    $querySectionString .= $querySection.' AND ';
                }
                $parseResLast = parseByColon($splitResults[$sectionCount]);
                list($sectionLast, $errorNew) = $parseResLast;
                $errorLog = array_merge($errorLog, $errorNew);
                $query = $querySectionString.$sectionLast;
                $returnArray = array($query, $errorLog);
                return $returnArray;
            } else {
                return '';
            }
        } else {
            str_replace(' ', '', $stringInput);
            $parseResult = parseByColon($stringInput);
            list($querySection, $errorLog) = $parseResult;
            $query = $queryHead . $querySection;
            $returnArray = array($query, $errorLog);
            return $returnArray;
        }
    }

    function parseByColon($stringInput){
        $errorLog = false;
        $validColumns = array('RecipeID', 'RecipeTitle', 'Course', 'URL', 'Source',
            'MainIngredient', 'Region', 'Category', 'DateCreated', 'LastOnMenu',
            'OnMenu', 'DateModified', 'Ingedients', 'IngredientsContinued', 'Instructions',
            'Notes', 'Servings', 'Special', 'Rating', 'PrepTime', 'CookTime', 'Wine', 'Language',);
        if(strpos($stringInput, ':') !== false){
            $explodeResults = explode(':', $stringInput);
            $colResult = str_replace(' ', '', $explodeResults[0]);
            $queryOutput = $explodeResults[1];
            if (in_array($colResult, $validColumns)) {
                $colInput = $colResult;
            } else {
                $colInput = 'RecipeTitle';
                $errorLog = $colResult;
            }
            $queryParse = parseByComma($queryOutput);
        } else {
            $queryParse = parseByComma($stringInput);
            $colInput = 'RecipeTitle';
        }
        if (is_array($queryParse)){
            $queryCount = count($queryParse);
            $queryCount = $queryCount -1;
            if ($queryCount > 1) {
                $querySection = "(" . $colInput . " LIKE '" . $queryParse[0] . "%'";
                for ($i = 1; $i < $queryCount; $i++) {
                    $querySection .= " AND ".$colInput." LIKE '".$queryParse[$i]."%'";
                }
                $querySection .= " AND ".$colInput." LIKE '".$queryParse[$queryCount]."%')";
            } else {
                $querySection = "(".$colInput." LIKE '".$queryParse[0]."%' AND ".$colInput." LIKE '".$queryParse[1]."%')";
            }
        } else {
            $querySection = $colInput." LIKE '".$queryParse."%'";
        }
        $returnArray = array($querySection, $errorLog);
        return $returnArray;
    }

    function parseByComma($stringInput){
        if (strpos($stringInput, ',') !== false){
            $explodeResults = explode(',', $stringInput);
            $queryCount = count($explodeResults);
            $querySubsection = array();
            for ($i = 0; $i < $queryCount; $i++){
                $querySubsection[$i] = str_replace(' ', '', $explodeResults[$i]);
            }
            return $querySubsection;
        } else {
            $query = str_replace(' ', '', $stringInput);
            return $query;
            /*$queryWord = str_replace(' ', '', $stringInput);
            $wordCorrect = checkSpelling($queryWord);
            if ($wordCorrect === false){
                return $queryWord;
            } else {
                return $wordCorrect;
            }*/
        }
    }

    /*function checkSpelling($wordInput){
        global $pspell_link;
        if (!pspell_check($pspell_link, $wordInput)) {
            $suggestions = pspell_suggest($pspell_link, $wordInput);
            $suggestion = $suggestions[0];
            return $suggestion;
        } else {
            return false;
        }
    }*/

    //complete query
    $queryParse = parseAndAssembleQuery($queryInputRaw);
    list($query, $errorLog) = $queryParse;
    //$tableData[0]['parseError'][0] = $errorLog;
    $errorCount = count($errorLog);
    $tableData[0]['errorCount'] = $errorCount;
    $query = $query.$dataOrder;
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
