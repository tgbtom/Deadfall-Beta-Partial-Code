function colourizeRarity(){
	elements = document.getElementsByClassName("rarity");
	for (i = 0; i < elements.length; i++){
		switch(elements[i].innerHTML){
			case "Common":
				elements[i].style.color = "gray";
				break;
			case "Uncommon":
				elements[i].style.color = "darkgreen";
				break;
			case "Rare":
				elements[i].style.color = "blue";
				break;
			case "Ultra-Rare":
				elements[i].style.color = "purple";
				break;
			case "Legendary":
				elements[i].style.color = "orange";
				break;
			default: elements[i].style.color = "black";
		}
	}
}

function colourizeMass(){
    element = document.getElementById("carryCapacity");
    if(element != null)
    {
        elementContent = element.innerHTML;
        //(5/10)
        newStringArray = elementContent.split("/");
        // 0=> current Mass, 1=> Maximum mass
        massPercentage = parseInt(newStringArray[0].substr(1)) / parseInt(newStringArray[1].slice(0, -1));
        if (massPercentage >= 1){
            element.style.color = "red";
        }
        else if(massPercentage >= 0.8){
            element.style.color = "orange";
        }
        else if(massPercentage >= 0.6){
            //yellow
            element.style.color = "yellow";
        }
        else{
            //green
            element.style.color = "#3DAF3D";
        }
    }
}

window.onload = function(){
    colourizeRarity();
    colourizeMass();
}

function popUpMenu(x)
{   
	var popup = document.getElementById(x);
	if (popup.style.visibility === 'visible'){
            var wasUp = true;
	}
	else{
            var wasUp = false;
	}
        
	var popuptext = document.getElementsByClassName('popuptext');
	for (var i = 0; i < popuptext.length; i++){
		popuptext[i].style.visibility = 'hidden';
	}
        
        if (!wasUp){
		popup.style.visibility = 'visible';
	}

}

function newAction(target, hiddenNameId)
{
	if (target === 'drop')
	{	
		document.sendItemData.action = "../functions/dropItem.php?nameTagId=" + hiddenNameId;
	}
	else if (target === 'Eat' || target === 'Drink' || target === 'Load')
	{
		document.sendItemData.action = "../functions/consume.php?nameTagId=" + hiddenNameId;
	}
	else if (target === 'Attack')
	{
		document.sendItemData.action = "../functions/consume.php?nameTagId=" + hiddenNameId;
	}
	else
	{
		document.sendItemData.action = "../inTown/?locat=warehouse";
	}
}

function changeChar(newChar) {
		if (newChar.length === 0) 
		{
			return;
		} 
		else
		{
			var xmlhttp = new XMLHttpRequest();
				xmlhttp.onreadystatechange = function() {
					if (xmlhttp.readyState === 4 && xmlhttp.status === 200) 
					{
						//document.getElementById("txtHint").innerHTML = xmlhttp.responseText;
					}
				};
			xmlhttp.open("GET", "../functions/changeChar.php?change="+newChar, true);
			xmlhttp.send();
			window.location.reload();
		}	
	}
