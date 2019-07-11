<?php /** @noinspection SqlResolve */
/*
 User: Baldur.Siegel
 Date: 2/20/2019
 Time: 7:54 PM
 User: Baldur.Siegel
 Date: 3/29/2019
 Time: 5:24 PM

 TODO:  -autocomplete!!!
        -report bad syntax back to user
        -translate query across languages (EN - DE)
        -parse out incorrect/extra symbols (i.e. '::')
        -correct incorrectly spelled words
        -add 'exact' function
        -add inclusion parameters ("apple and potato" searches for 'apple and potato')
        -optimise query column selection (only use needed columns)

 v2.1: 2019-4-5:
    general code restructuring, parsing is now split into logical groups,
    and passes information (i.e. errors) back as return. query execution is
    its own function, allow multiple queries to be executed in one go
    -added error, column, keyword response
    -changed query execution into function

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

function searchFunction($userQuery, $orderData, $parameters){
    $resultData[0]['Signature'] = 42;
    /*$pspell_link = pspell_new("en");*/

    //Parameter Declaration
    $forceStrict = $parameters[0];
    $forceExact = $parameters[1];
    $localOrder = $parameters[2];

    if($localOrder === 'true'){
        if(strpos($userQuery, ';') !== false){
            $explodeResults = explode(';', $userQuery);
            $userQuery = $explodeResults[0];
            $orderData = str_replace(' ', '', $explodeResults[1]);
        } else {
            $resultData[0]['orderError'] = 'Order Separator Delimiter Not Found';
        }
    }

    $parseData = parseQueryStrict($userQuery, $orderData);
    $query0 = $parseData[0];
    if($forceExact === 'true'){
        $resultData[0]['Trim Exact'] = true;
        $query0 = str_replace($query0, "%", "");
    }
    $columns = $parseData[1];
    $keywords = $parseData[2];
    $colError = $parseData[3];

    $resultData[0]['Initial Query Parse'] = $query0;
    $resultData[0]['Initial Columns'] = $columns;
    $resultData[0]['Initial Keywords'] = $keywords;
    $resultData[0]['colErr'] = $colError;
    $resultData[0]['Parameters'] = $parameters;
    $resultData[0]['Input'] = $userQuery;

    //Dynamic Query Selection/Execution
    $tableData = executeQuery($query0);
    $tableData[0]['content'] = 'original';
    if($tableData[0]['errorMsg'] === '404' && $forceStrict !== 'true'){
        $softData = parseQuerySoft($userQuery, $orderData);
        $querySet = $softData[0];
        $contentSet = $softData[1];
        $queryCount = count($querySet);
        for($i = 0; $i < $queryCount; $i++){
            $resultData[0]['softQueries'][$i] = $querySet[$i];
            $softTableData = executeQuery($querySet[$i]);
            $softTableData[0]['content'] = $contentSet[$i];
            $e = $i + 1;
            $resultData[$e] = $softTableData;
        }
        $resultData[0]['softExecution'] = true;
    } else {
        $resultData[0]['softExecution'] = false;
        $resultData[1] = $tableData;
    }

    return $resultData;
}

function parseQueryStrict($stringInput, $orderData){
    if(preg_match('#(all)#i', $stringInput) === 1){
        $query = 'SELECT *'.' FROM RecipeTable';
        $returnArray = array($query, '', '', '');
        return $returnArray;
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
                    $keywords[$keywordCount] = $queryParse[$i];
                    $keywordCount++;
                    $columns[$columnCount] = $colInputRaw;
                    $columnCount++;
                    if($i === 0){
                        $subsection .= "(".$colInput." LIKE '%".$queryParse[$i]."%'";
                    } else if($i + 1 >= $queryCount){
                        $subsection .= " AND ".$colInput." LIKE '%".$queryParse[$i]."%')";
                    } else {
                        $subsection .= " AND ".$colInput." LIKE '%".$queryParse[$i]."%'";
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
            $subsection = $colInput." LIKE '%".$queryParse."%'";
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

function parseQuerySoft($stringInput, $orderData){
    $validColumns = array('RecipeID', 'RecipeTitle', 'Course', 'URL', 'Source',
        'MainIngredient', 'Region', 'Category', 'DateCreated', 'LastOnMenu',
        'OnMenu', 'DateModified', 'Ingedients', 'IngredientsContinued', 'Instructions',
        'Notes', 'Servings', 'Special', 'Rating', 'PrepTime', 'CookTime', 'Wine', 'Language',);
    $queryHead = 'SELECT *'.' FROM RecipeTable WHERE ';
    $query = 'none';
    $columns = array();
    $queries = array();
    $querySet = array();

    //parse orderData
    $explodeResults = explode(':', $orderData);
    $dataOrder = ' ORDER BY '.$explodeResults[0].' '.$explodeResults[1];

    //parse out quotes and apostrophes
    if(strpos($stringInput, '"') !== false || strpos($stringInput, "'") !== false){
        if(strpos($stringInput, '"') !== false){
            str_replace($stringInput, '"', '');
        }
        if(strpos($stringInput, "'") !== false){
            str_replace($stringInput, "'", '');
        }
    }
    //generate user intact query
    $queries[0] = $stringInput;

    $inputSeparated = explode(' ', $stringInput);
    $count = count($inputSeparated);
    for($i = 0; $i < $count; $i++){
        if(in_array($inputSeparated[$i], $validColumns)){
            $pointer = count($columns);
            $columns[$pointer] = $inputSeparated[$i];
        } else {
            $pointer = count($queries);
            $queries[$pointer] = $inputSeparated[$i];
        }
    }
    $colCount = count($columns);
    $queryCount = count($queries);
    for($i = 0; $i < $queryCount; $i++){
        $querySet[$i] = $queryHead." RecipeTitle LIKE '%".$queries[$i]."%'".$dataOrder;
    }
    $returnArray = array($querySet, $queries);
    return $returnArray;
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
            if (substr($URL, 0,7)=='http://'){
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

            ++$i;
        }
        $tableData[0]['errorMsg'] = 'none';
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
                $aResult['result'] = searchFunction($_POST['arguments'][0], $_POST['arguments'][1], $_POST['arguments'][2]);
            }
            break;

        default:
            $aResult['error'] = 'Not found function '.$_POST['function'].'!';
            break;
    }

}

echo json_encode($aResult);