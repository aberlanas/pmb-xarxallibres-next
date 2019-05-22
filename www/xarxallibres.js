
$(document).ready(function(){
    $("#butDevuelve").click(function(){
	
	console.log(" * Devolviendo ... ");
	var books=[];
	var rows = $("#tDev tbody tr");
	rows.each(function(){
		console.log(" * next book *");
		var book = {};
		$(this).find("td[name='valor']").each(function() {
			book.expl_cb=$(this)[0].innerText;
		})
		$(this).find("td input[class='devolucion']").each(function() {
			if($(this)[0].checked){	book.action=$(this)[0].value;}
		})
		if(book.action != "nodevolver"){
			book.categ="retour";
			book.expl_statut="17";
			book.module="estado";
			books.push(book);
		}
		
	});	
	console.log(books);
	
	books.forEach(function(book){
		if (book.action=="devolver"){
			var xlurl="circ.php?categ=retour&cb_expl="+book.expl_cb;
			console.log(" URL : "+xlurl);
			window.open(xlurl);
		}
	});
	/*
	$("td[name='valor']").each(function () {
			console.log($(this)[0].innerText);
	});

	$("input[type='radio']:checked").each(function () {

	        console.log('selected: ' + $(this).val());
		var expl_id=$(this).attr("name").replace('devolucion','');

		if ($(this).val() == "retirar"){
			console.log(" ** Retirando ...  : "+expl_id);
		}
		else if ($(this).val() == "devolver"){
			console.log(" ** Devolviendo! ... : "+expl_id);		
		}
		if ($(this).val() == "nodevolver"){
			console.log(" ** Dejando igual ... : "+expl_id);		
		}

    	});
	*/

	});
});

function saluda() {
	alert("hola");
}
