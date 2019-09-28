function testAjax(buildingName, clickedElement){

    var allPointers = document.getElementsByClassName("pointer");
    for (var i=0; i < allPointers.length; i++){
        if(allPointers[i].className.match(/(?:^|\s)pointerBlue(?!\S)/)){
            allPointers[i].className = allPointers[i].className.replace
            ( /(?:^|\s)pointerBlue(?!\S)/g , '' )
        }
    }
    clickedElement.className += " pointerBlue";


    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function(){
        if (this.readyState == 4 && this.status == 200){
            var myJson = JSON.parse(this.responseText);

            if(myJson["charClass"] == "Builder"){
                var apNotice = "<small> 2X</small>";
            }
            else{
                var apNotice = "";
            }
            document.getElementById("allDetails").removeAttribute("hidden");
            document.getElementById("buildInfoName").innerHTML = myJson["name"] + " [Lv. " + myJson["level"] + " of " + myJson["maxLevel"] + "]";
            document.getElementById("buildName").value = myJson["name"];
            document.getElementById("costs").innerHTML = myJson["costs"];
            document.getElementById("description").innerHTML = myJson["description"];
            document.getElementById("apAssigned").innerHTML = myJson["currentAp"] + "/" + myJson["maxAp"] + "<img src='../images/icons/ap.png'></img>";


            if(myJson["level"] >= 1){
                if(myJson["currentAp"] >= 1){
                    document.getElementById("buttonSpan").innerHTML = "<button type='submit' value='Submit' class='buildButton'><span>Contribute  " + apNotice + "</span></button>";
                    document.getElementById("buttonSpan").style.marginTop = "-8px";
                    document.getElementById("apInput").disabled = false;
                    document.getElementById("costs").style.visibility = "hidden";
                }
                else{
                    document.getElementById("costs").style.visibility = "visible";
                    if(myJson["affordable"] == true){
                        document.getElementById("buttonSpan").innerHTML = "<button type='submit' value='Submit' class='buildButton'><span>Upgrade  " + apNotice + "</span></button>";
                        document.getElementById("buttonSpan").style.marginTop = "-8px";
                        document.getElementById("apInput").disabled = false;
                    }
                    else{
                        document.getElementById("buttonSpan").innerHTML = "Not Enough Resources";
                        document.getElementById("buttonSpan").style.marginTop = "0px";  
                        document.getElementById("apInput").disabled = true;
                    }
                }
            }
            else if(myJson["currentAp"] >= 1){
                document.getElementById("buttonSpan").innerHTML = "<button type='submit' value='Submit' class='buildButton'><span>Contribute  " + apNotice + "</span></button>";
                document.getElementById("buttonSpan").style.marginTop = "-8px";
                document.getElementById("apInput").disabled = false;
                document.getElementById("costs").style.visibility = "hidden";
            }
            else{
                document.getElementById("costs").style.visibility = "visible";
                if(myJson["affordable"] == false){
                    document.getElementById("buttonSpan").innerHTML = "Not Enough Resources";
                    document.getElementById("buttonSpan").style.marginTop = "0px";  
                    document.getElementById("apInput").disabled = true;
                }
                else{
                        document.getElementById("buttonSpan").innerHTML = "<button type='submit' value='Submit' class='buildButton'><span>Build Now  " + apNotice + "</span></button>"; 
                        document.getElementById("buttonSpan").style.marginTop = "-8px";
                        document.getElementById("apInput").disabled = false; 
 
                }
            }
        }
    };

    xmlhttp.open("POST", "../inTown/townfunctions/displaystructure.php", true);

    //Allow data to be POSTed
    xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

    xmlhttp.send("x=" + buildingName);

}


function lockDownAp(maxAp){
    var el = document.getElementById("apInput");
    if(parseInt(el.value) > parseInt(maxAp)){
        el.value = parseInt(maxAp);
    }
    else if(parseInt(el.value) < 0){
        el.value = 0;
    }
}