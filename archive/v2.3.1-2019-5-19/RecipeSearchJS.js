/*
 Todo:  -maybe? replace ajax with fetch command
        -table clipping occurring on repeated searches - clear previous tables
        -detect timeout and stop loading icon/notify user
 */


//RecipeSearch JavaScript
let result, rsString, rsParsed, query, i, c, tableData, loading, metaInfo,
    currentState, searchErrorText, parameters, searchCount, textFieldValue;
let count = 0;
let columnID = 0;
let recipeDataArray = [];
let error = false;
let errorText = '';
let direction = ['DESC','ASC','ASC','ASC','ASC','DESC','ASC','DESC','DESC'];
let orderData = 'RecipeTitle:ASC';
let columnTitle = ['RecipeTitle','Course','Source','MainIngredient','Region','DateCreated','Category','LastOnMenu','OnMenu'];

function readURLnUpdate() {
    let hash = window.location.hash;
    if (hash !== '') {
        hash = hash.replace('#', '');
        textFieldValue = document.getElementById('textField').value;
        textFieldValue = hash;
        initiateSearch();
    }
}

function updateURLParameter() {
    let text = document.getElementById('textField').value;
    let href = location.protocol + '//' + location.host + location.pathname;
    window.location.href = href + '#' + text;
}

function initiateSearch() {
    textFieldValue = document.getElementById("textField").value;
    let chkbx0 = document.getElementById('strictParse').checked;
    let chkbx1 = document.getElementById('forceExact').checked;
    let chkbx2 = document.getElementById('carryOrderData').checked;
    parameters = [chkbx0, chkbx1, chkbx2];
    searchDB(textFieldValue, 'searchFunction');
    updateURLParameter(textFieldValue);
    setLoadingAnimation(true);
    changeDisplayStyle(3);
    revealOptions('none');
}

function sortTable(callID){
    columnID = callID;
    let column = columnTitle[columnID];
    orderData = column + ':' + direction[columnID];
    initiateSearch();
    setLoadingAnimation(true);
    if(direction[columnID] === 'ASC'){
        direction[columnID] = 'DESC';
    } else if(direction[columnID] === 'DESC') {
        direction[columnID] = 'ASC';
    }
}

function updateSortDisplay() {
    let headers = document.querySelectorAll('metaTableHeader');
    //remove all borders
    headers.forEach(element =>{
        if(headers.classList.contains('sortBorderBottom')){
            headers.classList.remove('sortBorderBottom');
        }
        if(headers.classList.contains('sortBorderTop')){
            headers.classList.remove('sortBorderTop');
        }
    });

    //add specific border
    let header = document.getElementsByClassName('metaTableHeader')[columnID];
    if(direction[columnID] === 'ASC') {
        header.classList.add('sortBorderTop');
    }
    if(direction[columnID] === 'DESC') {
        header.classList.add('sortBorderBottom');
    }
}

function searchDB(query, functionRequest) {
    $.ajax({
        type: "POST",
        url: 'RecipeSearchPHP.php',
        dataType: 'json',
        data: {function: functionRequest, arguments: [query, orderData, parameters]},
        statusCode: {
            500: function() {
                errorText = ' 500 - Internal Server Error ';
                changeDisplayStyle(2);
                setLoadingAnimation(false);
            },
            404: function() {
                errorText = ' 404 - Database Not Found ';
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
    count = rsParsed.length - 1;
    for (i = 0; i < count; i++) {
        recipeDataArray[i] = rsParsed[i + 1];
    }
    searchCount = recipeDataArray.length;
    displayResults();
    setLoadingAnimation(false);
}

function displayResults(){
    //report invalid columns
    if(Array.isArray(metaInfo.colErr) && metaInfo.colErr[0] !== undefined){
        document.getElementById('searchError').innerHTML = '';
        document.getElementById('searchError').style.display = 'none';
        let countC = metaInfo.colErr.length;
        if(countC === 1){
            searchErrorText = 'Invalid Column: "' + metaInfo.colErr[0] + '"';
        } else if(countC === 2) {
            searchErrorText = 'Invalid Columns: ' + metaInfo.colErr[0] + ' and ' + metaInfo.colErr[1];
        } else if(countC > 2) {
            searchErrorText = 'Invalid Columns: ';
            for(i = 0; i < countC; i++){
                if(i + 1 >= countC){
                    searchErrorText += 'and "' + metaInfo.colErr[i] + '"';
                } else {
                    searchErrorText += '"' + metaInfo.colErr[i] + '", ';
                }
            }
        } else {
            console.log('colErr encountered invalid count: ' + countC);
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

    //clear all html from table area first
    let tableArea = document.getElementById('recipeData');
    while (tableArea.firstChild) {
        console.log(tableArea.firstChild);
        tableArea.removeChild(tableArea.firstChild);
    }
    //check for empty result sets
    let allEmpty = false;
    let emptyCount = 0;
    for (i = 0; i < searchCount; i++) {
        if (Number(recipeDataArray[i][0].numResults) <= 0) {
            emptyCount++;
        }
    }
    if (emptyCount === searchCount) {allEmpty = true;}
    //generate table(s)
    if (allEmpty === false) {
        let tablePost = '<table class="recipeData">' +
            '<th class="metaTableHeader" onclick="sortTable(0)">Title</th>' +
            '<th class="metaTableHeader" onclick="sortTable(1)" >Course</th>' +
            '<th class="metaTableHeader" onclick="sortTable(2)" >Source</th>' +
            '<th class="metaTableHeader" onclick="sortTable(3)" >Main Ingredient</th>' +
            '<th class="metaTableHeader" onclick="sortTable(4)" >Region</th>' +
            '<th class="metaTableHeader" onclick="sortTable(5)" >Date Added</th>' +
            '<th class="metaTableHeader" onclick="sortTable(6)" >Category</th>' +
            '<th class="metaTableHeader" onclick="sortTable(7)" >Last on Menu</th>' +
            '<th class="metaTableHeader" onclick="sortTable(8)" >On Menu</th>';
        let resultNum = 0;
        for (c = 0; c < searchCount; c++) {
            resultNum = Number(recipeDataArray[c][0].numResults) + resultNum;
            if (!(Number(recipeDataArray[c][0].numResults) <= 0)) {
                if (c !== 0) {
                    tablePost += '<tr><td colspan="9" class="tableDivider">Search for "' + recipeDataArray[c][0].content + '"</td>';
                }
                tablePost += generateTable(recipeDataArray[c]);
            }
        }
        document.getElementById('resultNum').innerHTML = resultNum.toString();
        document.getElementById('recipeData').innerHTML = tablePost + '</table>';
        updateSortDisplay();
        changeDisplayStyle(0);
    } else {
        changeDisplayStyle(1);
    }
    setLoadingAnimation(false);
}

function generateTable(recipeData){
    tableData = '';
    for(i = 1; i <= recipeData[0].numResults; i++) {
        tableData += '<tr><td class="title"><a href="DisplayRecipe.php?id=' + recipeData[i].RecipeID + '">' + recipeData[i].RecipeTitle + '</a></td>' +
            '<td>' + recipeData[i].Course + '</td>';
        if (recipeData[i].URL !== 'false') {
            tableData += '<td><a href="' + recipeData[i].URL + '">' + recipeData[i].Source + '</a></td>';
        } else {
            tableData += '<td>' + recipeData[i].Source + '</td>';
        }
        tableData += '<td>' + recipeData[i].MainIngredient + '</td>' +
            '<td>' + recipeData[i].Region + '</td>' +
            '<td>' + recipeData[i].DateCreated + '</td>' +
            '<td>' + recipeData[i].Category + '</td>';
        if (recipeData[i].LastonMenu !== '') {
            tableData += '<td><a href="../MenuDB/InputMenuDay.php?MenuDate=' + recipeData[i].LastOnMenu + '">' + recipeData[i].LastOnMenu + '</a></td>';
        } else {
            tableData += '<td>' + recipeData[i].LastOnMenu + '</td>';
        }
        tableData += '<td style="text-align: center">' + recipeData[i].OnMenu + '</td>';
    }
    return tableData;
}

function changeDisplayStyle(toggle){
    switch (toggle) {
        case(0):
            document.getElementById('searchInfo').style.display = 'block';
            document.getElementById('results').style.display = 'block';
            document.getElementById('error').style.display = 'none';
            break;
        case(1):
            document.getElementById('searchInfo').style.display = 'block';
            document.getElementById('results').style.display = 'none';
            document.getElementById('error').style.display = 'block';
            document.getElementById('searchData').style.display = 'none';
            document.getElementById('displayError').innerHTML = ' No recipes found - Please retry search ';
            break;
        case(2):
            document.getElementById('results').style.display = 'none';
            document.getElementById('error').style.display = 'block';
            document.getElementById('displayError').innerHTML += 'Error: ' + errorText;
            break;
        case(3):
            document.getElementById('search').style.marginTop = '0';
            document.getElementById('searchHeader').classList.remove('searchHeaderLarge');
            break;
        case(4):
            document.getElementById('search').style.marginTop = '7em';
            document.getElementById('searchHeader').classList.add('searchHeaderLarge');
            break;
        case(5):
            document.getElementById('results').style.display = 'none';
            document.getElementById('error').style.display = 'none';
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

function revealOptions(forceValue){
    currentState = document.getElementById('optionPanel').style.display;
    if(forceValue === undefined) {
        if(currentState === 'block') {
            document.getElementById('optionPanel').style.display = 'none';
        } else {
            document.getElementById('optionPanel').style.display = 'block';
        }
    } else {
        if(forceValue === 'block' || forceValue === 'none'){
            document.getElementById('optionPanel').style.display = forceValue;
        } else {
            console.log('revealOptions bad parameter: ' + forceValue);
        }
    }
}