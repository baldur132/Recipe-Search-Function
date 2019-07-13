
//RecipeSearch Javascript
let i, e, rsString, rsParsed, metaInfo, tableData, parameters, cid = 0;
let recipeDataArray = [];
let searchBar = document.getElementById('searchBar');
let chkbx0 = document.getElementById('strictParse');
let chkbx1 = document.getElementById('forceExact');
let orderData = 'RecipeTitle:ASC';
let direction = ['DESC','ASC','ASC','ASC','ASC','DESC','ASC','DESC','DESC'];
let columnTitle = ['RecipeTitle','Course','Source','MainIngredient','Region','DateCreated','Category','LastOnMenu','OnMenu'];

//URL Short Term Memory
//read URL hash and if not empty search
function readURLnUpdate() {
    let hash = window.location.hash;
    if (hash) {
        hash = hash.replace('#', '');
        searchBar.value = hash.replace(/(%20)/g, ' ');
        initiateSearch();
    }
}
//update the url hash after every search
function updateURLParameter() {
    let text = searchBar.value;
    if (text) {
        let href = location.protocol + '//' + location.host + location.pathname;
        window.location.href = href + '#' + text.replace(/\s/g, '%20');
    } else {
        window.location.hash = '';
    }
}

//Search JS
function initiateSearch() {
    let text = searchBar.value;
    parameters = [chkbx0.checked, chkbx1.checked];
    searchDB(text, 'searchFunction');
    updateURLParameter();
    changeDisplay('optionPanel', 'hide');
    changeDisplay('loadIcon', 'show');
    document.getElementById('searchHeader').classList.remove('searchHeaderLarge');
}
//generate sorted query
function sortTable(id){
    cid = id;
    let column = columnTitle[id];
    orderData = column + ':' + direction[id];
    initiateSearch();
    (direction[id] === 'ASC' ? direction[id] = 'DESC' : direction[id] = 'ASC');
}
//send to php and query db
function searchDB(query, functionRequest) {
    $.ajax({
        type: "POST",
        url: 'RecipeSearchPHP.php',
        dataType: 'json',
        data: {function: functionRequest, arguments: [query, orderData, parameters]},
        statusCode: {
            500: function() {
                errorText = ' 500 - Internal Server Error ';
                //stop loading animation and display primary error
            },
            404: function() {
                errorText = ' 404 - Database Not Found ';
                //stop loading animation and display primary error
            }
        },

        success: function (obj) {
            if (!('error' in obj)) {
                let result = obj.result;
                prepareJSON(result);
            } else {
                console.log(obj.error);
            }
        }
    })
}
//parse incoming JSON text
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
}
//compile all results into one table and display
function displayResults() {
    //clear all residual errors
    let sErrorArea = document.getElementById('searchError');
    let count = document.getElementById('searchError').childNodes.length;
    for (i = 0; i < count; i++) {sErrorArea.removeChild(sErrorArea.lastChild);}

    let pError = false;
    let errorText = '';
    let errorArea = document.getElementById('displayError');
    errorArea.innerHTML = '';

    //clear all old tables
    let tableArea = document.getElementById('results');
    count = document.getElementById('results').childNodes.length;
    for (i = 0; i < count; i++) {tableArea.removeChild(tableArea.lastChild);}
    changeDisplay('searchData', 'hide');

    //Search Error Check and Display
    //check for column errors and display if present
    if (Array.isArray(metaInfo.colErr) && metaInfo.colErr[0] !== undefined) {
        let columns = '';
        let a = document.createElement('div');
        let count = metaInfo.colErr.length;
        if (count === 1) {
            a.innerHTML = 'Invalid Column: "' + metaInfo.colErr[0] + '" --> Replaced with "RecipeTitle"';
        } else if (count >= 2) {
            for (i = 0; i < count; i++) {
                if (count + 1 >= count) {
                    columns += '"' + metaInfo.colErr[i] + '"'
                } else {
                    columns += '"' + metaInfo.colErr[i] + '", '
                }
            }
            a.innerHTML = 'Invalid Columns: ' + columns + ' --> Replaced all with "RecipeTitle"'
        } else {
            console.log('colErr encountered invalid count: ' + count);
        }
        sErrorArea.appendChild(a);
    }
    //check for order errors and display if present
    if (metaInfo.orderError !== 'none') {
        console.log('bad user order detected');
        let a = document.createElement('div');
        a.innerHTML = 'Invalid Order Data - Please Check Syntax --> ' + metaInfo.orderError;
        sErrorArea.appendChild(a);
    }

    //Primary Error Check and Display
    let generate = true;
    //check for SQL errors
    if (metaInfo.errorMsg === 'SQLerr') {
        console.log('bad sql detected');
        errorText += '500 - Bad SQL Statement Generated<br>';
        pError = true;
        generate = false;
    }
    if (metaInfo.errorMsg === 'disabled') {
        console.log('server side script not functioning');
        errorText += 'Search PHP Not Active<br>';
        pError = true;
        generate = false;
    }
    if (metaInfo === undefined || metaInfo === null || metaInfo === '') {
        console.log('bad or no server response');
        errorText += 'Server Response Could Not Be Interpreted<br>';
        pError = true;
        generate = false;
    }
    //check for no results
    let allEmpty = false;
    let emptyCount = 0;
    for (i = 0; i < searchCount; i++) {
        if (recipeDataArray[i][0].numResults === 0) {
            emptyCount++;
        }
    }
    if (emptyCount === searchCount) {
        allEmpty = true;
        pError = true;
        generate = false;
        errorText += 'No Results Found - Please Try Again<br>';
    }
    //display
    if (pError === true) {errorArea.innerHTML = errorText;}

    //Table Generation and Display
    //generate table(s)
    if (allEmpty === false || generate === true) {
        if (recipeDataArray[0][0].numResults === 0 && recipeDataArray[1][0].numResults === 0 && chkbx0.checked === false){
            console.log('alerting to alternate searches');
            let a = document.createElement('div');
            a.innerHTML = 'No Results Found for "' + searchBar.value + '" --> Showing Simplified Searches';
            a.classList.add('errorBlue');
            sErrorArea.appendChild(a);
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
        for (e = 0; e < searchCount; e++) {
            resultNum = recipeDataArray[e][0].numResults + resultNum;
            if (!recipeDataArray[e][0].numResults <= 0) {
                if (e !== 0) {
                    tablePost += '<tr><td colspan="9" class="tableDivider"><h3>Search for "' + recipeDataArray[e][0].content + '"</h3></td>';
                }
                tablePost += generateTable(recipeDataArray[e]);
            }
        }
        changeDisplay('searchData', 'show');
        document.getElementById('resultNum').innerHTML = resultNum.toString();
        document.getElementById('results').innerHTML = tablePost + '</table>';
        updateSortDisplay(cid);

    }
    changeDisplay('loadIcon', 'hide');
}
function generateTable(recipeData){
    tableData = '';
    for(i = 1; i < recipeData.length; i++) {
        tableData += '<tr><td class="title"><a href="DisplayRecipe.php?id=' + recipeData[i].RecipeID + '#">' + recipeData[i].RecipeTitle + '</a></td>' +
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

//Display Modification
//general display toggle
function changeDisplay(id, toggle) {
    let element = document.getElementById(id);
    switch (toggle) {
        case('show'):
            if (element.classList.contains('hidden')) {element.classList.remove('hidden');}
            break;
        case('hide'):
            if (!element.classList.contains('hidden')) {element.classList.add('hidden');}
            break;
    }
}
//update borders indicating sort direction
function updateSortDisplay(id) {
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
    let header = document.getElementsByClassName('metaTableHeader')[id];
    (direction[id] === 'ASC' ? header.classList.add('sortBorderTop') : header.classList.add('sortBorderBottom'));
}
//hide or show option tray
function hideOptions() {document.getElementById('optionPanel').classList.toggle('hidden');}

//Autocomplete Integration
//credit to W3Schools.com for base autocomplete code
function autocomplete(inp, arr) {
    let currentFocus;
    let max = 10;
    let seed = ['RecipeTitle', 'Ingedients', 'Category', 'Course', 'Region', 'Source'];
    inp.addEventListener("input", function (e) {
        let current = 0;
        let split, hold = '', pre = '';
        let a, b, c, d, i, x, val = this.value;
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
        /*if (!val) {
            return false;
        }*/
        currentFocus = -1;
        /*create a DIV element that will contain the items (values):*/
        a = document.createElement("DIV");
        a.setAttribute("id", this.id + "autocomplete-list");
        a.setAttribute("class", "autocomplete-items");
        this.parentNode.appendChild(a);
        for (i = 0; i < arr.length; i++) {
            /*check if the item starts with the same letters as the text field value:*/
            if (current <= max && arr[i].substr(0, val.length).toUpperCase() === val.toUpperCase()) {
                b = document.createElement("DIV");
                b.setAttribute("class", "autocomplete-item");
                b.innerHTML = pre;
                b.innerHTML += "<strong>" + arr[i].substr(0, val.length) + "</strong>";
                b.innerHTML += arr[i].substr(val.length);
                if (current === 0 && pre === '') {
                    for (d = 0; d < seed.length; d++) {
                        c = document.createElement('div');
                        c.innerHTML = seed[d] + ': ' + b.innerHTML;
                        c.innerHTML += "<input type='hidden' value='" + c.innerText + "'>";
                        c.addEventListener("click", function (e) {
                            inp.value = this.getElementsByTagName("input")[0].value;
                            closeAllLists();
                        });
                        a.appendChild(c);
                        current++;
                    }
                }
                b.innerHTML += "<input type='hidden' value='" + arr[i] + "'>";
                b.addEventListener("click", function (e) {
                    inp.value = hold + this.getElementsByTagName("input")[0].value;
                    closeAllLists();
                });
                a.appendChild(b);
                current++;
            }
        }
        if (current === 0 && !pre.search(':')) {
            for (d = 0; d < seed.length; d++) {
                c = document.createElement('div');
                c.innerHTML = seed[d] + ': ' + b.innerHTML;
                c.innerHTML += "<input type='hidden' value='" + c.innerText + "'>";
                c.addEventListener("click", function (e) {
                    inp.value = this.getElementsByTagName("input")[0].value;
                    closeAllLists();
                });
                a.appendChild(c);
                current++;
            }
        }
        b = document.createElement('DIV');
        b.innerHTML = '<strong>' + inp.value + '</strong>';
        a.appendChild(b);

        //Generate X to clear input when clicked
        x = document.createElement("DIV");
        x.setAttribute("id", "clearInput");
        x.setAttribute("class", "clearInput");
        x.innerHTML = '&times';
        x.addEventListener("click", function (e) {
            inp.value = '';
            closeAllLists();
        });
        this.parentNode.appendChild(x);
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
        if (document.getElementById('clearInput') !== null) {
            document.getElementById('clearInput').remove();
        }
        if (elmnt !== undefined && (elmnt.id === 'clearInput' || elmnt.classList.contains('autocomplete-item'))) {
            document.getElementById('searchBar').focus();
        }
        currentFocus = -1;

    }

    document.addEventListener("click", function (e) {
        closeAllLists(e.target);
    });
}
let food = ['acorn squash','alfalfa sprouts','almond','anchovy','anise','appetite','appetizer','apple','apricot','artichoke','asparagus','aspic','ate','avocado','bacon','bagel','bake','baked Alaska','bamboo shoots','banana','barbecue','barley','basil','batter','beancurd','beans','beef','beet','bell pepper','berry','biscuit','bitter','black beans','black tea','black-eyed peas','blackberry','bland','blood orange','blueberry','boil','bowl','boysenberry','bran','bread','breadfruit','breakfast','brisket','broccoli','broil','brown rice','brownie','brunch','Brussels sprouts','buckwheat','buns','burrito','butter','butter bean','cake','calorie','candy','candy apple','cantaloupe','capers','caramel','caramel apple','carbohydrate','carrot','cashew','cassava','casserole','cater','cauliflower','caviar','cayenne pepper','celery','cereal','chard','cheddar','cheese','cheesecake','chef','cherry','chew','chick peas','chicken','chili','chips','chives','chocolate','chopsticks','chow','chutney','cilantro','cinnamon','citron','citrus','clam','cloves','cobbler','coconut','cod','coffee','coleslaw','collard greens','comestibles','cook','cookbook','cookie','coriander','coriander seed','corn','cornflakes','cornmeal','cottage cheese','crab','crackers','cranberry','cream','cream cheese','crepe','crisp','crunch','crust','cucumber','cuisine','cupboard','cupcake','curds','currants','curry','custard','daikon','daily bread','dairy','dandelion greens','Danish pastry','dates','dessert','diet','digest','digestive system','dill','dine','diner','dinner','dip','dish','dough','doughnut','dragonfruit','dressing','dried','drink','dry','durian','eat','Edam cheese','edible','egg','eggplant','elderberry','endive','entree','fast','fat','fava beans','feast','fed','feed','fennel','fennel seed','fig','fillet','fire','fish','flan','flax','flour','food','food pyramid','foodstuffs','fork','freezer','French fries','fried','fritter','frosting','fruit','fry','garlic','gastronomy','gelatin','ginger','ginger ale','gingerbread','glasses','Gouda cheese','grain','granola','grape','grapefruit','grated','gravy','green bean', 'green salad','green tea','greens','grub','guacamole','guava','gyro','halibut','ham','hamburger','hash','hazelnut','herbs','honey','honeydew','horseradish','hot','hot dog','hot sauce','hummus','hunger','hungry','ice','ice cream','ice cream cone','iceberg lettuce','iced tea','icing','jackfruit','jalape�o','jam','jelly','jellybeans','jicama','jimmies','Jordan almonds','jug','juice','julienne','junk food','kale','kebab','ketchup','kettle','kettle corn','kidney beans','kitchen','kiwi','knife','kohlrabi','kumquat','ladle','lamb','lard','lasagna','legumes','lemon','lemonade','lentils','lettuce','licorice','lima beans','lime','liver','loaf','lobster','lollipop','loquat','lox','lunch','lunch box','lunchmeat','lychee','macaroni','macaroon','main course','maize','mandarin orange','mango','maple syrup','margarine','marionberry','marmalade','marshmallow','mashed potatoes','mayonnaise','meat','meatball','meatloaf','melon','menu','meringue','micronutrient','milk','milkshake','millet','mincemeat','minerals','mint','mints','mochi','molasses','mole sauce','mozzarella','muffin','mug','munch','mushroom','mussels','mustard','mustard greens','mutton','napkin','nectar','nectarine','nibble','noodles','nosh','nourish','nourishment','nut','nutmeg','nutrient','nutrition','nutritious','oatmeal','oats','oil','okra','oleo','olive','omelet','omnivore','onion','orange','order','oregano','oven','oyster','pan','pancake','papaya','parsley','parsnip','pasta','pastry','pate','patty','pattypan squash','pea','pea pod','peach','peanut','peanut butter','pear','pecan','pepper','pepperoni','persimmon','pickle','picnic','pie','pilaf','pineapple','pita bread','pitcher','pizza','plate','platter','plum','poached','pomegranate','pomelo','pop','popcorn','popovers','popsicle','pork','pork chops','pot','pot roast','potato','preserves','pretzel','prime rib','protein','provisions','prune','pudding','pumpernickel','pumpkin','punch','quiche','quinoa','radish','raisin','raspberry','rations','ravioli','recipe','refreshments','refrigerator','relish','restaurant','rhubarb','ribs','rice','risotto','roast','roll','rolling pin','romaine','rosemary','rye','saffron','sage','salad','salami','salmon','salsa','salt','sandwich','sauce','sauerkraut','sausage','savory','scallops','scrambled','seaweed','seeds','sesame seed','shallots','sherbet','shish kebab','shrimp','slaw','slice','smoked','snack','soda','soda bread','sole','sorbet','sorghum','sorrel','soup','sour','sour cream','soy','soy sauce','soybeans','spaghetti','spareribs','spatula','spices','spicy','spinach','split peas','spoon','spork','sprinkles','sprouts','spuds','squash','squid','steak','stew','stir-fry','stomach','stove','straw','strawberry','string bean','stringy','strudel','sub sandwich','submarine sandwich','succotash','suet','sugar','summer squash','sundae','sunflower','supper','sushi','sustenance','sweet','sweet potato','Swiss chard','syrup','taco','take-out','tamale','tangerine','tapioca','taro','tarragon','tart','tea','teapot','teriyaki','thyme','toast','toaster','toffee','tofu','tomatillo','tomato','torte','tortilla','tuber','tuna','turkey','turmeric','turnip','ugli fruit','unleavened','utensils','vanilla','veal','vegetable','venison','vinegar','vitamin','wafer','waffle','walnut','wasabi','water','water chestnut','watercress','watermelon','wheat','whey','whipped cream','wok','yam','yeast','yogurt','yolk','zucchini', 'RecipeID', 'RecipeTitle', 'Course', 'URL', 'Source', 'MainIngredient', 'Region', 'Category', 'DateCreated', 'LastOnMenu', 'OnMenu', 'DateModified', 'Ingedients', 'IngredientsContinued', 'Instructions', 'Notes', 'Servings', 'Special', 'Rating', 'PrepTime', 'CookTime', 'Wine', 'Language', 'NPictures'];