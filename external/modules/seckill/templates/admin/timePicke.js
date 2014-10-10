// JavaScript Document

 $.fn.numeral = function() {
            $(this).css("ime-mode", "disabled");
            this.bind("keypress",function() {
                if (event.keyCode == 46) {
                    if (this.value.indexOf(".") != -1) {
                        return false;
                    }
                } else {
                    return event.keyCode >= 46 && event.keyCode <= 57;
                }
            });
            this.bind("blur", function() {
                if (this.value.lastIndexOf(".") == (this.value.length - 1)) {
                    this.value = this.value.substr(0, this.value.length - 1);
                } 
            });
            this.bind("paste", function() {
                var s = clipboardData.getData('text');
                if (!/\D/.test(s));
                value = s.replace(/^0*/, '');
                return false;
            });
            this.bind("dragenter", function() {
                return false;
            });

        };

function timePicke(){
	var obj;
	var inputCont;
	var alertMsg;
	var revert_msg;
	this.init = function(id){
		obj = $('#'+id);
		if(!obj){
			return;
		}
		inputCont = obj.val();
		if(inputCont.length <=2){
			obj.val('::');
		}
		
        obj.keyup(function(event) {
		  checkInput();
		  if(((event.which < 48 && event.which > 57) || (event.which <96 && event.which > 105))){
			event.preventDefault(); 
		  }
		  checkdel(event);
          });
       obj.blur(function(){check_time_format()});
	   obj.focus(function(){msg_revert()});
	   obj.numeral();
	}
	
	function checkInput(){
		inputCont = obj.val();
		if(inputCont.length > 8){
			obj.val(inputCont.substr(inputCont,8));
		}
		if(inputCont.length <=2){
			obj.val('::');
			obj.position(0)
		}
		if(inputCont.length == 4){
		    obj.position(3)
		}
		
		if(inputCont.length == 6){
			obj.position(7)
		}
		
	}
	

	function check_time_format(){
		inputCont = obj.val();
		var reg = new RegExp("^([0-1]\\d|2[0-3]):[0-5]\\d:[0-5]\\d$");   
		if(!reg.test(inputCont)){
			revert_msg = obj.parent().find('label').text();
			obj.parent().find('label').remove();
			obj.after('<label class="error_label">'+alertMsg+'</label>');
		}
	}
	this.set_alert_msg = function(msg_info){
		alertMsg = msg_info;
	}
	
	function checkdel(event){
		if(event.which == 8 && (inputCont.substr(obj.position()-1,1) == ':')){
			event.preventDefault();
			obj.position(obj.position()-1);
		}
	}
	
	function msg_revert(){
		obj.parent().find('label').text(revert_msg);
		obj.parent().find('label').addClass("alert_label");
		obj.parent().find('label').removeClass("error_label");
	}
}