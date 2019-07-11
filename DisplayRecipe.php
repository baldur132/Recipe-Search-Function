<?php
// V3 rewritten to new CSS
//   Image display improvements
// 2019-4-18 BIS works

// V2 with external shared scripts
//  DB agnostic
//2016-10-09 SGS works

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
	<link type="text/css" rel="stylesheet" href="RDB.css" media="screen,projection" />
	<title><?php echo "$Recipe->RecipeTitle"?> - Display Recipe</title>
	<!--<meta name="google-translate-customization" content="20a55dedff2a61e9-c6bb8ba3a150feb5-g15c2ff799cf8c79f-c"/>-->
    <link rel="icon" type="image/x-icon" href="favicon.ico">
</head>

<body>
<div class="containerThin">
    <div class="sitetitle">
            <h1><?php if($Recipe->RecipeTitle === ' '){echo "Unknown";} else {echo "$Recipe->RecipeTitle";}?></h1>
            <h2>Recipe Display</h2>
    </div>

    <div class="menu">
        <div class="menuIcon" id="menuIcon">
            <div class="menuIconLine menuIconT"></div>
            <div class="menuIconLine menuIconM"></div>
            <div class="menuIconLine menuIconB"></div>
        </div>
        <div class="menuExtended" id="menuExtended">
    		<a href="RecipePrintout.php?id=<?php echo "$id"?>">Print</a>
            <a href="InputRecipe.php?id=<?php echo "$id"?>">Edit</a>
            <div class="dropdown">
                <button>Add</button>
                <div class="dropContent overWidth">
                    <a onclick="displayModal()">Picture</a>
                    <a onclick="displayModal()">On Menu</a>
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

    <div class="displayError hidden"></div>

	<div class="content" id="content">
        <div class="recipeContainer">
            <div class="img recipeFullRow" id="picture">
                <div class="imgDisplay" id="imgDisplay">
                    <?php if ($Recipe->NPictures > 0) {
                        for ($i = 1; $i <= $Recipe->NPictures; $i++) {
                            echo('<div class="recipePicture"><img src="RecipePictures/' . $Recipe->RecipeID . '-'.$i.'-420x280.jpg" id="rPic'.$i.'" alt="Recipe Image"></div>');
                        }
                    } ?>
                </div>
                <?php if($Recipe->NPictures > 1){echo '
                <div class="imgPositionDisplay" id="imgPositionDisplay">
                    <button class="svgArrow noStyle" id="arrowLeft" onclick="changeImg(0)">
                        <svg version="1.1" viewBox="0 0 33.867 14.288" xmlns="http://www.w3.org/2000/svg">
                            <g transform="matrix(.99079 0 0 .99886 .010921 -272.38)" style="stroke-width:1.0052">
                                <path d="m11.017 285.13-9.1654-5.2917 9.1654-5.2917z" style="stroke-linejoin:round;stroke-width:2.6596;"></path>
                                <path d="m11.642 280.07h20.108" style="fill:none;stroke-linecap:round;stroke-width:3.7235;"></path>
                            </g>
                        </svg>
                    </button>
                    <div class="blobs">';
                        for ($i = 0; $i < $Recipe->NPictures; $i++){
                            echo('<div class="imgPosBlob" id="blb'.$i.'"></div>');
                        }
                    echo '</div>
                    <button class="svgArrow noStyle" id="arrowRight" onclick="changeImg(1)">
                        <svg version="1.1" viewBox="0 0 33.867 14.288" xmlns="http://www.w3.org/2000/svg">
                            <g transform="matrix(.9903 0 0 1 .013874 -272.7)" style="stroke-width:1.0049">
                                <path d="m23.14 285.13 9.1654-5.2917-9.1654-5.2917z" style="stroke-linejoin:round;stroke-width:2.6587;"></path>
                                <path d="m22.516 280.07h-20.108" style="fill:none;stroke-linecap:round;stroke-width:3.7223;"></path>
                            </g>
                        </svg>
                    </button>
                </div>';} ?>
            </div>

            <?php
            if($Recipe->Source === ''){
                echo "<div class=\"recipeText recipeLeftHalf emptyInfo\" id=\"source\">";
            } else {
                echo "<div class=\"recipeText recipeLeftHalf\" id=\"source\">";
            } ?>
                <h2>Source: </h2>
                <h3><?php
                if (strtolower(substr($Recipe->URL, 0,4))=='http'){
                    echo('<a href="'.$Recipe->URL.'">'.'Recipe on: '.$Recipe->Source.'</a>');
                }
                else{
                    echo "$Recipe->Source";
                }
                ?></h3>
            </div>
            <?php
            if($Recipe->Region === ''){
                echo "<div class=\"recipeText recipeRightHalf emptyInfo\" id=\"region\"><h2>Region</h2><h3>".$Recipe->Region."</h3></div>";
            } else {
                echo "<div class=\"recipeText recipeRightHalf\" id=\"region\"><h2>Region</h2><h3>".$Recipe->Region."</h3></div>";
            }
            if($Recipe->Course === ''){
                echo "<div class=\"recipeText recipeLeftHalf emptyInfo\" id=\"course\"><h2>Course:</h2><h3>".$Recipe->Course."</h3></div>";
            } else {
                echo "<div class=\"recipeText recipeLeftHalf\" id=\"course\"><h2>Course:</h2><h3>".$Recipe->Course."</h3></div>";
            }
            if($Recipe->Language === ''){
                echo "<div class=\"recipeText recipeRightHalf emptyInfo\" id=\"lang\"><h2>Language:</h2><h3>".$Recipe->Language."</h3></div>";
            } else {
                echo "<div class=\"recipeText recipeRightHalf\" id=\"lang\"><h2>Language:</h2><h3>".$Recipe->Language."</h3></div>";
            }
            if($Recipe->MainIngredient === ''){
                echo "<div class=\"recipeText recipeLeftHalf emptyInfo\" id=\"mainIng\"><h2>Main Ingredient:</h2><h3>".$Recipe->MainIngredient."</h3></div>";
            } else {
                echo "<div class=\"recipeText recipeLeftHalf\" id=\"mainIng\"><h2>Main Ingredient:</h2><h3>".$Recipe->MainIngredient."</h3></div>";
            }
            if($Recipe->Special === ''){
                echo "<div class=\"recipeText recipeRightHalf emptyInfo\" id=\"special\"><h2>Special:</h2><h3>".$Recipe->Special."</h3></div>";
            } else {
                echo "<div class=\"recipeText recipeRightHalf\" id=\"special\"><h2>Special:</h2><h3>".$Recipe->Special."</h3></div>";
            } ?>

            <div class="recipeText ingredients recipeLeftHalf" id="ingredients">
                <h2>Ingredients:</h2><p><?php echo str_replace("\r\n","<br>",$Recipe->Ingredients)?></p>
            </div>
            <?php
            if(preg_match('#\s*?#', $Recipe->IngredientsContinued)){
                echo "<div class=\"recipeText ingredients recipeRightHalf emptyInfo\" id=\"ingredientsCont\">";
            } else {
                echo "<div class=\"recipeText ingredients recipeRightHalf\" id=\"ingredientsCont\">";
            }
            echo "<h2>Ingredients Continued:</h2><p>".str_replace("\r\n","<br>",$Recipe->IngredientsContinued)."</p></div>"?>

            <div class="recipeText instructions recipeFullRow" id="instructions">
              <h2>Instructions: </h2><p><?php echo str_replace("\r\n","<br>",$Recipe->Instructions)?></p>
            </div>

            <div class="recipeLeftThird">
                <?php
                if($Recipe->Notes === ''){
                    echo "<div class=\"recipeText emptyInfo\" id=\"notes\">";
                } else {
                    echo "<div class=\"recipeText recipeLeftThird\" id=\"notes\">";
                } ?>
                    <h2>Notes:</h2><p><?php echo str_replace("\r\n","<br>",$Recipe->Notes); ?></p>
                </div>
                <div class="recipeText" id="frequency">
                    <h2>Frequency:</h2>
                    <?php switch($Recipe->OnMenu){
                        case 0: echo ('<h3>Dish was never on menu</h3>'); break;
                        case 1: echo ('<h3>Dish on menu once:</h3>'); break;
                        default: echo('<h3>Dish was on menu '.$Recipe->OnMenu. ' times:</h3> '); break;
                        } ?>
                    <div class="onMenu" id="onMenu">
                        <?php
                        $i = 0;
                        $NumMenuDays = $Recipe->OnMenu;
                        while ($i < $NumMenuDays) {
                            echo ('<a class="menuDay" href="../MenuDB/InputMenuDay.php?MenuDate=');
                            echo ($MenuDays[$i].'">'.$MenuDays[$i].'<br></a>');
                            $i++;
                        } ?>
                    </div>
                </div>
            </div>
            <div class="recipeCenterThird">
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
                    <!--<div id="google_translate_element"></div>
                        <script type="text/javascript">
                            function googleTranslateElementInit() {
                            new google.translate.TranslateElement({pageLanguage: 'de', layout: google.translate.TranslateElement.InlineLayout.SIMPLE, multilanguagePage: true}, 'google_translate_element');
                        }
                    </script><script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
                    -->
                </div>
            </div>
            <div class="recipeRightThird">
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
        </div> <!-- recipeContainer -->

        <div id="modal" class="modal hidden">
            <div class="modal-content">
                <span class="close" id="closeSpan">&times;</span>
                <div class="textContainer">
                    <h1 class="center">Not Available</h1>
                </div>
            </div>
        </div>
	</div> <!-- content -->
</div> <!-- containerThin -->
<script type="text/javascript" src="../js/jquery-3.1.1.js"></script>
<script type="text/javascript" src="../js/imagesloaded.pkgd.min.js"></script>
<script type="text/javascript">
    //base page initialization and scripts
    let exists = true;
    if ('<?php echo $Recipe->Exists; ?>' === ''){
        exists = false;
    } else {
        exists = '<?php echo $Recipe->Exists; ?>';
    }
    if (!exists) {
        document.getElementById('content').style.display = 'none';
        let errordiv = document.getElementsByClassName('displayError')[0];
        errordiv.classList.remove('hidden');
        errordiv.innerHTML = 'Recipe Not Found - Invalid ID';
    }

    let info, i = 10, menuDays = document.getElementsByClassName('menuDay');
    if (menuDays.length >= 10 && window.location.hash !== '#showAll') {
        while (i < menuDays.length) {
            menuDays[i].parentNode.removeChild(menuDays[i]);
        }
        info = document.createElement("p");
        info.innerHTML = 'Most Recent Days (<a id="showAll">show all</a>):';
        menuDays[0].parentNode.insertBefore(info, menuDays[0]);
        document.getElementById('showAll').addEventListener('click', function(e) {
            window.location.hash = 'showAll';
            location.reload();
        });
    } else if (window.location.hash === '#showAll') {
        info = document.createElement("p");
        info.innerHTML = 'Showing All Days (<a id="showAll">hide</a>):';
        menuDays[0].parentNode.insertBefore(info, menuDays[0]);
        document.getElementById('showAll').addEventListener('click', function(e) {
            location.reload();
        });
    }
    window.location.hash = '';
    function displayMiniMenu() {
        let menu = document.getElementById('menuExtended');
        (menu.style.display === 'flex' ? menu.style.display = 'none' : menu.style.display = 'flex');
        menu.classList.toggle('miniMenu');
        menu.classList.toggle('fadeIn');
        this.classList.toggle('change');
    }
    document.getElementById('menuIcon').addEventListener('click', displayMiniMenu, false);

    let modal = document.getElementById('modal');
    function displayModal(){
        if (modal.classList.contains('hidden')) {modal.classList.remove('hidden');}
    }
    function hideModal(){
        if (!modal.classList.contains('hidden')) {modal.classList.add('hidden');}
    }
    document.getElementById('closeSpan').onclick = function() {hideModal()};
    window.onclick = function(event) {if (event.target === modal) {hideModal();}};
    window.onkeydown = function(event) {if (event.key ===  'Escape') {hideModal();}};

    //image display code
    const parent = document.getElementById('imgDisplay');
    const NPictures = Number(<?php echo($Recipe->NPictures); ?>);
    let max, imgW = 420;
    let blobs = document.querySelectorAll('.imgPosBlob');
    let $imgDisplay = $( '#imgDisplay' );

    function findClosest() {
        let userOffset = parent.scrollLeft;
        let closest = 0;
        let edge = 'left';
        let i = imgW;
        while (i < NPictures * imgW) {
            if (userOffset < i) {
                if (userOffset > i - (imgW / 2)) {
                    closest = (i / imgW);
                    return [closest, edge];
                } else {
                    closest = (i / imgW) - 1;
                    edge = 'right';
                    return [closest, edge];
                }
            } else {
                i = i + imgW;
                if (i  === NPictures * imgW) {
                    closest = i / imgW - 1;
                    return [closest, edge];
                }
            }
        }
    }

    function scrollImg(target) {
        $imgDisplay.animate({
            scrollLeft: target
        }, 200)
    }
    function scrollImgLinear(target){
        $imgDisplay.animate({
            scrollLeft: target
        }, 200, 'linear')
    }

    function changeImg(e) {
        let state = findClosest();
        const target = state[0];
        const edge = state[1];
        let adjusted = false;
        if (parent.scrollLeft % imgW === 0){adjusted = true}

        switch (e) {
            case (0):
                if (adjusted === false) {
                    if (edge === 'right') {
                        scrollImg(imgW * target);
                    } else {
                        scrollImg(imgW * (target - 1));
                    }
                } else {
                    if (target === 0) {
                        scrollImg(max);
                    } else {
                        scrollImg((target * imgW) - imgW);
                    }
                }
                break;
            case (1):
                if (adjusted === false) {
                    if (edge === 'left') {
                        scrollImg(imgW * target);
                    } else {
                        scrollImg(imgW * (target + 1))
                    }
                } else {
                    if (target * imgW === max) {
                        scrollImg(0);
                    } else {
                        scrollImg((target * imgW) + imgW);
                    }
                }
                break;
        }
        setTimeout(updateBlobs, 200);
    }
    function updateBlobs() {
        let state = findClosest();
        const target = state[0];
        blobs.forEach(element =>{
            if (element.classList.contains('filledBlob')) {
                element.classList.remove('filledBlob');
            }
        });
        if (target <= blobs.length && target >= 0){
            blobs[target].classList.add('filledBlob');
        } else {
            console.log('invalid target: ' + target);
        }
    }
    function snapImg(direction) {
        let state = findClosest();
        const target = state[0];
        const edge = state[1];
        switch (direction) {
            case('right'):
                if (edge === 'right') {
                    scrollImgLinear(imgW * target);
                } else {
                    scrollImgLinear(imgW * (target - 1));
                }
                break;
            case('left'):
                if (edge === 'left') {
                    scrollImgLinear(imgW * target);
                } else {
                    scrollImgLinear(imgW * (target + 1));
                }
                break;
            case('none'):
                break;
        }
        setTimeout(updateBlobs, 100);
    }
    function swipeDetect() {
        let swipedir, startX, startY, distX, distY,
            threshold = 50, allowedTime = 1000,
            elapsedTime, startTime;

        parent.addEventListener('touchstart', function (e) {
            let touchObj = e.changedTouches[0];
            swipedir = 'none';
            startX = touchObj.pageX;
            startY = touchObj.pageY;
            startTime = new Date().getTime();
        }, false);

        parent.addEventListener('touchend', function (e) {
            let touchObj = e.changedTouches[0];
            distX = touchObj.pageX - startX;
            distY = touchObj.pageY - startY;
            elapsedTime = new Date().getTime() - startTime;
            if (elapsedTime <= allowedTime) {
                if (Math.abs(distX) >= threshold) {
                    swipedir = (distX < 0) ? 'left' : 'right';
                }
            }
            snapImg(swipedir);
        }, false);
    }
    if (NPictures > 1) {
        imagesLoaded( document.querySelector('#imgDisplay'), function( instance ) {
            imgW = document.getElementById('rPic1').width;
            max = (imgW * NPictures) - imgW;
        });
        swipeDetect();
        updateBlobs();
    } else if (NPictures === 1) {
        parent.style.marginBottom = '1em';
    }
</script>
</body>
