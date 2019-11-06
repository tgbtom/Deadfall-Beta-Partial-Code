/** Direction can be either "add" or "remove" */
function skillAjax(skillId, clickedElement, direction){
    var allPointers = document.getElementsByClassName("pointer");
    for (var i=0; i < allPointers.length; i++){
        if(allPointers[i].className.match(/(?:^|\s)pointerBlack(?!\S)/)){
            allPointers[i].className = allPointers[i].className.replace
            ( /(?:^|\s)pointerBlack(?!\S)/g , '' )
        }
    }
    clickedElement.className += " pointerBlack";

    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function(){
        if(this.readyState == 4 && this.status == 200){
            //PERFORM JS HERE
            skillJson = JSON.parse(this.responseText);

            document.getElementById("skillName").innerHTML = skillJson["name"].replace("_", " ");
            document.getElementById("skillDescription").innerHTML = skillJson["description"].replace("_", " ");
            if(direction == "NA"){
                document.getElementById("skill_button").style.display = "none";
            }
            else{
                if(direction == "Remove"){
                    document.getElementById("skill_button").className = "skill_button_remove";
                }
                else{
                    document.getElementById("skill_button").className = "skill_button";
                }
                document.getElementById("skill_button").innerHTML = direction;
                document.getElementById("skill_button").style.display = "block";
            }
        }
    };

    xmlhttp.open("POST", "../intown/townfunctions/displayskill.php", true);
    xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xmlhttp.send("skillid=" + skillId);
}

function modifySkillsAjax(){

    var allPointers = document.getElementsByClassName("pointer");
    for (var i=0; i < allPointers.length; i++){
        if(allPointers[i].className.match(/(?:^|\s)pointerBlack(?!\S)/)){
            //allPointers[i] is the DOM element of the selected TD
            selectedSkillId = allPointers[i].id.split("_")[1];
        }
    }

    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function(){
        if(this.readyState == 4 && this.status == 200){
            myJson = JSON.parse(this.responseText);

            var skillPercent = (myJson["UsedPoints"] / myJson["MaxPoints"]) * 100;
            document.getElementById("spBar").style.width = skillPercent + "%";
            document.getElementById("spBar").innerHTML = Math.round(skillPercent) + "%";
            document.getElementById("usedSkillPoints").innerHTML = myJson["UsedPoints"];

            if(myJson["Direction"] == "Assign"){
                var elementToMove = document.getElementById("skill_" + myJson["SkillId"]);
                elementToMove.parentElement.parentElement.removeChild(elementToMove.parentElement);
    
                var elementToAdd = document.getElementById("skill" + myJson["NewSlot"]);
                elementToAdd.firstChild.innerHTML = "<td>" + myJson["SkillName"] + " <div class='numberCircle'>" + myJson["SkillCost"] + "</div></td>";
                elementToAdd.firstChild.className = "pointer pointerGreen";
                elementToAdd.firstChild.onclick = function(){skillAjax(this.id.split("_")[1], this, "Remove")};
                elementToAdd.firstChild.id = "skill_" + myJson["SkillId"];
    
                skillAjax(myJson["SkillId"], elementToAdd.firstChild, "Remove");
            }
            else if(myJson["Direction"] == "Remove"){
                //Need to move the skill across the other direction, from assigned, to available

                var elementToEmpty = document.getElementById("skill" + myJson["EmptiedSlot"]);
                elementToEmpty.innerHTML = "<td>Empty Skill Slot</td>";

                //Copy and rows below this deletion and move them up one space.
                var allRowsAssigned = document.getElementById("skillsAssigned").rows;
                for(var i = myJson["EmptiedSlot"] + 2; i < 7; i++){
                    if(i == 6 || allRowsAssigned[i].firstChild.innerHTML == "Empty Skill Slot"){
                        allRowsAssigned[i - 1].removeChild(allRowsAssigned[i - 1].firstChild);
                        allRowsAssigned[i - 1].innerHTML = "<td>Empty Skill Slot</td>";
                    }
                    else{
                        var cln = allRowsAssigned[i].firstChild.cloneNode(true);
                        var tdNode = allRowsAssigned[i-1].childNodes[0];
                        allRowsAssigned[i-1].replaceChild(cln, allRowsAssigned[i-1].childNodes[0]);
                        allRowsAssigned[i-1].childNodes[0].onclick = function(){
                            skillAjax(this.id.split("_")[1], this, "Remove")
                        };
                    }
                }

                var tr = document.createElement("tr");
                var td = document.createElement("td");
                var tbody = document.getElementById("allSkills");

                tbody.appendChild(tr);
                tr.appendChild(td);

                td.innerHTML = "<td>" + myJson["SkillName"] + " <div class='numberCircle'>" + myJson["SkillCost"] + "</div></td>";
                td.className = "pointer";
                td.onclick = function(){skillAjax(this.id.split("_")[1], this, "Assign")};
                td.id = "skill_" + myJson["SkillId"];
    
                skillAjax(myJson["SkillId"], td, "Assign");
            }
        }
    };

    xmlhttp.open("POST", "../intown/townfunctions/modifyActiveSkills.php", true);
    xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xmlhttp.send("skillid=" + selectedSkillId);
}