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
    elementContent = element.innerHTML;
    //Mass: 5/10
    stringToArray = elementContent.split(" ");
    //Mass:, 5/10
    newStringArray = stringToArray[1].split("/");
    // 0=> current Mass, 1=> Maximum mass
    massPercentage = newStringArray[0] / newStringArray[1];
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
        element.style.color = "darkgreen";
    }
}

window.onload = function(){
    colourizeRarity();
    colourizeMass();
}