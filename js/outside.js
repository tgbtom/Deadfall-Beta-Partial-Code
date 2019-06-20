function moveCharacter()
	{
		var xhttp = new XMLHttpRequest();
	}
	
	function displayItem(item, desc, mass)
	{
		document.getElementById("itemName").innerHTML = item;
		document.getElementById("itemName2").value = item;
		document.getElementById("itemDesc").innerHTML = desc;
		document.getElementById("itemDescMass").innerHTML = "Mass: " + mass;
		document.getElementById("PickUp").value = "Pick Up " + item;
		document.getElementById("PickUp").disabled = false;
		//unselect previous items to change which item has the selected border
		var sel = document.getElementsByClassName("selected");
		for (i = 0; i < sel.length; i++) 
		{
		sel[i].className = "notSelected";
		}
	}
	function display(Z, Co, Co2, Dep)
		{
			document.getElementById("zedCount").innerHTML = Z;
			document.getElementById("xco").innerHTML = Co;
			document.getElementById("yco").innerHTML = Co2;
			document.getElementById("lootability").innerHTML = Dep;
                        
			//Note: Arguments[0 - 2] come before the groundItems
			document.getElementById("itemsDiv").innerHTML = "";
                        if ((Co == "0" && Co2 == "0")){
                            document.getElementById("itemsDiv").innerHTML = "<b><i>Use the navigation arrows on the left, to leave the town.</i></b>";
                        }
                        else if (arguments.length <= 4){
                            document.getElementById("itemsDiv").innerHTML = "<b><i>Nothing on the ground.</i></b>";
                        }
                        else{
                            for (i = 0; i < arguments.length; i++)
                            {
								// > 3 to ensure it skips past coordinates and Zed Count AND depletion amount
								if (i > 3)
								{
                    				if (arguments[i] != "-1"){
									var itemNameNow = itemsInfo[arguments[i]][0];
									var itemDescNow = itemsInfo[arguments[i]][1];
									var itemMassNow = itemsInfo[arguments[i]][2];
									document.getElementById("itemsDiv").innerHTML = document.getElementById("itemsDiv").innerHTML + '<img onclick="displayItem(`' + itemNameNow + '`,`' + itemDescNow + '`,`' + itemMassNow + '`); this.className = `selected`" title="' + itemNameNow + '" src="../images/items/' + itemNameNow + '.png">';
                    			}
				}
                            }
			}
                                                
                        if (Dep <= 0){
                            document.getElementById("lootWarning").innerHTML = "This Zone is Depleted";
                        }
		}
		function remoteDisplay(Z, Co, Co2, Dep)
		{
			document.getElementById("remoteZedCount").innerHTML = Z;
			document.getElementById("remoteXco").innerHTML = Co;
			document.getElementById("remoteYco").innerHTML = Co2;
			document.getElementById("remoteLootability").innerHTML = Dep;
                        
			//Note: Arguments[0 - 2] come before the groundItems
			document.getElementById("remoteItemsDiv").innerHTML = "";
                        if ((Co == "0" && Co2 == "0")){
                            document.getElementById("remoteItemsDiv").innerHTML = "<b><i>Click on a Zone to see the last known information</i></b>";
                        }
                        else if (arguments.length <= 4){
                            document.getElementById("remoteItemsDiv").innerHTML = "<b><i>Nothing seen at The zone.</i></b>";
                        }
                        else{
                            for (i = 0; i < arguments.length; i++)
                            {
				// > 3 to ensure it skips past coordinates and Zed Count AND depletion amount
				if (i > 3)
				{
                    if (arguments[i] != "-1")
                    {
					var itemNameNow = itemsInfo[arguments[i]][0];
					var itemDescNow = itemsInfo[arguments[i]][1];
					document.getElementById("remoteItemsDiv").innerHTML = document.getElementById("remoteItemsDiv").innerHTML + '<img title="' + itemNameNow + '" src="../images/items/' + itemNameNow + '.png">';
                                    }
				}
                            }
			}
		}