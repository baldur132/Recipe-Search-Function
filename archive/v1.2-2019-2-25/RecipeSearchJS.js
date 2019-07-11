//Result presence dictated style elements

var result, rsString, rsParsed, query, i, tableData;
var errorText = '';
var loading;

function initiateSearch(){
    var textFieldValue = document.getElementById("textField").value;
    searchDB(textFieldValue);
    toggleDisplayStyle();
    setLoadingAnimation(true);
}

function searchDB(query){
    //credit to StackExchange users Victor and Christian Alexandru
    $.ajax({
        type: "POST",
        url: 'RecipeSearchPHP.php',
        dataType: 'json',
        data: {functionname: 'searchFunction', arguments: [query]},

        success: function (obj, textstatus) {
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

function displayResults(){
    //setLoadingAnimation(false);
    if(rsParsed[0].errorBadCol == 'true'){
        searchErrorText = "Could not identify column name; Searching instead for '" + rsParsed[0].queryInput + "'";
        document.getElementById('searchError').innerHTML = searchErrorText;
        document.getElementById('searchError').style.display = 'block';
    } else {
        document.getElementById('searchError').style.display = 'none';
    }
    if(rsParsed[0].numResults === '0' || rsParsed[0].numResults == null){
        errorText = result[0];
        toggleDisplayStyle(false);
    } else {
        toggleDisplayStyle(true);
        document.getElementById('resultNum').innerHTML = rsParsed[0].numResults;
        tableData = '<table><th>Title</th>' +
                '<th>Course</th>' +
                '<th>Source</th>' +
                '<th>Main Ingredient</th>' +
                '<th>Region</th>' +
                '<th>Date Added</th>' +
                '<th>Category</th>' +
                '<th>Last on Menu</th>' +
                '<th>On Menu</th>'
        for(i = 1; i <= rsParsed[0].numResults; i++) {
             tableData += '<tr><td><a href="DisplayRecipe.php?id=' + rsParsed[i].RecipeID + '"</a>' + rsParsed[i].RecipeTitle + '</td>' +
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
                document.getElementById('error').style.textAlign = 'center';
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
};

function setLoadingAnimation(toggle){
    if(toggle === true){
        document.getElementById('loadingIcon').style.display = 'block';
    } else {
        document.getElementById('loadingIcon').style.display = 'none';
    }
}