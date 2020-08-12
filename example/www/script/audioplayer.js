function audio_play(evt, elemId) {
			document.getElementById(elemId).play(); 
			evt.stopPropagation();
		}
		
		function popup_show(elemId){
			document.getElementById(elemId).style.display = "block";
		}
		
		function popup_hide(elemId) {
			document.getElementById("info_" + elemId).style.display = "none";
			document.getElementById("audio_" + elemId).pause();
			document.getElementById("audio_" + elemId).currentTime = 0;
		}