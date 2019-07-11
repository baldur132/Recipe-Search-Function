/*
 Todo:  -add sort functionality for tables->make separate ordered query
        -maybe? replace ajax with fetch command
 */

let result, rsString, rsParsed, query, i, tableData, loading, textFieldValue;
let errorText = '';
let direction = ['ASC','ASC','ASC','ASC','ASC','ASC','ASC','ASC'];
let orderData = 'RecipeTitle:ASC';
let columnTitle = ['recipeTitle','Course','Source','MainIngredient','Region','DateCreated','Category','LastOnMenu','OnMenu'];

function initiateSearch(){
    textFieldValue = document.getElementById("textField").value;
    searchDB(textFieldValue, 'searchFunction');
    testPHP(textFieldValue, 'searchFunction');
    setLoadingAnimation(true);
}

function sortTable(columnID){
    let column = columnTitle[columnID];
    orderData = column + ':' + direction[columnID];
    searchDB(textFieldValue, 'searchFunction');
    setLoadingAnimation(true);
    if(direction[columnID] === 'ASC'){
        direction[columnID] = 'DESC';
    } else if(direction[columnID] === 'DESC') {
        direction[columnID] = 'ASC';
    }
}

function searchDB(query, functionRequest){
    //credit to StackExchange users Victor and Christian Alexandru
    $.ajax({
        type: "POST",
        url: 'RecipeSearchPHP.php',
        dataType: 'json',
        data: {function: functionRequest, arguments: [query, orderData]},

        error: function (jqXHR){
            internalServerErr(jqXHR);
        },
        success: function (obj) {
            if( !('error' in obj) ) {
                result = obj.result;
                rsString = JSON.stringify(result);
                rsParsed = JSON.parse(rsString);
                displayResults();
            }
            else {
                console.log(obj.error);
            }
        }
    });
}

function testPHP(query, functionRequest){
    //credit to StackExchange users Victor and Christian Alexandru
    $.ajax({
        type: "POST",
        url: 'RecipeSearchPHPRewrite.php',
        dataType: 'json',
        data: {function: functionRequest, arguments: [query, orderData]},

        error: function (jqXHR){
            internalServerErr(jqXHR);
        },
        success: function (obj) {
            if( !('error' in obj) ) {
                result = obj.result;
            }
            else {
                console.log(obj.error);
            }
        }
    });
}

function internalServerErr(errCode){
    switch(errCode) {
        case(500):
            errorText = '500 - Internal Server Error';
            toggleDisplayStyle(false);
            setLoadingAnimation(false);
            break;
        default:
            errorText = 'Error: ' + errCode;
    }
}

function displayResults(){
    //setLoadingAnimation(false);
    /*if(rsParsed[0].badCol[0] !== false || rsParsed[0].badCol[0] == null){
        searchErrorText = "Could not identify column name(s): ; Replacing with 'RecipeTitle'";
        document.getElementById('searchError').innerHTML = searchErrorText;
        document.getElementById('searchError').style.display = 'block';
    }*/
    if(rsParsed[0].numResults === '0' || rsParsed[0].numResults == null){
        errorText = result[0];
        toggleDisplayStyle(false);
        setLoadingAnimation(false);
    } else {
        toggleDisplayStyle(true);
        document.getElementById('resultNum').innerHTML = rsParsed[0].numResults;
        tableData = '<table class="recipeData">' +
                '<th onclick="sortTable(0)">Title</th>' +
                '<th onclick="sortTable(1)" >Course</th>' +
                '<th onclick="sortTable(2)" >Source</th>' +
                '<th onclick="sortTable(3)" >Main Ingredient</th>' +
                '<th onclick="sortTable(4)" >Region</th>' +
                '<th onclick="sortTable(5)" >Date Added</th>' +
                '<th onclick="sortTable(6)" >Category</th>' +
                '<th onclick="sortTable(7)" >Last on Menu</th>' +
                '<th onclick="sortTable(8)" >On Menu</th>';
        for(i = 1; i <= rsParsed[0].numResults; i++) {
             tableData += '<tr><td class="title"><a href="DisplayRecipe.php?id=' + rsParsed[i].RecipeID + '"</a>' + rsParsed[i].RecipeTitle + '</td>' +
                 '<td>' + rsParsed[i].Course + '</td>' +
                 '<td>' + rsParsed[i].Source + '</td>' +
                 '<td>' + rsParsed[i].MainIngredient + '</td>' +
                 '<td>' + rsParsed[i].Region + '</td>' +
                 '<td>' + rsParsed[i].DateCreated + '</td>' +
                 '<td>' + rsParsed[i].Category + '</td>' +
                 '<td>' + rsParsed[i].LastOnMenu + '</td>' +
                 '<td style="text-align: center">' + rsParsed[i].OnMenu + '</td>';
        }
        document.getElementById('recipeData').innerHTML = tableData + '</table>';
        setLoadingAnimation(false);
    }
}

function toggleDisplayStyle(toggle){
    switch (toggle) {
        case(true):
            document.getElementById('results').style.display = 'block';
            document.getElementById('error').style.display = 'none';
            break;
        case(false):
            if(rsParsed[0].errorMsg === '404'){
                document.getElementById('results').style.display = 'none';
                document.getElementById('error').style.display = 'block';
                document.getElementById('displayError').innerHTML = 'No recipes found - Please retry search';
            } else {
                document.getElementById('results').style.display = 'none';
                document.getElementById('error').style.display = 'block';
                document.getElementById('displayError').innerHTML += 'Error: ' + errorText;
            }
            break;
        default:
            document.getElementById('results').style.display = 'none';
    }
}

function setLoadingAnimation(toggle){
    switch(toggle){
        case(true):
            document.getElementById('loadingIcon').style.display = 'inline';
            break;
        case(false):
            document.getElementById('loadingIcon').style.display = 'none';
            break;
        default:
            document.getElementById('loadingIcon').style.display = 'none';
    }
}

