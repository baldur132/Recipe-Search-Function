<?php
// V3 rewritten to new CSS
//   Image display improvements
// 2019-4-18 BIS works

// V2 with external shared scripts
//  DB agnostic
//2016-10-09 SGS works

include_once("../MenuDB/SharedScriptsMenuDB.php");
include_once("SharedScriptsRecipeDB.php");

$id=$_GET['id'];
$Recipe = FindRecipeByID($id);
$MenuDays = GetDaysOnMenu($Recipe->RecipeTitle);
?>

<head>
	<meta http-equiv="content-type" content="text/html" charset="utf-8" />
    <link rel="shortcut icon" href="../favicon.ico" />
	<meta name="description" content="your description goes here" />
	<meta name="keywords" content="reicipe data base" />
	<meta name="author" content="Stefan Siegel" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
	<link type="text/css" rel="stylesheet" href="OneRecipeGrid.css" media="screen,projection" />
	<title><?php echo "$Recipe->RecipeTitle"?> - Display Recipe</title>
	<meta name="google-translate-customization" content="20a55dedff2a61e9-c6bb8ba3a150feb5-g15c2ff799cf8c79f-c"/>
    <link rel="icon" type="image/x-icon" href="favicon.ico">
</head>

<body>
<div class="containerThin"> <!-- use id="containerThin" (=700px) or id="container" (=920px) to make layout fixed-width -->
    <div class="sitetitle">
            <h1><?php if($Recipe->RecipeTitle === ' '){echo "Unknown";} else {echo "$Recipe->RecipeTitle";}?></h1>
            <h2>Recipe Display</h2>
    </div>

    <div class="menu">
        <div class="menuInternal">
    		<a href="RecipePrintout.php?id=<?php echo "$id"?>">Print</a>
            <a href="InputRecipe.php?id=<?php echo "$id"?>">Edit</a>
            <div class="dropdown">
                <button>Add</button>
                <div class="dropContent">
                    <button onclick="displayModal(0)">Picture</button>
                    <button onclick="displayModal(1)">On Menu</button>
                </div>
            </div>
            <div class="dropdown">
                <button>Navigation</button>
                <div class="dropContent">
                    <a href="../Cooking.php">Home</a>
                    <a href="RecipeSearch.html">Search</a>
                    <a href="RecipeList.php">List</a>
                    <a href="../MenuDB/DisplayMenuWeek.php">Menu</a>
                </div>
            </div>
        </div>
    </div>

    <div class="bigError hide"></div>

	<div class="content" id="content">
        <div class="recipeContainer">
            <div class="col1-7_row1" id="picture">
                <div class="imgNavContainer" id="imgNavContainer">
                <div class="imgButton"><button class="picButton picButtonActive picButtonHover" id="prevButton" onclick="showPrevPic()"><svg viewBox="0 0 11.642 11.642" class="arrowSVG strokeFill"><g transform="translate(0 -285.36)"><path d="m8.9869 286.16-6.3412 5.02 6.3412 5.02" class="arrowPath"></path></g></svg></button></div><?php
                if ($Recipe->NPictures >0){
                //This recipe has one or more pictures. Display the one that was requested, or by default the last one
                    echo('<div class="recipePicture"><a href="RecipePictures/'.$Recipe->RecipeID.'-1-Original.jpg" id="imgLink"><img src="RecipePictures/'.$Recipe->RecipeID.'-1-420x280.jpg" id="Picture" alt="Recipe Image"></a></div>');
                }
                ?><div class="imgButton"><button class="picButton picButtonActive picButtonHover" id="nextButton" onclick="showNextPic()"><svg viewBox="0 0 11.642 11.642" class="arrowSVG strokeFill"><g transform="translate(0 -285.36)"><path d="m2.6547 296.2 6.3412-5.02-6.3412-5.02" class="arrowPath"></path></g></svg></button></div>
                </div>
                <div class="imgPositionDisplay" id="imgPositionDisplay">
                    <?php
                    if ($Recipe->NPictures >0){
                        for ($i = 0; $i < $Recipe->NPictures; $i++){
                            echo('<button class="imgPosBlob" id="blb'.$i.'" onclick="showPicture('.$i.')"></button>');
                        }
                    }
                    ?>
                </div>
            </div>

            <div class="col1-4_row3 recipeText" id="source">
                <h2>Source: </h2>
                <h3>
                <?php if (strtolower(substr($Recipe->URL, 0,4))=='http'){
                    echo('<a href="'.$Recipe->URL.'">'.'Recipe on: '.$Recipe->Source.'</a>');
                    }
                else{
                    echo "$Recipe->Source";
                }
                ?>
                </h3>
            </div>

            <div class="col1-4_row4 recipeText" id="course"><h2>Course:</h2> <h3><?php echo "$Recipe->Course"?></h3></div>
            <div class="col1-4_row5 recipeText" id="mainIng"><h2>Main Ingredient:</h2><h3><?php echo "$Recipe->MainIngredient"?></h3></div>
            <div class="col4-7_row3 recipeText" id="region"><h2>Region</h2><h3> <?php echo "$Recipe->Region"?></h3><br></div>
            <div class="col4-7_row4 recipeText" id="lang"><h2>Language:</h2><h3> <?php echo "$Recipe->Language"?></h3><br></div>
            <div class="col4-7_row5 recipeText" id="special"><h2>Special:</h2><h3> <?php echo "$Recipe->Special"?></h3><br></div>

            <div class="col1-4_row6 recipeText ingredients" id="ingredients">
                <h2>Ingredients:</h2><br>
                <p><?php echo str_replace("\r\n","<BR>",$Recipe->Ingredients)?></p>
            </div>
            <div class="col4-7_row6 recipeText ingredients" id="ingredientsCont">
                <h2>Ingredients Continued:</h2><br>
                <p><?php echo str_replace("\r\n","<BR>",$Recipe->IngredientsContinued)?></p>
            </div>

            <br><br><br>

            <div class="col1-7_row7 recipeText instructions" id="instructions">
              <h2>Instructions: </h2><br>
                <p><?php echo str_replace("\r\n","<BR>",$Recipe->Instructions)?></p>
            </div>

            <div class="col1-3_row8">
                <div class="recipeText" id="notes">
                    <h2>Notes:</h2> <br>
                    <p><?php echo str_replace("\r\n","<BR>",$Recipe->Notes); ?></p>
                    <br>
                </div>
                <div class="recipeText" id="frequency">
                    <h2>Frequency:</h2>
                    <?php switch($Recipe->OnMenu){
                        case 0: echo ('<h3>Dish was never on menu</h3>'); break;
                        case 1: echo ('<h3>Dish on menu once:</h3>'); break;
                        case 2: echo ('<h3>Dish on menu twice:</h3>'); break;
                        default: echo('<h3>Dish '.$Recipe->OnMenu. ' times on menu:</h3> '); break;
                        } ?>
                    <div id="onMenu">
                        <?php
                        $i=0;
                        if ($Recipe->OnMenu > 10){
                            $NumMenuDays = 10;
                            echo('most recent days: <br><br>');
                        }
                        else{$NumMenuDays = $Recipe->OnMenu;}

                        while ($i < $NumMenuDays) {
                            echo ('<a href="../MenuDB/InputMenuDay.php?MenuDate=');
                            echo ($MenuDays[$i].'">'.$MenuDays[$i].'</a><br>');
                            $i++;
                        }
                        $DisplayAddButton = true;
                        if ($NumMenuDays > 0){
                            if($MenuDays[0] == date('Y-m-d')) {$DisplayAddButton = false;}
                        } ?>
                    </div>
                </div>
            </div>
            <div class="col3-5_row8">
                <?php
                if($Recipe->Category === ''){
                    echo "<div class='recipeText' id='category'><h2>Category:</h2><h3>Not Assigned</h3></div>";
                } else {
                    echo "<div class='recipeText' id='category'><h2>Category:</h2><h3>".$Recipe->Category."</h3></div>";
                }
                if($Recipe->Servings === ''){
                    echo "<div class='recipeText' id='servings'><h2>Servings:</h2><h3>Not Given</h3></div>";
                } else {
                    echo "<div class='recipeText' id='servings'><h2>Servings:</h2> <h3>".$Recipe->Servings."</h3></div>";
                }
                if($Recipe->Rating === ''){
                    echo "<div class='recipeText' id='rating'><h2>Rating:</h2><h3>None Given</h3></div>";
                } else {
                    echo "<div class='recipeText' id='rating'><h2>Rating:</h2> <h3>".$Recipe->Rating."</h3></div>";
                }
                if($Recipe->PrepTime === '' || $Recipe->PrepTime === '00:00:00'){
                    echo "<div class='recipeText' id='prepTime'><h2>Preparation Time:</h2><h3>Unknown</h3></div>";
                } else {
                    echo "<div class='recipeText' id='prepTime'><h2>Preparation Time:</h2> <h3>".$Recipe->PrepTime."</h3></div>";
                }?>
                <div class='recipeText' id="translate">
                    <h2>Translate Page to:</h2>
                    <div id="google_translate_element"></div>
                        <script type="text/javascript">
                            function googleTranslateElementInit() {
                            new google.translate.TranslateElement({pageLanguage: 'de', layout: google.translate.TranslateElement.InlineLayout.SIMPLE, multilanguagePage: true}, 'google_translate_element');
                        }
                    </script><script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
                </div>
            </div>
            <div class="col5-7_row8">
                <?php
                if($Recipe->CookTime === '' || $Recipe->CookTime === '00:00:00'){
                    echo "<div class='recipeText' id='cookTime'><h2>Cooking Time:</h2><h3>Unknown</h3></div>";
                } else {
                    echo "<div class='recipeText' id='cookTime'><h2>Cooking Time:</h2><h3>".$Recipe->CookTime."</h3></div>";
                }
                if($Recipe->Wine === '' || $Recipe->Wine === ' '){
                    echo "<div class='recipeText' id='wine'><h2>Wine:</h2><h3>None Given</h3></div>";
                } else {
                    echo "<div class='recipeText' id='wine'><h2>Wine:</h2><h3>".$Recipe->Wine."</h3></div>";
                }
                if($Recipe->DateCreated === '' || $Recipe->DateCreated === '0000-00-00'){
                    echo "<div class='recipeText' id='dateCreated'><h2>Date Created:</h2><h3>None Given</h3></div>";
                } else {
                    echo "<div class='recipeText' id='dateCreated'><h2>Date Created:</h2><h3>".$Recipe->DateCreated."</h3></div>";
                }
                if($Recipe->DateModified === '' || $Recipe->DateModified === '0000-00-00'){
                    echo "<div class='recipeText' id='dateModified'><h2>Date Modified:</h2><h3>None Assigned</h3></div>";
                } else {
                    echo "<div class='recipeText' id='dateModified'><h2>Date Modified:</h2><h3>".$Recipe->DateModified."</h3></div>";
                }?>
                <div class='recipeText'>
                    <h2> Recipe ID:</h2><h3><?php echo "$id"; ?></h3>
                </div>
            </div>
        </div>
	</div>

    <div id="addRecipe" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div class="textContainer">
                <div class="addRecipePictureModal modalInternalGrid">
                    <h2 class="headerText">Add Picture to Recipe:</h2>
                    <form id="upload" action="UploadPicture.php" method="POST" enctype="multipart/form-data">
                        <input class="hide" id="NPictures" type="text" name="NPictures" value="<?php echo($Recipe->NPictures);?>">
                        <input class="hide" id="RecipeID" type="text" name="RecipeID" value="<?php echo($Recipe->RecipeID);?>">
                        <input class="hide" id="ReturnURL" type="text" name="ReturnURL" value="">

                        <h3 class="picInputLabelFile">Select File:</h3><h3 class="picInputLabelURL">Paste URL:</h3>
                        <input type="file" id="fileselect" class="picInputFile" size="20" name="fileselect"/>
                        <input type="text" size="20" name="ud_PictureURL" class="picInputURL" value="">
                        <br>
                        <input type="Submit" value="Upload" class="pictureSubmit">
                    </form>
                </div>
                <div class="addRecipeToMenu modalInternalGrid" id="AddToMenuSection">
                    <?php
                    if($DisplayAddButton){
                        echo('
                        <h2 class="headerText">Add to today\'s menu:</h2>
                        <form action="AddRecipeToMenu.php" method="post">
                        <div class="submitWrapper">
                            <input type="Submit" value="I made this Dish today" class="addMenuButton">
                            <input type="hidden" name="ud_RecipeID" value="'.$id.'">
                        </div>
                        </form>');
                    } else {
                        echo("<h2 style='grid-column:1/3;margin:0;padding:0;text-align:center;'>Dish is already on today's menu</h2>");
                    } ?>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    let exists = true;
    if ('<?php echo $Recipe->Exists; ?>' === ''){
        exists = false;
    } else {
        exists = '<?php echo $Recipe->Exists; ?>';
    }
    if (!exists) {
        document.getElementById('content').style.display = 'none';
        let errordiv = document.getElementsByClassName('bigError')[0];
        errordiv.classList.remove('hide');
        errordiv.classList.add('block');
        errordiv.innerHTML = 'Recipe Not Found - Invalid ID';
    }
    let returnURL = document.getElementById('ReturnURL');
    returnURL.setAttribute('value', window.location.href);
    let recipeID = <?php echo $id; ?>;
    let picNum = <?php echo $Recipe->NPictures; ?>;
    let currentPic = 1;
    let noImg = false;
    let srcPath, hrefPath, i;
    let pictureLink = document.getElementById('imgLink');
    let picture = document.getElementById('Picture');
    let buttons = document.querySelectorAll('.picButton');
    let blobs = document.querySelectorAll('.imgPosBlob');
    let modal = document.getElementById('addRecipe');
    let closeSpan = document.getElementsByClassName('close')[0];
    let pathHeader = 'RecipePictures/';
    let pathEnder = '-420x280.jpg';
    if (picNum === 0 || isNaN(picNum)) {
        document.getElementById('imgNavContainer').style.display = 'none';
        document.getElementById('imgPositionDisplay').style.display = 'none';
        noImg = true;
        console.log('picNum is 0 or NaN');
    }
    if (picNum === 1) {
        document.getElementById('imgPositionDisplay').style.display = 'none';
        buttons.forEach(element =>{
            if (element.classList.contains('picButtonActive')) {
                element.classList.remove('picButtonActive');
            }
            if (element.classList.contains('picButtonHover')) {
                element.classList.remove('picButtonHover');
            }
            element.classList.add('picButtonHoverEmpty');
            element.classList.add('picButtonActiveEmpty');
        })
    }
    function displayModal(id){
        modal.style.display = 'block';
        let modalContent = document.getElementsByClassName('modalInternalGrid')[id];
        modalContent.style.display = 'grid';
    }
    function hideModal(){
        modal.style.display = 'none';
        let modalContent = document.getElementsByClassName('modalInternalGrid');
        for (i = 0; i < modalContent.length; i++) {
            modalContent[i].style.display = 'none';
        }
    }
    closeSpan.onclick = function() {hideModal()};
    window.onclick = function(event) {if (event.target === modal) {hideModal();}};
    function showNextPic(){
        if (currentPic < picNum) {
            currentPic++;
            srcPath = pathHeader + recipeID + '-' + currentPic + pathEnder;
            picture.setAttribute('src', srcPath);
            hrefPath = pathHeader + recipeID + '-' + currentPic + '-Original.jpg';
            pictureLink.setAttribute('href', hrefPath);
        } else if (currentPic >= picNum) {
            currentPic = 1;
            srcPath = pathHeader + recipeID + '-' + currentPic + pathEnder;
            picture.setAttribute('src', srcPath);
            hrefPath = pathHeader + recipeID + '-' + currentPic + '-Original.jpg';
            pictureLink.setAttribute('href', hrefPath);
        } else {
            console.log('invalid picNum')
        }
        updateBlobs();
    }
    function showPrevPic() {
        if (currentPic > 1) {
            currentPic--;
            srcPath = pathHeader + recipeID + '-' + currentPic + pathEnder;
            picture.setAttribute('src', srcPath);
            hrefPath = pathHeader + recipeID + '-' + currentPic + '-Original.jpg';
            pictureLink.setAttribute('href', hrefPath);
        } else if (currentPic === 1) {
            currentPic = picNum;
            srcPath = pathHeader + recipeID + '-' + currentPic + pathEnder;
            picture.setAttribute('src', srcPath);
            hrefPath = pathHeader + recipeID + '-' + currentPic + '-Original.jpg';
            pictureLink.setAttribute('href', hrefPath);
        } else {
            console.log('invalid picNum');
        }
        updateBlobs();
    }
    function showPicture(id) {
        if (id <= picNum && id >= 0) {
            currentPic = id+1;
            srcPath = pathHeader + recipeID + '-' + currentPic + pathEnder;
            picture.setAttribute('src', srcPath);
            hrefPath = pathHeader + recipeID + '-' + currentPic + '-Original.jpg';
            pictureLink.setAttribute('href', hrefPath);
        } else {
            console.log('invalid ID');
        }
        updateBlobs();
    }
    function updateBlobs() {
        blobs.forEach(element =>{
            if (element.classList.contains('filledBlob')) {
                element.classList.remove('filledBlob');
            }
        });
        if (currentPic <= blobs.length && currentPic >= 0){
            let selector = currentPic-1;
            blobs[selector].classList.add('filledBlob');
        } else {
            console.log('invalid currentPic: ' + currentPic);
        }
    }
    if(!noImg){
        updateBlobs();
    }
</script>
</body>
