/*
 Todo:  -maybe? replace ajax with fetch command
 */

let result, rsString, rsParsed, i, tableData, metaInfo,
    recipeData, count, searchErrorText, textFieldValue;
let error = false;
let errorText = '';
let direction = ['DSC','ASC','ASC','ASC','ASC','ASC','ASC','ASC'];
let orderData = 'RecipeTitle:ASC';
let columnTitle = ['recipeTitle','Course','Source','MainIngredient','Region','DateCreated','Category','LastOnMenu','OnMenu'];

function initiateSearch(){
    textFieldValue = document.getElementById("textField").value;
    searchDB(textFieldValue, 'searchFunction');
    setLoadingAnimation(true);
    changeDisplayStyle(3);
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

function searchDB(query, functionRequest) {
    $.ajax({
        type: "POST",
        url: 'RecipeSearchPHP.php',
        dataType: 'json',
        data: {function: functionRequest, arguments: [query, orderData]},
        statusCode: {
            500: function() {
                errorText = '500 - Internal Server Error';
                changeDisplayStyle(2);
                setLoadingAnimation(false);
            },
            404: function() {
                errorText = '404 - Database Not Found';
                changeDisplayStyle(2);
                setLoadingAnimation(false);
            }
        },

        success: function (obj) {
            if (!('error' in obj)) {
                result = obj.result;
                prepareJSON(result);
            } else {
                console.log(obj.error);
            }
        }
    })
}

function prepareJSON(jsonInput){
    rsString = JSON.stringify(jsonInput);
    rsParsed = JSON.parse(rsString);
    metaInfo = rsParsed[0];
    recipeData = rsParsed[1];
    displayResults();
    setLoadingAnimation(false);
}

function displayResults(){
    if(Array.isArray(metaInfo.colErr) && metaInfo.colErr[0] !== undefined){
        document.getElementById('searchError').innerHTML = '';
        document.getElementById('searchError').style.display = 'none';
        count = metaInfo.colErr.length;
        if(count === 1){
            searchErrorText = 'Invalid Column: "' + metaInfo.colErr[0] + '"';
        } else if(count === 2) {
            searchErrorText = 'Invalid Columns: ' + metaInfo.colErr[0] + ' and ' + metaInfo.colErr[1];
        } else if(count > 2) {
            searchErrorText = 'Invalid Columns: ';
            for(i = 0; i < count; i++){
                if(i + 1 >= count){
                    searchErrorText += 'and "' + metaInfo.colErr[i] + '"';
                } else {
                    searchErrorText += '"' + metaInfo.colErr[i] + '", ';
                }
            }
        } else {
            console.log('colErr encountered invalid count: ' + count);
            error = true;
        }
        if(error === false){
            searchErrorText += ' --> Replacing all instances with "RecipeTitle"';
            document.getElementById('searchError').innerHTML = searchErrorText;
            document.getElementById('searchError').style.display = 'block';
            error = false;
        }
    } else {
        document.getElementById('searchError').style.display = 'none';
    }
    if(recipeData[0].numResults === '0' || recipeData[0].numResults == null){
        changeDisplayStyle(1);
        setLoadingAnimation(false);
    } else {
        changeDisplayStyle(0);
        document.getElementById('resultNum').innerHTML = recipeData[0].numResults;
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
        for(i = 1; i <= recipeData[0].numResults; i++) {
             tableData += '<tr><td class="title"><a href="DisplayRecipe.php?id=' + recipeData[i].RecipeID + '"</a>' + recipeData[i].RecipeTitle + '</td>' +
                 '<td>' + recipeData[i].Course + '</td>' +
                 '<td>' + recipeData[i].Source + '</td>' +
                 '<td>' + recipeData[i].MainIngredient + '</td>' +
                 '<td>' + recipeData[i].Region + '</td>' +
                 '<td>' + recipeData[i].DateCreated + '</td>' +
                 '<td>' + recipeData[i].Category + '</td>' +
                 '<td>' + recipeData[i].LastOnMenu + '</td>' +
                 '<td style="text-align: center">' + recipeData[i].OnMenu + '</td>';
        }
        document.getElementById('recipeData').innerHTML = tableData + '</table>';
        setLoadingAnimation(false);
    }
}

function changeDisplayStyle(toggle){
    switch (toggle) {
        case(0):
            document.getElementById('results').style.display = 'block';
            document.getElementById('error').style.display = 'none';
            break;
        case(1):
            document.getElementById('results').style.display = 'none';
            document.getElementById('error').style.display = 'block';
            document.getElementById('displayError').innerHTML = 'No recipes found - Please retry search';
            break;
        case(2):
            document.getElementById('results').style.display = 'none';
            document.getElementById('error').style.display = 'block';
            document.getElementById('displayError').innerHTML += 'Error: ' + errorText;
            break;
        case(3):
            document.getElementById('search').style.marginTop = '0';
            document.getElementById('searchHeader').style.fontSize = '1.6em';
            document.getElementById('searchHeader').style.padding = '0';
            break;
        case(4):
            document.getElementById('search').style.marginTop = '7em';
            document.getElementById('searchHeader').style.fontSize = '2.8em';
            document.getElementById('searchHeader').style.padding = '5px';
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

