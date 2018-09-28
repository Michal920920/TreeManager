var remove = function()
{
    	var id = $("#removeid").val();
        var url = "api/CoreModule-Tree-Manager/remove/" + id;
        
        $.getJSON(url, function(data) {
        	if(data){
        	if($('#removeError').length){
        		$('#removeError').fadeTo("slow", 0);
        		
        		$(".row").remove();
            	tree();
        		}
        	
        	else{
        		$(".row").remove();
        		tree();
        		}
        	}
        	
        	else if (!data){	//výpis chybové hlášky
            	if($('#removeError').length){
            		$('#removeError').fadeTo("slow", 1);
            	}
            	else{
            		$('#removeid').before('<div id="removeError">Špatně zadaná hodnota');
            		$('#removeError').hide().fadeTo("slow", 1);
            		}
        		}
            });
};

var add = function()
{
    	var pid = $("#addpid").val();
        var url = "api/CoreModule-Tree-Manager/add/" + pid;
        
        $.getJSON(url, function(data) {
        	if($('#addError').length){
        		$('#addError').fadeTo("slow", 0);
        		
        		$(".row").remove();
            	tree();
        		}
        	
        	else{
        		$(".row").remove();
        		tree();
        	}
        	
            }).fail(function(data) {	//výpis chybové hlášky
            	if($('#addError').length){
            		$('#addError').fadeTo("slow", 1);
            	}
            	else{
            		$('#addpid').before('<div id="addError">Špatně zadaná hodnota');
            		$('#addError').hide().fadeTo("slow", 1);
            	}
            });
};
    
    var tree = function()
    {
            var url = "api/CoreModule-Tree-Manager/refresh-tree";
            var previousLvl = null;
            var previousid = null;
            
            $.getJSON(url, function(data) {
            	 $.each(data, function() {
            		 if(previousLvl == null){	//pokud předešlý lvl nebyl zadán, vytvoří se první row
            			 $('.table').append("<div class='row' id='lvl_"+this.lvl+"'><div class='cell' id='"+this.id+"'>id: "+this.id+"["+this.pid+"]</div>");
            			}
            		 
            		 else if(previousLvl == this.lvl){	//pokud je lvl stejný jako předešlý
            			 $('#'+previousid).after("<div class='cell' id='"+this.id+"'>id: "+this.id+"["+this.pid+"]</div>");
            		 }
            		 else{	//pokud předešlý nemá stejný lvl a zároveň není prvním row
            			 $('#lvl_'+previousLvl).after("<div class='row' id='lvl_"+this.lvl+"'><div class='cell' id='"+this.id+"'>id: "+this.id+"["+this.pid+"]</div>");
            		 }
            		 previousLvl = this.lvl;
            		 previousid =  this.id;
         	    
            	 	});
            	 
                }).fail(function (data) {
                    alert("Při výpisu došlo k chybě.")
                });
};

var events = function()
{
	$("#add").click(add);
	$("#remove").click(remove);
	tree();
}
$(document).ready(events);