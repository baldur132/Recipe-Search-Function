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
let chkbx0 = document.getElementById('strictParse');
let chkbx1 = document.getElementById('forceExact');
let chkbx2 = document.getElementById('carryOrderData');
let direction = ['DESC','ASC','ASC','ASC','ASC','DESC','ASC','DESC','DESC'];
let orderData = 'RecipeTitle:ASC';
let columnTitle = ['RecipeTitle','Course','Source','MainIngredient','Region','DateCreated','Category','LastOnMenu','OnMenu'];

function readURLnUpdate() {
    let hash = window.location.hash;
    console.log('hash: "' + hash + '"');
    if (hash) {
        hash = hash.replace('#', '');
        textFieldValue = document.getElementById('textField');
        textFieldValue.value = hash;
        initiateSearch();
    }
}

function updateURLParameter() {
    let text = document.getElementById('textField').value;
    if (text) {
        let href = location.protocol + '//' + location.host + location.pathname;
        window.location.href = href + '#' + text;
    }
}

function initiateSearch() {
    textFieldValue = document.getElementById("textField").value;
    parameters = [chkbx0.checked, chkbx1.checked, chkbx2.checked];
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
    //clear recipeData array
    recipeDataArray.length = 0;
    count = rsParsed.length - 1;
    for (i = 0; i < count; i++) {
        recipeDataArray[i] = rsParsed[i + 1];
    }
    searchCount = recipeDataArray.length;
    displayResults();
    setLoadingAnimation(false);
}

function displayResults(){
    //clear error area
    document.getElementById('searchError').style.display = 'none';
    let errorArea = document.getElementById('searchError');
    let count = document.getElementById('searchError').childNodes.length;
    for (i = 0; i < count; i++) {
        errorArea.removeChild(errorArea.lastChild);
    }
    searchErrorText = '';

    //report invalid columns
    if(Array.isArray(metaInfo.colErr) && metaInfo.colErr[0] !== undefined){
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
            searchErrorText += ' --> Replacing all instances with "RecipeTitle"<br>';
            document.getElementById('searchError').innerHTML += searchErrorText;
            document.getElementById('searchError').style.display = 'block';
        }
    } else {
        document.getElementById('searchError').style.display = 'none';
    }

    //clear all html from table area first
    let tableArea = document.getElementById('recipeData');
    count = document.getElementById('recipeData').childNodes.length;
    for (i = 0; i < count; i++) {
        tableArea.removeChild(tableArea.lastChild);
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
        if (recipeDataArray[0][0].numResults === '0' && chkbx0.checked === false){
            if (recipeDataArray[1][0].content === 'original' && recipeDataArray[1][0].numResults === '0') {
                searchErrorText = 'No Results Found for "' + textFieldValue + '" --> Showing Simplified Searches<br>';
                document.getElementById('searchError').innerHTML += searchErrorText;
                document.getElementById('searchError').style.display = 'block';
            }
        }
        let tablePost = '<table class="recipeData">' +
            '<th class="metaTableHeader" onclick="sortTable(0)">Title</th>' +
            '<th class="metaTableHeader" onclick="sortTable(1)" >Course</th>' +
            '<th class="metaTableHeader" onclick="sortTable(2)" >Source</th>' +
            '<th class="metaTableHeader" onclick="sortTable(3)" >Main Ingredient</th>' +
            '<th class="metaTableHeader" onclick="sortTable(4)" >Region</th>' +
            '<th class="metaTableHeader" onclick="sortTable(5)" >Date Added</th>' +
            '<th class="metaTableHeader" onclick="sortTable(6)" >Category</th>' +
            '<th class="metaTableHeader" onclick="sortTable(7)" >Last Menu</th>' +
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
            document.getElementById('searchData').style.display = 'block';
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
            document.getElementById('displayError').innerHTML += errorText;
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

//credit to W3Schools.com for base autocomplete code
function autocomplete(inp, arr) {
    let currentFocus;
    inp.addEventListener("input", function (e) {
        let max = 10;
        let current = 0;
        let split, hold = '', pre = '';
        let a, b, i, val = this.value;
        //ignore any characters before a colon
        if (this.value.search(':') !== -1) {
            split = this.value.split(':');
            pre = '<span class="lightText">' + split[0] + ': </span>';
            hold = split[0] + ' ';
            val = split[1].trim();
        }
        //whitespace management
        if (this.value.trim().search(' ') !== -1) {
            split = this.value.trim().split(' ');
            hold = '';
            pre = '';
            for (i = 0; i < split.length - 1; i++) {
                if (split[i].search(':') !== -1) {
                    pre += '<span class="lightText">' + split[i] + ' </span>';
                    hold += split[i] + ' ';
                } else {
                    pre += '<strong>' + split[i] + ' </strong>';
                    hold += split[i] + ' ';
                }
            }
            val = split[split.length - 1];
        }
        closeAllLists();
        if (!val) {
            return false;
        }
        currentFocus = -1;
        /*create a DIV element that will contain the items (values):*/
        a = document.createElement("DIV");
        a.setAttribute("id", this.id + "autocomplete-list");
        a.setAttribute("class", "autocomplete-items");
        this.parentNode.appendChild(a);
        /*for each item in the array...*/
        for (i = 0; i < arr.length; i++) {
            /*check if the item starts with the same letters as the text field value:*/
            if (arr[i].substr(0, val.length).toUpperCase() === val.toUpperCase() && current <= max) {
                b = document.createElement("DIV");
                b.innerHTML = pre;
                b.innerHTML += "<strong>" + arr[i].substr(0, val.length) + "</strong>";
                b.innerHTML += arr[i].substr(val.length);
                b.innerHTML += "<input type='hidden' value='" + arr[i] + "'>";
                b.addEventListener("click", function (e) {
                    inp.value = hold + this.getElementsByTagName("input")[0].value;
                    closeAllLists();
                });
                a.appendChild(b);
                current++;
            }
        }
    });
    inp.addEventListener("keydown", function (e) {
        let x = document.getElementById(this.id + "autocomplete-list");
        if (x) x = x.getElementsByTagName("div");
        if (e.keyCode === 40) { //down
            currentFocus++;
            addActive(x);
        } else if (e.keyCode === 38) { //up
            currentFocus--;
            addActive(x);
        } else if (e.keyCode === 13) { //enter
            e.preventDefault();
            if (currentFocus > -1) {
                if (x) x[currentFocus].click();
                currentFocus = -1;
            } else {
                initiateSearch();
                closeAllLists();
            }
        }
    });

    function addActive(x) {
        if (!x) return false;
        removeActive(x);
        if (currentFocus >= x.length) currentFocus = 0;
        if (currentFocus < 0) currentFocus = (x.length - 1);
        /*add class "autocomplete-active":*/
        x[currentFocus].classList.add("autocomplete-active");
    }

    function removeActive(x) {
        for (let i = 0; i < x.length; i++) {
            x[i].classList.remove("autocomplete-active");
        }
    }

    function closeAllLists(elmnt) {
        let x = document.getElementsByClassName("autocomplete-items");
        for (let i = 0; i < x.length; i++) {
            if (elmnt !== x[i] && elmnt !== inp) {
                x[i].parentNode.removeChild(x[i]);
            }
        }
        currentFocus = -1;
    }

    document.addEventListener("click", function (e) {
        closeAllLists(e.target);
    });
}

let food = ['acorn squash','alfalfa sprouts','almond','anchovy','anise','appetite','appetizer','apple','apricot','artichoke','asparagus','aspic','ate','avocado','bacon','bagel','bake','baked Alaska','bamboo shoots','banana','barbecue','barley','basil','batter','beancurd','beans','beef','beet','bell pepper','berry','biscuit','bitter','black beans','black tea','black-eyed peas','blackberry','bland','blood orange','blueberry','boil','bowl','boysenberry','bran','bread','breadfruit','breakfast','brisket','broccoli','broil','brown rice','brownie','brunch','Brussels sprouts','buckwheat','buns','burrito','butter','butter bean','cake','calorie','candy','candy apple','cantaloupe','capers','caramel','caramel apple','carbohydrate','carrot','cashew','cassava','casserole','cater','cauliflower','caviar','cayenne pepper','celery','cereal','chard','cheddar','cheese','cheesecake','chef','cherry','chew','chick peas','chicken','chili','chips','chives','chocolate','chopsticks','chow','chutney','cilantro','cinnamon','citron','citrus','clam','cloves','cobbler','coconut','cod','coffee','coleslaw','collard greens','comestibles','cook','cookbook','cookie','corn','cornflakes','cornmeal','cottage cheese','crab','crackers','cranberry','cream','cream cheese','crepe','crisp','crunch','crust','cucumber','cuisine','cupboard','cupcake','curds','currants','curry','custard','daikon','daily bread','dairy','dandelion greens','Danish pastry','dates','dessert','diet','digest','digestive system','dill','dine','diner','dinner','dip','dish','dough','doughnut','dragonfruit','dressing','dried','drink','dry','durian','eat','Edam cheese','edible','egg','eggplant','elderberry','endive','entree','fast','fat','fava beans','feast','fed','feed','fennel','fig','fillet','fire','fish','flan','flax','flour','food','food pyramid','foodstuffs','fork','freezer','French fries','fried','fritter','frosting','fruit','fry','garlic','gastronomy','gelatin','ginger','ginger ale','gingerbread','glasses','Gouda cheese','grain','granola','grape','grapefruit','grated','gravy','green bean','green tea','greens','grub','guacamole','guava','gyro','halibut','ham','hamburger','hash','hazelnut','herbs','honey','honeydew','horseradish','hot','hot dog','hot sauce','hummus','hunger','hungry','ice','ice cream','ice cream cone','iceberg lettuce','iced tea','icing','jackfruit','jalape�o','jam','jelly','jellybeans','jicama','jimmies','Jordan almonds','jug','juice','julienne','junk food','kale','kebab','ketchup','kettle','kettle corn','kidney beans','kitchen','kiwi','knife','kohlrabi','kumquat','ladle','lamb','lard','lasagna','legumes','lemon','lemonade','lentils','lettuce','licorice','lima beans','lime','liver','loaf','lobster','lollipop','loquat','lox','lunch','lunch box','lunchmeat','lychee','macaroni','macaroon','main course','maize','mandarin orange','mango','maple syrup','margarine','marionberry','marmalade','marshmallow','mashed potatoes','mayonnaise','meat','meatball','meatloaf','melon','menu','meringue','micronutrient','milk','milkshake','millet','mincemeat','minerals','mint','mints','mochi','molasses','mole sauce','mozzarella','muffin','mug','munch','mushroom','mussels','mustard','mustard greens','mutton','napkin','nectar','nectarine','nibble','noodles','nosh','nourish','nourishment','nut','nutmeg','nutrient','nutrition','nutritious','oatmeal','oats','oil','okra','oleo','olive','omelet','omnivore','onion','orange','order','oregano','oven','oyster','pan','pancake','papaya','parsley','parsnip','pasta','pastry','pate','patty','pattypan squash','pea','pea pod','peach','peanut','peanut butter','pear','pecan','pepper','pepperoni','persimmon','pickle','picnic','pie','pilaf','pineapple','pita bread','pitcher','pizza','plate','platter','plum','poached','pomegranate','pomelo','pop','popcorn','popovers','popsicle','pork','pork chops','pot','pot roast','potato','preserves','pretzel','prime rib','protein','provisions','prune','pudding','pumpernickel','pumpkin','punch','quiche','quinoa','radish','raisin','raspberry','rations','ravioli','recipe','refreshments','refrigerator','relish','restaurant','rhubarb','ribs','rice','risotto','roast','roll','rolling pin','romaine','rosemary','rye','saffron','sage','salad','salami','salmon','salsa','salt','sandwich','sauce','sauerkraut','sausage','savory','scallops','scrambled','seaweed','seeds','sesame seed','shallots','sherbet','shish kebab','shrimp','slaw','slice','smoked','snack','soda','soda bread','sole','sorbet','sorghum','sorrel','soup','sour','sour cream','soy','soy sauce','soybeans','spaghetti','spareribs','spatula','spices','spicy','spinach','split peas','spoon','spork','sprinkles','sprouts','spuds','squash','squid','steak','stew','stir-fry','stomach','stove','straw','strawberry','string bean','stringy','strudel','sub sandwich','submarine sandwich','succotash','suet','sugar','summer squash','sundae','sunflower','supper','sushi','sustenance','sweet','sweet potato','Swiss chard','syrup','taco','take-out','tamale','tangerine','tapioca','taro','tarragon','tart','tea','teapot','teriyaki','thyme','toast','toaster','toffee','tofu','tomatillo','tomato','torte','tortilla','tuber','tuna','turkey','turmeric','turnip','ugli fruit','unleavened','utensils','vanilla','veal','vegetable','venison','vinegar','vitamin','wafer','waffle','walnut','wasabi','water','water chestnut','watercress','watermelon','wheat','whey','whipped cream','wok','yam','yeast','yogurt','yolk','zucchini'];